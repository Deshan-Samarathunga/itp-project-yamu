const express = require('express');
const router = express.Router();
const { createReview, getVehicleReviews, getMyReviews, getDriverReviews, getAllReviews, updateReviewStatus } = require('../controllers/reviewController');
const { protect, authorize } = require('../middleware/auth');

router.get('/', protect, authorize('admin'), getAllReviews);
router.get('/my', protect, getMyReviews);
router.get('/driver', protect, authorize('driver'), getDriverReviews);
router.get('/vehicle/:vehicleId', getVehicleReviews);
router.post('/', protect, authorize('customer'), createReview);
router.put('/:id/status', protect, authorize('admin'), updateReviewStatus);

module.exports = router;
