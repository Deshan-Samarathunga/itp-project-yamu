const mongoose = require('mongoose');

const paymentSchema = new mongoose.Schema({
  booking: { type: mongoose.Schema.Types.ObjectId, ref: 'Booking', required: true },
  customer: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  driver: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  promotion: { type: mongoose.Schema.Types.ObjectId, ref: 'Promotion' },
  promoCode: { type: String },
  amount: { type: Number, required: true },
  discountAmount: { type: Number, default: 0 },
  finalAmount: { type: Number, required: true },
  paymentMethod: { type: String, default: '' },
  transactionReference: { type: String, default: '' },
  paymentStatus: {
    type: String,
    enum: ['pending', 'paid', 'failed', 'refunded'],
    default: 'pending'
  },
  paidAt: { type: Date }
}, {
  timestamps: true
});

module.exports = mongoose.model('Payment', paymentSchema);
