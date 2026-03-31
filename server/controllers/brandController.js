const Brand = require('../models/Brand');

// @desc    Get all brands
// @route   GET /api/brands
const getBrands = async (req, res) => {
  try {
    const brands = await Brand.find({ brandStatus: 1 }).sort({ brandName: 1 });
    res.json(brands);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Create brand (admin)
// @route   POST /api/brands
const createBrand = async (req, res) => {
  try {
    const brand = await Brand.create(req.body);
    res.status(201).json(brand);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update brand (admin)
// @route   PUT /api/brands/:id
const updateBrand = async (req, res) => {
  try {
    const brand = await Brand.findByIdAndUpdate(req.params.id, req.body, { new: true });
    if (!brand) {
      return res.status(404).json({ message: 'Brand not found' });
    }
    res.json(brand);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Delete brand (admin)
// @route   DELETE /api/brands/:id
const deleteBrand = async (req, res) => {
  try {
    const brand = await Brand.findByIdAndDelete(req.params.id);
    if (!brand) {
      return res.status(404).json({ message: 'Brand not found' });
    }
    res.json({ message: 'Brand removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { getBrands, createBrand, updateBrand, deleteBrand };
