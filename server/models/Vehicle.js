const mongoose = require('mongoose');

const vehicleSchema = new mongoose.Schema({
  owner: { type: mongoose.Schema.Types.ObjectId, ref: 'User', required: true },
  vehicleTitle: { type: String, required: true },
  vehicleBrand: { type: String, required: true },
  vehicleDesc: { type: String, default: '' },
  price: { type: Number, required: true },
  transmission: { type: String, enum: ['Automatic', 'Manual'], required: true },
  fuelType: { type: String, required: true },
  year: { type: Number, required: true },
  engineCapacity: { type: String, default: '' },
  capacity: { type: Number, default: 5 },
  location: { type: String, required: true },
  registrationNumber: { type: String, required: true },
  images: {
    img1: { type: String, default: '' },
    img2: { type: String, default: '' },
    img3: { type: String, default: '' },
    img4: { type: String, default: '' }
  },
  features: {
    airConditioner: { type: Boolean, default: false },
    powerDoorLocks: { type: Boolean, default: false },
    antiLockBrakingSystem: { type: Boolean, default: false },
    brakeAssist: { type: Boolean, default: false },
    powerSteering: { type: Boolean, default: false },
    driverAirbag: { type: Boolean, default: false },
    passengerAirbag: { type: Boolean, default: false },
    powerWindows: { type: Boolean, default: false },
    cdPlayer: { type: Boolean, default: false }
  },
  listingStatus: {
    type: String,
    enum: ['pending', 'approved', 'rejected', 'inactive'],
    default: 'pending'
  },
  availabilityStatus: {
    type: String,
    enum: ['available', 'booked', 'unavailable'],
    default: 'available'
  },
  maintenanceStatus: {
    type: String,
    enum: ['good', 'due soon', 'under maintenance', 'unavailable'],
    default: 'good'
  },
  serviceDate: { type: Date },
  nextServiceDate: { type: Date },
  serviceNotes: { type: String, default: '' },
  serviceCost: { type: Number },
  approvedBy: { type: mongoose.Schema.Types.ObjectId, ref: 'User' },
  approvedAt: { type: Date }
}, {
  timestamps: true
});

module.exports = mongoose.model('Vehicle', vehicleSchema);
