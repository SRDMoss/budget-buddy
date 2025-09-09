import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/Auth';

export default function Protected({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="container">Loading…</div>;
  if (!user) return <Navigate to="/login" replace />;
  return children;
}
