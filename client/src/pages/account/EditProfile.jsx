import { useEffect, useState } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { useAuth } from '../../context/AuthContext';

export default function EditProfile() {
  const { refreshUser } = useAuth();
  const [form, setForm] = useState({
    fullName: '',
    phone: '',
    address: '',
    city: '',
    dob: '',
    bio: ''
  });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    API.get('/users/profile')
      .then((res) => {
        const user = res.data.user;
        setForm({
          fullName: user?.fullName || '',
          phone: user?.phone || '',
          address: user?.address || '',
          city: user?.city || '',
          dob: user?.dob || '',
          bio: user?.bio || ''
        });
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    try {
      await API.put('/users/profile', form);
      await refreshUser();
      setMessage('Common profile updated successfully.');
    } catch (error) {
      setMessage(error.response?.data?.message || 'Profile update failed.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card" style={{ maxWidth: 820 }}>
          <div className="form-header">
            <h2>Edit Profile</h2>
          </div>

          {loading ? (
            <div className="spinner-container"><div className="spinner"></div></div>
          ) : (
            <>
              {message && <div className={`alert ${message.includes('successfully') ? 'alert-success' : 'alert-danger'}`}>{message}</div>}
              <form onSubmit={handleSubmit}>
                <div className="form-row">
                  <div className="form-group">
                    <label>Full Name</label>
                    <input value={form.fullName} onChange={(e) => setForm((prev) => ({ ...prev, fullName: e.target.value }))} required />
                  </div>
                  <div className="form-group">
                    <label>Phone</label>
                    <input value={form.phone} onChange={(e) => setForm((prev) => ({ ...prev, phone: e.target.value }))} />
                  </div>
                </div>
                <div className="form-row">
                  <div className="form-group">
                    <label>City</label>
                    <input value={form.city} onChange={(e) => setForm((prev) => ({ ...prev, city: e.target.value }))} />
                  </div>
                  <div className="form-group">
                    <label>Date of Birth</label>
                    <input type="date" value={form.dob} onChange={(e) => setForm((prev) => ({ ...prev, dob: e.target.value }))} />
                  </div>
                </div>
                <div className="form-group">
                  <label>Address</label>
                  <input value={form.address} onChange={(e) => setForm((prev) => ({ ...prev, address: e.target.value }))} />
                </div>
                <div className="form-group">
                  <label>Bio</label>
                  <textarea rows="4" value={form.bio} onChange={(e) => setForm((prev) => ({ ...prev, bio: e.target.value }))} />
                </div>
                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? 'Saving...' : 'Save Changes'}
                </button>
              </form>
            </>
          )}
        </div>
      </main>
    </div>
  );
}
