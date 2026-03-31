const express = require('express');
const router = express.Router();
const {
  getProfile,
  updateProfile,
  updatePassword,
  getCustomerProfile,
  updateCustomerProfile,
  getDriverProfile,
  updateDriverProfile,
  getStaffProfile,
  updateStaffProfile,
  getAdminProfile,
  updateAdminProfile,
  getRoleOptions,
  applyForRole,
  getMyRoleApplications,
  getUserById
} = require('../controllers/userController');
const { protect } = require('../middleware/auth');
const upload = require('../middleware/upload');

router.get('/profile', protect, getProfile);
router.put('/profile', protect, (req, res, next) => { req.uploadDir = 'profiles'; next(); }, upload.single('profilePic'), updateProfile);
router.put('/password', protect, updatePassword);
router.get('/customer-profile', protect, getCustomerProfile);
router.put('/customer-profile', protect, updateCustomerProfile);
router.get('/driver-profile', protect, getDriverProfile);
router.put('/driver-profile', protect, updateDriverProfile);
router.get('/staff-profile', protect, getStaffProfile);
router.put('/staff-profile', protect, updateStaffProfile);
router.get('/admin-profile', protect, getAdminProfile);
router.put('/admin-profile', protect, updateAdminProfile);
router.get('/roles/options', protect, getRoleOptions);
router.get('/roles/applications', protect, getMyRoleApplications);
router.post('/roles/apply', protect, applyForRole);
router.get('/:id', getUserById);

module.exports = router;
