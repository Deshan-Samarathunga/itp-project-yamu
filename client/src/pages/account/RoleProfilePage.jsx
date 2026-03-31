import { useEffect, useMemo, useState } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { useAuth } from '../../context/AuthContext';
import { badgeClassForStatus, formatRole } from '../../utils/account';

const PROFILE_CONFIG = {
  customer: {
    title: 'Customer Profile',
    endpoint: '/users/customer-profile',
    fields: [
      { key: 'preferredContactMethod', label: 'Preferred Contact Method' },
      { key: 'emergencyContactName', label: 'Emergency Contact Name' },
      { key: 'emergencyContactPhone', label: 'Emergency Contact Phone' },
      { key: 'notes', label: 'Notes', textarea: true }
    ]
  },
  driver: {
    title: 'Driver Profile',
    endpoint: '/users/driver-profile',
    onboarding: true,
    fields: [
      { key: 'drivingLicenseNumber', label: 'Driving License Number' },
      { key: 'licenseExpiryDate', label: 'License Expiry Date', type: 'date' },
      { key: 'nicId', label: 'NIC / ID' },
      { key: 'serviceArea', label: 'Service Area' },
      { key: 'providerDetails', label: 'Provider Details', textarea: true }
    ]
  },
  staff: {
    title: 'Staff Profile',
    endpoint: '/users/staff-profile',
    onboarding: true,
    fields: [
      { key: 'storeName', label: 'Store Name' },
      { key: 'storeOwner', label: 'Store Owner' },
      { key: 'businessRegistrationNumber', label: 'Business Registration Number' },
      { key: 'storeAddress', label: 'Store Address' },
      { key: 'storeContactNumber', label: 'Store Contact Number' },
      { key: 'storeEmail', label: 'Store Email', type: 'email' }
    ]
  },
  admin: {
    title: 'Admin Profile',
    endpoint: '/users/admin-profile',
    fields: [
      { key: 'systemPermissions', label: 'System Permissions', readOnly: true },
      { key: 'adminNotes', label: 'Admin Notes', textarea: true }
    ]
  }
};

export default function RoleProfilePage({ roleKey }) {
  const { user, refreshUser } = useAuth();
  const config = PROFILE_CONFIG[roleKey];
  const roleAssignment = useMemo(
    () => (user?.roles || []).find((item) => item.roleKey === roleKey),
    [roleKey, user]
  );
  const [form, setForm] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');
  const [accessError, setAccessError] = useState('');

  useEffect(() => {
    setLoading(true);
    setAccessError('');
    API.get(config.endpoint)
      .then((res) => setForm(res.data || {}))
      .catch((error) => {
        setAccessError(error.response?.data?.message || 'This role profile is not available for your account.');
      })
      .finally(() => setLoading(false));
  }, [config.endpoint]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSaving(true);
    setMessage('');

    try {
      await API.put(config.endpoint, form);
      await refreshUser();
      setMessage(`${config.title} updated successfully.`);
    } catch (error) {
      setMessage(error.response?.data?.message || 'Profile update failed.');
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card" style={{ maxWidth: 860 }}>
          <div className="form-header">
            <h2>{config.title}</h2>
          </div>

          {roleAssignment && (
            <div className="badge-row" style={{ marginBottom: '1rem' }}>
              <span className="badge badge-info">{formatRole(roleKey)}</span>
              <span className={`badge ${badgeClassForStatus(roleAssignment.roleStatus)}`}>{roleAssignment.roleStatus}</span>
              <span className={`badge ${badgeClassForStatus(roleAssignment.verificationStatus)}`}>{roleAssignment.verificationStatus}</span>
            </div>
          )}

          {config.onboarding && (
            <div className="alert alert-info">
              Pending provider roles may complete onboarding here, but operational access remains blocked until admin approval.
            </div>
          )}

          {loading ? (
            <div className="spinner-container"><div className="spinner"></div></div>
          ) : accessError ? (
            <div className="alert alert-danger">{accessError}</div>
          ) : (
            <>
              {message && <div className={`alert ${message.includes('successfully') ? 'alert-success' : 'alert-danger'}`}>{message}</div>}
              <form onSubmit={handleSubmit}>
                <div className="form-row">
                  {config.fields.filter((field) => !field.textarea).map((field) => (
                    <div className="form-group" key={field.key}>
                      <label>{field.label}</label>
                      <input
                        type={field.type || 'text'}
                        value={field.type === 'date' && form[field.key] ? String(form[field.key]).slice(0, 10) : form[field.key] || ''}
                        onChange={(e) => setForm((prev) => ({ ...prev, [field.key]: e.target.value }))}
                        readOnly={field.readOnly}
                      />
                    </div>
                  ))}
                </div>

                {config.fields.filter((field) => field.textarea).map((field) => (
                  <div className="form-group" key={field.key}>
                    <label>{field.label}</label>
                    <textarea
                      rows="4"
                      value={form[field.key] || ''}
                      onChange={(e) => setForm((prev) => ({ ...prev, [field.key]: e.target.value }))}
                      readOnly={field.readOnly}
                    />
                  </div>
                ))}

                <button type="submit" className="btn btn-primary" disabled={saving}>
                  {saving ? 'Saving...' : 'Save Role Profile'}
                </button>
              </form>
            </>
          )}
        </div>
      </main>
    </div>
  );
}
