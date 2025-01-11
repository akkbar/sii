const siiModel = require('../models/siiModel')
const logError = require('../middlewares/errorlogger')
const moment = require('moment')

exports.getDashboard = async (req, res) => {
    const header = {pageTitle: 'Dashboard', user: req.session.user}

    const plantId = req.session.user.plantId;

    const openManifest = await siiModel.countOpenManifest(plantId);
    const allManifest = await siiModel.countAllManifest(plantId);
    const allUser = await siiModel.countAllUser(plantId);
    const allKanban = await siiModel.countAllKanban(plantId);

    // Simulate the `shiroki_auto_remove` logic if needed
    const x = await autoRemove(plantId);

    // Prepare data for rendering
    const data = {
        open_manifest: openManifest.count,
        all_manifest: allManifest.count,
        all_user: allUser.count,
        all_kanban: allKanban.count,
        x,
    };
    res.render('sii/dashboard', {header: header, data:data})
}
const autoRemove = async (plantId) => {
    try {
        const fiveDaysAgo = moment().subtract(5, 'days').format('YYYY-MM-DD HH:mm:ss');
        const tenDaysAgo = moment().subtract(10, 'days').format('YYYY-MM-DD HH:mm:ss');
        const threeDaysAgo = moment().subtract(3, 'days').format('YYYY-MM-DD HH:mm:ss');
    
        // Delete manifest older than 5 days
        await siiModel.deleteManifestOlderThan(fiveDaysAgo, plantId);
    
        // Delete scan records older than 10 days
        await siiModel.deleteScanOlderThan(tenDaysAgo, plantId);
    
        // Update manifest older than 3 days to set `isvalid` = 0
        await siiModel.editManifestOlderThan(
            { isvalid: 0 },
            threeDaysAgo,
            plantId
        );
  
        return true;
    } catch (error) {
        console.error('Error in autoRemove:', error);
        throw error;
    }
};

// function shiroki_monitor_manifest(){
//     $data['manifest_table'] = $this->shiroki_model->monitor_manifest($this->plant_id);
//     $this->load->view('shiroki/shiroki_monitor_manifest', $data);
// }
// function shiroki_monitor_manifest_ajax(){
//     $list = $this->shiroki_model->get_monman_data_dt($this->plant_id);
//     $data = array();
//     $class_row = array();
//     $no = $_POST['start'];
//     foreach ($list as $record){
//         $no++;
//         $row = array();
//         $row[] = $no;
//         $row[] = $record->manifest;
//         $row[] = round($record->prog, 1);
//         $row[] = '<div class="progress mb-3">
//                 <div class="progress-bar bg-success" role="progressbar" aria-valuenow="'.round($record->prog).'" aria-valuemin="0" aria-valuemax="100" style="width: '.round($record->prog, 1).'%">
//                     <span class="sr-only">'.round($record->prog, 1).'%</span>
//                 </div>
//             </div>';
//         $row[] = date('d-m-Y H:i', strtotime(substr($record->na7, 0, -4)));
//         $row[] = $record->dock_code;
//         $data[] = $row;
//     }
//     $output = array(
//         "draw" => $_POST['draw'],
//         "recordsTotal" => $this->shiroki_model->monman_data_count_all($this->plant_id),
//         "recordsFiltered" => $this->shiroki_model->monman_data_count_filtered($this->plant_id),
//         "data" => $data
//     );
//     echo json_encode($output);	
// }
//==========================================================================================================================
//==========================================================================================================================
//MONITOR MANIFEST
//==========================================================================================================================
//==========================================================================================================================
exports.monitorManifest = async (req, res) => {
    try {
        const plantId = req.user.plantId; // Assuming `plantId` is retrieved from the authenticated user/session

        // Fetch manifest data
        const manifestTable = await siiModel.monitorManifest(plantId);

        // Render the view with the retrieved data
        res.render('sii/monitorManifest', { manifest_table: manifestTable });
    } catch (error) {
        console.error('Error in monitorManifest:', error);
        res.status(500).send('Internal Server Error');
    }
}

