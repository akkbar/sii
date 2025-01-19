const express = require('express');
const router = express.Router();
const siiController = require('../controllers/siiController')
const isAuthenticated = require('../middlewares/authMiddleware')
const authorizeRoles = require('../middlewares/authorizeRoles');

router.get('/dashboard', isAuthenticated, siiController.getDashboard)


router.get('/dataManifest', isAuthenticated, siiController.dataManifest)
router.post('/dataManifest', isAuthenticated, siiController.dataManifestAjax)

router.get('/logDataManifest', isAuthenticated, siiController.logDataManifest)
router.post('/logDataManifest', isAuthenticated, siiController.logDataManifestAjax)

router.get('/logScan', isAuthenticated, siiController.logScan)
router.post('/logScan', isAuthenticated, siiController.logScanAjax)

router.get('/dataKanban', isAuthenticated, siiController.dataKanban)
router.post('/dataKanban', isAuthenticated, siiController.dataKanbanAjax)
router.post('/tambahKanban', isAuthenticated, authorizeRoles('Admin', 'Manager'), siiController.tambahKanban)
router.post('/cekPartName', isAuthenticated, authorizeRoles('Admin', 'Manager'), siiController.cekPartName)
router.post('/updateDataKanban', isAuthenticated, authorizeRoles('Admin', 'Manager'), siiController.updateDataKanban)

router.get('/manifestHalt', isAuthenticated, siiController.manifestHalt)
router.post('/manifestHalt', isAuthenticated, siiController.manifestHaltAjax)

router.get('/haltKey', isAuthenticated, siiController.haltKey)
router.post('/haltKey', isAuthenticated, siiController.haltKeyAjax)
router.post('/addAdmin', isAuthenticated, authorizeRoles('Admin', 'Manager'), siiController.addAdmin)
router.post('/updateAdmin', isAuthenticated, authorizeRoles('Admin', 'Manager'), siiController.updateAdmin)

router.get('/sampah', isAuthenticated, siiController.sampah)
router.post('/sampah', isAuthenticated, siiController.sampahAjax)
router.post('/trash_manifest', isAuthenticated, authorizeRoles('Admin', 'Manager'), siiController.trashManifest)

//======================================================================================================
//======================================================================================================

router.get('/logManifest/:encryptID', isAuthenticated, siiController.logManifest);
router.post('/logManifest/:manifest', isAuthenticated, siiController.logManifestAjax);



module.exports = router;
