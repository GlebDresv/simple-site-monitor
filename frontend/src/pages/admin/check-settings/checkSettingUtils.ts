export function transformCheckSettingPayload(data: Record<string, unknown>) {
  return {
    ...data,
    request_timeout: Number(data.request_timeout),
    check_interval: Number(data.check_interval),
    notification_settings_id: Number(data.notification_settings_id),
  };
}
