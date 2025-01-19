const siiDb = require('../models/siiDb');


class siiModel {
    //====================================================================================================================
    //====================================================================================================================
    //====================================================================================================================
    //====================================================================================================================
    async countOpenManifest(plantId) {
        const result = await siiDb('manifest_data')
            .where('plant_id', plantId)
            .andWhere('proses', '<', 100)
            .andWhere('isvalid', 1)
            .groupBy('manifest')
            .count('* as count');

        return result.length; // Number of rows grouped by 'manifest'
    }

    async countAllManifest(plantId) {
        const result = await siiDb('manifest_data')
            .where('plant_id', plantId)
            .andWhere('isvalid', 1)
            .groupBy('manifest')
            .count('* as count');

        return result.length; // Number of rows grouped by 'manifest'
    }

    async countAllUser(plantId) {
        const result = await siiDb('db_main.users')
            .where('plant_id', plantId)
            .andWhere('isactive', 1)
            .count('* as count')
            .first();

        return result.count; // Total number of valid users
    }

    async countAllKanban(plantId) {
        const result = await siiDb('manifest_data')
            .where('plant_id', plantId)
            .count('* as count')
            .first();

        return result.count; // Total number of kanban records
    }
     // Delete manifest records older than a specific date
    async deleteManifestOlderThan(date, plantId) {
        return await siiDb('manifest_data')
        .whereRaw("substr(arrival_date, 1, 19) <= ?", [date])
        .andWhere('plant_id', plantId)
        .del();
    }

    // Delete scan records older than a specific date
    async deleteScanOlderThan(date, plantId) {
        return await siiDb('scan_manifest')
        .where('timestamp', '<=', date)
        .andWhere('plant_id', plantId)
        .del();
    }

