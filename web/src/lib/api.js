const BASE = import.meta.env.VITE_API_BASE_URL;

async function toJson(res) {
  const text = await res.text();
  try { return JSON.parse(text); } catch { throw new Error(text || res.statusText); }
}

export async function get(path, init = {}) {
  const res = await fetch(BASE + path, { credentials: 'include', ...init });
  if (!res.ok) throw await toJson(res);
  return toJson(res);
}

async function csrf() {
  const r = await get('/auth/csrf');
  return r.csrf;
}

export async function post(path, body) {
  const token = await csrf();
  const res = await fetch(BASE + path, {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    body: JSON.stringify(body),
  });
  if (!res.ok) throw await toJson(res);
  return toJson(res);
}

export async function patch(path, body) {
  const token = await csrf();
  const res = await fetch(BASE + path, {
    method: 'PATCH',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    body: JSON.stringify(body),
  });
  if (!res.ok) throw await toJson(res);
  return toJson(res);
}

export async function del(path) {
  const token = await csrf();
  const res = await fetch(BASE + path, {
    method: 'DELETE',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token },
    body: '{}',
  });
  if (!res.ok) throw await toJson(res);
  return toJson(res);
}
