import { useEffect, useState } from 'react';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { useAuth } from '../../context/AuthContext';
import { badgeClassForStatus, formatRole } from '../../utils/account';

export default function RoleSwitch() {
  const { switchRole } = useAuth();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [switchingRole, setSwitchingRole] = useState('');

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

  const handleSwitch = async (roleKey) => {
    setSwitchingRole(roleKey);
    setMessage('');

    try {
      await switchRole(roleKey);
      await loadData();
      setMessage(`Active role switched to ${formatRole(roleKey)}.`);
    } catch (error) {
      setMessage(error.response?.data?.message || 'Role switch failed.');
    } finally {
      setSwitchingRole('');
    }
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-card">
          <div className="form-header">
            <h2>Role Switch</h2>
          </div>

          {message && <div className={`alert ${message.includes('switched') ? 'alert-success' : 'alert-danger'}`}>{message}</div>}

          {loading ? (
            <div className="spinner-container"><div className="spinner"></div></div>
          ) : (
            <div className="role-card-grid">
              {(data?.roleOptions || []).map((option) => (
                <div key={option.roleKey} className="role-card">
                  <h4>{formatRole(option.roleKey)}</h4>
                  <div className="badge-row">
                    <span className={`badge ${badgeClassForStatus(option.roleStatus)}`}>{option.roleStatus}</span>
                    <span className={`badge ${badgeClassForStatus(option.verificationStatus)}`}>{option.verificationStatus}</span>
                  </div>
                  <p>
                    {data?.activeRole === option.roleKey
                      ? 'This is your active role.'
                      : option.canSwitch
                        ? 'You can enter this portal now.'
                        : 'This role cannot be activated yet.'}
                  </p>
                  <button
                    type="button"
                    className="btn btn-primary btn-sm"
                    disabled={!option.canSwitch || switchingRole === option.roleKey || data?.activeRole === option.roleKey}
                    onClick={() => handleSwitch(option.roleKey)}
                  >
                    {switchingRole === option.roleKey ? 'Switching...' : data?.activeRole === option.roleKey ? 'Active Role' : 'Switch To Role'}
                  </button>
                </div>
              ))}
            </div>
          )}
        </div>
      </main>
    </div>
  );
}
