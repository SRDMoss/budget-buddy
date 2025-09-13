import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/Auth';

export default function Login() {
  const { login, error } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setBusy(true);
    try { await login(email, password); } finally { setBusy(false); }
  };

  return (
    <div
      className="container"
      style={{ minHeight: 'calc(100vh - 120px)', display: 'grid', placeItems: 'center' }}
    >
      <div className="card" style={{ width: '100%', maxWidth: 440, padding: '1.25rem' }}>
        <h1 style={{ margin: 0, marginBottom: '0.75rem', textAlign: 'center' }}>
          Budget Buddy — Sign in
        </h1>

        {error && (
          <p className="error" aria-live="polite" style={{ marginTop: 0 }}>
            {String(error)}
          </p>
        )}

        <form onSubmit={submit} autoComplete="off">
          {/* Fields stacked with nice spacing */}
          <div style={{ display: 'flex', flexDirection: 'column', gap: '0.9rem' }}>
            <div>
              <label htmlFor="email" style={{ display: 'block', marginBottom: 6 }}>
                Email
              </label>
              <input
                id="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                type="email"
                autoComplete="username"
                placeholder="you@example.com"
                required
                style={{ width: '100%' }}
              />
            </div>

            <div>
              <label htmlFor="password" style={{ display: 'block', marginBottom: 6 }}>
                Password
              </label>
              <input
                id="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                type="password"
                autoComplete="current-password"
                placeholder="••••••••"
                required
                style={{ width: '100%' }}
              />
            </div>
          </div>

          {/* Actions + demo note: row on wide screens, wraps below on narrow */}
          <div
            style={{
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              flexWrap: 'wrap',
              gap: '0.75rem',
              marginTop: '1rem',
              textAlign: 'center',
            }}
          >
            <button disabled={busy} style={{ minWidth: 140 }}>
              {busy ? 'Signing in…' : 'Sign in'}
            </button>

            <div
              style={{
                fontSize: 14,
                lineHeight: 1.35,
                color: '#374151',
                background: '#fffbe6',
                border: '1px solid #facc15',
                borderRadius: 8,
                padding: '0.5rem 0.6rem',
              }}
            >
              <strong>Demo account</strong> (resets nightly):<br />
              Email: <code>demo@bb.local</code><br />
              Password: <code>password</code>
            </div>
          </div>
        </form>

        <p style={{ marginTop: '0.9rem', textAlign: 'center' }}>
          No account? <Link to="/register">Create one</Link>
        </p>
      </div>
    </div>
  );
}
