const express = require('express');
const router = express.Router();
const prodController = require('../controllers/prodController')
const isAuthenticated = require('../middlewares/authMiddleware')
const authorizeRoles = require('../middlewares/authorizeRoles');

router.get('/shiftSet', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSet)
router.post('/shiftSet', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetAjax)
router.post('/shiftSetAdd', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetAdd)
router.post('/shiftSetRead', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetRead)
router.post('/shiftSetEdit', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetEdit)
router.post('/shiftSetDelete', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.shiftSetDelete)


router.get('/idealCT', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.idealCT)
router.post('/idealCT', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.idealCTAjax)
router.post('/idealCTAdd', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.idealCTAdd)
router.post('/idealCTRead', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.idealCTRead)
router.post('/idealCTEdit', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.idealCTEdit)
router.post('/idealCTDelete', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.idealCTDelete)


router.get('/baseline', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.baseline)
router.post('/baseline', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.baselineAjax)
router.post('/baselineAdd', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.baselineAdd)
router.post('/baselineRead', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.baselineRead)
router.post('/baselineEdit', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.baselineEdit)
router.post('/baselineDelete', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.baselineDelete)


router.get('/rejectCategory', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.rejectCategory)
router.post('/rejectCategory', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.rejectCategoryAjax)
router.post('/rejectCategoryAdd', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.rejectCategoryAdd)
router.post('/rejectCategoryRead', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.rejectCategoryRead)
router.post('/rejectCategoryEdit', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.rejectCategoryEdit)
router.post('/rejectCategoryDelete', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.rejectCategoryDelete)



router.get('/downtimeCategory', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.downtimeCategory)
router.post('/downtimeCategory', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.downtimeCategoryAjax)
router.post('/downtimeCategoryAdd', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.downtimeCategoryAdd)
router.post('/downtimeCategoryRead', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.downtimeCategoryRead)
router.post('/downtimeCategoryEdit', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.downtimeCategoryEdit)
router.post('/downtimeCategoryDelete', isAuthenticated, authorizeRoles('Admin', 'Manager'), prodController.downtimeCategoryDelete)


router.get('/chart', isAuthenticated, prodController.oeeChart)


router.get('/operatorPage', isAuthenticated, prodController.operatorPage)
router.get('/machineDashboard', isAuthenticated, prodController.machineDashboard)
router.post('/rejectOperator', isAuthenticated, prodController.rejectOperatorAjax)
router.post('/rejectOperatorRead', isAuthenticated, prodController.rejectOperatorRead)
router.post('/rejectOperatorEdit', isAuthenticated, prodController.rejectOperatorEdit)
router.post('/rejectAdd', isAuthenticated, prodController.rejectAdd)

router.post('/downtimeOperator', isAuthenticated, prodController.downtimeOperatorAjax)
router.post('/downtimeOperatorRead', isAuthenticated, prodController.downtimeOperatorRead)
router.post('/downtimeOperatorEdit', isAuthenticated, prodController.downtimeOperatorEdit)

router.post('/alarmOperator', isAuthenticated, prodController.alarmOperatorAjax)
router.post('/alarmOperatorRead', isAuthenticated, prodController.alarmOperatorRead)
router.post('/alarmOperatorEdit', isAuthenticated, prodController.alarmOperatorEdit)


router.get('/rejectParetoChart', isAuthenticated, prodController.rejectParetoChart)
router.get('/rejectPareto', isAuthenticated, prodController.rejectPareto)
router.post('/rejectParetoAjax', isAuthenticated, prodController.rejectParetoAjax)

router.get('/downtimeParetoChart', isAuthenticated, prodController.downtimeParetoChart)
router.get('/downtimePareto', isAuthenticated, prodController.downtimePareto)
router.post('/downtimeParetoAjax', isAuthenticated, prodController.downtimeParetoAjax)


router.get('/utilizationReportChart', isAuthenticated, prodController.utilizationReportChart)
router.get('/utilizationReport', isAuthenticated, prodController.utilizationReport)
router.post('/utilizationReportAjax', isAuthenticated, prodController.utilizationReportAjax)

router.get('/oeeReportChart', isAuthenticated, prodController.oeeReportChart)
router.get('/oeeReport', isAuthenticated, prodController.oeeReport)
router.post('/oeeReportAjax', isAuthenticated, prodController.oeeReportAjax)

router.get('/partReportChart', isAuthenticated, prodController.partReportChart)
router.get('/partReport', isAuthenticated, prodController.partReport)
router.post('/partReportAjax', isAuthenticated, prodController.partReportAjax)

router.get('/setupReportChart', isAuthenticated, prodController.setupReportChart)
router.get('/setupReport', isAuthenticated, prodController.setupReport)
router.post('/setupReportAjax', isAuthenticated, prodController.setupReportAjax)

router.post('/alarmReportDetail', isAuthenticated, prodController.alarmReportDetail)
router.get('/alarmReport', isAuthenticated, prodController.alarmReport)
router.post('/alarmReportAjax', isAuthenticated, prodController.alarmReportAjax)

module.exports = router;
