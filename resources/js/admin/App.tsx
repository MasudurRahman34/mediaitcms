import { lazy, Suspense } from 'react';
import { Navigate, Route, Routes } from 'react-router-dom';
import { ToastProvider } from './components/ui';
import { AdminLayout } from './components/AdminLayout';
import { DashboardPage } from './pages/Dashboard';

const PagesPage = lazy(() => import('./pages/Pages').then((m) => ({ default: m.PagesPage })));
const PageBuilderPage = lazy(() =>
  import('./pages/PageBuilder').then((m) => ({ default: m.PageBuilderPage })),
);
const ServicesPage = lazy(() =>
  import('./pages/Services').then((m) => ({ default: m.ServicesPage })),
);
const SolutionsPage = lazy(() =>
  import('./pages/Solutions').then((m) => ({ default: m.SolutionsPage })),
);
const ArticlesPage = lazy(() =>
  import('./pages/Articles').then((m) => ({ default: m.ArticlesPage })),
);
const ArticleEditorPage = lazy(() =>
  import('./pages/ArticleEditor').then((m) => ({ default: m.ArticleEditorPage })),
);
const CareersPage = lazy(() => import('./pages/Careers').then((m) => ({ default: m.CareersPage })));
const ApplicationsPage = lazy(() =>
  import('./pages/Applications').then((m) => ({ default: m.ApplicationsPage })),
);
const MediaPage = lazy(() => import('./pages/Media').then((m) => ({ default: m.MediaPage })));
const NavigationPage = lazy(() =>
  import('./pages/Navigation').then((m) => ({ default: m.NavigationPage })),
);
const SettingsPage = lazy(() =>
  import('./pages/Settings').then((m) => ({ default: m.SettingsPage })),
);
const UsersPage = lazy(() => import('./pages/Users').then((m) => ({ default: m.UsersPage })));
const EnquiriesPage = lazy(() =>
  import('./pages/Enquiries').then((m) => ({ default: m.EnquiriesPage })),
);
const AuditLogPage = lazy(() =>
  import('./pages/AuditLog').then((m) => ({ default: m.AuditLogPage })),
);

type Props = {
  user: { id: number; name: string; email: string };
  permissions: string[];
  logoutUrl: string;
};

function RouteFallback() {
  return (
    <div className="flex h-64 items-center justify-center">
      <div className="border-line size-6 animate-spin rounded-full border-2 border-t-accent" />
    </div>
  );
}

export function AdminApp({ user, permissions, logoutUrl }: Props) {
  return (
    <ToastProvider>
      <Suspense fallback={<RouteFallback />}>
        <Routes>
          <Route
            element={<AdminLayout user={user} permissions={permissions} logoutUrl={logoutUrl} />}
          >
            <Route index element={<DashboardPage permissions={permissions} />} />
            <Route path="pages" element={<PagesPage />} />
            <Route path="pages/:id" element={<PageBuilderPage />} />
            <Route path="services" element={<ServicesPage />} />
            <Route path="solutions" element={<SolutionsPage />} />
            <Route path="articles" element={<ArticlesPage />} />
            <Route path="articles/:id" element={<ArticleEditorPage />} />
            <Route path="careers" element={<CareersPage />} />
            <Route path="applications" element={<ApplicationsPage />} />
            <Route path="media" element={<MediaPage />} />
            <Route path="navigation" element={<NavigationPage />} />
            <Route path="settings" element={<SettingsPage />} />
            <Route path="users" element={<UsersPage />} />
            <Route path="enquiries" element={<EnquiriesPage />} />
            <Route path="audit-log" element={<AuditLogPage />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
      </Suspense>
    </ToastProvider>
  );
}
