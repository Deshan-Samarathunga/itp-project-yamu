const User = require('../models/User');
const { generateToken } = require('../middleware/auth');
const { getRoleProfilesBundle } = require('../utils/profileHelpers');
const { getRoleAssignment, canAccessAssignedRole } = require('../utils/roleAccess');

const buildAuthPayload = async (user) => {
  const freshUser = await User.findById(user._id).select('-password');
  const profiles = await getRoleProfilesBundle(freshUser);

  return {
    _id: freshUser._id,
    fullName: freshUser.fullName,
    email: freshUser.email,
    username: freshUser.username,
    role: freshUser.role,
    roles: freshUser.roles,
    accountStatus: freshUser.accountStatus,
    verificationStatus: freshUser.verificationStatus,
    profilePic: freshUser.profilePic,
    phone: freshUser.phone,
    city: freshUser.city,
    address: freshUser.address,
    bio: freshUser.bio,
    dob: freshUser.dob,
    ...profiles
  };
};

// @desc    Register user
// @route   POST /api/auth/register
const register = async (req, res) => {
  try {
    const { fullName, email, password, username } = req.body;

    const userExists = await User.findOne({
      $or: [{ email }, { username: username || email }]
    });

    if (userExists) {
      return res.status(400).json({ message: 'User already exists with this email' });
    }

    const user = await User.create({
      username: username || email,
      fullName,
      email,
      password,
      role: 'customer',
      accountStatus: 'active',
      verificationStatus: 'verified',
      roles: [{
        roleKey: 'customer',
        roleStatus: 'active',
        verificationStatus: 'verified',
        isPrimary: true
      }]
    });

    const payload = await buildAuthPayload(user);

    res.status(201).json({
      ...payload,
      token: generateToken(user._id)
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Login user
// @route   POST /api/auth/login
const login = async (req, res) => {
  try {
    const { email, password } = req.body;

    const user = await User.findOne({
      $or: [{ email }, { username: email }]
    });

    if (!user) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    const isMatch = await user.matchPassword(password);
    if (!isMatch) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    if (['suspended', 'deactivated', 'rejected'].includes(user.accountStatus)) {
      return res.status(403).json({ message: 'Your account is not available for login' });
    }

    user.lastLoginAt = new Date();
    await user.save({ validateModifiedOnly: true });

    const payload = await buildAuthPayload(user);

    res.json({
      ...payload,
      token: generateToken(user._id)
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get current user profile
// @route   GET /api/auth/me
const getMe = async (req, res) => {
  try {
    const payload = await buildAuthPayload(req.user);
    res.json(payload);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Switch active role
// @route   PUT /api/auth/switch-role
const switchRole = async (req, res) => {
  try {
    const { role } = req.body;
    const user = await User.findById(req.user._id);

    if (!user) {
      return res.status(404).json({ message: 'User not found' });
    }

    const roleAssignment = getRoleAssignment(user, role);
    if (!roleAssignment) {
      return res.status(400).json({ message: 'Role not assigned to your account' });
    }

    if (!canAccessAssignedRole(user, role)) {
      return res.status(400).json({ message: 'Selected role is currently unavailable' });
    }

    user.role = role;
    await user.save({ validateModifiedOnly: true });

    const payload = await buildAuthPayload(user);

    res.json({
      ...payload,
      token: generateToken(user._id)
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { register, login, getMe, switchRole };
