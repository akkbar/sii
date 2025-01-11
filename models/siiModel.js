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


    // var $master_data_order = array(null, 't1.timestamp', 't1.kanban_cus', 't1.kanban_shi', null, 't2.uName', null);
    // var $master_data_search = array('t1.timestamp', 't1.kanban_cus', 't1.kanban_shi', 't1.desc', 't2.uName');
	// var $master_data_def_order = array('t1.timestamp'=> 'desc');
	// private function _get_master_data_dt($plant_id){
	// 	$this->db2 = $this->load->database('codesysdb', TRUE);
	// 	$this->db2->select('t1.*, t2.uName as uName');
	// 	$this->db2->from('shiroki_master_shiroki as t1');
	// 	$this->db2->join('db_tool.users as t2', 't1.addedby = t2.id', 'left');
	// 	$this->db2->where('t1.plant_id', $plant_id);
	// 	$this->db2->where('t1.isvalid', 1);
    //     $i = 0;
    //     foreach ($this->master_data_search as $item){
    //         if($_POST['search']['value']){
    //             if($i===0){
    //                 $this->db2->group_start();
    //                 $this->db2->like($item, $_POST['search']['value']);
    //             }
    //             else{
    //                 $this->db2->or_like($item, $_POST['search']['value']);
    //             }
    //             if(count($this->master_data_search) - 1 == $i)
    //                 $this->db2->group_end();
    //         }
    //         $i++;
    //     }
    //     if(isset($_POST['order'])){
    //         $this->db2->order_by($this->master_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
    //     } 
    //     else if(isset($this->master_data_def_order)){
    //         $order = $this->master_data_def_order;
    //         $this->db2->order_by(key($order), $order[key($order)]);
	// 	}
    // }
    // public function get_master_data_dt($plant_id){
    //     $this->_get_master_data_dt($plant_id);
    //     if($_POST['length'] != -1)
    //     $this->db2->limit($_POST['length'], $_POST['start']);
    //     $query = $this->db2->get();
    //     return $query->result();
    // }
    // public function master_data_count_filtered($plant_id){
    //     $this->_get_master_data_dt($plant_id);
    //     $query = $this->db2->get();
    //     return $query->num_rows();
    // }
    // public function master_data_count_all($plant_id){
	// 	$this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->from('shiroki_master_shiroki');
    //     $this->db2->where('isvalid', 1);
    //     $this->db2->where('plant_id', $plant_id);
    //     return $this->db2->count_all_results();
	// }
    //====================================================================================================================
	//====================================================================================================================

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
