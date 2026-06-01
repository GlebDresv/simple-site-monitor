import {
  Create,
  Datagrid,
  DateField,
  DeleteButton,
  Edit,
  EditButton,
  FunctionField,
  List,
  NumberInput,
  ReferenceField,
  ReferenceInput,
  SelectInput,
  TextField,
  TextInput,
  maxValue,
  minValue,
  required,
  useGetList,
} from 'react-admin';
import { useMemo } from 'react';
import { AppSimpleForm } from '../../../admin/AppSimpleForm';
import { transformCheckSettingPayload } from './checkSettingUtils';
import { useAppConstants } from './useAppConstants';

type CheckSettingFormProps = {
  mode?: 'create' | 'edit';
};

const CheckSettingForm = ({ mode = 'edit' }: CheckSettingFormProps) => {
  const { httpMethodChoices, checkIntervalChoices, loading, error } = useAppConstants();
  const { data: notificationSettings = [], isLoading: notificationsLoading } = useGetList(
    'notification-settings',
    {
      pagination: { page: 1, perPage: 100 },
      sort: { field: 'id', order: 'ASC' },
    },
    { enabled: mode === 'create' },
  );

  const defaultValues = useMemo(() => {
    if (mode !== 'create') {
      return undefined;
    }

    const defaults: Record<string, unknown> = {};

    if (httpMethodChoices[0]) {
      defaults.method = httpMethodChoices[0].id;
    }

    if (checkIntervalChoices[0]) {
      defaults.check_interval = checkIntervalChoices[0].id;
    }

    if (notificationSettings[0]) {
      defaults.notification_settings_id = notificationSettings[0].id;
    }

    return Object.keys(defaults).length > 0 ? defaults : undefined;
  }, [mode, httpMethodChoices, checkIntervalChoices, notificationSettings]);

  const formKey =
    mode === 'create'
      ? `create-${httpMethodChoices[0]?.id ?? 'm'}-${checkIntervalChoices[0]?.id ?? 'i'}-${notificationSettings[0]?.id ?? 'n'}`
      : 'edit';

  if (loading || (mode === 'create' && notificationsLoading)) {
    return null;
  }

  return (
    <AppSimpleForm key={formKey} defaultValues={defaultValues}>
      <TextInput source="name" label="Название" validate={[required()]} fullWidth />
      <NumberInput
        source="request_timeout"
        label="Таймаут (сек)"
        validate={[required(), minValue(1), maxValue(5)]}
        min={1}
        max={5}
        fullWidth
        helperText="От 1 до 5 секунд"
      />
      <SelectInput
        source="method"
        label="HTTP метод"
        choices={httpMethodChoices}
        validate={[required()]}
        fullWidth
        helperText={error ?? undefined}
      />
      <SelectInput
        source="check_interval"
        label="Интервал проверки (мин)"
        choices={checkIntervalChoices}
        validate={[required()]}
        fullWidth
        helperText="Интервал между проверками в минутах"
      />
      <ReferenceInput
        source="notification_settings_id"
        reference="notification-settings"
        label="Настройки уведомлений"
      >
        <SelectInput optionText="name" validate={[required()]} />
      </ReferenceInput>
    </AppSimpleForm>
  );
};

export const CheckSettingList = () => {
  const { checkIntervalLabels } = useAppConstants();

  return (
    <List sort={{ field: 'id', order: 'DESC' }} title="Настройки проверки">
      <Datagrid rowClick="edit">
        <TextField source="name" label="Название" />
        <TextField source="method" label="Метод" />
        <TextField source="request_timeout" label="Таймаут (сек)" />
        <FunctionField
          label="Интервал (мин)"
          render={(record: { check_interval?: number }) =>
            checkIntervalLabels[record.check_interval ?? 0] ?? String(record.check_interval ?? '')
          }
        />
        <ReferenceField
          source="notification_settings_id"
          reference="notification-settings"
          label="Уведомления"
          link={false}
        >
          <TextField source="name" />
        </ReferenceField>
        <DateField source="created_at" label="Создан" showTime />
        <EditButton />
        <DeleteButton />
      </Datagrid>
    </List>
  );
};

export const CheckSettingCreate = () => (
  <Create transform={transformCheckSettingPayload} title="Добавить настройки проверки">
    <CheckSettingForm mode="create" />
  </Create>
);

export const CheckSettingEdit = () => (
  <Edit transform={transformCheckSettingPayload} title="Редактировать настройки проверки">
    <CheckSettingForm mode="edit" />
  </Edit>
);
