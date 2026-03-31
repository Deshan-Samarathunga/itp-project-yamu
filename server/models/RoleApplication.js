const mongoose = require('mongoose');

const roleApplicationSchema = new mongoose.Schema({
  applicant: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  roleKey: {
    type: String,
    enum: ['driver', 'staff'],
    required: true
  },
  status: {
    type: String,
    enum: ['pending', 'approved', 'rejected'],
    default: 'pending'
  },
  motivation: { type: String, default: '' },
  adminNotes: { type: String, default: '' },
  reviewedBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  reviewedAt: { type: Date }
}, {
  timestamps: true
});

roleApplicationSchema.index({ applicant: 1, roleKey: 1, status: 1 });

module.exports = mongoose.model('RoleApplication', roleApplicationSchema);
