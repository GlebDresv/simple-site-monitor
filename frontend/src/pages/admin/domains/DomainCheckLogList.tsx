import {
  Datagrid,
  DateField,
  FunctionField,
  LinkBase,
  List,
  NumberField,
  Pagination,
  TopToolbar,
  useBasename,
  useGetOne,
} from 'react-admin';
import { Link as MuiLink } from '@mui/material';
import { useParams } from 'react-router-dom';

const DomainCheckLogPagination = () => <Pagination rowsPerPageOptions={[20]} />;

const DomainCheckLogActions = () => {
  const basename = useBasename();

  return (
    <TopToolbar>
      <MuiLink component={LinkBase} to={`${basename}/domains`} underline="hover">
        ← К доменам
      </MuiLink>
    </TopToolbar>
  );
};

export function DomainCheckLogList() {
  const { domainId } = useParams();
  const id = Number(domainId);
  const basename = useBasename();

  const { data: domain } = useGetOne(
    'domains',
    { id },
    { enabled: Number.isFinite(id) && id > 0 },
  );

  return (
    <List
      resource="check-logs"
      filter={{ domain_id: id }}
      sort={{ field: 'checked_at', order: 'DESC' }}
      perPage={20}
      pagination={<DomainCheckLogPagination />}
      actions={<DomainCheckLogActions />}
      title={domain ? `Логи: ${domain.name}` : 'Логи проверок'}
      exporter={false}
    >
      <Datagrid
        bulkActionButtons={false}
        rowClick={(logId) => `${basename}/domains/${domainId}/logs/${logId}`}
      >
        <DateField source="checked_at" label="Проверено" showTime />
        <NumberField source="response_code" label="HTTP код" />
        <NumberField source="response_time" label="Время (мс)" />
        <NumberField source="redirects_count" label="Редиректы" />
        <FunctionField
          label="Тело ответа"
          render={(record: { response_body?: string | null }) => {
            if (!record.response_body) {
              return '—';
            }

            return record.response_body.length > 80
              ? `${record.response_body.slice(0, 80)}…`
              : record.response_body;
          }}
        />
      </Datagrid>
    </List>
  );
}
