import '@fontsource-variable/plus-jakarta-sans';
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import { AdminApp } from './admin/App';

const container = document.getElementById('admin-root');

if (!container) {
  throw new Error('Admin root element not found');
}

const user = JSON.parse(container.dataset.user ?? '{}') as {
  id: number;
  name: string;
  email: string;
};

const permissions = JSON.parse(container.dataset.permissions ?? '[]') as string[];

createRoot(container).render(
  <StrictMode>
    <BrowserRouter basename="/admin">
      <AdminApp
        user={user}
        permissions={permissions}
        logoutUrl={container.dataset.logoutUrl ?? '/admin/logout'}
      />
    </BrowserRouter>
  </StrictMode>,
);
