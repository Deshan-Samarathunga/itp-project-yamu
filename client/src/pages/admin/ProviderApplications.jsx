import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { badgeClassForStatus, formatRole } from '../../utils/account';

export default function AdminProviderApplications() {
  const [applications, setApplications] = useState([]);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [notes, setNotes] = useState({});
  const [actionId, setActionId] = useState('');

  const loadApplications = () => {
    setLoading(true);
    API.get('/admin/provider-applications')
      .then((res) => setApplications(res.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadApplications();
  }, []);

  const handleReview = async (id, status) => {
    setActionId(id);
    setMessage('');

    try {
      await API.put(`/admin/provider-applications/${id}`, {
        status,
        adminNotes: notes[id] || ''
      });
      setMessage(`Application ${status}.`);
      await loadApplications();
    } catch (error) {
      setMessage(error.response?.data?.message || 'Application review failed.');
    } finally {
      setActionId('');
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>Provider Applications</h2>
        </div>

        <div className="action-row" style={{ marginBottom: '1rem' }}>
          <Link to="/admin/users" className="btn btn-outline btn-sm">Back to Users</Link>
        </div>

        {message && <div className={`alert ${message.includes('failed') ? 'alert-danger' : 'alert-success'}`}>{message}</div>}

        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : applications.length ? (
          <div className="role-card-grid">
            {applications.map((application) => (
              <div key={application._id} className="role-card">
                <h4>{formatRole(application.roleKey)} Application</h4>
                <div className="badge-row">
                  <span className={`badge ${badgeClassForStatus(application.status)}`}>{application.status}</span>
                  <span className={`badge ${badgeClassForStatus(application.applicant?.accountStatus)}`}>{application.applicant?.accountStatus}</span>
                </div>
                <p><strong>Applicant:</strong> {application.applicant?.fullName || '-'}</p>
                <p><strong>Email:</strong> {application.applicant?.email || '-'}</p>
                <p><strong>Motivation:</strong> {application.motivation || '-'}</p>
                <div className="form-group" style={{ marginTop: '1rem' }}>
                  <label>Admin Notes</label>
                  <textarea
                    rows="3"
                    value={notes[application._id] ?? application.adminNotes ?? ''}
                    onChange={(e) => setNotes((prev) => ({ ...prev, [application._id]: e.target.value }))}
                  />
                </div>
                <div className="action-row">
                  <button
                    type="button"
                    className="btn btn-primary btn-sm"
                    disabled={actionId === application._id || application.status === 'approved'}
                    onClick={() => handleReview(application._id, 'approved')}
                  >
                    {actionId === application._id ? 'Saving...' : 'Approve'}
                  </button>
                  <button
                    type="button"
                    className="btn btn-danger btn-sm"
                    disabled={actionId === application._id || application.status === 'rejected'}
                    onClick={() => handleReview(application._id, 'rejected')}
                  >
                    {actionId === application._id ? 'Saving...' : 'Reject'}
                  </button>
                  <Link to={`/admin/users/${application.applicant?._id}`} className="btn btn-outline btn-sm">User Details</Link>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="empty-state"><h3>No provider applications found</h3></div>
        )}
      </main>
    </div>
  );
}
