import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import API from '../../api/axios';
import Sidebar from '../../components/Sidebar';
import { badgeClassForStatus, formatRole } from '../../utils/account';

export default function AdminUsers() {
  const [users, setUsers] = useState([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({ search: '', role: '', status: '' });

  const loadUsers = () => {
    setLoading(true);
    API.get('/admin/users', { params: filters })
      .then((res) => setUsers(res.data))
      .catch(() => {})
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    loadUsers();
  }, [filters.role, filters.status]);

  const handleSearch = (e) => {
    e.preventDefault();
    loadUsers();
  };

  return (
    <div className="dashboard-layout">
      <Sidebar />
      <main className="dashboard-content">
        <div className="form-header">
          <h2>User Management</h2>
        </div>

        <div className="form-card" style={{ marginBottom: '1.5rem' }}>
          <form onSubmit={handleSearch}>
            <div className="form-row">
              <div className="form-group">
                <label>Search</label>
                <input
                  value={filters.search}
                  onChange={(e) => setFilters((prev) => ({ ...prev, search: e.target.value }))}
                  placeholder="Search by name or email"
                />
              </div>
              <div className="form-group">
                <label>Role</label>
                <select value={filters.role} onChange={(e) => setFilters((prev) => ({ ...prev, role: e.target.value }))}>
                  <option value="">All Roles</option>
                  <option value="customer">Customer</option>
                  <option value="driver">Driver</option>
                  <option value="staff">Staff</option>
                  <option value="admin">Admin</option>
                </select>
              </div>
            </div>
            <div className="form-row">
              <div className="form-group">
                <label>Account Status</label>
                <select value={filters.status} onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}>
                  <option value="">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="pending">Pending</option>
                  <option value="suspended">Suspended</option>
                  <option value="rejected">Rejected</option>
                  <option value="deactivated">Deactivated</option>
                </select>
              </div>
              <div className="form-group" style={{ display: 'flex', alignItems: 'end' }}>
                <button type="submit" className="btn btn-primary">Search Users</button>
              </div>
            </div>
          </form>
        </div>

        {loading ? (
          <div className="spinner-container"><div className="spinner"></div></div>
        ) : (
          <div className="table-container">
            <table>
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Active Role</th>
                  <th>Assigned Roles</th>
                  <th>Account</th>
                  <th>Verify</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {users.map((user) => (
                  <tr key={user._id}>
                    <td style={{ fontWeight: 600 }}>{user.fullName}</td>
                    <td>{user.email}</td>
                    <td><span className="badge badge-info">{formatRole(user.role)}</span></td>
                    <td>
                      <div className="badge-row">
                        {(user.roles || []).map((role) => (
                          <span key={role.roleKey} className="badge badge-neutral">{formatRole(role.roleKey)}</span>
                        ))}
                      </div>
                    </td>
                    <td><span className={`badge ${badgeClassForStatus(user.accountStatus)}`}>{user.accountStatus}</span></td>
                    <td><span className={`badge ${badgeClassForStatus(user.verificationStatus)}`}>{user.verificationStatus}</span></td>
                    <td>
                      <Link to={`/admin/users/${user._id}`} className="btn btn-secondary btn-sm">Open</Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </main>
    </div>
  );
}
