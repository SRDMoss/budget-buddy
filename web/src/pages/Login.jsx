import { useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/Auth';

export default function Login() {
  const { login, error } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [busy, setBusy] = useState(false);

  const submit = async (e) => {
    e.preventDefault(); setBusy(true);
    try { await login(email, password); } finally { setBusy(false); }
  };

  return (
    <div className="container">
      <h1>Budget Buddy — Sign in</h1>
      {error && <p className="error">{String(error)}</p>}
      <form onSubmit={submit} className="card" autoComplete="off">
        <div className="row">
          <label>Email</label>
          <input 
	    value={email} 
	    onChange={e=>setEmail(e.target.value)} 
	    type="email" 
	    autocomplete="username"
	    placeholder="you@example.com"
	    required 
	  />
        </div>
        <div className="row">
          <label>Password</label>
          <input 
	    value={password}
	    onChange={e=>setPassword(e.target.value)}
	    type="password" 
	    autoComplete="current-password"
	    placeholder="••••••••"
	    required
	  />
        </div>
        <button disabled={busy}>{busy ? 'Signing in…' : 'Sign in'}</button>
      </form>
      <p style={{ marginTop: '0.75rem' }}>
        No account? <Link to="/register">Create one</Link>
      </p>
    </div>
  );
}
