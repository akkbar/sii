<table class="table">
    <tr>
        <th>No</th>
        <th>Result</th>
        <th>Manifest</th>
        <th>Kanban Customer</th>
        <th>Kanban Shiroki</th>
        <th>Note</th>
        <th>User</th>
    </tr>
<?php
    $a = 1;
    $prog = 1;
    if(!empty($manifest_scan)){
        foreach($manifest_scan as $row){
?>
    <tr>
        <td><?php echo $a; ?></td>
        <td><?php if($row->result == 1){
				echo '<span class="badge badge-success">Scan Berhasil</span>';
			}elseif($row->result == 2){
				echo '<span class="badge badge-warning">Scan Customer OK</span>';
			}elseif($row->result == 0 and empty($row->scan_shiroki)){
				echo '<span class="badge badge-danger">Scan Customer Gagal</span>';
			}elseif($row->result == 0 and !empty($row->scan_shiroki)){
				echo '<span class="badge badge-danger">Scan Shiroki Gagal</span>';
			} ?></td>
        <td><?php echo $row->manifest_id; ?></td>
        <td><?php echo $row->scan_part; ?></td>
        <td><?php echo $row->scan_shiroki; ?></td>
        <td><?php echo $row->note; ?></td>
        <td><?php echo $row->uName; ?></td>
    </tr>
<?php
            $a++;
        }   
    }
?>
</table>