import { useState } from 'react';
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
import { formatDate } from '@/lib/format';

type Job = {
  id: number;
  title: string;
  slug: string;
  department: string | null;
  location: string | null;
  employment_type: string;
  experience_level: string | null;
  salary_range: string | null;
  description: string | null;
  requirements: string | null;
  status: 'draft' | 'open' | 'closed';
  closing_at: string | null;
  is_featured: boolean;
  applications_count?: number;
  seo_title: string | null;
  seo_description: string | null;
};

const blank = {
  title: '',
  slug: '',
  department: '',
  location: '',
  employment_type: 'full-time',
  experience_level: '',
  salary_range: '',
  description: '',
  requirements: '',
  status: 'draft' as Job['status'],
  closing_at: '',
  is_featured: false,
  seo_title: '',
  seo_description: '',
};

export function CareersPage() {
  const { data, loading, reload } = useApi<{ data: Job[] }>(endpoints.jobs);
  const [open, setOpen] = useState(false);
  const [editing, setEditing] = useState<Job | null>(null);
  const [form, setForm] = useState({ ...blank });
  const [tab, setTab] = useState('details');
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<Job | null>(null);
  const [deleting, setDeleting] = useState(false);
  const toast = useToast();

  const load = async (job?: Job) => {
    setErrors({});
    setTab('details');

    if (job) {
      const response = await api.get<{ data: Job }>(`${endpoints.jobs}/${job.id}`);
      const detail = response.data;
      setEditing(detail);
      setForm({
        title: detail.title,
        slug: detail.slug,
        department: detail.department ?? '',
        location: detail.location ?? '',
        employment_type: detail.employment_type,
        experience_level: detail.experience_level ?? '',
        salary_range: detail.salary_range ?? '',
        description: detail.description ?? '',
        requirements: detail.requirements ?? '',
        status: detail.status,
        closing_at: detail.closing_at ? detail.closing_at.slice(0, 16) : '',
        is_featured: detail.is_featured,
        seo_title: detail.seo_title ?? '',
        seo_description: detail.seo_description ?? '',
      });
    } else {
      setEditing(null);
      setForm({ ...blank });
    }

    setOpen(true);
  };

  const save = async () => {
    setSaving(true);
    setErrors({});

    try {
      const payload = {
        ...form,
        slug: form.slug || undefined,
        closing_at: form.closing_at || null,
        seo_title: form.seo_title || null,
        seo_description: form.seo_description || null,
      };

      if (editing) {
        await api.put(`${endpoints.jobs}/${editing.id}`, payload);
      } else {
        await api.post(endpoints.jobs, payload);
      }

      toast.push(editing ? 'Vacancy updated.' : 'Vacancy created.');
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
      await api.delete(`${endpoints.jobs}/${deleteTarget.id}`);
      toast.push('Vacancy deleted.');
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
        title="Jobs"
        description="Vacancies, closing dates and application counts."
        actions={
          <Button icon={<Plus size={15} />} onClick={() => void load()}>
            New vacancy
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
                <th>Role</th>
                <th className="hidden sm:table-cell">Applications</th>
                <th className="hidden md:table-cell">Closes</th>
                <th>Status</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((job) => (
                  <tr key={job.id}>
                    <td>
                      <p className="font-medium text-ink">{job.title}</p>
                      <p className="text-xs text-ink/35">
                        {[job.department, job.location].filter(Boolean).join(' · ') ||
                          '/careers/' + job.slug}
                      </p>
                    </td>
                    <td className="hidden text-ink/50 sm:table-cell">
                      {job.applications_count ?? 0}
                    </td>
                    <td className="hidden text-xs text-ink/40 md:table-cell">
                      {formatDate(job.closing_at)}
                    </td>
                    <td>
                      <Badge
                        tone={
                          job.status === 'open'
                            ? 'success'
                            : job.status === 'closed'
                              ? 'neutral'
                              : 'warning'
                        }
                      >
                        {job.status}
                      </Badge>
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <button
                          type="button"
                          onClick={() => void load(job)}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                          aria-label="Edit"
                        >
                          <Pencil size={15} />
                        </button>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(job)}
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
                  <td colSpan={5} className="py-14 text-center text-sm text-ink/35">
                    No vacancies yet.
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
        title={editing ? `Edit: ${editing.title}` : 'New vacancy'}
        size="lg"
        footer={
          <>
            <Button variant="ghost" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button onClick={save} loading={saving} disabled={!form.title}>
              {editing ? 'Save changes' : 'Create vacancy'}
            </Button>
          </>
        }
      >
        <Tabs
          tabs={[
            { id: 'details', label: 'Details' },
            { id: 'description', label: 'Description' },
            { id: 'seo', label: 'SEO' },
          ]}
          active={tab}
          onChange={setTab}
        />

        <div className="mt-5 space-y-4">
          {tab === 'details' && (
            <>
              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Job title" required error={errors.title}>
                  <Input
                    value={form.title}
                    onChange={(event) => setForm({ ...form, title: event.target.value })}
                  />
                </Field>
                <Field label="Slug" hint="Leave blank to generate">
                  <Input
                    value={form.slug}
                    onChange={(event) => setForm({ ...form, slug: event.target.value })}
                  />
                </Field>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Department">
                  <Input
                    value={form.department}
                    onChange={(event) => setForm({ ...form, department: event.target.value })}
                  />
                </Field>
                <Field label="Location">
                  <Input
                    value={form.location}
                    onChange={(event) => setForm({ ...form, location: event.target.value })}
                    placeholder="Swindon / Hybrid"
                  />
                </Field>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Employment type">
                  <select
                    value={form.employment_type}
                    onChange={(event) => setForm({ ...form, employment_type: event.target.value })}
                    className="admin-input"
                  >
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                    <option value="temporary">Temporary</option>
                    <option value="internship">Internship</option>
                  </select>
                </Field>
                <Field label="Experience level">
                  <Input
                    value={form.experience_level}
                    onChange={(event) => setForm({ ...form, experience_level: event.target.value })}
                    placeholder="Senior"
                  />
                </Field>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Salary range">
                  <Input
                    value={form.salary_range}
                    onChange={(event) => setForm({ ...form, salary_range: event.target.value })}
                  />
                </Field>
                <Field label="Closing date">
                  <input
                    type="datetime-local"
                    value={form.closing_at}
                    onChange={(event) => setForm({ ...form, closing_at: event.target.value })}
                    className="admin-input"
                  />
                </Field>
              </div>

              <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Status">
                  <select
                    value={form.status}
                    onChange={(event) =>
                      setForm({ ...form, status: event.target.value as Job['status'] })
                    }
                    className="admin-input"
                  >
                    <option value="draft">Draft</option>
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                  </select>
                </Field>
                <div className="flex items-end pb-2">
                  <Toggle
                    checked={form.is_featured}
                    onChange={(value) => setForm({ ...form, is_featured: value })}
                    label="Feature this role"
                  />
                </div>
              </div>
            </>
          )}

          {tab === 'description' && (
            <>
              <div>
                <span className="admin-label">Role description</span>
                <RichTextEditor
                  value={form.description}
                  onChange={(html) => setForm({ ...form, description: html })}
                  placeholder="About the role, the team and the work…"
                />
              </div>

              <div>
                <span className="admin-label">Requirements</span>
                <RichTextEditor
                  value={form.requirements}
                  onChange={(html) => setForm({ ...form, requirements: html })}
                  placeholder="Skills and experience we are looking for…"
                />
              </div>
            </>
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
            </>
          )}
        </div>
      </Modal>

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete vacancy"
        message={`Delete “${deleteTarget?.title}” and all of its applications? This cannot be undone.`}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
