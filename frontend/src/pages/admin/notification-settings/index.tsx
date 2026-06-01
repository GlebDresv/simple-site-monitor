import {
  BooleanField,
  BooleanInput,
  Create,
  Datagrid,
  DateField,
  DeleteButton,
  Edit,
  EditButton,
  List,
  NumberInput,
  TextField,
  TextInput,
  required,
} from 'react-admin';
import { AppSimpleForm } from '../../../admin/AppSimpleForm';
import { useAppConstants } from '../check-settings/useAppConstants';
import { transformNotificationSettingPayload } from './notificationSettingUtils';

const NotificationSettingForm = () => {
  const { telegramBotName } = useAppConstants();

  return (
  <AppSimpleForm>
    <TextInput source="name" label="Название" validate={[required()]} fullWidth />
    <TextInput
      source="tg_chat_id"
      label="Telegram Chat ID"
      validate={[required()]}
      fullWidth
      helperText={
        telegramBotName
          ? `Добавьте чатбот ${telegramBotName} в свой канал, чтобы получать уведомления`
          : undefined
      }
    />
    <BooleanInput source="notify_on_shutdown" label="Уведомлять при падении" defaultValue={true} />
    <BooleanInput source="notify_on_recovery" label="Уведомлять при восстановлении" defaultValue={true} />
    <NumberInput
      source="debounce_interval"
      label="Debounce (мин)"
      defaultValue={5}
      validate={[
        required(),
        (value) =>
          value == null || Number(value) < 5 ? 'Минимум 5 минут' : undefined,
      ]}
      min={5}
      fullWidth
      helperText="Минимум 5 минут"
    />
  </AppSimpleForm>
  );
};

export const NotificationSettingList = () => (
  <List sort={{ field: 'id', order: 'DESC' }} title="Настройки уведомлений">
    <Datagrid rowClick="edit">
      <TextField source="name" label="Название" />
      <TextField source="tg_chat_id" label="Telegram Chat ID" />
      <BooleanField source="notify_on_shutdown" label="При падении" />
      <BooleanField source="notify_on_recovery" label="При восстановлении" />
      <TextField source="debounce_interval" label="Debounce (мин)" />
      <DateField source="created_at" label="Создан" showTime />
      <EditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);

export const NotificationSettingCreate = () => (
  <Create transform={transformNotificationSettingPayload} title="Добавить настройки уведомлений">
    <NotificationSettingForm />
  </Create>
);

export const NotificationSettingEdit = () => (
  <Edit transform={transformNotificationSettingPayload} title="Редактировать настройки уведомлений">
    <NotificationSettingForm />
  </Edit>
);
