const User = require('../models/User');
const Vehicle = require('../models/Vehicle');
const Booking = require('../models/Booking');
const Review = require('../models/Review');
const Complaint = require('../models/Complaint');
const Payment = require('../models/Payment');
const RoleApplication = require('../models/RoleApplication');
const CustomerProfile = require('../models/CustomerProfile');
const DriverProfile = require('../models/DriverProfile');
const StaffProfile = require('../models/StaffProfile');
const AdminProfile = require('../models/AdminProfile');
const { ROLE_KEYS, sanitizeAssignedRoles } = require('../utils/roleAccess');

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

const syncProviderVerificationSnapshots = (user) => {
  const driverRole = user.roles.find((item) => item.roleKey === 'driver');
  const staffRole = user.roles.find((item) => item.roleKey === 'staff');

  if (driverRole) {
    user.driverProfile = {
      ...user.driverProfile,
      verificationStatus: driverRole.verificationStatus,
      verifiedAt: ['approved', 'verified'].includes(driverRole.verificationStatus) ? new Date() : user.driverProfile?.verifiedAt
    };
  }

  if (staffRole) {
    user.staffProfile = {
      ...user.staffProfile,
      verificationStatus: staffRole.verificationStatus,
      verifiedAt: ['approved', 'verified'].includes(staffRole.verificationStatus) ? new Date() : user.staffProfile?.verifiedAt
    };
  }
};

