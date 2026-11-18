export type ApiError = {
  message: string;
  errors?: Record<string, string[]>;
  status: number;
};

function csrfToken(): string {
  const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
  return meta?.content ?? '';
}

async function parse(response: Response): Promise<unknown> {
  const text = await response.text();
  if (!text) return null;
  try {
    return JSON.parse(text);
  } catch {
    return text;
  }
}

function toError(status: number, payload: unknown): ApiError {
  const body = payload as { message?: string; errors?: Record<string, string[]> } | null;
  return {
    status,
    message: body?.message ?? 'Something went wrong. Please try again.',
    errors: body?.errors,
  };
}

export async function apiGet<T>(url: string, params?: Record<string, unknown>): Promise<T> {
  const query = params
    ? `?${new URLSearchParams(
        Object.entries(params)
          .filter(([, v]) => v !== undefined && v !== null && v !== '')
          .map(([k, v]) => [k, String(v)]),
      ).toString()}`
    : '';
  const response = await fetch(`${url}${query}`, {
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    credentials: 'same-origin',
  });
  const payload = await parse(response);
  if (!response.ok) throw toError(response.status, payload);
  return payload as T;
}

export async function apiSend<T>(
  method: 'POST' | 'PUT' | 'PATCH' | 'DELETE',
  url: string,
  body?: unknown,
): Promise<T> {
  const isForm = body instanceof FormData;
  const response = await fetch(url, {
    method,
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': csrfToken(),
      ...(isForm ? {} : { 'Content-Type': 'application/json' }),
    },
    body: isForm ? body : body === undefined ? undefined : JSON.stringify(body),
  });
  const payload = await parse(response);
  if (!response.ok) throw toError(response.status, payload);
  return payload as T;
}

export function isApiError(error: unknown): error is ApiError {
  return typeof error === 'object' && error !== null && 'status' in error && 'message' in error;
}
