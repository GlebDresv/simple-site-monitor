import type { MouseEvent } from 'react';
import ArticleIcon from '@mui/icons-material/Article';
import { Button } from '@mui/material';
import { LinkBase, useBasename, useRecordContext } from 'react-admin';

export function DomainLogButton() {
  const record = useRecordContext<{ id?: number }>();
  const basename = useBasename();

  if (!record?.id) {
    return null;
  }

  return (
    <Button
      component={LinkBase}
      to={`${basename}/domains/${record.id}/logs`}
      size="small"
      startIcon={<ArticleIcon />}
      onClick={(event: MouseEvent) => event.stopPropagation()}
    >
      Logs
    </Button>
  );
}
