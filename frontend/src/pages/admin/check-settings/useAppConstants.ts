import { useEffect, useMemo, useState } from 'react';
import { type AppConstants, buildChoiceLabels, fetchConstants } from '../../../api/constants';

export function useAppConstants() {
  const [constants, setConstants] = useState<AppConstants | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    fetchConstants()
      .then(setConstants)
      .catch((err) => setError(err instanceof Error ? err.message : 'Ошибка загрузки'))
      .finally(() => setLoading(false));
  }, []);

  const checkIntervalLabels = useMemo(
    () => buildChoiceLabels(constants?.check_intervals ?? []),
    [constants],
  );

  return {
    httpMethodChoices: constants?.http_methods ?? [],
    checkIntervalChoices: constants?.check_intervals ?? [],
    checkIntervalLabels,
    telegramBotName: constants?.telegram_bot_name ?? '',
    loading,
    error,
  };
}
