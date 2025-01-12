
<script>
    var audio_beep = new Audio('<?php echo base_url(); ?>assets/audio/beep.mp3');
    var audio_good = new Audio('<?php echo base_url(); ?>assets/audio/good.mp3');
    var audio_ng = new Audio('<?php echo base_url(); ?>assets/audio/ng.mp3');
    var manifest_100 = new Audio('<?php echo base_url(); ?>assets/audio/manifest_ini_telah_selesai_diproses.mp3');
    var manifest_404 = new Audio('<?php echo base_url(); ?>assets/audio/kode_manifest_tidak_ditemukan.mp3');
    var manifest_200 = new Audio('<?php echo base_url(); ?>assets/audio/memproses_manifest.mp3');
    var part_404 = new Audio('<?php echo base_url(); ?>assets/audio/ditolak_silakan_coba_lagi.mp3');
    var part_200 = new Audio('<?php echo base_url(); ?>assets/audio/suz/kode_diterima_f.mp3');
    var part_100 = new Audio('<?php echo base_url(); ?>assets/audio/kode_ini_telah_selesai_silakan_scan_kode_lainnya.mp3');
    var shiroki_404 = new Audio('<?php echo base_url(); ?>assets/audio/ditolak_silakan_coba_lagi.mp3');
    var shiroki_200 = new Audio('<?php echo base_url(); ?>assets/audio/suz/kanban_shiroki_diterima_f.mp3');
    var manifest_ok = new Audio('<?php echo base_url(); ?>assets/audio/seluruh_part_telah_selesai_terscan_silakan_tutup_manifest_ini_untuk_membuka_manifest_baru.mp3');
    var shiroki_500 = new Audio('<?php echo base_url(); ?>assets/audio/ditolak_tuliskan_penyebab_kesalahan_scan.mp3');
    var shiroki_501 = new Audio('<?php echo base_url(); ?>assets/audio/terdeteksi_kesalahan_scan_sebelumnya_silakan_tuliskan_penyebab_salah_scan.mp3');
    var shiroki_333 = new Audio('<?php echo base_url(); ?>assets/audio/terima_kasih.mp3');
    
</script>
<style>
    #divf {
        position: fixed; /* Fixed/sticky position */
        bottom: 20px; /* Place the button at the bottom of the page */
        right: 30px; /* Place the button 30px from the right */
        z-index: 99; /* Make sure it does not overlap */
        border: none; /* Remove borders */
        outline: none; /* Remove outline */
        /* padding: 15px; */
        border-radius: 10px; /* Rounded corners */
        font-size: 18px; /* Increase font size */
        min-width: 400px;
    }
</style>
<script>
    function cek_proses(){
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki/shiroki_proses_manifest",
            cache: false,
            success: function(point){
                if(point.val == 100){
                    $('#ok_tutup').modal('show');
                }else{
                    $('#ng_tutup').modal('show');
                }
            }
        });
    }
    function submit_tutup(){
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki/shiroki_submit_tutup_manifest",
            data: {
                nama: $('#nama').val(),
                pass: $('#pass').val(),
                alasan: $('#alasan').val()
            },
            cache: false,
            success: function(point){
                if(point.note == 1){
                    location.href = '<?php echo base_url(); ?>shiroki_manifest_run';
                }else{
                    $('#tutup_note').html(point.note);
                }
            }
        });
    }
