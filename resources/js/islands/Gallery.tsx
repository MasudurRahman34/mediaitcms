import { useCallback, useEffect, useState } from 'react';
import { AnimatePresence, motion, useReducedMotion } from 'motion/react';
import { ChevronLeft, ChevronRight, X } from 'lucide-react';

type GalleryItem = {
  src: string;
  thumb?: string;
  alt?: string;
  caption?: string | null;
};

type Props = {
  items?: GalleryItem[];
};

export default function Gallery({ items = [] }: Props) {
  const [active, setActive] = useState<number | null>(null);
  const reduceMotion = useReducedMotion();

  const close = useCallback(() => setActive(null), []);
  const next = useCallback(
    () => setActive((current) => (current === null ? null : (current + 1) % items.length)),
    [items.length],
  );
  const previous = useCallback(
    () =>
      setActive((current) =>
        current === null ? null : (current - 1 + items.length) % items.length,
      ),
    [items.length],
  );

  useEffect(() => {
    if (active === null) return;

    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') close();
      if (event.key === 'ArrowRight') next();
      if (event.key === 'ArrowLeft') previous();
    };

    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', onKey);

    return () => {
      document.body.style.overflow = '';
      window.removeEventListener('keydown', onKey);
    };
  }, [active, close, next, previous]);

  if (!items.length) return null;

  return (
    <>
      <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {items.map((item, index) => (
          <li key={`${item.src}-${index}`}>
            <button
              type="button"
              onClick={() => setActive(index)}
              className="group relative block w-full overflow-hidden rounded-2xl border border-white/10 bg-surface-800"
              aria-label={`Open image ${index + 1}${item.caption ? `: ${item.caption}` : ''}`}
            >
              <img
                src={item.thumb ?? item.src}
                alt={item.alt ?? ''}
                loading="lazy"
                decoding="async"
                className="aspect-[16/10] w-full object-cover transition duration-700 group-hover:scale-[1.03]"
              />
              {item.caption && (
                <span className="absolute inset-x-0 bottom-0 bg-gradient-to-t from-near-black/90 to-transparent p-4 text-left text-xs font-medium text-ink/85">
                  {item.caption}
                </span>
              )}
            </button>
          </li>
        ))}
      </ul>

      <AnimatePresence>
        {active !== null && items[active] && (
          <motion.div
            initial={reduceMotion ? false : { opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[90] flex items-center justify-center bg-near-black/95 p-4 backdrop-blur-md"
            role="dialog"
            aria-modal="true"
            aria-label="Image viewer"
            onClick={close}
          >
            <button
              type="button"
              onClick={close}
              aria-label="Close image viewer"
              className="absolute top-5 right-5 inline-flex size-11 items-center justify-center rounded-full border border-white/15 bg-white/5 text-ink transition hover:bg-white/10"
            >
              <X size={20} />
            </button>

            {items.length > 1 && (
              <>
                <button
                  type="button"
                  onClick={(event) => {
                    event.stopPropagation();
                    previous();
                  }}
                  aria-label="Previous image"
                  className="absolute left-3 inline-flex size-11 items-center justify-center rounded-full border border-white/15 bg-white/5 text-ink transition hover:bg-white/10 sm:left-6"
                >
                  <ChevronLeft size={22} />
                </button>
                <button
                  type="button"
                  onClick={(event) => {
                    event.stopPropagation();
                    next();
                  }}
                  aria-label="Next image"
                  className="absolute right-3 inline-flex size-11 items-center justify-center rounded-full border border-white/15 bg-white/5 text-ink transition hover:bg-white/10 sm:right-6"
                >
                  <ChevronRight size={22} />
                </button>
              </>
            )}

            <motion.figure
              initial={reduceMotion ? false : { scale: 0.97, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ opacity: 0 }}
              transition={{ duration: 0.2 }}
              className="max-h-[86vh] w-full max-w-6xl"
              onClick={(event) => event.stopPropagation()}
            >
              <img
                src={items[active].src}
                alt={items[active].alt ?? ''}
                className="mx-auto max-h-[78vh] w-auto rounded-2xl object-contain shadow-2xl"
              />
              {(items[active].caption || items[active].alt) && (
                <figcaption className="mt-4 text-center text-sm text-ink/60">
                  {items[active].caption ?? items[active].alt}
                  <span className="ml-3 text-xs text-ink/35">
                    {active + 1} / {items.length}
                  </span>
                </figcaption>
              )}
            </motion.figure>
          </motion.div>
        )}
      </AnimatePresence>
    </>
  );
}
