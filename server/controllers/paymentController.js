const Payment = require('../models/Payment');
const Booking = require('../models/Booking');

// @desc    Create payment
// @route   POST /api/payments
const createPayment = async (req, res) => {
  try {
    const { bookingId, paymentMethod, transactionReference } = req.body;

    const booking = await Booking.findById(bookingId);
    if (!booking) {
      return res.status(404).json({ message: 'Booking not found' });
    }

    const payment = await Payment.create({
      booking: bookingId,
      customer: booking.customer,
      driver: booking.driver,
      promotion: booking.promotion,
      promoCode: booking.promoCode,
      amount: booking.total,
      discountAmount: booking.discountAmount,
      finalAmount: booking.finalAmount || booking.total,
      paymentMethod,
      transactionReference,
      paymentStatus: 'paid',
      paidAt: new Date()
    });

    // Update booking payment status
    booking.paymentStatus = 'paid';
    await booking.save();

    res.status(201).json(payment);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get my payments
// @route   GET /api/payments/my
const getMyPayments = async (req, res) => {
  try {
    const payments = await Payment.find({ customer: req.user._id })
      .populate('booking', 'bookingNo startDate endDate bookingStatus')
      .sort({ createdAt: -1 });
    res.json(payments);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all payments (admin)
// @route   GET /api/payments
const getAllPayments = async (req, res) => {
  try {
    const payments = await Payment.find()
      .populate('booking', 'bookingNo')
      .populate('customer', 'fullName email')
      .populate('driver', 'fullName email')
      .sort({ createdAt: -1 });
    res.json(payments);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get driver earnings
// @route   GET /api/payments/earnings
const getDriverEarnings = async (req, res) => {
  try {
    const payments = await Payment.find({ driver: req.user._id, paymentStatus: 'paid' })
      .populate('booking', 'bookingNo startDate endDate')
      .sort({ createdAt: -1 });

    const totalEarnings = payments.reduce((sum, p) => sum + p.finalAmount, 0);

    res.json({ totalEarnings, payments });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { createPayment, getMyPayments, getAllPayments, getDriverEarnings };
