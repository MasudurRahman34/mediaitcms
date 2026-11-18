import { useEffect, useState } from 'react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
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
import { ImageField } from '../components/MediaPicker';

type Benefit = { title: string; description: string };
type Faq = { question: string; answer: string };

type Service = {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  description: string | null;
  icon: string | null;
  cover_media_id: number | null;
  cover_url: string | null;
  benefits: Benefit[];
  faqs: Faq[];
  status: 'draft' | 'published' | 'archived';
  is_featured: boolean;
  sort_order: number;
  seo_title: string | null;
  seo_description: string | null;
  og_media_id: number | null;
  updated_at: string;
};

const blank = {
  title: '',
  slug: '',
  excerpt: '',
  description: '',
  icon: 'code',
  cover_media_id: null as number | null,
  benefits: [] as Benefit[],
  faqs: [] as Faq[],
  status: 'draft' as Service['status'],
  is_featured: false,
  seo_title: '',
  seo_description: '',
  og_media_id: null as number | null,
};

export function ServicesPage() {
  const { data, loading, reload } = useApi<{ data: Service[] }>(endpoints.services);
  const [editing, setEditing] = useState<Service | null>(null);
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState({ ...blank });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);
  const [tab, setTab] = useState('content');
  const [deleteTarget, setDeleteTarget] = useState<Service | null>(null);
  const [deleting, setDeleting] = useState(false);
  const toast = useToast();

  const openEditor = (service?: Service) => {
    setEditing(service ?? null);
    setErrors({});
    setTab('content');
    setForm(
      service
        ? {
            title: service.title,
            slug: service.slug,
            excerpt: service.excerpt ?? '',
            description: service.description ?? '',
            icon: service.icon ?? 'code',
            cover_media_id: service.cover_media_id,
            benefits: service.benefits ?? [],
            faqs: service.faqs ?? [],
            status: service.status,
            is_featured: service.is_featured,
            seo_title: service.seo_title ?? '',
            seo_description: service.seo_description ?? '',
            og_media_id: service.og_media_id,
          }
        : { ...blank },
    );
    setOpen(true);
  };

  const save = async () => {
    setSaving(true);
    setErrors({});

    try {
      const payload = {
        ...form,
        slug: form.slug || undefined,
        seo_title: form.seo_title || null,
        seo_description: form.seo_description || null,
      };

      if (editing) {
        await api.put(`${endpoints.services}/${editing.id}`, payload);
      } else {
        await api.post(endpoints.services, payload);
      }

      toast.push(editing ? 'Service updated.' : 'Service created.');
      setOpen(false);
      await reload();
    } catch (error) {
      setErrors(fieldErrors(error));
      toast.push(errorMessage(error), 'error');
    } finally {
      setSaving(false);
    }
  };

  const remove = async () => {
    if (!deleteTarget) return;
    setDeleting(true);
    try {
      await api.delete(`${endpoints.services}/${deleteTarget.id}`);
      toast.push('Service deleted.');
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
        title="Services"
        description="The service offerings shown on the website."
        actions={
          <Button icon={<Plus size={15} />} onClick={() => openEditor()}>
            New service
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
                <th>Service</th>
                <th className="hidden sm:table-cell">Featured</th>
                <th>Status</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((service) => (
                  <tr key={service.id}>
                    <td>
                      <p className="font-medium text-ink">{service.title}</p>
                      <p className="text-xs text-ink/35">/services/{service.slug}</p>
                    </td>
                    <td className="hidden sm:table-cell">
                      {service.is_featured ? (
                        <Badge tone="accent">Featured</Badge>
                      ) : (
                        <span className="text-ink/25">—</span>
                      )}
                    </td>
                    <td>
                      <Badge
                        tone={
                          service.status === 'published'
                            ? 'success'
                            : service.status === 'archived'
                              ? 'neutral'
                              : 'warning'
                        }
                      >
                        {service.status}
                      </Badge>
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <button
                          type="button"
                          onClick={() => openEditor(service)}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                          aria-label="Edit"
                        >
                          <Pencil size={15} />
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(service)}
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
                    No services yet.
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
        title={editing ? `Edit: ${editing.title}` : 'New service'}
        size="lg"
        footer={
          <>
            <Button variant="ghost" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button onClick={save} loading={saving} disabled={!form.title}>
              {editing ? 'Save changes' : 'Create service'}
            </Button>
          </>
        }
      >
        <Tabs
          tabs={[
            { id: 'content', label: 'Content' },
            { id: 'faqs', label: 'Benefits & FAQs' },
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

              <Field label="Short summary" hint="Shown on cards" error={errors.excerpt}>
                <textarea
                  value={form.excerpt}
                  onChange={(event) => setForm({ ...form, excerpt: event.target.value })}
                  rows={3}
                  className="admin-input"
                />
              </Field>

              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Icon" hint="Lucide icon name (code, sparkles, cloud…)">
                  <Input
                    value={form.icon}
                    onChange={(event) => setForm({ ...form, icon: event.target.value })}
                  />
                </Field>
                <Field label="Status">
                  <select
                    value={form.status}
                    onChange={(event) =>
                      setForm({ ...form, status: event.target.value as Service['status'] })
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
                <span className="admin-label">Description</span>
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
            </>
          )}

          {tab === 'faqs' && (
            <>
              <div className="border-line rounded-lg border p-3">
                <div className="mb-3 flex items-center justify-between">
                  <p className="text-xs font-semibold tracking-wide text-ink/50 uppercase">
                    Benefits
                  </p>
                  <Button
                    size="sm"
                    variant="subtle"
                    icon={<Plus size={13} />}
                    onClick={() =>
                      setForm({
                        ...form,
                        benefits: [...form.benefits, { title: '', description: '' }],
                      })
                    }
                  >
                    Add
                  </Button>
                </div>
                <div className="space-y-3">
                  {form.benefits.length === 0 && (
                    <p className="text-xs text-ink/30">No benefits yet.</p>
                  )}
                  {form.benefits.map((benefit, index) => (
                    <div key={index} className="border-line rounded-lg border bg-near-black/30 p-3">
                      <div className="mb-2 flex items-center justify-between">
                        <span className="text-[11px] text-ink/35">Benefit {index + 1}</span>
                        <button
                          type="button"
                          onClick={() =>
                            setForm({
                              ...form,
                              benefits: form.benefits.filter((_, i) => i !== index),
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
                            benefits: form.benefits.map((item, i) =>
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
                            benefits: form.benefits.map((item, i) =>
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
                <div className="mb-3 flex items-center justify-between">
                  <p className="text-xs font-semibold tracking-wide text-ink/50 uppercase">FAQs</p>
                  <Button
                    size="sm"
                    variant="subtle"
                    icon={<Plus size={13} />}
                    onClick={() =>
                      setForm({ ...form, faqs: [...form.faqs, { question: '', answer: '' }] })
                    }
                  >
                    Add
                  </Button>
                </div>
                <div className="space-y-3">
                  {form.faqs.length === 0 && <p className="text-xs text-ink/30">No FAQs yet.</p>}
                  {form.faqs.map((faq, index) => (
                    <div key={index} className="border-line rounded-lg border bg-near-black/30 p-3">
                      <div className="mb-2 flex items-center justify-between">
                        <span className="text-[11px] text-ink/35">Question {index + 1}</span>
                        <button
                          type="button"
                          onClick={() =>
                            setForm({ ...form, faqs: form.faqs.filter((_, i) => i !== index) })
                          }
                          className="text-ink/30 hover:text-red-300"
                          aria-label="Remove question"
                        >
                          <Trash2 size={13} />
                        </button>
                      </div>
                      <Input
                        value={faq.question}
                        placeholder="Question"
                        onChange={(event) =>
                          setForm({
                            ...form,
                            faqs: form.faqs.map((item, i) =>
                              i === index ? { ...item, question: event.target.value } : item,
                            ),
                          })
                        }
                      />
                      <div className="mt-2">
                        <RichTextEditor
                          value={faq.answer}
                          onChange={(html) =>
                            setForm({
                              ...form,
                              faqs: form.faqs.map((item, i) =>
                                i === index ? { ...item, answer: html } : item,
                              ),
                            })
                          }
                          placeholder="Answer…"
                        />
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </>
          )}

          {tab === 'seo' && (
            <>
              <Field label="SEO title" error={errors.seo_title}>
                <Input
                  value={form.seo_title}
                  onChange={(event) => setForm({ ...form, seo_title: event.target.value })}
                />
              </Field>
              <Field label="Meta description" error={errors.seo_description}>
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

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete service"
        message={`Delete “${deleteTarget?.title}”? This cannot be undone.`}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
