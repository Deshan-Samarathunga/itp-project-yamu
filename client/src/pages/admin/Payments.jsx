import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function AdminPayments() {
  const [payments, setPayments] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/payments').then(r => setPayments(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>Payments</h2></div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : payments.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Booking</th><th>User</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>{payments.map(p => (
                <tr key={p._id}>
                  <td>{p.booking?.bookingNo || '-'}</td>
                  <td>{p.user?.fullName || '-'}</td>
                  <td style={{ fontWeight: 600 }}>Rs.{p.amount?.toLocaleString()}</td>
                  <td>{p.paymentMethod || '-'}</td>
                  <td><span className={`badge ${p.status==='completed'?'badge-success':'badge-warning'}`}>{p.status||'pending'}</span></td>
                  <td>{new Date(p.createdAt).toLocaleDateString()}</td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No payments yet</h3></div>}
      </main>
    </div>
  );
}
