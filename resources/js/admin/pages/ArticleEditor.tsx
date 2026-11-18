import { useEffect, useState } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { ArrowLeft, ExternalLink, Save } from 'lucide-react';
import { api, endpoints, type MediaItem } from '../lib/api';
import { errorMessage, fieldErrors, useApi } from '../lib/useApi';
import {
  Badge,
  Button,
  Field,
  Input,
  PageHeader,
  Spinner,
  Tabs,
  Toggle,
  useToast,
} from '../components/ui';
import { RichTextEditor } from '../components/RichTextEditor';
import { ImageField } from '../components/MediaPicker';

type Category = { id: number; name: string };
type Tag = { id: number; name: string };
type Placement = {
  id?: number;
  location: 'news' | 'home_featured' | 'home_latest';
  sort_order: number;
  starts_at: string | null;
  ends_at: string | null;
};

type Article = {
  id: number;
  title: string;
  slug: string;
  excerpt: string | null;
  body: string | null;
  featured_media_id: number | null;
  og_media_id: number | null;
  status: 'draft' | 'published' | 'archived';
  published_at: string | null;
  is_featured: boolean;
  read_time: number;
  seo_title: string | null;
  seo_description: string | null;
  canonical_url: string | null;
  categories: Category[];
  tags: Tag[];
  placements: Placement[];
  url: string;
};

const locations: Array<{ value: Placement['location']; label: string; hint: string }> = [
  { value: 'news', label: 'News listing', hint: 'Always included when published' },
  { value: 'home_featured', label: 'Home — Featured', hint: 'Featured article section' },
  { value: 'home_latest', label: 'Home — Latest', hint: 'Latest articles section' },
];

