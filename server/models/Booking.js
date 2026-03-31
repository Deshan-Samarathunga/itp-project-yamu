const mongoose = require('mongoose');

const bookingSchema = new mongoose.Schema({
  bookingNo: { type: String, required: true, unique: true },
  customer: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  driver: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  vehicle: { type: mongoose.Schema.Types.ObjectId, ref: 'Vehicle' },
  startDate: { type: String, required: true },
  endDate: { type: String, required: true },
  total: { type: Number, required: true },
  bookingStatus: {
    type: String,
    enum: ['pending', 'confirmed', 'rejected', 'cancelled', 'completed'],
    default: 'pending'
  },
  paymentStatus: {
    type: String,
    enum: ['pending', 'paid', 'failed', 'refunded'],
    default: 'pending'
  },
  promotion: { type: mongoose.Schema.Types.ObjectId, ref: 'Promotion' },
  promoCode: { type: String },
  discountAmount: { type: Number, default: 0 },
  finalAmount: { type: Number },
  bookingDate: { type: Date, default: Date.now },
  cancelledAt: { type: Date },
  completedAt: { type: Date }
}, {
  timestamps: true
});

module.exports = mongoose.model('Booking', bookingSchema);
