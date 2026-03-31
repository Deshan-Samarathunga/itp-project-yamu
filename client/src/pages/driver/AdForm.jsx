import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function AdForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ title: '', description: '', dailyRate: '', serviceArea: '', languages: '', vehicleTypes: '' });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (isEdit) {
      API.get(`/driver-ads/${id}`).then(r => {
        const d = r.data;
        setForm({ title: d.title||'', description: d.description||'', dailyRate: d.dailyRate||'', serviceArea: d.serviceArea||'', languages: d.languages?.join(', ')||'', vehicleTypes: d.vehicleTypes?.join(', ')||'' });
      }).catch(() => {});
    }
  }, [id]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError('');
    const data = { ...form, dailyRate: Number(form.dailyRate), languages: form.languages.split(',').map(s => s.trim()).filter(Boolean), vehicleTypes: form.vehicleTypes.split(',').map(s => s.trim()).filter(Boolean) };
    try {
      if (isEdit) await API.put(`/driver-ads/${id}`, data);
      else await API.post('/driver-ads', data);
      navigate('/driver/ads');
    } catch (err) { setError(err.response?.data?.message || 'Failed'); }
    finally { setLoading(false); }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card" style={{ maxWidth: 700 }}>
          <div className="form-header"><h2>{isEdit ? 'Edit Ad' : 'Create New Ad'}</h2></div>
          {error && <div className="alert alert-danger">{error}</div>}
          <form onSubmit={handleSubmit}>
            <div className="form-group"><label>Ad Title</label><input value={form.title} onChange={e => setForm(p => ({ ...p, title: e.target.value }))} required /></div>
            <div className="form-row">
              <div className="form-group"><label>Daily Rate (Rs.)</label><input type="number" value={form.dailyRate} onChange={e => setForm(p => ({ ...p, dailyRate: e.target.value }))} required /></div>
              <div className="form-group"><label>Service Area</label><input value={form.serviceArea} onChange={e => setForm(p => ({ ...p, serviceArea: e.target.value }))} required /></div>
            </div>
            <div className="form-group"><label>Languages (comma separated)</label><input value={form.languages} onChange={e => setForm(p => ({ ...p, languages: e.target.value }))} placeholder="Sinhala, English, Tamil" /></div>
            <div className="form-group"><label>Vehicle Types (comma separated)</label><input value={form.vehicleTypes} onChange={e => setForm(p => ({ ...p, vehicleTypes: e.target.value }))} placeholder="Car, Van, SUV" /></div>
            <div className="form-group"><label>Description</label><textarea rows={4} value={form.description} onChange={e => setForm(p => ({ ...p, description: e.target.value }))} required /></div>
            <button type="submit" className="btn btn-primary" disabled={loading}>{loading ? 'Saving...' : isEdit ? 'Update Ad' : 'Create Ad'}</button>
          </form>
        </div>
      </main>
    </div>
  );
}
