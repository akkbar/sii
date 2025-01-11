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
						<i class="fa fa-table"></i> Data Modul Alarm
					</h1>
				</div>
			</div>
		</div>
	</section>
    <section class="content">
		<div class="content-fluid">
			<div class="row">
				<div class="col-lg-12 col-12">
                    <form method="POST" action="<?php echo base_url(); ?>shiroki_tambah_alarm">
					<div class="card">
						<div class="card-body">
                            <div class="form-group">
                                Un-plug then plug your alarm modul. Then click Scan button.<br>
                                Cabut kemudian Pasang modul alarm. Kemudian klik tombol Scan<br>
                                <button class="btn btn-sm btn-success" type="button" onclick="cek_serial_usb()"><i class="fa fa-search"></i> SCAN</button>
                            </div>
                            <div class="form-group">
                                <p id="hasil_scan"></p>
                                <label>Serial Port</label>
                                <select class="form-control" name="unit_ip" id="list_scan" required>
                                    <?php if(!empty($modul)){echo '<option value="'.$modul->unit_ip.'">'.$modul->unit_ip.'</option>';} ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Test Serial</label>
                                <select class="form-control" id="test_data">
                                    <option value="1">OK</option>
                                    <option value="2">NG</option>
                                    <option value="0">OFF</option>
                                </select>
                                <button class="btn btn-sm btn-primary" type="button" onclick="test_serial()"><i class="fa fa-send"></i> SEND</button>
                            </div>
						</div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-primary" type="submit"><i class="fa fa-save"></i> SAVE</button>
                        </div>
					</div>
                    </form>
				</div>
			</div>
		</div>
	</section>
</div>
<script src="<?php echo base_url('assets/adminlte/plugins/moment/moment.min.js')?>"></script>
<script src="<?php echo base_url('assets/adminlte/plugins/inputmask/min/jquery.inputmask.bundle.min.js')?>"></script>

<script src="<?php echo base_url('assets/adminlte/plugins/datatables/jquery.dataTables.min.js')?>"></script>
<link href="<?php echo base_url('assets/adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')?>" rel="stylesheet">
<script src="<?php echo base_url('assets/adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')?>"></script>
<script type="text/javascript">
	
	function cek_serial_usb(){
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki/shiroki_cek_modul",
            // data: {
            //     manifest: manifest,
            //     alarm: alarm
            // },
            cache: false,
            success: function(point){
                $('#hasil_scan').html(point.hasil_scan);
                $('#list_scan').html(point.list_scan);
            }
        });
    }
    function test_serial() {
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki/shiroki_test_modul",
            data: {
                list_scan: $('#list_scan').val(),
                test_data: $('#test_data').val()
            },
            cache: false,
            success: function(point){
                
            }
        });
    };
</script>
