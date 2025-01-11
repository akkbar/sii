<style>
    .background_blue{
        background: #8ddae3;
    }
    .background_yellow{
        background: #b1b3b1;
    }
    .background_red{
        background: #f57f89;
    }
    .background_green{
        background: #53c949;
    }
    .background_white{
        background: #ffffff;
    }
  #xcard{
        -webkit-transition: background 1.0s ease-in-out;
        -ms-transition:     background 1.0s ease-in-out;
        transition:         background 1.0s ease-in-out;
    }
</style>
<div class="content-wrapper">
	<section class="content-header">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <h1><i class="fa fa-qrcode"></i> Scanner</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="card" id="xcard">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 col-12">
                                    <label>Kanban Customer: </label>
                                    <input type="text" id="kanban_cus" class="form-control" autocomplete="off" placeholder="SCAN CUSTOMER"/>
                                </div>
                                <div class="col-md-12 col-12">
                                    <div id="hasil_scan"></div>
                                </div>
                                <div class="col-md-12 col-12">
                                    <label>Kanban Shiroki: </label>
                                    <input type="text" id="kanban_shi" class="form-control" autocomplete="off" placeholder="SCAN PART"/>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div id="reload_button"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    var $div2blink = $("#xcard"); // Save reference, only look this item up once, then save
    var valid = 0;
    var shi = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    var id = 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
    var waiting = 0;
    setInterval(function(){
        if(valid == 0){
            $div2blink.toggleClass("background_blue");
            if(waiting == 0){
                cek_kanban_cus();
            }
        }
        if(valid == 1){
            $div2blink.toggleClass("background_yellow");
            if(waiting == 0){
                cek_kanban_shi();
            }
        }
        if(valid == 2){
            $div2blink.toggleClass("background_red");
            if(waiting == 0){
                cek_kanban_shi();
            }
            $('#kanban_shi').val('');
        }
        if(valid == 3){
            $div2blink.toggleClass("background_green");
            $('#kanban_cus').attr('readonly', true);
            $('#kanban_shi').attr('readonly', true);
        }
    },1000);
    function cek_kanban_cus(){
        var kanban_cus = $('#kanban_cus').val();
        if(kanban_cus != ''){
            waiting = 1;
            $('#reload_button').html('Checking...');
            setTimeout(function(){
                load_row();
                $('#kanban_cus').val('');
            }, 2500);
        }
        if (kanban_cus == ''){
            keep_focus();
        }
    }
    function keep_focus(){
        $('#kanban_cus').focus();
    }
    function cek_kanban_shi(){
        var kanban_shi = $('#kanban_shi').val();
        if(kanban_shi != ''){
            waiting = 1;
            $('#reload_button').html('Checking...');
            setTimeout(function(){
                if(kanban_shi == shi){
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>shiroki_log_scanner",
                        data: {
                            kanban_shi: kanban_shi,
                            id: id,
                            result: 1
                        },
                        cache: false,
                        success: function(point){
                            $('#kanban_shi').val('');
                            $('#hasil_scan').append('<h4>Success!</h4>');
                            $('#reload_button').html('<button class="btn btn-success" onclick="window.location.reload()"> SCAN AGAIN!</button>');
                            waiting = 0;
                        }
                    });
                    valid = 3;
                }else{
                    $.ajax({
                        type: "POST",
                        url: "<?php echo base_url(); ?>shiroki_log_scanner",
                        data: {
                            kanban_shi: kanban_shi,
                            id: id,
                            result: 0
                        },
                        cache: false,
                        success: function(point){
                            $('#kanban_shi').val('');
                            $('#reload_button').html('');
                            waiting = 0;
                        }
                    });
                    valid = 2;
                }
            }, 2500);
        }
        if (kanban_shi == ''){
            keep_focus2();
        }
    }
    function keep_focus2(){
        $('#kanban_shi').focus();
    }
    function load_row(){
        var kanban_cus = $('#kanban_cus').val();
        $('#hasil_scan').html('');
        $.ajax({
            type: "POST",
            url: "<?php echo base_url(); ?>shiroki_cek_scanner",
            data: {
                kanban_cus: kanban_cus
            },
            cache: false,
            success: function(point){
                $('#hasil_scan').html(point.p);
                valid = point.valid;
                shi = point.shi;
                id = point.id;
                waiting = 0;
                $('#reload_button').html('');
            }
        });
    }
    function log_attempt(){

    }
</script>