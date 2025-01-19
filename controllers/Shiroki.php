<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/BaseController.php';
class Shiroki extends BaseController{
	public function __construct(){
		parent::__construct();
		$this->load->model('shiroki_model');
		$this->load->model('shiroki_excel_model');
		$this->load->model('encrypt_model');
		$this->isLoggedIn();   
	}
	public function cek_shi(){
		echo var_dump(ini_get('max_input_vars'));
	}
	public function shiroki_dash(){
		$data['open_manifest'] = $this->shiroki_model->count_open_manifest($this->plant_id);
		$data['all_manifest'] = $this->shiroki_model->count_all_manifest($this->plant_id);
		$data['all_user'] = $this->shiroki_model->count_all_user($this->plant_id);
		$data['all_kanban'] = $this->shiroki_model->count_all_kanban($this->plant_id);
		$data['x'] = $this->shiroki_auto_remove();
		$this->global['pageTitle'] = 'Dashboard';
		$this->loadViews("shiroki/shiroki_dash", $this->global, $data, NULL);
	}
	function shiroki_monitor_manifest(){ 
		$data['manifest_table'] = $this->shiroki_model->monitor_manifest($this->plant_id);
		$this->load->view('shiroki/shiroki_monitor_manifest', $data);
	}
	function shiroki_monitor_manifest_ajax(){
		$list = $this->shiroki_model->get_monman_data_dt($this->plant_id);
		$data = array();
		$class_row = array();
        $no = $_POST['start'];
        foreach ($list as $record){
			$no++;
            $row = array();
			$row[] = $no;
			$row[] = $record->manifest;
			$row[] = round($record->prog, 1);
			$row[] = '<div class="progress mb-3">
					<div class="progress-bar bg-success" role="progressbar" aria-valuenow="'.round($record->prog).'" aria-valuemin="0" aria-valuemax="100" style="width: '.round($record->prog, 1).'%">
						<span class="sr-only">'.round($record->prog, 1).'%</span>
					</div>
				</div>';
			$row[] = date('d-m-Y H:i', strtotime(substr($record->na7, 0, -4)));
			$row[] = $record->dock_code;
			$data[] = $row;
        }
        $output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->shiroki_model->monman_data_count_all($this->plant_id),
			"recordsFiltered" => $this->shiroki_model->monman_data_count_filtered($this->plant_id),
			"data" => $data
		);
        echo json_encode($output);	
	}
	function shiroki_monitor_scan(){
		$data['manifest_scan'] = $this->shiroki_model->monitor_scan($this->plant_id);
		$this->load->view('shiroki/shiroki_monitor_scan', $data);
	}
	
	// function shiroki_log_data(){
	// 	$this->global['pageTitle'] = 'Log Data';
	// 	$this->loadViews("shiroki/shiroki_log_data", $this->global, NULL, NULL);
	// }
	// function shiroki_log_data_ajax(){
	// 	$list = $this->shiroki_model->get_log_data_dt($this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
	// 		$xid = $this->encrypt_model->my_encrypt($record->id);
    //         $row = array();
	// 		$row[] = $no;
	// 		$row[] = $record->timestamp;
	// 		if($record->result == 1){
	// 			$row[] = '<span class="badge badge-success">Scan Berhasil</span>';
	// 		}elseif($record->result == 2){
	// 			$row[] = '<span class="badge badge-warning">Scan Customer OK</span>';
	// 		}elseif($record->result == 0 and empty($record->scan_shiroki)){
	// 			$row[] = '<span class="badge badge-danger">Scan Customer Gagal</span>';
	// 		}elseif($record->result == 0 and !empty($record->scan_shiroki)){
	// 			$row[] = '<span class="badge badge-danger">Scan Shiroki Gagal</span>';
	// 		}
	// 		$row[] = $record->manifest_id;
	// 		$row[] = $record->scan_part;
	// 		$row[] = $record->scan_shiroki;
	// 		$row[] = $record->note;
	// 		$row[] = $record->uName;
	// 		$row[] = $record->part_name;
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->log_data_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->log_data_count_filtered($this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
	function shiroki_cek_part_name(){
		header("Content-type: text/json");
		$kode_part = $this->input->post('part_name');
		$part = $this->shiroki_model->get_manifest_bypart($kode_part, $this->plant_id);
		if(!empty($part)){
			echo json_encode($part->part_name);
		}else{
			echo json_encode('');
		}
	}
	function shiroki_master_input(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->useradmin > 0){
				$this->global['pageTitle'] = 'Master Data Input';
				$this->loadViews("shiroki/shiroki_master_input", $this->global, NULL, NULL);
			}else{
				$this->loadThis('Fitur ini hanya dapat diakses admin');
			}
		}else{
			$this->loadThis('Fitur ini hanya dapat diakses admin');
		}
	}
	function shiroki_master_submit(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->useradmin > 0){
				$val = $this->input->post('val');
				if(!empty($val)){
					foreach($val as $array){
						if(!empty($array[0]) and !empty($array[1])){
							$kanban_cus = $array[0];
							$kanban_shi = $array[1];
							$input_array = array(
								'kanban_cus'=>$kanban_cus,
								'kanban_shi'=>$kanban_shi,
								'addedby'=>$this->user_id,
								'plant_id'=>$this->plant_id
							);
							$cek = $this->shiroki_model->get_kanban_cus($kanban_cus, $kanban_shi, $this->plant_id);
							if(!empty($cek)){
								$update = $this->shiroki_model->edit_master_data($input_array, $cek->id);
							}else{
								$insert = $this->shiroki_model->add_master_data($input_array);
							}
						}
					}
				}
				redirect('shiroki_master_data');
			}else{
				$this->loadThis('Fitur ini hanya dapat diakses admin');
			}
		}else{
			$this->loadThis('Fitur ini hanya dapat diakses admin');
		}
	}
	function shiroki_master_data(){
		$this->global['pageTitle'] = 'Data Kanban';
		$this->loadViews("shiroki/shiroki_master_data", $this->global, NULL, NULL);
	}
	function shiroki_master_data_ajax(){
		$list = $this->shiroki_model->get_master_data_dt($this->plant_id);
		$data = array();
        $no = $_POST['start'];
        foreach ($list as $record){
			$no++;
			$xid = $this->encrypt_model->my_encrypt($record->id);
            $row = array();
			$row[] = $no;
			$row[] = $record->timestamp;
			$row[] = $record->kanban_cus;
			$row[] = $record->kanban_shi;
			$row[] = $record->desc;
			$row[] = $record->uName;
			$row[] = '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#masterdata'.$no.'" title="Update Data"><i class="fa fa-pencil-alt"></i> Update</button>
			<div class="modal fade" id="masterdata'.$no.'">
				<div class="modal-dialog">
				<form action="'.base_url().'shiroki_update_master_data" method="POST">
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
										<td><input type="text" class="form-control" value="'.$record->kanban_cus.'" onkeyup="cek_part_name(this)" name="kanban_cus" required></td>
									</tr>
									<tr>
										<th>Kanban Shiroki</th>
										<td><input type="text" class="form-control" value="'.$record->kanban_shi.'" name="kanban_shi" required></td>
									</tr>
									<tr>
										<th>Deskripsi</th>
										<td><textarea name="desc" class="form-control partname" required readonly>'.$record->desc.'</textarea><span class="note"></span></td>
									</tr>
									<tr>
										<th>Delete?</th>
										<td><select name="isvalid" class="form-control"><option value="1">No</option><option value="0">Yes</option></select></td>
									</tr>
								</table>
							</div>
						</div>
						<div class="modal-footer justify-content-between">
							<input type="hidden" name="id" value="'.$xid.'">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Update</button>
						</div>
					</div>
				</form>
				</div>
			</div>';
            $data[] = $row;
        }
        $output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->shiroki_model->master_data_count_all($this->plant_id),
			"recordsFiltered" => $this->shiroki_model->master_data_count_filtered($this->plant_id),
			"data" => $data,
		);
        echo json_encode($output);	
	}
	function shiroki_update_master_data(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->useradmin > 0){
				$kanban_cus = $this->input->post('kanban_cus');
				$kanban_shi = $this->input->post('kanban_shi');
				$desc = $this->input->post('desc');
				$isvalid = $this->input->post('isvalid');
				$id = $this->encrypt_model->decrypt20($this->input->post('id'));
				$array = array(
					'kanban_cus'=>$kanban_cus,
					'kanban_shi'=>$kanban_shi,
					'desc'=>$desc,
					'isvalid'=>$isvalid,
					'addedby'=>$this->user_id,
					'timestamp'=>date('Y-m-d H:i:s')
				);
				$insert = $this->shiroki_model->edit_master_data($array, $id);
				redirect('shiroki_master_data');
			}else{
				$this->loadThis('Fitur ini hanya dapat diakses admin');
			}
		}else{
			$this->loadThis('Fitur ini hanya dapat diakses admin');
		}
	}
	function shiroki_tambah_master_data(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->useradmin > 0){
				$kanban_cus = $this->input->post('kanban_cus');
				$kanban_shi = $this->input->post('kanban_shi');
				$desc = $this->input->post('desc');
				if(!empty($kanban_cus) and !empty($kanban_shi)){
					$input_array = array(
						'kanban_cus'=>$kanban_cus,
						'kanban_shi'=>$kanban_shi,
						'desc'=>$desc,
						'addedby'=>$this->user_id,
						'plant_id'=>$this->plant_id
					);
					$cek = $this->shiroki_model->get_kanban_cus($kanban_cus, $kanban_shi, $this->plant_id);
					if(!empty($cek)){
						$update = $this->shiroki_model->edit_master_data($input_array, $cek->id);
					}else{
						$insert = $this->shiroki_model->add_master_data($input_array);
					}
				}
				redirect('shiroki_master_data');
			}else{
				$this->loadThis('Fitur ini hanya dapat diakses admin');
			}
		}else{
			$this->loadThis('Fitur ini hanya dapat diakses admin');
		}
	}


	// function shiroki_manifest_input(){
	// 	$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
	// 	if(!empty($user_role)){
	// 		if($user_role->role_id == 1 or $user_role->useradmin > 0){
	// 			$this->global['pageTitle'] = 'Manifest Data Input';
	// 			$this->loadViews("shiroki/shiroki_manifest_input", $this->global, NULL, NULL);
	// 		}else{
	// 			$this->loadThis('Hanya Admin yang dapat mengakses fitur ini.');
	// 		}
	// 	}else{
	// 		$this->loadThis('Nampaknya anda seorang hacker...');
	// 	}
	// }
	// function shiroki_manifest_submit(){
	// 	$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
	// 	if(!empty($user_role)){
	// 		if($user_role->role_id == 1 or $user_role->useradmin > 0){
	// 			$manifest = $this->input->post('manifest');
	// 			$supplier_code = $this->input->post('supplier_code');
	// 			$na1 = $this->input->post('na1');
	// 			$na2 = $this->input->post('na2');
	// 			$na3 = $this->input->post('na3');
	// 			$dock_code = $this->input->post('dock_code');
	// 			$order_no = $this->input->post('order_no');
	// 			$part_no = $this->input->post('part_no');
	// 			$prodline_tmmin = $this->input->post('prodline_tmmin');
	// 			$na4 = $this->input->post('na4');
	// 			$na5 = $this->input->post('na5');
	// 			$unique_no = $this->input->post('unique_no');
	// 			$qty_per_kanban = $this->input->post('qty_per_kanban');
	// 			$qty_order = $this->input->post('qty_order');
	// 			$qty_kanban = $this->input->post('qty_kanban');
	// 			$supplier = $this->input->post('supplier');
	// 			$part_name = $this->input->post('part_name');
	// 			$na6 = $this->input->post('na6');
	// 			$na7 = $this->input->post('na7');
	// 			$na8 = $this->input->post('na8');
	// 			$na9 = $this->input->post('na9');
	// 			$part_nox = $this->input->post('part_nox');
	// 			$multi = $this->input->post('multi');
	// 			$a = 0;
	// 			if(!empty($manifest)){
	// 				foreach($manifest as $manif){
	// 					$input_array = array(
	// 						'manifest'=>$manifest[$a],
	// 						'supplier_code'=>$supplier_code[$a],
	// 						'na1'=>$na1[$a],
	// 						'na2'=>$na2[$a],
	// 						'na3'=>$na3[$a],
	// 						'dock_code'=>$dock_code[$a],
	// 						'order_no'=>$order_no[$a],
	// 						'part_no'=>$part_no[$a],
	// 						'prodline_tmmin'=>$prodline_tmmin[$a],
	// 						'na4'=>$na4[$a],
	// 						'na5'=>$na5[$a],
	// 						'unique_no'=>$unique_no[$a],
	// 						'qty_per_kanban'=>$qty_per_kanban[$a],
	// 						'qty_order'=>$qty_order[$a],
	// 						'qty_kanban'=>$qty_kanban[$a],
	// 						'supplier'=>$supplier[$a],
	// 						'part_name'=>$part_name[$a],
	// 						'na6'=>$na6[$a],
	// 						'na7'=>$na7[$a],
	// 						'na8'=>$na8[$a],
	// 						'na9'=>$na9[$a],
	// 						'part_nox'=>$part_nox[$a],
	// 						'multi'=>$multi[$a],
	// 						'user_id'=>$this->user_id,
	// 						'plant_id'=>$this->plant_id
	// 					);
	// 					$cek = $this->shiroki_model->get_manifest_bydata($manifest[$a], $part_no[$a], $unique_no[$a], $qty_kanban[$a], $multi[$a], $this->plant_id);
	// 					if(!empty($cek)){
	// 						$update = $this->shiroki_model->delete_manifest_data($cek->id);
	// 						$insert = $this->shiroki_model->add_manifest_data($input_array);
	// 					}else{
	// 						$insert = $this->shiroki_model->add_manifest_data($input_array);
	// 					}
	// 					$a++;
	// 				}
	// 			}
	// 			redirect('shiroki_manifest_data');
	// 		}else{
	// 			$this->loadThis('Hanya Admin yang dapat mengakses fitur ini.');
	// 		}
	// 	}else{
	// 		$this->loadThis('Nampaknya anda seorang hacker...');
	// 	}
	// }
	// function shiroki_manifest_data(){
	// 	$this->global['pageTitle'] = 'Manifest Data';
	// 	$this->loadViews("shiroki/shiroki_manifest_data", $this->global, NULL, NULL);
	// }
	// function shiroki_manifest_ajax(){
	// 	$list = $this->shiroki_model->get_manifest_data_dt($this->plant_id);
	// 	$data = array();
	// 	$res = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
	// 		$xid = $this->encrypt_model->my_encrypt($record->manifest);
    //         $row = array();
	// 		$row[] = $no;
	// 		$row[] = $record->proses + 0;
	// 		$aa = $record->manifest;
	// 		//if(date('U') > date('U', strtotime(substr($record->na7, 0, -4)))){
	// 			$aa .= ' <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#masterdata'.$no.'" title="Buang ke Tempat Sampah"><i class="fa fa-trash-alt"></i></button>
	// 			<div class="modal fade" id="masterdata'.$no.'">
	// 				<div class="modal-dialog">
	// 				<form action="'.base_url().'shiroki_trash_manifest" method="POST">
	// 					<div class="modal-content">
	// 						<div class="modal-header">
	// 							<h4 class="modal-title">Buang Manifest</h4>
	// 							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	// 							<span aria-hidden="true">&times;</span></button>
	// 						</div>
	// 						<div class="modal-body">
	// 							Apakah anda yakin ingin memindahkan ke tempat sampah ?
	// 						</div>
	// 						<div class="modal-footer justify-content-between">
	// 							<input type="hidden" name="id" value="'.$this->encrypt_model->my_encrypt($record->manifest).'">
	// 							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	// 							<button type="submit" class="btn btn-danger"><i class="fa fa-trash-alt"></i> Buang</button>
	// 						</div>
	// 					</div>
	// 				</form>
	// 				</div>
	// 			</div>';
	// 		//}
	// 		$row[] .= $aa;
	// 		$row[] = $record->supplier_code;
	// 		$row[] = $record->na1;
	// 		$row[] = $record->na2;
	// 		$row[] = $record->na3;
	// 		$row[] = $record->dock_code;
	// 		$row[] = $record->order_no;
	// 		$row[] = $record->part_no;
	// 		$row[] = $record->prodline_tmmin;
	// 		$row[] = $record->na4;
	// 		$row[] = $record->na5;
	// 		$row[] = $record->unique_no;
	// 		$row[] = $record->qty_per_kanban;
	// 		$row[] = $record->qty_order;
	// 		$row[] = $record->qty_kanban;
	// 		$row[] = $record->supplier;
	// 		$row[] = $record->part_name;
	// 		$row[] = $record->na6;
	// 		$row[] = $record->na7;
	// 		$row[] = $record->na8;
	// 		$row[] = $record->na9;
	// 		$row[] = $record->part_nox;
	// 		$row[] = $record->multi;
	// 		$row[] = $record->submit_time;
	// 		$row[] = $record->update_time;
	// 		$row[] = $record->uName;
	// 		$xx = '<a href="'.base_url().'shiroki_log_manifest/'.$xid.'" class="btn btn-sm btn-primary">Cek Hasil Scan</a>';
	// 		$row[] = $xx;
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->manifest_data_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->manifest_data_count_filtered($this->plant_id),
	// 		"data" => $data
	// 	);
    //     echo json_encode($output);	
	// }
	function get_client_ip() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_X_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		else if(isset($_SERVER['HTTP_FORWARDED']))
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		else if(isset($_SERVER['REMOTE_ADDR']))
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}
	function shiroki_manifest_run(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->role_id == 2 or $user_role->useradmin > 0){
				$modul = $this->shiroki_model->get_alarm_module(1, $this->plant_id);
				if(!empty($modul)){
					$this->session->set_userdata('use_alarm', $modul->unit_ip);
					if(!$this->session->has_userdata('run_manifest')){
						$data['alarm'] = $this->shiroki_model->get_alarm_list($this->plant_id);
						$data['client_ip'] = $this->get_client_ip();
						$this->global['pageTitle'] = 'Proses Manifest';
						$this->loadViews("shiroki/shiroki_manifest_run", $this->global, $data, NULL);
					}else{
						redirect('shiroki_manifest_ongoing');
					}
				}else{
					redirect('shiroki_list_alarm');
				}
			}else{
				$this->loadThis('Hanya Admin atau Operator yang dapat mengakses fitur ini.');
			}
		}else{
			$this->loadThis('Nampaknya anda seorang hacker...');
		}
	}
	function shiroki_proses_manifest(){
		header("Content-type: text/json");
		$manifest = $this->session->userdata('run_manifest');
		$val = $this->shiroki_model->get_manifest_proses($manifest, $this->plant_id);
		$this->session->set_userdata('proses_manifest', $val);
		echo json_encode(array('val'=>$val));
	}
	function shiroki_submit_tutup_manifest(){
		header("Content-type: text/json");
		$manifest = $this->session->userdata('run_manifest');
		$nama = $this->input->post('nama');
		$pass = $this->input->post('pass');
		$alasan = $this->input->post('alasan');
		$key = $this->shiroki_model->get_admin($nama, $pass, $this->plant_id);
		if(!empty($key)){
			$array = array(
				'nama'=>$nama,
				'alasan'=>$alasan,
				'tanggal'=>date('Y-m-d H:i:s'),
				'kode'=>$manifest,
				'plant_id'=>$this->plant_id
			);
			$this->shiroki_model->add_halt($array);
			$this->session->unset_userdata('run_manifest');
			echo json_encode(array('note'=>1));
		}else{
			echo json_encode(array('note'=>'invalid user and pass'));
		}
	}
	function shiroki_manifest_cancel(){
		if($this->session->userdata('proses_manifest') == 100){
			$this->session->unset_userdata('run_manifest');
			redirect('shiroki_manifest_run');
		}else{
			redirect('shiroki_manifest_ongoing');
		}
	}
	function shiroki_manifest_table(){
		$manifest = $this->session->userdata('run_manifest');
		if(!empty($manifest)){
			$data['manifest'] = $manifest;
			$get_table = $this->shiroki_model->get_manifest_table($manifest, $this->plant_id);
			if(!empty($get_table)){
				$data['manifest_table'] = $get_table;
				$this->load->view("shiroki/shiroki_manifest_table", $data);
			}else{
				$this->session->unset_userdata('run_manifest');
				echo '<h1>Data Manifest tidak ditemukan!</h1>';
			}
		}else{
			echo '<h1>Data Manifest tidak ditemukan!</h1>';
		}
	}
	function shiroki_manifest_ongoing(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->role_id == 2 or $user_role->useradmin > 0){
				$manifest = $this->session->userdata('run_manifest');
				$modul = $this->shiroki_excel_model->get_alarm_module(1, $this->plant_id);
				if(!empty($modul)){
					$this->session->set_userdata('use_alarm', $modul->unit_ip);
					$data['alarm'] = $modul->unit_ip;
				}
				if(!empty($manifest)){
					$data['manifest'] = $manifest;
					$get_table = $this->shiroki_model->get_manifest_table($manifest, $this->plant_id);
					if(!empty($get_table)){
						$data['scan_salah'] = $this->session->userdata('scan_salah');
						$data['manifest_table'] = $get_table;
						$this->global['pageTitle'] = 'Proses Manifest';
						$this->global['unlock'] = 1;
						$this->loadViews("shiroki/shiroki_manifest_ongoing", $this->global, $data, NULL);
					}else{
						$this->session->unset_userdata('run_manifest');
						redirect('shiroki_manifest_run');
					}
				}else{
					redirect('shiroki_manifest_run');
				}
			}else{
				$this->loadThis('Hanya Admin atau Operator yang dapat mengakses fitur ini.');
			}
		}else{
			$this->loadThis('Nampaknya anda seorang hacker...');
		}
	}
	function shiroki_cek_manifest(){
		header("Content-type: text/json");
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->role_id == 2 or $user_role->useradmin > 0){
				$manifest = $this->input->post('manifest');
				$alarm = $this->input->post('alarm');
				$get_row = $this->shiroki_model->get_manifest_num($manifest, $this->plant_id);
				if(!empty($get_row)){
					if($get_row->prog == 100){
						echo json_encode('fin');
					}else{
						$get_tabel = $this->shiroki_model->get_tabel_manifest_part_shiroki($manifest, $this->plant_id);
						$a = 1;
						$tabel = '<table class="table"><tr>
						<th>PART NO.</th>
						<th>UNIQUE NO.</th>
						<th>PART NAME</th>
						<th>KBN. SHIROKI</th></tr>';
						if(!empty($get_tabel)){
							foreach($get_tabel as $row){
								if(empty($row->kanban_shi)){
									$a = 2;
									$tabel .= '<tr style="background-color:#f090a0"><td>'.$row->part_no.'</td><td>'.$row->unique_no.'</td><td>'.$row->part_name.'</td><td>BELUM TERDAFTAR</td></tr>';
								}else{
									$tabel .= '<tr><td>'.$row->part_no.'</td><td>'.$row->unique_no.'</td><td>'.$row->part_name.'</td><td>'.$row->kanban_shi.'</td></tr>';	
								}
							}
						}
						$tabel .= '</table>';
						if($a == 2){
							echo json_encode($tabel);
						}else{
							$this->session->set_userdata('run_manifest', $manifest);
							$this->session->set_userdata('use_alarm', $alarm);
							$array = array('last_access'=>$this->get_client_ip());
							$this->shiroki_model->edit_alarm_byip($array, $alarm, $this->plant_id);
							echo json_encode('good');
						}
					}
				}else{
					echo json_encode('ng');
				}
			}else{
				echo json_encode('ng');
			}
		}else{
			echo json_encode('ng');
		}
	}
	function shiroki_cek_part_manifest(){
		header("Content-type: text/json");
		if(!($this->session->has_userdata('scan_salah'))){
			$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
			if(!empty($user_role)){
				if($user_role->role_id == 1 or $user_role->role_id == 2 or $user_role->useradmin > 0){
					$part = $this->input->post('part');
					$manifest = $this->session->userdata('run_manifest');
					$get_row = $this->shiroki_model->get_manifest_part($manifest, $part, $this->plant_id);
					// echo var_dump($get_row);
					if(!empty($get_row)){
						if($get_row['manifest'] == $manifest){
							if($get_row['good'] >= $get_row['qty_kanban']){
								$array = array(
									'manifest_id'=>$manifest,
									'scan_part'=>$get_row['part_no'],
									'scanby'=>$this->user_id,
									'plant_id'=>$this->plant_id,
									'result'=>2
								);
								$add = $this->shiroki_model->add_manifest_scan_data($array);
								$point = array('note'=>'fin', 'sig'=>'good:'.$get_row['good'].' all:'.$get_row['qty_kanban'], 'scan_salah'=>0);
								echo json_encode($point);
							}else{
								$array = array(
									'manifest_id'=>$manifest,
									'scan_part'=>$get_row['part_no'],
									'scanby'=>$this->user_id,
									'plant_id'=>$this->plant_id,
									'result'=>2
								);
								$add = $this->shiroki_model->add_manifest_scan_data($array);
								$point = array('note'=>'good', 'scan_salah'=>0);
								echo json_encode($point);
							}
						}else{
							$array = array(
								'manifest_id'=>$manifest,
								'scan_part'=>$part,
								'scanby'=>$this->user_id,
								'plant_id'=>$this->plant_id,
								'result'=>0
							);
							$add = $this->shiroki_model->add_manifest_scan_data($array);
							$this->session->set_userdata('scan_salah', $add);
							$point = array('note'=>'Kode ter-scan tidak sesuai dengan Manifest!', 'scan_salah'=>1);
							echo json_encode($point);
						}
					}else{
						$array = array(
							'manifest_id'=>$manifest,
							'scan_part'=>$part,
							'scanby'=>$this->user_id,
							'plant_id'=>$this->plant_id,
							'result'=>0
						);
						$add = $this->shiroki_model->add_manifest_scan_data($array);
						$this->session->set_userdata('scan_salah', $add);
						$point = array('note'=>'Kode ter-scan tidak sesuai dengan Manifest!', 'scan_salah'=>1);
						echo json_encode($point);
					}	
				}else{
					$point = array('note'=>'Ditolak', 'scan_salah'=>0);
					echo json_encode($point);
				}
			}else{
				$point = array('note'=>'Ditolak', 'scan_salah'=>0);
				echo json_encode($point);
			}
		}else{
			$point = array('note'=>'Ditolak, lengkapi penyebab salah scan sebelumnya terlebih dahulu', 'scan_salah'=>1);
			echo json_encode($point);
		}	
	}
	function shiroki_cek_salah_scan(){
		header("Content-type: text/json");
		if(!($this->session->has_userdata('scan_salah'))){
			$point = array('scan_salah'=>0);
			echo json_encode($point);
		}else{
			$point = array('scan_salah'=>1);
			echo json_encode($point);
		}
	}
	function shiroki_submit_salah_scan(){
		header("Content-type: text/json");
		if(!($this->session->has_userdata('scan_salah'))){
			$point = array('note'=>'ok');
			echo json_encode($point);
		}else{
			$salah = $this->input->post('salah');
			if(!empty($salah)){
				$array = array('note'=>$salah);
				$update = $this->shiroki_model->edit_manifest_scan_data($array, $this->session->userdata('scan_salah'));
				$this->session->unset_userdata('scan_salah');
				$point = array('note'=>'ok');
				echo json_encode($point);
			}else{
				$point = array('note'=>'ng');
				echo json_encode($point);
			}
		}
	}
	function shiroki_cek_shiroki_manifest(){
		header("Content-type: text/json");
		if(!($this->session->has_userdata('scan_salah'))){
			$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
			if(!empty($user_role)){
				if($user_role->role_id == 1 or $user_role->role_id == 2 or $user_role->useradmin > 0){
					$part = $this->input->post('part');
					$shiroki = $this->input->post('shiroki');
					$manifest = $this->session->userdata('run_manifest');
					$get_row = $this->shiroki_model->get_manifest_part_shiroki($manifest, $part, $shiroki, $this->plant_id);
					// echo var_dump($get_row);
					if(!empty($get_row)){
						if(!empty($get_row['manifest']) and $get_row['shi'] > 0){
							if($get_row['good'] >= $get_row['qty_kanban']){
								$array = array(
									'manifest_id'=>$manifest,
									'scan_part'=>$get_row['part_no'],
									'scan_shiroki'=>$shiroki,
									'scanby'=>$this->user_id,
									'plant_id'=>$this->plant_id,
									'result'=>0
								);
								$add = $this->shiroki_model->add_manifest_scan_data($array);
								$point = array('note'=>'Part ini sudah ter-scan komplit!','sig'=>'good:'.$get_row['good'].' all:'.$get_row['qty_kanban'], 'scan_salah'=>0);
								echo json_encode($point);
							}else{
								$array = array(
									'manifest_id'=>$manifest,
									'scan_part'=>$get_row['part_no'],
									'scan_shiroki'=>$shiroki,
									'scanby'=>$this->user_id,
									'plant_id'=>$this->plant_id,
									'result'=>1
								);
								$add = $this->shiroki_model->add_manifest_scan_data($array);
								$proses = (($get_row['good'] + 1)* 100)/$get_row['qty_kanban'];
								$array_manifest = array('proses'=>$proses);
								$update = $this->shiroki_model->bulkedit_manifest_data($array_manifest, $manifest, $get_row['part_no'], $this->plant_id);
								$point = array('note'=>'good','sig'=>'good:'.$get_row['good'].' all:'.$get_row['qty_kanban'], 'scan_salah'=>0);
								echo json_encode($point);
							}
						}else{
							$array = array(
								'manifest_id'=>$manifest,
								'scan_part'=>$get_row['part_no'],
								'scan_shiroki'=>$shiroki,
								'scanby'=>$this->user_id,
								'plant_id'=>$this->plant_id,
								'result'=>0
							);
							$add = $this->shiroki_model->add_manifest_scan_data($array);
							$this->session->set_userdata('scan_salah', $add);
							$point = array('note'=>'Kode ter-scan tidak sesuai dengan Kanban Customer!', 'scan_salah'=>1);
							echo json_encode($point);
						}
					}else{
						$array = array(
							'manifest_id'=>$manifest,
							'scan_part'=>$part,
							'scan_shiroki'=>$shiroki,
							'scanby'=>$this->user_id,
							'plant_id'=>$this->plant_id,
							'result'=>2
						);
						$add = $this->shiroki_model->add_manifest_scan_data($array);
						$this->session->set_userdata('scan_salah', $add);
						$point = array('note'=>'Kode ter-scan tidak sesuai dengan Kanban Customer!', 'scan_salah'=>1);
						echo json_encode($point);
					}
				}else{
					$point = array('note'=>'Ditolak', 'scan_salah'=>0);
					echo json_encode($point);
				}
			}else{
				$point = array('note'=>'Ditolak', 'scan_salah'=>0);
				echo json_encode($point);
			}
		}else{
			$point = array('note'=>'Ditolak, lengkapi penyebab salah scan sebelumnya terlebih dahulu', 'scan_salah'=>1);
			echo json_encode($point);
		}
	}
	// function shiroki_setting(){
	// 	$this->global['pageTitle'] = 'User Setting';
	// 	$this->loadViews("shiroki/shiroki_setting", $this->global, NULL, NULL);
	// }
	// function shiroki_setting_ajax(){
	// 	$list = $this->shiroki_model->get_user_data_dt($this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
	// 		$xid = $this->encrypt_model->my_encrypt($record->id);
    //         $row = array();
	// 		$row[] = $no;
	// 		$row[] = $record->uName;
	// 		$row[] = $record->username;
	// 		if($record->role_id == 1){
	// 			$row[] = 'Admin';
	// 		}elseif($record->role_id == 2){
	// 			$row[] = 'Operator';
	// 		}else{
	// 			$row[] = 'Guest';
	// 		}
	// 		$row[] = '<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#userdata'.$no.'" title="Update Data"><i class="fa fa-pencil-alt"></i> Update</button>
	// 		<div class="modal fade" id="userdata'.$no.'">
	// 			<div class="modal-dialog">
	// 			<form action="'.base_url().'shiroki_update_user_data" method="POST">
	// 				<div class="modal-content">
	// 					<div class="modal-header">
	// 						<h4 class="modal-title">Update User Data</h4>
	// 						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	// 						<span aria-hidden="true">&times;</span></button>
	// 					</div>
	// 					<div class="modal-body">
	// 						<div>
	// 							<table class="table">
	// 								<tr>
	// 									<th>User role</th>
	// 									<td>
	// 										<select name="role_id" class="form-control sel2">
	// 											<option value="3">Guest</option>
	// 											<option value="1">Admin</option>
	// 											<option value="2">Operator</option>
	// 										</select>
	// 									</td>
	// 								</tr>
	// 							</table>
	// 						</div>
	// 					</div>
	// 					<div class="modal-footer justify-content-between">
	// 						<input type="hidden" name="id" value="'.$xid.'">
	// 						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	// 						<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Update</button>
	// 					</div>
	// 				</div>
	// 			</form>
	// 			</div>
	// 		</div>';
    //         $data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->user_data_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->user_data_count_filtered($this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
	// function shiroki_update_user_data(){
	// 	$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
	// 	if(!empty($user_role)){
	// 		if($user_role->role_id == 1 or $user_role->useradmin > 0){
	// 			$id = $this->encrypt_model->decrypt20($this->input->post('id'));
	// 			$role_id = $this->input->post('role_id');
	// 			$get_user = $this->shiroki_model->get_user_role($id, $this->plant_id);
	// 			if(!empty($get_user)){
	// 				if(!empty($get_user->role_id)){
	// 					$array = array('role_id'=>$role_id);
	// 					$insert = $this->shiroki_model->edit_user_data($array, $get_user->id);
	// 					redirect('shiroki_setting');
	// 				}else{
	// 					$array = array('role_id'=>$role_id, 'user_id'=>$get_user->id);
	// 					$insert = $this->shiroki_model->add_user_data($array);
	// 					redirect('shiroki_setting');
	// 				}
	// 			}else{
	// 				$this->loadThis('Data tidak valid, silakan coba lagi');
	// 			}
	// 		}else{
	// 			$this->loadThis('Hanya Admin yang dapat mengakses fitur ini');
	// 		}
	// 	}else{
	// 		$this->loadThis('Nampaknya anda seorang hacker ?');
	// 	}
	// }
	// function shiroki_manifest_log_data(){
	// 	$this->global['pageTitle'] = 'Log Data Manifest';
	// 	$this->loadViews("shiroki/shiroki_manifest_log_data", $this->global, NULL, NULL);
	// }
	// function shiroki_manifest_log_data_ajax(){
	// 	$list = $this->shiroki_model->get_manifest_log_data_dt($this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
    //         $row = array();
	// 		$row[] = $no;
	// 		$row[] = $record->manifest;
	// 		$row[] = $record->order_no;
	// 		$row[] = '<a href="'.base_url().'shiroki_log_manifest/'.$this->encrypt_model->my_encrypt($record->manifest).'" class="btn btn-primary btn-sm"><i class="fa fa-file"></i> '.round($record->prog, 2).'%</a>';
	// 		$row[] = date('d-m-Y H:i', strtotime(substr($record->na7, 0, -4)));
	// 		$row[] = $record->dock_code;
	// 		//if(date('U') > date('U', strtotime(substr($record->na7, 0, -4)))){
	// 			$row[] = '<button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#masterdata'.$no.'" title="Buang ke Tempat Sampah"><i class="fa fa-trash-alt"></i></button>
	// 			<div class="modal fade" id="masterdata'.$no.'">
	// 				<div class="modal-dialog">
	// 				<form action="'.base_url().'shiroki_trash_manifest" method="POST">
	// 					<div class="modal-content">
	// 						<div class="modal-header">
	// 							<h4 class="modal-title">Buang Manifest</h4>
	// 							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	// 							<span aria-hidden="true">&times;</span></button>
	// 						</div>
	// 						<div class="modal-body">
	// 							Apakah anda yakin ingin memindahkan ke tempat sampah ?
	// 						</div>
	// 						<div class="modal-footer justify-content-between">
	// 							<input type="hidden" name="id" value="'.$this->encrypt_model->my_encrypt($record->manifest).'">
	// 							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	// 							<button type="submit" class="btn btn-danger"><i class="fa fa-trash-alt"></i> Buang</button>
	// 						</div>
	// 					</div>
	// 				</form>
	// 				</div>
	// 			</div>';
	// 		// }else{
	// 		// 	$row[] = '';
	// 		// }
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->manifest_log_data_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->manifest_log_data_count_filtered($this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
	// function shiroki_manifest_bin_data(){
	// 	$this->global['pageTitle'] = 'Tempat Sampah';
	// 	$this->loadViews("shiroki/shiroki_manifest_bin_data", $this->global, NULL, NULL);
	// }
	// function shiroki_manifest_bin_data_ajax(){
	// 	$list = $this->shiroki_model->get_manifest_bin_data_dt($this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
    //         $row = array();
	// 		$row[] = $no;
	// 		$row[] = $record->manifest;
	// 		$row[] = $record->order_no;
	// 		$row[] = '<a href="'.base_url().'shiroki_log_manifest/'.$this->encrypt_model->my_encrypt($record->manifest).'" class="btn btn-primary btn-sm"><i class="fa fa-file"></i> '.round($record->prog, 2).'%</a>';
	// 		$row[] = date('d-m-Y H:i', strtotime(substr($record->na7, 0, -4)));
	// 		$row[] = $record->dock_code;
	// 		$row[] = '<button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#masterdatax'.$no.'" title="Buang Permanent"><i class="fa fa-trash-alt"></i></button>
	// 		<div class="modal fade" id="masterdatax'.$no.'">
	// 			<div class="modal-dialog">
	// 			<form action="'.base_url().'shiroki_trash_manifest" method="POST">
	// 				<div class="modal-content">
	// 					<div class="modal-header">
	// 						<h4 class="modal-title">Buang Manifest secara Permanent</h4>
	// 						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	// 						<span aria-hidden="true">&times;</span></button>
	// 					</div>
	// 					<div class="modal-body">
	// 						Apakah anda yakin ingin menghapus manifest secara permanent ?
	// 					</div>
	// 					<div class="modal-footer justify-content-between">
	// 						<input type="hidden" name="xid" value="'.$this->encrypt_model->my_encrypt($record->manifest).'">
	// 						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	// 						<button type="submit" class="btn btn-danger"><i class="fa fa-trash-alt"></i> Buang</button>
	// 					</div>
	// 				</div>
	// 			</form>
	// 			</div>
	// 		</div>
	// 		<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#masterdata'.$no.'" title="Kembalikan Manifest"><i class="fa fa-upload"></i></button>
	// 		<div class="modal fade" id="masterdata'.$no.'">
	// 			<div class="modal-dialog">
	// 			<form action="'.base_url().'shiroki_trash_manifest" method="POST">
	// 				<div class="modal-content">
	// 					<div class="modal-header">
	// 						<h4 class="modal-title">Kembalikan Manifest</h4>
	// 						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	// 						<span aria-hidden="true">&times;</span></button>
	// 					</div>
	// 					<div class="modal-body">
	// 						Apakah anda yakin ingin mengembalikan manifest ini ?
	// 					</div>
	// 					<div class="modal-footer justify-content-between">
	// 						<input type="hidden" name="yid" value="'.$this->encrypt_model->my_encrypt($record->manifest).'">
	// 						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	// 						<button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Kembalikan</button>
	// 					</div>
	// 				</div>
	// 			</form>
	// 			</div>
	// 		</div>';
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->manifest_bin_data_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->manifest_bin_data_count_filtered($this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
	// function shiroki_trash_manifest(){
	// 	$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
	// 	if(!empty($user_role)){
	// 		if($user_role->role_id == 1 or $user_role->useradmin > 0){
	// 			$id = $this->input->post('id');
	// 			$yid = $this->input->post('yid');
	// 			$xid = $this->input->post('xid');
	// 			$tid = $this->input->post('tid');
	// 			if(!empty($id)){
	// 				$array = array('isvalid'=>0);
	// 				$update = $this->shiroki_model->edit_manifest_byman($array, $this->encrypt_model->decrypt20($id), $this->plant_id);
	// 				redirect('shiroki_manifest_log_data');
	// 			}elseif(!empty($yid)){
	// 				$array = array('isvalid'=>1);
	// 				$update = $this->shiroki_model->edit_manifest_byman($array, $this->encrypt_model->decrypt20($yid), $this->plant_id);
	// 				redirect('shiroki_manifest_bin_data');
	// 			}elseif(!empty($xid)){
	// 				$update = $this->shiroki_model->delete_manifest_byman($this->encrypt_model->decrypt20($xid), $this->plant_id);
	// 				redirect('shiroki_manifest_bin_data');
	// 			}elseif(!empty($tid)){
	// 				$time = $this->encrypt_model->decrypt20($tid);
	// 				$valid = date('U') - $time;
	// 				if($valid < 80000){
	// 					$update = $this->shiroki_model->delete_manifest_byvalid(0, $this->plant_id);
	// 					redirect('shiroki_manifest_bin_data');
	// 				}else{
	// 					$this->loadThis('Invalid action');
	// 				}
	// 			}
	// 		}else{
	// 			$this->loadThis('Fitur ini hanya dapat diakses admin');
	// 		}
	// 	}else{
	// 		$this->loadThis('Fitur ini hanya dapat diakses admin');
	// 	}
	// }
	function shiroki_auto_remove(){
		$update = $this->shiroki_model->delete_manifest_3hari(date('Y-m-d H:i:s', strtotime('-5 day')), $this->plant_id);
		$update = $this->shiroki_model->delete_scan_7hari(date('Y-m-d H:i:s', strtotime('-10 day')), $this->plant_id);
		$array = array('isvalid'=>0);
		$update = $this->shiroki_model->edit_manifest_5hari($array, date('Y-m-d H:i:s', strtotime('-3 day')), $this->plant_id);
		return true;
	}
	// function shiroki_log_manifest($manifest){
	// 	$get_manifest = $this->shiroki_model->get_manifest_table($this->encrypt_model->decrypt20($manifest), $this->plant_id);
	// 	if(!empty($get_manifest)){
	// 		$data['manifest_table'] = $get_manifest;
	// 		$data['manifest'] = $manifest;
	// 		$this->global['pageTitle'] = 'Log Data Manifest';
	// 		$this->loadViews("shiroki/shiroki_manifest_log", $this->global, $data, NULL);
	// 	}else{
	// 		$this->loadThis('Manifest tidak ditemukan');
	// 	}
	// }
	// function shiroki_log_data_byman_ajax($manifest){
	// 	$manifest = $this->encrypt_model->decrypt20($manifest);
	// 	$list = $this->shiroki_model->get_log_data_byman_dt($manifest, $this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
    //         $row = array();
	// 		$row[] = $no;
	// 		$row[] = $record->timestamp;
	// 		if($record->result == 1){
	// 			$row[] = '<span class="badge badge-success">Scan Berhasil</span>';
	// 		}elseif($record->result == 2){
	// 			$row[] = '<span class="badge badge-warning">Scan Customer OK</span>';
	// 		}elseif($record->result == 0 and empty($record->scan_shiroki)){
	// 			$row[] = '<span class="badge badge-danger">Scan Customer Gagal</span>';
	// 		}elseif($record->result == 0 and !empty($record->scan_shiroki)){
	// 			$row[] = '<span class="badge badge-danger">Scan Shiroki Gagal</span>';
	// 		}
	// 		$row[] = $record->scan_part;
	// 		$row[] = $record->scan_shiroki;
	// 		$row[] = $record->note;
	// 		$row[] = $record->uName;
	// 		$row[] = $record->part_name;
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->log_data_byman_count_all($manifest, $this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->log_data_byman_count_filtered($manifest, $this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
	function shiroki_list_alarm(){
		$this->global['pageTitle'] = 'Alarm Module';
		$data['modul'] = $this->shiroki_model->get_alarm_module(1, $this->plant_id);
		$this->loadViews("shiroki/shiroki_alarm_module", $this->global, $data, NULL);
	}
	function shiroki_list_alarm_ajax(){
		$list = $this->shiroki_model->get_alarm_dt($this->plant_id);
		$data = array();
        $no = $_POST['start'];
        foreach ($list as $record){
			$no++;
            $row = array();
			$xid = $this->encrypt_model->my_encrypt($record->id);
			$row[] = $no;
			$row[] = $record->unit_name;
			$row[] = $record->unit_ip;
			$row[] = $record->unit_port;
			$row[] = '
			<button class="btn btn-sm btn-success" onclick="location.href=\'http://'.$record->unit_ip.'/?p1=1&p2=1&p3=1&p4=1&p5=1\'">Tes ON</button>
			<button class="btn btn-sm btn-danger" onclick="location.href=\'http://'.$record->unit_ip.'/?p1=0&p2=0&p3=0&p4=0&p5=0\'">Tes OFF</button>
			<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#masterdata'.$no.'" title="Update Data"><i class="fa fa-pencil-alt"></i> Update</button>
			<div class="modal fade" id="masterdata'.$no.'">
				<div class="modal-dialog">
				<form action="'.base_url().'shiroki_update_alarm_module" method="POST">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Update Modul Alarm</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span></button>
						</div>
						<div class="modal-body">
							<div>
								<table class="table">
									<tr>
										<th>Nama unit</th>
										<td><input type="text" class="form-control" value="'.$record->unit_name.'" name="unit_name" required></td>
									</tr>
									<tr>
										<th>IP</th>
										<td><input type="text" class="form-control ipv4" value="'.$record->unit_ip.'" name="unit_ip" required></td>
									</tr>
									<tr>
										<th>Port</th>
										<td><input type="number" class="form-control" value="'.$record->unit_port.'" name="unit_port" required></td>
									</tr>
									<tr>
										<th>Delete?</th>
										<td><select name="isvalid" class="form-control"><option value="1">No</option><option value="0">Yes</option></select></td>
									</tr>
								</table>
							</div>
						</div>
						<div class="modal-footer justify-content-between">
							<input type="hidden" name="id" value="'.$xid.'">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
							<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Update</button>
						</div>
					</div>
				</form>
				</div>
			</div>';
			$data[] = $row;
        }
        $output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $this->shiroki_model->alarm_count_all($this->plant_id),
			"recordsFiltered" => $this->shiroki_model->alarm_count_filtered($this->plant_id),
			"data" => $data,
		);
        echo json_encode($output);	
	}
	function shiroki_update_alarm_module(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->useradmin > 0){
				$unit_name = $this->input->post('unit_name');
				$unit_ip = $this->input->post('unit_ip');
				$unit_port = $this->input->post('unit_port');
				$isvalid = $this->input->post('isvalid');
				$id = $this->encrypt_model->decrypt20($this->input->post('id'));
				$array = array(
					'unit_name'=>$unit_name,
					'unit_ip'=>$unit_ip,
					'unit_port'=>$unit_port,
					'isvalid'=>$isvalid
				);
				$insert = $this->shiroki_model->edit_alarm_data($array, $id);
				redirect('shiroki_list_alarm');
			}else{
				$this->loadThis('Fitur ini hanya dapat diakses admin');
			}
		}else{
			$this->loadThis('Fitur ini hanya dapat diakses admin');
		}
	}
	function shiroki_tambah_alarm(){
		$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
		if(!empty($user_role)){
			if($user_role->role_id == 1 or $user_role->useradmin > -1){
				$unit_name = 'USB Alarm';
				$unit_ip = $this->input->post('unit_ip');
				$unit_port = '';
				if(!empty($unit_name) and !empty($unit_ip)){
					$input_array = array(
						'id'=>1,
						'unit_name'=>$unit_name,
						'unit_ip'=>$unit_ip,
						'unit_port'=>$unit_port,
						'plant_id'=>$this->plant_id
					);
					$insert = $this->shiroki_model->replace_alarm($input_array);
				}
				redirect('shiroki_list_alarm');
			}else{
				$this->loadThis('Fitur ini hanya dapat diakses admin');
			}
		}else{
			$this->loadThis('Fitur ini hanya dapat diakses admin');
		}
	}
	function shiroki_alarm_action(){
		header("Content-type: text/json");
		require_once(APPPATH.'libraries/Phpmodbus/ModbusMaster.php');
		$id = $this->encrypt_model->decrypt20($this->input->post('xid'));
		$modul = $this->shiroki_model->get_alarm_module($id, $this->plant_id);
		if(!empty($modul)){
			$modbus = new ModbusMaster($modul->unit_ip, "TCP");
			$data = array(TRUE, TRUE, TRUE, TRUE);
			try {
				$modbus->writeMultipleCoils(0, 0, $data);
			}
			catch (Exception $e) {
				echo $modbus;
				echo $e;
				exit;
			}
			// echo $modbus;
			echo json_encode($modbus);
		}else{
			echo json_encode('Not Found!');
		}
	}
	// function shiroki_cek_modul(){
	// 	header("Content-type: text/json");
	// 	$hasil = shell_exec('dmesg | grep tty');
	// 	$var1 = preg_split('/\r\n|\r|\n/', $hasil);
	// 	$valid = array();
	// 	if(!empty($var1)){
	// 		foreach($var1 as $row){
	// 			$col = explode(' ', $row);
	// 			if(!empty($col)){
	// 				foreach($col as $kata){
	// 					if(strpos($kata, 'tty') !== false){
	// 						$valid[] = preg_replace("/[^A-Za-z0-9 ]/", '',$kata);
	// 					}
	// 				}
	// 			}
	// 		}
	// 	}
	// 	$arr = array_unique($valid);
	// 	$option = '';
	// 	if(!empty($arr)){
	// 		foreach($arr as $opt){
	// 			$option .= '<option value="/dev/'.$opt.'">/dev/'.$opt.'</option>';
	// 		}
	// 	}
	// 	$array = array();
	// 	$array['hasil_scan'] = nl2br($hasil);
	// 	$array['list_scan'] = $option;
	// 	echo json_encode($array);
	// }
	// function shiroki_test_modul(){
	// 	header("Content-type: text/json");
	// 	$porta = fopen($this->input->post('list_scan'), "w");
	// 	// sleep(1);
	// 	// usleep(500000);
	// 	fwrite($porta, (string)$this->input->post('test_data'));
	// 	fclose($porta);
	// }
	// function shiroki_alarm($data){
	// 	$porta = fopen($this->session->userdata('use_alarm'), "w");
	// 	fwrite($porta, (string)$data);
	// 	fclose($porta);
	// }
	function shiroki_cek_modul(){
		header("Content-type: text/json");
		if (PHP_OS_FAMILY === 'Windows') {
    		exec('mode', $output);
			$option = '';
			foreach ($output as $line) {
				if (preg_match('/COM\d+/', $line, $matches)) {
					$option .= '<option value="'.$matches[0].'">'.$matches[0].'</option>';
				}
			}
			$array = array();
			$array['hasil_scan'] = nl2br(implode(', ', $output));
			$array['list_scan'] = $option;
			echo json_encode($array);
		}else{
			$hasil = shell_exec('dmesg | grep tty');
			$var1 = preg_split('/\r\n|\r|\n/', $hasil);
			$valid = array();
			if(!empty($var1)){
				foreach($var1 as $row){
					$col = explode(' ', $row);
					if(!empty($col)){
						foreach($col as $kata){
							if(strpos($kata, 'tty') !== false){
								$valid[] = preg_replace("/[^A-Za-z0-9 ]/", '',$kata);
							}
						}
					}
				}
			}
			$arr = array_unique($valid);
			$option = '';
			if(!empty($arr)){
				foreach($arr as $opt){
					$option .= '<option value="/dev/'.$opt.'">/dev/'.$opt.'</option>';
				}
			}
			$array = array();
			$array['hasil_scan'] = nl2br($hasil);
			$array['list_scan'] = $option;
			echo json_encode($array);
		}
	}
	function shiroki_test_modul(){
		header("Content-type: text/json");
		if (PHP_OS_FAMILY === 'Windows') {
			$py = FCPATH.'uploads\\shiroki\\alarm.py';
			$port = $this->input->post('list_scan');
			$data = (string)$this->input->post('test_data');
			$send = escapeshellarg($port.','.$data);
			$command = 'python3 ' .$py.' '.$send;
			exec($command, $output, $return_var);
		}else{
			$porta = fopen($this->input->post('list_scan'), "w");
			// sleep(1);
			// usleep(500000);
			fwrite($porta, (string)$this->input->post('test_data'));
			fclose($porta);
		}
	}
	function shiroki_alarm($state){
		if (PHP_OS_FAMILY === 'Windows') {
			$py = FCPATH.'uploads\\shiroki\\alarm.py';
			$port = $this->session->userdata('use_alarm');
			$data = (string)$state;
			$send = escapeshellarg($port.','.$data);
			$command = 'python3 ' .$py.' '.$send;
			exec($command, $output, $return_var);
		}else{
			$porta = fopen($this->session->userdata('use_alarm'), "w");
			fwrite($porta, (string)$state);
			fclose($porta);
		}
	}
	// function shiroki_list_admin(){
	// 	$this->global['pageTitle'] = 'Admin List';
	// 	$this->loadViews("shiroki/shiroki_admin", $this->global, NULL, NULL);
	// }
	// function shiroki_list_admin_ajax(){
	// 	$list = $this->shiroki_model->admin_dt($this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
    //         $row = array();
	// 		$xid = $this->encrypt_model->my_encrypt($record->id);
	// 		$row[] = $no;
	// 		$row[] = $record->nama;
	// 		$row[] = '******';
	// 		$row[] = $record->tanggal;
	// 		$row[] = '
	// 		<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#masterdata'.$no.'" title="Update Data"><i class="fa fa-pencil-alt"></i> Update</button>
	// 		<div class="modal fade" id="masterdata'.$no.'">
	// 			<div class="modal-dialog">
	// 			<form action="'.base_url().'shiroki/shiroki_update_admin" method="POST">
	// 				<div class="modal-content">
	// 					<div class="modal-header">
	// 						<h4 class="modal-title">Update Admin</h4>
	// 						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	// 						<span aria-hidden="true">&times;</span></button>
	// 					</div>
	// 					<div class="modal-body">
	// 						<div>
	// 							<table class="table">
	// 								<tr>
	// 									<th>Nama</th>
	// 									<td><input type="text" class="form-control" value="'.$record->nama.'" name="nama" required></td>
	// 								</tr>
	// 								<tr>
	// 									<th>Pass</th>
	// 									<td><input type="password" class="form-control" name="pass" required></td>
	// 								</tr>
	// 								<tr>
	// 									<th>Delete?</th>
	// 									<td><select name="isvalid" class="form-control"><option value="1">No</option><option value="0">Yes</option></select></td>
	// 								</tr>
	// 							</table>
	// 						</div>
	// 					</div>
	// 					<div class="modal-footer justify-content-between">
	// 						<input type="hidden" name="id" value="'.$xid.'">
	// 						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	// 						<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Update</button>
	// 					</div>
	// 				</div>
	// 			</form>
	// 			</div>
	// 		</div>';
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->admin_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->admin_count_filtered($this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
	// function shiroki_update_admin(){
	// 	$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
	// 	if(!empty($user_role)){
	// 		if($user_role->useradmin > 0){
	// 			$nama = $this->input->post('nama');
	// 			$pass = $this->input->post('pass');
	// 			$isvalid = $this->input->post('isvalid');
	// 			$id = $this->encrypt_model->decrypt20($this->input->post('id'));
	// 			$array = array(
	// 				'nama'=>$nama,
	// 				'pass'=>$pass,
	// 				'isvalid'=>$isvalid,
	// 				'tanggal'=>date('Y-m-d H:i:s')
	// 			);
	// 			$insert = $this->shiroki_model->edit_admin($array, $id);
	// 			redirect('shiroki/shiroki_list_admin');
	// 		}else{
	// 			$this->loadThis('Fitur ini hanya dapat diakses admin');
	// 		}
	// 	}else{
	// 		$this->loadThis('Fitur ini hanya dapat diakses admin');
	// 	}
	// }
	// function shiroki_tambah_admin(){
	// 	$user_role = $this->shiroki_model->get_user_role($this->user_id, $this->plant_id);
	// 	if(!empty($user_role)){
	// 		if($user_role->useradmin > -1){
	// 			$nama = $this->input->post('nama');
	// 			$pass = $this->input->post('pass');
	// 			$pass2 = $this->input->post('pass2');
	// 			if(!empty($nama) and !empty($pass) and ($pass == $pass2)){
	// 				$input_array = array(
	// 					'nama'=>$nama,
	// 					'pass'=>$pass,
	// 					'plant_id'=>$this->plant_id,
	// 					'tanggal'=>date('Y-m-d H:i:s')
	// 				);
	// 				$insert = $this->shiroki_model->add_admin($input_array);
	// 				redirect('shiroki/shiroki_list_admin');
	// 			}else{
	// 				$this->loadThis('Tidak valid');
	// 			}
	// 		}else{
	// 			$this->loadThis('Fitur ini hanya dapat diakses admin');
	// 		}
	// 	}else{
	// 		$this->loadThis('Fitur ini hanya dapat diakses admin');
	// 	}
	// }
	// function shiroki_halt(){
	// 	$this->global['pageTitle'] = 'Halted Manifest';
	// 	$this->loadViews("shiroki/shiroki_halt", $this->global, NULL, NULL);
	// }
	// function shiroki_halt_ajax(){
	// 	$list = $this->shiroki_model->halt_dt($this->plant_id);
	// 	$data = array();
    //     $no = $_POST['start'];
    //     foreach ($list as $record){
	// 		$no++;
    //         $row = array();
	// 		$xid = $this->encrypt_model->my_encrypt($record->idx);
	// 		$row[] = $no;
	// 		$row[] = $record->kode;
	// 		$row[] = $record->prog;
	// 		$row[] = '<a href="'.base_url().'shiroki_log_manifest/'.$xid.'" class="btn btn-sm btn-primary">Cek Hasil Scan</a>';
	// 		$row[] = $record->nama;
	// 		$row[] = $record->alasan;
	// 		$row[] = $record->tanggal;
	// 		$data[] = $row;
    //     }
    //     $output = array(
	// 		"draw" => $_POST['draw'],
	// 		"recordsTotal" => $this->shiroki_model->halt_count_all($this->plant_id),
	// 		"recordsFiltered" => $this->shiroki_model->halt_count_filtered($this->plant_id),
	// 		"data" => $data,
	// 	);
    //     echo json_encode($output);	
	// }
}	
?>
