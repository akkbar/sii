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
        .whereRaw("substr(na7, 1, 19) <= ?", [date])
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
        .whereRaw("substr(na7, 1, 19) <= ?", [date])
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

}

module.exports = new siiModel();
