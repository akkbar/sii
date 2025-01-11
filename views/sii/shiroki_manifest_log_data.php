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
						<i class="fa fa-clipboard-check"></i> Log Data Manifest
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
							<table id="table" class="table" cellspacing="0" width="100% " style="white-space:nowrap;">
								<thead>
									<tr>
										<th class="text-center">No</th>
										<th>Manifest</th>
										<th>No Order</th>
										<th>Progress</th>
										<th>ETD</th>
										<th>Dock Code</th>
										<th>Action</th>
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
                url: "<?php echo site_url('shiroki_manifest_log_data_ajax')?>",
                type: "POST",
                data: function ( data ) {
                    // data.start_date = $('#start_date').val();
                    // data.end_date = $('#end_date').val();
                }
            },
			columnDefs: [
                // { visible: false, targets: [ 4, 13, 14] },
                { orderable: false, targets: [ 0, 6] }
            ],
            aoColumns: [
                null,
                null,
                null,
                null,
                null,
                null,
                null
                ],
            dom:
            "<'row'<'col-lg-12 col-md-12 col-12'B>>" +
            "<'row'<'col-lg-6 col-md-6 col-sm-12 col-12'l><'col-lg-6 col-md-6 col-sm-12 col-12'f>>" +
            "<'row'<'col-lg-12 col-md-12 col-12 table-responsive'tr>>" +
            "<'row'<'col-lg-5 col-md-5 col-12'i><'col-lg-7 col-md-7 col-12'p>>",
            buttons: [{
                    extend:    'copy',
                    text:      '<i class="fa fa-copy"></i>',
                    titleAttr: 'Copy',
                    className: 'btn btn-info'
                },{
                    extend:    'excel',
                    text:      '<i class="fa fa-file-excel"></i>',
                    titleAttr: 'Excel',
                    className: 'btn btn-success'
                },{
                    extend:    'pdf',
                    text:      '<i class="fa fa-file-pdf"></i>',
                    titleAttr: 'PDF',
                    className: 'btn btn-danger'
                },{
                    extend:    'print',
                    text:      '<i class="fa fa-print"></i>',
                    titleAttr: 'Print',
                    className: 'btn btn-warning',
                    exportOptions: {
                        columns: ':visible'
                    },
                    messageTop: 'Company Data'
                },{
                    extend: 'colvis',
                    className: 'btn btn-default',
                    postfixButtons: ['colvisRestore']
                }],
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
