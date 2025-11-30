import { Navigate, useLocation } from 'react-router-dom';
import { useAuthContext } from '../../contexts/JWTContext';

export default function RoleBasedGuard({ children, allowedRoles = [] }) {
  const { isAuthenticated, user } = useAuthContext();
  const { state } = useLocation();

  if (!isAuthenticated) {
    return <Navigate to="/login" state={{ from: state?.from }} replace />;
  }

  if (allowedRoles.length > 0 && !allowedRoles.includes(user?.role)) {
    return <Navigate to="/dashboard" replace />;
  }

  return <>{children}</>;
} 