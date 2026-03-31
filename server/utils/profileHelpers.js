const CustomerProfile = require('../models/CustomerProfile');
const DriverProfile = require('../models/DriverProfile');
const StaffProfile = require('../models/StaffProfile');
const AdminProfile = require('../models/AdminProfile');

const profileModels = {
  customer: CustomerProfile,
  driver: DriverProfile,
  staff: StaffProfile,
  admin: AdminProfile
};

const defaultFactories = {
  customer: (userId) => ({ user: userId }),
  driver: (userId) => ({ user: userId }),
  staff: (userId) => ({ user: userId }),
  admin: (userId) => ({ user: userId })
};

const getOrCreateRoleProfile = async (roleKey, userId) => {
  const Model = profileModels[roleKey];
  if (!Model) return null;

  let profile = await Model.findOne({ user: userId });
  if (!profile) {
    profile = await Model.create(defaultFactories[roleKey](userId));
  }
  return profile;
};

const getRoleProfilesBundle = async (user) => {
  const [customerProfile, driverProfile, staffProfile, adminProfile] = await Promise.all([
    getOrCreateRoleProfile('customer', user._id),
    user.roles?.some((item) => item.roleKey === 'driver') ? getOrCreateRoleProfile('driver', user._id) : null,
    user.roles?.some((item) => item.roleKey === 'staff') ? getOrCreateRoleProfile('staff', user._id) : null,
    user.roles?.some((item) => item.roleKey === 'admin') ? getOrCreateRoleProfile('admin', user._id) : null
  ]);

  return {
    customerProfile,
    driverProfile,
    staffProfile,
    adminProfile
  };
};

module.exports = {
  getOrCreateRoleProfile,
  getRoleProfilesBundle
};
