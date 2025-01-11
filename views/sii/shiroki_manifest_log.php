<style>
    li.buttons-columnVisibility a {
        cursor:pointer;
        }
    li.buttons-columnVisibility.active a {
        background-color:transparent;
        color:inherit; 
        }
    li.buttons-columnVisibility.active a:hover {
        background-color:#f5f5f5;
        color:inherit;
        }
    li.buttons-columnVisibility a:before {
        width:25px;
        font-family:"Glyphicons Halflings";
        content:"\e013";
        color:transparent;
        padding-right:10px;
        }
    li.buttons-columnVisibility.active a:before {
        color:inherit;
        }
    #table_filter label { min-width: 100%;}
    #table_filter label input{ min-width: 100%;}
</style>
<div class="content-wrapper">
	<section class="content-header">
		<div class="content-fluid">
			<div class="row">
				<div class="col-12">
					<h1>
						<i class="fa fa-clipboard-check"></i> Log Manifest <?php echo $this->encrypt_model->decrypt20($manifest); ?>
					</h1>
				</div>
			</div>
		</div>
	</section>
	<section class="content">
		<div class="content-fluid">
			<div class="row">
				<div class="col-lg-12 col-12">
					<div class="card">
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th rowspan="2">NO.</th>
                                    <th rowspan="2">ORDER NO</th>
                                    <th rowspan="2">PART NO.</th>
                                    <th rowspan="2">UNIQUE NO.</th>
                                    <th rowspan="2">PART NAME</th>
                                    <th rowspan="2">PCS/KBN</th>
                                    <th rowspan="2">NO of KBN</th>
                                    <th rowspan="2">TTL QTY</th>
                                    <th colspan="2">HASIL SCAN</th>
                                    <th rowspan="2">SISA</th>
                                </tr>
                                <tr>
                                    <th>GOOD</th>
                                    <th>NG</th>
                                </tr>
                            <?php
                                $a = 1;
                                $prog = 1;
                                if(!empty($manifest_table)){
                                    foreach($manifest_table as $row){
                            ?>
                                <tr <?php if($row[9] == 0){echo 'style="background-color:#5cff87"'; $prog++;}elseif($row[9] < $row[6] and $row[9] != 0){echo 'style="background-color:#c7c8c9"';} ?>>
                                    <td><?php echo $a; ?></td>
                                    <td><?php echo $row[0]; ?></td>
                                    <td><?php echo $row[1]; ?></td>
                                    <td><?php echo $row[2]; ?></td>
                                    <td><?php echo $row[3]; ?></td>
                                    <td><?php echo $row[4]; ?></td>
                                    <td><?php echo $row[5]; ?></td>
                                    <td><?php echo $row[6]; ?></td>
                                    <td><?php echo $row[7]; ?></td>
                                    <td><?php echo $row[8]; ?></td>
                                    <td><?php echo $row[9]; ?></td>
                                </tr>
                            <?php
                                        $a++;
                                    }   
                                }
                            ?>
                            </table>
                        </div>
						<div class="card-body">
							<table id="table" class="table" cellspacing="0" width="100% " style="white-space:nowrap;">
								<thead>
									<tr>
										<th class="text-center">No</th>
										<th>Scan Time</th>
										<th>Result</th>
										<th>Kanban Customer</th>
										<th>Kanban Shiroki</th>
										<th>Note</th>
										<th>User</th>
										<th>Part Name</th>
									</tr>
								</thead>
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
</div>
<script src="<?php echo base_url(); ?>assets/adminlte/plugins/select2/js/select2.full.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url(); ?>assets/plugins/datepicker/datepicker3.css" />
<script src="<?php echo base_url(); ?>assets/plugins/datepicker/bootstrap-datepicker.js"></script>

<script src="<?php echo base_url('assets/adminlte/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<link href="<?php echo base_url('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')?>" rel="stylesheet">
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')?>"></script>
<link href="<?php echo base_url('assets/adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')?>" rel="stylesheet">
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js')?>"></script>
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')?>"></script>
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-buttons/js/buttons.html5.min.js')?>"></script>
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-buttons/js/buttons.print.min.js')?>"></script>
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js')?>"></script>
<script src="<?php echo base_url('assets/plugins/DataTables/JSZip-2.5.0/jszip.min.js')?>"></script>
<script src="<?php echo base_url('assets/plugins/DataTables/pdfmake-0.1.36/pdfmake.min.js')?>"></script>
<script src="<?php echo base_url('assets/plugins/DataTables/pdfmake-0.1.36/vfs_fonts.js')?>"></script>
<script type="text/javascript">
	$(document).ready(function () {
		$(".sel2").select2({
        	minimumResultsForSearch: 20
		});
	});
	var table;
    $(document).ready(function() {
        table = $('#table').DataTable({ 
            processing: true,
            serverSide: true,
            order: [],
            stateSave: true,
            ajax: {
                url: "<?php echo site_url('shiroki_log_data_byman_ajax/'.$manifest)?>",
                type: "POST",
                data: function ( data ) {
                    // data.start_date = $('#start_date').val();
                    // data.end_date = $('#end_date').val();
                }
            },
			columnDefs: [
                // { visible: false, targets: [ 4, 13, 14] },
                { orderable: false, targets: [ 0] }
            ],
            aoColumns: [
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null
                ],
            dom:
            "<'row'<'col-lg-6 col-md-6 col-sm-12 col-12'l><'col-lg-6 col-md-6 col-sm-12 col-12'f>>" +
            "<'row'<'col-lg-12 col-md-12 col-12 table-responsive'tr>>" +
            "<'row'<'col-lg-5 col-md-5 col-12'i><'col-lg-7 col-md-7 col-12'p>>",
            fade: false,
            language: {
                buttons: {
                    colvis: 'Columns'
                },
                searchPlaceholder: "Search records",
                search: ""
            }
        });
    });
</script>
