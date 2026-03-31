const ROLE_KEYS = ['customer', 'driver', 'staff', 'admin'];
const PROVIDER_ROLES = ['driver', 'staff'];
const BLOCKED_ROLE_STATUSES = ['rejected', 'suspended', 'deactivated'];
const BLOCKED_ACCOUNT_STATUSES = ['rejected', 'suspended', 'deactivated'];
const USABLE_ACCOUNT_STATUSES = ['active'];
const USABLE_VERIFICATION_STATUSES = ['approved', 'verified'];

const getRoleAssignment = (user, roleKey = user?.role) => {
  if (!user || !Array.isArray(user.roles)) {
    return null;
  }

  return user.roles.find((item) => item.roleKey === roleKey) || null;
};

const hasAssignedRole = (user, roleKey) => {
  return Boolean(getRoleAssignment(user, roleKey));
};

const isAccountBlocked = (user) => {
  return !user || BLOCKED_ACCOUNT_STATUSES.includes(user.accountStatus);
};

const isRoleBlocked = (user, roleKey) => {
  const assignment = getRoleAssignment(user, roleKey);
  return !assignment || BLOCKED_ROLE_STATUSES.includes(assignment.roleStatus) || assignment.verificationStatus === 'rejected';
};

const canAccessAssignedRole = (user, roleKey) => {
  return hasAssignedRole(user, roleKey) && !isRoleBlocked(user, roleKey) && !isAccountBlocked(user);
};

const canOnboardProviderRole = (user, roleKey) => {
  if (!PROVIDER_ROLES.includes(roleKey)) {
    return false;
  }

  return canAccessAssignedRole(user, roleKey);
};

const isRoleOperational = (user, roleKey) => {
  const assignment = getRoleAssignment(user, roleKey);

  if (!assignment || isAccountBlocked(user)) {
    return false;
  }

  return USABLE_ACCOUNT_STATUSES.includes(user.accountStatus)
    && assignment.roleStatus === 'active'
    && USABLE_VERIFICATION_STATUSES.includes(assignment.verificationStatus);
};

const isCustomerOperational = (user) => {
  return isRoleOperational(user, 'customer')
    && USABLE_VERIFICATION_STATUSES.includes(user.verificationStatus);
};

const sanitizeAssignedRoles = (roles = []) => {
  const cleanRoles = roles.filter((role) => ROLE_KEYS.includes(role));
  return [...new Set(cleanRoles)];
};

module.exports = {
  ROLE_KEYS,
  PROVIDER_ROLES,
  USABLE_VERIFICATION_STATUSES,
  getRoleAssignment,
  hasAssignedRole,
  isAccountBlocked,
  isRoleBlocked,
  canAccessAssignedRole,
  canOnboardProviderRole,
  isRoleOperational,
  isCustomerOperational,
  sanitizeAssignedRoles
};
