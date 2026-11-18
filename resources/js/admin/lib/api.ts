import { apiGet, apiSend, isApiError, type ApiError } from '@/lib/http';

export const api = {
  get: apiGet,
  post: <T>(url: string, body?: unknown) => apiSend<T>('POST', url, body),
  put: <T>(url: string, body?: unknown) => apiSend<T>('PUT', url, body),
  patch: <T>(url: string, body?: unknown) => apiSend<T>('PATCH', url, body),
  delete: <T>(url: string) => apiSend<T>('DELETE', url),
};

export const endpoints = {
  me: '/admin/api/me',
  dashboard: '/admin/api/dashboard',
  pages: '/admin/api/pages',
  services: '/admin/api/services',
  solutions: '/admin/api/solutions',
  technologies: '/admin/api/technologies',
  articles: '/admin/api/articles',
  categories: '/admin/api/categories',
  tags: '/admin/api/tags',
  jobs: '/admin/api/jobs',
  applications: '/admin/api/applications',
  media: '/admin/api/media',
  navigation: '/admin/api/navigation',
  settings: '/admin/api/settings',
  users: '/admin/api/users',
  roles: '/admin/api/roles',
  enquiries: '/admin/api/enquiries',
  auditLogs: '/admin/api/audit-logs',
};

export type PaginationMeta = {
  current_page: number;
  last_page: number;
  total: number;
  per_page?: number;
};

export type Paginated<T> = { data: T[]; meta: PaginationMeta };

export type MediaItem = {
  id: number;
  url: string;
  thumbnail: string | null;
  filename: string;
  mime: string | null;
  size: number;
  human_size: string;
  width: number | null;
  height: number | null;
  alt: string | null;
  title: string | null;
  folder: string;
  disk: string;
  is_image: boolean;
  created_at: string;
};

export type PageRecord = {
  id: number;
  title: string;
  slug: string;
  template: string;
  status: 'draft' | 'published';
  is_system: boolean;
  sections_count: number;
  updated_at: string;
  published_at: string | null;
  url: string;
};

export type SectionField = {
  name: string;
  type:
    | 'text'
    | 'textarea'
    | 'richtext'
    | 'image'
    | 'url'
    | 'boolean'
    | 'select'
    | 'number'
    | 'repeater'
    | 'cta'
    | 'icon';
  label: string;
  hint?: string | null;
  required?: boolean;
  options?: Record<string, string>;
  fields?: SectionField[];
  default?: unknown;
};

export type SectionDefinition = {
  label: string;
  description: string;
  icon: string;
  fields: SectionField[];
};

export type PageSection = {
  id?: number;
  type: string;
  layout: string | null;
  is_enabled: boolean;
  content: Record<string, unknown>;
};

export type PageDetail = PageRecord & {
  seo_title: string | null;
  seo_description: string | null;
  og_media_id: number | null;
  og_media_url: string | null;
  has_unpublished_changes: boolean;
  sections: PageSection[];
  versions: Array<{
    id: number;
    version_number: number;
    label: string | null;
    published_at: string | null;
    sections_count: number;
  }>;
};

export { isApiError };
export type { ApiError };
