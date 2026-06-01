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
  TextField,
  TextInput,
  required,
} from 'react-admin';
import { AppSimpleForm } from '../../../admin/AppSimpleForm';
import { useAppConstants } from '../check-settings/useAppConstants';

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
      <DateField source="created_at" label="Создан" showTime />
      <EditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);

export const NotificationSettingCreate = () => (
  <Create title="Добавить настройки уведомлений">
    <NotificationSettingForm />
  </Create>
);

export const NotificationSettingEdit = () => (
  <Edit title="Редактировать настройки уведомлений">
    <NotificationSettingForm />
  </Edit>
);
