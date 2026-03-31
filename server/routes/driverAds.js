const express = require('express');
const router = express.Router();
const { createDriverAd, getMyDriverAds, getDriverAds, getDriverAd, updateDriverAd, deleteDriverAd } = require('../controllers/driverAdController');
const { protect } = require('../middleware/auth');

router.get('/', getDriverAds);
router.get('/my', protect, getMyDriverAds);
router.get('/:id', getDriverAd);
router.post('/', protect, createDriverAd);
router.put('/:id', protect, updateDriverAd);
router.delete('/:id', protect, deleteDriverAd);

module.exports = router;
