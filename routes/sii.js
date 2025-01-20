const express = require('express');
const router = express.Router();
const siiController = require('../controllers/siiController')
const isAuthenticated = require('../middlewares/authMiddleware')
const authorizeRoles = require('../middlewares/authorizeRoles');

router.get('/dashboard', isAuthenticated, siiController.getDashboard)
router.post('/monitorManifestAjax', isAuthenticated, siiController.monitorManifestAjax)


router.get('/runManifest', isAuthenticated, siiController.runManifest)
router.post('/cekManifest', isAuthenticated, siiController.cekManifest)
router.get('/manifestOngoing', isAuthenticated, siiController.manifestOngoing)
router.post('/prosesManifest', isAuthenticated, siiController.prosesManifest)
router.post('/submitTutupManifest', isAuthenticated, siiController.submitTutupManifest)
router.get('/manifestCancel', isAuthenticated, siiController.manifestCancel)
router.get('/manifestTable', isAuthenticated, siiController.manifestTable)
router.post('/cekPartManifest', isAuthenticated, siiController.cekPartManifest)
router.post('/cekSiiManifest', isAuthenticated, siiController.cekSiiManifest)
router.post('/cekSalahScan', isAuthenticated, siiController.cekSalahScan)
router.post('/submitSalahScan', isAuthenticated, siiController.submitSalahScan)


router.get('/listAlarm', isAuthenticated, siiController.listAlarm)
router.post('/setAlarm', isAuthenticated, siiController.setAlarm)
router.post('/cekModule', isAuthenticated, siiController.cekModule)
router.post('/testModule', isAuthenticated, siiController.testModule)
router.post('/triggerAlarm', isAuthenticated, siiController.triggerAlarm)

router.get('/dataManifest', isAuthenticated, siiController.dataManifest)
router.post('/dataManifest', isAuthenticated, siiController.dataManifestAjax)
router.get('/manifestInput', isAuthenticated, siiController.dataManifestInput)
router.post('/manifestInput', isAuthenticated, siiController.dataManifestSubmit)
 

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
