const Promotion = require('../models/Promotion');

// @desc    Create promotion (admin)
// @route   POST /api/promotions
const createPromotion = async (req, res) => {
  try {
    const promotion = await Promotion.create(req.body);
    res.status(201).json(promotion);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all promotions
// @route   GET /api/promotions
const getPromotions = async (req, res) => {
  try {
    const promotions = await Promotion.find().sort({ createdAt: -1 });
    res.json(promotions);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get active promotions (public)
// @route   GET /api/promotions/active
const getActivePromotions = async (req, res) => {
  try {
    const now = new Date();
    const promotions = await Promotion.find({
      status: 'active',
      validFrom: { $lte: now },
      validTo: { $gte: now }
    });
    res.json(promotions);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Validate promo code
// @route   POST /api/promotions/validate
const validatePromoCode = async (req, res) => {
  try {
    const { code, bookingAmount } = req.body;

    const promotion = await Promotion.findOne({ code, status: 'active' });
    if (!promotion) {
      return res.status(404).json({ message: 'Invalid promo code' });
    }

    const now = new Date();
    if (promotion.validFrom > now || promotion.validTo < now) {
      return res.status(400).json({ message: 'Promo code has expired' });
    }

    if (promotion.usageLimit && promotion.usageCount >= promotion.usageLimit) {
      return res.status(400).json({ message: 'Promo code usage limit reached' });
    }

    if (bookingAmount < promotion.minimumBookingAmount) {
      return res.status(400).json({ message: `Minimum booking amount is Rs.${promotion.minimumBookingAmount}` });
    }

    let discount = 0;
    if (promotion.discountType === 'percentage') {
      discount = (bookingAmount * promotion.discountValue) / 100;
    } else {
      discount = promotion.discountValue;
    }

    res.json({
      valid: true,
      promotionId: promotion._id,
      discountType: promotion.discountType,
      discountValue: promotion.discountValue,
      discountAmount: discount,
      finalAmount: bookingAmount - discount
    });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update promotion (admin)
// @route   PUT /api/promotions/:id
const updatePromotion = async (req, res) => {
  try {
    const promotion = await Promotion.findByIdAndUpdate(req.params.id, req.body, { new: true });
    if (!promotion) {
      return res.status(404).json({ message: 'Promotion not found' });
    }
    res.json(promotion);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Delete promotion (admin)
// @route   DELETE /api/promotions/:id
const deletePromotion = async (req, res) => {
  try {
    const promotion = await Promotion.findByIdAndDelete(req.params.id);
    if (!promotion) {
      return res.status(404).json({ message: 'Promotion not found' });
    }
    res.json({ message: 'Promotion removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { createPromotion, getPromotions, getActivePromotions, validatePromoCode, updatePromotion, deletePromotion };
