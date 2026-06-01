import {
  LinkBase,
  Show,
  TopToolbar,
  useBasename,
  useGetOne,
  useRecordContext,
} from 'react-admin';
import {
  Box,
  Card,
  CardContent,
  Grid,
  Link as MuiLink,
  Typography,
} from '@mui/material';
import { useParams } from 'react-router-dom';

type CheckLogRecord = {
  id: number;
  domain_id: number;
  checked_at: string;
  response_code: number | null;
  response_time: number | null;
  response_headers: Record<string, string | string[]> | null;
  redirects_count: number;
  response_body: string | null;
};

function formatHeaders(headers: CheckLogRecord['response_headers']): string {
  if (!headers || Object.keys(headers).length === 0) {
    return '—';
  }

  return JSON.stringify(headers, null, 2);
}

function PreBlock({ content }: { content: string }) {
  return (
    <Box
      component="pre"
      sx={{
        m: 0,
        p: 2,
        bgcolor: 'grey.100',
        borderRadius: 1,
        fontFamily: 'monospace',
        fontSize: '0.875rem',
        overflow: 'auto',
        maxHeight: 480,
        whiteSpace: 'pre-wrap',
        wordBreak: 'break-word',
      }}
    >
      {content}
    </Box>
  );
}

function ResponseCode({ code }: { code: number | null }) {
  if (code === null) {
    return <>—</>;
  }

  const color = code >= 200 && code < 300 ? 'success.main' : 'error.main';

  return (
    <Typography component="span" color={color} fontWeight="medium">
      {code}
    </Typography>
  );
}

function CheckLogDetails() {
  const record = useRecordContext<CheckLogRecord>();

  if (!record) {
    return null;
  }

  const checkedAt = new Date(record.checked_at).toLocaleString('ru-RU');

  return (
    <Card>
      <CardContent>
        <Grid container spacing={2}>
          <Grid size={{ xs: 12, sm: 6, md: 3 }}>
            <Typography variant="caption" color="text.secondary" display="block">
              Проверено
            </Typography>
            <Typography variant="body1">{checkedAt}</Typography>
          </Grid>
          <Grid size={{ xs: 12, sm: 6, md: 3 }}>
            <Typography variant="caption" color="text.secondary" display="block">
              HTTP код
            </Typography>
            <Typography variant="body1">
              <ResponseCode code={record.response_code} />
            </Typography>
          </Grid>
          <Grid size={{ xs: 12, sm: 6, md: 3 }}>
            <Typography variant="caption" color="text.secondary" display="block">
              Время ответа
            </Typography>
            <Typography variant="body1">
              {record.response_time !== null ? `${record.response_time} мс` : '—'}
            </Typography>
          </Grid>
          <Grid size={{ xs: 12, sm: 6, md: 3 }}>
            <Typography variant="caption" color="text.secondary" display="block">
              Редиректы
            </Typography>
            <Typography variant="body1">{record.redirects_count}</Typography>
          </Grid>
        </Grid>

        <Typography variant="subtitle2" sx={{ mt: 3, mb: 1 }}>
          Заголовки ответа
        </Typography>
        <PreBlock content={formatHeaders(record.response_headers)} />

        <Typography variant="subtitle2" sx={{ mt: 3, mb: 1 }}>
          Тело ответа
        </Typography>
        <PreBlock content={record.response_body ?? '—'} />
      </CardContent>
    </Card>
  );
}

const DomainCheckLogShowActions = ({ domainId }: { domainId: number }) => {
  const basename = useBasename();

  return (
    <TopToolbar>
      <MuiLink
        component={LinkBase}
        to={`${basename}/domains/${domainId}/logs`}
        underline="hover"
      >
        ← К логам
      </MuiLink>
      <MuiLink
        component={LinkBase}
        to={`${basename}/domains`}
        underline="hover"
        sx={{ ml: 2 }}
      >
        ← К доменам
      </MuiLink>
    </TopToolbar>
  );
};

export function DomainCheckLogShow() {
  const { domainId, id } = useParams();
  const logId = Number(id);
  const domainIdNum = Number(domainId);

  const { data: domain } = useGetOne(
    'domains',
    { id: domainIdNum },
    { enabled: Number.isFinite(domainIdNum) && domainIdNum > 0 },
  );

  return (
    <Show
      resource="check-logs"
      id={logId}
      actions={<DomainCheckLogShowActions domainId={domainIdNum} />}
      title={domain ? `Лог проверки: ${domain.name}` : 'Лог проверки'}
    >
      <CheckLogDetails />
    </Show>
  );
}
