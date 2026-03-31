import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { FiUsers, FiTruck, FiCalendar, FiDollarSign, FiAlertCircle, FiShield } from 'react-icons/fi';

export default function AdminDashboard() {
  const [stats, setStats] = useState({});
  useEffect(() => {
    API.get('/admin/dashboard').then(r => setStats(r.data)).catch(() => {});
  }, []);

  const cards = [
    { icon: <FiUsers />, color: 'blue', label: 'Total Users', value: stats.totalUsers || 0, link: '/admin/users' },
    { icon: <FiTruck />, color: 'green', label: 'Vehicles', value: stats.totalVehicles || 0, link: '/admin/vehicles' },
    { icon: <FiCalendar />, color: 'amber', label: 'Bookings', value: stats.totalBookings || 0, link: '/admin/bookings' },
    { icon: <FiDollarSign />, color: 'purple', label: 'Revenue', value: `Rs.${(stats.totalRevenue || 0).toLocaleString()}`, link: '/admin/payments' },
    { icon: <FiAlertCircle />, color: 'red', label: 'Active Complaints', value: stats.activeComplaints || 0, link: '/admin/disputes' },
    { icon: <FiShield />, color: 'blue', label: 'Provider Applications', value: stats.pendingProviderApplications || 0, link: '/admin/provider-applications' },
  ];

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>Admin Dashboard</h2></div>
        <div className="stats-grid">
          {cards.map((c, i) => (
            <Link to={c.link} key={i} style={{ textDecoration: 'none' }}>
              <div className="stat-card">
                <div className={`stat-icon ${c.color}`}>{c.icon}</div>
                <div className="stat-info">
                  <h3>{c.value}</h3>
                  <p>{c.label}</p>
                </div>
              </div>
            </Link>
          ))}
        </div>
        <div className="grid-2" style={{ marginTop: '2rem' }}>
          <div className="card" style={{ padding: '1.5rem' }}>
            <h3 style={{ marginBottom: '1rem' }}>Account Management</h3>
            <p style={{ color: 'var(--text-light)', marginBottom: '1rem' }}>
              Use the role-management flow to inspect individual users, approve provider applications, and control account access.
            </p>
            <div className="action-row">
              <Link to="/admin/users" className="btn btn-primary btn-sm">Manage Users</Link>
              <Link to="/admin/provider-applications" className="btn btn-outline btn-sm">Review Applications</Link>
            </div>
          </div>
          <div className="card" style={{ padding: '1.5rem' }}>
            <h3 style={{ marginBottom: '1rem' }}>Current Queue</h3>
            <div className="detail-list">
              <div><strong>Pending Bookings:</strong> {stats.pendingBookings || 0}</div>
              <div><strong>Open Complaints:</strong> {stats.activeComplaints || 0}</div>
              <div><strong>Pending Provider Applications:</strong> {stats.pendingProviderApplications || 0}</div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
