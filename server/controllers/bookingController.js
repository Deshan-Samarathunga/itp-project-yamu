const Booking = require('../models/Booking');
const Vehicle = require('../models/Vehicle');
const DriverAd = require('../models/DriverAd');
const { generateBookingNumber, calcTotalDays } = require('../utils/helpers');
const { isCustomerOperational, isRoleOperational } = require('../utils/roleAccess');

// @desc    Create vehicle booking
// @route   POST /api/bookings
const createBooking = async (req, res) => {
  try {
    if (!isCustomerOperational(req.user)) {
      return res.status(403).json({ message: 'Only active and verified customers can book services' });
    }

    const { vehicleId, startDate, endDate } = req.body;

    const vehicle = await Vehicle.findById(vehicleId);
    if (!vehicle) {
      return res.status(404).json({ message: 'Vehicle not found' });
    }

    if (vehicle.listingStatus !== 'approved') {
      return res.status(400).json({ message: 'This vehicle listing is not approved yet' });
    }

    if (vehicle.availabilityStatus !== 'available') {
      return res.status(400).json({ message: 'This vehicle is currently unavailable' });
    }

    // Check for booking overlap
    const overlap = await Booking.findOne({
      vehicle: vehicleId,
      bookingStatus: { $in: ['pending', 'confirmed'] },
      $nor: [
        { endDate: { $lt: startDate } },
        { startDate: { $gt: endDate } }
      ]
    });

    if (overlap) {
      return res.status(400).json({ message: 'This vehicle is already booked for the selected dates' });
    }

    const totalDays = calcTotalDays(startDate, endDate);
    if (totalDays <= 0) {
      return res.status(400).json({ message: 'Please choose a valid booking date range' });
    }

    const totalPrice = vehicle.price * totalDays;

    const booking = await Booking.create({
      bookingNo: generateBookingNumber(),
      customer: req.user._id,
      driver: vehicle.owner,
      vehicle: vehicleId,
      startDate,
      endDate,
      total: totalPrice,
      finalAmount: totalPrice,
      bookingStatus: 'pending',
      paymentStatus: 'pending'
    });

    // Update vehicle availability
    vehicle.availabilityStatus = 'booked';
    await vehicle.save();

    res.status(201).json(booking);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Create driver service booking
// @route   POST /api/bookings/driver-service
const createDriverBooking = async (req, res) => {
  try {
    if (!isCustomerOperational(req.user)) {
      return res.status(403).json({ message: 'Only active and verified customers can book services' });
    }

    const { driverAdId, startDate, endDate } = req.body;

    const driverAd = await DriverAd.findById(driverAdId).populate('driver', 'fullName');
    if (!driverAd) {
      return res.status(404).json({ message: 'Driver service not found' });
    }

    if (driverAd.driver._id.toString() === req.user._id.toString()) {
      return res.status(400).json({ message: 'You cannot book your own driver service' });
    }

    if (!isRoleOperational(driverAd.driver, 'driver')) {
      return res.status(400).json({ message: 'This driver role is not approved for bookings yet' });
    }

    // Check overlap for driver
    const overlap = await Booking.findOne({
      driver: driverAd.driver._id,
      vehicle: null,
      bookingStatus: { $in: ['pending', 'confirmed'] },
      $nor: [
        { endDate: { $lt: startDate } },
        { startDate: { $gt: endDate } }
      ]
    });

    if (overlap) {
      return res.status(400).json({ message: 'This driver is already booked for the selected dates' });
    }

    const totalDays = calcTotalDays(startDate, endDate);
    if (totalDays <= 0) {
      return res.status(400).json({ message: 'Please choose a valid booking date range' });
    }

    const totalPrice = driverAd.dailyRate * totalDays;

    const booking = await Booking.create({
      bookingNo: generateBookingNumber(),
      customer: req.user._id,
      driver: driverAd.driver._id,
      vehicle: null,
      startDate,
      endDate,
      total: totalPrice,
      finalAmount: totalPrice,
      bookingStatus: 'pending',
      paymentStatus: 'pending'
    });

    res.status(201).json(booking);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get user's bookings (customer)
// @route   GET /api/bookings/my
const getMyBookings = async (req, res) => {
  try {
    if (!isCustomerOperational(req.user)) {
      return res.status(403).json({ message: 'Customer access is not available for this account' });
    }

    const bookings = await Booking.find({ customer: req.user._id })
      .populate('vehicle', 'vehicleTitle vehicleBrand price images location')
      .populate('driver', 'fullName email phone profilePic')
      .sort({ createdAt: -1 });

    res.json(bookings);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get bookings for driver/staff
// @route   GET /api/bookings/provider
const getProviderBookings = async (req, res) => {
  try {
    if (!['driver', 'staff'].some((role) => isRoleOperational(req.user, role))) {
      return res.status(403).json({ message: 'Provider access requires an approved and active provider role' });
    }

    const bookings = await Booking.find({ driver: req.user._id })
      .populate('vehicle', 'vehicleTitle vehicleBrand price images')
      .populate('customer', 'fullName email phone profilePic')
      .sort({ createdAt: -1 });

    res.json(bookings);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get booking by ID
// @route   GET /api/bookings/:id
const getBooking = async (req, res) => {
  try {
    const booking = await Booking.findById(req.params.id)
      .populate('vehicle')
      .populate('customer', 'fullName email phone city profilePic')
      .populate('driver', 'fullName email phone city profilePic');

    if (!booking) {
      return res.status(404).json({ message: 'Booking not found' });
    }

    const isAdmin = req.user.role === 'admin';
    const isCustomerOwner = booking.customer && booking.customer._id.toString() === req.user._id.toString();
    const isProviderOwner = booking.driver && booking.driver._id.toString() === req.user._id.toString();

    if (!isAdmin && !isCustomerOwner && !isProviderOwner) {
      return res.status(403).json({ message: 'Not authorized to view this booking' });
    }

    res.json(booking);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update booking status
// @route   PUT /api/bookings/:id/status
const updateBookingStatus = async (req, res) => {
  try {
    const { status } = req.body;
    const booking = await Booking.findById(req.params.id);

    if (!booking) {
      return res.status(404).json({ message: 'Booking not found' });
    }

    const isAdmin = req.user.role === 'admin';
    const isProviderOwner = booking.driver && booking.driver.toString() === req.user._id.toString();
    const isCustomerOwner = booking.customer && booking.customer.toString() === req.user._id.toString();

    if (!isAdmin && !isProviderOwner && !isCustomerOwner) {
      return res.status(403).json({ message: 'Not authorized to update this booking' });
    }

    if (isProviderOwner && !['driver', 'staff'].some((role) => isRoleOperational(req.user, role))) {
      return res.status(403).json({ message: 'Provider access requires an approved and active provider role' });
    }

    if (isCustomerOwner && !isCustomerOperational(req.user)) {
      return res.status(403).json({ message: 'Customer access is not available for this account' });
    }

    if (isCustomerOwner && !['cancelled'].includes(status)) {
      return res.status(403).json({ message: 'Customers can only cancel their own bookings' });
    }

    booking.bookingStatus = status;

    if (status === 'cancelled' || status === 'rejected') {
      booking.cancelledAt = new Date();

      // Release vehicle
      if (booking.vehicle) {
        const vehicle = await Vehicle.findById(booking.vehicle);
        if (vehicle) {
          // Check if other bookings exist
          const otherBookings = await Booking.countDocuments({
            vehicle: booking.vehicle,
            _id: { $ne: booking._id },
            bookingStatus: { $in: ['pending', 'confirmed'] }
          });
          vehicle.availabilityStatus = otherBookings > 0 ? 'booked' : 'available';
          await vehicle.save();
        }
      }
    }

    if (status === 'completed') {
      booking.completedAt = new Date();

      // Release vehicle
      if (booking.vehicle) {
        const vehicle = await Vehicle.findById(booking.vehicle);
        if (vehicle) {
          const otherBookings = await Booking.countDocuments({
            vehicle: booking.vehicle,
            _id: { $ne: booking._id },
            bookingStatus: { $in: ['pending', 'confirmed'] }
          });
          vehicle.availabilityStatus = otherBookings > 0 ? 'booked' : 'available';
          await vehicle.save();
        }
      }
    }

    await booking.save();

    res.json(booking);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get all bookings (admin)
// @route   GET /api/bookings
const getAllBookings = async (req, res) => {
  try {
    const bookings = await Booking.find()
      .populate('vehicle', 'vehicleTitle vehicleBrand images')
      .populate('customer', 'fullName email')
      .populate('driver', 'fullName email')
      .sort({ createdAt: -1 });

    res.json(bookings);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = {
  createBooking, createDriverBooking, getMyBookings, getProviderBookings,
  getBooking, updateBookingStatus, getAllBookings
};
