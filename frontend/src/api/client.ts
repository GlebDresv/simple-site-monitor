const API_URL = '/api';

function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[2]) : null;
}

export async function getCsrfCookie(): Promise<void> {
  await fetch(`${API_URL}/sanctum/csrf-cookie`, {
    credentials: 'include',
  });
}

export async function fetchCredentials(
  path: string,
  options: RequestInit = {},
): Promise<Response> {
  const headers = new Headers(options.headers);

  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json');
  }

  if (options.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const xsrfToken = getCookie('XSRF-TOKEN');
  if (xsrfToken && !headers.has('X-XSRF-TOKEN')) {
    headers.set('X-XSRF-TOKEN', xsrfToken);
  }

  return fetch(`${API_URL}${path}`, {
    ...options,
    headers,
    credentials: 'include',
  });
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
}

export async function loginUser(email: string, password: string): Promise<AuthUser> {
  await getCsrfCookie();

  const response = await fetchCredentials('/login', {
    method: 'POST',
    body: JSON.stringify({ email, password }),
  });

  if (!response.ok) {
    const data = await response.json().catch(() => ({}));
    throw new Error(data.message ?? 'Неверный email или пароль');
  }

  const data = await response.json();
  return data.user;
}

export async function registerUser(
  name: string,
  email: string,
  password: string,
  passwordConfirmation: string,
): Promise<AuthUser> {
  await getCsrfCookie();

  const response = await fetchCredentials('/register', {
    method: 'POST',
    body: JSON.stringify({
      name,
      email,
      password,
      password_confirmation: passwordConfirmation,
    }),
  });

  if (!response.ok) {
    const data = await response.json().catch(() => ({}));
    const message =
      data.errors?.email?.[0] ??
      data.errors?.name?.[0] ??
      data.errors?.password?.[0] ??
      data.message ??
      'Ошибка регистрации';
    throw new Error(message);
  }

  const data = await response.json();
  return data.user;
}

export async function fetchCurrentUser(): Promise<AuthUser | null> {
  const response = await fetchCredentials('/user');

  if (response.status === 401 || response.status === 403) {
    return null;
  }

  if (!response.ok) {
    throw new Error('Не удалось проверить авторизацию');
  }

  return response.json();
}
