import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function AdminBrands() {
  const [brands, setBrands] = useState([]);
  const [loading, setLoading] = useState(true);
  const [form, setForm] = useState({ brandName: '', brandLogo: '' });
  const [editing, setEditing] = useState(null);

  useEffect(() => { API.get('/brands').then(r => setBrands(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      if (editing) {
        const res = await API.put(`/brands/${editing}`, form);
        setBrands(p => p.map(b => b._id === editing ? res.data : b));
      } else {
        const res = await API.post('/brands', form);
        setBrands(p => [...p, res.data]);
      }
      setForm({ brandName: '', brandLogo: '' });
      setEditing(null);
    } catch {}
  };

  const deleteBrand = async (id) => {
    if (!window.confirm('Delete brand?')) return;
    try { await API.delete(`/brands/${id}`); setBrands(p => p.filter(b => b._id !== id)); } catch {}
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>Manage Brands</h2></div>
        <div className="form-card" style={{ maxWidth: 500, marginBottom: '2rem' }}>
          <form onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group"><label>Brand Name</label><input value={form.brandName} onChange={e => setForm(p => ({ ...p, brandName: e.target.value }))} required /></div>
              <div className="form-group"><label>Logo Filename</label><input value={form.brandLogo} onChange={e => setForm(p => ({ ...p, brandLogo: e.target.value }))} /></div>
            </div>
            <button type="submit" className="btn btn-primary btn-sm">{editing ? 'Update' : 'Add Brand'}</button>
            {editing && <button type="button" className="btn btn-outline btn-sm" style={{ marginLeft: 8 }} onClick={() => { setEditing(null); setForm({ brandName:'', brandLogo:'' }); }}>Cancel</button>}
          </form>
        </div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : (
          <div className="table-container">
            <table>
              <thead><tr><th>Brand</th><th>Logo</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>{brands.map(b => (
                <tr key={b._id}>
                  <td style={{ fontWeight: 600 }}>{b.brandName}</td>
                  <td>{b.brandLogo || '-'}</td>
                  <td><span className="badge badge-success">{b.status || 'active'}</span></td>
                  <td>
                    <button className="btn btn-outline btn-sm" style={{marginRight:4}} onClick={() => { setEditing(b._id); setForm({ brandName: b.brandName, brandLogo: b.brandLogo || '' }); }}>Edit</button>
                    <button className="btn btn-danger btn-sm" onClick={() => deleteBrand(b._id)}>Delete</button>
                  </td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  );
}
