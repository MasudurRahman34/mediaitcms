import { useEffect, useRef, useState } from 'react';
import { useInView, useReducedMotion } from 'motion/react';

type Props = {
  value?: string | number;
  suffix?: string;
  duration?: number;
};

function parseValue(raw: string | number) {
  const text = String(raw).trim();
  const match = text.match(/-?[\d.,]+/);
  if (!match) return null;

  const numeric = Number(match[0].replace(/,/g, ''));
  if (!Number.isFinite(numeric)) return null;

  return {
    target: numeric,
    prefix: text.slice(0, match.index ?? 0),
    suffix: text.slice((match.index ?? 0) + match[0].length),
    decimals: (match[0].split('.')[1] ?? '').length,
  };
}

export default function Counter({ value = 0, suffix = '', duration = 1.6 }: Props) {
  const parsed = parseValue(value);
  const ref = useRef<HTMLSpanElement>(null);
  const inView = useInView(ref, { once: true, margin: '-80px' });
  const reduceMotion = useReducedMotion();
  const [display, setDisplay] = useState(parsed ? '0' : String(value));

  useEffect(() => {
    if (!parsed) return;

    if (reduceMotion || !inView) {
      if (reduceMotion) setDisplay(parsed.target.toFixed(parsed.decimals));
      return;
    }

    let frame = 0;
    const start = performance.now();
    const total = duration * 1000;

    const tick = (now: number) => {
      const progress = Math.min((now - start) / total, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      setDisplay((parsed.target * eased).toFixed(parsed.decimals));
      if (progress < 1) frame = requestAnimationFrame(tick);
    };

    frame = requestAnimationFrame(tick);
    return () => cancelAnimationFrame(frame);
  }, [inView, parsed, reduceMotion, duration]);

  if (!parsed)
    return (
      <span ref={ref}>
        {value}
        {suffix}
      </span>
    );

  return (
    <span ref={ref}>
      {parsed.prefix}
      {Number(display).toLocaleString('en-GB', {
        minimumFractionDigits: parsed.decimals,
        maximumFractionDigits: parsed.decimals,
      })}
      {parsed.suffix}
      {suffix}
    </span>
  );
}
