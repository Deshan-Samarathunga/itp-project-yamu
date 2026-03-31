import { useState } from 'react';
import { FiMapPin, FiPhone, FiMail } from 'react-icons/fi';

export default function Contact() {
  const [form, setForm] = useState({ name: '', email: '', subject: '', message: '' });
  const [sent, setSent] = useState(false);

  const handleSubmit = (e) => { e.preventDefault(); setSent(true); };

  return (
    <div className="page-content">
      <div className="page-header">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you</p>
      </div>
      <section style={{ padding: '4rem 0' }}>
        <div className="container">
          <div className="row" style={{ gap: '3rem' }}>
            <div style={{ flex: 1 }}>
              <h2 style={{ fontSize: '1.75rem', fontWeight: 800, marginBottom: '1.5rem' }}>Get In Touch</h2>
              <div style={{ display: 'flex', flexDirection: 'column', gap: '1.5rem' }}>
                {[
                  { icon: <FiMapPin />, title: 'Address', text: '155 Galle Road, Colombo 03, Sri Lanka' },
                  { icon: <FiPhone />, title: 'Phone', text: '+94 77 123 4567' },
                  { icon: <FiMail />, title: 'Email', text: 'info@yamu.lk' },
                ].map((c, i) => (
                  <div key={i} style={{ display: 'flex', gap: '1rem', alignItems: 'flex-start' }}>
                    <div className="stat-icon amber" style={{ width: 48, height: 48, fontSize: '1.2rem', flexShrink: 0 }}>{c.icon}</div>
                    <div>
                      <h4 style={{ marginBottom: '0.25rem' }}>{c.title}</h4>
                      <p style={{ color: 'var(--text-light)' }}>{c.text}</p>
                    </div>
                  </div>
                ))}
              </div>
            </div>
            <div style={{ flex: 1 }}>
              <div className="form-card">
                {sent ? (
                  <div className="alert alert-success">Thank you! Your message has been sent. We'll get back to you soon.</div>
                ) : (
                  <form onSubmit={handleSubmit}>
                    <div className="form-row">
                      <div className="form-group">
                        <label>Name</label>
                        <input value={form.name} onChange={e => setForm(p => ({ ...p, name: e.target.value }))} required />
                      </div>
                      <div className="form-group">
                        <label>Email</label>
                        <input type="email" value={form.email} onChange={e => setForm(p => ({ ...p, email: e.target.value }))} required />
                      </div>
                    </div>
                    <div className="form-group">
                      <label>Subject</label>
                      <input value={form.subject} onChange={e => setForm(p => ({ ...p, subject: e.target.value }))} required />
                    </div>
                    <div className="form-group">
                      <label>Message</label>
                      <textarea rows={5} value={form.message} onChange={e => setForm(p => ({ ...p, message: e.target.value }))} required />
                    </div>
                    <button type="submit" className="btn btn-primary btn-block">Send Message</button>
                  </form>
                )}
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  );
}
