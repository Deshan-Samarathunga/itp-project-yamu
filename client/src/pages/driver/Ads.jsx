import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function DriverAds() {
  const [ads, setAds] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/driver-ads/my').then(r => setAds(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  const deleteAd = async (id) => {
    if (!window.confirm('Delete this ad?')) return;
    try { await API.delete(`/driver-ads/${id}`); setAds(p => p.filter(a => a._id !== id)); } catch {}
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
          <h2>My Tour Ads</h2>
          <Link to="/driver/ads/new" className="btn btn-primary btn-sm">+ New Ad</Link>
        </div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : ads.length > 0 ? (
          <div className="grid-3">
            {ads.map(ad => (
              <div key={ad._id} className="card" style={{ padding: '1.5rem' }}>
                <h4>{ad.title}</h4>
                <p style={{ color: 'var(--accent)', fontWeight: 700, margin: '0.5rem 0' }}>Rs.{ad.dailyRate?.toLocaleString()} / Day</p>
                <p style={{ color: 'var(--text-light)', fontSize: '0.9rem' }}>{ad.serviceArea}</p>
                <p style={{ color: 'var(--text-light)', fontSize: '0.85rem', margin: '0.5rem 0 1rem' }}>{ad.description?.slice(0, 80)}...</p>
                <span className={`badge ${ad.status==='active'?'badge-success':'badge-warning'}`}>{ad.status || 'active'}</span>
                <div style={{ display: 'flex', gap: '0.5rem', marginTop: '1rem' }}>
                  <Link to={`/driver/ads/edit/${ad._id}`} className="btn btn-outline btn-sm">Edit</Link>
                  <button className="btn btn-danger btn-sm" onClick={() => deleteAd(ad._id)}>Delete</button>
                </div>
              </div>
            ))}
          </div>
        ) : <div className="empty-state"><h3>No ads yet</h3><p>Create your first tour ad to attract customers</p><Link to="/driver/ads/new" className="btn btn-primary" style={{ marginTop: '1rem' }}>Create Ad</Link></div>}
      </main>
    </div>
  );
}