// @desc    Get admin dashboard stats
// @route   GET /api/admin/dashboard
const getDashboardStats = async (req, res) => {
  try {
    const [totalUsers, totalVehicles, totalBookings, totalRevenue, pendingBookings, activeComplaints, pendingProviderApplications] = await Promise.all([
      User.countDocuments(),
      Vehicle.countDocuments(),
      Booking.countDocuments(),
      Payment.aggregate([{ $match: { paymentStatus: 'paid' } }, { $group: { _id: null, total: { $sum: '$finalAmount' } } }]),
      Booking.countDocuments({ bookingStatus: 'pending' }),
      Complaint.countDocuments({ status: { $in: ['open', 'in_progress'] } }),
      RoleApplication.countDocuments({ status: 'pending' })
    ]);

    res.json({
      totalUsers,
      totalVehicles,
      totalBookings,
      totalRevenue: totalRevenue.length > 0 ? totalRevenue[0].total : 0,
      pendingBookings,
      activeComplaints,
      pendingProviderApplications
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all users (admin)
// @route   GET /api/admin/users
const getAllUsers = async (req, res) => {
  try {
    const { role, status, search } = req.query;
    const filter = {};

    if (role) filter['roles.roleKey'] = role;
    if (status) filter.accountStatus = status;
    if (search) {
      filter.$or = [
        { fullName: { $regex: search, $options: 'i' } },
        { email: { $regex: search, $options: 'i' } },
        { username: { $regex: search, $options: 'i' } }
      ];
    }

    const users = await User.find(filter).select('-password').sort({ createdAt: -1 });
    res.json(users.map(sanitizeUser));
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get user details (admin)
// @route   GET /api/admin/users/:id
const getUserDetails = async (req, res) => {
  try {
    const user = await User.findById(req.params.id).select('-password');
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    const [bookings, reviews, complaints, customerProfile, driverProfile, staffProfile, adminProfile, roleApplications] = await Promise.all([
      Booking.countDocuments({ $or: [{ customer: user._id }, { driver: user._id }] }),
      Review.countDocuments({ customer: user._id }),
      Complaint.countDocuments({ complainant: user._id }),
      CustomerProfile.findOne({ user: user._id }),
      DriverProfile.findOne({ user: user._id }),
      StaffProfile.findOne({ user: user._id }),
      AdminProfile.findOne({ user: user._id }),
      RoleApplication.find({ applicant: user._id }).sort({ createdAt: -1 })
    ]);

    res.json({
      user: sanitizeUser(user),
      stats: { bookings, reviews, complaints },
      profiles: { customerProfile, driverProfile, staffProfile, adminProfile },
      roleApplications
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update user role assignments/details (admin)
// @route   PUT /api/admin/users/:id
const updateUser = async (req, res) => {
  try {
    const user = await User.findById(req.params.id);
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    const {
      fullName,
      email,
      activeRole,
      assignedRoles,
      accountStatus,
      verificationStatus,
      roleStatuses
    } = req.body;

    if (fullName) user.fullName = fullName;
    if (email) {
      user.email = email;
      user.username = email;
    }
    if (accountStatus) user.accountStatus = accountStatus;
    if (verificationStatus) user.verificationStatus = verificationStatus;

    const requestedRoles = Array.isArray(assignedRoles) ? assignedRoles : user.roles.map((item) => item.roleKey);
    const nextRoles = sanitizeAssignedRoles(requestedRoles);
    const includesExistingAdmin = user.roles.some((item) => item.roleKey === 'admin');
    const nonAdminRoles = nextRoles.filter((roleKey) => roleKey !== 'admin');
    const ensuredRoles = nonAdminRoles.includes('customer') ? nonAdminRoles : [...nonAdminRoles, 'customer'];

    if (includesExistingAdmin) {
      ensuredRoles.push('admin');
    }

    user.roles = ensuredRoles.map((roleKey, index) => {
      const existing = user.roles.find((item) => item.roleKey === roleKey);
      const rolePatch = roleStatuses?.[roleKey] || {};
      return {
        roleKey,
        roleStatus: rolePatch.roleStatus || existing?.roleStatus || (roleKey === 'customer' ? 'active' : 'pending'),
        verificationStatus: rolePatch.verificationStatus || existing?.verificationStatus || (roleKey === 'customer' ? 'verified' : 'pending'),
        isPrimary: existing?.isPrimary ?? index === 0
      };
    });

    if (activeRole && ensuredRoles.includes(activeRole)) {
      user.role = activeRole;
    } else if (!ensuredRoles.includes(user.role)) {
      user.role = 'customer';
    }

    syncProviderVerificationSnapshots(user);
    await user.save();

    res.json({
      message: 'User updated',
      user: sanitizeUser(await User.findById(req.params.id).select('-password'))
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update user account status (admin)
// @route   PUT /api/admin/users/:id/status
const updateUserStatus = async (req, res) => {
  try {
    const { accountStatus } = req.body;
    const user = await User.findById(req.params.id);

    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    user.accountStatus = accountStatus;
    await user.save({ validateModifiedOnly: true });

    res.json({ message: 'User status updated', user: sanitizeUser(user) });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update user verification status (admin)
// @route   PUT /api/admin/users/:id/verify
const updateUserVerification = async (req, res) => {
  try {
    const { verificationStatus } = req.body;
    const user = await User.findById(req.params.id);

    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    user.verificationStatus = verificationStatus;
    await user.save({ validateModifiedOnly: true });

    res.json({ message: 'User verification updated', user: sanitizeUser(user) });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Delete user (admin)
// @route   DELETE /api/admin/users/:id
const deleteUser = async (req, res) => {
  try {
    const user = await User.findByIdAndDelete(req.params.id);
    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }
    res.json({ message: 'User removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get provider role applications
// @route   GET /api/admin/provider-applications
const getProviderApplications = async (req, res) => {
  try {
    const applications = await RoleApplication.find()
      .populate('applicant', 'fullName email role roles accountStatus verificationStatus')
      .populate('reviewedBy', 'fullName email')
      .sort({ createdAt: -1 });

    res.json(applications);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Approve/reject provider application
// @route   PUT /api/admin/provider-applications/:id
const reviewProviderApplication = async (req, res) => {
  try {
    const { status, adminNotes } = req.body;
    const application = await RoleApplication.findById(req.params.id);

    if (!application) {
      return res.status(404).json({ message: 'Provider application not found' });
    }

    if (!['approved', 'rejected'].includes(status)) {
      return res.status(400).json({ message: 'Application status must be approved or rejected' });
    }

    const user = await User.findById(application.applicant);
    if (!user) {
      return res.status(404).json({ message: 'Application user not found' });
    }

    const roleAssignment = user.roles.find((item) => item.roleKey === application.roleKey);
    if (!roleAssignment) {
      user.roles.push({
        roleKey: application.roleKey,
        roleStatus: status === 'approved' ? 'active' : 'rejected',
        verificationStatus: status === 'approved' ? 'approved' : 'rejected',
        isPrimary: false
      });
    } else {
      roleAssignment.roleStatus = status === 'approved' ? 'active' : 'rejected';
      roleAssignment.verificationStatus = status === 'approved' ? 'approved' : 'rejected';
    }

    if (status === 'rejected' && user.role === application.roleKey) {
      user.role = 'customer';
    }

    syncProviderVerificationSnapshots(user);
    await user.save();

    application.status = status;
    application.adminNotes = adminNotes || '';
    application.reviewedBy = req.user._id;
    application.reviewedAt = new Date();
    await application.save();

    res.json({ message: `Application ${status}`, application });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all complaints (admin)
// @route   GET /api/admin/complaints
const getAllComplaints = async (req, res) => {
  try {
    const complaints = await Complaint.find()
      .populate('complainant', 'fullName email')
      .populate('targetUser', 'fullName email')
      .populate('booking', 'bookingNo')
      .sort({ createdAt: -1 });
    res.json(complaints);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update complaint status (admin)
// @route   PUT /api/admin/complaints/:id
const updateComplaint = async (req, res) => {
  try {
    const complaint = await Complaint.findById(req.params.id);
    if (!complaint) {
      return res.status(404).json({ message: 'Complaint not found' });
    }

    if (req.body.status) complaint.status = req.body.status;
    if (req.body.adminNotes) complaint.adminNotes = req.body.adminNotes;

    await complaint.save();
    res.json(complaint);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all vehicles (admin)
// @route   GET /api/admin/vehicles
const getAllVehicles = async (req, res) => {
  try {
    const vehicles = await Vehicle.find()
      .populate('owner', 'fullName email')
      .sort({ createdAt: -1 });
    res.json(vehicles);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = {
  getDashboardStats,
  getAllUsers,
  getUserDetails,
  updateUser,
  updateUserStatus,
  updateUserVerification,
  deleteUser,
  getProviderApplications,
  reviewProviderApplication,
  getAllComplaints,
  updateComplaint,
  getAllVehicles
};
