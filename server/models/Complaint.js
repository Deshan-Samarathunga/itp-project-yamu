const mongoose = require('mongoose');

const complaintSchema = new mongoose.Schema({
  booking: { type: mongoose.Schema.Types.ObjectId, ref: 'Booking', required: true },
  complainant: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  targetUser: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  targetVehicle: { type: mongoose.Schema.Types.ObjectId, ref: 'Vehicle' },
  subject: { type: String, default: '' },
  category: { type: String, default: '' },
  description: { type: String, default: '' },
  attachment: { type: String, default: '' },
  status: {
    type: String,
    enum: ['open', 'in_progress', 'resolved', 'closed'],
    default: 'open'
  },
  driverResponse: { type: String, default: '' },
  adminNotes: { type: String, default: '' }
}, {
  timestamps: true
});

module.exports = mongoose.model('Complaint', complaintSchema);
