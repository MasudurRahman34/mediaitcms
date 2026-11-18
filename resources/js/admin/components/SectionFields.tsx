import { useState } from 'react';
import {
  DndContext,
  closestCenter,
  PointerSensor,
  useSensor,
  useSensors,
  type DragEndEvent,
} from '@dnd-kit/core';
import {
  SortableContext,
  arrayMove,
  useSortable,
  verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { GripVertical, Plus, Trash2 } from 'lucide-react';
import type { PageSection, SectionDefinition, SectionField } from '../lib/api';
import { Button, Toggle } from './ui';
import { RichTextEditor } from './RichTextEditor';
import { ImageField } from './MediaPicker';
import { cn } from '@/lib/cn';

/* ------------------------------------------------------------ field editor */

export function SectionFields({
  fields,
  values,
  onChange,
}: {
  fields: SectionField[];
  values: Record<string, unknown>;
  onChange: (values: Record<string, unknown>) => void;
}) {
  const set = (name: string, value: unknown) => onChange({ ...values, [name]: value });

  return (
    <div className="space-y-4">
      {fields.map((field) => {
        const value = values[field.name];

        if (field.type === 'boolean') {
          return (
            <div key={field.name} className="pt-1">
              <Toggle
                checked={Boolean(value)}
                onChange={(next) => set(field.name, next)}
                label={field.label}
              />
            </div>
          );
        }

        if (field.type === 'richtext') {
          return (
            <div key={field.name}>
              <span className="admin-label">{field.label}</span>
              <RichTextEditor
                value={typeof value === 'string' ? value : ''}
                onChange={(html) => set(field.name, html)}
              />
            </div>
          );
        }

        if (field.type === 'textarea') {
          return (
            <div key={field.name}>
              <span className="admin-label">{field.label}</span>
              <textarea
                value={typeof value === 'string' ? value : ''}
                onChange={(event) => set(field.name, event.target.value)}
                rows={4}
                className="admin-input min-h-24"
              />
              {field.hint && <p className="mt-1 text-xs text-ink/35">{field.hint}</p>}
            </div>
          );
        }

        if (field.type === 'image') {
          return (
            <ImageField
              key={field.name}
              label={field.label}
              value={typeof value === 'number' ? value : null}
              onChange={(id) => set(field.name, id)}
            />
          );
        }

        if (field.type === 'select') {
          return (
            <div key={field.name}>
              <span className="admin-label">{field.label}</span>
              <select
                value={typeof value === 'string' ? value : ''}
                onChange={(event) => set(field.name, event.target.value)}
                className="admin-input"
              >
                {Object.entries(field.options ?? {}).map(([optionValue, label]) => (
                  <option key={optionValue} value={optionValue}>
                    {label}
                  </option>
                ))}
              </select>
            </div>
          );
        }

        if (field.type === 'number') {
          return (
            <div key={field.name}>
              <span className="admin-label">{field.label}</span>
              <input
                type="number"
                min={1}
                max={24}
                value={typeof value === 'number' ? value : Number(value ?? 3)}
                onChange={(event) => set(field.name, Number(event.target.value))}
                className="admin-input"
              />
            </div>
          );
        }

        if (field.type === 'cta') {
          const cta = (value ?? {}) as { label?: string; url?: string };
          return (
            <div key={field.name} className="border-line rounded-lg border p-3">
              <p className="mb-3 text-xs font-semibold tracking-wide text-ink/50 uppercase">
                {field.label}
              </p>
              <div className="grid gap-3 sm:grid-cols-2">
                <input
                  value={cta.label ?? ''}
                  onChange={(event) => set(field.name, { ...cta, label: event.target.value })}
                  placeholder="Label"
                  className="admin-input"
                />
                <input
                  value={cta.url ?? ''}
                  onChange={(event) => set(field.name, { ...cta, url: event.target.value })}
                  placeholder="/contact"
                  className="admin-input"
                />
              </div>
            </div>
          );
        }

        if (field.type === 'repeater') {
          const rows = Array.isArray(value) ? (value as Array<Record<string, unknown>>) : [];
          const subFields = field.fields ?? [];

          return (
            <div key={field.name} className="border-line rounded-lg border p-3">
              <div className="mb-3 flex items-center justify-between">
                <p className="text-xs font-semibold tracking-wide text-ink/50 uppercase">
                  {field.label}
                </p>
                <Button
                  size="sm"
                  variant="subtle"
                  icon={<Plus size={13} />}
                  onClick={() =>
                    set(field.name, [
                      ...rows,
                      Object.fromEntries(
                        subFields.map((sub) => [sub.name, sub.type === 'number' ? 0 : '']),
                      ),
                    ])
                  }
                >
                  Add
                </Button>
              </div>

              <div className="space-y-3">
                {rows.length === 0 && <p className="text-xs text-ink/30">No items yet.</p>}

                {rows.map((row, index) => (
                  <div key={index} className="border-line rounded-lg border bg-near-black/30 p-3">
                    <div className="mb-2 flex items-center justify-between">
                      <span className="text-[11px] font-medium text-ink/35">Item {index + 1}</span>
                      <button
                        type="button"
                        onClick={() =>
                          set(
                            field.name,
                            rows.filter((_, i) => i !== index),
                          )
                        }
                        className="text-ink/30 transition hover:text-red-300"
                        aria-label={`Remove item ${index + 1}`}
                      >
                        <Trash2 size={13} />
                      </button>
                    </div>

                    <SectionFields
                      fields={subFields}
                      values={row}
                      onChange={(next) =>
                        set(
                          field.name,
                          rows.map((item, i) => (i === index ? next : item)),
                        )
                      }
                    />
                  </div>
                ))}
              </div>
            </div>
          );
        }

        if (field.type === 'icon') {
          return (
            <div key={field.name}>
              <span className="admin-label">{field.label}</span>
              <input
                value={typeof value === 'string' ? value : ''}
                onChange={(event) => set(field.name, event.target.value)}
                placeholder="code, sparkles, cloud…"
                className="admin-input"
              />
              <p className="mt-1 text-xs text-ink/35">
                Lucide icon name, e.g. code, sparkles, cloud, shield, users, layers, zap, chart.
              </p>
            </div>
          );
        }

        return (
          <div key={field.name}>
            <span className="admin-label">
              {field.label} {field.required && <span className="text-accent-soft">*</span>}
            </span>
            <input
              value={typeof value === 'string' ? value : ''}
              onChange={(event) => set(field.name, event.target.value)}
              className="admin-input"
            />
            {field.hint && <p className="mt-1 text-xs text-ink/35">{field.hint}</p>}
          </div>
        );
      })}
    </div>
  );
}

/* ---------------------------------------------------------- sortable item */

function SortableSection({
  section,
  index,
  definition,
  active,
  onSelect,
  onToggle,
  onRemove,
}: {
  section: PageSection;
  index: number;
  definition?: SectionDefinition;
  active: boolean;
  onSelect: () => void;
  onToggle: () => void;
  onRemove: () => void;
}) {
  const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable({
    id: `section-${index}`,
  });

  return (
    <li
      ref={setNodeRef}
      style={{ transform: CSS.Transform.toString(transform), transition }}
      className={cn(
        'group flex items-center gap-3 rounded-lg border p-3 transition',
        active ? 'border-accent/50 bg-accent/5' : 'border-line bg-panel',
        isDragging && 'z-10 opacity-80 shadow-xl',
        !section.is_enabled && 'opacity-55',
      )}
    >
      <button
        type="button"
        {...attributes}
        {...listeners}
        className="cursor-grab rounded p-1 text-ink/25 transition hover:text-ink/60 active:cursor-grabbing"
        aria-label="Reorder section"
      >
        <GripVertical size={16} />
      </button>

      <button type="button" onClick={onSelect} className="min-w-0 flex-1 text-left">
        <p className="truncate text-sm font-medium text-ink">{definition?.label ?? section.type}</p>
        <p className="truncate text-xs text-ink/35">
          {(section.content?.heading as string) || definition?.description || section.type}
        </p>
      </button>

      <span
        onClick={(event) => {
          event.stopPropagation();
          onToggle();
        }}
      >
        <Toggle checked={section.is_enabled} onChange={onToggle} />
      </span>

      <button
        type="button"
        onClick={onRemove}
        className="rounded p-1 text-ink/25 transition hover:text-red-300"
        aria-label="Remove section"
      >
        <Trash2 size={14} />
      </button>
    </li>
  );
}

/* -------------------------------------------------------------- builder UI */

export function SectionList({
  sections,
  registry,
  activeIndex,
  onSelect,
  onReorder,
  onToggle,
  onRemove,
}: {
  sections: PageSection[];
  registry: Record<string, SectionDefinition>;
  activeIndex: number | null;
  onSelect: (index: number) => void;
  onReorder: (sections: PageSection[]) => void;
  onToggle: (index: number) => void;
  onRemove: (index: number) => void;
}) {
  const sensors = useSensors(useSensor(PointerSensor, { activationConstraint: { distance: 5 } }));

  const handleDragEnd = (event: DragEndEvent) => {
    const { active, over } = event;
    if (!over || active.id === over.id) return;

    const oldIndex = Number(String(active.id).replace('section-', ''));
    const newIndex = Number(String(over.id).replace('section-', ''));

    onReorder(arrayMove(sections, oldIndex, newIndex));
  };

  return (
    <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
      <SortableContext
        items={sections.map((_, index) => `section-${index}`)}
        strategy={verticalListSortingStrategy}
      >
        <ul className="space-y-2">
          {sections.map((section, index) => (
            <SortableSection
              key={`section-${index}-${section.type}`}
              section={section}
              index={index}
              definition={registry[section.type]}
              active={activeIndex === index}
              onSelect={() => onSelect(index)}
              onToggle={() => onToggle(index)}
              onRemove={() => onRemove(index)}
            />
          ))}
        </ul>
      </SortableContext>
    </DndContext>
  );
}

export function AddSectionGrid({
  registry,
  onAdd,
}: {
  registry: Record<string, SectionDefinition>;
  onAdd: (type: string) => void;
}) {
  const [query, setQuery] = useState('');
  const filtered = Object.entries(registry).filter(([, definition]) =>
    definition.label.toLowerCase().includes(query.toLowerCase()),
  );

  return (
    <div>
      <input
        value={query}
        onChange={(event) => setQuery(event.target.value)}
        placeholder="Search sections…"
        className="admin-input mb-4"
      />

      <div className="grid gap-2 sm:grid-cols-2">
        {filtered.map(([type, definition]) => (
          <button
            key={type}
            type="button"
            onClick={() => onAdd(type)}
            className="border-line rounded-lg border p-3 text-left transition hover:border-accent/50 hover:bg-accent/5"
          >
            <p className="text-sm font-medium text-ink">{definition.label}</p>
            <p className="mt-1 text-xs text-ink/40">{definition.description}</p>
          </button>
        ))}
      </div>
    </div>
  );
}
