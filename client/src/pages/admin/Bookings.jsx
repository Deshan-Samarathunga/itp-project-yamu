import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function AdminBookings() {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/bookings').then(r => setBookings(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>All Bookings</h2></div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : (
          <div className="table-container">
            <table>
              <thead><tr><th>Booking #</th><th>Customer</th><th>Provider</th><th>Vehicle</th><th>Dates</th><th>Total</th><th>Status</th></tr></thead>
              <tbody>{bookings.map(b => (
                <tr key={b._id}>
                  <td style={{ fontWeight: 600 }}>{b.bookingNo}</td>
                  <td>{b.customer?.fullName||'-'}</td>
                  <td>{b.driver?.fullName||'-'}</td>
                  <td>{b.vehicle?.vehicleTitle||'Driver Service'}</td>
                  <td>{new Date(b.startDate).toLocaleDateString()} - {new Date(b.endDate).toLocaleDateString()}</td>
                  <td>Rs.{b.total?.toLocaleString()}</td>
                  <td><span className={`badge ${b.bookingStatus==='completed'?'badge-success':b.bookingStatus==='confirmed'?'badge-info':b.bookingStatus==='cancelled'||b.bookingStatus==='rejected'?'badge-danger':'badge-warning'}`}>{b.bookingStatus}</span></td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  );
}
