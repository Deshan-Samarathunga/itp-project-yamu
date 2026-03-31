export const ROLE_LABELS = {
  customer: 'Customer',
  driver: 'Driver',
  staff: 'Staff',
  admin: 'Admin'
};

export const STATUS_BADGES = {
  active: 'badge-success',
  verified: 'badge-success',
  approved: 'badge-success',
  pending: 'badge-warning',
  rejected: 'badge-danger',
  suspended: 'badge-danger',
  deactivated: 'badge-danger',
  inactive: 'badge-neutral',
  unverified: 'badge-neutral'
};

export const PROVIDER_ROLES = ['driver', 'staff'];

export const formatRole = (role) => ROLE_LABELS[role] || role;

export const badgeClassForStatus = (status) => STATUS_BADGES[status] || 'badge-neutral';

export const getDashboardLink = (user) => {
  if (!user) return '/signin';

  switch (user.role) {
    case 'admin':
      return '/admin/dashboard';
    case 'staff':
      return '/staff/dashboard';
    case 'driver':
      return '/driver/dashboard';
    default:
      return '/customer/bookings';
  }
};
