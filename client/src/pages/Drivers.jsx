import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import API from '../api/axios';

export default function Drivers() {
  const [ads, setAds] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    API.get('/driver-ads').then(r => setAds(r.data)).catch(() => {}).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="page-content"><div className="spinner-container"><div className="spinner"></div></div></div>;

  return (
    <div className="page-content">
      <div className="page-header">
        <h1>Find a Driver</h1>
        <p>Professional and verified drivers for your journey</p>
      </div>
      <section style={{ padding: '3rem 0' }}>
        <div className="container">
          {ads.length > 0 ? (
            <div className="grid-3">
              {ads.map(ad => (
                <div key={ad._id} className="card" style={{ padding: '1.5rem', textAlign: 'center' }}>
                  <img
                    src={`https://ui-avatars.com/api/?name=${encodeURIComponent(ad.driver?.fullName || 'D')}&background=f0a500&color=0d1b2a&bold=true&size=80`}
                    alt={ad.driver?.fullName}
                    style={{ width: 80, height: 80, borderRadius: '50%', margin: '0 auto 1rem', border: '3px solid var(--accent)' }}
                  />
                  <h4>{ad.driver?.fullName || 'Driver'}</h4>
                  <p style={{ color: 'var(--accent)', fontWeight: 700, margin: '0.5rem 0' }}>Rs.{ad.dailyRate?.toLocaleString()} / Day</p>
                  <p style={{ color: 'var(--text-light)', fontSize: '0.9rem', marginBottom: '0.5rem' }}>{ad.serviceArea}</p>
                  <p style={{ color: 'var(--text-light)', fontSize: '0.85rem', marginBottom: '1rem' }}>{ad.description?.slice(0, 100)}...</p>
                  <div style={{ display: 'flex', gap: '0.5rem', flexWrap: 'wrap', justifyContent: 'center', marginBottom: '1rem' }}>
                    {ad.languages?.map((l, i) => <span key={i} className="badge badge-info">{l}</span>)}
                  </div>
                  <Link to={`/drivers/${ad._id}`} className="btn btn-primary btn-sm btn-block">View Details</Link>
                </div>
              ))}
            </div>
          ) : (
            <div className="empty-state">
              <h3>No driver ads available yet</h3>
              <p>Check back later for available drivers</p>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
