import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { FiCalendar, FiEye } from 'react-icons/fi';

export default function MyBookings() {
  const { user } = useAuth();
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    API.get('/bookings/my').then(r => setBookings(r.data)).catch(() => {}).finally(() => setLoading(false));
  }, []);

  const statusBadge = (s) => {
    const map = { pending: 'badge-warning', confirmed: 'badge-info', completed: 'badge-success', cancelled: 'badge-danger', rejected: 'badge-danger' };
    return <span className={`badge ${map[s] || 'badge-neutral'}`}>{s}</span>;
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>My Bookings</h2>
        </div>
        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : bookings.length > 0 ? (
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>Booking #</th>
                  <th>Vehicle</th>
                  <th>Dates</th>
                  <th>Total</th>
                  <th>Status</th>
                  <th>Payment</th>
                </tr>
              </thead>
              <tbody>
                {bookings.map(b => (
                  <tr key={b._id}>
                    <td style={{ fontWeight: 600 }}>{b.bookingNo}</td>
                    <td>{b.vehicle?.vehicleTitle || 'Driver Service'}</td>
                    <td>{new Date(b.startDate).toLocaleDateString()} - {new Date(b.endDate).toLocaleDateString()}</td>
                    <td style={{ fontWeight: 600 }}>Rs.{b.total?.toLocaleString()}</td>
                    <td>{statusBadge(b.bookingStatus)}</td>
                    <td>{statusBadge(b.paymentStatus)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="empty-state">
            <FiCalendar style={{ fontSize: '3rem', color: 'var(--gray-300)' }} />
            <h3>No bookings yet</h3>
            <p>Browse our vehicles and make your first booking!</p>
            <Link to="/cars" className="btn btn-primary" style={{ marginTop: '1rem' }}>Browse Cars</Link>
          </div>
        )}
      </main>
    </div>
  );
}
