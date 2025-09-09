import { useEffect, useState } from 'react';
import { get, post } from '../lib/api';

const fmt = (n) => {
  if (n == null || isNaN(Number(n))) return '0.00';
  return Number(n).toFixed(2);
};

export default function Transactions() {
  // Period controls
  const [mode, setMode] = useState('month'); // 'month' | 'year'
  const [month, setMonth] = useState(new Date().toISOString().slice(0,7));
  const [year, setYear] = useState(String(new Date().getFullYear()));

  // Filters
  const [type, setType] = useState(''); // '', 'income', 'expense'
  const [categoryId, setCategoryId] = useState('');
  const [categories, setCategories] = useState([]);

  // Paging
  const [limit, setLimit] = useState(50);
  const [offset, setOffset] = useState(0);

  // Data
  const [rows, setRows] = useState([]);
  const [err, setErr] = useState('');

  // Create modal state
  const [showCreate, setShowCreate] = useState(false);
  const [cType, setCType] = useState('expense'); // default
  const [cAmount, setCAmount] = useState('');
  const [cCurrency, setCCurrency] = useState('USD');
  const [cDate, setCDate] = useState(new Date().toISOString().slice(0,10));
  const [cCategoryId, setCCategoryId] = useState('');
  const [cPayee, setCPayee] = useState('');
  const [cNote, setCNote] = useState('');
  const [cErr, setCErr] = useState('');

  // Build querystring
  function buildQuery() {
    const p = new URLSearchParams();
    if (mode === 'month') p.set('month', month);
    else p.set('year', year);

    if (type) p.set('type', type);
    if (categoryId) p.set('category_id', String(categoryId));
    if (limit) p.set('limit', String(limit));
    if (offset) p.set('offset', String(offset));
    return p.toString();
  }

  async function load() {
    try {
      const r = await get(`/transactions?${buildQuery()}`);
      setRows(r.items || []);
      setErr('');
    } catch (e) {
      setErr(e.error || e.message || 'Failed to load transactions');
      setRows([]);
    }
  }

  useEffect(() => { load(); }, [mode, month, year, type, categoryId, limit, offset]);

  // Load categories once
  useEffect(() => {
    (async () => {
      try {
        const r = await get('/categories');
        setCategories(r.items || r);
      } catch {
        // non-fatal
      }
    })();
  }, []);

  async function createTxn(e) {
    e?.preventDefault?.();
    setCErr('');
    const amt = parseFloat(cAmount);
    if (!cType || Number.isNaN(amt) || amt <= 0 || !cDate) {
      setCErr('Type, positive amount, and date are required.');
      return;
    }
    try {
      await post('/transactions', {
        type: cType,
        amount: Number(amt.toFixed(2)),
        currency: cCurrency || 'USD',
        txn_date: cDate,
        category_id: cCategoryId ? Number(cCategoryId) : null,
        payee: cPayee || null,
        note: cNote || null,
      });
      // If new tx falls outside current view, nudge the period into view
      const newYM = cDate.slice(0,7);
      const newY = cDate.slice(0,4);
      if (mode === 'month' && month !== newYM) setMonth(newYM);
      if (mode === 'year' && year !== newY) setYear(newY);
      setOffset(0);
      await load();
      setShowCreate(false);
      // reset lightweight fields
      setCAmount(''); setCPayee(''); setCNote(''); setCCategoryId('');
    } catch (e) {
      setCErr(e.error || e.message || 'Failed to create transaction');
    }
  }

  return (
    <div className="container">
      <h2>Transactions</h2>

      <div className="card" style={{ display:'flex', gap:'1rem', alignItems:'center', flexWrap:'wrap' }}>
        {/* Period picker */}
        <label>View
          <select value={mode} onChange={e=>{ setMode(e.target.value); setOffset(0); }} style={{ marginLeft: 8 }}>
            <option value="month">Month</option>
            <option value="year">Year</option>
          </select>
        </label>

        {mode === 'month' ? (
          <label>Month
            <input
              type="month"
              value={month}
              onChange={e=>{ setMonth(e.target.value); setOffset(0); }}
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
              onChange={e=>{ setYear(e.target.value.replace(/\D/g, '').slice(0,4)); setOffset(0); }}
              style={{ width: 100, marginLeft: 8 }}
            />
          </label>
        )}

        {/* Filters */}
        <label>Type
          <select value={type} onChange={e=>{ setType(e.target.value); setOffset(0); }} style={{ marginLeft: 8 }}>
            <option value="">All</option>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
          </select>
        </label>

        <label>Category
          <select
            value={categoryId}
            onChange={e=>{ setCategoryId(e.target.value); setOffset(0); }}
            style={{ marginLeft: 8 }}
          >
            <option value="">All</option>
            {categories.map(c => (
              <option key={c.id} value={c.id}>{c.name}</option>
            ))}
          </select>
        </label>

        <button onClick={()=>{ setOffset(0); load(); }}>Refresh</button>
        <button onClick={()=>setShowCreate(true)}>Add Transaction</button>
        {err && <span className="error">{err}</span>}
      </div>

      <div className="card" style={{ marginTop:'1rem', overflowX:'auto' }}>
        <table className="table">
          <thead>
            <tr>
              <th style={{ whiteSpace:'nowrap' }}>Date</th>
              <th>Type</th>
              <th>Category</th>
              <th>Payee</th>
              <th style={{ textAlign:'right' }}>Amount</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((r) => (
              <tr key={r.id}>
                <td>{r.txn_date}</td>
                <td>{r.type}</td>
                <td>{r.category_name || '—'}</td>
                <td>{r.payee || '—'}</td>
                <td style={{ textAlign:'right' }}>${fmt(r.amount)}</td>
              </tr>
            ))}
            {!rows.length && (
              <tr><td colSpan="5" style={{ textAlign:'center', padding:'1rem' }}>No transactions</td></tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Paging controls */}
      <div className="row" style={{ gap:'0.5rem', marginTop:'0.5rem' }}>
        <button disabled={offset===0} onClick={()=>setOffset(Math.max(0, offset - limit))}>Prev</button>
        <button onClick={()=>setOffset(offset + limit)}>Next</button>
        <label>Page size
          <select value={limit} onChange={e=>{ setLimit(parseInt(e.target.value,10)); setOffset(0); }} style={{ marginLeft: 8 }}>
            <option value={25}>25</option>
            <option value={50}>50</option>
            <option value={100}>100</option>
          </select>
        </label>
      </div>

      {/* Create Transaction Modal */}
      {showCreate && (
        <div
          role="dialog"
          aria-modal="true"
          style={{
            position:'fixed', inset:0, background:'rgba(0,0,0,0.4)',
            display:'flex', alignItems:'center', justifyContent:'center', zIndex: 1000
          }}
          onClick={(e)=>{ if (e.target === e.currentTarget) setShowCreate(false); }}
        >
          <form
            onSubmit={createTxn}
            className="card"
            style={{ width:'min(520px, 92vw)', background:'#fff', padding:'1rem', borderRadius:8 }}
          >
            <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
              <b>New Transaction</b>
              <button type="button" onClick={()=>setShowCreate(false)}>✕</button>
            </div>

            <div className="row" style={{ marginTop:'0.5rem', gap:'1rem', flexWrap:'wrap' }}>
              <label>Type
                <select value={cType} onChange={e=>setCType(e.target.value)} style={{ marginLeft:8 }}>
                  <option value="expense">Expense</option>
                  <option value="income">Income</option>
                </select>
              </label>
              <label>Amount
                <input
                  type="number" step="0.01" min="0"
                  value={cAmount}
                  onChange={e=>setCAmount(e.target.value)}
                  style={{ marginLeft:8, width:140 }}
                  required
                />
              </label>
              <label>Currency
                <input
                  value={cCurrency}
                  onChange={e=>setCCurrency(e.target.value.toUpperCase().slice(0,3))}
                  style={{ marginLeft:8, width:80 }}
                />
              </label>
            </div>

            <div className="row" style={{ gap:'1rem', flexWrap:'wrap' }}>
              <label>Date
                <input type="date" value={cDate} onChange={e=>setCDate(e.target.value)} style={{ marginLeft:8 }} required />
              </label>
              <label>Category
                <select value={cCategoryId} onChange={e=>setCCategoryId(e.target.value)} style={{ marginLeft:8 }}>
                  <option value="">(none)</option>
                  {categories.map(c => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                  ))}
                </select>
              </label>
            </div>

            <div className="row" style={{ gap:'1rem', flexWrap:'wrap' }}>
              <label>Payee
                <input value={cPayee} onChange={e=>setCPayee(e.target.value)} style={{ marginLeft:8, width:'min(380px, 70vw)' }} />
              </label>
            </div>
            <div className="row" style={{ gap:'1rem', flexWrap:'wrap' }}>
              <label>Note
                <input value={cNote} onChange={e=>setCNote(e.target.value)} style={{ marginLeft:8, width:'min(380px, 70vw)' }} />
              </label>
            </div>

            {cErr && <div className="error" style={{ marginTop:'0.5rem' }}>{cErr}</div>}

            <div style={{ display:'flex', gap:'0.5rem', justifyContent:'flex-end', marginTop:'0.75rem' }}>
              <button type="button" onClick={()=>setShowCreate(false)}>Cancel</button>
              <button type="submit">Save</button>
            </div>
          </form>
        </div>
      )}
    </div>
  );
}
