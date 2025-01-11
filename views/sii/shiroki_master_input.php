<div class="content-wrapper">
	<section class="content-header">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <h1><i class="fa fa-upload"></i> Input Master Data</h1>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
		<div class="content-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-xs-12">
                    <div class="card">
                        <form method="POST" action="<?php echo base_url(); ?>shiroki_master_submit">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-xs-12">
                                    <label>Paste here:</label>
                                    <textarea class="form-control" name="excel_data" /></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-12 col-xs-12">
                                    <div id="excel_table"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-primary">Submit</button>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(function() {
        $('textarea[name=excel_data]').keyup(function() {
            var data = $(this).val();
            var rows = data.split("\n");
            var table = $('<table class="table" />'); 
            var title = '<tr><th>No</th><th>Kanban Customer</th><th>Kanban Shiroki</th></tr>';
            table.append(title);
            var a = 0;
            var no = 1;
            for(var y in rows) {
                var cells = rows[y].split("\t");
                var row = $('<tr />');
                row.append('<td>' + no + '</td>');
                for(var x in cells) {
                    row.append('<td><input value="'+cells[x]+'" type="hidden" name="val['+a+'][]">'+cells[x]+'</td>');
                }
                a++;
                no++;
                table.append(row);
            }
            $('#excel_table').html(table);
        });
    });
</script>