const express = require('express');
const router = express.Router();
const {
  getDashboardStats, getAllUsers, getUserDetails, updateUser, updateUserStatus,
  updateUserVerification, deleteUser, getProviderApplications, reviewProviderApplication,
  getAllComplaints, updateComplaint, getAllVehicles
} = require('../controllers/adminController');
const { protect, authorize } = require('../middleware/auth');

router.use(protect, authorize('admin'));

router.get('/dashboard', getDashboardStats);
router.get('/users', getAllUsers);
router.get('/users/:id', getUserDetails);
router.put('/users/:id', updateUser);
router.put('/users/:id/status', updateUserStatus);
router.put('/users/:id/verify', updateUserVerification);
router.delete('/users/:id', deleteUser);
router.get('/provider-applications', getProviderApplications);
router.put('/provider-applications/:id', reviewProviderApplication);
router.get('/complaints', getAllComplaints);
router.put('/complaints/:id', updateComplaint);
router.get('/vehicles', getAllVehicles);

module.exports = router;
