const mongoose = require('mongoose');

const adminProfileSchema = new mongoose.Schema({
  user: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true, unique: true },
  systemPermissions: { type: String, default: 'all' },
  adminNotes: { type: String, default: '' }
}, {
  timestamps: true
});

module.exports = mongoose.model('AdminProfile', adminProfileSchema);
