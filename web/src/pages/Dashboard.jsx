import { useEffect, useMemo, useState } from 'react';
import { get } from '../lib/api';

// tiny currency helper
const fmt = (n) => {
  if (n == null || isNaN(Number(n))) return '0.00';
  return Number(n).toFixed(2);
};

/** Simple responsive line chart (SVG). `points` = [{x: number, y: number}] */
function LineChart({ points = [], height = 160, stroke = '#2563eb' }) {
  if (!points.length) return <div style={{height}}>No data</div>;
  const padding = 16;
  const w = Math.max(320, points.length * 10 + padding * 2);
  const xs = points.map(p => p.x), ys = points.map(p => p.y);
  const minX = Math.min(...xs), maxX = Math.max(...xs);
  const minY = Math.min(...ys, 0), maxY = Math.max(...ys, 0);
  const sx = (x) => padding + (w - padding*2) * (maxX === minX ? 0.5 : (x - minX) / (maxX - minX));
  const sy = (y) => height - padding - (height - padding*2) * (maxY === minY ? 0.5 : (y - minY) / (maxY - minY));
  const d = points.map((p, i) => `${i ? 'L':'M'} ${sx(p.x)} ${sy(p.y)}`).join(' ');
  return (
    <svg width="100%" viewBox={`0 0 ${w} ${height}`} style={{ display:'block' }}>
      <path d={d} fill="none" stroke={stroke} strokeWidth="2" />
    </svg>
  );
}

/** Simple horizontal bar chart (category totals). `rows` = [{label, value}] */
function BarChart({ rows = [], height = 220, color = '#10b981' }) {
  if (!rows.length) return <div style={{height}}>No data</div>;
  const padding = 16, barH = 18, gap = 10;
  const w = 640, innerW = w - padding*2;
  const max = Math.max(...rows.map(r => r.value), 1);
  const h = Math.max(height, padding*2 + rows.length * (barH + gap));
  return (
    <svg width="100%" viewBox={`0 0 ${w} ${h}`} style={{ display:'block' }}>
      {rows.map((r, i) => {
        const y = padding + i * (barH + gap);
        const bw = innerW * (r.value / max);
        return (
          <g key={i}>
            <rect x={padding} y={y} width={bw} height={barH} fill={color} rx="6" />
            <text x={padding} y={y - 4} fontSize="10" fill="#555">{r.label}</text>
            <text x={padding + bw + 6} y={y + barH - 4} fontSize="11" fill="#111">${fmt(r.value)}</text>
          </g>
        );
      })}
    </svg>
  );
}

export default function Dashboard() {
  const [mode, setMode] = useState('month'); // 'month' | 'year'
  const [month, setMonth] = useState(new Date().toISOString().slice(0,7));
  const [year, setYear] = useState(String(new Date().getFullYear()));
  const [data, setData] = useState(null);
  const [err, setErr] = useState('');

  const [categories, setCategories] = useState([]);
  const [categoryId, setCategoryId] = useState('');

  async function load() {
    try {
      const params = new URLSearchParams(mode === 'month' ? { month } : { year });
      if (categoryId) params.set('category_id', String(categoryId));
      const url = mode === 'month'
        ? `/reports/month?${params.toString()}`
        : `/reports/year?${params.toString()}`;
      const r = await get(url);
      setData(r);
      setErr('');
    } catch (e) {
      setErr(e.error || e.message || 'Failed to load report');
      setData(null);
    }
  }
  useEffect(() => { load(); }, [mode, month, year, categoryId]);

  // load categories once
  useEffect(() => {
    (async () => {
      try {
        const r = await get('/categories');
        setCategories(r.items || r); // support either shape
      } catch {
        // non-fatal
      }
    })();
  }, []);

  // transform for charts
  const linePoints = useMemo(() => {
    if (!data) return [];
    if (mode === 'month') {
      // daily: [{date:'YYYY-MM-DD', income, expense, net}]
      return (data.daily || []).map((d, i) => ({ x: i + 1, y: Number(d.net) }));
    } else {
      // monthly: [{month:'YYYY-MM', income, expense, net}]
      return (data.monthly || []).map((m, i) => ({ x: i + 1, y: Number(m.net) }));
    }
  }, [data, mode]);

  const byCat = useMemo(() => {
    if (!data) return [];
    const map = new Map();
    for (const row of (data.byCategory || [])) {
      const name = row.category_name || 'Uncategorized';
      const val = Number(row.total);
      if (row.type === 'expense') map.set(name, (map.get(name) || 0) + val);
    }
    return Array.from(map.entries())
      .map(([label, value]) => ({ label, value }))
      .sort((a,b) => b.value - a.value)
      .slice(0, 8);
  }, [data]);

  return (
    <div className="container">
      <h2>Dashboard</h2>

      <div className="card row" style={{ alignItems:'center', gap:'1rem' }}>
        <label>View
          <select value={mode} onChange={e=>setMode(e.target.value)} style={{ marginLeft: 8 }}>
            <option value="month">Month</option>
            <option value="year">Year</option>
          </select>
        </label>

        {mode === 'month' ? (
          <label>Month
            <input
              type="month"
              value={month}
              onChange={e=>setMonth(e.target.value)}
              style={{ marginLeft: 8 }}
            />
          </label>
        ) : (
          <label>Year
            <input
              type="number"
              min="2000"
              max="2100"
              value={year}
              onChange={e=>setYear(e.target.value.replace(/\D/g, '').slice(0,4))}
              style={{ width: 100, marginLeft: 8 }}
            />
          </label>
        )}

        <label>Category
          <select
            value={categoryId}
            onChange={e=>setCategoryId(e.target.value)}
            style={{ marginLeft: 8 }}
          >
            <option value="">All</option>
            {categories.map(c => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </label>

        <button onClick={load}>Refresh</button>
        {err && <span className="error" style={{ marginLeft: '1rem' }}>{err}</span>}
      </div>

      {/* Summary cards */}
      <div className="row" style={{ gap:'1rem' }}>
        <div className="card" style={{ flex:'1 1 200px' }}>
          <div><b>Total Income</b></div>
          <div style={{ fontSize: 24 }}>${fmt(data?.totals?.income)}</div>
        </div>
        <div className="card" style={{ flex:'1 1 200px' }}>
          <div><b>Total Expense</b></div>
          <div style={{ fontSize: 24 }}>${fmt(data?.totals?.expense)}</div>
        </div>
        <div className="card" style={{ flex:'1 1 200px' }}>
          <div><b>Net</b></div>
          <div style={{ fontSize: 24 }}>${fmt(data?.totals?.net)}</div>
        </div>
      </div>

      {/* Timeline line */}
      <div className="card" style={{ marginTop: '1rem' }}>
        <b>{mode === 'month' ? 'Daily Net' : 'Monthly Net'}</b>
        <LineChart points={linePoints} />
      </div>

      {/* Category breakdown bar */}
      <div className="card" style={{ marginTop: '1rem' }}>
        <b>Top Expense Categories ({mode})</b>
        <BarChart rows={byCat} />
      </div>
    </div>
  );
}
