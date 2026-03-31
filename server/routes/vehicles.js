const express = require('express');
const router = express.Router();
const { getVehicles, getVehicle, createVehicle, updateVehicle, deleteVehicle, getMyVehicles } = require('../controllers/vehicleController');
const { protect } = require('../middleware/auth');
const upload = require('../middleware/upload');

const vehicleUpload = (req, res, next) => {
  req.uploadDir = 'vehicles';
  next();
};

const imgFields = upload.fields([
  { name: 'img1', maxCount: 1 },
  { name: 'img2', maxCount: 1 },
  { name: 'img3', maxCount: 1 },
  { name: 'img4', maxCount: 1 }
]);

router.get('/', getVehicles);
router.get('/my', protect, getMyVehicles);
router.get('/:id', getVehicle);
router.post('/', protect, vehicleUpload, imgFields, createVehicle);
router.put('/:id', protect, vehicleUpload, imgFields, updateVehicle);
router.delete('/:id', protect, deleteVehicle);

module.exports = router;
