import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function StaffVehicles() {
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/vehicles/my').then(r => setVehicles(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  const deleteV = async (id) => {
    if (!window.confirm('Delete vehicle?')) return;
    try { await API.delete(`/vehicles/${id}`); setVehicles(p => p.filter(v => v._id !== id)); } catch {}
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1.5rem' }}>
          <h2>My Vehicles</h2>
          <Link to="/staff/vehicles/new" className="btn btn-primary btn-sm">+ Add Vehicle</Link>
        </div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : vehicles.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Vehicle</th><th>Brand</th><th>Price/Day</th><th>Location</th><th>Listing</th><th>Availability</th><th>Actions</th></tr></thead>
              <tbody>{vehicles.map(v => (
                <tr key={v._id}>
                  <td style={{ fontWeight: 600 }}>{v.vehicleTitle}</td>
                  <td>{v.vehicleBrand}</td>
                  <td>Rs.{v.price?.toLocaleString()}</td>
                  <td>{v.location}</td>
                  <td><span className={`badge ${v.listingStatus==='approved'?'badge-success':v.listingStatus==='rejected'?'badge-danger':'badge-warning'}`}>{v.listingStatus}</span></td>
                  <td><span className={`badge ${v.availabilityStatus==='available'?'badge-success':'badge-info'}`}>{v.availabilityStatus}</span></td>
                  <td>
                    <Link to={`/staff/vehicles/edit/${v._id}`} className="btn btn-outline btn-sm" style={{ marginRight: 4 }}>Edit</Link>
                    <button className="btn btn-danger btn-sm" onClick={() => deleteV(v._id)}>Delete</button>
                  </td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No vehicles yet</h3><Link to="/staff/vehicles/new" className="btn btn-primary" style={{ marginTop: '1rem' }}>Add Vehicle</Link></div>}
      </main>
    </div>
  );
}
