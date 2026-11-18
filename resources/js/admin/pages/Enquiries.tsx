import { useState } from 'react';
import { Mail, Search, Trash2 } from 'lucide-react';
import { api, endpoints, type Paginated } from '../lib/api';
import { errorMessage, useApi } from '../lib/useApi';
import {
  Badge,
  Button,
  ConfirmDialog,
  Input,
  Modal,
  PageHeader,
  Spinner,
  useToast,
} from '../components/ui';
import { formatDate } from '@/lib/format';

type Enquiry = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  company: string | null;
  subject: string | null;
  message: string;
  status: 'new' | 'read' | 'replied' | 'archived';
  created_at: string;
};

const statuses: Enquiry['status'][] = ['new', 'read', 'replied', 'archived'];

export function EnquiriesPage() {
  const toast = useToast();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [detail, setDetail] = useState<Enquiry | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Enquiry | null>(null);
  const [deleting, setDeleting] = useState(false);

  const { data, loading, reload } = useApi<Paginated<Enquiry>>(
    endpoints.enquiries,
    { q: search || undefined, status: status || undefined, page },
    [page],
  );

  const update = async (enquiry: Enquiry, next: Enquiry['status']) => {
    try {
      await api.patch(`${endpoints.enquiries}/${enquiry.id}`, { status: next });
      setDetail((current) =>
        current && current.id === enquiry.id ? { ...current, status: next } : current,
      );
      await reload();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    }
  };

  const remove = async () => {
    if (!deleteTarget) return;
    setDeleting(true);
    try {
      await api.delete(`${endpoints.enquiries}/${deleteTarget.id}`);
      toast.push('Enquiry deleted.');
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
        title="Enquiries"
        description="Messages submitted through the website contact form."
      />

      <div className="flex flex-col gap-3 sm:flex-row">
        <div className="relative flex-1">
          <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-ink/35">
            <Search size={16} />
          </span>
          <Input
            value={search}
            onChange={(event) => {
              setSearch(event.target.value);
              setPage(1);
            }}
            placeholder="Search enquiries…"
            className="pl-9"
          />
        </div>
        <select
          value={status}
          onChange={(event) => {
            setStatus(event.target.value);
            setPage(1);
          }}
          className="admin-input sm:w-44"
        >
          <option value="">All statuses</option>
          {statuses.map((option) => (
            <option key={option} value={option}>
              {option}
            </option>
          ))}
        </select>
      </div>

      <div className="admin-card overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-16">
            <Spinner />
          </div>
        ) : (
          <table className="admin-table">
            <thead>
              <tr>
                <th>From</th>
                <th className="hidden sm:table-cell">Subject</th>
                <th>Status</th>
                <th className="hidden md:table-cell">Received</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((enquiry) => (
                  <tr
                    key={enquiry.id}
                    className="cursor-pointer"
                    onClick={() => setDetail(enquiry)}
                  >
                    <td>
                      <p className="font-medium text-ink">{enquiry.name}</p>
                      <p className="text-xs text-ink/35">{enquiry.email}</p>
                    </td>
                    <td className="hidden max-w-xs truncate text-ink/55 sm:table-cell">
                      {enquiry.subject ?? '—'}
                    </td>
                    <td>
                      <Badge tone={enquiry.status === 'new' ? 'accent' : 'neutral'}>
                        {enquiry.status}
                      </Badge>
                    </td>
                    <td className="hidden text-xs text-ink/40 md:table-cell">
                      {formatDate(enquiry.created_at)}
                    </td>
                    <td className="text-right" onClick={(event) => event.stopPropagation()}>
                      <div className="flex items-center justify-end gap-1">
                        <a
                          href={`mailto:${enquiry.email}?subject=Re: ${encodeURIComponent(enquiry.subject ?? 'Your enquiry')}`}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                          aria-label="Reply by email"
                        >
                          <Mail size={15} />
                        </a>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(enquiry)}
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
                    No enquiries found.
                  </td>
                </tr>
              )}
            </tbody>
          </table>
        )}
      </div>

      {data && data.meta.last_page > 1 && (
        <div className="flex items-center justify-between text-sm text-ink/45">
          <span>
            Page {data.meta.current_page} of {data.meta.last_page} · {data.meta.total} enquiries
          </span>
          <div className="flex gap-2">
            <Button
              size="sm"
              variant="secondary"
              disabled={page <= 1}
              onClick={() => setPage(page - 1)}
            >
              Previous
            </Button>
            <Button
              size="sm"
              variant="secondary"
              disabled={page >= data.meta.last_page}
              onClick={() => setPage(page + 1)}
            >
              Next
            </Button>
          </div>
        </div>
      )}

      <Modal
        open={Boolean(detail)}
        onClose={() => setDetail(null)}
        title={detail ? (detail.subject ?? 'Enquiry') : 'Enquiry'}
        size="md"
        footer={
          detail && (
            <>
              <Button variant="ghost" onClick={() => setDetail(null)}>
                Close
              </Button>
              <a
                href={`mailto:${detail.email}?subject=Re: ${encodeURIComponent(detail.subject ?? 'Your enquiry')}`}
                className="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white transition hover:bg-accent-deep"
              >
                <Mail size={14} />
                Reply
              </a>
            </>
          )
        }
      >
        {detail && (
          <div className="space-y-4">
            <div className="grid gap-3 sm:grid-cols-2">
              <div>
                <p className="text-xs text-ink/35">From</p>
                <p className="text-sm text-ink/80">{detail.name}</p>
                <a
                  href={`mailto:${detail.email}`}
                  className="text-xs text-accent-soft hover:text-ink"
                >
                  {detail.email}
                </a>
              </div>
              <div>
                <p className="text-xs text-ink/35">Received</p>
                <p className="text-sm text-ink/80">{formatDate(detail.created_at, true)}</p>
              </div>
              {detail.phone && (
                <div>
                  <p className="text-xs text-ink/35">Phone</p>
                  <p className="text-sm text-ink/80">{detail.phone}</p>
                </div>
              )}
              {detail.company && (
                <div>
                  <p className="text-xs text-ink/35">Company</p>
                  <p className="text-sm text-ink/80">{detail.company}</p>
                </div>
              )}
            </div>

            <div>
              <p className="text-xs text-ink/35">Message</p>
              <p className="border-line mt-1.5 rounded-lg border bg-near-black/30 p-3 text-sm leading-relaxed whitespace-pre-line text-ink/75">
                {detail.message}
              </p>
            </div>

            <div>
              <p className="mb-2 text-xs text-ink/35">Status</p>
              <div className="flex flex-wrap gap-2">
                {statuses.map((option) => (
                  <button
                    key={option}
                    type="button"
                    onClick={() => void update(detail, option)}
                    className={`rounded-lg border px-3 py-1.5 text-xs transition ${
                      detail.status === option
                        ? 'border-accent/50 bg-accent/15 text-ink'
                        : 'border-line text-ink/50 hover:text-ink'
                    }`}
                  >
                    {option}
                  </button>
                ))}
              </div>
            </div>
          </div>
        )}
      </Modal>

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete enquiry"
        message="This message will be permanently removed."
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
