import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useId,
  useMemo,
  useRef,
  useState,
  type ButtonHTMLAttributes,
  type InputHTMLAttributes,
  type ReactNode,
  type SelectHTMLAttributes,
  type TextareaHTMLAttributes,
} from 'react';
import { AlertTriangle, CheckCircle2, Loader2, X } from 'lucide-react';
import { cn } from '@/lib/cn';

/* ---------------------------------------------------------------- Button */

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
  variant?: 'primary' | 'secondary' | 'ghost' | 'danger' | 'subtle';
  size?: 'sm' | 'md';
  loading?: boolean;
  icon?: ReactNode;
};

export function Button({
  variant = 'primary',
  size = 'md',
  loading = false,
  icon,
  className,
  children,
  disabled,
  ...rest
}: ButtonProps) {
  const variants = {
    primary: 'bg-accent text-white hover:bg-accent-deep shadow-sm shadow-accent/20',
    secondary:
      'border border-line bg-panel-raised text-ink hover:border-accent/40 hover:bg-white/[0.06]',
    ghost: 'text-ink/65 hover:bg-white/5 hover:text-ink',
    danger: 'border border-red-500/30 bg-red-500/10 text-red-200 hover:bg-red-500/20',
    subtle: 'bg-white/5 text-ink/80 hover:bg-white/10',
  } as const;

  return (
    <button
      type="button"
      {...rest}
      disabled={disabled || loading}
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition disabled:cursor-not-allowed disabled:opacity-50',
        size === 'sm' ? 'px-3 py-1.5 text-xs' : 'px-4 py-2 text-sm',
        variants[variant],
        className,
      )}
    >
      {loading ? <Loader2 size={15} className="animate-spin" /> : icon}
      {children}
    </button>
  );
}

/* ----------------------------------------------------------------- Inputs */

export function Field({
  label,
  hint,
  error,
  required,
  children,
  className,
}: {
  label?: string;
  hint?: string | null;
  error?: string;
  required?: boolean;
  children: ReactNode;
  className?: string;
}) {
  return (
    <div className={className}>
      {label && (
        <label className="admin-label">
          {label} {required && <span className="text-accent-soft">*</span>}
        </label>
      )}
      {children}
      {error ? (
        <p className="mt-1.5 text-xs text-red-300">{error}</p>
      ) : hint ? (
        <p className="mt-1.5 text-xs text-ink/35">{hint}</p>
      ) : null}
    </div>
  );
}

export function Input({ className, ...rest }: InputHTMLAttributes<HTMLInputElement>) {
  return <input {...rest} className={cn('admin-input', className)} />;
}

export function Textarea({ className, ...rest }: TextareaHTMLAttributes<HTMLTextAreaElement>) {
  return <textarea {...rest} className={cn('admin-input min-h-24 resize-y', className)} />;
}

export function Select({ className, children, ...rest }: SelectHTMLAttributes<HTMLSelectElement>) {
  return (
    <select {...rest} className={cn('admin-input appearance-none', className)}>
      {children}
    </select>
  );
}

export function Toggle({
  checked,
  onChange,
  label,
}: {
  checked: boolean;
  onChange: (value: boolean) => void;
  label?: string;
}) {
  const id = useId();
  return (
    <label htmlFor={id} className="flex cursor-pointer items-center gap-3 text-sm text-ink/70">
      <button
        id={id}
        type="button"
        role="switch"
        aria-checked={checked}
        onClick={() => onChange(!checked)}
        className={cn(
          'relative h-6 w-11 shrink-0 rounded-full transition',
          checked ? 'bg-accent' : 'bg-white/15',
        )}
      >
        <span
          className={cn(
            'absolute top-1 left-1 size-4 rounded-full bg-white transition',
            checked && 'translate-x-5',
          )}
        />
      </button>
      {label}
    </label>
  );
}

/* ------------------------------------------------------------------ Badge */

export function Badge({
  children,
  tone = 'neutral',
}: {
  children: ReactNode;
  tone?: 'neutral' | 'success' | 'warning' | 'danger' | 'accent';
}) {
  const tones = {
    neutral: 'border-line bg-white/5 text-ink/60',
    success: 'border-emerald-400/25 bg-emerald-500/10 text-emerald-200',
    warning: 'border-amber-400/25 bg-amber-500/10 text-amber-200',
    danger: 'border-red-400/25 bg-red-500/10 text-red-200',
    accent: 'border-accent/30 bg-accent/10 text-accent-soft',
  } as const;

  return (
    <span
      className={cn(
        'inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium',
        tones[tone],
      )}
    >
      {children}
    </span>
  );
}

/* ------------------------------------------------------------- Page header */

