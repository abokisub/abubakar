import { Navigate, useLocation } from 'react-router-dom';
import { useAuthContext } from '../../contexts/JWTContext';

export default function GuestGuard({ children }) {
  const { isAuthenticated } = useAuthContext();
  const { state } = useLocation();

  if (isAuthenticated) {
    return <Navigate to={state?.from || '/dashboard'} replace />;
  }

  return <>{children}</>;
} 