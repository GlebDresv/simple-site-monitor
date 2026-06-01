import { HttpError, type Options } from 'ra-core';
import { fetchCredentials } from './client';

function parseResponseBody(body: string): unknown {
  if (!body) {
    return {};
  }

  try {
    return JSON.parse(body);
  } catch {
    return undefined;
  }
}

function getErrorMessage(json: unknown, statusText: string): string {
  if (json && typeof json === 'object' && 'message' in json) {
    const message = (json as { message: unknown }).message;

    if (typeof message === 'string' && message.length > 0) {
      return message;
    }
  }

  return statusText || 'Request failed';
}

function normalizeLaravelErrors(errors: unknown): Record<string, string> | undefined {
  if (!errors || typeof errors !== 'object' || Array.isArray(errors)) {
    return undefined;
  }

  const normalized: Record<string, string> = {};

  for (const [field, value] of Object.entries(errors)) {
    if (Array.isArray(value)) {
      normalized[field] = value.map(String).join(' ');
    } else if (typeof value === 'string') {
      normalized[field] = value;
    }
  }

  return Object.keys(normalized).length > 0 ? normalized : undefined;
}

function normalizeErrorBody(json: unknown): unknown {
  if (!json || typeof json !== 'object') {
    return json;
  }

  const body = { ...(json as Record<string, unknown>) };
  const errors = normalizeLaravelErrors(body.errors);

  if (errors) {
    body.errors = errors;
  }

  return body;
}

export async function httpClient(url: string, options: Options = {}) {
  const path = url.replace(/^\/api/, '');

  const response = await fetchCredentials(path, {
    method: options.method,
    body: options.body as BodyInit | null | undefined,
    headers: options.headers,
    signal: options.signal,
  });

  const body = await response.text();
  const json = parseResponseBody(body);

  if (response.status < 200 || response.status >= 300) {
    throw new HttpError(
      getErrorMessage(json, response.statusText),
      response.status,
      normalizeErrorBody(json),
    );
  }

  return {
    status: response.status,
    headers: response.headers,
    body,
    json,
  };
}
