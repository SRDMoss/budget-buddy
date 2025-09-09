import { useEffect, useMemo, useState } from 'react';
import { get } from '../lib/api';

const fmt = (n) => {
  if (n == null || isNaN(Number(n))) return '0.00';
  return Number(n).toFixed(2);
};

export default function Reports() {
  const [mode, setMode] = useState('month'); // 'month' | 'year'
  const [month, setMonth] = useState(new Date().toISOString().slice(0,7));
  const [year, setYear] = useState(String(new Date().getFullYear()));
  const [data, setData] = useState();
  const [err, setErr] = useState();

  const [categories, setCategories] = useState([]);
  const [categoryId, setCategoryId] = useState('');

  const load = async () => {
    try {
      const params = new URLSearchParams(mode === 'month' ? { month } : { year });
      if (categoryId) params.set('category_id', String(categoryId));
      const url = mode === 'month'
        ? `/reports/month?${params.toString()}`
        : `/reports/year?${params.toString()}`;
      const r = await get(url);
      setData(r);
      setErr(undefined);
    } catch (e) {
      setErr(e.error || e.message || 'Failed to load report');
      setData(undefined);
    }
  };

  useEffect(()=>{ load(); },[mode, month, year, categoryId]);

  // load categories once
  useEffect(() => {
    (async () => {
      try {
        const r = await get('/categories');
        setCategories(r.items || r);
      } catch {
        // non-fatal on reports page
      }
    })();
  }, []);

  // timeline rows for display (daily for month, monthly for year)
  const timeline = useMemo(() => {
    if (!data) return [];
    if (mode === 'month') {
      // data.daily: [{ date:'YYYY-MM-DD', income, expense, net }]
      return (data.daily || []).map(d => ({
        label: d.date,
        income: Number(d.income),
        expense: Number(d.expense),
        net: Number(d.net),
      }));
    } else {
      // data.monthly: [{ month:'YYYY-MM', income, expense, net }]
      return (data.monthly || []).map(m => ({
        label: m.month,
        income: Number(m.income),
        expense: Number(m.expense),
        net: Number(m.net),
      }));
    }
  }, [data, mode]);

  return (
    <div className="container">
      <h2>Reports</h2>

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
        {err && <span className="error" style={{ marginLeft: 8 }}>{String(err)}</span>}
      </div>

      {data && (
        <>
          <div className="card">
            <b>Totals ({mode})</b>
            <div>Income: ${fmt(data.totals?.income)}</div>
            <div>Expense: ${fmt(data.totals?.expense)}</div>
            <div>Net: ${fmt(data.totals?.net)}</div>
          </div>

          <div className="card">
            <b>By Category ({mode})</b>
            <table className="table">
              <thead>
                <tr><th>Category</th><th>Type</th><th>Total</th></tr>
              </thead>
              <tbody>
                {(data.byCategory || []).map((r,i)=>(
                  <tr key={i}>
                    <td>{r.category_name || 'Uncategorized'}</td>
                    <td>{r.type}</td>
                    <td>${fmt(r.total)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>

          <div className="card">
            <b>{mode === 'month' ? 'Daily' : 'Monthly'} Breakdown</b>
            <table className="table">
              <thead>
                <tr>
                  <th>{mode === 'month' ? 'Date' : 'Month'}</th>
                  <th>Income</th>
                  <th>Expense</th>
                  <th>Net</th>
                </tr>
              </thead>
              <tbody>
                {timeline.map((r,i)=>(
                  <tr key={i}>
                    <td>{r.label}</td>
                    <td>${fmt(r.income)}</td>
                    <td>${fmt(r.expense)}</td>
                    <td>${fmt(r.net)}</td>
                  </tr>
                ))}
                {!timeline.length && (
                  <tr><td colSpan="4" style={{ textAlign:'center', padding:'1rem' }}>No data</td></tr>
                )}
              </tbody>
            </table>
          </div>
        </>
      )}
    </div>
  );
}
