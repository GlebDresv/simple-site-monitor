import { Card, CardContent, Typography } from '@mui/material';

export default function Dashboard() {
  return (
    <Card>
      <CardContent>
        <Typography variant="h5" component="h2" gutterBottom>
          Кабинет
        </Typography>
        <Typography variant="body1" color="text.secondary">
          Добро пожаловать в панель управления.
        </Typography>
      </CardContent>
    </Card>
  );
}
