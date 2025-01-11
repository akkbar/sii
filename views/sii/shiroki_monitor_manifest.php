<table class="table">
    <tr>
        <th>NO.</th>
        <th>MANIFEST</th>
        <th colspan="2">PROGRESS</th>
        <th>ETD</th>
        <th>DOCK CODE</th>
    </tr>
<?php
    $a = 1;
    $prog = 1;
    if(!empty($manifest_table)){
        foreach($manifest_table as $row){
?>
    <tr>
        <td><?php echo $a; ?></td>
        <td><a href="<?php echo base_url(); ?>shiroki_log_manifest/<?php echo $this->encrypt_model->my_encrypt($row->manifest); ?>" class="btn btn-sm btn-primary"><?php echo $row->manifest; ?></a></td>
        <td><?php echo round($row->prog, 1); ?>%</td>
        <td style="min-width: 100px;">
            <div class="progress mb-3">
                <div class="progress-bar bg-success" role="progressbar" aria-valuenow="<?php echo round($row->prog); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo round($row->prog, 1); ?>%">
                    <span class="sr-only"><?php echo round($row->prog, 1); ?>%</span>
                </div>
            </div>
        </td>
        <td><?php echo date('d-m-Y H:i', strtotime(substr($row->na7, 0, -4))); ?></td>
        <td><?php echo $row->dock_code; ?></td>
        
    </tr>
<?php
            $a++;
        }   
    }
?>
</table>