const express = require('express');
const router = express.Router();
const { createPayment, getMyPayments, getAllPayments, getDriverEarnings } = require('../controllers/paymentController');
const { protect, authorize } = require('../middleware/auth');

router.get('/', protect, authorize('admin'), getAllPayments);
router.get('/my', protect, getMyPayments);
router.get('/earnings', protect, authorize('driver', 'staff'), getDriverEarnings);
router.post('/', protect, createPayment);

module.exports = router;
