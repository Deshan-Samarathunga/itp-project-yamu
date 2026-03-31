const User = require('../models/User');
const CustomerProfile = require('../models/CustomerProfile');
const DriverProfile = require('../models/DriverProfile');
const StaffProfile = require('../models/StaffProfile');
const AdminProfile = require('../models/AdminProfile');
const RoleApplication = require('../models/RoleApplication');
const {
  PROVIDER_ROLES,
  getRoleAssignment,
  hasAssignedRole,
  canAccessAssignedRole,
  canOnboardProviderRole,
  isCustomerOperational
} = require('../utils/roleAccess');
const { getOrCreateRoleProfile, getRoleProfilesBundle } = require('../utils/profileHelpers');

const sanitizeUser = (user) => ({
  _id: user._id,
  username: user.username,
  fullName: user.fullName,
  email: user.email,
  role: user.role,
  roles: user.roles,
  address: user.address,
  city: user.city,
  phone: user.phone,
  dob: user.dob,
  bio: user.bio,
  profilePic: user.profilePic,
  accountStatus: user.accountStatus,
  verificationStatus: user.verificationStatus,
  createdAt: user.createdAt,
  updatedAt: user.updatedAt
});

const buildSelfProfilePayload = async (user) => {
  const freshUser = await User.findById(user._id).select('-password');
  const profiles = await getRoleProfilesBundle(freshUser);
  const roleApplications = await RoleApplication.find({ applicant: freshUser._id }).sort({ createdAt: -1 });

  return {
    user: sanitizeUser(freshUser),
    ...profiles,
    roleApplications
  };
};

const syncDriverProfileToUser = (user, driverProfile) => {
  user.driverProfile = {
    ...user.driverProfile,
    drivingLicenseNumber: driverProfile.drivingLicenseNumber || '',
    licenseExpiryDate: driverProfile.licenseExpiryDate || null,
    nicId: driverProfile.nicId || '',
    serviceArea: driverProfile.serviceArea || '',
    providerDetails: driverProfile.providerDetails || '',
    verificationStatus: getRoleAssignment(user, 'driver')?.verificationStatus || user.driverProfile?.verificationStatus || 'pending',
    verifiedAt: user.driverProfile?.verifiedAt
  };
};

const syncStaffProfileToUser = (user, staffProfile) => {
  user.staffProfile = {
    ...user.staffProfile,
    storeName: staffProfile.storeName || '',
    storeOwner: staffProfile.storeOwner || '',
    businessRegistrationNumber: staffProfile.businessRegistrationNumber || '',
    storeAddress: staffProfile.storeAddress || '',
    storeContactNumber: staffProfile.storeContactNumber || '',
    storeEmail: staffProfile.storeEmail || '',
    verificationStatus: getRoleAssignment(user, 'staff')?.verificationStatus || user.staffProfile?.verificationStatus || 'pending',
    verifiedAt: user.staffProfile?.verifiedAt
  };
};

