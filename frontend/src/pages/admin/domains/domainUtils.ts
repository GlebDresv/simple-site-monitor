export const domainStatusChoices = [
  { id: 'unknown', name: 'Неизвестно' },
  { id: 'up', name: 'Доступен' },
  { id: 'down', name: 'Недоступен' },
];

export const domainStatusLabels: Record<string, string> = {
  unknown: 'Неизвестно',
  up: 'Доступен',
  down: 'Недоступен',
};

export function transformDomainPayload(data: Record<string, unknown>) {
  return {
    ...data,
    check_settings_id: data.check_settings_id || null,
  };
}
