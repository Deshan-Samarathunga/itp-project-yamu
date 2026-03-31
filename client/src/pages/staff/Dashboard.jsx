import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { FiTruck, FiCalendar, FiDollarSign } from 'react-icons/fi';

export default function StaffDashboard() {
  const [stats, setStats] = useState({ vehicles: 0, bookings: 0, earnings: 0 });
  useEffect(() => {
    API.get('/vehicles/my').then(r => setStats(p => ({ ...p, vehicles: r.data.length }))).catch(() => {});
    API.get('/bookings/provider').then(r => {
      setStats(p => ({ ...p, bookings: r.data.length, earnings: r.data.filter(b => b.bookingStatus === 'completed').reduce((s, b) => s + (b.total || 0), 0) }));
    }).catch(() => {});
  }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>Staff Dashboard</h2></div>
        <div className="stats-grid">
          <div className="stat-card"><div className="stat-icon blue"><FiTruck /></div><div className="stat-info"><h3>{stats.vehicles}</h3><p>My Vehicles</p></div></div>
          <div className="stat-card"><div className="stat-icon green"><FiCalendar /></div><div className="stat-info"><h3>{stats.bookings}</h3><p>Total Bookings</p></div></div>
          <div className="stat-card"><div className="stat-icon amber"><FiDollarSign /></div><div className="stat-info"><h3>Rs.{stats.earnings.toLocaleString()}</h3><p>Earnings</p></div></div>
        </div>
      </main>
    </div>
  );
}
