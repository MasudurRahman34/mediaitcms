import { useState } from 'react';
import { ArrowDown, ArrowUp, ExternalLink, ImagePlus, Pencil, Plus, Trash2, X } from 'lucide-react';
import { api, endpoints } from '../lib/api';
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
  Toggle,
  useToast,
} from '../components/ui';
import { RichTextEditor } from '../components/RichTextEditor';
import { ImageField, MediaPicker } from '../components/MediaPicker';

type Benefit = { title: string; description: string };
type Feature = {
  title: string;
  description: string;
  media_id: number | null;
  media_url?: string | null;
};
type Screenshot = { id: number; media_id: number; url: string | null; caption: string | null };
type Technology = { id: number; name: string };

type Solution = {
  id: number;
  title: string;
  slug: string;
  intro: string | null;
  description: string | null;
  cover_media_id: number | null;
  live_url: string | null;
  business_benefits: Benefit[];
  status: 'draft' | 'published' | 'archived';
  is_featured: boolean;
  seo_title: string | null;
  seo_description: string | null;
  og_media_id: number | null;
  features: Feature[];
  screenshots: Screenshot[];
  technologies: Technology[];
};

export function SolutionsPage() {
  const { data, loading, reload } = useApi<{ data: Solution[] }>(endpoints.solutions);
  const { data: techData, reload: reloadTech } = useApi<{ data: Technology[] }>(
    endpoints.technologies,
  );
  const [open, setOpen] = useState(false);
  const [editingId, setEditingId] = useState<number | null>(null);
  const [form, setForm] = useState({
    title: '',
    slug: '',
    intro: '',
    description: '',
    cover_media_id: null as number | null,
    live_url: '',
    business_benefits: [] as Benefit[],
    status: 'draft' as Solution['status'],
    is_featured: false,
    seo_title: '',
    seo_description: '',
    og_media_id: null as number | null,
    features: [] as Feature[],
    screenshots: [] as Screenshot[],
  });
  const [technologyIds, setTechnologyIds] = useState<number[]>([]);
  const [newTech, setNewTech] = useState('');
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);
  const [tab, setTab] = useState('content');
  const [pickerOpen, setPickerOpen] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Solution | null>(null);
  const [deleting, setDeleting] = useState(false);
  const toast = useToast();

  const reset = () => {
    setEditingId(null);
    setErrors({});
    setTab('content');
    setTechnologyIds([]);
    setForm({
      title: '',
      slug: '',
      intro: '',
      description: '',
      cover_media_id: null,
      live_url: '',
      business_benefits: [],
      status: 'draft',
      is_featured: false,
      seo_title: '',
      seo_description: '',
      og_media_id: null,
      features: [],
      screenshots: [],
    });
  };

  const loadDetail = async (id: number) => {
    const response = await api.get<{ data: Solution }>(`${endpoints.solutions}/${id}`);
    const solution = response.data;

    setEditingId(solution.id);
    setForm({
      title: solution.title,
      slug: solution.slug,
      intro: solution.intro ?? '',
      description: solution.description ?? '',
      cover_media_id: solution.cover_media_id,
      live_url: solution.live_url ?? '',
      business_benefits: solution.business_benefits ?? [],
      status: solution.status,
      is_featured: solution.is_featured,
      seo_title: solution.seo_title ?? '',
      seo_description: solution.seo_description ?? '',
      og_media_id: solution.og_media_id,
      features: solution.features ?? [],
      screenshots: solution.screenshots ?? [],
    });
    setTechnologyIds(solution.technologies?.map((tech) => tech.id) ?? []);
    setTab('content');
    setOpen(true);
  };

  const save = async () => {
    setSaving(true);
    setErrors({});

    try {
      const payload = {
        ...form,
        slug: form.slug || undefined,
        live_url: form.live_url || null,
        seo_title: form.seo_title || null,
        seo_description: form.seo_description || null,
        technology_ids: technologyIds,
        features: form.features.map((feature) => ({
          title: feature.title,
          description: feature.description,
          media_id: feature.media_id,
        })),
      };

      if (editingId) {
        const response = await api.put<{ data: Solution }>(
          `${endpoints.solutions}/${editingId}`,
          payload,
        );
        setForm((current) => ({
          ...current,
          screenshots: response.data.screenshots ?? current.screenshots,
        }));
        toast.push('Solution updated.');
      } else {
        const response = await api.post<{ data: Solution }>(endpoints.solutions, payload);
        setEditingId(response.data.id);
        toast.push('Solution created. You can now add screenshots.');
        await loadDetail(response.data.id);
      }

      await reload();
    } catch (error) {
      setErrors(fieldErrors(error));
      toast.push(errorMessage(error), 'error');
    } finally {
      setSaving(false);
    }
  };

  const addScreenshot = async (mediaId: number) => {
    if (!editingId) return;

    try {
      const response = await api.post<{ data: Screenshot }>(
        `${endpoints.solutions}/${editingId}/screenshots`,
        {
          media_id: mediaId,
        },
      );
      setForm((current) => ({ ...current, screenshots: [...current.screenshots, response.data] }));
      await reload();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    }
  };

  const persistScreenshotOrder = async (screenshots: Screenshot[]) => {
    if (!editingId) return;

    setForm((current) => ({ ...current, screenshots }));

    try {
      await api.put(`${endpoints.solutions}/${editingId}/screenshots`, {
        screenshots: screenshots.map((shot) => ({ id: shot.id, caption: shot.caption })),
      });
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    }
  };

  const addTechnology = async () => {
    if (!newTech.trim()) return;

    try {
      const response = await api.post<{ data: Technology }>(endpoints.technologies, {
        name: newTech.trim(),
      });
      setTechnologyIds((current) => [...current, response.data.id]);
      setNewTech('');
      await reloadTech();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    }
  };

  const remove = async () => {
    if (!deleteTarget) return;
    setDeleting(true);
    try {
      await api.delete(`${endpoints.solutions}/${deleteTarget.id}`);
      toast.push('Solution deleted.');
      setDeleteTarget(null);
      await reload();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    } finally {
      setDeleting(false);
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Solutions"
        description="Case studies for the software and AI products you have delivered."
        actions={
          <Button
            icon={<Plus size={15} />}
            onClick={() => {
              reset();
              setOpen(true);
            }}
          >
            New solution
          </Button>
        }
      />

      <div className="admin-card overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-16">
            <Spinner />
          </div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>Solution</th>
                <th className="hidden sm:table-cell">Media</th>
                <th>Status</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((solution) => (
                  <tr key={solution.id}>
                    <td>
                      <p className="font-medium text-ink">{solution.title}</p>
                      <p className="text-xs text-ink/35">/solutions/{solution.slug}</p>
                    </td>
                    <td className="hidden text-xs text-ink/45 sm:table-cell">
                      {solution.features?.length ?? 0} features ·{' '}
                      {solution.screenshots?.length ?? 0} shots
                    </td>
                    <td>
                      <Badge tone={solution.status === 'published' ? 'success' : 'warning'}>
                        {solution.status}
                      </Badge>
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <button
                          type="button"
                          onClick={() => loadDetail(solution.id)}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                          aria-label="Edit"
                        >
                          <Pencil size={15} />
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(solution)}
                          className="rounded p-1.5 text-ink/35 transition hover:text-red-300"
                          aria-label="Delete"
                        >
                          <Trash2 size={15} />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={4} className="py-14 text-center text-sm text-ink/35">
                    No solutions yet.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      <Modal
        open={open}
        onClose={() => setOpen(false)}
        title={editingId ? 'Edit solution' : 'New solution'}
        size="xl"
        footer={
          <>
            <Button variant="ghost" onClick={() => setOpen(false)}>
              Close
            </Button>
            <Button onClick={save} loading={saving} disabled={!form.title}>
              {editingId ? 'Save changes' : 'Create solution'}
            </Button>
          </>
        }
      >
        <Tabs
          tabs={[
            { id: 'content', label: 'Content' },
            { id: 'features', label: 'Features' },
            { id: 'screenshots', label: 'Screenshots' },
            { id: 'seo', label: 'SEO' },
          ]}
          active={tab}
          onChange={setTab}
        />

        <div className="mt-5 space-y-4">
          {tab === 'content' && (
            <>
              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Title" required error={errors.title}>
                  <Input
                    value={form.title}
                    onChange={(event) => setForm({ ...form, title: event.target.value })}
                  />
                </Field>
                <Field label="Slug" hint="Leave blank to generate" error={errors.slug}>
                  <Input
                    value={form.slug}
                    onChange={(event) => setForm({ ...form, slug: event.target.value })}
                  />
                </Field>
              </div>

              <Field label="Introduction" hint="One or two sentences for cards and the hero">
                <textarea
                  value={form.intro}
                  onChange={(event) => setForm({ ...form, intro: event.target.value })}
                  rows={3}
                  className="admin-input"
                />
              </Field>

              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Live URL" error={errors.live_url}>
                  <Input
                    value={form.live_url}
                    onChange={(event) => setForm({ ...form, live_url: event.target.value })}
                    placeholder="https://"
                  />
                </Field>
                <Field label="Status">
                  <select
                    value={form.status}
                    onChange={(event) =>
                      setForm({ ...form, status: event.target.value as Solution['status'] })
                    }
                    className="admin-input"
                  >
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                    <option value="archived">Archived</option>
                  </select>
                </Field>
              </div>

              <div>
                <span className="admin-label">Detailed description</span>
                <RichTextEditor
                  value={form.description}
                  onChange={(html) => setForm({ ...form, description: html })}
                />
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <ImageField
                  label="Cover image"
                  value={form.cover_media_id}
                  onChange={(mediaId) => setForm({ ...form, cover_media_id: mediaId })}
                />
                <div className="flex items-end pb-2">
                  <Toggle
                    checked={form.is_featured}
                    onChange={(value) => setForm({ ...form, is_featured: value })}
                    label="Feature on the home page"
                  />
                </div>
              </div>

              <div className="border-line rounded-lg border p-3">
                <div className="mb-3 flex items-center justify-between">
                  <p className="text-xs font-semibold tracking-wide text-ink/50 uppercase">
                    Business benefits
                  </p>
                  <Button
                    size="sm"
                    variant="subtle"
                    icon={<Plus size={13} />}
                    onClick={() =>
                      setForm({
                        ...form,
                        business_benefits: [
                          ...form.business_benefits,
                          { title: '', description: '' },
                        ],
                      })
                    }
                  >
                    Add
                  </Button>
                </div>
                <div className="space-y-3">
                  {form.business_benefits.map((benefit, index) => (
                    <div key={index} className="border-line rounded-lg border bg-near-black/30 p-3">
                      <div className="mb-2 flex items-center justify-between">
                        <span className="text-[11px] text-ink/35">Benefit {index + 1}</span>
                        <button
                          type="button"
                          onClick={() =>
                            setForm({
                              ...form,
                              business_benefits: form.business_benefits.filter(
                                (_, i) => i !== index,
                              ),
                            })
                          }
                          className="text-ink/30 hover:text-red-300"
                          aria-label="Remove benefit"
                        >
                          <Trash2 size={13} />
                        </button>
                      </div>
                      <Input
                        value={benefit.title}
                        placeholder="Title"
                        onChange={(event) =>
                          setForm({
                            ...form,
                            business_benefits: form.business_benefits.map((item, i) =>
                              i === index ? { ...item, title: event.target.value } : item,
                            ),
                          })
                        }
                      />
                      <textarea
                        value={benefit.description}
                        rows={2}
                        placeholder="Description"
                        onChange={(event) =>
                          setForm({
                            ...form,
                            business_benefits: form.business_benefits.map((item, i) =>
                              i === index ? { ...item, description: event.target.value } : item,
                            ),
                          })
                        }
                        className="admin-input mt-2"
                      />
                    </div>
                  ))}
                </div>
              </div>

              <div className="border-line rounded-lg border p-3">
                <p className="mb-3 text-xs font-semibold tracking-wide text-ink/50 uppercase">
                  Technologies
                </p>
                <div className="mb-3 flex flex-wrap gap-2">
                  {techData?.data.map((tech) => {
                    const selected = technologyIds.includes(tech.id);
                    return (
                      <button
                        key={tech.id}
                        type="button"
                        onClick={() =>
                          setTechnologyIds((current) =>
                            selected
                              ? current.filter((id) => id !== tech.id)
                              : [...current, tech.id],
                          )
                        }
                        className={`rounded-full border px-3 py-1.5 text-xs transition ${
                          selected
                            ? 'border-accent/50 bg-accent/15 text-accent-soft'
                            : 'border-line bg-white/5 text-ink/55 hover:text-ink'
                        }`}
                      >
                        {tech.name}
                      </button>
                    );
                  })}
                </div>
                <div className="flex gap-2">
                  <Input
                    value={newTech}
                    onChange={(event) => setNewTech(event.target.value)}
                    placeholder="Add technology"
                  />
                  <Button size="sm" variant="secondary" onClick={addTechnology}>
                    Add
                  </Button>
                </div>
              </div>
            </>
          )}

          {tab === 'features' && (
            <div className="border-line rounded-lg border p-3">
              <div className="mb-3 flex items-center justify-between">
                <p className="text-xs font-semibold tracking-wide text-ink/50 uppercase">
                  Key features
                </p>
                <Button
                  size="sm"
                  variant="subtle"
                  icon={<Plus size={13} />}
                  onClick={() =>
                    setForm({
                      ...form,
                      features: [...form.features, { title: '', description: '', media_id: null }],
                    })
                  }
                >
                  Add feature
                </Button>
              </div>

              <div className="space-y-4">
                {form.features.length === 0 && (
                  <p className="py-6 text-center text-xs text-ink/30">
                    Add features to explain what the product does.
                  </p>
                )}

                {form.features.map((feature, index) => (
                  <div key={index} className="border-line rounded-lg border bg-near-black/30 p-3">
                    <div className="mb-2 flex items-center justify-between">
                      <span className="text-[11px] text-ink/35">Feature {index + 1}</span>
                      <button
                        type="button"
                        onClick={() =>
                          setForm({
                            ...form,
                            features: form.features.filter((_, i) => i !== index),
                          })
                        }
                        className="text-ink/30 hover:text-red-300"
                        aria-label="Remove feature"
                      >
                        <Trash2 size={13} />
                      </button>
                    </div>

                    <Input
                      value={feature.title}
                      placeholder="Feature title"
                      onChange={(event) =>
                        setForm({
                          ...form,
                          features: form.features.map((item, i) =>
                            i === index ? { ...item, title: event.target.value } : item,
                          ),
                        })
                      }
                    />
                    <div className="mt-2">
                      <RichTextEditor
                        value={feature.description}
                        placeholder="How this feature helps…"
                        onChange={(html) =>
                          setForm({
                            ...form,
                            features: form.features.map((item, i) =>
                              i === index ? { ...item, description: html } : item,
                            ),
                          })
                        }
                      />
                    </div>
                    <div className="mt-2 max-w-[14rem]">
                      <ImageField
                        label="Feature screenshot"
                        value={feature.media_id}
                        onChange={(mediaId) =>
                          setForm({
                            ...form,
                            features: form.features.map((item, i) =>
                              i === index ? { ...item, media_id: mediaId } : item,
                            ),
                          })
                        }
                      />
                    </div>
                  </div>
                ))}
              </div>
            </div>
          )}

          {tab === 'screenshots' && (
            <div>
              <div className="mb-4 flex items-center justify-between">
                <p className="text-xs text-ink/40">
                  Drag-free ordering with the arrows. Screenshots feed the public lightbox gallery.
                </p>
                <Button
                  size="sm"
                  variant="secondary"
                  icon={<ImagePlus size={14} />}
                  onClick={() => setPickerOpen(true)}
                  disabled={!editingId}
                >
                  Add screenshot
                </Button>
              </div>

              {!editingId && (
                <p className="mb-4 rounded-lg border border-amber-400/25 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                  Save the solution first, then add screenshots.
                </p>
              )}

              {form.screenshots.length === 0 ? (
                <p className="py-10 text-center text-sm text-ink/30">No screenshots yet.</p>
              ) : (
                <ul className="space-y-3">
                  {form.screenshots.map((shot, index) => (
                    <li
                      key={shot.id}
                      className="border-line flex items-center gap-3 rounded-lg border p-3"
                    >
                      {shot.url && (
                        <img
                          src={shot.url}
                          alt=""
                          className="size-16 rounded object-cover"
                          loading="lazy"
                        />
                      )}
                      <input
                        value={shot.caption ?? ''}
                        placeholder="Caption"
                        onChange={(event) =>
                          setForm({
                            ...form,
                            screenshots: form.screenshots.map((item, i) =>
                              i === index ? { ...item, caption: event.target.value } : item,
                            ),
                          })
                        }
                        onBlur={() => persistScreenshotOrder(form.screenshots)}
                        className="admin-input flex-1"
                      />
                      <div className="flex items-center gap-1">
                        <button
                          type="button"
                          disabled={index === 0}
                          onClick={() => {
                            const next = [...form.screenshots];
                            [next[index - 1], next[index]] = [next[index], next[index - 1]];
                            void persistScreenshotOrder(next);
                          }}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink disabled:opacity-25"
                          aria-label="Move up"
                        >
                          <ArrowUp size={14} />
                        </button>
                        <button
                          type="button"
                          disabled={index === form.screenshots.length - 1}
                          onClick={() => {
                            const next = [...form.screenshots];
                            [next[index + 1], next[index]] = [next[index], next[index + 1]];
                            void persistScreenshotOrder(next);
                          }}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink disabled:opacity-25"
                          aria-label="Move down"
                        >
                          <ArrowDown size={14} />
                        </button>
                        <button
                          type="button"
                          onClick={() =>
                            setForm({
                              ...form,
                              screenshots: form.screenshots.filter((item) => item.id !== shot.id),
                            })
                          }
                          className="rounded p-1.5 text-ink/35 transition hover:text-red-300"
                          aria-label="Remove screenshot"
                        >
                          <X size={14} />
                        </button>
                      </div>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          )}

          {tab === 'seo' && (
            <>
              <Field label="SEO title">
                <Input
                  value={form.seo_title}
                  onChange={(event) => setForm({ ...form, seo_title: event.target.value })}
                />
              </Field>
              <Field label="Meta description">
                <textarea
                  value={form.seo_description}
                  onChange={(event) => setForm({ ...form, seo_description: event.target.value })}
                  rows={4}
                  className="admin-input"
                />
              </Field>
              <ImageField
                label="Social sharing image"
                value={form.og_media_id}
                onChange={(mediaId) => setForm({ ...form, og_media_id: mediaId })}
              />
            </>
          )}
        </div>
      </Modal>

      <MediaPicker
        open={pickerOpen}
        onClose={() => setPickerOpen(false)}
        onSelect={(media) => void addScreenshot(media.id)}
      />

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete solution"
        message={`Delete “${deleteTarget?.title}”? This cannot be undone.`}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
