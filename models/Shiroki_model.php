<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Shiroki_model extends CI_Model{
	//====================================================================================================================
	//====================================================================================================================
	var $master_data_order = array(null, 't1.timestamp', 't1.kanban_cus', 't1.kanban_shi', null, 't2.uName', null);
    var $master_data_search = array('t1.timestamp', 't1.kanban_cus', 't1.kanban_shi', 't1.desc', 't2.uName');
	var $master_data_def_order = array('t1.timestamp'=> 'desc');
	private function _get_master_data_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('t1.*, t2.uName as uName');
		if($this->input->post('start_date')){
            $this->db2->where('t1.timestamp >=', $this->input->post('start_date').' 00:00:00');
		}
		if($this->input->post('end_date')){
            $this->db2->where('t1.timestamp <=', $this->input->post('end_date').' 23:59:59');
		}
		$this->db2->from('shiroki_master_shiroki as t1');
		$this->db2->join('db_tool.users as t2', 't1.addedby = t2.id', 'left');
		$this->db2->where('t1.plant_id', $plant_id);
		$this->db2->where('t1.isvalid', 1);
        $i = 0;
        foreach ($this->master_data_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->master_data_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->master_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->master_data_def_order)){
            $order = $this->master_data_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
    }
    public function get_master_data_dt($plant_id){
        $this->_get_master_data_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function master_data_count_filtered($plant_id){
        $this->_get_master_data_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function master_data_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_master_shiroki');
        $this->db2->where('isvalid', 1);
        $this->db2->where('plant_id', $plant_id);
        return $this->db2->count_all_results();
	}
	function add_master_data($array){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->trans_start();
		$this->db2->insert('shiroki_master_shiroki', $array);
		$insert_id = $this->db2->insert_id();
		$this->db2->trans_complete();
		return $insert_id;
	}
	function edit_master_data($array, $id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('id', $id);
		$this->db2->update('shiroki_master_shiroki', $array);
		return TRUE;
	}
	public function get_kanban_cus($kanban_cus, $kanban_shi, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_master_shiroki');
        $this->db2->where('kanban_cus', $kanban_cus);
        $this->db2->where('kanban_shi', $kanban_shi);
        $this->db2->where('isvalid', 1);
        $this->db2->where('plant_id', $plant_id);
		$return = $this->db2->get();
		return $return->row();
	}
	//====================================================================================================================
	//====================================================================================================================
	function add_log($array){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->trans_start();
		$this->db2->insert('shiroki_scan_shiroki', $array);
		$insert_id = $this->db2->insert_id();
		$this->db2->trans_complete();
		return $insert_id;
	}
	//====================================================================================================================
	//====================================================================================================================
	var $log_data_order = array(null, 't1.timestamp', 't1.result', 't1.manifest_id', 't1.scan_part', 't1.scan_shiroki', null, 't2.uName', 't3.part_name', null);
    var $log_data_search = array('t1.timestamp', 't1.manifest_id', 't1.scan_part', 't1.scan_shiroki', 't2.uName', 't1.note',  't3.part_name');
	var $log_data_def_order = array('t1.timestamp'=> 'desc');
	private function _get_log_data_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('t1.*, t2.uName as uName, t3.part_name as part_name');
		$this->db2->from('shiroki_scan_manifest as t1');
		$this->db2->join('db_tool.users as t2', 't1.scanby = t2.id', 'left');
		$this->db2->join('shiroki_manifest_shiroki as t3', 't1.manifest_id = t3.manifest and t1.scan_part = t3.part_no', 'left');
		$this->db2->where('t1.plant_id', $plant_id);
        $i = 0;
        foreach ($this->log_data_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->log_data_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->log_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->log_data_def_order)){
            $order = $this->log_data_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
    }
    public function get_log_data_dt($plant_id){
        $this->_get_log_data_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function log_data_count_filtered($plant_id){
        $this->_get_log_data_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function log_data_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_scan_manifest');
        $this->db2->where('plant_id', $plant_id);
        return $this->db2->count_all_results();
    }
    //====================================================================================================================
    //====================================================================================================================
	//====================================================================================================================
	var $monman_data_order = array(null, 'manifest', null, 'avg(proses)', 'na7', null);
    var $monman_data_search = array('manifest', 'na7', 'dock_code');
	var $monman_data_def_order = array('avg(proses)'=> 'desc');
	private function _get_monman_data_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*, avg(proses) as prog');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('isvalid', 1);
        $this->db2->where('na7 >=', date('Y-m-d H:i:s.000', strtotime('-3 day')));
        $this->db2->where('na7 <=', date('Y-m-d H:i:s.000', strtotime('+3 day')));
        $this->db2->having('avg(proses) <', 100);
        $i = 0;
        foreach ($this->monman_data_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->monman_data_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->monman_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->monman_data_def_order)){
            $order = $this->monman_data_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
        $this->db2->group_by('manifest');
    }
    public function get_monman_data_dt($plant_id){
        $this->_get_monman_data_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function monman_data_count_filtered($plant_id){
        $this->_get_monman_data_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function monman_data_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('isvalid', 1);
        $this->db2->having('avg(proses) <', 100);
        $this->db2->group_by('manifest');
        return $this->db2->count_all_results();
    }
    //====================================================================================================================
    function get_manifest_bypart($part, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('part_no', $part);
        $this->db2->order_by('id', 'desc');
        $this->db2->limit(1);
        $return = $this->db2->get();
		return $return->row();
    }
    function get_manifest_num($manifest, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*, min(proses) as prog');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('manifest', $manifest);
        $this->db2->where('isvalid', 1);
        $this->db2->group_by('manifest');
        $return = $this->db2->get();
		return $return->row();
    }
    function get_tabel_manifest_part_shiroki($manifest, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('t1.*, sum(t1.qty_kanban) as sum_kanban, count(t2.id) as num, t3.kanban_shi');
        $this->db2->from('shiroki_manifest_shiroki as t1');
        $this->db2->join('shiroki_scan_manifest as t2', 't1.manifest = t2.manifest_id and t2.result = 1 and t2.scan_part = t1.part_no', 'left');
        $this->db2->join('shiroki_master_shiroki as t3', 't3.isvalid = 1 and t3.kanban_cus = t1.part_no and t3.plant_id = t1.plant_id and t3.kanban_shi like concat("%-", t1.unique_no)', 'left');
        $this->db2->where('t1.plant_id', $plant_id);
        $this->db2->where('t1.manifest', $manifest);
        $this->db2->where('t1.isvalid', 1);
        $this->db2->group_by('t1.part_no');
        $return = $this->db2->get();
		return $return->result();
    }
    function get_manifest_part($manifest, $part, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('manifest, part_no, part_nox, unique_no, qty_per_kanban, sum(qty_kanban*qty_per_kanban) as sum_kanban, avg(proses) as avg_proses');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('manifest', $manifest);
        $this->db2->group_start();
        $this->db2->where('part_no', $part);
        $this->db2->or_where('part_nox', $part);
        $this->db2->group_end();
        $this->db2->where('isvalid', 1);
        $return = $this->db2->get();
        $res1 = $return->row();
        $arr = array();
        $arr['manifest'] = null;
        $arr['part_no'] = null;
        $arr['part_nox'] = null;
        $arr['qty_per_kanban'] = null;
        $arr['sum_kanban'] = null;
        $arr['avg_proses'] = null;
        $arr['unique_no'] = 'xxxxxxxx';
        if(!empty($res1)){
            $arr['manifest'] = $res1->manifest;
            $arr['part_no'] = $res1->part_no;
            $arr['part_nox'] = $res1->part_nox;
            $arr['qty_per_kanban'] = $res1->qty_per_kanban;
            $arr['sum_kanban'] = $res1->sum_kanban;
            $arr['avg_proses'] = $res1->avg_proses;
            $arr['unique_no'] = $res1->unique_no;
        }
        $this->db2->select('*');
        $this->db2->from('shiroki_scan_manifest');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('manifest_id', $manifest);
        $this->db2->where('scan_part', $arr['part_no']);
        $this->db2->like('scan_shiroki', '-'.$arr['unique_no'], 'before');
        $this->db2->where('result', 1);
        $return = $this->db2->get();
		$res2 = $return->num_rows();
        $arr['good'] = $res2;
        return $arr;
    }
    function get_manifest_part_shiroki($manifest, $part, $shiroki, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('unique_no, manifest, part_no, part_nox, qty_per_kanban, sum(qty_kanban*qty_per_kanban) as sum_kanban, avg(proses) as avg_proses');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('manifest', $manifest);
        $this->db2->group_start();
        $this->db2->where('part_no', $part);
        $this->db2->or_where('part_nox', $part);
        $this->db2->group_end();
        $this->db2->where('isvalid', 1);
        $return = $this->db2->get();
        $res1 = $return->row();
        $arr = array();
        $arr['manifest'] = null;
        $arr['part_no'] = null;
        $arr['part_nox'] = null;
        $arr['qty_per_kanban'] = null;
        $arr['sum_kanban'] = null;
        $arr['avg_proses'] = null;
        $arr['unique_no'] = 'xxxxxxxxxxx';
        if(!empty($res1)){
            $arr['manifest'] = $res1->manifest;
            $arr['part_no'] = $res1->part_no;
            $arr['part_nox'] = $res1->part_nox;
            $arr['qty_per_kanban'] = $res1->qty_per_kanban;
            $arr['sum_kanban'] = $res1->sum_kanban;
            $arr['avg_proses'] = $res1->avg_proses;
            $arr['unique_no'] = $res1->unique_no;
        }
        // echo var_dump($arr);
        
        $this->db2->select('*');
        $this->db2->from('shiroki_scan_manifest');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('manifest_id', $manifest);
        $this->db2->where('scan_part', $arr['part_no']);
        $this->db2->like('scan_shiroki', '-'.$arr['unique_no'], 'before');
        $this->db2->where('result', 1);
        $return = $this->db2->get();
		$res2 = $return->num_rows();
        $arr['good'] = $res2;
        
        $this->db2->select('*');
        $this->db2->from('shiroki_master_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('kanban_cus', $arr['part_no']);
        $this->db2->where('isvalid', 1);
        $this->db2->like('kanban_shi', '-'.$arr['unique_no'], 'before');
        $this->db2->where('kanban_shi', $shiroki);
        $return = $this->db2->get();
        $arr['shi'] = $return->num_rows();
        
        return $arr;
    }
    function get_manifest_table($manifest, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*, sum(qty_kanban) as sum_kanban');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('manifest', $manifest);
        $this->db2->group_by('manifest');
        $this->db2->group_by('part_no');
        $return = $this->db2->get();
        $result = $return->result();
        $array = array();
        if(!empty($result)){
            foreach($result as $row){
                $cek = array();
                $cek[] = $row->order_no;
                $cek[] = $row->part_no;
                $cek[] = $row->unique_no;
                $cek[] = $row->part_name;
                $cek[] = $row->qty_per_kanban;
                $cek[] = $row->sum_kanban;
                $cek[] = $row->qty_per_kanban*$row->sum_kanban;
                $good = $this->get_result_manifest_scan($row->manifest, $row->part_no, 1, $row->plant_id);
                $ng = $this->get_result_manifest_scan($row->manifest, $row->part_no, 0, $row->plant_id);
                $cek[] = $good;
                $cek[] = $ng;
                $cek[] = ($row->qty_per_kanban*$row->sum_kanban)-$good;
                $array[] = $cek;
            }
        }
        return $array;
    }
    function get_result_manifest_scan($manifest, $part, $result, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*');
        $this->db2->from('shiroki_manifest_shiroki as t1');
        if($result == 0){
            $this->db2->join('shiroki_scan_manifest as t2', 't1.manifest = t2.manifest_id and t2.result = 0 and (t1.part_no = t2.scan_part or t1.part_nox = t2.scan_part)', 'left');
        }else{
            $this->db2->join('shiroki_scan_manifest as t2', 't1.manifest = t2.manifest_id and t2.result = 1 and (t1.part_no = t2.scan_part or t1.part_nox = t2.scan_part)', 'left');
        }
        $this->db2->where('t1.plant_id', $plant_id);
        $this->db2->where('t1.manifest', $manifest);
        $this->db2->where('t1.part_no', $part);
        $this->db2->where('t1.isvalid', 1);
        $this->db2->where('t2.scan_part is not null');
        $this->db2->group_by('t2.id');
        $return = $this->db2->get();
		return $return->num_rows();
    }
    function get_manifest_bydata($manifest, $part_no, $unique_no, $qty_kanban, $multi, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('manifest', $manifest);
        $this->db2->where('part_no', $part_no);
        $this->db2->where('unique_no', $unique_no);
        $this->db2->where('qty_kanban', $qty_kanban);
        $this->db2->where('multi', $multi);
        $this->db2->where('plant_id', $plant_id);
		$return = $this->db2->get();
		return $return->row();
    }
    function add_manifest_scan_data($array){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->trans_start();
		$this->db2->insert('shiroki_scan_manifest', $array);
		$insert_id = $this->db2->insert_id();
		$this->db2->trans_complete();
		return $insert_id;
    }
    function edit_manifest_scan_data($array, $id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('id', $id);
		$this->db2->update('shiroki_scan_manifest', $array);
		return TRUE;
    }
    function add_manifest_data($array){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->trans_start();
		$this->db2->insert('shiroki_manifest_shiroki', $array);
		$insert_id = $this->db2->insert_id();
		$this->db2->trans_complete();
		return $insert_id;
    }
    function delete_manifest_data($id){
		$this->db2->where('id', $id);
        $this->db2->delete('shiroki_manifest_shiroki');
        return true;
	}
	function edit_manifest_data($array, $id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('id', $id);
		$this->db2->update('shiroki_manifest_shiroki', $array);
		return TRUE;
    }
    function bulkedit_manifest_data($array, $manifest, $part_no, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('manifest', $manifest);
		$this->db2->where('part_no', $part_no);
		$this->db2->where('plant_id', $plant_id);
		$this->db2->update('shiroki_manifest_shiroki', $array);
		return TRUE;
    }
    function edit_manifest_byman($array, $manifest, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('manifest', $manifest);
		$this->db2->where('plant_id', $plant_id);
		$this->db2->update('shiroki_manifest_shiroki', $array);
		return TRUE;
    }
    
    function delete_manifest_byman($manifest, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('manifest', $manifest);
		$this->db2->where('plant_id', $plant_id);
		$this->db2->delete('shiroki_manifest_shiroki');
		return TRUE;
    }
    
    function delete_manifest_byvalid($isvalid, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('isvalid', $isvalid);
		$this->db2->where('plant_id', $plant_id);
		$this->db2->delete('shiroki_manifest_shiroki');
		return TRUE;
    }
    
    //====================================================================================================================
	//====================================================================================================================
	var $manifest_data_order = array(null, 't1.proses', 't1.manifest', 't1.supplier_code', 't1.na1', 't1.na2', 't1.na3', 't1.dock_code', 't1.order_no', 't1.part_no', 't1.prodline_tmmin', 't1.na4', 't1.na5', 't1.unique_no', 't1.qty_per_kanban', 't1.qty_order', 't1.qty_kanban', 't1.supplier', 't1.part_name', 't1.na6', 't1.na7', 't1.na8', 't1.na9','t1.part_nox', 't1.multi', 't1.submit_time', 't1.update_time', 't2.uName');
    var $manifest_data_search = array('t1.manifest', 't1.supplier_code', 't1.na1', 't1.na2', 't1.na3', 't1.dock_code', 't1.order_no', 't1.part_no', 't1.prodline_tmmin', 't1.na4', 't1.na5', 't1.unique_no', 't1.qty_per_kanban', 't1.qty_order', 't1.qty_kanban', 't1.supplier', 't1.part_name', 't1.na6', 't1.na7', 't1.na8', 't1.na9', 't1.submit_time', 't1.update_time', 't2.uName');
	var $manifest_data_def_order = array('t1.submit_time'=> 'desc');
	private function _get_manifest_data_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('t1.*, t2.uName as uName');
		$this->db2->from('shiroki_manifest_shiroki as t1');
		$this->db2->join('db_tool.users as t2', 't1.user_id = t2.id', 'left');
		$this->db2->where('t1.plant_id', $plant_id);
        $this->db2->where('isvalid', 1);
        $i = 0;
        foreach ($this->manifest_data_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->manifest_data_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->manifest_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->manifest_data_def_order)){
            $order = $this->manifest_data_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
    }
    public function get_manifest_data_dt($plant_id){
        $this->_get_manifest_data_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function manifest_data_count_filtered($plant_id){
        $this->_get_manifest_data_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function manifest_data_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('isvalid', 1);
        return $this->db2->count_all_results();
	}
	//====================================================================================================================
	//====================================================================================================================
//====================================================================================================================
	//====================================================================================================================
	var $user_data_order = array(null, 't1.uName', 't1.username', 't2.role_id', null);
    var $user_data_search = array('t1.uName');
	var $user_data_def_order = array('t1.uName'=> 'asc');
	private function _get_user_data_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('t1.*, t2.role_id as role_id');
		$this->db2->from('db_tool.users as t1');
		$this->db2->join('shiroki_role_set as t2', 't1.id = t2.user_id', 'left');
		$this->db2->where('t1.plant_id', $plant_id);
		$this->db2->where('t1.uFlag', 1);
        $i = 0;
        foreach ($this->user_data_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->user_data_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->user_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->user_data_def_order)){
            $order = $this->user_data_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
    }
    public function get_user_data_dt($plant_id){
        $this->_get_user_data_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function user_data_count_filtered($plant_id){
        $this->_get_user_data_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function user_data_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('db_tool.users');
        $this->db2->where('uFlag', 1);
        $this->db2->where('plant_id', $plant_id);
        return $this->db2->count_all_results();
	}
	function add_user_data($array){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->trans_start();
		$this->db2->insert('shiroki_role_set', $array);
		$insert_id = $this->db2->insert_id();
		$this->db2->trans_complete();
		return $insert_id;
	}
	function edit_user_data($array, $id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('user_id', $id);
		$this->db2->update('shiroki_role_set', $array);
		return TRUE;
	}
	public function get_user_role($user_id, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('t1.*, t2.role_id');
        $this->db2->from('db_tool.users as t1');
        $this->db2->join('shiroki_role_set as t2', 't1.id = t2.user_id', 'left');
        $this->db2->where('t1.id', $user_id);
        $this->db2->where('t1.plant_id', $plant_id);
		$return = $this->db2->get();
		return $return->row();
	}
	//====================================================================================================================
    //====================================================================================================================
    //====================================================================================================================
	//====================================================================================================================
	var $manifest_log_data_order = array(null, 't1.manifest', 't1.order_no', 'avg(t1.proses)', 't1.na7', 't1.dock_code', null);
    var $manifest_log_data_search = array('t1.manifest', 't1.order_no');
	var $manifest_log_data_def_order = array('t1.manifest'=> 'desc');
	private function _get_manifest_log_data_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('t1.*, avg(t1.proses) as prog');
		$this->db2->from('shiroki_manifest_shiroki as t1');
		$this->db2->where('t1.plant_id', $plant_id);
		$this->db2->where('t1.isvalid', 1);
        $i = 0;
        foreach ($this->manifest_log_data_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->manifest_log_data_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->manifest_log_data_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->manifest_log_data_def_order)){
            $order = $this->manifest_log_data_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
        $this->db2->group_by('t1.manifest');
    }
    public function get_manifest_log_data_dt($plant_id){
        $this->_get_manifest_log_data_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function manifest_log_data_count_filtered($plant_id){
        $this->_get_manifest_log_data_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function manifest_log_data_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
		$this->db2->where('isvalid', 1);
        $this->db2->group_by('manifest');
        return $this->db2->count_all_results();
    }
    //====================================================================================================================
    //====================================================================================================================
    
    
	var $log_data_byman_order = array(null, 't1.timestamp', 't1.result', 't1.scan_part', 't1.scan_shiroki', 't2.uName', 't3.part_name');
    var $log_data_byman_search = array('t1.timestamp', 't1.scan_part', 't1.scan_shiroki', 't2.uName', 't3.part_name');
	var $log_data_byman_def_order = array('t1.timestamp'=> 'asc');
	private function _get_log_data_byman_dt($manifest, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('t1.*, t2.uName as uName, t3.part_name as part_name');
		$this->db2->from('shiroki_scan_manifest as t1');
		$this->db2->join('db_tool.users as t2', 't1.scanby = t2.id', 'left');
		$this->db2->join('shiroki_manifest_shiroki as t3', 't1.manifest_id = t3.manifest and t1.scan_part = t3.part_no', 'left');
		$this->db2->where('t1.plant_id', $plant_id);
		$this->db2->where('t1.manifest_id', $manifest);
        $i = 0;
        foreach ($this->log_data_byman_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->log_data_byman_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->log_data_byman_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->log_data_byman_def_order)){
            $order = $this->log_data_byman_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
    }
    public function get_log_data_byman_dt($manifest, $plant_id){
        $this->_get_log_data_byman_dt($manifest, $plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function log_data_byman_count_filtered($manifest, $plant_id){
        $this->_get_log_data_byman_dt($manifest, $plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function log_data_byman_count_all($manifest, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_scan_manifest');
        $this->db2->where('plant_id', $plant_id);
		$this->db2->where('manifest_id', $manifest);
        return $this->db2->count_all_results();
    }
    
    
    function monitor_scan($plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('t1.*, t2.uName as uName');
        $this->db2->from('shiroki_scan_manifest as t1');
        $this->db2->join('db_tool.users as t2', 't1.scanby = t2.id');
        $this->db2->where('t1.plant_id', $plant_id);
        $this->db2->order_by('t1.timestamp', 'desc');
        $this->db2->limit(30);
        $return = $this->db2->get();
		return $return->result();
    }
    //====================================================================================================================
	//====================================================================================================================
	var $alarm_order = array(null, 'unit_name', 'unit_ip', 'unit_port', null);
    var $alarm_search = array('unit_name', 'unit_ip');
	var $alarm_def_order = array('unit_name'=> 'asc');
	private function _get_alarm_dt($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->select('*');
		$this->db2->from('shiroki_alarm_module');
		$this->db2->where('plant_id', $plant_id);
		$this->db2->where('isvalid', 1);
        $i = 0;
        foreach ($this->alarm_search as $item){
            if($_POST['search']['value']){
                if($i===0){
                    $this->db2->group_start();
                    $this->db2->like($item, $_POST['search']['value']);
                }
                else{
                    $this->db2->or_like($item, $_POST['search']['value']);
                }
                if(count($this->alarm_search) - 1 == $i)
                    $this->db2->group_end();
            }
            $i++;
        }
        if(isset($_POST['order'])){
            $this->db2->order_by($this->alarm_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
        } 
        else if(isset($this->alarm_def_order)){
            $order = $this->alarm_def_order;
            $this->db2->order_by(key($order), $order[key($order)]);
		}
    }
    public function get_alarm_dt($plant_id){
        $this->_get_alarm_dt($plant_id);
        if($_POST['length'] != -1)
        $this->db2->limit($_POST['length'], $_POST['start']);
        $query = $this->db2->get();
        return $query->result();
    }
    public function alarm_count_filtered($plant_id){
        $this->_get_alarm_dt($plant_id);
        $query = $this->db2->get();
        return $query->num_rows();
    }
    public function alarm_count_all($plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->from('shiroki_alarm_module');
        $this->db2->where('plant_id', $plant_id);
		$this->db2->where('isvalid', 1);
        return $this->db2->count_all_results();
    }
    //====================================================================================================================
    function edit_alarm_data($array, $id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('id', $id);
		$this->db2->update('shiroki_alarm_module', $array);
		return TRUE;
    }
    function edit_alarm_byip($array, $id, $plant_id){
		$this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->where('unit_ip', $id);
		$this->db2->where('plant_id', $plant_id);
		$this->db2->update('shiroki_alarm_module', $array);
		return TRUE;
    }
    function replace_alarm($array){
        $this->db2 = $this->load->database('codesysdb', TRUE);
		$this->db2->replace('shiroki_alarm_module', $array);
		return TRUE;
    }
    public function get_alarm_module($id, $plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*');
        $this->db2->from('shiroki_alarm_module');
        $this->db2->where('id', $id);
        $this->db2->where('plant_id', $plant_id);
		$this->db2->where('isvalid', 1);
        $query = $this->db2->get();
        return $query->row();
    }
    public function get_alarm_list($plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*');
        $this->db2->from('shiroki_alarm_module');
        $this->db2->where('plant_id', $plant_id);
		$this->db2->where('isvalid', 1);
        $query = $this->db2->get();
        return $query->result();
    }
    function scan_3hari($plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*, avg(proses) as prog');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('isvalid', 1);
        $this->db2->where('submit_time', date('Y-m-d H:i:s', strtotime('-3 day')));
        $this->db2->having('avg(proses) =', 100);
        $this->db2->group_by('manifest');
        $this->db2->group_by('plant_id');
        $query = $this->db2->get();
        return $query->result();
    }
    function scan_5hari($plant_id){
        $this->db2 = $this->load->database('codesysdb', TRUE);
        $this->db2->select('*, avg(proses) as prog');
        $this->db2->from('shiroki_manifest_shiroki');
        $this->db2->where('plant_id', $plant_id);
        $this->db2->where('isvalid', 0);
        $this->db2->where('submit_time', date('Y-m-d H:i:s', strtotime('-5 day')));
        $this->db2->group_by('manifest');
        $this->db2->group_by('plant_id');
        $query = $this->db2->get();
        return $query->result();
    }
    
}

?>
