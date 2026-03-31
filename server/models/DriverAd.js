const mongoose = require('mongoose');

const driverAdSchema = new mongoose.Schema({
  driver: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  adTitle: { type: String, default: '' },
  tagline: { type: String, default: '' },
  serviceLocation: { type: String, default: '' },
  languages: { type: String, default: '' },
  experienceYears: { type: Number, default: 0 },
  dailyRate: { type: Number, default: 0 },
  maxGroupSize: { type: Number, default: 1 },
  availabilityStatus: {
    type: String,
    enum: ['available', 'unavailable', 'booked'],
    default: 'available'
  },
  advertisementStatus: {
    type: String,
    enum: ['draft', 'active', 'inactive'],
    default: 'draft'
  },
  specialties: { type: String, default: '' },
  description: { type: String, default: '' },
  contactPreference: {
    type: String,
    enum: ['phone', 'email', 'both'],
    default: 'both'
  }
}, {
  timestamps: true
});

module.exports = mongoose.model('DriverAd', driverAdSchema);
