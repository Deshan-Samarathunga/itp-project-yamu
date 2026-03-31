import { useEffect, useState } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { badgeClassForStatus, formatRole, PROVIDER_ROLES } from '../../utils/account';

export default function RoleApplication() {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [savingRole, setSavingRole] = useState('');
  const [motivations, setMotivations] = useState({ driver: '', staff: '' });

  const loadData = () => {
    setLoading(true);
    API.get('/users/roles/options')
      .then((res) => setData(res.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadData();
  }, []);

  const handleApply = async (roleKey) => {
    setSavingRole(roleKey);
    setMessage('');

    try {
      await API.post('/users/roles/apply', {
        roleKey,
        motivation: motivations[roleKey]
      });
      setMessage(`${formatRole(roleKey)} application submitted successfully.`);
      setMotivations((prev) => ({ ...prev, [roleKey]: '' }));
      await loadData();
    } catch (error) {
      setMessage(error.response?.data?.message || 'Role application failed.');
    } finally {
      setSavingRole('');
    }
  };

  const roleOptions = data?.roleOptions || [];

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>Provider Role Applications</h2>
        </div>

        {message && <div className={`alert ${message.includes('submitted') ? 'alert-success' : 'alert-danger'}`}>{message}</div>}

        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : (
          <>
            <div className="role-card-grid">
              {PROVIDER_ROLES.map((roleKey) => {
                const option = roleOptions.find((item) => item.roleKey === roleKey);
                const hasPending = (data?.roleApplications || []).some((item) => item.roleKey === roleKey && item.status === 'pending');

                return (
                  <div key={roleKey} className="role-card">
                    <h4>{formatRole(roleKey)}</h4>
                    <div className="badge-row">
                      <span className={`badge ${badgeClassForStatus(option?.roleStatus || 'inactive')}`}>{option?.roleStatus || 'not assigned'}</span>
                      <span className={`badge ${badgeClassForStatus(option?.verificationStatus || 'unverified')}`}>{option?.verificationStatus || 'not reviewed'}</span>
                    </div>
                    <p>
                      Submit an application to request this provider role. Admin approval is required before operational access is granted.
                    </p>
                    <div className="form-group" style={{ marginTop: '1rem' }}>
                      <label>Motivation</label>
                      <textarea
                        rows="3"
                        value={motivations[roleKey]}
                        onChange={(e) => setMotivations((prev) => ({ ...prev, [roleKey]: e.target.value }))}
                        placeholder={`Why do you want to become a ${formatRole(roleKey).toLowerCase()}?`}
                      />
                    </div>
                    <button
                      type="button"
                      className="btn btn-primary btn-sm"
                      disabled={hasPending || savingRole === roleKey}
                      onClick={() => handleApply(roleKey)}
                    >
                      {savingRole === roleKey ? 'Submitting...' : hasPending ? 'Pending Review' : `Apply for ${formatRole(roleKey)}`}
                    </button>
                  </div>
                );
              })}
            </div>

            <div className="form-card" style={{ marginTop: '1.5rem' }}>
              <h3 style={{ marginBottom: '1rem' }}>Application History</h3>
              {data?.roleApplications?.length ? (
                <div className="table-container">
                  <table>
                    <thead>
                      <tr>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Motivation</th>
                        <th>Admin Notes</th>
                        <th>Submitted</th>
                      </tr>
                    </thead>
                    <tbody>
                      {data.roleApplications.map((application) => (
                        <tr key={application._id}>
                          <td>{formatRole(application.roleKey)}</td>
                          <td><span className={`badge ${badgeClassForStatus(application.status)}`}>{application.status}</span></td>
                          <td>{application.motivation || '-'}</td>
                          <td>{application.adminNotes || '-'}</td>
                          <td>{new Date(application.createdAt).toLocaleDateString()}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="empty-state"><h3>No applications yet</h3></div>
              )}
            </div>
          </>
        )}
      </main>
    </div>
  );
}
