import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { AnimatePresence, motion, useReducedMotion } from 'motion/react';
import { ChevronDown, Menu, X } from 'lucide-react';

type NavItem = {
  label: string;
  url: string;
  external?: boolean;
  children?: NavItem[];
};

type Props = {
  items?: NavItem[];
  ctaUrl?: string;
};

export default function MobileNav({ items = [], ctaUrl = '/contact' }: Props) {
  const [open, setOpen] = useState(false);
  const [expanded, setExpanded] = useState<string | null>(null);
  const [mounted, setMounted] = useState(false);
  const closeRef = useRef<HTMLButtonElement>(null);
  const triggerRef = useRef<HTMLButtonElement>(null);
  const reduceMotion = useReducedMotion();

  // Portals need a DOM target, which only exists after mount.
  useEffect(() => setMounted(true), []);

  useEffect(() => {
    if (!open) return;

    const { overflow } = document.body.style;
    document.body.style.overflow = 'hidden';
    setExpanded(null);

    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') setOpen(false);
    };

    window.addEventListener('keydown', onKey);
    closeRef.current?.focus();

    return () => {
      document.body.style.overflow = overflow;
      window.removeEventListener('keydown', onKey);
    };
  }, [open]);

  // Return focus to the trigger when the panel closes.
  useEffect(() => {
    if (!open && mounted) {
      triggerRef.current?.focus({ preventScroll: true });
    }
  }, [open, mounted]);

  const panel = (
    <AnimatePresence>
      {open && (
        <motion.div
          initial={reduceMotion ? false : { opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.2 }}
          className="fixed inset-0 z-[100] bg-near-black/95 backdrop-blur-xl lg:hidden"
          role="dialog"
          aria-modal="true"
          aria-label="Site menu"
        >
          <div className="flex h-dvh flex-col">
            <div className="flex h-20 shrink-0 items-center justify-between px-5">
              <span className="text-sm font-semibold tracking-[0.3em] text-ink/50 uppercase">
                Menu
              </span>
              <button
                ref={closeRef}
                type="button"
                onClick={() => setOpen(false)}
                aria-label="Close menu"
                className="inline-flex size-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-ink transition hover:bg-white/10"
              >
                <X size={20} />
              </button>
            </div>

            <nav className="flex-1 overflow-y-auto px-5 pb-8" aria-label="Mobile navigation">
              <ul className="divide-y divide-white/10">
                {items.map((item) => {
                  const hasChildren = Boolean(item.children?.length);
                  const isExpanded = expanded === item.label;

                  return (
                    <li key={item.label} className="py-1">
                      {hasChildren ? (
                        <>
                          <button
                            type="button"
                            onClick={() => setExpanded(isExpanded ? null : item.label)}
                            aria-expanded={isExpanded}
                            className="flex w-full items-center justify-between py-4 text-left text-lg font-medium text-ink"
                          >
                            {item.label}
                            <ChevronDown
                              size={18}
                              className={`text-ink/40 transition ${isExpanded ? 'rotate-180' : ''}`}
                            />
                          </button>
                          {isExpanded && (
                            <ul className="mb-2 space-y-1 border-l border-white/10 pl-4">
                              {item.children?.map((child) => (
                                <li key={child.label}>
                                  <a
                                    href={child.url}
                                    target={child.external ? '_blank' : undefined}
                                    rel={child.external ? 'noopener noreferrer' : undefined}
                                    className="block py-2.5 text-base text-ink/60 transition hover:text-ink"
                                  >
                                    {child.label}
                                  </a>
                                </li>
                              ))}
                            </ul>
                          )}
                        </>
                      ) : (
                        <a
                          href={item.url}
                          target={item.external ? '_blank' : undefined}
                          rel={item.external ? 'noopener noreferrer' : undefined}
                          onClick={() => setOpen(false)}
                          className="block py-4 text-lg font-medium text-ink transition hover:text-accent-soft"
                        >
                          {item.label}
                        </a>
                      )}
                    </li>
                  );
                })}
              </ul>

              <a
                href={ctaUrl}
                onClick={() => setOpen(false)}
                className="mt-8 flex w-full items-center justify-center rounded-full bg-accent px-6 py-4 text-sm font-semibold text-white"
              >
                Get in touch
              </a>
            </nav>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );

  return (
    <div className="lg:hidden">
      <button
        ref={triggerRef}
        type="button"
        onClick={() => setOpen(true)}
        aria-label="Open menu"
        aria-expanded={open}
        aria-haspopup="dialog"
        className="inline-flex size-11 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-ink transition hover:bg-white/10"
      >
        <Menu size={20} />
      </button>

      {/*
        Rendered through a portal: the sticky header uses backdrop-filter,
        which establishes a containing block for position: fixed descendants
        and would otherwise clip this overlay to the header's height.
      */}
      {mounted && createPortal(panel, document.body)}
    </div>
  );
}
