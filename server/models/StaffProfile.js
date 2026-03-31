const mongoose = require('mongoose');

const staffProfileSchema = new mongoose.Schema({
  user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true, unique: true },
  storeName: { type: String, default: '' },
  storeOwner: { type: String, default: '' },
  businessRegistrationNumber: { type: String, default: '' },
  storeAddress: { type: String, default: '' },
  storeContactNumber: { type: String, default: '' },
  storeEmail: { type: String, default: '' },
  onboardingCompleted: { type: Boolean, default: false }
}, {
  timestamps: true
});

module.exports = mongoose.model('StaffProfile', staffProfileSchema);
