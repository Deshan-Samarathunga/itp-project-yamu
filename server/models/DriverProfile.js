const mongoose = require('mongoose');

const driverProfileSchema = new mongoose.Schema({
  user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true, unique: true },
  drivingLicenseNumber: { type: String, default: '' },
  licenseExpiryDate: { type: Date },
  nicId: { type: String, default: '' },
  serviceArea: { type: String, default: '' },
  providerDetails: { type: String, default: '' },
  onboardingCompleted: { type: Boolean, default: false }
}, {
  timestamps: true
});

module.exports = mongoose.model('DriverProfile', driverProfileSchema);
