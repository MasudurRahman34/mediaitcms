import { useState } from 'react';
import { Search } from 'lucide-react';
import { endpoints, type Paginated } from '../lib/api';
import { useApi } from '../lib/useApi';
import { Badge, Button, Input, PageHeader, Spinner } from '../components/ui';
import { formatDate } from '@/lib/format';

type AuditEntry = {
  id: number;
  action: string;
  description: string | null;
  user: string;
  ip: string | null;
  changes: Record<string, unknown> | null;
  created_at: string;
};

export function AuditLogPage() {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);

  const { data, loading } = useApi<Paginated<AuditEntry>>(
    endpoints.auditLogs,
    { q: search || undefined, page },
    [page],
  );

  return (
    <div className="space-y-6">
      <PageHeader
        title="Audit log"
        description="A read-only record of administrative changes across the CMS."
      />

      <div className="relative max-w-md">
        <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-ink/35">
          <Search size={16} />
        </span>
        <Input
          value={search}
          onChange={(event) => {
            setSearch(event.target.value);
            setPage(1);
          }}
          placeholder="Filter by action or detail…"
          className="pl-9"
        />
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
                <th>Action</th>
                <th className="hidden sm:table-cell">Detail</th>
                <th className="hidden md:table-cell">By</th>
                <th className="text-right">When</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((entry) => (
                  <tr key={entry.id}>
                    <td>
                      <Badge tone="accent">{entry.action}</Badge>
                    </td>
                    <td className="hidden max-w-sm truncate text-ink/60 sm:table-cell">
                      {entry.description ?? '—'}
                    </td>
                    <td className="hidden text-xs text-ink/45 md:table-cell">
                      {entry.user}
                      {entry.ip && <span className="ml-2 text-ink/25">{entry.ip}</span>}
                    </td>
                    <td className="text-right text-xs text-ink/40">
                      {formatDate(entry.created_at, true)}
                    </td>
                  </tr>
                ))
              ) : (
                <tr>
                  <td colSpan={4} className="py-14 text-center text-sm text-ink/35">
                    No activity recorded yet.
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
            Page {data.meta.current_page} of {data.meta.last_page}
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
    </div>
  );
}