</script>
<div class="content-wrapper">
	<section class="content-header">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <h1><i class="fa fa-upload"></i> Manifest <?php echo $manifest; ?> <?php if(!empty($alarm)){echo '<small class="float-right">Alarm: '.$alarm.'</small>';} ?></h1>
                    <button onclick="cek_proses()" class="btn btn-warning float-right"><i class="fa fa-times"></i> Tutup Manifest</button>
                </div>
            </div>
        </div>
    </section>
    <div class="modal fade" id="ok_tutup">
        <div class="modal-dialog">
            <div class="modal-content bg-primary">
                <div class="modal-header">
                    <h4 class="modal-title">Tutup Manifest</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    Proses Manifest 100%
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="<?php echo base_url(); ?>shiroki_manifest_cancel" class="btn btn-success float-right"><i class="fa fa-times"></i> Tutup Manifest</a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ng_tutup">
        <div class="modal-dialog">
            <div class="modal-content bg-primary">
                <div class="modal-header">
                    <h4 class="modal-title">Tutup Manifest</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <table class="table">
                        <tr>
                            <td colspan="2">Proses belum 100%</td>
                        </tr>
                        <tr>
                            <td>Nama</td>
                            <td><input type="text" class="form-control" id="nama" required></td>
                        </tr>
                        <tr>
                            <td>Pass</td>
                            <td><input type="password" class="form-control" id="pass" required></td>
                        </tr>
                        <tr>
                            <td>Alasan</td>
                            <td><textarea type="text"rows="3" class="form-control" id="alasan" required></textarea></td>
                        </tr>
                    </table>
                    <span class="text-red" id="tutup_note"></span>
                </div>
                <div class="modal-footer justify-content-between">
                    <button onclick="submit_tutup()" class="btn btn-success float-right"><i class="fa fa-times"></i> Tutup Manifest</button>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="card" style="margin-bottom: 205px;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 col-12" id="manifest_table">
                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<div class="card card-primary" id="divf">
    <div class="card-header">
        <h4 class="card-title">Scan Kanban Customer</h4>
    </div>
    <div class="card-body">
        <input type="text" id="scan_part" class="form-control" autocomplete="off" placeholder="SCAN KANBAN CUSTOMER"/>
        <span id="reload_button"></span>
    </div>
</div>
<div class="modal fade" id="modal_shiroki">
    <div class="modal-dialog">
        <div class="modal-content bg-primary">
            <div class="modal-header">
                <h4 class="modal-title">Scan QR Shiroki</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <label>Kanban Shiroki</label>
                <input type="text" id="scan_shiroki" class="form-control" autocomplete="off" placeholder="SCAN SHIROKI"/>
                <span id="reload_shiroki"></span>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal_salah">
    <div class="modal-dialog">
        <div class="modal-content bg-danger">
            <div class="modal-header">
                <h4 class="modal-title">Note</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <label>Tuliskan penyebab kesalahan scan</label>
                <textarea id="scan_salah" class="form-control" autocomplete="off" placeholder="Penyebab kesalahan scan"> </textarea>
                <span id="reload_salah"></span>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" onclick="submit_salah()">Submit</button>
            </div>
        </div>
    </div>
