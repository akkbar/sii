const siiDb = require('../models/siiDb');


class siiModel {
    // function count_open_manifest($plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->select('*');
    //     $this->db2->from('shiroki_manifest_shiroki');
    //     $this->db2->where('plant_id', $plant_id);
    //     $this->db2->where('proses <', 100);
    //     $this->db2->where('isvalid', 1);
    //     $this->db2->group_by('manifest');
    //     $return = $this->db2->get();
	// 	return $return->num_rows();
    // }
    // function count_all_manifest($plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->select('*');
    //     $this->db2->from('shiroki_manifest_shiroki');
    //     $this->db2->where('plant_id', $plant_id);
    //     $this->db2->where('isvalid', 1);
    //     $this->db2->group_by('manifest');
    //     $return = $this->db2->get();
	// 	return $return->num_rows();
    // }
    // function count_all_user($plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->select('*');
    //     $this->db2->from('db_tool.users');
    //     $this->db2->where('plant_id', $plant_id);
    //     $this->db2->where('uFlag', 1);
    //     $return = $this->db2->get();
	// 	return $return->num_rows();
    // }
    // function count_all_kanban($plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->select('*');
    //     $this->db2->from('shiroki_master_shiroki');
    //     $this->db2->where('plant_id', $plant_id);
    //     $return = $this->db2->get();
	// 	return $return->num_rows();
    // }
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
    // function delete_manifest_3hari($date, $plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
	// 	$this->db2->where('substr(na7, 1, 19) <=', $date);
	// 	$this->db2->where('plant_id', $plant_id);
	// 	$this->db2->delete('shiroki_manifest_shiroki');
	// 	return TRUE;
    // }
    // function delete_scan_7hari($date, $plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->where('timestamp <=', $date);
    //     $this->db2->where('plant_id', $plant_id);
    //     $this->db2->delete('shiroki_scan_manifest');
    //     return TRUE;
    // }
    // function edit_manifest_5hari($array, $date, $plant_id){
	// 	$this->db2 = $this->load->database('codesysdb', TRUE);
	// 	$this->db2->where('substr(na7, 1, 19) <=', $date);
	// 	$this->db2->where('plant_id', $plant_id);
	// 	$this->db2->update('shiroki_manifest_shiroki', $array);
	// 	return TRUE;
    // }
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
    // function monitor_manifest($plant_id){
    //     $this->db2 = $this->load->database('codesysdb', TRUE);
    //     $this->db2->select('*, avg(proses) as prog');
    //     $this->db2->from('shiroki_manifest_shiroki');
    //     $this->db2->where('plant_id', $plant_id);
    //     $this->db2->where('proses <', 100);
    //     $this->db2->where('isvalid', 1);
    //     $this->db2->group_by('manifest');
    //     $this->db2->limit(10);
    //     $return = $this->db2->get();
    //     return $return->result();
    // }
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
    //MANIFEST SAMPAH
    //====================================================================================================================
	//====================================================================================================================
    _sampahList(filters, columnSearches) {
        let query = siiDb('manifest_data')
            .select('*')
            .select(siiDb.raw('AVG(proses) as prog'))
            .where({isvalid: 0})
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

}

module.exports = new siiModel();
