import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { FiCalendar, FiDollarSign, FiStar, FiTrendingUp } from 'react-icons/fi';

export default function DriverDashboard() {
  const [stats, setStats] = useState({ bookings: 0, earnings: 0, reviews: 0, ads: 0 });
  const [bookings, setBookings] = useState([]);

  useEffect(() => {
    API.get('/bookings/provider').then(r => {
      const b = r.data;
      setBookings(b.slice(0, 5));
      setStats({
        bookings: b.length,
        earnings: b.filter(x => x.bookingStatus === 'completed').reduce((s, x) => s + (x.total || 0), 0),
        reviews: 0,
        ads: 0
      });
    }).catch(() => {});
    API.get('/driver-ads/my').then(r => setStats(p => ({ ...p, ads: r.data.length }))).catch(() => {});
  }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>Driver Dashboard</h2></div>
        <div className="stats-grid">
          <div className="stat-card"><div className="stat-icon blue"><FiCalendar /></div><div className="stat-info"><h3>{stats.bookings}</h3><p>Total Bookings</p></div></div>
          <div className="stat-card"><div className="stat-icon green"><FiDollarSign /></div><div className="stat-info"><h3>Rs.{stats.earnings.toLocaleString()}</h3><p>Total Earnings</p></div></div>
          <div className="stat-card"><div className="stat-icon amber"><FiStar /></div><div className="stat-info"><h3>{stats.ads}</h3><p>Active Ads</p></div></div>
          <div className="stat-card"><div className="stat-icon purple"><FiTrendingUp /></div><div className="stat-info"><h3>{stats.reviews}</h3><p>Reviews</p></div></div>
        </div>
        <h3 style={{ marginBottom: '1rem' }}>Recent Bookings</h3>
        {bookings.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Booking #</th><th>Customer</th><th>Dates</th><th>Amount</th><th>Status</th></tr></thead>
              <tbody>{bookings.map(b => (
                <tr key={b._id}>
                  <td style={{ fontWeight: 600 }}>{b.bookingNo}</td>
                  <td>{b.customer?.fullName || '-'}</td>
                  <td>{new Date(b.startDate).toLocaleDateString()} - {new Date(b.endDate).toLocaleDateString()}</td>
                  <td>Rs.{b.total?.toLocaleString()}</td>
                  <td><span className={`badge ${b.bookingStatus==='completed'?'badge-success':b.bookingStatus==='confirmed'?'badge-info':'badge-warning'}`}>{b.bookingStatus}</span></td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No bookings yet</h3></div>}
      </main>
    </div>
  );
}
