import { Navigate, useLocation } from 'react-router-dom';
import { useAuthContext } from '../../contexts/JWTContext';

export default function AuthGuard({ children }) {
  const { isAuthenticated } = useAuthContext();
  const { state } = useLocation();

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: state?.from }} replace />;
  }

  return <>{children}</>;
} 