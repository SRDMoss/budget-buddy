import { createContext, useContext, useEffect, useState } from 'react';
import { get, post } from '../lib/api';

const Ctx = createContext(null);
export const useAuth = () => useContext(Ctx);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setErr] = useState();

  const refresh = async () => {
    try {
      const r = await get('/auth/me');
      setUser(r.user ?? null);
      setErr(undefined);
    } catch (e) {
      setErr(e.error || e.message);
      setUser(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { refresh(); }, []);

  const login = async (email, password) => {
    setLoading(true);
    try {
      await post('/auth/login', { email, password });
      await refresh();
    } catch (e) {
      setErr(e.error || e.message);
      setLoading(false);
      throw e;
    }
  };

  const logout = async () => { await post('/auth/logout', {}); setUser(null); };

  return (
    <Ctx.Provider value={{ user, loading, error, login, logout, refresh }}>
      {children}
    </Ctx.Provider>
  );
}
