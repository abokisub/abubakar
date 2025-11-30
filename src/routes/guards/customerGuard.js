import { Navigate, useLocation } from 'react-router-dom';
import { useAuthContext } from '../../contexts/JWTContext';

export default function CustomerGuard({ children }) {
  const { isAuthenticated, user } = useAuthContext();
  const { state } = useLocation();

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: state?.from }} replace />;
  }

  if (user?.role !== 'customer') {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
} 