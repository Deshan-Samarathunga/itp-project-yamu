const mongoose = require('mongoose');

const customerProfileSchema = new mongoose.Schema({
  user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true, unique: true },
  preferredContactMethod: { type: String, default: 'email' },
  emergencyContactName: { type: String, default: '' },
  emergencyContactPhone: { type: String, default: '' },
  notes: { type: String, default: '' }
}, {
  timestamps: true
});

module.exports = mongoose.model('CustomerProfile', customerProfileSchema);
