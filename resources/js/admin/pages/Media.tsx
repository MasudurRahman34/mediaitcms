import { useRef, useState } from 'react';
import { Copy, Image as ImageIcon, Search, Trash2, Upload } from 'lucide-react';
import { api, endpoints, type MediaItem, type Paginated } from '../lib/api';
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
  useToast,
} from '../components/ui';
import { fileSize, formatDate } from '@/lib/format';

export function MediaPage() {
  const toast = useToast();
  const fileRef = useRef<HTMLInputElement>(null);
  const [search, setSearch] = useState('');
  const [folder, setFolder] = useState('');
  const [page, setPage] = useState(1);
  const [uploading, setUploading] = useState(false);
  const [editing, setEditing] = useState<MediaItem | null>(null);
  const [form, setForm] = useState({ alt: '', title: '', folder: 'uploads' });
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);
  const [deleteTarget, setDeleteTarget] = useState<MediaItem | null>(null);
  const [deleting, setDeleting] = useState(false);

  const { data, loading, reload } = useApi<Paginated<MediaItem> & { folders: string[] }>(
    endpoints.media,
    { q: search || undefined, folder: folder || undefined, page },
    [page],
  );

  const uploadFiles = async (files: FileList | null) => {
    if (!files?.length) return;

    setUploading(true);

    for (const file of Array.from(files)) {
      try {
        const payload = new FormData();
        payload.append('file', file);
        payload.append('folder', 'uploads');
        await api.post(endpoints.media, payload);
      } catch (error) {
        toast.push(`${file.name}: ${errorMessage(error)}`, 'error');
      }
    }

    setUploading(false);
    toast.push('Upload complete.');
    await reload();
  };

  const saveMeta = async () => {
    if (!editing) return;
    setSaving(true);
    setErrors({});

    try {
      await api.put(`${endpoints.media}/${editing.id}`, {
        alt: form.alt || null,
        title: form.title || null,
        folder: form.folder,
      });
      toast.push('Media updated.');
      setEditing(null);
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
      await api.delete(`${endpoints.media}/${deleteTarget.id}`);
      toast.push('File deleted.');
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
        title="Media library"
        description="Upload images and files once, then reuse them across the site."
        actions={
          <>
            <input
              ref={fileRef}
              type="file"
              multiple
              className="sr-only"
              accept="image/*,.pdf,.doc,.docx,.svg"
              onChange={(event) => {
                void uploadFiles(event.target.files);
                event.target.value = '';
              }}
            />
            <Button
              icon={<Upload size={15} />}
              loading={uploading}
              onClick={() => fileRef.current?.click()}
            >
              Upload files
            </Button>
          </>
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
            placeholder="Search by filename, title or alt text…"
            className="pl-9"
          />
        </div>
        <select
          value={folder}
          onChange={(event) => {
            setFolder(event.target.value);
            setPage(1);
          }}
          className="admin-input sm:w-48"
        >
          <option value="">All folders</option>
          {data?.folders?.map((name) => (
            <option key={name} value={name}>
              {name}
            </option>
          ))}
        </select>
      </div>

      {loading ? (
        <div className="flex justify-center py-16">
          <Spinner />
        </div>
      ) : data?.data.length ? (
        <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
          {data.data.map((media) => (
            <li key={media.id} className="admin-card group overflow-hidden">
              <div className="relative aspect-[4/3] bg-near-black/50">
                {media.is_image ? (
                  <img
                    src={media.thumbnail ?? media.url}
                    alt={media.alt ?? media.filename}
                    loading="lazy"
                    className="size-full object-cover"
                  />
                ) : (
                  <div className="flex size-full items-center justify-center text-ink/25">
                    <ImageIcon size={22} />
                  </div>
                )}
                <div className="absolute inset-0 flex items-center justify-center gap-1 bg-near-black/70 opacity-0 transition group-hover:opacity-100">
                  <button
                    type="button"
                    onClick={() => {
                      void navigator.clipboard.writeText(media.url);
                      toast.push('URL copied.');
                    }}
                    className="rounded bg-white/10 p-2 text-ink transition hover:bg-white/20"
                    aria-label="Copy URL"
                  >
                    <Copy size={14} />
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setEditing(media);
                      setForm({
                        alt: media.alt ?? '',
                        title: media.title ?? '',
                        folder: media.folder,
                      });
                      setErrors({});
                    }}
                    className="rounded bg-white/10 p-2 text-ink transition hover:bg-white/20"
                    aria-label="Edit metadata"
                  >
                    <ImageIcon size={14} />
                  </button>
                  <button
                    type="button"
                    onClick={() => setDeleteTarget(media)}
                    className="rounded bg-white/10 p-2 text-ink transition hover:bg-red-500/40"
                    aria-label="Delete"
                  >
                    <Trash2 size={14} />
                  </button>
                </div>
              </div>
              <div className="p-2.5">
                <p className="truncate text-[11px] font-medium text-ink/70">
                  {media.title ?? media.filename}
                </p>
                <p className="mt-0.5 truncate text-[10px] text-ink/30">
                  {media.width && media.height ? `${media.width}×${media.height} · ` : ''}
                  {fileSize(media.size)}
                </p>
              </div>
            </li>
          ))}
        </ul>
      ) : (
        <div className="border-line rounded-xl border border-dashed py-20 text-center">
          <p className="text-sm text-ink/45">No media yet.</p>
          <Button variant="secondary" className="mt-4" onClick={() => fileRef.current?.click()}>
            Upload your first file
          </Button>
        </div>
      )}

      {data && data.meta.last_page > 1 && (
        <div className="flex items-center justify-between text-sm text-ink/45">
          <span>
            Page {data.meta.current_page} of {data.meta.last_page} · {data.meta.total} files
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
        open={Boolean(editing)}
        onClose={() => setEditing(null)}
        title="File details"
        size="md"
        footer={
          <>
            <Button variant="ghost" onClick={() => setEditing(null)}>
              Cancel
            </Button>
            <Button onClick={saveMeta} loading={saving}>
              Save
            </Button>
          </>
        }
      >
        {editing && (
          <div className="space-y-4">
            <div className="border-line overflow-hidden rounded-lg border bg-near-black/40">
              {editing.is_image ? (
                <img
                  src={editing.url}
                  alt={editing.alt ?? ''}
                  className="max-h-64 w-full object-contain"
                />
              ) : (
                <div className="flex h-32 items-center justify-center text-ink/30">
                  <ImageIcon size={26} />
                </div>
              )}
            </div>

            <dl className="grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt className="text-ink/35">Filename</dt>
                <dd className="truncate text-ink/75">{editing.filename}</dd>
              </div>
              <div>
                <dt className="text-ink/35">Size</dt>
                <dd className="text-ink/75">{fileSize(editing.size)}</dd>
              </div>
              <div>
                <dt className="text-ink/35">Dimensions</dt>
                <dd className="text-ink/75">
                  {editing.width && editing.height ? `${editing.width} × ${editing.height}` : '—'}
                </dd>
              </div>
              <div>
                <dt className="text-ink/35">Uploaded</dt>
                <dd className="text-ink/75">{formatDate(editing.created_at)}</dd>
              </div>
            </dl>

            <Field
              label="Alt text"
              hint="Describe the image for screen readers and search engines"
              error={errors.alt}
            >
              <Input
                value={form.alt}
                onChange={(event) => setForm({ ...form, alt: event.target.value })}
              />
            </Field>

            <Field label="Title">
              <Input
                value={form.title}
                onChange={(event) => setForm({ ...form, title: event.target.value })}
              />
            </Field>

            <Field label="Folder">
              <Input
                value={form.folder}
                onChange={(event) => setForm({ ...form, folder: event.target.value })}
              />
            </Field>

            <div className="flex items-center gap-2">
              <Badge tone={editing.disk === 'public' ? 'neutral' : 'warning'}>{editing.disk}</Badge>
              <span className="truncate text-xs text-ink/30">{editing.url}</span>
            </div>
          </div>
        )}
      </Modal>

      <ConfirmDialog
        open={Boolean(deleteTarget)}
        title="Delete file"
        message={`Delete “${deleteTarget?.filename}”? Files used by content cannot be deleted.`}
        onCancel={() => setDeleteTarget(null)}
        onConfirm={remove}
        loading={deleting}
      />
    </div>
  );
}
