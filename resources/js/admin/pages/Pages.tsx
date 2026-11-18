import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { ExternalLink, Eye, Plus } from 'lucide-react';
import { api, endpoints, type PageRecord } from '../lib/api';
import { errorMessage, fieldErrors, useApi } from '../lib/useApi';
import {
  Badge,
  Button,
  Field,
  Input,
  Modal,
  PageHeader,
  Spinner,
  useToast,
} from '../components/ui';
import { slugify, relativeTime } from '@/lib/format';

export function PagesPage() {
  const { data, loading, reload } = useApi<{ data: PageRecord[] }>(endpoints.pages);
  const [creating, setCreating] = useState(false);
  const [saving, setSaving] = useState(false);
  const [form, setForm] = useState({ title: '', slug: '', template: 'standard' });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const navigate = useNavigate();
  const toast = useToast();

  const submit = async () => {
    setSaving(true);
    setErrors({});
    try {
      const response = await api.post<{ data: { id: number } }>(endpoints.pages, {
        title: form.title,
        slug: form.slug || slugify(form.title),
        template: form.template,
      });
      toast.push('Page created.');
      navigate(`/pages/${response.data.id}`);
    } catch (error) {
      setErrors(fieldErrors(error));
      toast.push(errorMessage(error), 'error');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Pages"
        description="Manage the site structure and build pages section by section."
        actions={
          <Button icon={<Plus size={15} />} onClick={() => setCreating(true)}>
            New page
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
                <th>Page</th>
                <th className="hidden sm:table-cell">Sections</th>
                <th>Status</th>
                <th className="hidden md:table-cell">Updated</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.map((page) => (
                <tr key={page.id}>
                  <td>
                    <Link
                      to={`/pages/${page.id}`}
                      className="font-medium text-ink hover:text-accent-soft"
                    >
                      {page.title}
                    </Link>
                    <p className="text-xs text-ink/35">/{page.slug}</p>
                  </td>
                  <td className="hidden text-ink/50 sm:table-cell">{page.sections_count}</td>
                  <td>
                    <Badge tone={page.status === 'published' ? 'success' : 'warning'}>
                      {page.status}
                    </Badge>
                  </td>
                  <td className="hidden text-xs text-ink/40 md:table-cell">
                    {relativeTime(page.updated_at)}
                  </td>
                  <td>
                    <div className="flex items-center justify-end gap-1">
                      <a
                        href={page.url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                        title="View page"
                      >
                        <ExternalLink size={15} />
                      </a>
                      <Link
                        to={`/pages/${page.id}`}
                        className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                        title="Edit page"
                      >
                        <Eye size={15} />
                      </Link>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      <Modal
        open={creating}
        onClose={() => setCreating(false)}
        title="Create page"
        footer={
          <>
            <Button variant="ghost" onClick={() => setCreating(false)}>
              Cancel
            </Button>
            <Button onClick={submit} loading={saving} disabled={!form.title}>
              Create
            </Button>
          </>
        }
      >
        <div className="space-y-4">
          <Field label="Title" required error={errors.title}>
            <Input
              value={form.title}
              onChange={(event) => setForm({ ...form, title: event.target.value })}
              placeholder="e.g. Pricing"
              autoFocus
            />
          </Field>
          <Field label="Slug" hint="Leave blank to generate from the title" error={errors.slug}>
            <Input
              value={form.slug}
              onChange={(event) => setForm({ ...form, slug: event.target.value })}
              placeholder="pricing"
            />
          </Field>
          <Field label="Template">
            <select
              value={form.template}
              onChange={(event) => setForm({ ...form, template: event.target.value })}
              className="admin-input"
            >
              <option value="standard">Standard</option>
              <option value="home">Home</option>
              <option value="about">About</option>
            </select>
          </Field>
        </div>
      </Modal>
    </div>
  );
}
