# Media IT Soft — Business Website & CMS

A production-ready Laravel + React (TypeScript) business website for a software, AI and IT services company, with a complete admin CMS.

The **public site is fully server-rendered by Blade** — every CMS-managed word, heading, link and meta tag is in the HTML response, so SEO does not depend on JavaScript. React powers interactive islands (navigation, forms, galleries, counters) on the public site and the entire admin console.

---

## Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 12, PHP 8.3+, MySQL 8 |
| Frontend | React 19 + TypeScript, mounted inside Blade |
| Build | Vite 7 (Laravel Vite integration) |
| Styling | Tailwind CSS v4 (design tokens in `@theme`) |
| Auth | Laravel session authentication |
| Animation | Motion for React + CSS transitions |
| Icons | Lucide React (React) / inline Blade SVG set |
| Rich text | TipTap (sanitised with HTMLPurifier) |
| Drag & drop | dnd-kit |
| Files | Laravel Storage, S3-compatible via config |

---

## Requirements

- PHP **8.3+** with `pdo_mysql`, `gd`, `mbstring`, `fileinfo`, `openssl`, `intl`, `zip`
- Composer 2
- Node **20+** and npm
- MySQL **8+**

## Installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
```

Create the database and point `.env` at it:

```sql
CREATE DATABASE mediaitcms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```dotenv
DB_DATABASE=mediaitcms
DB_USERNAME=root
DB_PASSWORD=
APP_URL=http://mediaitcms.test
```

