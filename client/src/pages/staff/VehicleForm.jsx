import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function VehicleForm() {
  const { id } = useParams();
  const navigate = useNavigate();
  const isEdit = !!id;
  const [form, setForm] = useState({ vehicleTitle:'', vehicleBrand:'Toyota', vehicleDesc:'', price:'', transmission:'Automatic', fuelType:'Petrol', year:'2024', engineCapacity:'', capacity:'5', location:'', registrationNumber:'' });
  const [features, setFeatures] = useState({ airConditioner:false, powerDoorLocks:false, antiLockBrakingSystem:false, brakeAssist:false, powerSteering:false, driverAirbag:false, passengerAirbag:false, powerWindows:false, cdPlayer:false });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (isEdit) {
      API.get(`/vehicles/${id}`).then(r => {
        const v = r.data;
        setForm({ vehicleTitle:v.vehicleTitle||'', vehicleBrand:v.vehicleBrand||'', vehicleDesc:v.vehicleDesc||'', price:v.price||'', transmission:v.transmission||'Automatic', fuelType:v.fuelType||'Petrol', year:v.year||'', engineCapacity:v.engineCapacity||'', capacity:v.capacity||5, location:v.location||'', registrationNumber:v.registrationNumber||'' });
        if (v.features) setFeatures(v.features);
      });
    }
  }, [id]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true); setError('');
    const data = { ...form, price: Number(form.price), year: Number(form.year), capacity: Number(form.capacity), features };
    try {
      if (isEdit) await API.put(`/vehicles/${id}`, data);
      else await API.post('/vehicles', data);
      navigate('/staff/vehicles');
    } catch (err) { setError(err.response?.data?.message || 'Failed'); }
    finally { setLoading(false); }
  };

  const brands = ['Toyota','Suzuki','Nissan','BMW','Audi','Benz','Ford','KIA','Tesla','Volkswagen','Mitsubishi','Peugeot'];

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card" style={{ maxWidth: 800 }}>
          <div className="form-header"><h2>{isEdit ? 'Edit Vehicle' : 'Add New Vehicle'}</h2></div>
          {error && <div className="alert alert-danger">{error}</div>}
          <form onSubmit={handleSubmit}>
            <div className="form-row">
              <div className="form-group"><label>Vehicle Title</label><input value={form.vehicleTitle} onChange={e=>setForm(p=>({...p,vehicleTitle:e.target.value}))} required /></div>
              <div className="form-group"><label>Brand</label><select value={form.vehicleBrand} onChange={e=>setForm(p=>({...p,vehicleBrand:e.target.value}))}>{brands.map(b=><option key={b}>{b}</option>)}</select></div>
            </div>
            <div className="form-row">
              <div className="form-group"><label>Price / Day (Rs.)</label><input type="number" value={form.price} onChange={e=>setForm(p=>({...p,price:e.target.value}))} required /></div>
              <div className="form-group"><label>Year</label><input type="number" value={form.year} onChange={e=>setForm(p=>({...p,year:e.target.value}))} required /></div>
            </div>
            <div className="form-row">
              <div className="form-group"><label>Transmission</label><select value={form.transmission} onChange={e=>setForm(p=>({...p,transmission:e.target.value}))}><option>Automatic</option><option>Manual</option></select></div>
              <div className="form-group"><label>Fuel Type</label><select value={form.fuelType} onChange={e=>setForm(p=>({...p,fuelType:e.target.value}))}><option>Petrol</option><option>Diesel</option><option>Electric</option><option>Hybrid</option></select></div>
            </div>
            <div className="form-row">
              <div className="form-group"><label>Engine Capacity (CC)</label><input value={form.engineCapacity} onChange={e=>setForm(p=>({...p,engineCapacity:e.target.value}))} /></div>
              <div className="form-group"><label>Passengers</label><input type="number" value={form.capacity} onChange={e=>setForm(p=>({...p,capacity:e.target.value}))} /></div>
            </div>
            <div className="form-row">
              <div className="form-group"><label>Location</label><input value={form.location} onChange={e=>setForm(p=>({...p,location:e.target.value}))} required /></div>
              <div className="form-group"><label>Registration No.</label><input value={form.registrationNumber} onChange={e=>setForm(p=>({...p,registrationNumber:e.target.value}))} required /></div>
            </div>
            <div className="form-group"><label>Description</label><textarea rows={3} value={form.vehicleDesc} onChange={e=>setForm(p=>({...p,vehicleDesc:e.target.value}))} /></div>
            <div className="form-group">
              <label>Features</label>
              <div style={{display:'grid',gridTemplateColumns:'repeat(3,1fr)',gap:'0.75rem'}}>
                {Object.entries(features).map(([k,v])=>(
                  <label key={k} style={{display:'flex',alignItems:'center',gap:'0.5rem',fontSize:'0.9rem',cursor:'pointer'}}>
                    <input type="checkbox" checked={v} onChange={()=>setFeatures(p=>({...p,[k]:!v}))} />
                    {k.replace(/([A-Z])/g,' $1').trim()}
                  </label>
                ))}
              </div>
            </div>
            <button type="submit" className="btn btn-primary" disabled={loading}>{loading?'Saving...':isEdit?'Update Vehicle':'Add Vehicle'}</button>
          </form>
        </div>
      </main>
    </div>
  );
}
