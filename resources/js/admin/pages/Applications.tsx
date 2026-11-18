import { useState } from 'react';
import { Download, FileText, Search, StickyNote } from 'lucide-react';
import { api, endpoints, type Paginated } from '../lib/api';
import { errorMessage, useApi } from '../lib/useApi';
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
import { formatDate, relativeTime } from '@/lib/format';

type ApplicationRow = {
  id: number;
  name: string;
  initials: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string | null;
  status: 'new' | 'reviewing' | 'shortlisted' | 'rejected' | 'hired';
  job: { id: number; title: string } | null;
  cv: { name: string; size: number; url: string };
  created_at: string;
};

type ApplicationDetail = ApplicationRow & {
  cover_letter: string | null;
  linkedin_url: string | null;
  notes: Array<{ id: number; body: string; author: string; created_at: string }>;
};

const statuses: ApplicationRow['status'][] = [
  'new',
  'reviewing',
  'shortlisted',
  'rejected',
  'hired',
];

const tone = (status: ApplicationRow['status']) =>
  status === 'hired'
    ? 'success'
    : status === 'rejected'
      ? 'danger'
      : status === 'shortlisted'
        ? 'accent'
        : status === 'reviewing'
          ? 'warning'
          : 'neutral';

export function ApplicationsPage() {
  const toast = useToast();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [detail, setDetail] = useState<ApplicationDetail | null>(null);
  const [note, setNote] = useState('');
  const [savingNote, setSavingNote] = useState(false);

  const { data, loading, reload } = useApi<Paginated<ApplicationRow>>(
    endpoints.applications,
    { q: search || undefined, status: status || undefined, page },
    [page],
  );

  const openDetail = async (application: ApplicationRow) => {
    const response = await api.get<{ data: ApplicationDetail }>(
      `${endpoints.applications}/${application.id}`,
    );
    setDetail(response.data);
    setNote('');
  };

  const updateStatus = async (next: ApplicationRow['status']) => {
    if (!detail) return;

    try {
      await api.patch(`${endpoints.applications}/${detail.id}`, { status: next });
      setDetail({ ...detail, status: next });
      toast.push('Status updated.');
      await reload();
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    }
  };

  const addNote = async () => {
    if (!detail || !note.trim()) return;
    setSavingNote(true);

    try {
      const response = await api.post<{ data: ApplicationDetail['notes'][number] }>(
        `${endpoints.applications}/${detail.id}/notes`,
        { body: note.trim() },
      );
      setDetail({ ...detail, notes: [response.data, ...detail.notes] });
      setNote('');
    } catch (error) {
      toast.push(errorMessage(error), 'error');
    } finally {
      setSavingNote(false);
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Applications"
        description="Review candidates, download CVs and keep internal notes."
        actions={
          <a
            href={`${endpoints.applications}-export`}
            className="border-line inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-xs text-ink/60 transition hover:text-ink"
          >
            <Download size={14} />
            Export CSV
          </a>
        }
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
            placeholder="Search by name or email…"
            className="pl-9"
          />
        </div>
        <select
          value={status}
          onChange={(event) => {
            setStatus(event.target.value);
            setPage(1);
          }}
          className="admin-input sm:w-48"
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
                <th>Candidate</th>
                <th className="hidden sm:table-cell">Role</th>
                <th>Status</th>
                <th className="hidden md:table-cell">Applied</th>
                <th className="text-right">CV</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((application) => (
                  <tr
                    key={application.id}
                    className="cursor-pointer"
                    onClick={() => void openDetail(application)}
                  >
                    <td>
                      <div className="flex items-center gap-3">
                        <span className="grid size-9 place-items-center rounded-full bg-accent/15 text-xs font-semibold text-accent-soft">
                          {application.initials}
                        </span>
                        <div>
                          <p className="font-medium text-ink">{application.name}</p>
                          <p className="text-xs text-ink/35">{application.email}</p>
                        </div>
                      </div>
                    </td>
                    <td className="hidden text-ink/50 sm:table-cell">
                      {application.job?.title ?? '—'}
                    </td>
                    <td>
                      <Badge tone={tone(application.status)}>{application.status}</Badge>
                    </td>
                    <td className="hidden text-xs text-ink/40 md:table-cell">
                      {relativeTime(application.created_at)}
                    </td>
                    <td className="text-right" onClick={(event) => event.stopPropagation()}>
                      <a
                        href={application.cv.url}
                        className="border-line inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs text-ink/60 transition hover:border-accent/40 hover:text-ink"
                      >
                        <FileText size={13} />
                        Download
                      </a>
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={5} className="py-14 text-center text-sm text-ink/35">
                    No applications found.
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
            Page {data.meta.current_page} of {data.meta.last_page} · {data.meta.total} applications
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
        title={detail ? `${detail.name} — ${detail.job?.title ?? 'Application'}` : 'Application'}
        size="lg"
        footer={
          <Button variant="ghost" onClick={() => setDetail(null)}>
            Close
          </Button>
        }
      >
        {detail && (
          <div className="space-y-5">
            <div className="grid gap-3 sm:grid-cols-2">
              <div>
                <p className="text-xs text-ink/35">Email</p>
                <a
                  href={`mailto:${detail.email}`}
                  className="text-sm text-accent-soft hover:text-ink"
                >
                  {detail.email}
                </a>
              </div>
              <div>
                <p className="text-xs text-ink/35">Phone</p>
                <p className="text-sm text-ink/75">{detail.phone ?? '—'}</p>
              </div>
              <div>
                <p className="text-xs text-ink/35">Applied</p>
                <p className="text-sm text-ink/75">{formatDate(detail.created_at, true)}</p>
              </div>
              <div>
                <p className="text-xs text-ink/35">LinkedIn</p>
                {detail.linkedin_url ? (
                  <a
                    href={detail.linkedin_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="text-sm text-accent-soft hover:text-ink"
                  >
                    View profile
                  </a>
                ) : (
                  <p className="text-sm text-ink/40">—</p>
                )}
              </div>
            </div>

            <div className="border-line flex flex-wrap items-center gap-3 rounded-lg border p-3">
              <Field label="Status" className="flex-1">
                <select
                  value={detail.status}
                  onChange={(event) =>
                    void updateStatus(event.target.value as ApplicationRow['status'])
                  }
                  className="admin-input"
                >
                  {statuses.map((option) => (
                    <option key={option} value={option}>
                      {option}
                    </option>
                  ))}
                </select>
              </Field>

              <a
                href={detail.cv.url}
                className="mt-5 inline-flex items-center gap-2 rounded-lg bg-accent px-3 py-2 text-sm font-medium text-white transition hover:bg-accent-deep"
              >
                <Download size={14} />
                CV ({detail.cv.name})
              </a>
            </div>

            {detail.cover_letter && (
              <div>
                <p className="text-xs text-ink/35">Cover letter</p>
                <p className="border-line mt-1.5 rounded-lg border bg-near-black/30 p-3 text-sm leading-relaxed whitespace-pre-line text-ink/70">
                  {detail.cover_letter}
                </p>
              </div>
            )}

            <div>
              <p className="mb-2 flex items-center gap-1.5 text-xs font-semibold tracking-wide text-ink/50 uppercase">
                <StickyNote size={13} />
                Internal notes
              </p>

              <div className="flex gap-2">
                <Input
                  value={note}
                  onChange={(event) => setNote(event.target.value)}
                  placeholder="Add a note visible to the hiring team only…"
                />
                <Button size="sm" onClick={addNote} loading={savingNote} disabled={!note.trim()}>
                  Add
                </Button>
              </div>

              <ul className="mt-3 space-y-2">
                {detail.notes.length === 0 && (
                  <li className="text-xs text-ink/30">No notes yet.</li>
                )}
                {detail.notes.map((entry) => (
                  <li key={entry.id} className="border-line rounded-lg border bg-near-black/20 p-3">
                    <p className="text-sm text-ink/75">{entry.body}</p>
                    <p className="mt-1.5 text-[11px] text-ink/30">
                      {entry.author} · {relativeTime(entry.created_at)}
                    </p>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        )}
      </Modal>
    </div>
  );
}
