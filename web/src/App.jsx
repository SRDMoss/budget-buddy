import { BrowserRouter, Routes, Route, Navigate, Link, useLocation } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/Auth';
import Protected from './components/Protected';

import Login from './pages/Login';
import Register from './pages/Register';
import Categories from './pages/Categories';
import Transactions from './pages/Transactions';
import Reports from './pages/Reports';
import Dashboard from './pages/Dashboard';

function Nav() {
  const { user, logout } = useAuth();
  const loc = useLocation();
  const hide = loc.pathname === '/login' || loc.pathname === '/register';
  if (hide) return null;

  return (
    <div className="nav container">
      <h1><i>Budget Buddy!</i></h1>

      <Link to="/dashboard">Dashboard</Link>
      <Link to="/categories">Categories</Link>
      <Link to="/transactions">Transactions</Link>
      <Link to="/reports">Reports</Link>
      
      <div style={{ marginLeft: 'auto' }}>
        {user ? (
          <>
            <span>{user.email}</span>{' '}
            <button onClick={logout}>Logout</button>
          </>
        ) : (
          <Link to="/login">Login</Link>
        )}
      </div>
    </div>
  );
}

// Gate that redirects signed-in users away from auth pages
function GuestOnly({ children }) {
  const { user, loading } = useAuth();
  if (loading) return <div className="container">Loading…</div>;
  if (user) return <Navigate to="/categories" replace />;
  return children;
}

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Nav />
        <Routes>
          {/* Auth pages */}
          <Route path="/login" element={<GuestOnly><Login /></GuestOnly>} />
          <Route path="/register" element={<GuestOnly><Register /></GuestOnly>} />

          {/* App pages (protected) */}
          <Route path="/categories" element={<Protected><Categories /></Protected>} />
          <Route path="/transactions" element={<Protected><Transactions /></Protected>} />
          <Route path="/reports" element={<Protected><Reports /></Protected>} />
          <Route path="/dashboard" element={<Protected><Dashboard /></Protected>} />

          {/* Defaults */}
          <Route path="/" element={<Navigate to="/dashboard" replace />} />
          <Route path="*" element={<Navigate to="/dashboard" replace />} />

        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}
