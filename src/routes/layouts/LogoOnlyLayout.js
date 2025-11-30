import { Outlet } from 'react-router-dom';
import { styled } from '@mui/material/styles';
import { Box } from '@mui/material';
import Logo from '../../components/Logo';

const StyledRoot = styled('div')({
  display: 'flex',
  minHeight: '100%',
  overflow: 'hidden',
});

const Main = styled('div')(({ theme }) => ({
  flexGrow: 1,
  overflow: 'auto',
  minHeight: '100%',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  padding: theme.spacing(3),
}));

export default function LogoOnlyLayout() {
  return (
    <StyledRoot>
      <Main>
        <Logo sx={{ mb: 5 }} />
        <Outlet />
      </Main>
    </StyledRoot>
  );
} 