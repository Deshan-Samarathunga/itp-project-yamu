const express = require('express');
const router = express.Router();
const {
  createBooking, createDriverBooking, getMyBookings, getProviderBookings,
  getBooking, updateBookingStatus, getAllBookings
} = require('../controllers/bookingController');
const { protect, authorize } = require('../middleware/auth');

router.get('/', protect, authorize('admin'), getAllBookings);
router.get('/my', protect, getMyBookings);
router.get('/provider', protect, authorize('staff', 'driver'), getProviderBookings);
router.get('/:id', protect, getBooking);
router.post('/', protect, authorize('customer'), createBooking);
router.post('/driver-service', protect, authorize('customer'), createDriverBooking);
router.put('/:id/status', protect, updateBookingStatus);

module.exports = router;