export function ArticleEditorPage() {
  const { id } = useParams<{ id: string }>();
  const isNew = id === 'new';
  const navigate = useNavigate();
  const toast = useToast();

  const {
    data: articleData,
    loading,
    reload,
  } = useApi<{ data: Article }>(
    isNew ? `${endpoints.articles}?skip=1` : `${endpoints.articles}/${id}`,
  );
  const { data: categoryData, reload: reloadCategories } = useApi<{ data: Category[] }>(
    endpoints.categories,
  );
  const { data: tagData, reload: reloadTags } = useApi<{ data: Tag[] }>(endpoints.tags);

  const [form, setForm] = useState({
    title: '',
    slug: '',
    excerpt: '',
    body: '',
    featured_media_id: null as number | null,
    og_media_id: null as number | null,
    status: 'draft' as Article['status'],
    published_at: '',
    is_featured: false,
    read_time: 5,
    seo_title: '',
    seo_description: '',
    canonical_url: '',
  });
  const [categoryIds, setCategoryIds] = useState<number[]>([]);
  const [tagIds, setTagIds] = useState<number[]>([]);
  const [placements, setPlacements] = useState<Placement[]>([]);
  const [newCategory, setNewCategory] = useState('');
  const [newTag, setNewTag] = useState('');
  const [tab, setTab] = useState('content');
  const [errors, setErrors] = useState<Record<string, string>>({});
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (!articleData || isNew) return;
    const article = articleData.data;

    setForm({
      title: article.title,
      slug: article.slug,
      excerpt: article.excerpt ?? '',
      body: article.body ?? '',
      featured_media_id: article.featured_media_id,
      og_media_id: article.og_media_id,
      status: article.status,
      published_at: article.published_at ? article.published_at.slice(0, 16) : '',
      is_featured: article.is_featured,
      read_time: article.read_time,
      seo_title: article.seo_title ?? '',
      seo_description: article.seo_description ?? '',
      canonical_url: article.canonical_url ?? '',
    });
    setCategoryIds(article.categories.map((category) => category.id));
    setTagIds(article.tags.map((tag) => tag.id));
    setPlacements(
      article.placements.length
        ? article.placements
        : [{ location: 'news', sort_order: 0, starts_at: null, ends_at: null }],
    );
  }, [articleData, isNew]);

  const save = async (thenPublish = false) => {
    setSaving(true);
    setErrors({});

    try {
      const payload = {
        ...form,
        slug: form.slug || undefined,
        published_at: form.published_at || null,
        seo_title: form.seo_title || null,
        seo_description: form.seo_description || null,
        canonical_url: form.canonical_url || null,
        status: thenPublish
          ? form.status === 'archived'
            ? 'published'
            : form.status === 'draft'
              ? 'published'
              : form.status
          : form.status,
        category_ids: categoryIds,
        tag_ids: tagIds,
      };

      let articleId = isNew ? null : Number(id);

      if (articleId) {
        await api.put(`${endpoints.articles}/${articleId}`, payload);
      } else {
        const response = await api.post<{ data: Article }>(endpoints.articles, payload);
        articleId = response.data.id;
      }

      const activePlacements = placements;

      await api.put(`${endpoints.articles}/${articleId}/placements`, {
        placements: activePlacements.map((placement, index) => ({
          location: placement.location,
          sort_order: placement.sort_order ?? index,
          starts_at: placement.starts_at || null,
          ends_at: placement.ends_at || null,
        })),
      });

      toast.push(thenPublish ? 'Article published.' : 'Article saved.');

      if (isNew) {
        navigate(`/articles/${articleId}`, { replace: true });
      } else {
        await reload();
      }
    } catch (error) {
      setErrors(fieldErrors(error));
      toast.push(errorMessage(error), 'error');
    } finally {
      setSaving(false);
    }
  };

  if (!isNew && loading && !articleData) {
    return (
      <div className="flex h-64 items-center justify-center">
        <Spinner />
      </div>
    );
  }

  const togglePlacement = (location: Placement['location']) => {
    setPlacements((current) => {
      const exists = current.some((placement) => placement.location === location);
      if (exists) {
        return current.filter((placement) => placement.location !== location);
      }
      return [...current, { location, sort_order: current.length, starts_at: null, ends_at: null }];
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-4">
        <div className="flex items-center gap-3">
          <button
            type="button"
            onClick={() => navigate('/articles')}
            className="border-line rounded-lg border p-2 text-ink/50 transition hover:text-ink"
            aria-label="Back to articles"
          >
            <ArrowLeft size={16} />
          </button>
          <div>
            <h1 className="text-xl font-semibold text-ink">
              {isNew ? 'New article' : form.title || 'Untitled article'}
            </h1>
            <p className="text-xs text-ink/35">{isNew ? 'Draft' : `/${form.slug}`}</p>
          </div>
          {!isNew && (
            <Badge tone={form.status === 'published' ? 'success' : 'warning'}>{form.status}</Badge>
          )}
        </div>

        <div className="flex items-center gap-2">
          {!isNew && (
            <a
              href={articleData?.data.url ?? '#'}
              target="_blank"
              rel="noopener noreferrer"
              className="border-line inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-xs text-ink/60 transition hover:text-ink"
            >
              <ExternalLink size={14} />
              View
            </a>
          )}
          <Button variant="secondary" loading={saving} onClick={() => save()}>
            Save
          </Button>
          <Button icon={<Save size={14} />} loading={saving} onClick={() => save(true)}>
            Save & publish
          </Button>
        </div>
      </div>

      <Tabs
        tabs={[
          { id: 'content', label: 'Content' },
          { id: 'placement', label: 'Placement' },
          { id: 'seo', label: 'SEO' },
        ]}
        active={tab}
        onChange={setTab}
      />

      {tab === 'content' && (
        <div className="grid gap-6 xl:grid-cols-[1fr_20rem]">
          <div className="space-y-4">
            <div className="admin-card space-y-4 p-5">
              <Field label="Title" required error={errors.title}>
                <Input
                  value={form.title}
                  onChange={(event) => setForm({ ...form, title: event.target.value })}
                  placeholder="A clear, specific headline"
                />
              </Field>

              <Field label="Slug" hint="Leave blank to generate from the title" error={errors.slug}>
                <Input
                  value={form.slug}
                  onChange={(event) => setForm({ ...form, slug: event.target.value })}
                />
              </Field>

              <Field
                label="Excerpt"
                hint="Used on cards, listings and as the fallback meta description"
              >
                <textarea
                  value={form.excerpt}
                  onChange={(event) => setForm({ ...form, excerpt: event.target.value })}
                  rows={3}
                  className="admin-input"
                />
              </Field>

              <div>
                <span className="admin-label">Article body</span>
                <RichTextEditor
                  value={form.body}
                  onChange={(html) => setForm({ ...form, body: html })}
                  placeholder="Write your article…"
                />
              </div>
            </div>
          </div>

          <aside className="space-y-4">
            <div className="admin-card space-y-4 p-5">
              <h2 className="text-sm font-semibold text-ink">Publishing</h2>

              <Field label="Status">
                <select
                  value={form.status}
                  onChange={(event) =>
                    setForm({ ...form, status: event.target.value as Article['status'] })
                  }
                  className="admin-input"
                >
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="archived">Archived</option>
                </select>
              </Field>

              <Field label="Publish date" hint="Leave blank to use the current time">
                <input
                  type="datetime-local"
                  value={form.published_at}
                  onChange={(event) => setForm({ ...form, published_at: event.target.value })}
                  className="admin-input"
                />
              </Field>

              <Field label="Reading time (minutes)">
                <Input
                  type="number"
                  min={0}
                  max={120}
                  value={form.read_time}
                  onChange={(event) => setForm({ ...form, read_time: Number(event.target.value) })}
                />
              </Field>

              <Toggle
                checked={form.is_featured}
                onChange={(value) => setForm({ ...form, is_featured: value })}
                label="Mark as featured"
              />
            </div>

            <div className="admin-card p-5">
              <ImageField
                label="Featured image"
                value={form.featured_media_id}
                onChange={(mediaId) => setForm({ ...form, featured_media_id: mediaId })}
              />
            </div>

            <div className="admin-card space-y-3 p-5">
              <h2 className="text-sm font-semibold text-ink">Categories</h2>
              <div className="space-y-2">
                {categoryData?.data.map((category) => (
                  <label key={category.id} className="flex items-center gap-2 text-sm text-ink/65">
                    <input
                      type="checkbox"
                      checked={categoryIds.includes(category.id)}
                      onChange={(event) =>
                        setCategoryIds((current) =>
                          event.target.checked
                            ? [...current, category.id]
                            : current.filter((value) => value !== category.id),
                        )
                      }
                      className="border-line size-4 rounded bg-near-black accent-[#3b82f6]"
                    />
                    {category.name}
                  </label>
                ))}
              </div>
              <div className="flex gap-2">
                <Input
                  value={newCategory}
                  onChange={(event) => setNewCategory(event.target.value)}
                  placeholder="New category"
                />
                <Button
                  size="sm"
                  variant="secondary"
                  onClick={async () => {
                    if (!newCategory.trim()) return;
                    await api.post(endpoints.categories, { name: newCategory.trim() });
                    setNewCategory('');
                    await reloadCategories();
                  }}
                >
                  Add
                </Button>
              </div>
            </div>

            <div className="admin-card space-y-3 p-5">
              <h2 className="text-sm font-semibold text-ink">Tags</h2>
              <div className="flex flex-wrap gap-2">
                {tagData?.data.map((tag) => {
                  const selected = tagIds.includes(tag.id);
                  return (
                    <button
                      key={tag.id}
                      type="button"
                      onClick={() =>
                        setTagIds((current) =>
                          selected
                            ? current.filter((value) => value !== tag.id)
                            : [...current, tag.id],
                        )
                      }
                      className={`rounded-full border px-2.5 py-1 text-xs transition ${
                        selected
                          ? 'border-accent/50 bg-accent/15 text-accent-soft'
                          : 'border-line bg-white/5 text-ink/50 hover:text-ink'
                      }`}
                    >
                      {tag.name}
                    </button>
                  );
                })}
              </div>
              <div className="flex gap-2">
                <Input
                  value={newTag}
                  onChange={(event) => setNewTag(event.target.value)}
                  placeholder="New tag"
                />
                <Button
                  size="sm"
                  variant="secondary"
                  onClick={async () => {
                    if (!newTag.trim()) return;
                    await api.post(endpoints.tags, { name: newTag.trim() });
                    setNewTag('');
                    await reloadTags();
                  }}
                >
                  Add
                </Button>
              </div>
            </div>
          </aside>
        </div>
      )}

      {tab === 'placement' && (
        <div className="admin-card p-5">
          <h2 className="text-sm font-semibold text-ink">Where should this article appear?</h2>
          <p className="mt-1 text-xs text-ink/40">
            One article record can be surfaced in several places at once — no duplicates needed.
          </p>

          <div className="mt-5 space-y-3">
            {locations.map((location) => {
              const placement = placements.find((item) => item.location === location.value);
              const active = Boolean(placement);

              return (
                <div
                  key={location.value}
                  className={`rounded-lg border p-4 transition ${
                    active ? 'border-accent/40 bg-accent/5' : 'border-line'
                  }`}
                >
                  <div className="flex flex-wrap items-center justify-between gap-3">
                    <label className="flex items-center gap-2.5 text-sm font-medium text-ink">
                      <input
                        type="checkbox"
                        checked={active}
                        onChange={() => togglePlacement(location.value)}
                        className="border-line size-4 rounded bg-near-black accent-[#3b82f6]"
                      />
                      {location.label}
                    </label>
                    <span className="text-xs text-ink/35">{location.hint}</span>
                  </div>

                  {active && placement && (
                    <div className="mt-3 grid gap-3 sm:grid-cols-3">
                      <Field label="Order">
                        <Input
                          type="number"
                          min={0}
                          value={placement.sort_order}
                          onChange={(event) =>
                            setPlacements((current) =>
                              current.map((item) =>
                                item.location === location.value
                                  ? { ...item, sort_order: Number(event.target.value) }
                                  : item,
                              ),
                            )
                          }
                        />
                      </Field>
                      <Field label="Starts">
                        <input
                          type="datetime-local"
                          value={placement.starts_at?.slice(0, 16) ?? ''}
                          onChange={(event) =>
                            setPlacements((current) =>
                              current.map((item) =>
                                item.location === location.value
                                  ? { ...item, starts_at: event.target.value || null }
                                  : item,
                              ),
                            )
                          }
                          className="admin-input"
                        />
                      </Field>
                      <Field label="Ends">
                        <input
                          type="datetime-local"
                          value={placement.ends_at?.slice(0, 16) ?? ''}
                          onChange={(event) =>
                            setPlacements((current) =>
                              current.map((item) =>
                                item.location === location.value
                                  ? { ...item, ends_at: event.target.value || null }
                                  : item,
                              ),
                            )
                          }
                          className="admin-input"
                        />
                      </Field>
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      )}

      {tab === 'seo' && (
        <div className="grid gap-6 lg:grid-cols-2">
          <div className="admin-card space-y-4 p-5">
            <h2 className="text-sm font-semibold text-ink">Search engine</h2>

            <Field label="SEO title" hint="Defaults to the article title">
              <Input
                value={form.seo_title}
                onChange={(event) => setForm({ ...form, seo_title: event.target.value })}
              />
            </Field>

            <Field label="Meta description" hint="150–160 characters works best">
              <textarea
                value={form.seo_description}
                onChange={(event) => setForm({ ...form, seo_description: event.target.value })}
                rows={4}
                className="admin-input"
              />
            </Field>

            <Field
              label="Canonical URL"
              hint="Only set this when the canonical version lives elsewhere"
            >
              <Input
                value={form.canonical_url}
                onChange={(event) => setForm({ ...form, canonical_url: event.target.value })}
                placeholder="https://"
              />
            </Field>
          </div>

          <div className="admin-card p-5">
            <ImageField
              label="Social sharing image"
              value={form.og_media_id}
              onChange={(mediaId) => setForm({ ...form, og_media_id: mediaId })}
            />
          </div>
        </div>
      )}
    </div>
  );
}
