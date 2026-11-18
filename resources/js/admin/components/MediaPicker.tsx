import { useCallback, useEffect, useRef, useState } from 'react';
import { Image, Search, Upload, X } from 'lucide-react';
import { api, endpoints, type MediaItem } from '@/admin/lib/api';
import { Button, Input, Modal, Spinner, useToast } from './ui';
import { cn } from '@/lib/cn';

type Props = {
  open: boolean;
  onClose: () => void;
  onSelect: (media: MediaItem) => void;
  allowAnyFile?: boolean;
};

export function MediaPicker({ open, onClose, onSelect, allowAnyFile = false }: Props) {
  const [items, setItems] = useState<MediaItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [uploading, setUploading] = useState(false);
  const [search, setSearch] = useState('');
  const fileRef = useRef<HTMLInputElement>(null);
  const toast = useToast();

  const load = useCallback(
    async (query?: string) => {
      setLoading(true);
      try {
        const response = await api.get<{ data: MediaItem[] }>(endpoints.media, {
          q: query || undefined,
          images_only: allowAnyFile ? undefined : 1,
          per_page: 48,
        });
        setItems(response.data);
      } catch {
        setItems([]);
      } finally {
        setLoading(false);
      }
    },
    [allowAnyFile],
  );

  useEffect(() => {
    if (open) void load(search);
  }, [open, load, search]);

  const upload = async (file: File) => {
    setUploading(true);
    try {
      const form = new FormData();
      form.append('file', file);
      const response = await api.post<{ data: MediaItem }>(endpoints.media, form);
      setItems((current) => [response.data, ...current]);
      onSelect(response.data);
      onClose();
    } catch {
      toast.push('Upload failed. Check the file type and size.', 'error');
    } finally {
      setUploading(false);
    }
  };

  return (
    <Modal
      open={open}
      onClose={onClose}
      title="Media library"
      size="xl"
      footer={
        <>
          <Button variant="ghost" onClick={onClose}>
            Close
          </Button>
          <Button
            variant="secondary"
            icon={<Upload size={15} />}
            loading={uploading}
            onClick={() => fileRef.current?.click()}
          >
            Upload
          </Button>
        </>
      }
    >
      <input
        ref={fileRef}
        type="file"
        accept={allowAnyFile ? undefined : 'image/*'}
        className="sr-only"
        onChange={(event) => {
          const file = event.target.files?.[0];
          if (file) void upload(file);
          event.target.value = '';
        }}
      />

      <div className="relative mb-5">
        <span className="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-ink/35">
          <Search size={16} />
        </span>
        <Input
          value={search}
          onChange={(event) => setSearch(event.target.value)}
          placeholder="Search media…"
          className="pl-9"
        />
      </div>

      {loading ? (
        <div className="flex justify-center py-16">
          <Spinner />
        </div>
      ) : items.length === 0 ? (
        <div className="border-line rounded-xl border border-dashed py-16 text-center text-sm text-ink/45">
          No media yet — upload your first file.
        </div>
      ) : (
        <ul className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
          {items.map((media) => (
            <li key={media.id}>
              <button
                type="button"
                onClick={() => {
                  onSelect(media);
                  onClose();
                }}
                className="group border-line w-full overflow-hidden rounded-lg border bg-near-black/40 text-left transition hover:border-accent/50"
              >
                <span className="block aspect-[4/3] overflow-hidden bg-near-black/60">
                  {media.is_image ? (
                    <img
                      src={media.thumbnail ?? media.url}
                      alt={media.alt ?? media.filename}
                      loading="lazy"
                      className="size-full object-cover transition group-hover:scale-[1.03]"
                    />
                  ) : (
                    <span className="flex size-full items-center justify-center text-ink/30">
                      <Image size={22} />
                    </span>
                  )}
                </span>
                <span className="block truncate px-2.5 py-2 text-[11px] text-ink/55">
                  {media.title ?? media.filename}
                </span>
              </button>
            </li>
          ))}
        </ul>
      )}
    </Modal>
  );
}

/* Single-image field used across editors. */
export function ImageField({
  value,
  onChange,
  label = 'Image',
  hint,
}: {
  value: number | null;
  onChange: (id: number | null, url: string | null) => void;
  label?: string;
  hint?: string;
}) {
  const [open, setOpen] = useState(false);
  const [preview, setPreview] = useState<string | null>(null);

  useEffect(() => {
    if (!value) {
      setPreview(null);
    }
  }, [value]);

  return (
    <div>
      <span className="admin-label">{label}</span>
      {value ? (
        <div className="border-line relative overflow-hidden rounded-lg border">
          {preview ? (
            <img src={preview} alt="" className="aspect-[4/3] w-full object-cover" />
          ) : (
            <div className="flex aspect-[4/3] items-center justify-center bg-near-black/50 text-xs text-ink/35">
              Image #{value}
            </div>
          )}
          <div className="border-line bg-panel flex items-center justify-between gap-2 border-t px-2 py-1.5">
            <Button size="sm" variant="ghost" onClick={() => setOpen(true)}>
              Replace
            </Button>
            <button
              type="button"
              onClick={() => onChange(null, null)}
              className="rounded p-1 text-ink/40 transition hover:text-red-300"
              aria-label="Remove image"
            >
              <X size={14} />
            </button>
          </div>
        </div>
      ) : (
        <button
          type="button"
          onClick={() => setOpen(true)}
          className="border-line flex aspect-[4/3] w-full flex-col items-center justify-center gap-2 rounded-lg border border-dashed bg-near-black/30 text-xs text-ink/40 transition hover:border-accent/40 hover:text-ink/70"
        >
          <Image size={18} />
          Choose image
        </button>
      )}
      {hint && <p className="mt-1.5 text-xs text-ink/35">{hint}</p>}

      <MediaPicker
        open={open}
        onClose={() => setOpen(false)}
        onSelect={(media) => {
          setPreview(media.thumbnail ?? media.url);
          onChange(media.id, media.url);
        }}
      />
    </div>
  );
}
