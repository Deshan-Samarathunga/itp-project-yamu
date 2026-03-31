const mongoose = require('mongoose');

const promotionSchema = new mongoose.Schema({
  code: { type: String, required: true, unique: true },
  title: { type: String, default: '' },
  description: { type: String, default: '' },
  discountType: {
    type: String,
    enum: ['fixed', 'percentage'],
    default: 'fixed'
  },
  discountValue: { type: Number, default: 0 },
  validFrom: { type: Date },
  validTo: { type: Date },
  usageLimit: { type: Number },
  usageCount: { type: Number, default: 0 },
  minimumBookingAmount: { type: Number, default: 0 },
  status: {
    type: String,
    enum: ['active', 'inactive', 'expired'],
    default: 'active'
  },
  applicableVehicle: { type: mongoose.Schema.Types.ObjectId, ref: 'Vehicle' }
}, {
  timestamps: true
});

module.exports = mongoose.model('Promotion', promotionSchema);
