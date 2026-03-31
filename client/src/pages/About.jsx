import { Link } from 'react-router-dom';

export default function About() {
  return (
    <div className="page-content">
      <div className="page-header">
        <h1>About YAMU</h1>
        <p>Your trusted vehicle rental platform in Sri Lanka</p>
      </div>
      <section style={{ padding: '4rem 0' }}>
        <div className="container">
          <div className="row" style={{ alignItems: 'center', gap: '3rem' }}>
            <div style={{ flex: 1 }}>
              <img src="https://images.unsplash.com/photo-1449965408869-ebd3fee7e9ee?w=600&h=400&fit=crop" alt="About" style={{ borderRadius: 'var(--radius)', width: '100%' }} />
            </div>
            <div style={{ flex: 1 }}>
              <h3 style={{ color: 'var(--accent)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: 2, marginBottom: '0.5rem' }}>Our Story</h3>
              <h2 style={{ fontSize: '2rem', fontWeight: 800, marginBottom: '1rem' }}>Making car rental easy & accessible</h2>
              <p style={{ color: 'var(--text-light)', lineHeight: 1.8, marginBottom: '1rem' }}>YAMU is Sri Lanka's premier vehicle rental platform, connecting verified vehicle owners and professional drivers with customers looking for reliable transportation.</p>
              <p style={{ color: 'var(--text-light)', lineHeight: 1.8, marginBottom: '1.5rem' }}>Our platform supports rental centers, independent drivers, and individual car owners — providing a marketplace where anyone can find the perfect vehicle for their needs.</p>
              <Link to="/cars" className="btn btn-primary">Browse Vehicles</Link>
            </div>
          </div>

          <div className="grid-3" style={{ marginTop: '4rem' }}>
            {[
              { num: '150+', label: 'Vehicles Available' },
              { num: '500+', label: 'Happy Customers' },
              { num: '50+', label: 'Verified Drivers' },
            ].map((s, i) => (
              <div key={i} className="card" style={{ textAlign: 'center', padding: '2.5rem' }}>
                <h1 style={{ fontSize: '2.5rem', fontWeight: 800, color: 'var(--accent)' }}>{s.num}</h1>
                <p style={{ color: 'var(--text-light)', fontWeight: 600, marginTop: '0.5rem' }}>{s.label}</p>
              </div>
            ))}
          </div>
        </div>
      </section>
    </div>
  );
}
