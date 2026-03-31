import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function AdminPromotions() {
  const [promos, setPromos] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/promotions').then(r => setPromos(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  const deleteP = async (id) => {
    if (!window.confirm('Delete promotion?')) return;
    try { await API.delete(`/promotions/${id}`); setPromos(p => p.filter(x => x._id !== id)); } catch {}
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
          <h2>Promotions</h2>
          <Link to="/admin/promotions/new" className="btn btn-primary btn-sm">+ Add Promotion</Link>
        </div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : promos.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Code</th><th>Discount</th><th>Type</th><th>Valid Until</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>{promos.map(p => (
                <tr key={p._id}>
                  <td style={{ fontWeight: 600 }}>{p.code}</td>
                  <td>{p.discountType==='percentage'?`${p.discount}%`:`Rs.${p.discount}`}</td>
                  <td>{p.discountType}</td>
                  <td>{p.validUntil ? new Date(p.validUntil).toLocaleDateString() : '-'}</td>
                  <td><span className={`badge ${p.status==='active'?'badge-success':'badge-neutral'}`}>{p.status||'active'}</span></td>
                  <td>
                    <Link to={`/admin/promotions/edit/${p._id}`} className="btn btn-outline btn-sm" style={{marginRight:4}}>Edit</Link>
                    <button className="btn btn-danger btn-sm" onClick={() => deleteP(p._id)}>Delete</button>
                  </td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No promotions</h3><Link to="/admin/promotions/new" className="btn btn-primary" style={{marginTop:'1rem'}}>Create Promotion</Link></div>}
      </main>
    </div>
  );
}
