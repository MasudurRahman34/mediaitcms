import { useState } from 'react';
import { NavLink, Outlet, useLocation } from 'react-router-dom';
import {
  Briefcase,
  Building2,
  ChartNoAxesColumn,
  FileText,
  Image as ImageIcon,
  Inbox,
  LayoutDashboard,
  LogOut,
  Menu,
  Newspaper,
  PanelTop,
  Settings,
  ShieldCheck,
  Users,
  Wrench,
  X,
} from 'lucide-react';
import { cn } from '@/lib/cn';

type NavEntry = {
  to: string;
  label: string;
  icon: React.ReactNode;
  permission?: string;
  section: 'content' | 'people' | 'system';
  end?: boolean;
};

const entries: NavEntry[] = [
  {
    to: '/',
    label: 'Dashboard',
    icon: <LayoutDashboard size={17} />,
    section: 'content',
    end: true,
  },
  {
    to: '/pages',
    label: 'Pages',
    icon: <PanelTop size={17} />,
    permission: 'pages.manage',
    section: 'content',
  },
  {
    to: '/services',
    label: 'Services',
    icon: <Wrench size={17} />,
    permission: 'content.manage',
    section: 'content',
  },
  {
    to: '/solutions',
    label: 'Solutions',
    icon: <Building2 size={17} />,
    permission: 'content.manage',
    section: 'content',
  },
  {
    to: '/articles',
    label: 'Articles',
    icon: <Newspaper size={17} />,
    permission: 'articles.manage',
    section: 'content',
  },
  {
    to: '/media',
    label: 'Media',
    icon: <ImageIcon size={17} />,
    permission: 'media.manage',
    section: 'content',
  },
  {
    to: '/careers',
    label: 'Jobs',
    icon: <Briefcase size={17} />,
    permission: 'careers.manage',
    section: 'people',
  },
  {
    to: '/applications',
    label: 'Applications',
    icon: <FileText size={17} />,
    permission: 'applications.view',
    section: 'people',
  },
  {
    to: '/enquiries',
    label: 'Enquiries',
    icon: <Inbox size={17} />,
    permission: 'enquiries.view',
    section: 'people',
  },
  {
    to: '/navigation',
    label: 'Navigation',
    icon: <Menu size={17} />,
    permission: 'navigation.manage',
    section: 'system',
  },
  {
    to: '/users',
    label: 'Users & roles',
    icon: <Users size={17} />,
    permission: 'users.manage',
    section: 'system',
  },
  {
    to: '/audit-log',
    label: 'Audit log',
    icon: <ShieldCheck size={17} />,
    permission: 'audit.view',
    section: 'system',
  },
  {
    to: '/settings',
    label: 'Settings',
    icon: <Settings size={17} />,
    permission: 'settings.manage',
    section: 'system',
  },
];

const sections = [
  { id: 'content', label: 'Content' },
  { id: 'people', label: 'People & inbox' },
  { id: 'system', label: 'System' },
] as const;

type Props = {
  user: { id: number; name: string; email: string };
  permissions: string[];
  logoutUrl: string;
};

