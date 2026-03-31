const mongoose = require('mongoose');

const brandSchema = new mongoose.Schema({
  brandName: { type: String, required: true },
  brandLogo: { type: String, default: '' },
  brandStatus: { type: Number, default: 1 }
}, {
  timestamps: true
});

module.exports = mongoose.model('Brand', brandSchema);
