import { useCallback, useEffect, useRef, useState } from 'react';
import { api } from './api';
import { isApiError } from '@/lib/http';

export function useApi<T>(url: string, params?: Record<string, unknown>, deps: unknown[] = []) {
  const [data, setData] = useState<T | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const paramsKey = JSON.stringify(params ?? {});
  const paramsRef = useRef(params);
  paramsRef.current = params;

  const load = useCallback(async () => {
    setLoading(true);
    setError(null);
    try {
      const result = await api.get<T>(url, paramsRef.current);
      setData(result);
      return result;
    } catch (err) {
      setError(isApiError(err) ? err.message : 'Unable to load data.');
      return null;
    } finally {
      setLoading(false);
    }
  }, [url, paramsKey]);

  useEffect(() => {
    void load();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [load, ...deps]);

  return { data, loading, error, reload: load, setData };
}

/** Extracts Laravel validation errors into a flat field map. */
export function fieldErrors(error: unknown): Record<string, string> {
  if (!isApiError(error) || !error.errors) return {};
  return Object.fromEntries(
    Object.entries(error.errors).map(([key, messages]) => [key, messages[0]]),
  );
}

export function errorMessage(error: unknown, fallback = 'Something went wrong.'): string {
  if (isApiError(error)) return error.message;
  if (error instanceof Error) return error.message;
  return fallback;
}
