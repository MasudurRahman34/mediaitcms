import { useRef, useState } from 'react';
import { FileText, Upload, X } from 'lucide-react';

type Props = {
  submitUrl?: string;
  jobTitle?: string;
  errors?: Record<string, string[]>;
};

const MAX_BYTES = 10 * 1024 * 1024;
const ACCEPTED = ['.pdf', '.doc', '.docx'];

export default function JobApplicationForm({ submitUrl = '', jobTitle = '', errors = {} }: Props) {
  const [file, setFile] = useState<File | null>(null);
  const [dragging, setDragging] = useState(false);
  const [fileError, setFileError] = useState<string | null>(null);
  const inputRef = useRef<HTMLInputElement>(null);

  const error = (key: string) => errors[key]?.[0];

  const assignFile = (candidate: File | undefined) => {
    if (!candidate) return;

    const extension = `.${candidate.name.split('.').pop()?.toLowerCase() ?? ''}`;

    if (!ACCEPTED.includes(extension)) {
      setFileError('Please upload a PDF, DOC or DOCX file.');
      return;
    }

    if (candidate.size > MAX_BYTES) {
      setFileError('Your CV must be 10MB or smaller.');
      return;
    }

    setFileError(null);

    if (inputRef.current) {
      const transfer = new DataTransfer();
      transfer.items.add(candidate);
      inputRef.current.files = transfer.files;
    }

    setFile(candidate);
  };

  const inputClass = (key: string) =>
    `w-full rounded-xl border bg-near-black/50 px-4 py-3 text-sm text-ink transition placeholder:text-ink/35 focus:outline-none ${
      error(key) ? 'border-red-400/60' : 'border-white/10 focus:border-accent/60'
    }`;

  return (
    <form
      method="POST"
      action={submitUrl}
      encType="multipart/form-data"
      className="glass-card p-6 sm:p-7"
    >
      <input
        type="hidden"
        name="_token"
        value={document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? ''}
      />
      <div className="hidden" aria-hidden="true">
        <label htmlFor="ja-website">Website</label>
        <input id="ja-website" name="website" type="text" tabIndex={-1} autoComplete="off" />
      </div>

      <h2 className="text-lg font-semibold text-ink">Apply for this role</h2>
      <p className="mt-1.5 text-sm text-ink/55">{jobTitle}</p>

      <div className="mt-6 space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label
              htmlFor="ja-first-name"
              className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
            >
              First name <span className="text-accent-soft">*</span>
            </label>
            <input
              id="ja-first-name"
              name="first_name"
              type="text"
              required
              autoComplete="given-name"
              className={inputClass('first_name')}
            />
            {error('first_name') && (
              <p className="mt-1.5 text-xs text-red-300">{error('first_name')}</p>
            )}
          </div>
          <div>
            <label
              htmlFor="ja-last-name"
              className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
            >
              Last name <span className="text-accent-soft">*</span>
            </label>
            <input
              id="ja-last-name"
              name="last_name"
              type="text"
              required
              autoComplete="family-name"
              className={inputClass('last_name')}
            />
            {error('last_name') && (
              <p className="mt-1.5 text-xs text-red-300">{error('last_name')}</p>
            )}
          </div>
        </div>

        <div>
          <label
            htmlFor="ja-email"
            className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
          >
            Email <span className="text-accent-soft">*</span>
          </label>
          <input
            id="ja-email"
            name="email"
            type="email"
            required
            autoComplete="email"
            className={inputClass('email')}
          />
          {error('email') && <p className="mt-1.5 text-xs text-red-300">{error('email')}</p>}
        </div>

        <div className="grid gap-4 sm:grid-cols-2">
          <div>
            <label
              htmlFor="ja-phone"
              className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
            >
              Phone
            </label>
            <input
              id="ja-phone"
              name="phone"
              type="tel"
              autoComplete="tel"
              className={inputClass('phone')}
            />
          </div>
          <div>
            <label
              htmlFor="ja-linkedin"
              className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
            >
              LinkedIn
            </label>
            <input
              id="ja-linkedin"
              name="linkedin_url"
              type="url"
              placeholder="https://"
              className={inputClass('linkedin_url')}
            />
          </div>
        </div>

        <div>
          <label className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase">
            CV <span className="text-accent-soft">*</span>
          </label>
          <div
            onDragOver={(event) => {
              event.preventDefault();
              setDragging(true);
            }}
            onDragLeave={() => setDragging(false)}
            onDrop={(event) => {
              event.preventDefault();
              setDragging(false);
              assignFile(event.dataTransfer.files?.[0]);
            }}
            className={`rounded-xl border border-dashed p-5 text-center transition ${
              dragging ? 'border-accent/70 bg-accent/10' : 'border-white/15 bg-near-black/40'
            }`}
          >
            <input
              ref={inputRef}
              id="ja-cv"
              name="cv"
              type="file"
              accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
              required
              className="sr-only"
              onChange={(event) => assignFile(event.target.files?.[0])}
            />

            {file ? (
              <div className="flex items-center justify-center gap-3 text-sm text-ink">
                <FileText size={18} className="text-accent-soft" />
                <span className="max-w-[16rem] truncate">{file.name}</span>
                <button
                  type="button"
                  onClick={() => {
                    setFile(null);
                    if (inputRef.current) inputRef.current.value = '';
                  }}
                  aria-label="Remove CV"
                  className="text-ink/40 transition hover:text-ink"
                >
                  <X size={16} />
                </button>
              </div>
            ) : (
              <>
                <Upload size={22} className="mx-auto text-ink/40" />
                <p className="mt-3 text-sm text-ink/70">
                  Drag your CV here, or{' '}
                  <button
                    type="button"
                    onClick={() => inputRef.current?.click()}
                    className="font-medium text-accent-soft underline underline-offset-2"
                  >
                    browse files
                  </button>
                </p>
                <p className="mt-1 text-xs text-ink/40">PDF, DOC or DOCX · max 10MB</p>
              </>
            )}
          </div>
          {(fileError || error('cv')) && (
            <p className="mt-1.5 text-xs text-red-300">{fileError ?? error('cv')}</p>
          )}
        </div>

        <div>
          <label
            htmlFor="ja-cover"
            className="mb-1.5 block text-xs font-medium tracking-wide text-ink/55 uppercase"
          >
            Cover note
          </label>
          <textarea
            id="ja-cover"
            name="cover_letter"
            rows={4}
            className={`${inputClass('cover_letter')} resize-y`}
            placeholder="Why this role, and what you would bring…"
          />
          {error('cover_letter') && (
            <p className="mt-1.5 text-xs text-red-300">{error('cover_letter')}</p>
          )}
        </div>

        <label className="flex cursor-pointer items-start gap-3 text-xs leading-relaxed text-ink/55">
          <input
            type="checkbox"
            name="consent"
            value="1"
            required
            className="mt-0.5 size-4 rounded border-white/20 accent-[#3b82f6]"
          />
          <span>
            I consent to Media IT Soft processing my application and storing my CV securely.
            {error('consent') && (
              <span className="mt-1 block text-red-300">{error('consent')}</span>
            )}
          </span>
        </label>
      </div>

      <button
        type="submit"
        className="mt-6 inline-flex w-full items-center justify-center gap-2 rounded-full bg-accent px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-accent-deep"
      >
        Submit application
      </button>
    </form>
  );
}
