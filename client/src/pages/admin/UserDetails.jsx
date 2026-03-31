import { useEffect, useMemo, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { badgeClassForStatus, formatRole } from '../../utils/account';

const MANAGEABLE_ROLES = ['customer', 'driver', 'staff'];

const emptyRoleStatus = {
  roleStatus: 'pending',
  verificationStatus: 'pending'
};

export default function AdminUserDetails() {
  const { id } = useParams();
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');
  const [form, setForm] = useState({
    fullName: '',
    email: '',
    activeRole: 'customer',
    accountStatus: 'active',
    verificationStatus: 'verified',
    assignedRoles: ['customer'],
    roleStatuses: {}
  });

  const assignedRoles = useMemo(() => form.assignedRoles || [], [form.assignedRoles]);

  const loadDetails = () => {
    setLoading(true);
    API.get(`/admin/users/${id}`)
      .then((res) => {
        const payload = res.data;
        const editableRoles = (payload.user.roles || [])
          .map((role) => role.roleKey)
          .filter((roleKey) => roleKey !== 'admin');

        setDetail(payload);
        setForm({
          fullName: payload.user.fullName || '',
          email: payload.user.email || '',
          activeRole: payload.user.role || 'customer',
          accountStatus: payload.user.accountStatus || 'active',
          verificationStatus: payload.user.verificationStatus || 'verified',
          assignedRoles: editableRoles,
          roleStatuses: (payload.user.roles || []).filter((role) => role.roleKey !== 'admin').reduce((acc, role) => {
            acc[role.roleKey] = {
              roleStatus: role.roleStatus,
              verificationStatus: role.verificationStatus
            };
            return acc;
          }, {})
        });
      })
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadDetails();
  }, [id]);

  const handleRoleToggle = (roleKey) => {
    setForm((prev) => {
      const nextAssigned = prev.assignedRoles.includes(roleKey)
        ? prev.assignedRoles.filter((role) => role !== roleKey)
        : [...prev.assignedRoles, roleKey];
      const shouldKeepActiveRole = prev.activeRole === 'admin' || nextAssigned.includes(prev.activeRole);

      return {
        ...prev,
        assignedRoles: nextAssigned.includes('customer') ? nextAssigned : [...nextAssigned, 'customer'],
        activeRole: shouldKeepActiveRole ? prev.activeRole : 'customer',
        roleStatuses: {
          ...prev.roleStatuses,
          [roleKey]: prev.roleStatuses[roleKey] || { ...emptyRoleStatus }
        }
      };
    });
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    try {
      await API.put(`/admin/users/${id}`, form);
      setMessage('User roles and profile access updated.');
      await loadDetails();
    } catch (error) {
      setMessage(error.response?.data?.message || 'User update failed.');
    } finally {
      setSaving(false);
    }
  };

  const handleStatusUpdate = async (accountStatus) => {
    setMessage('');

    try {
      await API.put(`/admin/users/${id}/status`, { accountStatus });
      setMessage(`Account status changed to ${accountStatus}.`);
      await loadDetails();
    } catch (error) {
      setMessage(error.response?.data?.message || 'Account status update failed.');
    }
  };

  const handleVerificationUpdate = async (verificationStatus) => {
    setMessage('');

    try {
      await API.put(`/admin/users/${id}/verify`, { verificationStatus });
      setMessage(`Verification changed to ${verificationStatus}.`);
      await loadDetails();
    } catch (error) {
      setMessage(error.response?.data?.message || 'Verification update failed.');
    }
  };

  const profiles = detail?.profiles || {};

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>User Details</h2>
        </div>

        <div className="action-row" style={{ marginBottom: '1rem' }}>
          <Link to="/admin/users" className="btn btn-outline btn-sm">Back to Users</Link>
          <Link to="/admin/provider-applications" className="btn btn-secondary btn-sm">Provider Applications</Link>
        </div>

        {message && <div className={`alert ${message.includes('failed') ? 'alert-danger' : 'alert-success'}`}>{message}</div>}

        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : !detail ? (
          <div className="empty-state"><h3>User not found</h3></div>
        ) : (
          <>
            <div className="detail-grid">
              <div className="form-card">
                <h3 style={{ marginBottom: '1rem' }}>User Summary</h3>
                <div className="detail-list">
                  <div><strong>Name:</strong> {detail.user.fullName}</div>
                  <div><strong>Email:</strong> {detail.user.email}</div>
                  <div><strong>Phone:</strong> {detail.user.phone || '-'}</div>
                  <div><strong>Active Role:</strong> <span className="badge badge-info">{formatRole(detail.user.role)}</span></div>
                  <div><strong>Account:</strong> <span className={`badge ${badgeClassForStatus(detail.user.accountStatus)}`}>{detail.user.accountStatus}</span></div>
                  <div><strong>Verification:</strong> <span className={`badge ${badgeClassForStatus(detail.user.verificationStatus)}`}>{detail.user.verificationStatus}</span></div>
                </div>
              </div>

              <div className="form-card">
                <h3 style={{ marginBottom: '1rem' }}>Quick Actions</h3>
                <div className="action-row">
                  <button type="button" className="btn btn-primary btn-sm" onClick={() => handleStatusUpdate('active')}>Set Active</button>
                  <button type="button" className="btn btn-danger btn-sm" onClick={() => handleStatusUpdate('suspended')}>Suspend</button>
                  <button type="button" className="btn btn-outline btn-sm" onClick={() => handleStatusUpdate('deactivated')}>Deactivate</button>
                </div>
                <div className="action-row" style={{ marginTop: '1rem' }}>
                  <button type="button" className="btn btn-primary btn-sm" onClick={() => handleVerificationUpdate('verified')}>Verify</button>
                  <button type="button" className="btn btn-outline btn-sm" onClick={() => handleVerificationUpdate('pending')}>Mark Pending</button>
                  <button type="button" className="btn btn-danger btn-sm" onClick={() => handleVerificationUpdate('rejected')}>Reject</button>
                </div>
              </div>
            </div>

            <div className="form-card" style={{ marginTop: '1.5rem' }}>
              <h3 style={{ marginBottom: '1rem' }}>Role Management</h3>
              <form onSubmit={handleSave}>
                <div className="form-row">
                  <div className="form-group">
                    <label>Full Name</label>
                    <input value={form.fullName} onChange={(e) => setForm((prev) => ({ ...prev, fullName: e.target.value }))} />
                  </div>
                  <div className="form-group">
                    <label>Email</label>
                    <input type="email" value={form.email} onChange={(e) => setForm((prev) => ({ ...prev, email: e.target.value }))} />
                  </div>
                </div>
                <div className="form-row">
                  <div className="form-group">
                    <label>Account Status</label>
                    <select value={form.accountStatus} onChange={(e) => setForm((prev) => ({ ...prev, accountStatus: e.target.value }))}>
                      <option value="active">active</option>
                      <option value="pending">pending</option>
                      <option value="suspended">suspended</option>
                      <option value="rejected">rejected</option>
                      <option value="deactivated">deactivated</option>
                    </select>
                  </div>
                  <div className="form-group">
                    <label>User Verification</label>
                    <select value={form.verificationStatus} onChange={(e) => setForm((prev) => ({ ...prev, verificationStatus: e.target.value }))}>
                      <option value="verified">verified</option>
                      <option value="approved">approved</option>
                      <option value="pending">pending</option>
                      <option value="rejected">rejected</option>
                      <option value="unverified">unverified</option>
                    </select>
                  </div>
                </div>

                <div className="form-group">
                  <label>Assigned Roles</label>
                  <div className="checkbox-grid">
                    <label className="check-option">
                      <input type="checkbox" checked readOnly />
                      <span>Customer (required)</span>
                    </label>
                    {MANAGEABLE_ROLES.filter((role) => role !== 'customer').map((roleKey) => (
                      <label className="check-option" key={roleKey}>
                        <input
                          type="checkbox"
                          checked={assignedRoles.includes(roleKey)}
                          onChange={() => handleRoleToggle(roleKey)}
                        />
                        <span>{formatRole(roleKey)}</span>
                      </label>
                    ))}
                    {detail.user.roles.some((role) => role.roleKey === 'admin') && (
                      <label className="check-option">
                        <input type="checkbox" checked readOnly />
                        <span>Admin (seeded)</span>
                      </label>
                    )}
                  </div>
                </div>

                <div className="form-group">
                  <label>Active Role</label>
                  <select value={form.activeRole} onChange={(e) => setForm((prev) => ({ ...prev, activeRole: e.target.value }))}>
                    {assignedRoles.map((roleKey) => (
                      <option key={roleKey} value={roleKey}>{formatRole(roleKey)}</option>
                    ))}
                    {detail.user.roles.some((role) => role.roleKey === 'admin') && <option value="admin">Admin</option>}
                  </select>
                </div>

                <div className="detail-grid">
                  {assignedRoles.map((roleKey) => (
                    <div className="form-card nested-card" key={roleKey}>
                      <h4 style={{ marginBottom: '1rem' }}>{formatRole(roleKey)} Status</h4>
                      <div className="form-group">
                        <label>Role Status</label>
                        <select
                          value={form.roleStatuses[roleKey]?.roleStatus || emptyRoleStatus.roleStatus}
                          onChange={(e) => setForm((prev) => ({
                            ...prev,
                            roleStatuses: {
                              ...prev.roleStatuses,
                              [roleKey]: {
                                ...(prev.roleStatuses[roleKey] || emptyRoleStatus),
                                roleStatus: e.target.value
                              }
                            }
                          }))}
                        >
                          <option value="active">active</option>
                          <option value="pending">pending</option>
                          <option value="rejected">rejected</option>
                          <option value="suspended">suspended</option>
                          <option value="deactivated">deactivated</option>
                        </select>
                      </div>
                      <div className="form-group">
                        <label>Role Verification</label>
                        <select
                          value={form.roleStatuses[roleKey]?.verificationStatus || emptyRoleStatus.verificationStatus}
                          onChange={(e) => setForm((prev) => ({
                            ...prev,
                            roleStatuses: {
                              ...prev.roleStatuses,
                              [roleKey]: {
                                ...(prev.roleStatuses[roleKey] || emptyRoleStatus),
                                verificationStatus: e.target.value
                              }
                            }
                          }))}
                        >
                          <option value="verified">verified</option>
                          <option value="approved">approved</option>
                          <option value="pending">pending</option>
                          <option value="rejected">rejected</option>
                          <option value="unverified">unverified</option>
                        </select>
                      </div>
                    </div>
                  ))}
                </div>

                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? 'Saving...' : 'Save User Changes'}
                </button>
              </form>
            </div>

            <div className="detail-grid" style={{ marginTop: '1.5rem' }}>
              {Object.entries(profiles).map(([key, profile]) => (
                <div className="form-card" key={key}>
                  <h3 style={{ marginBottom: '1rem' }}>{formatRole(key.replace('Profile', ''))} Profile</h3>
                  {profile ? (
                    <div className="detail-list">
                      {Object.entries(profile).filter(([field]) => !['_id', 'user', 'createdAt', 'updatedAt', '__v'].includes(field)).map(([field, value]) => (
                        <div key={field}>
                          <strong>{field}:</strong> {String(value || '-')}
                        </div>
                      ))}
                    </div>
                  ) : (
                    <p style={{ color: 'var(--text-light)' }}>No profile data yet.</p>
                  )}
                </div>
              ))}
            </div>

            <div className="form-card" style={{ marginTop: '1.5rem' }}>
              <h3 style={{ marginBottom: '1rem' }}>Role Applications</h3>
              {detail.roleApplications?.length ? (
                <div className="table-container">
                  <table>
                    <thead>
                      <tr>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Motivation</th>
                        <th>Admin Notes</th>
                      </tr>
                    </thead>
                    <tbody>
                      {detail.roleApplications.map((application) => (
                        <tr key={application._id}>
                          <td>{formatRole(application.roleKey)}</td>
                          <td><span className={`badge ${badgeClassForStatus(application.status)}`}>{application.status}</span></td>
                          <td>{application.motivation || '-'}</td>
                          <td>{application.adminNotes || '-'}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              ) : (
                <div className="empty-state"><h3>No role applications</h3></div>
              )}
            </div>
          </>
        )}
      </main>
    </div>
  );
}
