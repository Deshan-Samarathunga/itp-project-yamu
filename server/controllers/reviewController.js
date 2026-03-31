const Review = require('../models/Review');

// @desc    Create review
// @route   POST /api/reviews
const createReview = async (req, res) => {
  try {
    const { bookingId, vehicleId, driverId, rating, comment } = req.body;

    const existing = await Review.findOne({ booking: bookingId, customer: req.user._id });
    if (existing) {
      return res.status(400).json({ message: 'You have already reviewed this booking' });
    }

    const review = await Review.create({
      booking: bookingId,
      customer: req.user._id,
      vehicle: vehicleId,
      driver: driverId || null,
      rating,
      comment,
      status: 'pending'
    });

    res.status(201).json(review);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get reviews for a vehicle
// @route   GET /api/reviews/vehicle/:vehicleId
const getVehicleReviews = async (req, res) => {
  try {
    const reviews = await Review.find({ vehicle: req.params.vehicleId, status: 'approved' })
      .populate('customer', 'fullName profilePic')
      .sort({ createdAt: -1 });
    res.json(reviews);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get my reviews
// @route   GET /api/reviews/my
const getMyReviews = async (req, res) => {
  try {
    const reviews = await Review.find({ customer: req.user._id })
      .populate('vehicle', 'vehicleTitle vehicleBrand')
      .sort({ createdAt: -1 });
    res.json(reviews);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get driver's reviews
// @route   GET /api/reviews/driver
const getDriverReviews = async (req, res) => {
  try {
    const reviews = await Review.find({ driver: req.user._id })
      .populate('customer', 'fullName profilePic')
      .populate('vehicle', 'vehicleTitle')
      .sort({ createdAt: -1 });
    res.json(reviews);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all reviews (admin)
// @route   GET /api/reviews
const getAllReviews = async (req, res) => {
  try {
    const reviews = await Review.find()
      .populate('customer', 'fullName email')
      .populate('vehicle', 'vehicleTitle')
      .sort({ createdAt: -1 });
    res.json(reviews);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update review status (admin)
// @route   PUT /api/reviews/:id/status
const updateReviewStatus = async (req, res) => {
  try {
    const review = await Review.findById(req.params.id);
    if (!review) {
      return res.status(404).json({ message: 'Review not found' });
    }
    review.status = req.body.status;
    await review.save();
    res.json(review);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { createReview, getVehicleReviews, getMyReviews, getDriverReviews, getAllReviews, updateReviewStatus };