export function PageHeader({
  title,
  description,
  actions,
}: {
  title: string;
  description?: string;
  actions?: ReactNode;
}) {
  return (
    <div className="border-line flex flex-col gap-4 border-b pb-6 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <h1 className="text-2xl font-semibold text-ink">{title}</h1>
        {description && <p className="mt-1.5 text-sm text-ink/50">{description}</p>}
      </div>
      {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
    </div>
  );
}

/* ------------------------------------------------------------- Empty state */

export function EmptyState({
  title,
  description,
  action,
}: {
  title: string;
  description?: string;
  action?: ReactNode;
}) {
  return (
    <div className="border-line flex flex-col items-center justify-center rounded-xl border border-dashed px-6 py-16 text-center">
      <p className="text-sm font-medium text-ink/70">{title}</p>
      {description && <p className="mt-2 max-w-sm text-sm text-ink/40">{description}</p>}
      {action && <div className="mt-5">{action}</div>}
    </div>
  );
}

export function Spinner({ className }: { className?: string }) {
  return <Loader2 className={cn('animate-spin text-ink/40', className)} size={20} />;
}

/* ------------------------------------------------------------------ Modal */

export function Modal({
  open,
  onClose,
  title,
  children,
  footer,
  size = 'md',
}: {
  open: boolean;
  onClose: () => void;
  title: string;
  children: ReactNode;
  footer?: ReactNode;
  size?: 'sm' | 'md' | 'lg' | 'xl';
}) {
  useEffect(() => {
    if (!open) return;

    const onKey = (event: KeyboardEvent) => {
      if (event.key === 'Escape') onClose();
    };

    document.body.style.overflow = 'hidden';
    window.addEventListener('keydown', onKey);

    return () => {
      document.body.style.overflow = '';
      window.removeEventListener('keydown', onKey);
    };
  }, [open, onClose]);

  if (!open) return null;

  const widths = { sm: 'max-w-md', md: 'max-w-xl', lg: 'max-w-3xl', xl: 'max-w-5xl' };

  return (
    <div
      className="fixed inset-0 z-[70] flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
    >
      <div className="absolute inset-0 bg-near-black/80 backdrop-blur-sm" onClick={onClose} />
      <div
        className={cn(
          'border-line bg-panel relative flex max-h-[90vh] w-full flex-col rounded-2xl border shadow-2xl',
          widths[size],
        )}
      >
        <div className="border-line flex items-center justify-between border-b px-5 py-4">
          <h2 className="text-base font-semibold text-ink">{title}</h2>
          <button
            type="button"
            onClick={onClose}
            aria-label="Close"
            className="rounded-lg p-1.5 text-ink/40 transition hover:bg-white/5 hover:text-ink"
          >
            <X size={18} />
          </button>
        </div>
        <div className="scrollbar-slim flex-1 overflow-y-auto px-5 py-5">{children}</div>
        {footer && (
          <div className="border-line flex items-center justify-end gap-2 border-t px-5 py-4">
            {footer}
          </div>
        )}
      </div>
    </div>
  );
}

/* ----------------------------------------------------------------- Toasts */

type Toast = { id: number; message: string; tone: 'success' | 'error' | 'info' };

type ToastContextValue = {
  push: (message: string, tone?: Toast['tone']) => void;
};

const ToastContext = createContext<ToastContextValue>({ push: () => {} });

export function useToast() {
  return useContext(ToastContext);
}

export function ToastProvider({ children }: { children: ReactNode }) {
  const [toasts, setToasts] = useState<Toast[]>([]);
  const counter = useRef(0);

  const push = useCallback((message: string, tone: Toast['tone'] = 'success') => {
    const id = ++counter.current;
    setToasts((current) => [...current, { id, message, tone }]);
    window.setTimeout(() => {
      setToasts((current) => current.filter((toast) => toast.id !== id));
    }, 4200);
  }, []);

  const value = useMemo(() => ({ push }), [push]);

  return (
    <ToastContext.Provider value={value}>
      {children}
      <div className="pointer-events-none fixed right-4 bottom-4 z-[80] flex w-full max-w-sm flex-col gap-2">
        {toasts.map((toast) => (
          <div
            key={toast.id}
            role="status"
            className={cn(
              'pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 text-sm shadow-xl backdrop-blur',
              toast.tone === 'success' &&
                'border-emerald-400/30 bg-emerald-500/10 text-emerald-100',
              toast.tone === 'error' && 'border-red-400/30 bg-red-500/10 text-red-100',
              toast.tone === 'info' && 'border-line bg-panel-raised text-ink/80',
            )}
          >
            {toast.tone === 'success' ? (
              <CheckCircle2 size={17} className="mt-0.5 shrink-0" />
            ) : toast.tone === 'error' ? (
              <AlertTriangle size={17} className="mt-0.5 shrink-0" />
            ) : null}
            <span>{toast.message}</span>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
}

/* --------------------------------------------------------------- Confirm */

export function ConfirmDialog({
  open,
  title,
  message,
  confirmLabel = 'Delete',
  onConfirm,
  onCancel,
  loading,
}: {
  open: boolean;
  title: string;
  message: string;
  confirmLabel?: string;
  onConfirm: () => void;
  onCancel: () => void;
  loading?: boolean;
}) {
  return (
    <Modal
      open={open}
      onClose={onCancel}
      title={title}
      size="sm"
      footer={
        <>
          <Button variant="ghost" onClick={onCancel}>
            Cancel
          </Button>
          <Button variant="danger" onClick={onConfirm} loading={loading}>
            {confirmLabel}
          </Button>
        </>
      }
    >
      <p className="text-sm leading-relaxed text-ink/65">{message}</p>
    </Modal>
  );
}

/* ------------------------------------------------------------------ Tabs */

export function Tabs({
  tabs,
  active,
  onChange,
}: {
  tabs: Array<{ id: string; label: string }>;
  active: string;
  onChange: (id: string) => void;
}) {
  return (
    <div className="border-line flex gap-1 overflow-x-auto border-b" role="tablist">
      {tabs.map((tab) => (
        <button
          key={tab.id}
          type="button"
          role="tab"
          aria-selected={active === tab.id}
          onClick={() => onChange(tab.id)}
          className={cn(
            'border-b-2 px-3.5 py-2.5 text-sm font-medium whitespace-nowrap transition',
            active === tab.id
              ? 'border-accent text-ink'
              : 'border-transparent text-ink/45 hover:text-ink/80',
          )}
        >
          {tab.label}
        </button>
      ))}
    </div>
  );
}
