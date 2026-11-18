import { useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, ExternalLink, History, Eye, Plus, Save, Send, Trash2 } from 'lucide-react';
import {
  api,
  endpoints,
  type PageDetail,
  type PageSection,
  type SectionDefinition,
} from '../lib/api';
import { errorMessage, fieldErrors, useApi } from '../lib/useApi';
import {
  Badge,
  Button,
  ConfirmDialog,
  Field,
  Input,
  Modal,
  PageHeader,
  Spinner,
  Tabs,
  useToast,
} from '../components/ui';
import { AddSectionGrid, SectionFields, SectionList } from '../components/SectionFields';
import { ImageField } from '../components/MediaPicker';

type DetailResponse = { data: PageDetail; registry: Record<string, SectionDefinition> };

export function PageBuilderPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const toast = useToast();

  const { data, loading, reload } = useApi<DetailResponse>(`${endpoints.pages}/${id}`);
  const registry = data?.registry ?? {};

  const [sections, setSections] = useState<PageSection[]>([]);
  const [meta, setMeta] = useState({
    title: '',
    slug: '',
    seo_title: '',
    seo_description: '',
    og_media_id: null as number | null,
  });
  const [activeIndex, setActiveIndex] = useState<number | null>(null);
  const [dirty, setDirty] = useState(false);
  const [saving, setSaving] = useState(false);
  const [publishing, setPublishing] = useState(false);
  const [addingOpen, setAddingOpen] = useState(false);
  const [versionsOpen, setVersionsOpen] = useState(false);
  const [removeIndex, setRemoveIndex] = useState<number | null>(null);
  const [tab, setTab] = useState('sections');
  const [errors, setErrors] = useState<Record<string, string>>({});

  useEffect(() => {
    if (!data) return;
    setSections(data.data.sections);
    setMeta({
      title: data.data.title,
      slug: data.data.slug,
      seo_title: data.data.seo_title ?? '',
      seo_description: data.data.seo_description ?? '',
      og_media_id: data.data.og_media_id,
    });
    setDirty(false);
    setActiveIndex(data.data.sections.length ? 0 : null);
  }, [data]);

  useEffect(() => {
    if (!dirty) return;
    const handler = (event: BeforeUnloadEvent) => {
      event.preventDefault();
      event.returnValue = '';
    };
    window.addEventListener('beforeunload', handler);
    return () => window.removeEventListener('beforeunload', handler);
  }, [dirty]);

  const activeSection = activeIndex !== null ? sections[activeIndex] : null;
  const activeDefinition = activeSection ? registry[activeSection.type] : undefined;

  const sectionDefinitions = useMemo(() => registry, [registry]);

  const updateSectionContent = (content: Record<string, unknown>) => {
    if (activeIndex === null) return;
    setSections((current) =>
      current.map((section, index) => (index === activeIndex ? { ...section, content } : section)),
    );
    setDirty(true);
  };

  const addSection = (type: string) => {
    const definition = sectionDefinitions[type];
    const defaults: Record<string, unknown> = {};

    definition?.fields.forEach((field) => {
      defaults[field.name] =
        field.default ??
        (field.type === 'boolean'
          ? false
          : field.type === 'number'
            ? 3
            : field.type === 'repeater'
              ? []
              : field.type === 'select'
                ? (Object.keys(field.options ?? {})[0] ?? '')
                : field.type === 'cta'
                  ? { label: '', url: '' }
                  : field.type === 'image'
                    ? null
                    : '');
    });

    const next = [...sections, { type, layout: null, is_enabled: true, content: defaults }];
    setSections(next);
    setActiveIndex(next.length - 1);
    setAddingOpen(false);
    setDirty(true);
  };

  const save = async (publishAfter = false) => {
    if (!data) return;

    const setBusy = publishAfter ? setPublishing : setSaving;
    setBusy(true);
    setErrors({});

    try {
      await api.put(`${endpoints.pages}/${data.data.id}`, {
        title: meta.title,
        slug: meta.slug,
        seo_title: meta.seo_title || null,
        seo_description: meta.seo_description || null,
        og_media_id: meta.og_media_id,
      });

      await api.put(`${endpoints.pages}/${data.data.id}/sections`, { sections });

      if (publishAfter) {
        await api.post(`${endpoints.pages}/${data.data.id}/publish`);
        toast.push('Page published.');
      } else {
        toast.push('Draft saved.');
      }

      setDirty(false);
      await reload();
    } catch (error) {
      setErrors(fieldErrors(error));
      toast.push(errorMessage(error), 'error');
    } finally {
      setBusy(false);
    }
  };

  const unpublish = async () => {
    if (!data) return;
    await api.post(`${endpoints.pages}/${data.data.id}/unpublish`);
    toast.push('Page moved back to draft.');
    await reload();
  };

  const restore = async (versionId: number) => {
    if (!data) return;
    await api.post(`${endpoints.pages}/${data.data.id}/versions/${versionId}/restore`);
    toast.push('Version restored into the draft.');
    setVersionsOpen(false);
    await reload();
  };

  if (loading && !data) {
    return (
      <div className="flex h-64 items-center justify-center">
        <Spinner />
      </div>
    );
  }

  if (!data) {
    return (
      <div className="space-y-4">
        <p className="text-sm text-ink/60">Page not found.</p>
        <Link to="/pages" className="text-sm text-accent-soft">
          Back to pages
        </Link>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={() => navigate('/pages')}
            className="border-line rounded-lg border p-2 text-ink/50 transition hover:text-ink"
            aria-label="Back to pages"
          >
            <ArrowLeft size={16} />
          </button>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-xl font-semibold text-ink">{meta.title}</h1>
              <Badge tone={data.data.status === 'published' ? 'success' : 'warning'}>
                {data.data.status}
              </Badge>
              {dirty && <Badge tone="accent">Unsaved</Badge>}
            </div>
            <p className="text-xs text-ink/35">/{meta.slug}</p>
          </div>
        </div>

        <div className="flex flex-wrap items-center gap-2">
          <a
            href={`/admin/pages/${data.data.id}/preview`}
            target="_blank"
            rel="noopener noreferrer"
            className="border-line inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs text-ink/60 transition hover:text-ink"
          >
            <Eye size={14} />
            Preview
          </a>
          <Button
            variant="secondary"
            icon={<History size={14} />}
            onClick={() => setVersionsOpen(true)}
          >
            Versions
          </Button>
          <Button
            variant="secondary"
            icon={<Save size={14} />}
            loading={saving}
            onClick={() => save()}
          >
            Save draft
          </Button>
          <Button icon={<Send size={14} />} loading={publishing} onClick={() => save(true)}>
            Publish
          </Button>
        </div>
      </div>

      <Tabs
        tabs={[
          { id: 'sections', label: 'Sections' },
          { id: 'settings', label: 'Page settings' },
        ]}
        active={tab}
        onChange={setTab}
      />

      {tab === 'sections' ? (
        <div className="grid gap-6 xl:grid-cols-[22rem_1fr]">
          <div className="space-y-4">
            <div className="admin-card p-4">
              <div className="mb-3 flex items-center justify-between">
                <h2 className="text-xs font-semibold tracking-wide text-ink/50 uppercase">
                  Sections ({sections.length})
                </h2>
                <Button
                  size="sm"
                  variant="subtle"
                  icon={<Plus size={13} />}
                  onClick={() => setAddingOpen(true)}
                >
                  Add
                </Button>
              </div>

              {sections.length === 0 ? (
                <p className="py-6 text-center text-xs text-ink/35">
                  No sections yet. Add your first one.
                </p>
              ) : (
                <SectionList
                  sections={sections}
                  registry={registry}
                  activeIndex={activeIndex}
                  onSelect={setActiveIndex}
                  onReorder={(next) => {
                    setSections(next);
                    setDirty(true);
                  }}
                  onToggle={(index) => {
                    setSections((current) =>
                      current.map((section, i) =>
                        i === index ? { ...section, is_enabled: !section.is_enabled } : section,
                      ),
                    );
                    setDirty(true);
                  }}
                  onRemove={(index) => setRemoveIndex(index)}
                />
              )}
            </div>

            {data.data.status === 'published' && (
              <Button variant="ghost" onClick={unpublish} className="w-full">
                Unpublish page
              </Button>
            )}
          </div>

          <div className="admin-card p-5">
            {activeSection && activeDefinition ? (
              <>
                <div className="border-line mb-5 flex items-center justify-between border-b pb-4">
                  <div>
                    <h2 className="text-sm font-semibold text-ink">{activeDefinition.label}</h2>
                    <p className="mt-0.5 text-xs text-ink/35">{activeDefinition.description}</p>
                  </div>
                  <Button
                    size="sm"
                    variant="danger"
                    icon={<Trash2 size={13} />}
                    onClick={() => setRemoveIndex(activeIndex)}
                  >
                    Remove
                  </Button>
                </div>

                <SectionFields
                  fields={activeDefinition.fields}
                  values={activeSection.content ?? {}}
                  onChange={updateSectionContent}
                />
              </>
            ) : (
              <div className="py-20 text-center text-sm text-ink/35">
                Select a section to edit its content.
              </div>
            )}
          </div>
        </div>
      ) : (
        <div className="grid gap-6 lg:grid-cols-2">
          <div className="admin-card space-y-4 p-5">
            <h2 className="text-sm font-semibold text-ink">Page details</h2>

            <Field label="Title" required error={errors.title}>
              <Input
                value={meta.title}
                onChange={(event) => {
                  setMeta({ ...meta, title: event.target.value });
                  setDirty(true);
                }}
              />
            </Field>

            <Field
              label="Slug"
              hint="Changing this automatically creates a 301 redirect"
              error={errors.slug}
            >
              <Input
                value={meta.slug}
                onChange={(event) => {
                  setMeta({ ...meta, slug: event.target.value });
                  setDirty(true);
                }}
              />
            </Field>

            <Field label="Published URL">
              <a
                href={data.data.url}
                target="_blank"
                rel="noopener noreferrer"
                className="flex items-center gap-1.5 text-sm text-accent-soft hover:text-ink"
              >
                {data.data.url}
                <ExternalLink size={13} />
              </a>
            </Field>
          </div>

          <div className="admin-card space-y-4 p-5">
            <h2 className="text-sm font-semibold text-ink">SEO</h2>

            <Field label="SEO title" hint="Defaults to the page title">
              <Input
                value={meta.seo_title}
                onChange={(event) => {
                  setMeta({ ...meta, seo_title: event.target.value });
                  setDirty(true);
                }}
              />
            </Field>

            <Field label="Meta description" hint="Aim for 150–160 characters">
              <textarea
                value={meta.seo_description}
                onChange={(event) => {
                  setMeta({ ...meta, seo_description: event.target.value });
                  setDirty(true);
                }}
                rows={4}
                className="admin-input"
              />
            </Field>

            <ImageField
              label="Social sharing image"
              value={meta.og_media_id}
              onChange={(mediaId) => {
                setMeta({ ...meta, og_media_id: mediaId });
                setDirty(true);
              }}
            />
          </div>
        </div>
      )}

      <Modal open={addingOpen} onClose={() => setAddingOpen(false)} title="Add section" size="lg">
        <AddSectionGrid registry={registry} onAdd={addSection} />
      </Modal>

      <Modal
        open={versionsOpen}
        onClose={() => setVersionsOpen(false)}
        title="Version history"
        size="md"
      >
        <ul className="space-y-2">
          {data.data.versions.length === 0 && (
            <li className="py-8 text-center text-sm text-ink/35">No published versions yet.</li>
          )}
          {data.data.versions.map((version) => (
            <li
              key={version.id}
              className="border-line flex items-center justify-between gap-3 rounded-lg border p-3"
            >
              <div>
                <p className="text-sm font-medium text-ink">Version {version.version_number}</p>
                <p className="text-xs text-ink/35">
                  {version.sections_count} sections ·{' '}
                  {version.published_at
                    ? new Date(version.published_at).toLocaleString('en-GB')
                    : 'not published'}
                </p>
              </div>
              <Button size="sm" variant="secondary" onClick={() => restore(version.id)}>
                Restore
              </Button>
            </li>
          ))}
        </ul>
      </Modal>

      <ConfirmDialog
        open={removeIndex !== null}
        title="Remove section"
        message="This section will be removed from the working draft. Publish again to update the live site."
        onCancel={() => setRemoveIndex(null)}
        onConfirm={() => {
          if (removeIndex === null) return;
          setSections((current) => current.filter((_, index) => index !== removeIndex));
          setActiveIndex(null);
          setRemoveIndex(null);
          setDirty(true);
        }}
      />
    </div>
  );
}
