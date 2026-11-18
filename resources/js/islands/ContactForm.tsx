import { useState } from 'react';
import { CheckCircle2, Send } from 'lucide-react';

type Props = {
  submitUrl?: string;
  errors?: Record<string, string[]>;
  old?: Record<string, string | null>;
};

type Field = 'name' | 'email' | 'phone' | 'company' | 'subject' | 'message';

const fields: Array<{
  name: Field;
  label: string;
  type: string;
  required?: boolean;
  span?: boolean;
}> = [
  { name: 'name', label: 'Full name', type: 'text', required: true },
  { name: 'email', label: 'Email address', type: 'email', required: true },
  { name: 'phone', label: 'Phone', type: 'tel' },
  { name: 'company', label: 'Company', type: 'text' },
  { name: 'subject', label: 'How can we help?', type: 'text', span: true },
];

export default function ContactForm({ submitUrl = '/contact', errors = {}, old = {} }: Props) {
  const [consent, setConsent] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const error = (key: string) => errors[key]?.[0];

  const inputClass = (key: string) =>
    `w-full rounded-xl border bg-near-black/50 px-4 py-3 text-sm text-ink transition placeholder:text-ink/35 focus:outline-none ${
      error(key) ? 'border-red-400/60' : 'border-white/10 focus:border-accent/60'
    }`;

  return (
    <form
      method="POST"
      action={submitUrl}
      onSubmit={() => setSubmitting(true)}
      className="glass-card p-6 sm:p-8"
      noValidate={false}
    >
      <input
        type="hidden"
        name="_token"
        value={document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''}
      />
      <div className="hidden" aria-hidden="true">
        <label htmlFor="cf-website">Website</label>
        <input id="cf-website" name="website" type="text" tabIndex={-1} autoComplete="off" />
      </div>

      <div className="grid gap-5 sm:grid-cols-2">
        {fields.map((field) => (
          <div key={field.name} className={field.span ? 'sm:col-span-2' : ''}>
            <label
              htmlFor={`cf-${field.name}`}
              className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
            >
              {field.label} {field.required && <span className="text-accent-soft">*</span>}
            </label>
            <input
              id={`cf-${field.name}`}
              name={field.name}
              type={field.type}
              required={field.required}
              defaultValue={old[field.name] ?? ''}
              aria-invalid={Boolean(error(field.name))}
              aria-describedby={error(field.name) ? `cf-${field.name}-error` : undefined}
              className={inputClass(field.name)}
              autoComplete={
                field.name === 'name' ? 'name' : field.name === 'email' ? 'email' : undefined
              }
            />
            {error(field.name) && (
              <p id={`cf-${field.name}-error`} className="mt-1.5 text-xs text-red-300">
                {error(field.name)}
              </p>
            )}
          </div>
        ))}

        <div className="sm:col-span-2">
          <label
            htmlFor="cf-message"
            className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
          >
            Message <span className="text-accent-soft">*</span>
          </label>
          <textarea
            id="cf-message"
            name="message"
            rows={5}
            required
            defaultValue={old.message ?? ''}
            aria-invalid={Boolean(error('message'))}
            aria-describedby={error('message') ? 'cf-message-error' : undefined}
            className={`${inputClass('message')} resize-y`}
            placeholder="Tell us about your project, timeline and goals…"
          />
          {error('message') && (
            <p id="cf-message-error" className="mt-1.5 text-xs text-red-300">
              {error('message')}
            </p>
          )}
        </div>
      </div>

      <label className="mt-6 flex cursor-pointer items-start gap-3 text-xs leading-relaxed text-ink/55">
        <input
          type="checkbox"
          name="consent"
          value="1"
          checked={consent}
          onChange={(event) => setConsent(event.target.checked)}
          required
          className="mt-0.5 size-4 rounded border-white/20 bg-transparent accent-[#3b82f6]"
        />
        <span>
          I agree to Media IT Soft storing my details to respond to this enquiry.
          {error('consent') && <span className="mt-1 block text-red-300">{error('consent')}</span>}
        </span>
      </label>

      <button
        type="submit"
        disabled={submitting || !consent}
        className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-accent-deep disabled:cursor-not-allowed disabled:opacity-50"
      >
        {submitting ? 'Sending…' : 'Send enquiry'}
        {submitting ? <CheckCircle2 size={16} /> : <Send size={16} />}
      </button>

      <p className="mt-4 text-center text-xs text-ink/35">
        We reply within one working day. Your details are never shared.
      </p>
    </form>
  );
}