</div>
<script>
    var waiting = 0;
    var scanpart = 0;
    var valid_part = '';
    var salah = 0;
    var enable_cus = 1;
    var enable_shi = 0;

    halt = 0;
    $('#ng_tutup').on('hidden.bs.modal', function () {
        console.log('Modal is now hidden');
        halt = 0;
    });
    $('#ng_tutup').on('shown.bs.modal', function () {
        console.log('Modal is now shown');
        halt = 1;
    });
    setInterval(function(){
        if(waiting == 0 && scanpart == 0 && enable_cus == 1){
            if(halt == 0){
                keep_focus();
            }
        }
        if(waiting == 0 && scanpart == 1 && enable_shi == 1){
            if(halt == 0){
                keep_focus2();
            }
        }
        if(salah == 1){
            $('#scan_salah').focus();
        }
    },1000);
    $("#scan_part").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            if(waiting == 0 && scanpart == 0 && enable_cus == 1){
                cek_part();
            }
        }
    });
    function cek_part(){
        var part = $('#scan_part').val();
        if ($("#scan_part").val().length > 1) { //leng
            offall();
            // audio_beep.play();
            waiting = 1;
            $('#reload_button').html('Checking...');
            load_part();
            $('#scan_part').val('');
        }
    }

    $("#scan_part").on("input", function() {
        delay(function(){
            if ($("#scan_part").val().length < 1) { //leng
                $("#scan_part").val("");
            }
        }, 2000 ); //leng
    });
    // const mypartInput = document.getElementById('scan_part');
    // mypartInput.onpaste = e => e.preventDefault();

    function keep_focus(){
        $('#scan_part').focus();
    }
    function load_part(){
        var part = $('#scan_part').val();
        $('#reload_button').html('');
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki_cek_part_manifest",
            data: {
                part: part
            },
            cache: false,
            success: function(point){
                if(point.scan_salah == 0){
                    if(point.note != 'good' && point.note != 'fin'){
                        part_404.play();
                        $('#reload_button').html(point.note);
                    }else if(point.note == 'fin'){
                        part_100.play();
                        $('#reload_button').html('Part ini sudah ter-scan komplit!');
                    }else{
                        scanpart = 1;
                        valid_part = part;
                        $('#reload_shiroki').html('');
                        $('#modal_shiroki').modal('show');
                        part_200.play();
                        enable_shi = 1;
                        nyala_ok();
                    }
                    waiting = 0;
                }else{
                    shiroki_501.play();
                    $('#modal_salah').modal('show');
                    salah = 1;
                    enable_cus = 0;
                    nyala_err();
                }
            }
        });
    }
    $("#scan_shiroki").on('keyup', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            if(waiting == 0 && scanpart == 1 && enable_shi == 1){
                cek_shiroki();
            }
        }
    });
    function cek_shiroki(){
        var shiroki = $('#scan_shiroki').val();
        if ($("#scan_shiroki").val().length > 1) { //leng
            offall();
            // audio_beep.play();
            waiting = 1;
            $('#reload_shiroki').html('Checking...');
            load_shiroki();
            $('#scan_shiroki').val('');
        }
    }
    $("#scan_shiroki").on("input", function() {
        delay(function(){
            if ($("#scan_shiroki").val().length < 1) { //leng
                $("#scan_shiroki").val("");
            }
        }, 2000 ); //leng
    });
    // const myshiInput = document.getElementById('scan_shiroki');
    // myshiInput.onpaste = e => e.preventDefault();

    function keep_focus2(){
        $('#scan_shiroki').focus();
    }
    function load_shiroki(){
        var shiroki = $('#scan_shiroki').val();
        $('#reload_shiroki').html('');
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki_cek_shiroki_manifest",
            data: {
                part: valid_part,
                shiroki: shiroki
            },
            cache: false,
            success: function(point){
                if(point.scan_salah == 0){
                    if(point.note == 'good'){
                        shiroki_200.play();
                        enable_shi = 0;
                        enable_cus = 1;
                        nyala_ok();
                        setTimeout(function(){
                            reload_manifest();
                            $('#modal_shiroki').modal('hide');
                        }, 2500);
                    }else{
                        $('#reload_shiroki').html(point.note);
                        $('#scan_shiroki').val('');
                        shiroki_404.play();
                    }
                    waiting = 0;
                }else{
                    shiroki_500.play();
                    salah = 1;
                    enable_shi = 0;
                    enable_cus = 0;
                    $('#modal_shiroki').modal('hide');
                    $('#modal_salah').modal('show');
                    nyala_err();
                }
            }
        });
    }
    function cancel_part(){
        $('#scan_part').val('');
        valid_part = '';
        waiting = 0;
        scanpart = 0;
        salah = 0;
        enable_shi = 0;
        enable_cus = 1;
        offall();
    }
    $('#modal_shiroki').on('hidden.bs.modal', function () {
        if(salah == 0){
            cancel_part();
        }
    });
    $('#modal_salah').on('hidden.bs.modal', function () {
        cek_salah();
    });
    function cek_salah(){
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki_cek_salah_scan",
            cache: false,
            success: function(point){
                if(point.scan_salah == 1){
                    waiting = 1;
                    $('#modal_salah').modal('show');
                    salah = 1;
                    enable_shi = 0;
                    enable_cus = 0;
                    nyala_err();
                }
            }
        });
    }
    function submit_salah(){
        var scan_salah_txt = $('#scan_salah').val();
        if(scan_salah_txt != '' && scan_salah_txt != null){
            $.ajax({
                type: "POST",
                url: "<?php echo base_url(); ?>shiroki_submit_salah_scan",
                data: {
                    salah: scan_salah_txt
                },
                cache: false,
                success: function(point){
                    if(point.note == 'ok'){
                        $('#scan_salah').val('');
                        shiroki_333.play();
                        $('#modal_salah').modal('hide');
                        cancel_part();
                        reload_manifest();
                    }else{
                        $('#reload_salah').html('Anda harus menuliskan penyebab kesalahan scan');            
                    }
                }
            });
        }else{
            $('#reload_salah').html('Anda harus menuliskan penyebab kesalahan scan');
        }
    }
    function reload_manifest(){
        $('#manifest_table').load('<?php echo base_url(); ?>shiroki_manifest_table');
        offall();
    }
    reload_manifest();


    
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
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();
</script>
