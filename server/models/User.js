const mongoose = require('mongoose');
const bcrypt = require('bcryptjs');

const userSchema = new mongoose.Schema({
  username: { type: String, required: true, unique: true },
  password: { type: String, required: true },
  role: {
    type: String,
    enum: ['admin', 'staff', 'driver', 'customer'],
    default: 'customer'
  },
  fullName: { type: String, required: true },
  email: { type: String, required: true, unique: true },
  address: { type: String, default: '' },
  city: { type: String, default: '' },
  phone: { type: String, default: '' },
  dob: { type: String, default: '' },
  licenseOrNic: { type: String, default: '' },
  verificationStatus: {
    type: String,
    enum: ['unverified', 'pending', 'approved', 'rejected', 'verified'],
    default: 'verified'
  },
  bio: { type: String, default: '' },
  profilePic: { type: String, default: 'avatar.png' },
  accountStatus: {
    type: String,
    enum: ['active', 'pending', 'suspended', 'rejected', 'deactivated'],
    default: 'active'
  },
  roles: [{
    roleKey: { type: String, enum: ['admin', 'staff', 'driver', 'customer'] },
    roleStatus: { type: String, default: 'active' },
    verificationStatus: { type: String, default: 'verified' },
    isPrimary: { type: Boolean, default: false }
  }],
  // Driver profile fields
  driverProfile: {
    drivingLicenseNumber: { type: String, default: '' },
    licenseExpiryDate: { type: Date },
    nicId: { type: String, default: '' },
    serviceArea: { type: String, default: '' },
    providerDetails: { type: String, default: '' },
    verificationStatus: { type: String, default: 'pending' },
    verifiedAt: { type: Date }
  },
  // Staff profile fields
  staffProfile: {
    storeName: { type: String, default: '' },
    storeOwner: { type: String, default: '' },
    businessRegistrationNumber: { type: String, default: '' },
    storeAddress: { type: String, default: '' },
    storeContactNumber: { type: String, default: '' },
    storeEmail: { type: String, default: '' },
    verificationStatus: { type: String, default: 'pending' },
    verifiedAt: { type: Date }
  },
  // Admin profile fields
  adminProfile: {
    systemPermissions: { type: String, default: 'all' }
  },
  lastLoginAt: { type: Date }
}, {
  timestamps: true
});

// Hash password before save
userSchema.pre('save', async function (next) {
  if (!this.isModified('password')) return next();
  const salt = await bcrypt.genSalt(10);
  this.password = await bcrypt.hash(this.password, salt);
  next();
});

// Compare password
userSchema.methods.matchPassword = async function (enteredPassword) {
  return await bcrypt.compare(enteredPassword, this.password);
};

module.exports = mongoose.model('User', userSchema);
