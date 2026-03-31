import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function AdminVehicles() {
  const [vehicles, setVehicles] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/admin/vehicles').then(r => setVehicles(r.data)).catch(() => API.get('/vehicles').then(r => setVehicles(r.data)).catch(() => {})).finally(() => setLoading(false)); }, []);

  const updateListing = async (id, status) => {
    try { await API.put(`/vehicles/${id}`, { listingStatus: status }); setVehicles(p => p.map(v => v._id===id?{...v,listingStatus:status}:v)); } catch {}
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>All Vehicles</h2></div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : (
          <div className="table-container">
            <table>
              <thead><tr><th>Vehicle</th><th>Brand</th><th>Owner</th><th>Price</th><th>Listing</th><th>Actions</th></tr></thead>
              <tbody>{vehicles.map(v => (
                <tr key={v._id}>
                  <td style={{ fontWeight: 600 }}>{v.vehicleTitle}</td>
                  <td>{v.vehicleBrand}</td>
                  <td>{v.owner?.fullName || '-'}</td>
                  <td>Rs.{v.price?.toLocaleString()}</td>
                  <td><span className={`badge ${v.listingStatus==='approved'?'badge-success':v.listingStatus==='rejected'?'badge-danger':'badge-warning'}`}>{v.listingStatus}</span></td>
                  <td>
                    {v.listingStatus==='pending' && <>
                      <button className="btn btn-primary btn-sm" style={{marginRight:4}} onClick={()=>updateListing(v._id,'approved')}>Approve</button>
                      <button className="btn btn-danger btn-sm" onClick={()=>updateListing(v._id,'rejected')}>Reject</button>
                    </>}
                    {v.listingStatus==='approved' && <button className="btn btn-outline btn-sm" onClick={()=>updateListing(v._id,'inactive')}>Deactivate</button>}
                  </td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  );
}
