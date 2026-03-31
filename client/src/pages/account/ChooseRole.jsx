import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { badgeClassForStatus, formatRole, PROVIDER_ROLES } from '../../utils/account';

export default function ChooseRole() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    API.get('/users/roles/options')
      .then((res) => setData(res.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, []);

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>Choose Role</h2>
        </div>

        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : (
          <>
            <div className="form-card">
              <h3 style={{ marginBottom: '1rem' }}>Current Access</h3>
              <p style={{ color: 'var(--text-light)' }}>
                Your active portal is <strong>{formatRole(data?.activeRole)}</strong>. Switch roles to move between customer, provider, and admin portals when the assigned role is usable.
              </p>
              <div className="action-row" style={{ marginTop: '1.25rem' }}>
                <Link to="/account/role-switch" className="btn btn-primary btn-sm">Open Role Switch</Link>
                <Link to="/account/role-application" className="btn btn-outline btn-sm">Apply for Provider Role</Link>
              </div>
            </div>

            <div className="role-card-grid" style={{ marginTop: '1.5rem' }}>
              {(data?.roleOptions || []).map((option) => (
                <div key={option.roleKey} className="role-card">
                  <h4>{formatRole(option.roleKey)}</h4>
                  <div className="badge-row">
                    <span className={`badge ${badgeClassForStatus(option.roleStatus)}`}>{option.roleStatus}</span>
                    <span className={`badge ${badgeClassForStatus(option.verificationStatus)}`}>{option.verificationStatus}</span>
                  </div>
                  <p>
                    {option.canSwitch
                      ? 'This role can be activated now.'
                      : option.onboardingOnly
                        ? 'Onboarding is available, but operational access is still blocked.'
                        : 'This role is currently blocked or pending review.'}
                  </p>
                  <Link to={`/account/${option.roleKey}-profile`} className="btn btn-secondary btn-sm">Role Profile</Link>
                </div>
              ))}

              {PROVIDER_ROLES.filter((role) => !(data?.roleOptions || []).some((option) => option.roleKey === role)).map((role) => (
                <div key={role} className="role-card">
                  <h4>{formatRole(role)}</h4>
                  <div className="badge-row">
                    <span className="badge badge-neutral">not assigned</span>
                  </div>
                  <p>This provider role is not assigned to your account yet.</p>
                  <Link to="/account/role-application" className="btn btn-primary btn-sm">Apply Now</Link>
                </div>
              ))}
            </div>
          </>
        )}
      </main>
    </div>
  );
}
