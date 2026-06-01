import { SimpleForm, type SimpleFormProps } from 'react-admin';
import { FormToolbar } from './FormToolbar';

export function AppSimpleForm({ toolbar, ...props }: SimpleFormProps) {
  return <SimpleForm toolbar={toolbar ?? <FormToolbar />} {...props} />;
}
