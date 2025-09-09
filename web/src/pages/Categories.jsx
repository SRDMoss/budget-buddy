import { useEffect, useRef, useState } from 'react';
import { get, post, patch, del } from '../lib/api';

/** Simple modal (no external libs) */
function Modal({ title, open, onClose, children, footer }) {
  if (!open) return null;
  return (
    <div
      role="dialog"
      aria-modal="true"
      className="modal-overlay"
      style={{
        position:'fixed', inset:0, background:'rgba(0,0,0,.35)',
        display:'flex', alignItems:'center', justifyContent:'center', zIndex:1000
      }}
      onClick={(e)=>{ if (e.target === e.currentTarget) onClose(); }}
    >
      <div className="card" style={{ width:'min(640px, 94vw)', background:'#fff', maxHeight:'90vh', overflow:'auto' }}>
        <div style={{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:8 }}>
          <h3 style={{ margin:0 }}>{title}</h3>
          <button onClick={onClose} aria-label="Close">✕</button>
        </div>
        <div>{children}</div>
        {footer && <div style={{ marginTop:12, display:'flex', gap:8, justifyContent:'flex-end' }}>{footer}</div>}
      </div>
    </div>
  );
}

/** Color swatch cell */
function Swatch({ hex }) {
  const good = /^#[0-9A-Fa-f]{6}$/.test(hex || '');
  return (
    <div title={hex || ''} style={{
      width:20, height:20, borderRadius:4,
      background: good ? hex : '#ccc', border:'1px solid #bbb'
    }} />
  );
}

export default function Categories() {
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState('');

  // Modal state
  const [addOpen, setAddOpen] = useState(false);
  const [editOpen, setEditOpen] = useState(false);
  const [deleteOpen, setDeleteOpen] = useState(false);

  // Form state
  const [name, setName] = useState('');
  const [color, setColor] = useState('#10B981');
  const [isArchived, setIsArchived] = useState(false);

  // Selected item for edit/delete
  const [selected, setSelected] = useState(null);

  const nameRef = useRef(null);

  const load = async () => {
    setLoading(true);
    try {
      const r = await get('/categories');
      setItems(r.items || []);
      setErr('');
    } catch (e) {
      setErr(e.error || e.message || 'Failed to load categories');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, []);
  useEffect(() => { if (addOpen && nameRef.current) nameRef.current.focus(); }, [addOpen]);

  // --- Helpers ---
  const validate = (n, c) => {
    if (!n || n.trim().length === 0) return 'Name is required';
    if (n.length > 100) return 'Name must be ≤ 100 chars';
    if (c && !/^#[0-9A-Fa-f]{6}$/.test(c)) return 'Color must be a 6-digit hex like #10B981';
    return '';
  };

  // --- Actions ---
  async function createCategory(e) {
    e.preventDefault();
    const msg = validate(name, color);
    if (msg) { setErr(msg); return; }
    try {
      await post('/categories', { name: name.trim(), color_hex: color || null });
      setAddOpen(false);
      setName('');
      setColor('#10B981');
      await load();
    } catch (e) {
      setErr(e.error || e.message || 'Create failed');
    }
  }

  async function openEdit(cat) {
    setSelected(cat);
    setName(cat.name);
    setColor(cat.color_hex || '');
    setIsArchived(!!cat.is_archived);
    setEditOpen(true);
  }

  async function updateCategory(e) {
    e.preventDefault();
    if (!selected) return;
    const msg = validate(name, color);
    if (msg) { setErr(msg); return; }
    try {
      const payload = { name: name.trim(), color_hex: color || null, is_archived: isArchived ? 1 : 0 };
      await patch(`/categories/${selected.id}`, payload);
      setEditOpen(false);
      setSelected(null);
      await load();
    } catch (e) {
      setErr(e.error || e.message || 'Update failed');
    }
  }

  async function confirmDelete(cat) {
    setSelected(cat);
    setDeleteOpen(true);
  }

  async function deleteCategory() {
    if (!selected) return;
    try {
      await del(`/categories/${selected.id}`);
      setDeleteOpen(false);
      setSelected(null);
      await load();
    } catch (e) {
      setErr(e.error || e.message || 'Delete failed');
    }
  }

  return (
    <div className="container">
      <h2>Categories</h2>

      {err && <p className="error" role="alert">{err}</p>}

      <div className="card" style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
        <div>
          <b>Total:</b> {items.length}
        </div>
        <div className="row">
          <button onClick={()=>{ setAddOpen(true); setErr(''); }}>Add Category</button>
          <button onClick={load} aria-label="Refresh list">Refresh</button>
        </div>
      </div>

      <div className="card">
        {loading ? (
          <div>Loading…</div>
        ) : items.length === 0 ? (
          <div>No categories yet. Click <b>Add Category</b> to create one.</div>
        ) : (
          <table className="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Color</th>
                <th>Archived</th>
                <th style={{ width: 160 }}></th>
              </tr>
            </thead>
            <tbody>
              {items.map(c => (
                <tr key={c.id}>
                  <td>{c.name}</td>
                  <td><Swatch hex={c.color_hex} /></td>
                  <td>{c.is_archived ? 'Yes' : 'No'}</td>
                  <td>
                    <div className="row">
                      <button onClick={()=>openEdit(c)}>Edit</button>
                      <button onClick={()=>confirmDelete(c)}>Delete</button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>

      {/* Add Modal */}
      <Modal
        title="Add Category"
        open={addOpen}
        onClose={()=>{ setAddOpen(false); setErr(''); }}
        footer={
          <>
            <button onClick={()=>{ setAddOpen(false); }}>Cancel</button>
            <button onClick={createCategory}>Create</button>
          </>
        }
      >
        <form onSubmit={createCategory} className="row" style={{ gap:'0.75rem' }}>
          <label style={{ width:'100%' }}>
            Name
            <input ref={nameRef} value={name} onChange={e=>setName(e.target.value)} required />
          </label>
          <label>
            Color
            <input type="color" value={color} onChange={e=>setColor(e.target.value)} />
          </label>
        </form>
      </Modal>

      {/* Edit Modal */}
      <Modal
        title={`Edit Category${selected ? `: ${selected.name}` : ''}`}
        open={editOpen}
        onClose={()=>{ setEditOpen(false); setSelected(null); setErr(''); }}
        footer={
          <>
            <button onClick={()=>{ setEditOpen(false); setSelected(null); }}>Cancel</button>
            <button onClick={updateCategory}>Save changes</button>
          </>
        }
      >
        <form onSubmit={updateCategory} className="row" style={{ gap:'0.75rem' }}>
          <label style={{ width:'100%' }}>
            Name
            <input value={name} onChange={e=>setName(e.target.value)} required />
          </label>
          <label>
            Color
            <input type="color" value={color || '#cccccc'} onChange={e=>setColor(e.target.value)} />
          </label>
          <label style={{ display:'flex', alignItems:'center', gap:8 }}>
            <input type="checkbox" checked={isArchived} onChange={e=>setIsArchived(e.target.checked)} />
            Archived
          </label>
        </form>
      </Modal>

      {/* Delete Modal */}
      <Modal
        title="Delete Category"
        open={deleteOpen}
        onClose={()=>{ setDeleteOpen(false); setSelected(null); setErr(''); }}
        footer={
          <>
            <button onClick={()=>{ setDeleteOpen(false); setSelected(null); }}>Cancel</button>
            <button onClick={deleteCategory}>Delete</button>
          </>
        }
      >
        <p>
          Are you sure you want to delete{' '}
          <b>{selected?.name}</b>? This action cannot be undone.
        </p>
      </Modal>
    </div>
  );
}
