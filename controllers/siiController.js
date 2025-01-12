const siiModel = require('../models/siiModel')
const encryptModel = require('../models/encryptModel')
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
//KANBAN DATA
//==========================================================================================================================
//==========================================================================================================================

// function shiroki_master_data(){
//     $this->global['pageTitle'] = 'Data Kanban';
//     $this->loadViews("shiroki/shiroki_master_data", $this->global, NULL, NULL);
// }

exports.dataKanban = async (req, res) => {
    try {
        const header = {pageTitle: 'Data Kanban', user: req.session.user}
        res.render('sii/dataKanban', { header: header });
    } catch (error) {
        console.error('Error:', error);
        res.status(500).send('Internal Server Error');
    }
}
exports.dataKanbanAjax = async (req, res) => {
    const filters = {
        plantId: req.session.user.plantId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        null,
        'timestamp',
        'kanban_cus',
        'kanban_sii',
        'desc',
        'addedby',
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
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'kanban_cus'
        
        const data = await siiModel.dataKanbanList(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await siiModel.dataKanbanFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await siiModel.dataKanbanCountAll(),
            recordsFiltered,
            data: data.map((record, index) => {
                const no = Number(filters.start) + index + 1;
                const encryptID = encryptModel.encryptOA(record.id)
                return [
                    no,
                    record.timestamp,
                    record.kanban_cus,
                    record.kanban_sii,
                    record.desc,
                    record.fullname,
                    `<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#masterdata${no}" title="Update Data"><i class="fa fa-pencil-alt"></i> Update</button>
                    <div class="modal fade" id="masterdata${no}">
                        <div class="modal-dialog">
                        <form action="/sii/updateDataKanban" method="POST">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">Update Master Data</h4>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <div>
                                        <table class="table">
                                            <tr>
                                                <th>Kanban Customer</th>
                                                <td><input type="text" class="form-control" value="${record.kanban_cus}" onkeyup="cek_part_name(this)" name="kanban_cus" required></td>
                                            </tr>
                                            <tr>
                                                <th>Kanban Shiroki</th>
                                                <td><input type="text" class="form-control" value="${record.kanban_sii}" name="kanban_shi" required></td>
                                            </tr>
                                            <tr>
                                                <th>Deskripsi</th>
                                                <td><textarea name="desc" class="form-control partname" required readonly>${record.desc}</textarea><span class="note"></span></td>
                                            </tr>
                                            <tr>
                                                <th>Delete?</th>
                                                <td><select name="isvalid" class="form-control"><option value="1">No</option><option value="0">Yes</option></select></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <input type="hidden" name="id" value="${encryptID}">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Update</button>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>`
                ];
            }),
        };
        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'siiController/dataKanbanAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}
exports.cekPartName = async (req, res) => {
    try {
        // Get the `part_name` from the POST request body
        const { part_name } = req.body;
    
        // Fetch the part from the database
        const part = await siiModel.getManifestByPart(part_name, req.session.user.plantId);
    
        if (part) {
            // Respond with the part name if found
            res.json(part.part_name);
        } else {
            // Respond with an empty string if not found
            res.json('');
        }
    } catch (error) {
      console.error('Error in cekPartName:', error.message);
      res.status(500).json({ error: 'An error occurred while fetching the part name.' });
    }
};
exports.updateDataKanban = async (req, res) => {
    try {
        // Extract data from the request body
        const { kanban_cus, kanban_sii, desc, isvalid, id: encryptedId } = req.body;
        // Decrypt the ID
        const id = encryptModel.decryptOA(encryptedId); // Replace `decryptOA` with your decryption method
        if (!id) {
            return res.status(400).send('Invalid ID.');
        }
    
        // Prepare the data for update
        const data = {
            kanban_cus,
            kanban_sii,
            desc,
            isvalid,
            addedby: req.session.user.id,
        };

        // Update the record in the database
        const updated = await siiModel.editMasterData(data, id);
        if (updated) {
            return res.redirect('/sii/dataKanban'); // Redirect on success
        } else {
            return res.status(500).send('Failed to update master data.');
        }
    } catch (error) {
        console.error('Error in updateMasterData:', error.message);
        return res.status(500).send('An error occurred while updating master data.');
    }
};
exports.tambahKanban = async (req, res) => {
    try {
        const { kanban_cus, kanban_sii, desc } = req.body;

        if (kanban_cus && kanban_sii) {
            const inputArray = {
                kanban_cus,
                kanban_sii,
                desc: desc || null,
                addedby: req.session.user.id,
                plant_id: req.session.user.plantId,
            };

            const existingData = await siiModel.getKanbanCus(kanban_cus, kanban_sii, req.user.plantId);

            if (existingData) {
                // Update existing record
                await siiModel.editMasterData(inputArray, existingData.id);
            } else {
                // Insert new record
                await siiModel.addMasterData(inputArray);
            }

            // Redirect or send a success response
            res.redirect('/sii/dataKanban');
        } else {
            res.status(400).send('Kanban customer and Kanban SII are required.');
        }
    } catch (error) {
        console.error('Error:', error.message);
        res.status(500).send('An error occurred while processing the request.');
    }
};
//==========================================================================================================================
//==========================================================================================================================
//SAMPAH MANIFEST
//==========================================================================================================================
//==========================================================================================================================
exports.sampah = async (req, res) => {
    try {
        const header = {pageTitle: 'Tempat Sampah', user: req.session.user}
        const data = {
            tid: encryptModel.encryptOA(new moment().format('YYYY-MM-DD HH:mm:ss'))
        }
        res.render('sii/sampah', { header: header, data: data });
    } catch (error) {
        console.error('Error in sampah:', error);
        res.status(500).send('Internal Server Error');
    }
}
exports.sampahAjax = async (req, res) => {
    const filters = {
        plantId: req.session.user.plantId,
        draw: req.body.draw,
        start: req.body.start,
        length: req.body.length,
        search_value: req.body.search['value'],
        order: req.body.order || []
    };
    const columnNames = [
        null,
        'manifest',
        'order_no',
        null,
        'na7',
        'dock_code',
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
        
        const orderColumn = orderColumnIndex !== null ? columnNames[orderColumnIndex] : 'update_time'
        
        const data = await siiModel.sampahList(filters, orderColumn, orderDirection, columnSearches)

        const recordsFiltered = await siiModel.sampahListFiltered(filters, columnSearches)

        const output = {
            draw: filters.draw,
            recordsTotal: await siiModel.sampahListCountAll(),
            recordsFiltered,
            data: data.map((record, index) => {
                const no = filters.start + index + 1;
                const encryptID = encryptModel.encryptOA(record.id)
                const encryptManifest = encryptModel.encryptOA(record.manifest)
                return [
                    no,
                    record.manifest,
                    record.order_no,
                    `<a href="/sii/log_manifest/${encryptID}" class="btn btn-primary btn-sm"><i class="fa fa-file"></i> ${Math.round(record.prog * 100) / 100}%</a>`,
                    moment(record.na7.substring(0, record.na7.length - 4)).format('DD-MM-YYYY HH:mm'),
                    record.dock_code,
                    `
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#masterdatax${no}" title="Buang Permanent"><i class="fa fa-trash-alt"></i></button>
                        <div class="modal fade" id="masterdatax${no}">
                            <div class="modal-dialog">
                            <form action="sii/trash_manifest" method="POST">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Buang Manifest secara Permanent</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah anda yakin ingin menghapus manifest secara permanent?
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="xid" value="${encryptManifest}">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-danger"><i class="fa fa-trash-alt"></i> Buang</button>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#masterdata${no}" title="Kembalikan Manifest"><i class="fa fa-upload"></i></button>
                        <div class="modal fade" id="masterdata${no}">
                            <div class="modal-dialog">
                            <form action="/sii/restore_manifest" method="POST">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Kembalikan Manifest</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">
                                        Apakah anda yakin ingin mengembalikan manifest ini?
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <input type="hidden" name="yid" value="${encryptManifest}">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Kembalikan</button>
                                    </div>
                                </div>
                            </form>
                            </div>
                        </div>
                    `,
                ];
            }),
        };
        res.json(output)
    } catch (error) {
        await logError('error', error.message, error.stack, { functionName: 'siiController/sampahAjax' })
        res.status(500).json({ error: 'An error occurred while fetching the data' })
    }
}

exports.trash_manifest = async (req, res) => {
    try {
        const header = {pageTitle: 'Tempat Sampah', user: req.session.user}
        res.render('sii/sampah', { header: header });
    } catch (error) {
        console.error('Error in sampah:', error);
        res.status(500).send('Internal Server Error');
    }
}

exports.trashManifest = async (req, res) => {
    try {
        const plantId = req.session.user.plantId;
        const { id, yid, xid, tid } = req.body;
    
        if (id) {
            const decryptedId = encryptModel.decryptOA(id);
            await siiModel.editManifestByMan({ isvalid: 0 }, decryptedId, plantId);
            return res.redirect('/sii/manifest_log_data');
        } else if (yid) {
            // Mark manifest as valid
            const decryptedYid = encryptModel.decryptOA(yid);
            await siiModel.editManifestByMan({ isvalid: 1 }, decryptedYid, plantId);
            return res.redirect('/sii/trash_manifest');
        } else if (xid) {
            // Permanently delete manifest
            const decryptedXid = encryptModel.decryptOA(xid);
            await siiModel.deleteManifestByMan(decryptedXid, plantId);
            return res.redirect('/sii/trash_manifest');
        } else if (tid) {
            // Validate time and delete manifest by validity
            const decryptedTid = encryptModel.decryptOA(tid);
            const currentTime = Date.now();
            const validTime = currentTime - decryptedTid * 1000;

            if (validTime < 80000 * 1000) { // Convert to milliseconds
                await siiModel.deleteManifestByValid(0, plantId);
                return res.redirect('/sii/trash_manifest');
            } else {
                return res.status(400).send('Invalid action');
            }
        } else {
            return res.status(400).send('No valid action specified');
        }
    } catch (error) {
        console.error('Error in trashManifest:', error);
        res.status(500).send('An error occurred while processing your request');
    }
};