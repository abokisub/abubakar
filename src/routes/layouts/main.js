import { Outlet } from 'react-router-dom';
import { styled } from '@mui/material/styles';
import { Box } from '@mui/material';

const StyledRoot = styled('div')({
  display: 'flex',
  minHeight: '100%',
  overflow: 'hidden',
});

const Main = styled('div')(({ theme }) => ({
  flexGrow: 1,
  overflow: 'auto',
  minHeight: '100%',
  padding: theme.spacing(3),
}));

export default function MainLayout() {
  return (
    <StyledRoot>
      <Main>
        <Outlet />
      </Main>
    </StyledRoot>
  );
} 