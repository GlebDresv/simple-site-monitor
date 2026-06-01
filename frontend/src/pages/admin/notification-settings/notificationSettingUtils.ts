export function transformNotificationSettingPayload(data: Record<string, unknown>) {
  return {
    ...data,
    debounce_interval: Number(data.debounce_interval),
  };
}