export function AdminLayout({ user, permissions, logoutUrl }: Props) {
  const [open, setOpen] = useState(false);
  const location = useLocation();

  const allowed = entries.filter(
    (entry) => !entry.permission || permissions.includes(entry.permission),
  );

  const title =
    allowed.find((entry) =>
      entry.end ? location.pathname === '/' : location.pathname.startsWith(entry.to),
    )?.label ?? 'CMS';

  const initials = user.name
    .split(' ')
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');

  const sidebar = (
    <div className="flex h-full flex-col">
      <div className="border-line flex h-16 items-center justify-between border-b px-5">
        <a href="/admin" className="flex items-center gap-2.5">
          <svg className="size-8 text-ink" viewBox="0 0 56 56" fill="none" aria-hidden="true">
            <path
              d="M7 50V21.5a10.5 10.5 0 0 1 21 0V50M28 50V21.5a10.5 10.5 0 0 1 21 0V50"
              stroke="currentColor"
              strokeWidth="9"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          </svg>
          <span className="flex flex-col leading-none">
            <span className="text-sm font-extrabold tracking-tight text-ink">MEDIA</span>
            <span className="mt-0.5 text-[0.5rem] font-semibold tracking-[0.35em] text-accent-soft">
              IT SOFT
            </span>
          </span>
        </a>
        <button
          type="button"
          onClick={() => setOpen(false)}
          className="rounded p-1 text-ink/40 hover:text-ink lg:hidden"
        >
          <X size={18} />
        </button>
      </div>

      <nav className="scrollbar-slim flex-1 overflow-y-auto px-3 py-5">
        {sections.map((section) => {
          const items = allowed.filter((entry) => entry.section === section.id);
          if (!items.length) return null;

          return (
            <div key={section.id} className="mb-6">
              <p className="mb-2 px-3 text-[10px] font-semibold tracking-[0.18em] text-ink/30 uppercase">
                {section.label}
              </p>
              <ul className="space-y-0.5">
                {items.map((entry) => (
                  <li key={entry.to}>
                    <NavLink
                      to={entry.to}
                      end={entry.end}
                      onClick={() => setOpen(false)}
                      className={({ isActive }) =>
                        cn(
                          'flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm transition',
                          isActive
                            ? 'bg-accent/15 font-medium text-ink'
                            : 'text-ink/55 hover:bg-white/5 hover:text-ink',
                        )
                      }
                    >
                      <span className="text-ink/40">{entry.icon}</span>
                      {entry.label}
                    </NavLink>
                  </li>
                ))}
              </ul>
            </div>
          );
        })}
      </nav>

      <div className="border-line border-t p-3">
        <div className="flex items-center gap-3 rounded-lg px-2 py-2">
          <span className="grid size-9 shrink-0 place-items-center rounded-full bg-accent/20 text-xs font-semibold text-accent-soft">
            {initials || 'A'}
          </span>
          <div className="min-w-0 flex-1">
            <p className="truncate text-sm font-medium text-ink">{user.name}</p>
            <p className="truncate text-xs text-ink/40">{user.email}</p>
          </div>
        </div>
        <form method="POST" action={logoutUrl} className="mt-1">
          <input
            type="hidden"
            name="_token"
            value={
              document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''
            }
          />
          <button
            type="submit"
            className="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-ink/50 transition hover:bg-white/5 hover:text-ink"
          >
            <LogOut size={16} />
            Sign out
          </button>
        </form>
      </div>
    </div>
  );

  return (
    <div className="min-h-dvh bg-midnight">
      <aside className="border-line bg-panel fixed inset-y-0 left-0 z-40 hidden w-64 border-r lg:block">
        {sidebar}
      </aside>

      {open && (
        <div className="fixed inset-0 z-50 lg:hidden">
          <div className="absolute inset-0 bg-near-black/70" onClick={() => setOpen(false)} />
          <aside className="border-line bg-panel relative h-full w-72 border-r">{sidebar}</aside>
        </div>
      )}

      <div className="lg:pl-64">
        <header className="border-line sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b bg-midnight/90 px-4 backdrop-blur sm:px-6">
          <div className="flex items-center gap-3">
            <button
              type="button"
              onClick={() => setOpen(true)}
              className="border-line rounded-lg border p-2 text-ink/60 lg:hidden"
              aria-label="Open menu"
            >
              <Menu size={18} />
            </button>
            <div>
              <p className="text-[10px] font-semibold tracking-[0.18em] text-ink/30 uppercase">
                Media IT Soft
              </p>
              <p className="text-sm font-medium text-ink">{title}</p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <a
              href="/"
              target="_blank"
              rel="noopener noreferrer"
              className="border-line inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs text-ink/60 transition hover:border-accent/40 hover:text-ink"
            >
              <ChartNoAxesColumn size={14} />
              View site
            </a>
            <span className="hidden text-xs text-ink/35 sm:inline">v1.0</span>
          </div>
        </header>

        <main className="px-4 py-6 sm:px-6 lg:px-8">
          <Outlet />
        </main>
      </div>
    </div>
  );
}