exports.monitorManifestAjax = async (req, res) => {
    const filters = {
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'fullname',
        'user_role',
        'last_login',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'fullname'
        
        const data = await mainModel.userList(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await mainModel.userListFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await mainModel.userListCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.fullname,
                    record.user_role,
                    record.last_login,
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i> Edit</button>
                    ${
                        record.user_role !== 'Admin' 
                            ? `<button class="btn btn-sm btn-danger" onclick="delete_modal('${record.id}')">
                                   <i class="fa fa-trash-alt"></i> Delete
                               </button>`
                            : ''
                    }`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'userController/userListAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
//==========================================================================================================================
//==========================================================================================================================
//SHIFT SETTING
//==========================================================================================================================
//==========================================================================================================================
exports.shiftSet = (req, res) => {
    const header = {pageTitle: 'Manage Shift time', user: req.session.user}
    res.render('production/shift', {header: header})
};

exports.shiftSetAjax = async (req, res) => {
    const filters = {
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'shift1_start',
        'shift1_end',
        'shift2_start',
        'shift2_end',
        'shift3_start',
        'shift3_end',
        'isactive',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'id'
        
        const data = await cncModel.shiftTime(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.shiftTimeFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.shiftTimeCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.shift1_start,
                    record.shift1_end,
                    record.shift2_start,
                    record.shift2_end,
                    record.shift3_start,
                    record.shift3_end,
                    getActiveText(record.isactive),
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="delete_modal('${record.id}')"><i class="fa fa-trash-alt"></i> Delete</button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/shiftSetAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
};
function getActiveText(status) {
    switch (status) {
        case 1:
            return '<span class="badge badge-success"><i class="fa fa-check"></i></span>'
        default:
            return ''
    }
}
exports.shiftSetAdd = async (req, res) => {
    try {
        const shiftData = req.body
        const { error } = cncValidator.joiShiftData(shiftData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }

        const sanitizedData = cncValidator.sanitizeShiftData(shiftData)
        if (req.body.isactive == 1) {
            await cncModel.setShiftActive()
        }
        await cncModel.addShiftTime(sanitizedData)
        res.status(201).json({ message: 'Shift data added successfully' })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/shiftSetAdd' })
        res.status(500).json({ message: error.message })
    }
};

exports.shiftSetRead = async (req, res) => {
    try {
        const id = req.body.editId
        const shiftData = await cncModel.getShiftTime(id)
        res.status(201).json({ message: 'Shift time readed successfully', shiftData: shiftData })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/shiftSetRead' })
        res.status(500).json({ message: error.message })
    }
};

exports.shiftSetEdit = async (req, res) => {
    try {
        const { shift1_start, shift1_end, shift2_start, shift2_end, shift3_start, shift3_end, isactive } = req.body;
        const shiftData = {
            shift1_start: shift1_start,
            shift1_end: shift1_end,
            shift2_start: shift2_start,
            shift2_end: shift2_end,
            shift3_start: shift3_start,
            shift3_end: shift3_end,
            isactive: isactive
        }
        const { error } = cncValidator.joiShiftData(shiftData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeShiftData(shiftData)
        if (isactive == 1) {
            await cncModel.setShiftActive()
        }
        await cncModel.editShiftTime(req.body.editId, sanitizedData)
        res.status(201).json({ message: 'Shift time edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/shiftSetEdit' })
        res.status(500).json({ message: error.message })
    }
};
exports.shiftSetDelete = async (req, res) => {
    try {
        await cncModel.deleteShiftTime(req.body.id)
        res.status(201).json({ message: 'Shift time deleted successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/shiftSetDelete' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//==========================================================================================================================
//PRODUCT LIST
//==========================================================================================================================
//==========================================================================================================================
exports.idealCT = (req, res) => {
    const header = {pageTitle: 'Manage Ideal Cycletime', user: req.session.user}
    res.render('production/idealct', {header: header})
};

exports.idealCTAjax = async (req, res) => {
    const filters = {
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'product_name',
        'ideal_ct',
        'update_time',
        'note',
        'ideal_mode',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'product_name'
        
        const data = await cncModel.idealCT(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.idealCTFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.idealCTCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.product_name,
                    record.ideal_ct,
                    record.update_time ? new Date(record.update_time).toLocaleString() : '',
                    record.note,
                    getModeText(record.ideal_mode),
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="delete_modal('${record.id}')"><i class="fa fa-trash-alt"></i> Delete</button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/idealCTAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
};
function getModeText(status) {
    switch (status) {
        case 'Auto':
            return '<span class="badge badge-success"><i class="fa fa-cog fa-spin"></i> Auto</span>'
        default:
            return '<span class="badge badge-primary"><i class="fa fa-pencil-alt"></i> Manual</span>'
    }
}
exports.idealCTAdd = async (req, res) => {
    try {
        const idealCTData = req.body
        const { error } = cncValidator.joiProductData(idealCTData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }

        const sanitizedData = cncValidator.sanitizeProductData(idealCTData)
        const existingProduct = await cncModel.getProductByName(sanitizedData.product_name);
        if (existingProduct) {
            return res.status(400).json({ message: 'Product name already exists' });
        }
        await cncModel.addIdealCT(sanitizedData)
        res.status(201).json({ message: 'Product data added successfully' })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/idealCTAdd' })
        res.status(500).json({ message: error.message })
    }
};

exports.idealCTRead = async (req, res) => {
    try {
        const id = req.body.editId
        const idealCTData = await cncModel.getIdealCT(id)
        res.status(201).json({ message: 'Ideal CT readed successfully', idealCTData: idealCTData })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/idealCTRead' }) 
        res.status(500).json({ message: error.message })
    }
};

exports.idealCTEdit = async (req, res) => {
    try {
        const { product_name, ideal_mode, ideal_ct} = req.body;
        const idealCTData = {
            product_name: product_name,
            ideal_mode: ideal_mode,
            ideal_ct: ideal_ct === '' ? null : parseFloat(ideal_ct),
        }
        const { error } = cncValidator.joiProductData(idealCTData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeProductData(idealCTData)
        const existingProduct = await cncModel.getProductByName(sanitizedData.product_name);
        if (existingProduct) {
            if(existingProduct.id != req.body.editId){
                return res.status(400).json({ message: 'Product name already exists' });
            }
        }
        await cncModel.editIdealCT(req.body.editId, sanitizedData)
        res.status(201).json({ message: 'Ideal CT edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/idealCTEdit' })
        res.status(500).json({ message: error.message })
    }
};
exports.idealCTDelete = async (req, res) => {
    try {
        await cncModel.deleteIdealCT(req.body.id)
        res.status(201).json({ message: 'Ideal CT deleted successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/idealCTDelete' })
        res.status(500).json({ message: error.message })
    }
};
//==========================================================================================================================
//==========================================================================================================================
//BASELINE LIST
//==========================================================================================================================
//==========================================================================================================================
exports.baseline = (req, res) => {
    const header = {pageTitle: 'Manage Baseline', user: req.session.user}
    res.render('production/baseline', {header: header})
}

exports.baselineAjax = async (req, res) => {
    const filters = {
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'utilization',
        'oee',
        'ar',
        'pq',
        'qr',
        'isactive',
        null
    ]
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'oee'
        
        const data = await cncModel.baseline(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.baselineFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.baselineCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.utilization,
                    record.oee,
                    record.ar,
                    record.pr,
                    record.qr,
                    getActiveText(record.isactive),
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="delete_modal('${record.id}')"><i class="fa fa-trash-alt"></i> Delete</button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/baselineAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.baselineAdd = async (req, res) => {
    try {
        const { utilization, oee, ar, pr, qr, isactive} = req.body;
        const baselineData = {
            utilization: utilization === '' ? null : parseFloat(utilization),
            oee: oee === '' ? null : parseFloat(oee),
            ar: ar === '' ? null : parseFloat(ar),
            pr: pr === '' ? null : parseFloat(pr),
            qr: qr === '' ? null : parseFloat(qr),
            isactive: isactive,
        }
        const { error } = cncValidator.joiBaseline(baselineData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }

        const sanitizedData = cncValidator.sanitizeBaseline(baselineData)
        if (req.body.isactive == 1) {
            await cncModel.setBaselineActive()
        }
        await cncModel.addBaseline(sanitizedData)
        res.status(201).json({ message: 'Baseline added successfully' })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/baselineAdd' })
        res.status(500).json({ message: error.message })
    }
}

exports.baselineRead = async (req, res) => {
    try {
        const id = req.body.editId
        const baselineData = await cncModel.getBaseline(id)
        res.status(201).json({ message: 'Baseline readed successfully', baselineData: baselineData })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/baselineRead' })
        res.status(500).json({ message: error.message })
    }
}

exports.baselineEdit = async (req, res) => {
    try {
        const { utilization, oee, ar, pr, qr, isactive} = req.body;
        const baselineData = {
            utilization: utilization === '' ? null : parseFloat(utilization),
            oee: oee === '' ? null : parseFloat(oee),
            ar: ar === '' ? null : parseFloat(ar),
            pr: pr === '' ? null : parseFloat(pr),
            qr: qr === '' ? null : parseFloat(qr),
            isactive: isactive,
        }
        const { error } = cncValidator.joiBaseline(baselineData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeBaseline(baselineData)
        if (req.body.isactive == 1) {
            await cncModel.setBaselineActive()
        }
        await cncModel.editBaseline(req.body.editId, sanitizedData)
        res.status(201).json({ message: 'Baseline edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/baselineEdit' })
        res.status(500).json({ message: error.message })
    }
}
exports.baselineDelete = async (req, res) => {
    try {
        await cncModel.deleteBaseline(req.body.id)
        res.status(201).json({ message: 'Baseline deleted successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/baselineDelete' })
        res.status(500).json({ message: error.message })
    }
}
//==========================================================================================================================
//==========================================================================================================================
//REJECT CATEGORY
//==========================================================================================================================
//==========================================================================================================================
exports.rejectCategory = (req, res) => {
    const header = {pageTitle: 'Manage Reject Category', user: req.session.user}
    res.render('production/reject', {header: header})
};

exports.rejectCategoryAjax = async (req, res) => {
    const filters = {
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'category_name',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'category_name'
        
        const data = await cncModel.rejectCategory(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.rejectCategoryFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.rejectCategoryCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.category_name,
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="delete_modal('${record.id}')"><i class="fa fa-trash-alt"></i> Delete</button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectCategoryAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.rejectCategoryAdd = async (req, res) => {
    try {
        const { category_name} = req.body;
        const rejectCategoryData = {
            category_name: category_name,
        }
        const { error } = cncValidator.joiRejectCategory(rejectCategoryData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeRejectCategory(rejectCategoryData)
        
        await cncModel.addRejectCategory(sanitizedData)
        res.status(201).json({ message: 'Reject added successfully' })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectCategoryAdd' })
        res.status(500).json({ message: error.message })
    }
};

exports.rejectCategoryRead = async (req, res) => {
    try {
        const id = req.body.editId
        const rejectCategoryData = await cncModel.getRejectCategory(id)
        res.status(201).json({ message: 'Reject readed successfully', rejectCategoryData: rejectCategoryData })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectCategoryRead' })
        res.status(500).json({ message: error.message })
    }
};

exports.rejectCategoryEdit = async (req, res) => {
    try {
        const { category_name} = req.body;
        const rejectCategoryData = {
            category_name: category_name,
        }
        const { error } = cncValidator.joiBaseline(rejectCategoryData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeRejectCategory(rejectCategoryData)
        
        await cncModel.editRejectCategory(req.body.editId, sanitizedData)
        res.status(201).json({ message: 'Reject edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectCategoryEdit' })
        res.status(500).json({ message: error.message })
    }
};
exports.rejectCategoryDelete = async (req, res) => {
    try {
        const rejectCategoryData = {
            isactive: 0,
        }
        await cncModel.editRejectCategory(req.body.id, rejectCategoryData)
        res.status(201).json({ message: 'Reject deleted successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectCategoryDelete' })
        res.status(500).json({ message: error.message })
    }
};
//==========================================================================================================================
//==========================================================================================================================
//DOWNTIME CATEGORY
//==========================================================================================================================
//==========================================================================================================================
exports.downtimeCategory = (req, res) => {
    const header = {pageTitle: 'Manage Downtime Category', user: req.session.user}
    res.render('production/downtime', {header: header})
};

exports.downtimeCategoryAjax = async (req, res) => {
    const filters = {
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'category_name',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'category_name'
        
        const data = await cncModel.downtimeCategory(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.downtimeCategoryFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.downtimeCategoryCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.category_name,
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i> Edit</button>
                    <button class="btn btn-sm btn-danger" onclick="delete_modal('${record.id}')"><i class="fa fa-trash-alt"></i> Delete</button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeCategoryAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.downtimeCategoryAdd = async (req, res) => {
    try {
        const { category_name} = req.body;
        const downtimeCategoryData = {
            category_name: category_name,
        }
        const { error } = cncValidator.joiDowntimeCategory(downtimeCategoryData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeDowntimeCategory(downtimeCategoryData)
        
        await cncModel.addDowntimeCategory(sanitizedData)
        res.status(201).json({ message: 'Downtime added successfully' })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeCategoryAdd' })
        res.status(500).json({ message: error.message })
    }
};

exports.downtimeCategoryRead = async (req, res) => {
    try {
        const id = req.body.editId
        const downtimeCategoryData = await cncModel.getDowntimeCategory(id)
        res.status(201).json({ message: 'Downtime readed successfully', downtimeCategoryData: downtimeCategoryData })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeCategoryRead' })
        res.status(500).json({ message: error.message })
    }
};

exports.downtimeCategoryEdit = async (req, res) => {
    try {
        const { category_name} = req.body;
        const downtimeCategoryData = {
            category_name: category_name,
        }
        const { error } = cncValidator.joiDowntimeCategory(downtimeCategoryData)
        if (error) {
            return res.status(400).json({ message: error.details[0].message })
        }
        const sanitizedData = cncValidator.sanitizeDowntimeCategory(downtimeCategoryData)
        
        await cncModel.editDowntimeCategory(req.body.editId, sanitizedData)
        res.status(201).json({ message: 'Downtime edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeCategoryEdit' })
        res.status(500).json({ message: error.message })
    }
};
exports.downtimeCategoryDelete = async (req, res) => {
    try {
        const downtimeCategoryData = {
            isactive: 0,
        }
        await cncModel.editDowntimeCategory(req.body.id, downtimeCategoryData)
        res.status(201).json({ message: 'Downtime deleted successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeCategoryDelete' })
        res.status(500).json({ message: error.message })
    }
};
//==========================================================================================================================
//==========================================================================================================================
//CHART REPORT
//==========================================================================================================================
//==========================================================================================================================
//filter default: hourly
//options: shiftly, daily, monthly
exports.oeeChart = async (req, res) => {
    try {
        const { machineId, startDate, endDate, startMonth, endMonth, shift, selectedRange } = req.query;
        let reportData = []
        let data = []
        let htmlContent = ''

        const shift1 = await cncModel.getActiveShift()
        const now = moment()
        const shiftStart = moment(shift1, 'HH:mm:ss')
        shiftStart.set({
            year: now.year(),
            month: now.month(),
            date: now.date(),
        })
        if (now.isBefore(shiftStart)) {
            shiftStart.subtract(1, 'day');
        }
        const formattedDate = shiftStart.format('YYYY-MM-DD');
        const start_date = startDate || formattedDate;
        const end_date = endDate || formattedDate;

        const start = moment(start_date, 'YYYY-MM-DD')
        const end = moment(end_date, 'YYYY-MM-DD')
        let startBaseline, endBaseline
        if (start.isSame(end)) {
            startBaseline = start.clone().subtract(1, 'days').format('YYYY-MM-DD')
            endBaseline = end.clone().subtract(1, 'days').format('YYYY-MM-DD')
        } else {
            const daysDifference = end.diff(start, 'days') + 1
            startBaseline = start.clone().subtract(daysDifference, 'days').format('YYYY-MM-DD')
            endBaseline = end.clone().subtract(daysDifference, 'days').format('YYYY-MM-DD')
        }


        const machineInfo = await cncModel.getMachine(machineId)
        if (selectedRange === 'hourly') {
            data = await cncModel.chartHourly(machineId, start_date, end_date)
            baseline = await cncModel.chartHourly(machineId, startBaseline, endBaseline)
            htmlContent = `<h3 class="card-title"><b>Hourly Report of ${machineInfo.mc_name}</b></h3><div class="card-tools">${start_date} to ${end_date}</div>`
        } else if (selectedRange === 'daily') {
            data = await cncModel.chartDaily(machineId, start_date, end_date)
            baseline = await cncModel.chartDaily(machineId, startBaseline, endBaseline)
            htmlContent = `<h3 class="card-title"><b>Daily Report of ${machineInfo.mc_name}</b></h3><div class="card-tools">${start_date} to ${end_date}</div>`
        } else if (selectedRange === 'shiftly') {
            data = await cncModel.chartShiftly(machineId, start_date, end_date, shift)
            baseline = await cncModel.chartShiftly(machineId, startBaseline, endBaseline, shift)
            htmlContent = `<h3 class="card-title"><b>Shiftly Report of ${machineInfo.mc_name}</b></h3><div class="card-tools">${start_date} to ${end_date}</div>`
        } else if (selectedRange === 'weekly') {
            // const start_date = startWeek || formattedDate;
            // const end_date = endWeek || formattedDate;
            // const start = moment(start_date, 'YYYY-MM-DD')
            // const end = moment(end_date, 'YYYY-MM-DD')
            // let startBaseline, endBaseline
            // if (start.isSame(end)) {
            //     startBaseline = start.clone().subtract(1, 'days').format('YYYY-MM-DD')
            //     endBaseline = end.clone().subtract(1, 'days').format('YYYY-MM-DD')
            // } else {
            //     const daysDifference = end.diff(start, 'days') + 1
            //     startBaseline = start.clone().subtract(daysDifference, 'days').format('YYYY-MM-DD')
            //     endBaseline = end.clone().subtract(daysDifference, 'days').format('YYYY-MM-DD')
            // }
            data = await cncModel.chartWeekly(machineId, start_date, end_date)
            baseline = await cncModel.chartWeekly(machineId, startBaseline, endBaseline)
            htmlContent = `<h3 class="card-title"><b>Weekly Report of ${machineInfo.mc_name}</b></h3><div class="card-tools">${start_date} to ${end_date}</div>`
        } else if (selectedRange === 'monthly') {
            const start_date = startMonth || formattedDate;
            const end_date = endMonth || formattedDate;
            const start = moment(start_date, 'YYYY-MM')
            const end = moment(end_date, 'YYYY-MM')
            let startBaseline, endBaseline
            if (start.isSame(end)) {
                startBaseline = start.clone().subtract(1, 'months').format('YYYY-MM')
                endBaseline = end.clone().subtract(1, 'months').format('YYYY-MM')
            } else {
                const monthsDifference = end.diff(start, 'months') + 1
                startBaseline = start.clone().subtract(monthsDifference, 'months').format('YYYY-MM')
                endBaseline = end.clone().subtract(monthsDifference, 'months').format('YYYY-MM')
            }
            data = await cncModel.chartMonthly(machineId, start_date, end_date)
            baseline = await cncModel.chartMonthly(machineId, startBaseline, endBaseline)
            htmlContent = `<h3 class="card-title"><b>Monthly Report of ${machineInfo.mc_name}</b></h3><div class="card-tools">${start_date} to ${end_date}</div>`
        }

        reportData = {
            utilizationData: {
                dates: data.dates,
                values: data.averages.utilization,
                baseline: data.baseline_utilization,
                hours: data.averages.runtime,
                unit: data.runtime_unit
            },
            oeeData: {
                dates: data.dates,
                values: data.averages.oee,
                baseline: data.baseline_oee
            },
            arData: {
                dates: data.dates,
                values: data.averages.ar,
                baseline: data.baseline_ar
            },
            prData: {
                dates: data.dates,
                values: data.averages.pr,
                baseline: data.baseline_pr
            },
            qrData: {
                dates: data.dates,
                values: data.averages.qr,
                baseline: data.baseline_qr
            },
            stacking: {
                dates: data.dates,
                runtime: data.stacking.runtime,
                downtime: data.stacking.downtime,
                idletime: data.stacking.idletime,
                setuptime: data.stacking.setuptime,
            },
            summary:{
                avgUtilization: data.summary.avgUtilization,
                avgOEE: data.summary.avgOEE,
                avgAR: data.summary.avgAR,
                avgPR: data.summary.avgPR,
                avgQR: data.summary.avgQR,
                baseUtilization: baseline.summary.avgUtilization,
                baseOEE: baseline.summary.avgOEE,
                baseAR: baseline.summary.avgAR,
                basePR: baseline.summary.avgPR,
                baseQR: baseline.summary.avgQR,
            },
            product: {
                ct: data.product.ct,
                count: data.product.count,
                ideal: data.product.ideal,
            },
            htmlContent: htmlContent
        };
        res.json(reportData);
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/oeeChart' })
        res.status(500).json({ message: error.message })
    }
};
//==========================================================================================================================
//==========================================================================================================================
//OPERATOR DASHBOARD
//==========================================================================================================================
//==========================================================================================================================
exports.operatorPage = async (req, res) => {
    try {
        const rejectCategory = await cncModel.getActiveRejectCategory()
        const downtimeCategory = await cncModel.getActiveDowntimeCategory()
        const machines = await cncModel.getMachines()
        const header = {pageTitle: 'Operator Dashboard', user: req.session.user}
        const data = {
            machinesOP: machines,
            rejectCategory: rejectCategory,
            downtimeCategory: downtimeCategory,
        }
        res.render('production/console', { header: header, data: data })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/operatorPage' })
        res.status(500).send('Internal server error')
    }
}

exports.machineDashboard = async (req, res) => {
    try {
        const { machineId } = req.query
        const machineInfo = await cncModel.getMachine(machineId)
        
        const data = {
            machineInfo: machineInfo
        }
        res.json(data)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/machineDashboard' })
        res.status(500).send('Internal server error')
    }
}

//==========================================================================================================================
//OPERATOR DASHBOARD: REJECT
//==========================================================================================================================
exports.rejectAdd = async (req, res) => {
    try {
        const { reject_count, reject_category, reject_reason, machineId } = req.body
        const now = moment().format('YYYY-MM-DD HH:mm:ss')
        const machineData = await cncModel.getMachineDataRealtime(machineId, now)
        
        // console.log(machineData)
        if(machineData){
            const data = {
                shift_time: moment(machineData.min_start).format('YYYY-MM-DD HH:mm:ss'),
                added_date: now,
                shift: machineData.shift,
                reject_count: reject_count > 0 ? reject_count : 0,
                reject_category: reject_category,
                reject_reason: reject_reason,
                product_name: machineData.product_name,
                operator_id: req.session.user.id,
                machine_id: machineId,
            }
            await cncModel.addReject(data)
            await calculateOEEHourly(now, machineId)
            await calculateMachineOEE(now, machineId)
            // console.log(data)
            res.status(201).json({ message: 'Reject inserted successfully'})
        }else{
            res.status(500).send('Machine session not found')
        }
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectAdd' })
        res.status(500).send('Internal server error')
    }
}

exports.rejectOperatorAjax = async (req, res) => {
    const filters = {
        machineId: req.body.machineId,
        product_name: req.body.product_name,
        start_time: req.body.start_time,
        end_time: req.body.end_time,

        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'added_date',
        'reject_count',
        'category_name',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'category_name'
        
        const data = await cncModel.rejectOperator(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.rejectOperatorFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.rejectOperatorCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.added_date).format("YYYY-MM-DD HH:mm:ss"),
                    record.reject_count,
                    record.category_name,
                    `<button class="btn btn-sm btn-primary" onclick="show_modal('${record.id}')"><i class="fa fa-pencil-alt"></i></button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectOperatorAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.rejectOperatorRead = async (req, res) => {
    try {
        const id = req.body.editId
        const data = await cncModel.getRejectOperator(id)
        res.status(201).json({ message: 'Reject readed successfully', data: data })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectOperatorRead' })
        res.status(500).json({ message: error.message })
    }
};

exports.rejectOperatorEdit = async (req, res) => {
    try {
        const { reject_count, reject_category, addedDate, shiftTime} = req.body;
        const data = {
            reject_count: reject_count > 0 ? reject_count : 0,
            reject_category: reject_category,
            operator_id: req.session.user.id,
        }
                
        await cncModel.editRejectOperator(req.body.editId, data)
        const now = moment(addedDate).startOf('hour').format('YYYY-MM-DD HH:mm:ss')
        await calculateOEEHourly(now, req.body.machineId)
        await calculateMachineOEE(shiftTime, req.body.machineId)
        res.status(201).json({ message: 'Reject edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectOperatorEdit' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//OPERATOR DASHBOARD: DOWNTIME
//==========================================================================================================================
exports.downtimeOperatorAjax = async (req, res) => {
    const filters = {
        machineId: req.body.machineId,
        start_time: req.body.start_time,
        end_time: req.body.end_time,

        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'start_time',
        'duration_seconds',
        'category_name',
        'downtime_reason',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'desc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'start_time'
        
        const data = await cncModel.downtimeOperator(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.downtimeOperatorFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.downtimeOperatorCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.start_time).format("YYYY-MM-DD HH:mm:ss"),
                    record.end_time ? moment.utc(record.duration_seconds * 1000).format("HH:mm:ss") : 'Active',
                    record.category_name,
                    record.downtime_reason,
                    `<button class="btn btn-sm btn-primary" onclick="show_downtime('${record.id}')"><i class="fa fa-pencil-alt"></i></button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeOperatorAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.downtimeOperatorRead = async (req, res) => {
    try {
        const id = req.body.editId
        const data = await cncModel.getDowntimeOperator(id)
        res.status(201).json({ message: 'Downtime readed successfully', data: data })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeOperatorRead' })
        res.status(500).json({ message: error.message })
    }
};

exports.downtimeOperatorEdit = async (req, res) => {
    try {
        const { downtime_category, downtime_reason} = req.body;
        const data = {
            downtime_category: downtime_category,
            downtime_reason: downtime_reason,
            operator_id: req.session.user.id,
        }
                
        await cncModel.editDowntimeOperator(req.body.editId, data)
        res.status(201).json({ message: 'Downtime edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeOperatorEdit' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//OPERATOR DASHBOARD: ALARM
//==========================================================================================================================
exports.alarmOperatorAjax = async (req, res) => {
    const filters = {
        machineId: req.body.machineId,
        start_time: req.body.start_time,
        end_time: req.body.end_time,

        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        'start_time',
        'alarm',
        'alarm_reason',
        null
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'desc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'start_time'
        
        const data = await cncModel.alarmOperator(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.alarmOperatorFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.alarmOperatorCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.start_time).format("YYYY-MM-DD HH:mm:ss"),
                    record.alarm,
                    record.alarm_reason,
                    `<button class="btn btn-sm btn-primary" onclick="show_alarm('${record.id}')"><i class="fa fa-pencil-alt"></i></button>`
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/alarmOperatorAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.alarmOperatorRead = async (req, res) => {
    try {
        const id = req.body.editId
        const data = await cncModel.getAlarmOperator(id)
        res.status(201).json({ message: 'Alarm readed successfully', data: data })
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/alarmOperatorRead' })
        res.status(500).json({ message: error.message })
    }
};

exports.alarmOperatorEdit = async (req, res) => {
    try {
        const { alarm_reason} = req.body;
        const data = {
            alarm_reason: alarm_reason,
            operator_id: req.session.user.id,
        }
                
        await cncModel.editAlarmOperator(req.body.editId, data)
        res.status(201).json({ message: 'Alarm edited successfully'})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/alarmOperatorEdit' })
        res.status(500).json({ message: error.message })
    }
};
//==========================================================================================================================
//==========================================================================================================================
//PARETO REPORT REJECT
//==========================================================================================================================
//==========================================================================================================================
exports.rejectPareto = (req, res) => {
    const header = {pageTitle: 'Reject Pareto', user: req.session.user}
    res.render('report/reject', {header: header})
};

exports.rejectParetoAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        shift: req.body.shift,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'shift',
        'added_date',
        'mc_name',
        'product_name',
        'category_name',
        'reject_count',
        'reject_reason',
        'fullname',
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'added_date'
        
        const data = await cncModel.rejectPareto(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.rejectParetoFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.rejectParetoCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.shift,
                    moment(record.added_date).format('YYYY-MM-DD HH:mm:ss'),
                    record.mc_name,
                    record.product_name,
                    record.category_name,
                    record.reject_count,
                    record.reject_reason,
                    record.fullname,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectParetoAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.rejectParetoChart = async (req, res) => {
    try {
        const { startDate, endDate, shift } = req.query;

        const shift1 = await cncModel.getActiveShift()
        const now = moment()
        const shiftStart = moment(shift1, 'HH:mm:ss')
        shiftStart.set({
            year: now.year(),
            month: now.month(),
            date: now.date(),
        })
        if (now.isBefore(shiftStart)) {
            shiftStart.subtract(1, 'day');
        }
        const formattedDate = shiftStart.format('YYYY-MM-DD');
        let start_date = startDate || formattedDate;
        start_date = moment(start_date)
        .set({ 
            hour: parseInt(shift1.split(':')[0]),  
            minute: parseInt(shift1.split(':')[1]),
            second: parseInt(shift1.split(':')[2]) 
        })
        .format('YYYY-MM-DD HH:mm:ss');
        let end_date = endDate || formattedDate;
        end_date = moment(end_date)
            .add(1, 'days')
            .set({ 
                hour: parseInt(shift1.split(':')[0]),  
                minute: parseInt(shift1.split(':')[1]),
                second: parseInt(shift1.split(':')[2]) 
            })
            .format('YYYY-MM-DD HH:mm:ss');

        data = await cncModel.paretoChart(start_date, end_date, shift)
        
        res.json({data, start_date, end_date, shift});
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/rejectParetoChart' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//==========================================================================================================================
//PARETO REPORT DOWNTIME
//==========================================================================================================================
//==========================================================================================================================
exports.downtimePareto = (req, res) => {
    const header = {pageTitle: 'Reject Pareto', user: req.session.user}
    res.render('report/downtime', {header: header})
};

exports.downtimeParetoAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'start_time',
        'mc_name',
        'duration_seconds',
        'category_name',
        'downtime_reason',
        'fullname',
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'start_time'
        
        const data = await cncModel.downtimePareto(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.downtimeParetoFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.downtimeParetoCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.start_time).format('YYYY-MM-DD HH:mm:ss'),
                    record.mc_name,
                    moment.utc(record.duration_seconds * 1000).format("HH:mm:ss"),
                    record.category_name,
                    record.downtime_reason,
                    record.fullname,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeParetoAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.downtimeParetoChart = async (req, res) => {
    try {
        const { startDate, endDate } = req.query;

        const shift1 = await cncModel.getActiveShift()
        const now = moment()
        const shiftStart = moment(shift1, 'HH:mm:ss')
        shiftStart.set({
            year: now.year(),
            month: now.month(),
            date: now.date(),
        })
        if (now.isBefore(shiftStart)) {
            shiftStart.subtract(1, 'day');
        }
        const formattedDate = shiftStart.format('YYYY-MM-DD');
        let start_date = startDate || formattedDate;
        start_date = moment(start_date)
        .set({ 
            hour: parseInt(shift1.split(':')[0]),  
            minute: parseInt(shift1.split(':')[1]),
            second: parseInt(shift1.split(':')[2]) 
        })
        .format('YYYY-MM-DD HH:mm:ss');
        let end_date = endDate || formattedDate;
        end_date = moment(end_date)
            .add(1, 'days')
            .set({ 
                hour: parseInt(shift1.split(':')[0]),  
                minute: parseInt(shift1.split(':')[1]),
                second: parseInt(shift1.split(':')[2]) 
            })
            .format('YYYY-MM-DD HH:mm:ss');

        data = await cncModel.downtimeParetoChart(start_date, end_date)
        
        res.json({data, start_date, end_date});
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/downtimeParetoChart' })
        res.status(500).json({ message: error.message })
    }
};


//==========================================================================================================================
//==========================================================================================================================
//UTILIZATION REPORT
//==========================================================================================================================
//==========================================================================================================================
exports.utilizationReport = async (req, res) => {
    const machines = await cncModel.getMachines()
    const header = {pageTitle: 'Utilization Report', user: req.session.user}
    const data = {
        machines: machines,
    }
    res.render('report/utilization', {header: header, data:data})
};

exports.utilizationReportAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        shift: req.body.shift,
        machineId: req.body.machineId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'oee_date',
        'shift',
        'mc_name',
        'product_name',
        'utilization',
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'oee_date'
        
        const data = await cncModel.utilizationReport(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.utilizationReportFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.utilizationReportCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.oee_date).format('YYYY-MM-DD'),
                    record.shift,
                    record.mc_name,
                    record.product_name,
                    record.utilization,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/utilizationReportAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.utilizationReportChart = async (req, res) => {
    try {
        const { startDate, endDate, shift, machineId } = req.query;

        data = await cncModel.utilizationChart(startDate, endDate, shift, machineId)
        
        res.json({data, startDate, endDate, shift, machineId});
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/utilizationReportChart' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//==========================================================================================================================
//OEE REPORT
//==========================================================================================================================
//==========================================================================================================================
exports.oeeReport = async (req, res) => {
    const machines = await cncModel.getMachines()
    const header = {pageTitle: 'OEE Report', user: req.session.user}
    const data = {
        machines: machines,
    }
    res.render('report/oee', {header: header, data:data})
};

exports.oeeReportAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        shift: req.body.shift,
        machineId: req.body.machineId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'oee_date',
        'shift',
        'mc_name',
        'product_name',
        'availability',
        'performance',
        'quality',
        'oee',
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'oee_date'
        
        const data = await cncModel.oeeReport(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.oeeReportFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.oeeReportCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.oee_date).format('YYYY-MM-DD'),
                    record.shift,
                    record.mc_name,
                    record.product_name,
                    record.availability,
                    record.performance,
                    record.quality,
                    record.oee,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/oeeReportAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.oeeReportChart = async (req, res) => {
    try {
        const { startDate, endDate, shift, machineId } = req.query;

        data = await cncModel.oeeReportChart(startDate, endDate, shift, machineId)
        
        res.json({data, startDate, endDate, shift, machineId});
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/oeeReportChart' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//==========================================================================================================================
//PART REPORT
//==========================================================================================================================
//==========================================================================================================================
exports.partReport = async (req, res) => {
    const machines = await cncModel.getMachines()
    const header = {pageTitle: 'Part Report', user: req.session.user}
    const data = {
        machines: machines,
    }
    res.render('report/part', {header: header, data:data})
};

exports.partReportAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        shift: req.body.shift,
        machineId: req.body.machineId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'oee_date',
        'shift',
        'mc_name',
        'product_name',
        null,
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'oee_date'
        
        const data = await cncModel.partReport(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.partReportFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.partReportCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.oee_date).format('YYYY-MM-DD'),
                    record.shift,
                    record.mc_name,
                    record.product_name,
                    record.total_count,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/partReportAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.partReportChart = async (req, res) => {
    try {
        const { startDate, endDate, shift, machineId } = req.query;

        data = await cncModel.partReportChart(startDate, endDate, shift, machineId)
        
        res.json({data, startDate, endDate, shift, machineId});
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/partReportChart' })
        res.status(500).json({ message: error.message })
    }
};


//==========================================================================================================================
//==========================================================================================================================
//SETUP REPORT
//==========================================================================================================================
//==========================================================================================================================
exports.setupReport = async (req, res) => {
    try {
        const machines = await cncModel.getMachines()
        const header = {pageTitle: 'Setup Report', user: req.session.user}
        const data = {
            machines: machines,
        }
        res.render('report/setup', {header: header, data:data})
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/setupReport' })
        res.status(500).send('Internal server error')
    }

};

exports.setupReportAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        machineId: req.body.machineId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    const columnNames = [
        'mc_name',
        'total_setup',
        null,
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'asc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'mc_name'
        
        const data = await cncModel.setupReport(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.setupReportFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.setupReportCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    
                    record.mc_name,
                    record.total_setup,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/setupReportAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.setupReportChart = async (req, res) => {
    try {
        const { startDate, endDate, machineId } = req.query;

        data = await cncModel.setupReportChart(startDate, endDate, machineId)
        
        res.json({data, startDate, endDate, machineId});
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/setupReportChart' })
        res.status(500).json({ message: error.message })
    }
};

//==========================================================================================================================
//==========================================================================================================================
//ALARM REPORT
//==========================================================================================================================
//==========================================================================================================================
exports.alarmReport = async (req, res) => {
    const machines = await cncModel.getMachines()
    const header = {pageTitle: 'Alarm Report', user: req.session.user}
    const data = {
        machines: machines,
    }
    res.render('report/alarm', {header: header, data:data})
};

exports.alarmReportAjax = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        machineId: req.body.machineId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }
    
    const shift1 = await cncModel.getActiveShift()
    const now = moment()
    const shiftStart = moment(shift1, 'HH:mm:ss')
    shiftStart.set({
        year: now.year(),
        month: now.month(),
        date: now.date(),
    })
    if (now.isBefore(shiftStart)) {
        shiftStart.subtract(1, 'day');
    }
    const formattedDate = shiftStart.format('YYYY-MM-DD');
    let start_date = req.body.start_date || formattedDate;
    start_date = moment(start_date)
    .set({ 
        hour: parseInt(shift1.split(':')[0]),  
        minute: parseInt(shift1.split(':')[1]),
        second: parseInt(shift1.split(':')[2]) 
    })
    .format('YYYY-MM-DD HH:mm:ss');
    let end_date = req.body.end_date || formattedDate;
    end_date = moment(end_date)
        .add(1, 'days')
        .set({ 
            hour: parseInt(shift1.split(':')[0]),  
            minute: parseInt(shift1.split(':')[1]),
            second: parseInt(shift1.split(':')[2]) 
        })
        .format('YYYY-MM-DD HH:mm:ss');

    filters.start_date = start_date
    filters.end_date = end_date
    
    const columnNames = [
        'alarm',
        'totalCount',
        'machineCount',
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'desc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'totalCount'
        
        const data = await cncModel.alarmReport(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.alarmReportFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.alarmReportCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    record.alarm,
                    record.totalCount,
                    record.machineCount,
                    `<button class="btn btn-sm btn-primary" onclick="load_data('${record.max_id}')"><i class="fa fa-arrow-right"></i></button>`,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/alarmReportAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.alarmReportDetail = async (req, res) => {
    const filters = {
        start_date: req.body.start_date,
        end_date: req.body.end_date,
        machineId: req.body.machineId,
        alarmId: req.body.alarmId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    }

    
    const shift1 = await cncModel.getActiveShift()
    const now = moment()
    const shiftStart = moment(shift1, 'HH:mm:ss')
    shiftStart.set({
        year: now.year(),
        month: now.month(),
        date: now.date(),
    })
    if (now.isBefore(shiftStart)) {
        shiftStart.subtract(1, 'day');
    }
    const formattedDate = shiftStart.format('YYYY-MM-DD');
    let start_date = req.body.start_date || formattedDate;
    start_date = moment(start_date)
    .set({ 
        hour: parseInt(shift1.split(':')[0]),  
        minute: parseInt(shift1.split(':')[1]),
        second: parseInt(shift1.split(':')[2]) 
    })
    .format('YYYY-MM-DD HH:mm:ss');
    let end_date = req.body.end_date || formattedDate;
    end_date = moment(end_date)
        .add(1, 'days')
        .set({ 
            hour: parseInt(shift1.split(':')[0]),  
            minute: parseInt(shift1.split(':')[1]),
            second: parseInt(shift1.split(':')[2]) 
        })
        .format('YYYY-MM-DD HH:mm:ss');

    filters.start_date = start_date
    filters.end_date = end_date
    


    const columnNames = [
        'start_time',
        'mc_name',
        'alarm_reason',
        'fullname',
    ];
    const columnSearches = req.body.columns.map((col, index) => {
        if (col.search.value && col.orderable) {
            return { column: columnNames[index], value: col.search.value }
        }
        return null
    }).filter(col => col)

    try {
        const orderColumnIndex = filters.order.length > 0 ? filters.order[0].column : null
        const orderDirection = filters.order.length > 0 ? filters.order[0].dir : 'desc'
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'start_time'
        
        const data = await cncModel.alarmDetail(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await cncModel.alarmDetailFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await cncModel.alarmDetailCountAll(),
            recordsFiltered,
            data: data.map(record => {
                return [
                    moment(record.start_time).format('YYYY-MM-DD HH:mm:ss'),
                    record.mc_name,
                    record.alarm_reason,
                    record.fullname,
                ];
            })
        };

        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'prodController/alarmReportDetail' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}