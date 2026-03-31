import { useState } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function UpdatePassword() {
  const [form, setForm] = useState({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  });
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setMessage('');

    if (form.newPassword !== form.confirmPassword) {
      setMessage('New password confirmation does not match.');
      return;
    }

    setSaving(true);

    try {
      await API.put('/users/password', {
        currentPassword: form.currentPassword,
        newPassword: form.newPassword
      });
      setMessage('Password updated successfully.');
      setForm({ currentPassword: '', newPassword: '', confirmPassword: '' });
    } catch (error) {
      setMessage(error.response?.data?.message || 'Password update failed.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card" style={{ maxWidth: 640 }}>
          <div className="form-header">
            <h2>Update Password</h2>
          </div>
          {message && <div className={`alert ${message.includes('successfully') ? 'alert-success' : 'alert-danger'}`}>{message}</div>}
          <form onSubmit={handleSubmit}>
            <div className="form-group">
              <label>Current Password</label>
              <input type="password" value={form.currentPassword} onChange={(e) => setForm((prev) => ({ ...prev, currentPassword: e.target.value }))} required />
            </div>
            <div className="form-group">
              <label>New Password</label>
              <input type="password" value={form.newPassword} onChange={(e) => setForm((prev) => ({ ...prev, newPassword: e.target.value }))} required />
            </div>
            <div className="form-group">
              <label>Confirm New Password</label>
              <input type="password" value={form.confirmPassword} onChange={(e) => setForm((prev) => ({ ...prev, confirmPassword: e.target.value }))} required />
            </div>
            <button type="submit" className="btn btn-primary" disabled={saving}>
              {saving ? 'Updating...' : 'Update Password'}
            </button>
          </form>
        </div>
      </main>
    </div>
  );
}
