import '@fontsource-variable/plus-jakarta-sans';
import { StrictMode } from 'react';
import { createRoot, type Root } from 'react-dom/client';
import type { ComponentType } from 'react';

/**
 * Public site island registry.
 *
 * Blade mounts an island with:
 *   <x-react name="ContactForm" :props="[...]" />
 *
 * Each island is a standalone micro-app; React never takes over the page,
 * so all SEO-critical markup stays server-rendered in the Blade response.
 */
const registry = import.meta.glob<{ default: ComponentType<Record<string, unknown>> }>(
  './islands/*.tsx',
  { eager: true },
);

const islands: Record<string, ComponentType<Record<string, unknown>>> = {};
for (const [path, module] of Object.entries(registry)) {
  const name = path.replace('./islands/', '').replace('.tsx', '');
  islands[name] = module.default;
}

const mounted = new WeakMap<Element, Root>();

function mountIslands(): void {
  document.querySelectorAll<HTMLElement>('[data-react]').forEach((element) => {
    if (mounted.has(element)) return;

    const name = element.dataset.react ?? '';
    const Component = islands[name];

    if (!Component) {
      if (import.meta.env.DEV) {
        console.warn(`[islands] Unknown island "${name}"`);
      }
      return;
    }

    let props: Record<string, unknown> = {};
    try {
      props = element.dataset.props ? JSON.parse(element.dataset.props) : {};
    } catch {
      props = {};
    }

    const root = createRoot(element);
    mounted.set(element, root);
    root.render(
      <StrictMode>
        <Component {...props} />
      </StrictMode>,
    );
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', mountIslands);
} else {
  mountIslands();
}

document.addEventListener('turbo:load', mountIslands);
