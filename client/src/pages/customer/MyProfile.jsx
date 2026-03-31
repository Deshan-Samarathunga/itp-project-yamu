import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function MyProfile() {
  const { user, setUser } = useAuth();
  const [form, setForm] = useState({ fullName: '', email: '', phone: '', address: '', city: '', dob: '', bio: '' });
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState('');

  useEffect(() => {
    if (user) {
      API.get('/auth/me').then(r => {
        const u = r.data;
        setForm({ fullName: u.fullName || '', email: u.email || '', phone: u.phone || '', address: u.address || '', city: u.city || '', dob: u.dob || '', bio: u.bio || '' });
      });
    }
  }, [user]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const res = await API.put('/users/profile', form);
      setUser(prev => ({ ...prev, ...res.data }));
      setMsg('Profile updated successfully!');
      setTimeout(() => setMsg(''), 3000);
    } catch (err) {
      setMsg(err.response?.data?.message || 'Update failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card" style={{ maxWidth: 700 }}>
          <div className="form-header"><h2>My Profile</h2></div>
          {msg && <div className={`alert ${msg.includes('success') ? 'alert-success' : 'alert-danger'}`}>{msg}</div>}
          <form onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group"><label>Full Name</label><input value={form.fullName} onChange={e => setForm(p => ({ ...p, fullName: e.target.value }))} required /></div>
              <div className="form-group"><label>Email</label><input type="email" value={form.email} onChange={e => setForm(p => ({ ...p, email: e.target.value }))} required /></div>
            </div>
            <div className="form-row">
              <div className="form-group"><label>Phone</label><input value={form.phone} onChange={e => setForm(p => ({ ...p, phone: e.target.value }))} /></div>
              <div className="form-group"><label>City</label><input value={form.city} onChange={e => setForm(p => ({ ...p, city: e.target.value }))} /></div>
            </div>
            <div className="form-group"><label>Address</label><input value={form.address} onChange={e => setForm(p => ({ ...p, address: e.target.value }))} /></div>
            <div className="form-group"><label>Date of Birth</label><input type="date" value={form.dob} onChange={e => setForm(p => ({ ...p, dob: e.target.value }))} /></div>
            <div className="form-group"><label>Bio</label><textarea rows={3} value={form.bio} onChange={e => setForm(p => ({ ...p, bio: e.target.value }))} /></div>
            <button type="submit" className="btn btn-primary" disabled={loading}>{loading ? 'Saving...' : 'Update Profile'}</button>
          </form>
        </div>
      </main>
    </div>
  );
}
