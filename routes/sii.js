const express = require('express');
const router = express.Router();
const siiController = require('../controllers/siiController')
const isAuthenticated = require('../middlewares/authMiddleware')
const authorizeRoles = require('../middlewares/authorizeRoles');

router.get('/dashboard', isAuthenticated, siiController.getDashboard)


router.get('/sampah', isAuthenticated, siiController.sampah)
router.post('/sampah', isAuthenticated, siiController.sampahAjax)

// router.get('/shiftSet', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSet)
// router.post('/shiftSet', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetAjax)
// router.post('/shiftSetAdd', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetAdd)
// router.post('/shiftSetRead', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetRead)
// router.post('/shiftSetEdit', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetEdit)
// router.post('/shiftSetDelete', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetDelete)

module.exports = router;
