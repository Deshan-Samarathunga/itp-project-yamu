const Vehicle = require('../models/Vehicle');
const { hasAssignedRole, isRoleOperational } = require('../utils/roleAccess');

// @desc    Get all vehicles (public, approved only)
// @route   GET /api/vehicles
const getVehicles = async (req, res) => {
  try {
    const { brand, transmission, fuelType, minPrice, maxPrice, location, search } = req.query;
    const filter = { listingStatus: 'approved' };

    if (brand) filter.vehicleBrand = brand;
    if (transmission) filter.transmission = transmission;
    if (fuelType) filter.fuelType = fuelType;
    if (location) filter.location = { $regex: location, $options: 'i' };
    if (minPrice || maxPrice) {
      filter.price = {};
      if (minPrice) filter.price.$gte = Number(minPrice);
      if (maxPrice) filter.price.$lte = Number(maxPrice);
    }
    if (search) {
      filter.$or = [
        { vehicleTitle: { $regex: search, $options: 'i' } },
        { vehicleBrand: { $regex: search, $options: 'i' } },
        { vehicleDesc: { $regex: search, $options: 'i' } }
      ];
    }

    const vehicles = await Vehicle.find(filter)
      .populate('owner', 'fullName email phone city profilePic')
      .sort({ updatedAt: -1 });

    res.json(vehicles);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get single vehicle
// @route   GET /api/vehicles/:id
const getVehicle = async (req, res) => {
  try {
    const vehicle = await Vehicle.findById(req.params.id)
      .populate('owner', 'fullName email phone city profilePic staffProfile');

    if (!vehicle) {
      return res.status(404).json({ message: 'Vehicle not found' });
    }

    res.json(vehicle);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Create vehicle
// @route   POST /api/vehicles
const createVehicle = async (req, res) => {
  try {
    const isAdmin = req.user.role === 'admin';

    if (!isAdmin && !isRoleOperational(req.user, 'staff')) {
      return res.status(403).json({ message: 'Approved and active staff access is required to publish vehicle ads' });
    }

    const vehicleData = {
      owner: req.user._id,
      vehicleTitle: req.body.vehicleTitle,
      vehicleBrand: req.body.vehicleBrand,
      vehicleDesc: req.body.vehicleDesc || '',
      price: req.body.price,
      transmission: req.body.transmission,
      fuelType: req.body.fuelType,
      year: req.body.year,
      engineCapacity: req.body.engineCapacity || '',
      capacity: req.body.capacity || 5,
      location: req.body.location,
      registrationNumber: req.body.registrationNumber,
      features: req.body.features || {},
      listingStatus: isAdmin ? 'approved' : 'pending',
      availabilityStatus: 'available',
      maintenanceStatus: req.body.maintenanceStatus || 'good'
    };

    // Handle image uploads
    if (req.files) {
      vehicleData.images = {
        img1: req.files.img1 ? req.files.img1[0].filename : '',
        img2: req.files.img2 ? req.files.img2[0].filename : '',
        img3: req.files.img3 ? req.files.img3[0].filename : '',
        img4: req.files.img4 ? req.files.img4[0].filename : ''
      };
    }

    if (isAdmin) {
      vehicleData.approvedBy = req.user._id;
      vehicleData.approvedAt = new Date();
    }

    const vehicle = await Vehicle.create(vehicleData);
    res.status(201).json(vehicle);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Update vehicle
// @route   PUT /api/vehicles/:id
const updateVehicle = async (req, res) => {
  try {
    const vehicle = await Vehicle.findById(req.params.id);

    if (!vehicle) {
      return res.status(404).json({ message: 'Vehicle not found' });
    }

    const isAdmin = req.user.role === 'admin';
    const isOwner = vehicle.owner.toString() === req.user._id.toString();

    if (!isAdmin && !isOwner) {
      return res.status(403).json({ message: 'Not authorized' });
    }

    if (!isAdmin && !isRoleOperational(req.user, 'staff')) {
      return res.status(403).json({ message: 'Approved and active staff access is required to manage vehicle ads' });
    }

    const updateFields = ['vehicleTitle', 'vehicleBrand', 'vehicleDesc', 'price', 'transmission',
      'fuelType', 'year', 'engineCapacity', 'capacity', 'location', 'registrationNumber',
      'maintenanceStatus', 'serviceDate', 'nextServiceDate', 'serviceNotes', 'serviceCost'];

    updateFields.forEach(field => {
      if (req.body[field] !== undefined) {
        vehicle[field] = req.body[field];
      }
    });

    if (req.body.features) {
      vehicle.features = { ...vehicle.features.toObject(), ...req.body.features };
    }

    // Handle image uploads
    if (req.files) {
      if (req.files.img1) vehicle.images.img1 = req.files.img1[0].filename;
      if (req.files.img2) vehicle.images.img2 = req.files.img2[0].filename;
      if (req.files.img3) vehicle.images.img3 = req.files.img3[0].filename;
      if (req.files.img4) vehicle.images.img4 = req.files.img4[0].filename;
    }

    // Admin can change listing status
    if (isAdmin && req.body.listingStatus) {
      vehicle.listingStatus = req.body.listingStatus;
      if (req.body.listingStatus === 'approved') {
        vehicle.approvedBy = req.user._id;
        vehicle.approvedAt = new Date();
      }
    }

    if (req.body.availabilityStatus) {
      vehicle.availabilityStatus = req.body.availabilityStatus;
    }

    // Sync availability based on maintenance
    if (['under maintenance', 'unavailable'].includes(vehicle.maintenanceStatus)) {
      vehicle.availabilityStatus = 'unavailable';
    }

    const updatedVehicle = await vehicle.save();
    res.json(updatedVehicle);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Delete vehicle
// @route   DELETE /api/vehicles/:id
const deleteVehicle = async (req, res) => {
  try {
    const vehicle = await Vehicle.findById(req.params.id);

    if (!vehicle) {
      return res.status(404).json({ message: 'Vehicle not found' });
    }

    const isAdmin = req.user.role === 'admin';
    const isOwner = vehicle.owner.toString() === req.user._id.toString();

    if (!isAdmin && !isOwner) {
      return res.status(403).json({ message: 'Not authorized' });
    }

    if (!isAdmin && !isRoleOperational(req.user, 'staff')) {
      return res.status(403).json({ message: 'Approved and active staff access is required to manage vehicle ads' });
    }

    await Vehicle.findByIdAndDelete(req.params.id);
    res.json({ message: 'Vehicle removed' });
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

// @desc    Get vehicles by owner (staff/driver)
// @route   GET /api/vehicles/my
const getMyVehicles = async (req, res) => {
  try {
    if (!hasAssignedRole(req.user, 'staff') && req.user.role !== 'admin') {
      return res.status(403).json({ message: 'Staff role is not assigned to your account' });
    }

    const vehicles = await Vehicle.find({ owner: req.user._id }).sort({ updatedAt: -1 });
    res.json(vehicles);
  } catch (error) {
    res.status(500).json({ message: error.message });
  }
};

module.exports = { getVehicles, getVehicle, createVehicle, updateVehicle, deleteVehicle, getMyVehicles };
