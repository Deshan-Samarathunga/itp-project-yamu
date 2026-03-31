import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';

export default function MyDisputes() {
  const [disputes, setDisputes] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/reviews/disputes/my').then(r => setDisputes(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>My Disputes</h2></div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : disputes.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Subject</th><th>Booking</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>{disputes.map(d => (
                <tr key={d._id}>
                  <td>{d.subject || d.reason || '-'}</td>
                  <td>{d.booking?.bookingNo || '-'}</td>
                  <td><span className={`badge ${d.status==='resolved'?'badge-success':d.status==='closed'?'badge-neutral':'badge-warning'}`}>{d.status||'open'}</span></td>
                  <td>{new Date(d.createdAt).toLocaleDateString()}</td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No disputes</h3><p>You have no disputes</p></div>}
      </main>
    </div>
  );
}
