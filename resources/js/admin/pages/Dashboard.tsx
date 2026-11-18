import { Link } from 'react-router-dom';
import {
  ArrowUpRight,
  Briefcase,
  Building2,
  FileText,
  Inbox,
  Newspaper,
  PanelTop,
  Users,
  Wrench,
} from 'lucide-react';
import { endpoints } from '../lib/api';
import { useApi } from '../lib/useApi';
import { Badge, PageHeader, Spinner } from '../components/ui';
import { relativeTime } from '@/lib/format';

type DashboardData = {
  stats: {
    pages: number;
    articles: number;
    services: number;
    solutions: number;
    active_jobs: number;
    applications: number;
    new_applications: number;
    enquiries: number;
  };
  recent_enquiries: Array<{
    id: number;
    name: string;
    email: string;
    subject: string | null;
    status: string;
    created_at: string;
  }>;
  recent_applications: Array<{
    id: number;
    first_name: string;
    last_name: string;
    email: string;
    status: string;
    created_at: string;
    job?: { title: string } | null;
  }>;
  open_jobs: Array<{
    id: number;
    title: string;
    location: string | null;
    closing_at: string | null;
  }>;
  recent_pages: Array<{
    id: number;
    title: string;
    slug: string;
    status: string;
    updated_at: string;
  }>;
};