Then:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
```

Serve it:

```bash
php artisan serve          # http://127.0.0.1:8000
# or point Laragon/nginx at the public/ directory
```

For local development with hot reload:

```bash
npm run dev                # Vite
php artisan serve          # Laravel
```

---

## Signing in

The seeder creates four accounts (password for all: `Password!234`):

| Email | Role | Access |
| --- | --- | --- |
| `admin@mediaitsoft.co.uk` | Super Admin | Everything |
| `admin.team@mediaitsoft.co.uk` | Admin | All content + recruitment (no user management) |
| `editor@mediaitsoft.co.uk` | Editor | Pages, services, solutions, articles, media |
| `recruiter@mediaitsoft.co.uk` | Recruiter | Jobs, applications, media |

Admin console: **`/admin`** → redirects to `/admin/login`.

Change these credentials before deploying anywhere public.

---

## Admin CMS

| Area | What it does |
| --- | --- |
| **Dashboard** | Published pages, articles, services, solutions, active jobs, applications, enquiries, recent activity |
| **Pages** | Section-based page builder: drag to reorder, enable/disable, generated forms per section type, live preview, draft/publish, version history and restore |
| **Services** | CRUD with benefits, FAQs, icon, cover image, feature flag, SEO |
| **Solutions** | CRUD with feature sections (each with image), screenshot gallery, technologies, business benefits, live URL |
| **Articles** | TipTap editor, categories, tags, featured image, and a placement matrix |
| **Media** | Upload, search, alt/title editing, delete guard for in-use files |
| **Jobs** | Vacancies with closing dates; close/reopen |
| **Applications** | Filter/search, status workflow, internal notes, authorised CV download, CSV export |
| **Navigation** | Header and footer menus, nested items, ordering |
| **Settings** | Company identity, logo/favicon, contact details, socials, SEO defaults, global CTA |
| **Users & roles** | Accounts plus a permission matrix per role |
| **Enquiries** | Contact form submissions with status handling |
| **Audit log** | Read-only record of administrative changes |

### Article placements

An article exists **once**. The placement matrix decides where it appears:

- News listing
- Home — Featured
- Home — Latest

Each placement supports its own sort order and optional start/end dates. An article can appear in several locations simultaneously with no duplicated records.

### Page builder sections

`hero`, `rich_text`, `stats`, `features_grid`, `services_grid`, `solutions_grid`, `articles_grid`, `team_grid`, `faq`, `logos`, `gallery`, `testimonials`, `cta`, `contact_details`.

Section definitions live in `app/Services/Sections/SectionRegistry.php` — a single source of truth used for the admin forms, the validation rules and the Blade partial that renders each section. Adding a section type means adding one registry entry and one Blade partial.

**Publishing model:** edits are written to working-draft rows (`page_sections.version_id IS NULL`). Publishing freezes the draft into an immutable `page_versions` snapshot and points the page at it. The public site only ever reads the published snapshot; preview renders the draft for authorised users.

---

## SEO

- Fully server-rendered content — no client-side dependency for indexable text
- Per-entity SEO fields (title, description, canonical, OG image) with sensible fallbacks
- Open Graph + Twitter cards
- JSON-LD: Organization, WebSite, BreadcrumbList, Article, JobPosting, Service, FAQPage
- `sitemap.xml` generated from published content only, cached and invalidated automatically
- `robots.txt` disallowing `/admin`
- Automatic 301 redirects when a page, service, solution, article or job slug changes
- Responsive images with `srcset`, explicit dimensions, lazy loading

## Security

- Session authentication with throttled login and password reset
- Permission-gated admin routes (server-side, not just hidden UI)
- CSRF protection on all state-changing requests
- Rich text sanitised with HTMLPurifier on **write and render**
- Public forms rate-limited with honeypot fields
- CVs validated, renamed, stored on a **private** disk, served only through an authorising controller that writes an audit entry
- Draft, archived and scheduled content returns 404 publicly
- Security headers (`nosniff`, `Referrer-Policy`, `Permissions-Policy`, `X-Frame-Options`) and `noindex` on admin

## Performance

- Separate Vite entries — the admin bundle never loads on public pages
- Route-level code splitting in the admin; TipTap loads only when an editor opens
- Public JS ≈ 39 kB (13 kB gzipped)
- Settings and navigation cached; eager loading on every listing
- Indexed slugs, foreign keys, statuses and dates

---

## Testing

Tests run against MySQL (`mediaitcms_test`) because the schema uses FULLTEXT indexes.

```bash
CREATE DATABASE mediaitcms_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
php artisan test
```

**74 tests / 219 assertions**, covering:

- Authentication: login, failures, deactivation, throttling, password reset
- Authorisation matrix per role
- Page builder: draft isolation, publish, versioning, restore, ordering, section enable/disable, every section type renders
- XSS sanitisation of rich text
- Slug change → 301 redirect
- Public pages, 404s for unpublished content, article placements, search
- Sitemap/robots, structured data
- Recruitment: applications, CV validation and private storage, closed jobs, authorised download, notes, export
- Contact enquiries, honeypot, notifications
- Media upload, in-use delete guard
- Settings, navigation, audit logging

Other checks:

```bash
npx tsc --noEmit        # TypeScript
npm run build           # production assets
npm run format          # Prettier
./vendor/bin/pint       # PHP style
```

---

## Scheduled tasks

Registered in `routes/console.php`:

| Command | Schedule | Purpose |
| --- | --- | --- |
| `cms:close-expired-jobs` | daily 06:00 | Closes vacancies past their closing date |
| `cms:prune-versions` | weekly | Prunes old page versions, always keeping the published one |

Run the scheduler in production (`* * * * * php artisan schedule:run`).

---

## Media & storage

Uploads use the `public` disk by default. To use S3-compatible storage, set `MEDIA_DISK=s3` and the `AWS_*` variables. Applicant CVs always use the private `local` disk regardless of this setting.

Demo images are generated locally with GD during seeding, so no external image services are required.

---

## Project structure

```
app/
  Enums/                      Status enums (content, page, job, application, placement)
  Http/Controllers/Site/      Public site controllers (Blade views)
  Http/Controllers/Admin/     Auth, admin shell, preview
  Http/Controllers/Admin/Api/ JSON API for the React console
  Http/Middleware/            Active user, redirects, security headers
  Models/                     Eloquent models + relationships
  Observers/                  Sitemap cache invalidation
  Services/
    Media/MediaService.php    Uploads, GD variants, in-use guard
    Pages/                    Renderer, publisher, page service
    Sections/SectionRegistry  Single source of truth for section types
    Seo/                      SeoManager, SitemapBuilder
  Support/                    RichText sanitiser, slug helpers
  Console/Commands/           Scheduled maintenance commands

resources/
  css/app.css                 Public design system (brand tokens)
  css/admin.css               Admin design system
  js/islands/                 React islands mounted in Blade
  js/admin/                   React admin SPA
  views/site/                 Public Blade templates
    sections/                 One partial per builder section type
  views/admin/                Admin shell + auth screens
  views/components/site/      Blade UI components

database/
  migrations/                 Full schema
  seeders/                    Roles, users, settings, navigation, demo content
```

---

## Deployment notes

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and a real `APP_URL`.
2. Configure mail (`MAIL_MAILER=smtp`) — the default is `log`.
3. Run `php artisan migrate --force`, `npm run build`, and `php artisan storage:link`.
4. Cache config for production: `php artisan config:cache route:cache view:cache`.
5. Serve `public/` only; never expose the project root.
6. Change the seeded passwords or remove the demo accounts.
7. Point a cron entry at `php artisan schedule:run`.
