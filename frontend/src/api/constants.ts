import { fetchCredentials } from './client';

export interface Choice {
  id: string | number;
  name: string;
}

export interface AppConstants {
  http_methods: Choice[];
  check_intervals: Choice[];
  telegram_bot_name: string;
}

let cachedConstants: AppConstants | null = null;
let pendingConstants: Promise<AppConstants> | null = null;

export async function fetchConstants(): Promise<AppConstants> {
  if (cachedConstants) {
    return cachedConstants;
  }

  if (pendingConstants) {
    return pendingConstants;
  }

  pendingConstants = fetchCredentials('/constants').then(async (response) => {
    if (!response.ok) {
      throw new Error('Не удалось загрузить константы');
    }

    cachedConstants = await response.json() as AppConstants;
    return cachedConstants;
  }).finally(() => {
    pendingConstants = null;
  });

  return pendingConstants;
}

export function clearConstantsCache(): void {
  cachedConstants = null;
}

export function buildChoiceLabels(choices: Choice[]): Record<string | number, string> {
  return Object.fromEntries(choices.map(({ id, name }) => [id, name]));
}
