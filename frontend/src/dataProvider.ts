import type { DataProvider } from 'react-admin';
import simpleRestProvider from 'ra-data-simple-rest';
import { httpClient } from './api/httpClient';

const baseProvider = simpleRestProvider('/api', httpClient, 'X-Total-Count');

export const dataProvider: DataProvider = {
  ...baseProvider,
  getList: async (resource, params) => {
    const { page, perPage } = params.pagination ?? { page: 1, perPage: 25 };
    const { field, order } = params.sort ?? { field: 'id', order: 'DESC' };

    const query = new URLSearchParams({
      sort: JSON.stringify([field, order]),
      range: JSON.stringify([(page - 1) * perPage, page * perPage - 1]),
      filter: JSON.stringify(params.filter),
    });

    const { headers, json } = await httpClient(`/api/${resource}?${query.toString()}`);
    const data = Array.isArray(json)
      ? json
      : ((json as { data?: unknown[] }).data ?? []);
    const headerTotal = headers.get('X-Total-Count') ?? headers.get('x-total-count');
    const total = headerTotal ? Number.parseInt(headerTotal, 10) : data.length;

    return { data, total };
  },
  delete: async (resource, params) => {
    await httpClient(`/api/${resource}/${encodeURIComponent(String(params.id))}`, {
      method: 'DELETE',
    });

    return { data: params.previousData! };
  },
};
