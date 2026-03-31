const express = require('express');
const router = express.Router();
const { createPromotion, getPromotions, getActivePromotions, validatePromoCode, updatePromotion, deletePromotion } = require('../controllers/promotionController');
const { protect, authorize } = require('../middleware/auth');

router.get('/', protect, authorize('admin'), getPromotions);
router.get('/active', getActivePromotions);
router.post('/validate', protect, validatePromoCode);
router.post('/', protect, authorize('admin'), createPromotion);
router.put('/:id', protect, authorize('admin'), updatePromotion);
router.delete('/:id', protect, authorize('admin'), deletePromotion);

module.exports = router;
