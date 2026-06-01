import type { AuthProvider } from 'react-admin';
import { fetchCredentials, fetchCurrentUser, getCsrfCookie } from './api/client';

export const authProvider: AuthProvider = {
  login: async () => Promise.resolve(),
  logout: async () => {
    await getCsrfCookie();
    await fetchCredentials('/logout', { method: 'POST' });
    return '/';
  },
  checkAuth: async () => {
    const user = await fetchCurrentUser();
    if (!user) {
      return Promise.reject(new Error('Unauthorized'));
    }
  },
  checkError: (error) => {
    const status = error.status;
    if (status === 401 || status === 403) {
      return Promise.reject(new Error('Unauthorized'));
    }
    return Promise.resolve();
  },
  getIdentity: async () => {
    const user = await fetchCurrentUser();
    if (!user) {
      return Promise.reject(new Error('Unauthorized'));
    }
    return { id: user.id, fullName: user.name };
  },
  getPermissions: () => Promise.resolve(''),
};
