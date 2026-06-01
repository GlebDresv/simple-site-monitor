import {
  Create,
  Datagrid,
  DateField,
  DeleteButton,
  Edit,
  EditButton,
  FunctionField,
  List,
  ReferenceField,
  ReferenceInput,
  SelectInput,
  TextField,
  TextInput,
  required,
} from 'react-admin';
import { AppSimpleForm } from '../../../admin/AppSimpleForm';
import { DomainLogButton } from './DomainLogButton';
import { DomainStatusDot } from './DomainStatusDot';
import { domainStatusLabels, transformDomainPayload } from './domainUtils';

const DomainForm = () => (
  <AppSimpleForm>
    <TextInput
      source="name"
      label="Домен"
      validate={[required()]}
      fullWidth
      helperText="Например: example.com"
    />
    <ReferenceInput
      source="check_settings_id"
      reference="check-settings"
      label="Настройки проверки"
    >
      <SelectInput optionText="name" emptyText="Не выбрано" />
    </ReferenceInput>
  </AppSimpleForm>
);

export const DomainList = () => (
  <List sort={{ field: 'id', order: 'DESC' }} title="Домены">
    <Datagrid rowClick="edit">
      <TextField source="name" label="Домен" />
      <FunctionField label="Статус" render={() => <DomainStatusDot />} />
      <ReferenceField
        source="check_settings_id"
        reference="check-settings"
        label="Настройки проверки"
        link={false}
      >
        <TextField source="name" />
      </ReferenceField>
      <DateField source="created_at" label="Создан" showTime />
      <DomainLogButton />
      <EditButton />
      <DeleteButton />
    </Datagrid>
  </List>
);

export const DomainCreate = () => (
  <Create transform={transformDomainPayload} title="Добавить домен">
    <DomainForm />
  </Create>
);

export const DomainEdit = () => (
  <Edit transform={transformDomainPayload} title="Редактировать домен">
    <AppSimpleForm>
      <TextInput
        source="name"
        label="Домен"
        validate={[required()]}
        fullWidth
        helperText="Например: example.com"
      />
      <ReferenceInput
        source="check_settings_id"
        reference="check-settings"
        label="Настройки проверки"
      >
        <SelectInput optionText="name" emptyText="Не выбрано" />
      </ReferenceInput>
      <FunctionField
        label="Текущий статус"
        render={(record: { last_status?: string }) =>
          domainStatusLabels[record.last_status ?? 'unknown'] ?? record.last_status
        }
      />
    </AppSimpleForm>
  </Edit>
);