// @desc    Get current user profile summary
// @route   GET /api/users/profile
const getProfile = async (req, res) => {
  try {
    const payload = await buildSelfProfilePayload(req.user);
    res.json(payload);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update user common profile
// @route   PUT /api/users/profile
const updateProfile = async (req, res) => {
  try {
    const user = await User.findById(req.user._id);
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    const { fullName, phone, address, city, dob, bio } = req.body;

    user.fullName = fullName || user.fullName;
    user.phone = phone !== undefined ? phone : user.phone;
    user.address = address !== undefined ? address : user.address;
    user.city = city !== undefined ? city : user.city;
    user.dob = dob !== undefined ? dob : user.dob;
    user.bio = bio !== undefined ? bio : user.bio;

    if (req.file) {
      user.profilePic = `profiles/${req.file.filename}`;
    }

    const updatedUser = await user.save();

    res.json({
      message: 'Profile updated successfully',
      user: sanitizeUser(updatedUser)
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update current user password
// @route   PUT /api/users/password
const updatePassword = async (req, res) => {
  try {
    const { currentPassword, newPassword } = req.body;
    const user = await User.findById(req.user._id);

    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    if (!currentPassword || !newPassword) {
      return res.status(400).json({ message: 'Current password and new password are required' });
    }

    const isMatch = await user.matchPassword(currentPassword);
    if (!isMatch) {
      return res.status(400).json({ message: 'Current password is incorrect' });
    }

    user.password = newPassword;
    await user.save();

    res.json({ message: 'Password updated successfully' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get customer profile
// @route   GET /api/users/customer-profile
const getCustomerProfile = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'customer')) {
      return res.status(403).json({ message: 'Customer role is not assigned to your account' });
    }

    const profile = await getOrCreateRoleProfile('customer', req.user._id);
    res.json(profile);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update customer profile
// @route   PUT /api/users/customer-profile
const updateCustomerProfile = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'customer')) {
      return res.status(403).json({ message: 'Customer role is not assigned to your account' });
    }

    const profile = await getOrCreateRoleProfile('customer', req.user._id);
    const { preferredContactMethod, emergencyContactName, emergencyContactPhone, notes } = req.body;

    profile.preferredContactMethod = preferredContactMethod ?? profile.preferredContactMethod;
    profile.emergencyContactName = emergencyContactName ?? profile.emergencyContactName;
    profile.emergencyContactPhone = emergencyContactPhone ?? profile.emergencyContactPhone;
    profile.notes = notes ?? profile.notes;

    await profile.save();

    res.json({ message: 'Customer profile updated', customerProfile: profile });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get driver profile
// @route   GET /api/users/driver-profile
const getDriverProfile = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'driver')) {
      return res.status(403).json({ message: 'Driver role is not assigned to your account' });
    }

    const profile = await getOrCreateRoleProfile('driver', req.user._id);
    res.json(profile);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update driver profile
// @route   PUT /api/users/driver-profile
const updateDriverProfile = async (req, res) => {
  try {
    if (!canOnboardProviderRole(req.user, 'driver')) {
      return res.status(403).json({ message: 'Driver onboarding is not available for your account' });
    }

    const user = await User.findById(req.user._id);
    const profile = await getOrCreateRoleProfile('driver', req.user._id);
    const { drivingLicenseNumber, licenseExpiryDate, nicId, serviceArea, providerDetails } = req.body;

    profile.drivingLicenseNumber = drivingLicenseNumber ?? profile.drivingLicenseNumber;
    profile.licenseExpiryDate = licenseExpiryDate ?? profile.licenseExpiryDate;
    profile.nicId = nicId ?? profile.nicId;
    profile.serviceArea = serviceArea ?? profile.serviceArea;
    profile.providerDetails = providerDetails ?? profile.providerDetails;
    profile.onboardingCompleted = Boolean(profile.drivingLicenseNumber && profile.nicId && profile.serviceArea);
    await profile.save();

    syncDriverProfileToUser(user, profile);
    await user.save({ validateModifiedOnly: true });

    res.json({ message: 'Driver profile updated', driverProfile: profile });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get staff profile
// @route   GET /api/users/staff-profile
const getStaffProfile = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'staff')) {
      return res.status(403).json({ message: 'Staff role is not assigned to your account' });
    }

    const profile = await getOrCreateRoleProfile('staff', req.user._id);
    res.json(profile);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update staff profile
// @route   PUT /api/users/staff-profile
const updateStaffProfile = async (req, res) => {
  try {
    if (!canOnboardProviderRole(req.user, 'staff')) {
      return res.status(403).json({ message: 'Staff onboarding is not available for your account' });
    }

    const user = await User.findById(req.user._id);
    const profile = await getOrCreateRoleProfile('staff', req.user._id);
    const { storeName, storeOwner, businessRegistrationNumber, storeAddress, storeContactNumber, storeEmail } = req.body;

    profile.storeName = storeName ?? profile.storeName;
    profile.storeOwner = storeOwner ?? profile.storeOwner;
    profile.businessRegistrationNumber = businessRegistrationNumber ?? profile.businessRegistrationNumber;
    profile.storeAddress = storeAddress ?? profile.storeAddress;
    profile.storeContactNumber = storeContactNumber ?? profile.storeContactNumber;
    profile.storeEmail = storeEmail ?? profile.storeEmail;
    profile.onboardingCompleted = Boolean(profile.storeName && profile.businessRegistrationNumber && profile.storeAddress);
    await profile.save();

    syncStaffProfileToUser(user, profile);
    await user.save({ validateModifiedOnly: true });

    res.json({ message: 'Staff profile updated', staffProfile: profile });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get admin profile
// @route   GET /api/users/admin-profile
const getAdminProfile = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'admin')) {
      return res.status(403).json({ message: 'Admin role is not assigned to your account' });
    }

    const profile = await getOrCreateRoleProfile('admin', req.user._id);
    res.json(profile);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update admin profile
// @route   PUT /api/users/admin-profile
const updateAdminProfile = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'admin')) {
      return res.status(403).json({ message: 'Admin role is not assigned to your account' });
    }

    const profile = await getOrCreateRoleProfile('admin', req.user._id);
    profile.adminNotes = req.body.adminNotes ?? profile.adminNotes;
    await profile.save();

    res.json({ message: 'Admin profile updated', adminProfile: profile });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get role choice/switch metadata
// @route   GET /api/users/roles/options
const getRoleOptions = async (req, res) => {
  try {
    const roleApplications = await RoleApplication.find({ applicant: req.user._id }).sort({ createdAt: -1 });

    const roleOptions = (req.user.roles || []).map((item) => ({
      roleKey: item.roleKey,
      roleStatus: item.roleStatus,
      verificationStatus: item.verificationStatus,
      isPrimary: item.isPrimary,
      canSwitch: canAccessAssignedRole(req.user, item.roleKey),
      onboardingOnly: PROVIDER_ROLES.includes(item.roleKey) && !['approved', 'verified'].includes(item.verificationStatus)
    }));

    res.json({
      activeRole: req.user.role,
      roleOptions,
      roleApplications
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Apply for provider role
// @route   POST /api/users/roles/apply
const applyForRole = async (req, res) => {
  try {
    const { roleKey, motivation } = req.body;

    if (!PROVIDER_ROLES.includes(roleKey)) {
      return res.status(400).json({ message: 'Only driver or staff applications are supported' });
    }

    if (!isCustomerOperational(req.user)) {
      return res.status(403).json({ message: 'Only active and verified customers can apply for provider roles' });
    }

    const user = await User.findById(req.user._id);
    const existingApplication = await RoleApplication.findOne({
      applicant: req.user._id,
      roleKey,
      status: 'pending'
    });

    if (existingApplication) {
      return res.status(400).json({ message: 'You already have a pending application for this role' });
    }

    const existingRole = getRoleAssignment(user, roleKey);
    if (existingRole && existingRole.roleStatus === 'active' && ['approved', 'verified'].includes(existingRole.verificationStatus)) {
      return res.status(400).json({ message: `Your ${roleKey} role is already active` });
    }

    if (existingRole) {
      existingRole.roleStatus = 'pending';
      existingRole.verificationStatus = 'pending';
    } else {
      user.roles.push({
        roleKey,
        roleStatus: 'pending',
        verificationStatus: 'pending',
        isPrimary: false
      });
    }

    await user.save({ validateModifiedOnly: true });
    await getOrCreateRoleProfile(roleKey, user._id);

    const application = await RoleApplication.create({
      applicant: user._id,
      roleKey,
      motivation: motivation || '',
      status: 'pending'
    });

    res.status(201).json({
      message: `${roleKey} application submitted`,
      application
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get own role applications
// @route   GET /api/users/roles/applications
const getMyRoleApplications = async (req, res) => {
  try {
    const applications = await RoleApplication.find({ applicant: req.user._id }).sort({ createdAt: -1 });
    res.json(applications);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get user by ID (public)
// @route   GET /api/users/:id
const getUserById = async (req, res) => {
  try {
    const user = await User.findById(req.params.id).select('-password');
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    res.json(sanitizeUser(user));
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = {
  getProfile,
  updateProfile,
  updatePassword,
  getCustomerProfile,
  updateCustomerProfile,
  getDriverProfile,
  updateDriverProfile,
  getStaffProfile,
  updateStaffProfile,
  getAdminProfile,
  updateAdminProfile,
  getRoleOptions,
  applyForRole,
  getMyRoleApplications,
  getUserById
};
