import type { ComponentProps } from 'react';
import { Route } from 'react-router-dom';
import { Admin, CustomRoutes, Layout, Resource } from 'react-admin';
import { authProvider } from '../authProvider';
import { dataProvider } from '../dataProvider';
import { CheckSettingCreate, CheckSettingEdit, CheckSettingList } from '../pages/admin/check-settings';
import LoginRedirect from '../components/LoginRedirect';
import { DomainCheckLogList } from '../pages/admin/domains/DomainCheckLogList';
import { DomainCheckLogShow } from '../pages/admin/domains/DomainCheckLogShow';
import { DomainCreate, DomainEdit, DomainList } from '../pages/admin/domains';
import {
  NotificationSettingCreate,
  NotificationSettingEdit,
  NotificationSettingList,
} from '../pages/admin/notification-settings';
import Dashboard from './Dashboard';

const CustomLayout = (props: ComponentProps<typeof Layout>) => (
  <Layout {...props} />
);

export default function AdminApp() {
  return (
    <Admin
      authProvider={authProvider}
      dataProvider={dataProvider}
      dashboard={Dashboard}
      layout={CustomLayout}
      loginPage={LoginRedirect}
      requireAuth
      basename="/cabinet"
    >
      <Resource
        name="domains"
        list={DomainList}
        create={DomainCreate}
        edit={DomainEdit}
        options={{ label: 'Домены' }}
      />
      <Resource
        name="check-settings"
        list={CheckSettingList}
        create={CheckSettingCreate}
        edit={CheckSettingEdit}
        options={{ label: 'Настройки проверки' }}
      />
      <Resource
        name="notification-settings"
        list={NotificationSettingList}
        create={NotificationSettingCreate}
        edit={NotificationSettingEdit}
        options={{ label: 'Уведомления' }}
      />
      <Resource
        name="check-logs"
        options={{ label: 'Логи проверок' }}
      />
      <CustomRoutes>
        <Route path="/domains/:domainId/logs" element={<DomainCheckLogList />} />
        <Route path="/domains/:domainId/logs/:id" element={<DomainCheckLogShow />} />
      </CustomRoutes>
    </Admin>
  );
}
