<div class="content-wrapper">
	<section class="content-header">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <h1><i class="fa fa-upload"></i> Input Manifest Data</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="inputfile" accept=".txt">
                                <label class="custom-file-label" for="customFile">Pilih file .txt</label>
                            </div>
                        </div>
                        <form method="POST" action="<?php echo base_url(); ?>shiroki_manifest_submit">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-xs-12 table-responsive">
                                    <div id="excel_table"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary">Submit</button>
                            <span id="tot"></span>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    var txt = '';
    document.getElementById('inputfile') .addEventListener('change', function() { 
        var fr = new FileReader();
        fr.onload=function(){ 
            txt = fr.result;
            $('#excel_table').html('<i class="fa fa-spinner fa-spin"></i> loading data');
            setTimeout(() => {
                generate_table();
            }, 1000);
        } 
        fr.readAsText(this.files[0]); 
    });
    $(function() {
        $('textarea[name=excel_data]').focus(function() {
            $('textarea[name=excel_data]').val('');    
        });
    });
    // $(function() {
    //     $('textarea[name=excel_data]').keyup(function() {
    //         $('#excel_table').html('<i class="fa fa-spinner fa-spin"></i> loading data');
    //         setTimeout(() => {
    //             generate_table();
    //         }, 1000);
    //     });
    // });
    function generate_table(){
        // var data = $('#area').val();
            var data = txt;
            var rows = data.split("\n");
            var table = $('<table class="table" style="white-space: nowrap;" />'); 
            var title = '<tr>'+
                            '<th>Valid Row</th>'+
                            '<th>Manifest no</th>'+
                            '<th>Supplier Code</th>'+
                            '<th></th>'+
                            '<th></th>'+
                            '<th></th>'+
                            '<th>Dock code</th>'+
                            '<th>Order no</th>'+
                            '<th>Part no</th>'+
                            '<th>Prod Line TMMIN</th>'+
                            '<th></th>'+
                            '<th></th>'+
                            '<th>Unique no</th>'+
                            '<th>Qty per KBN</th>'+
                            '<th>Qty Order</th>'+
                            '<th>Qty KBN</th>'+
                            '<th>Supplier</th>'+
                            '<th>Part name</th>'+
                            '<th></th>'+
                            '<th></th>'+
                            '<th></th>'+
                            '<th></th>'+
                            '<th>Part Nox</th>'+
                            '<th>Multi id</th>'+
                        '</tr>';
            table.append(title);
            var a = 0;
            var no = 1;
            var yy = false;
            var totvar = 0;
            for(var y in rows) {
                var cells = rows[y].split("\t");
                var row = $('<tr />');
                row.append('<td>' + no + '</td>');
                yy = false;
                for(var x in cells) {
                    if(x == 0){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="manifest[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 1){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="supplier_code[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 2){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na1[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 4){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na2[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 5){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na3[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 6){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="dock_code[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 7){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="order_no[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 10){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="part_no[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 11){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="prodline_tmmin[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 12){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na4[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 13){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na5[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 14){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="unique_no[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 15){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="qty_per_kanban[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 16){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="qty_order[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 17){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="qty_kanban[]">'+cells[x]+'</td>');
                        if(cells[x] != 0){
                            yy = true;
                        }
                        totvar++;
                    }
                    if(x == 21){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="supplier[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 22){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="part_name[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 23){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na6[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 24){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na7[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 25){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na8[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 26){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="na9[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 84){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="part_nox[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                    if(x == 92){
                        row.append('<td><input value="'+cells[x]+'" type="hidden" name="multi[]">'+cells[x]+'</td>');
                        totvar++;
                    }
                }
                if(yy == true){
                    table.append(row);
                }
                a++;
                no++;
            }
            $('#tot').html(totvar + ' vars, Limit 25.000 vars');
            $('#excel_table').html(table);
            $('textarea[name=excel_data]').blur();
    }
</script>