<script>
    var audio_beep = new Audio('<?php echo base_url(); ?>assets/audio/beep.mp3');
    var audio_good = new Audio('<?php echo base_url(); ?>assets/audio/good.mp3');
    var audio_ng = new Audio('<?php echo base_url(); ?>assets/audio/ng.mp3');
    var manifest_100 = new Audio('<?php echo base_url(); ?>assets/audio/manifest_ini_telah_selesai_diproses.mp3');
    var manifest_404 = new Audio('<?php echo base_url(); ?>assets/audio/kode_manifest_tidak_ditemukan.mp3');
    var manifest_200 = new Audio('<?php echo base_url(); ?>assets/audio/memproses_manifest.mp3');
    var part_404 = new Audio('<?php echo base_url(); ?>assets/audio/ditolak_silakan_coba_lagi.mp3');
    var part_200 = new Audio('<?php echo base_url(); ?>assets/audio/kode_diterima.mp3');
    var part_100 = new Audio('<?php echo base_url(); ?>assets/audio/kode_ini_telah_selesai_silakan_scan_kode_lainnya.mp3');
    var shiroki_200 = new Audio('<?php echo base_url(); ?>assets/audio/kanban_shiroki_diterima.mp3');
    var manifest_ok = new Audio('<?php echo base_url(); ?>assets/audio/seluruh_part_telah_selesai_terscan_silakan_tutup_manifest_ini_untuk_membuka_manifest_baru.mp3');
    var manifest_502 = new Audio('<?php echo base_url(); ?>assets/audio/ditemukan_kanban_yang_belum_terdaftar.mp3');
</script>
<div class="content-wrapper">
	<section class="content-header">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <h1><i class="fa fa-upload"></i> Jalankan Manifest</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8 col-md-8 col-xs-12">
                                    <label>Scan barcode Manifest untuk memulai/melanjutkan.</label>
                                    <div class="form-group">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" class="custom-control-input" id="manual_interupt">
                                            <label class="custom-control-label" for="manual_interupt"> Interupt Manual</label>
                                        </div>
                                    </div>
                                    <input type="text" id="manifest" class="form-control" autocomplete="off" placeholder="SCAN MANIFEST"/>
                                </div>
                                <div class="col-lg-4 col-md-4 col-xs-12">
                                    <label>Modul Alarm.</label>
                                    <select id="alarm" class="form-control sel2" onfocus="holdabit()" onblur="nohold()" onchange="nohold()">
                                    <?php
                                        if(!empty($alarm)){
                                            foreach($alarm as $row){
                                    ?>
                                        <option value="<?php echo $row->unit_ip; ?>"><?php echo $row->unit_name; ?> (<?php echo $row->unit_ip; ?>)</option>
                                    <?php
                                            }
                                        }
                                    ?>
                                        <option value="">Tanpa Alarm</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <span id="reload_button"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="<?php echo base_url(); ?>assets/adminlte/plugins/select2/js/select2.full.min.js"></script>
<script>
    $(document).ready(function () {
		$(".sel2").select2({
        	minimumResultsForSearch: 20
		});
	});
    function holdabit(){
        waiting = 1;
    }
    function nohold(){
        waiting = 0;
    }
    var waiting = 0;
    setInterval(function(){
        if(waiting == 0){
            keep_focus();
        }
    },1000);
    $("#manifest").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            if(waiting == 0){
                cek_manifest();
            }
        }
    });
    var switchStatus = false;
    $("#manual_interupt").on('change', function() {
        if ($(this).is(':checked')) {
            switchStatus = $(this).is(':checked');
            console.log(switchStatus);// To verify
        }
        else {
            switchStatus = $(this).is(':checked');
            console.log(switchStatus);// To verify
            cek_manifest();
        }
    });

    function cek_manifest(){
        if(switchStatus == false){
            var manifest = $('#manifest').val();
            // audio_beep.play();
            waiting = 1;
            $('#reload_button').html('Checking...');
            load_row();
            $('#manifest').val('');
        }
    }
    function keep_focus(){
        $('#manifest').focus();
    }
    function load_row(){
        var manifest = $('#manifest').val();
        var alarm = $('#alarm').val();
        $('#reload_button').html('');
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki_cek_manifest",
            data: {
                manifest: manifest,
                alarm: alarm
            },
            cache: false,
            success: function(point){
                if(point == 'ng'){
                    manifest_404.play();
                    waiting = 0;
                    $('#reload_button').html('Kode Manifest tidak ditemukan!');
                }else if(point == 'fin'){
                    manifest_100.play();
                    waiting = 0;
                    $('#reload_button').html('Manifest telah selesai diproses, periksa [data manifest] untuk detailnya');
                }else if(point == 'good'){
                    manifest_200.play();
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                }else{
                    manifest_502.play();
                    waiting = 0;
                    $('#reload_button').html(point);
                }
            }
        });
    }
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $("#manifest").on("input", function() {
        if(switchStatus == false){
            delay(function(){
                if ($("#manifest").val().length < 1) { //leng
                    $("#manifest").val("");
                }
            }, 2000 ); //leng
        }
    });
    function nyala_ok(){
        $.ajax({
            type: "GET",
            url: "<?php echo base_url(); ?>shiroki/shiroki_alarm/1",
            cache: false
        });
    }
    function nyala_err(){
        $.ajax({
            type: "GET",
            url: "<?php echo base_url(); ?>shiroki/shiroki_alarm/2",
            cache: false
        });
    }
    function offall(){
        $.ajax({
            type: "GET",
            url: "<?php echo base_url(); ?>shiroki/shiroki_alarm/3",
            cache: false
        });
    }
    // const myInput = document.getElementById('manifest');
    // myInput.onpaste = e => e.preventDefault();
</script>