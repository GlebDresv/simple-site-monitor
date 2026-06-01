import { Box, Tooltip } from '@mui/material';
import { useRecordContext } from 'react-admin';
import { domainStatusLabels } from './domainUtils';

const statusColors: Record<string, string> = {
  up: '#4caf50',
  down: '#f44336',
  unknown: '#9e9e9e',
};

export function DomainStatusDot() {
  const record = useRecordContext<{ last_status?: string }>();

  if (!record) {
    return null;
  }

  const status = record.last_status ?? 'unknown';

  return (
    <Tooltip title={domainStatusLabels[status] ?? status}>
      <Box
        sx={{
          width: 12,
          height: 12,
          borderRadius: '50%',
          bgcolor: statusColors[status] ?? statusColors.unknown,
        }}
      />
    </Tooltip>
  );
}
