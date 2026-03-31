import { NavLink, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import {
  FiGrid,
  FiCalendar,
  FiTruck,
  FiStar,
  FiDollarSign,
  FiAlertCircle,
  FiUser,
  FiLogOut,
  FiPlusCircle,
  FiUsers,
  FiTag,
  FiCreditCard,
  FiLayers,
  FiRepeat,
  FiShield,
  FiKey
} from 'react-icons/fi';

const commonItems = [
  { to: '/account/my-profile', icon: <FiUser />, label: 'My Profile' },
  { to: '/account/edit-profile', icon: <FiUser />, label: 'Edit Profile' },
  { to: '/account/update-password', icon: <FiKey />, label: 'Update Password' },
  { to: '/account/choose-role', icon: <FiShield />, label: 'Choose Role' },
  { to: '/account/role-switch', icon: <FiRepeat />, label: 'Switch Role' },
  { to: '/account/role-application', icon: <FiPlusCircle />, label: 'Role Applications' }
];

const roleMenus = {
  customer: [
    { to: '/customer/bookings', icon: <FiCalendar />, label: 'My Bookings' },
    { to: '/account/customer-profile', icon: <FiUser />, label: 'Customer Profile' },
    { to: '/customer/reviews', icon: <FiStar />, label: 'My Reviews' },
    { to: '/customer/disputes', icon: <FiAlertCircle />, label: 'My Disputes' },
    { to: '/customer/payments', icon: <FiDollarSign />, label: 'Payment History' }
  ],
  driver: [
    { to: '/driver/dashboard', icon: <FiGrid />, label: 'Dashboard' },
    { to: '/account/driver-profile', icon: <FiUser />, label: 'Driver Profile' },
    { to: '/driver/bookings', icon: <FiCalendar />, label: 'Bookings' },
    { to: '/driver/ads', icon: <FiPlusCircle />, label: 'My Ads' },
    { to: '/driver/earnings', icon: <FiDollarSign />, label: 'Earnings' }
  ],
  staff: [
    { to: '/staff/dashboard', icon: <FiGrid />, label: 'Dashboard' },
    { to: '/account/staff-profile', icon: <FiUser />, label: 'Staff Profile' },
    { to: '/staff/vehicles', icon: <FiTruck />, label: 'My Vehicles' },
    { to: '/staff/bookings', icon: <FiCalendar />, label: 'Bookings' }
  ],
  admin: [
    { to: '/admin/dashboard', icon: <FiGrid />, label: 'Dashboard' },
    { to: '/account/admin-profile', icon: <FiUser />, label: 'Admin Profile' },
    { to: '/admin/users', icon: <FiUsers />, label: 'Users' },
    { to: '/admin/provider-applications', icon: <FiShield />, label: 'Provider Applications' },
    { to: '/admin/vehicles', icon: <FiTruck />, label: 'Vehicles' },
    { to: '/admin/bookings', icon: <FiCalendar />, label: 'Bookings' },
    { to: '/admin/payments', icon: <FiCreditCard />, label: 'Payments' },
    { to: '/admin/promotions', icon: <FiTag />, label: 'Promotions' },
    { to: '/admin/brands', icon: <FiLayers />, label: 'Brands' }
  ]
};

export default function Sidebar() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const items = [...commonItems, ...(roleMenus[user?.role] || roleMenus.customer)];

  const handleLogout = () => { logout(); navigate('/'); };

  return (
    <aside className="sidebar">
      <div className="sidebar-user">
        <img
          src={user?.profilePic && user.profilePic !== 'avatar.png'
            ? `http://localhost:5000/uploads/${user.profilePic}`
            : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user?.fullName || 'U') + '&background=f0a500&color=0d1b2a&bold=true'}
          alt={user?.fullName}
        />
        <h4>{user?.fullName}</h4>
        <span>{user?.role}</span>
      </div>
      <nav className="sidebar-nav">
        {items.map(item => (
          <NavLink key={item.to} to={item.to}>{item.icon} {item.label}</NavLink>
        ))}
        <button onClick={handleLogout}><FiLogOut /> Logout</button>
      </nav>
    </aside>
  );
}
