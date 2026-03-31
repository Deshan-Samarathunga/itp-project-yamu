import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { badgeClassForStatus, formatRole } from '../../utils/account';

export default function MyProfile() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    API.get('/users/profile')
      .then((res) => setData(res.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  if (loading) {
    return (
      <div className="dashboard-layout">
        <Sidebar />
        <main className="dashboard-content">
          <div className="spinner-container"><div className="spinner"></div></div>
        </main>
      </div>
    );
  }

  const user = data?.user;

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>My Profile</h2>
        </div>

        {!user ? (
          <div className="empty-state"><h3>Profile data is unavailable</h3></div>
        ) : (
          <>
            <div className="detail-grid">
              <div className="form-card">
                <h3 style={{ marginBottom: '1rem' }}>Common Profile</h3>
                <div className="detail-list">
                  <div><strong>Full Name:</strong> {user.fullName || '-'}</div>
                  <div><strong>Email:</strong> {user.email || '-'}</div>
                  <div><strong>Phone:</strong> {user.phone || '-'}</div>
                  <div><strong>City:</strong> {user.city || '-'}</div>
                  <div><strong>Address:</strong> {user.address || '-'}</div>
                  <div><strong>Date of Birth:</strong> {user.dob || '-'}</div>
                  <div><strong>Bio:</strong> {user.bio || '-'}</div>
                </div>
              </div>

              <div className="form-card">
                <h3 style={{ marginBottom: '1rem' }}>Access Status</h3>
                <div className="detail-list">
                  <div>
                    <strong>Active Role:</strong>{' '}
                    <span className="badge badge-info">{formatRole(user.role)}</span>
                  </div>
                  <div>
                    <strong>Account Status:</strong>{' '}
                    <span className={`badge ${badgeClassForStatus(user.accountStatus)}`}>{user.accountStatus}</span>
                  </div>
                  <div>
                    <strong>Verification:</strong>{' '}
                    <span className={`badge ${badgeClassForStatus(user.verificationStatus)}`}>{user.verificationStatus}</span>
                  </div>
                </div>
                <div className="action-row" style={{ marginTop: '1.5rem' }}>
                  <Link to="/account/edit-profile" className="btn btn-primary btn-sm">Edit Profile</Link>
                  <Link to="/account/update-password" className="btn btn-outline btn-sm">Update Password</Link>
                </div>
              </div>
            </div>

            <div className="form-card" style={{ marginTop: '1.5rem' }}>
              <h3 style={{ marginBottom: '1rem' }}>Assigned Roles</h3>
              <div className="role-card-grid">
                {(user.roles || []).map((role) => (
                  <div key={role.roleKey} className="role-card">
                    <h4>{formatRole(role.roleKey)}</h4>
                    <div className="badge-row">
                      <span className={`badge ${badgeClassForStatus(role.roleStatus)}`}>{role.roleStatus}</span>
                      <span className={`badge ${badgeClassForStatus(role.verificationStatus)}`}>{role.verificationStatus}</span>
                    </div>
                    <p>
                      {user.role === role.roleKey ? 'Currently active in this portal.' : 'Assigned to your account.'}
                    </p>
                    <Link to={`/account/${role.roleKey}-profile`} className="btn btn-secondary btn-sm">Open Profile</Link>
                  </div>
                ))}
              </div>
            </div>

            <div className="form-card" style={{ marginTop: '1.5rem' }}>
              <h3 style={{ marginBottom: '1rem' }}>Role Management</h3>
              <div className="action-row">
                <Link to="/account/choose-role" className="btn btn-primary btn-sm">Choose Role</Link>
                <Link to="/account/role-switch" className="btn btn-outline btn-sm">Switch Role</Link>
                <Link to="/account/role-application" className="btn btn-secondary btn-sm">Provider Applications</Link>
              </div>
            </div>
          </>
        )}
      </main>
    </div>
  );
}
