import { useState, useEffect } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { FiStar } from 'react-icons/fi';

export default function MyReviews() {
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  useEffect(() => { API.get('/reviews/my').then(r => setReviews(r.data)).catch(() => {}).finally(() => setLoading(false)); }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header"><h2>My Reviews</h2></div>
        {loading ? <div className="spinner-container"><div className="spinner"></div></div> : reviews.length > 0 ? (
          <div className="table-container">
            <table>
              <thead><tr><th>Vehicle</th><th>Rating</th><th>Comment</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>{reviews.map(r => (
                <tr key={r._id}>
                  <td>{r.vehicle?.vehicleTitle || r.booking?.bookingNo || '-'}</td>
                  <td><div className="stars">{[1,2,3,4,5].map(s=><FiStar key={s} style={s<=r.rating?{fill:'var(--accent)',color:'var(--accent)'}:{color:'var(--gray-300)'}} />)}</div></td>
                  <td>{r.comment?.slice(0,60)}{r.comment?.length>60?'...':''}</td>
                  <td>{new Date(r.createdAt).toLocaleDateString()}</td>
                  <td><span className={`badge ${r.status==='approved'?'badge-success':'badge-warning'}`}>{r.status||'pending'}</span></td>
                </tr>
              ))}</tbody>
            </table>
          </div>
        ) : <div className="empty-state"><h3>No reviews yet</h3><p>Your reviews will appear here after completing a booking</p></div>}
      </main>
    </div>
  );
}