    // Update manifest records older than a specific date
    async editManifestOlderThan(data, date, plantId) {
        return await siiDb('manifest_data')
        .whereRaw("substr(arrival_date, 1, 19) <= ?", [date])
        .andWhere('plant_id', plantId)
        .update(data);
    }
    async monitorManifest(plantId) {
        const result = await siiDb('manifest_data')
          .select('*')
          .select(siiDb.raw('AVG(proses) as prog')) // Calculate the average of `proses`
          .where('plant_id', plantId)
          .andWhere('proses', '<', 100)
          .andWhere('isvalid', 1)
          .groupBy('manifest') // Group by `manifest`
          .limit(10); // Limit the results to 10
    
        return result; // Return the query results
    }
    //====================================================================================================================
    //====================================================================================================================
    //DATA MANIFEST
    //====================================================================================================================
    //====================================================================================================================
    _manifestData(filters, columnSearches) {
        let query = siiDb('manifest_data as t1')
            .select(
                't1.*',
                't2.fullname as fullname'
            )
            .leftJoin('db_main.users as t2', 't1.user_id', 't2.id')
            .where('t1.plant_id', filters.plantId)
            .where('t1.isvalid', 1)
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async manifestData(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._manifestData(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async manifestDataFiltered(filters, columnSearches) {
        let query = this._manifestData(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async manifestDataCountAll(filters) {
        let query = siiDb('manifest_data').where({plant_id: filters.plantId})
        const result = await query.count('* as total').where('isvalid', 1).first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
    async addManifestData(data) {
        try {
            // Use INSERT IGNORE to skip duplicates
            await siiDb.raw(`
                INSERT IGNORE INTO manifest_data (manifest, arrival_date, arrival_time, dock_code, pline_code, pline_no, supplier_name, supplier_code, order_no, subroute, outtime, part_no, part_name, unique_no, box_type, qty_per_kanban, qty_kanban, qty_order, plant_id, user_id)
                VALUES ?
            `, [data.map(item => [item.manifest, item.arrival_date, item.arrival_time, item.dock_code, item.pline_code, item.pline_no, item.supplier_name, item.supplier_code, item.order_no, item.subroute, item.outtime, item.part_no, item.part_name, item.unique_no, item.box_type, item.qty_per_kanban, item.qty_kanban, item.qty_order, item.plant_id, item.user_id])]);

            return { success: true, message: 'Manifests inserted successfully (duplicates ignored).' };
        } catch (error) {
            console.error('Database Error:', error);
            return { success: false, message: 'Failed to insert manifests.' };
        }
    }
    //====================================================================================================================
    //====================================================================================================================
    //LOG MANIFEST DATA
    //====================================================================================================================
    //====================================================================================================================
    _logManifestData(filters, columnSearches) {
        let query = siiDb('scan_manifest as t1')
            .select(
                't1.*',
                't2.fullname as fullname',
                't3.part_name as part_name'
            )
            .leftJoin('db_main.users as t2', 't1.scanby', 't2.id')
            .leftJoin(
                'manifest_data as t3',
                function () {
                this.on('t1.manifest_id', '=', 't3.manifest')
                    .andOn('t1.scan_part', '=', 't3.part_no');
                }
            )
            .where('t1.plant_id', filters.plantId);
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async logManifestData(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._logManifestData(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async logManifestDataFiltered(filters, columnSearches) {
        let query = this._logManifestData(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async logManifestDataCountAll(filters) {
        let query = siiDb('scan_manifest').where({plant_id: filters.plantId})
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
    //====================================================================================================================
    //LOG SCAN
    //====================================================================================================================
    //====================================================================================================================
    _logScan(filters, columnSearches) {
        let query = siiDb('scan_manifest as t1')
            .select(
                't1.*',
                't2.fullname as fullname',
                't3.part_name as part_name'
            )
            .leftJoin('db_main.users as t2', 't1.scanby', 't2.id')
            .leftJoin(
                'manifest_data as t3',
                function () {
                this.on('t1.manifest_id', '=', 't3.manifest')
                    .andOn('t1.scan_part', '=', 't3.part_no');
                }
            )
            .where('t1.plant_id', filters.plantId);
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async logScan(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._logScan(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async logScanFiltered(filters, columnSearches) {
        let query = this._logScan(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async logScanCountAll(filters) {
        let query = siiDb('scan_manifest').where({plant_id: filters.plantId})
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
    //DATA KANBAN
    //====================================================================================================================
	//====================================================================================================================
    _dataKanbanList(filters, columnSearches) {
        let query = siiDb('master_data as t1')
            .select('t1.*', 't2.fullname as fullname')
            .leftJoin('db_main.users as t2', 't1.addedby', 't2.id')
            .where('t1.plant_id', filters.plantId)
            .andWhere('t1.isvalid', 1);
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async dataKanbanList(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._dataKanbanList(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async dataKanbanFiltered(filters, columnSearches) {
        let query = this._dataKanbanList(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async dataKanbanCountAll() {
        let query = siiDb('master_data').where({isvalid: 1})
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
    async addMasterData(data) {
        try {
            const [insertId] = await siiDb('master_data')
            .insert(data)
            .returning('id'); // Returning the inserted ID (for PostgreSQL or MySQL 8+)
            return insertId;
        } catch (error) {
            console.error('Error in addMasterData:', error.message);
            throw error;
        }
    }
    async editMasterData(data, id) {
        try {
            await siiDb('master_data')
                .where({ id })
                .update(data);
            return true;
        } catch (error) {
            console.error('Error in editMasterData:', error.message);
            throw error;
        }
    }
    async getKanbanCus(kanbanCus, kanban_sii, plantId) {
        try {
            return await siiDb('master_data')
            .where({
                kanban_cus: kanbanCus,
                kanban_sii: kanban_sii,
                isvalid: 1,
                plant_id: plantId,
            })
            .first(); // Returns the first matching record or undefined
        } catch (error) {
            console.error('Error in getKanbanCus:', error.message);
            throw error;
        }
    }
    async getManifestByPart(partNo, plantId) {
        try {
          return await siiDb('manifest_data')
            .select('*') // Select all columns
            .where({ plant_id: plantId, part_no: partNo }) // Apply filters
            .orderBy('id', 'desc') // Order by `id` descending
            .first(); // Retrieve the first result
        } catch (error) {
            console.error('Error in getManifestByPart:', error.message);
            throw error;
        }
    }
    //====================================================================================================================
	//====================================================================================================================
    //MANIFEST HALT
    //====================================================================================================================
	//====================================================================================================================
    _manifestHalted(filters, columnSearches) {
        let query = siiDb('manifest_halt as t1')
            .select('t1.*', siiDb.raw('AVG(t2.proses) as prog'), 't2.id as idx')
            .join('manifest_data as t2', 't1.kode', 't2.manifest')
            .where('t2.isvalid', 1)
            .andWhere('t1.plant_id', filters.plantId)
            .groupBy('t1.kode');
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async manifestHalted(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._manifestHalted(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async manifestHaltedFiltered(filters, columnSearches) {
        let query = this._manifestHalted(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async manifestHaltedCountAll(filters) {
        let query = siiDb('manifest_halt').where('plant_id', filters.plantId)
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
    async addHalt(data) {
        const trx = await siiDb.transaction(); // Start a transaction
        try {
            const [insertId] = await trx('manifest_halt')
                .insert(data)
                .returning('id'); // Return the ID of the inserted record (PostgreSQL/MySQL 8+)
            await trx.commit(); // Commit the transaction
            return insertId;
        } catch (error) {
            await trx.rollback(); // Rollback the transaction in case of error
            console.error('Error in addHalt:', error.message);
            throw error;
        }
    }
    //====================================================================================================================
	//====================================================================================================================
    //ADMIN
    //====================================================================================================================
	//====================================================================================================================
    _haltKeyList(filters, columnSearches) {
        let query = siiDb('admin_pass')
            .select('*')
            .where('plant_id', filters.plantId)
            .andWhere('isvalid', 1);
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async haltKeyList(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._haltKeyList(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async haltKeyListFiltered(filters, columnSearches) {
        let query = this._haltKeyList(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async haltKeyListCountAll() {
        let query = siiDb('admin_pass').where({isvalid: 1})
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
    async addAdmin(data) {
        const trx = await siiDb.transaction();
        try {
            const [insertId] = await trx('admin_pass')
                .insert(data)
                .returning('id'); // Returning the inserted ID (works with PostgreSQL/MySQL 8+)
            await trx.commit();
            return insertId;
        } catch (error) {
            await trx.rollback();
            console.error('Error in addAdmin:', error.message);
            throw error;
        }
    }
    async editAdmin(data, id) {
        try {
            await siiDb('admin_pass')
                .where({ id })
                .update(data);
            return true;
        } catch (error) {
            console.error('Error in editAdmin:', error.message);
            throw error;
        }
    }
    async getAdmin(nama, pass, plantId) {
        try {
            return await siiDb('admin_pass')
                .select('*')
                .where({
                    nama,
                    pass,
                    isvalid: 1,
                    plant_id: plantId,
                })
            .first(); // Returns the first matching record or undefined
        } catch (error) {
            console.error('Error in getAdmin:', error.message);
            throw error;
        }
    }
    //====================================================================================================================
	//====================================================================================================================
    //MANIFEST SAMPAH
    //====================================================================================================================
	//====================================================================================================================
    _sampahList(filters, columnSearches) {
        let query = siiDb('manifest_data')
            .select('*')
            .select(siiDb.raw('AVG(proses) as prog'))
            .where({isvalid: 0, plant_id: filters.plantId})
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        query.groupBy('manifest')
        return query
    }
    async sampahList(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._sampahList(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async sampahListFiltered(filters, columnSearches) {
        let query = this._sampahList(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async sampahListCountAll() {
        let query = siiDb('manifest_data').where({isvalid: 0}).groupBy('manifest')
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
    async editManifestByMan(data, manifest, plantId) {
        try {
            await siiDb('manifest_data')
            .where('manifest', manifest)
            .andWhere('plant_id', plantId)
            .update(data);
            return true;
        } catch (error) {
            console.error('Error in editManifestByMan:', error.message);
            throw error;
        }
    }
    async deleteManifestByMan(manifest, plantId) {
        try {
            await siiDb('manifest_data')
            .where('manifest', manifest)
            .andWhere('plant_id', plantId)
            .del();
            return true;
        } catch (error) {
            console.error('Error in deleteManifestByMan:', error.message);
            throw error;
        }
    }
    async deleteManifestByValid(isvalid, plantId) {
        try {
            await siiDb('manifest_data')
            .where('isvalid', isvalid)
            .andWhere('plant_id', plantId)
            .del();

            return true;
        } catch (error) {
            console.error('Error in deleteManifestByValid:', error.message);
            throw error; // Re-throw the error for higher-level handling
        }
    }
    //====================================================================================================================
	//====================================================================================================================
    //GENERAL USE
    //====================================================================================================================
	//====================================================================================================================
    async getManifestTable(manifest, plantId) {
        try {
            // Fetch manifest table data with aggregated `sum_kanban`
            const result = await siiDb('manifest_data')
                .select(
                    '*',
                    siiDb.raw('SUM(qty_kanban) as sum_kanban')
                )
                .where('plant_id', plantId)
                .andWhere('manifest', manifest)
                .groupBy('manifest', 'part_no')
        
            // If no results, return an empty array
            if (!result || result.length === 0) {
                return [];
            }
    
            // Process each row and append scan results
            const processedResults = await Promise.all(
                result.map(async (row) => {
                    const good = await this.getResultManifestScan(row.manifest, row.part_no, 1, plantId);
                    const ng = await this.getResultManifestScan(row.manifest, row.part_no, 0, plantId);
        
                    return [
                        row.order_no,
                        row.part_no,
                        row.unique_no,
                        row.part_name,
                        row.qty_per_kanban,
                        row.sum_kanban,
                        row.qty_per_kanban * row.sum_kanban,
                        good,
                        ng,
                        row.qty_per_kanban * row.sum_kanban - good,
                    ];
                })
            );
    
            return processedResults;
        } catch (error) {
            console.error('Error in getManifestTable:', error.message);
            throw error;
        }
    }
    
    async getResultManifestScan(manifest, part, result, plantId) {
        try {
            // Base query
            const query = siiDb('manifest_data as t1')
                .join('scan_manifest as t2', function () {
                    this.on('t1.manifest', '=', 't2.manifest_id')
                        .andOn('t1.part_no', '=', 't2.scan_part')
                        .andOn('t2.result', '=', siiDb.raw('?', [result]));
                })
                .where('t1.plant_id', plantId)
                .where('t1.manifest', manifest)
                .where('t1.part_no', part)
                .where('t1.isvalid', 1)
                .whereNotNull('t2.scan_part')
                .groupBy('t2.id');
    
          // Execute the query and return the count
            const results = await query;
            return results.length;
        } catch (error) {
            console.error('Error in getResultManifestScan:', error.message);
            throw error;
        }
    }
    //====================================================================================================================
    //log_data_byman
	//====================================================================================================================
    _logDataMan(filters, columnSearches) {
        let query = siiDb('scan_manifest as t1')
            .select('t1.*', 't2.fullname as fullname', 't3.part_name as part_name')
            .leftJoin('db_main.users as t2', 't1.scanby', 't2.id')
            .leftJoin(
                'manifest_data as t3',
                function () {
                    this.on('t1.manifest_id', '=', 't3.manifest')
                        .andOn('t1.scan_part', '=', 't3.part_no');
                }
            )
            .where('t1.plant_id', filters.plantId)
            .andWhere('t1.manifest_id', filters.manifest)
        columnSearches.forEach(search => {
            query.where(search.column, 'like', `%${search.value}%`)
        });
        return query
    }
    async logDataMan(filters, orderColumn, orderDirection, columnSearches) {
        let query = this._logDataMan(filters, columnSearches)
        
        query.orderBy(orderColumn, orderDirection)
        query.limit(filters.length).offset(filters.start)

        const results = await query
        return results
    }

    async logDataManFiltered(filters, columnSearches) {
        let query = this._logDataMan(filters, columnSearches)

        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    async logDataManCountAll(filters) {
        let query = siiDb('scan_manifest').where('plant_id', filters.plantId)
        .andWhere('manifest_id', filters.manifest)
        const result = await query.count('* as total').first()
        return result ? result.total : 0;
    }
    //====================================================================================================================
	//====================================================================================================================
}

module.exports = new siiModel();
