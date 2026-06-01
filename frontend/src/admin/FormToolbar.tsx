import { DeleteButton, SaveButton, Toolbar, type ToolbarProps } from 'react-admin';

export function FormToolbar(props: ToolbarProps) {
  return (
    <Toolbar {...props}>
      <SaveButton alwaysEnable />
      <DeleteButton />
    </Toolbar>
  );
}
