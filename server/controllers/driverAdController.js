const DriverAd = require('../models/DriverAd');
const { hasAssignedRole, isRoleOperational } = require('../utils/roleAccess');

// @desc    Create driver ad
// @route   POST /api/driver-ads
const createDriverAd = async (req, res) => {
  try {
    if (!isRoleOperational(req.user, 'driver')) {
      return res.status(403).json({ message: 'Approved and active driver access is required to publish ads' });
    }

    const adData = { ...req.body, driver: req.user._id };
    const driverAd = await DriverAd.create(adData);
    res.status(201).json(driverAd);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get my driver ads
// @route   GET /api/driver-ads/my
const getMyDriverAds = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'driver') && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Driver role is not assigned to your account' });
    }

    const ads = await DriverAd.find({ driver: req.user._id }).sort({ createdAt: -1 });
    res.json(ads);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all active driver ads (public)
// @route   GET /api/driver-ads
const getDriverAds = async (req, res) => {
  try {
    const ads = await DriverAd.find({ advertisementStatus: 'active', availabilityStatus: 'available' })
      .populate('driver', 'fullName email phone city profilePic bio driverProfile')
      .sort({ createdAt: -1 });
    res.json(ads);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get single driver ad
// @route   GET /api/driver-ads/:id
const getDriverAd = async (req, res) => {
  try {
    const ad = await DriverAd.findById(req.params.id)
      .populate('driver', 'fullName email phone city profilePic bio driverProfile');
    if (!ad) {
      return res.status(404).json({ message: 'Driver ad not found' });
    }
    res.json(ad);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update driver ad
// @route   PUT /api/driver-ads/:id
const updateDriverAd = async (req, res) => {
  try {
    const ad = await DriverAd.findById(req.params.id);
    if (!ad) {
      return res.status(404).json({ message: 'Driver ad not found' });
    }

    const isAdmin = req.user.role === 'admin';
    const isOwner = ad.driver.toString() === req.user._id.toString();

    if (!isAdmin && !isOwner) {
      return res.status(403).json({ message: 'Not authorized' });
    }

    if (!isAdmin && !isRoleOperational(req.user, 'driver')) {
      return res.status(403).json({ message: 'Approved and active driver access is required to manage ads' });
    }

    const updatedAd = await DriverAd.findByIdAndUpdate(req.params.id, req.body, { new: true });
    res.json(updatedAd);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Delete driver ad
// @route   DELETE /api/driver-ads/:id
const deleteDriverAd = async (req, res) => {
  try {
    const ad = await DriverAd.findById(req.params.id);
    if (!ad) {
      return res.status(404).json({ message: 'Driver ad not found' });
    }

    const isAdmin = req.user.role === 'admin';
    const isOwner = ad.driver.toString() === req.user._id.toString();

    if (!isAdmin && !isOwner) {
      return res.status(403).json({ message: 'Not authorized' });
    }

    if (!isAdmin && !isRoleOperational(req.user, 'driver')) {
      return res.status(403).json({ message: 'Approved and active driver access is required to manage ads' });
    }

    await DriverAd.findByIdAndDelete(req.params.id);
    res.json({ message: 'Driver ad removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { createDriverAd, getMyDriverAds, getDriverAds, getDriverAd, updateDriverAd, deleteDriverAd };