export function DashboardPage({ permissions }: { permissions: string[] }) {
  const { data, loading } = useApi<DashboardData>(endpoints.dashboard);
  const can = (permission: string) => permissions.includes(permission);

  const cards = data
    ? [
        {
          label: 'Published pages',
          value: data.stats.pages,
          icon: <PanelTop size={18} />,
          href: '/pages',
          show: can('pages.manage'),
        },
        {
          label: 'Articles',
          value: data.stats.articles,
          icon: <Newspaper size={18} />,
          href: '/articles',
          show: can('articles.manage'),
        },
        {
          label: 'Services',
          value: data.stats.services,
          icon: <Wrench size={18} />,
          href: '/services',
          show: can('content.manage'),
        },
        {
          label: 'Solutions',
          value: data.stats.solutions,
          icon: <Building2 size={18} />,
          href: '/solutions',
          show: can('content.manage'),
        },
        {
          label: 'Active jobs',
          value: data.stats.active_jobs,
          icon: <Briefcase size={18} />,
          href: '/careers',
          show: can('careers.manage'),
        },
        {
          label: 'Applications',
          value: data.stats.applications,
          icon: <FileText size={18} />,
          href: '/applications',
          show: can('applications.view'),
        },
        {
          label: 'New enquiries',
          value: data.stats.enquiries,
          icon: <Inbox size={18} />,
          href: '/enquiries',
          show: can('enquiries.view'),
        },
      ].filter((card) => card.show)
    : [];

  if (loading && !data) {
    return (
      <div className="flex h-64 items-center justify-center">
        <Spinner />
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <PageHeader
        title="Dashboard"
        description="A snapshot of what is live and what needs attention."
      />

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {cards.map((card) => (
          <Link
            key={card.label}
            to={card.href}
            className="admin-card group p-5 transition hover:border-accent/30"
          >
            <div className="flex items-center justify-between">
              <span className="inline-flex size-9 items-center justify-center rounded-lg bg-accent/10 text-accent-soft">
                {card.icon}
              </span>
              <ArrowUpRight
                size={15}
                className="text-ink/20 transition group-hover:text-accent-soft"
              />
            </div>
            <p className="mt-4 text-3xl font-semibold text-ink">{card.value}</p>
            <p className="mt-1 text-xs text-ink/45">{card.label}</p>
          </Link>
        ))}
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <section className="admin-card">
          <header className="border-line flex items-center justify-between border-b px-5 py-4">
            <h2 className="text-sm font-semibold text-ink">Recent enquiries</h2>
            {can('enquiries.view') && (
              <Link to="/enquiries" className="text-xs text-accent-soft hover:text-ink">
                View all
              </Link>
            )}
          </header>
          <div className="divide-line/70 divide-y">
            {data?.recent_enquiries.length ? (
              data.recent_enquiries.map((enquiry) => (
                <div
                  key={enquiry.id}
                  className="flex items-center justify-between gap-3 px-5 py-3.5"
                >
                  <div className="min-w-0">
                    <p className="truncate text-sm font-medium text-ink">{enquiry.name}</p>
                    <p className="truncate text-xs text-ink/40">
                      {enquiry.subject ?? enquiry.email}
                    </p>
                  </div>
                  <div className="flex shrink-0 items-center gap-3">
                    <Badge tone={enquiry.status === 'new' ? 'accent' : 'neutral'}>
                      {enquiry.status}
                    </Badge>
                    <span className="text-[11px] text-ink/30">
                      {relativeTime(enquiry.created_at)}
                    </span>
                  </div>
                </div>
              ))
            ) : (
              <p className="px-5 py-8 text-center text-sm text-ink/35">No enquiries yet.</p>
            )}
          </div>
        </section>

        <section className="admin-card">
          <header className="border-line flex items-center justify-between border-b px-5 py-4">
            <h2 className="text-sm font-semibold text-ink">Recent applications</h2>
            {can('applications.view') && (
              <Link to="/applications" className="text-xs text-accent-soft hover:text-ink">
                View all
              </Link>
            )}
          </header>
          <div className="divide-line/70 divide-y">
            {data?.recent_applications.length ? (
              data.recent_applications.map((application) => (
                <div
                  key={application.id}
                  className="flex items-center justify-between gap-3 px-5 py-3.5"
                >
                  <div className="min-w-0">
                    <p className="truncate text-sm font-medium text-ink">
                      {application.first_name} {application.last_name}
                    </p>
                    <p className="truncate text-xs text-ink/40">
                      {application.job?.title ?? application.email}
                    </p>
                  </div>
                  <div className="flex shrink-0 items-center gap-3">
                    <Badge tone={application.status === 'new' ? 'accent' : 'neutral'}>
                      {application.status}
                    </Badge>
                    <span className="text-[11px] text-ink/30">
                      {relativeTime(application.created_at)}
                    </span>
                  </div>
                </div>
              ))
            ) : (
              <p className="px-5 py-8 text-center text-sm text-ink/35">No applications yet.</p>
            )}
          </div>
        </section>

        <section className="admin-card">
          <header className="border-line flex items-center justify-between border-b px-5 py-4">
            <h2 className="text-sm font-semibold text-ink">Open roles</h2>
            {can('careers.manage') && (
              <Link to="/careers" className="text-xs text-accent-soft hover:text-ink">
                Manage jobs
              </Link>
            )}
          </header>
          <div className="divide-line/70 divide-y">
            {data?.open_jobs.length ? (
              data.open_jobs.map((job) => (
                <div key={job.id} className="flex items-center justify-between gap-3 px-5 py-3.5">
                  <div className="min-w-0">
                    <p className="truncate text-sm font-medium text-ink">{job.title}</p>
                    <p className="truncate text-xs text-ink/40">{job.location ?? 'Remote / UK'}</p>
                  </div>
                  <span className="shrink-0 text-[11px] text-ink/30">
                    {job.closing_at
                      ? `Closes ${new Date(job.closing_at).toLocaleDateString('en-GB')}`
                      : 'No closing date'}
                  </span>
                </div>
              ))
            ) : (
              <p className="px-5 py-8 text-center text-sm text-ink/35">No open vacancies.</p>
            )}
          </div>
        </section>

        <section className="admin-card">
          <header className="border-line flex items-center justify-between border-b px-5 py-4">
            <h2 className="text-sm font-semibold text-ink">Recently updated pages</h2>
            {can('pages.manage') && (
              <Link to="/pages" className="text-xs text-accent-soft hover:text-ink">
                All pages
              </Link>
            )}
          </header>
          <div className="divide-line/70 divide-y">
            {data?.recent_pages.length ? (
              data.recent_pages.map((page) => (
                <Link
                  key={page.id}
                  to={`/pages/${page.id}`}
                  className="flex items-center justify-between gap-3 px-5 py-3.5 transition hover:bg-white/[0.025]"
                >
                  <div className="min-w-0">
                    <p className="truncate text-sm font-medium text-ink">{page.title}</p>
                    <p className="truncate text-xs text-ink/40">/{page.slug}</p>
                  </div>
                  <Badge tone={page.status === 'published' ? 'success' : 'warning'}>
                    {page.status}
                  </Badge>
                </Link>
              ))
            ) : (
              <p className="px-5 py-8 text-center text-sm text-ink/35">No pages yet.</p>
            )}
          </div>
        </section>
      </div>

      <p className="flex items-center gap-2 text-xs text-ink/30">
        <Users size={14} />
        Signed in as an administrator. Changes are recorded in the audit log.
      </p>
    </div>
  );
}
