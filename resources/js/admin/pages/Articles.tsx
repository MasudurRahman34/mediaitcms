import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { Pencil, Plus, Search, Trash2 } from 'lucide-react';
import { api, endpoints, type Paginated } from '../lib/api';
import { useApi } from '../lib/useApi';
import { errorMessage } from '../lib/useApi';
import {
  Badge,
  Button,
  ConfirmDialog,
  Input,
  PageHeader,
  Spinner,
  useToast,
} from '../components/ui';
import { formatDate } from '@/lib/format';

type ArticleRow = {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  featured_media_url: string | null;
  status: 'draft' | 'published' | 'archived';
  published_at: string | null;
  placements_count: number;
  categories: Array<{ id: number; name: string }>;
  updated_at: string;
  url: string;
};

export function ArticlesPage() {
  const navigate = useNavigate();
  const toast = useToast();
  const [search, setSearch] = useState('');
  const [status, setStatus] = useState('');
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState<ArticleRow | null>(null);
  const [deleting, setDeleting] = useState(false);

  const { data, loading, reload } = useApi<Paginated<ArticleRow>>(
    endpoints.articles,
    {
      q: search || undefined,
      status: status || undefined,
      page,
    },
    [page],
  );

  const remove = async () => {
    if (!deleteTarget) return;
    setDeleting(true);
    try {
      await api.delete(`${endpoints.articles}/${deleteTarget.id}`);
      toast.push('Article deleted.');
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
        title="Articles"
        description="Write, publish and place articles across the site."
        actions={
          <Button icon={<Plus size={15} />} onClick={() => navigate('/articles/new')}>
            New article
          </Button>
        }
      />

      <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
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
            placeholder="Search articles…"
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
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
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
                <th>Article</th>
                <th className="hidden sm:table-cell">Placements</th>
                <th>Status</th>
                <th className="hidden md:table-cell">Published</th>
                <th className="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              {data?.data.length ? (
                data.data.map((article) => (
                  <tr key={article.id}>
                    <td>
                      <div className="flex items-center gap-3">
                        {article.featured_media_url ? (
                          <img
                            src={article.featured_media_url}
                            alt=""
                            className="hidden size-10 rounded object-cover sm:block"
                            loading="lazy"
                          />
                        ) : null}
                        <div className="min-w-0">
                          <p className="truncate font-medium text-ink">{article.title}</p>
                          <p className="truncate text-xs text-ink/35">
                            {article.categories.map((c) => c.name).join(', ') ||
                              '/news/' + article.slug}
                          </p>
                        </div>
                      </div>
                    </td>
                    <td className="hidden sm:table-cell">
                      {article.placements_count > 0 ? (
                        <Badge tone="accent">
                          {article.placements_count} location
                          {article.placements_count > 1 ? 's' : ''}
                        </Badge>
                      ) : (
                        <span className="text-xs text-ink/25">News only</span>
                      )}
                    </td>
                    <td>
                      <Badge
                        tone={
                          article.status === 'published'
                            ? 'success'
                            : article.status === 'archived'
                              ? 'neutral'
                              : 'warning'
                        }
                      >
                        {article.status}
                      </Badge>
                    </td>
                    <td className="hidden text-xs text-ink/40 md:table-cell">
                      {formatDate(article.published_at)}
                    </td>
                    <td>
                      <div className="flex items-center justify-end gap-1">
                        <Link
                          to={`/articles/${article.id}`}
                          className="rounded p-1.5 text-ink/35 transition hover:text-ink"
                          aria-label="Edit article"
                        >
                          <Pencil size={15} />
                        </Link>
                        <button
                          type="button"
                          onClick={() => setDeleteTarget(article)}
                          className="rounded p-1.5 text-ink/35 transition hover:text-red-300"
                          aria-label="Delete article"
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
                    No articles found.
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
            Page {data.meta.current_page} of {data.meta.last_page} · {data.meta.total} articles
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

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete article"
        message={`Delete “${deleteTarget?.title}”? This cannot be undone.`}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
