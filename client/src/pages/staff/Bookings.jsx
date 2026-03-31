import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function StaffBookings() {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/bookings/provider').then(r => setBookings(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  const updateStatus = async (id, status) => {
    try { await API.put(`/bookings/${id}/status`, { status }); setBookings(p => p.map(b => b._id === id ? { ...b, bookingStatus: status } : b)); } catch {}
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>Bookings</h2></div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : bookings.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Booking #</th><th>Customer</th><th>Vehicle</th><th>Dates</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
              <tbody>{bookings.map(b => (
                <tr key={b._id}>
                  <td style={{ fontWeight: 600 }}>{b.bookingNo}</td>
                  <td>{b.customer?.fullName || '-'}</td>
                  <td>{b.vehicle?.vehicleTitle || '-'}</td>
                  <td>{new Date(b.startDate).toLocaleDateString()} - {new Date(b.endDate).toLocaleDateString()}</td>
                  <td>Rs.{b.total?.toLocaleString()}</td>
                  <td><span className={`badge ${b.bookingStatus==='completed'?'badge-success':b.bookingStatus==='confirmed'?'badge-info':b.bookingStatus==='cancelled'||b.bookingStatus==='rejected'?'badge-danger':'badge-warning'}`}>{b.bookingStatus}</span></td>
                  <td>
                    {b.bookingStatus === 'pending' && <>
                      <button className="btn btn-primary btn-sm" style={{ marginRight: 4 }} onClick={() => updateStatus(b._id, 'confirmed')}>Confirm</button>
                      <button className="btn btn-danger btn-sm" onClick={() => updateStatus(b._id, 'rejected')}>Reject</button>
                    </>}
                    {b.bookingStatus === 'confirmed' && <button className="btn btn-primary btn-sm" onClick={() => updateStatus(b._id, 'completed')}>Complete</button>}
                  </td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No bookings</h3></div>}
      </main>
    </div>
  );
}
