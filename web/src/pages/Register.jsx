import { useState } from 'react';
import { useAuth } from '../context/Auth';
import { post } from '../lib/api';

export default function Register() {
  const { login } = useAuth(); // we’ll reuse login after register
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [displayName, setDisplayName] = useState('');
  const [error, setError] = useState('');

  async function handleSubmit(e) {
    e.preventDefault();
    setError('');

    try {
      // call register endpoint via shared API helper (handles CSRF + base URL)
      await post('/auth/register', { email, password, display_name: displayName });
 
      // auto-login after successful register
      await login(email, password);
    } catch (err) {
      setError(err.error || err.message || 'Registration failed');
    }
  }

  return (
    <div className="container">
      <h2>Register</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label>Display Name</label>
          <input value={displayName} onChange={e => setDisplayName(e.target.value)} required />
        </div>
        <div>
          <label>Email</label>
          <input type="email" value={email} onChange={e => setEmail(e.target.value)} required />
        </div>
        <div>
          <label>Password</label>
          <input type="password" value={password} onChange={e => setPassword(e.target.value)} required />
        </div>
        <button type="submit">Register</button>
      </form>
      {error && <p style={{ color: 'red' }}>{error}</p>}
    </div>
  );
}
