<style>
th {
  background: white;
  position: sticky;
  top: 0; /* Don't forget this, required for the stickiness */
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
}
</style>
<table class="table">
    <tr>
        <th>NO.</th>
        <th>ORDER NO</th>
        <th>PART NO.</th>
        <th>UNIQUE NO.</th>
        <th>PART NAME</th>
        <th>PCS/KBN</th>
        <th>NO of KBN</th>
        <th>TTL QTY</th>
        <th>GOOD</th>
        <th>NG</th>
        <th>SISA</th>
    </tr>
<?php
    $a = 1;
    $prog = 1;
    if(!empty($manifest_table)){
        foreach($manifest_table as $row){
?>
<!-- <tr <?php //if($row[9] == 0){echo 'style="background-color:#5cff87"'; $prog++;}elseif($row[9] < $row[6] and $row[9] != 0){echo 'style="background-color:#c7c8c9"';} ?>> -->
    <tr <?php if($row[9] == 0){echo 'style="background-color:#5cff87"'; $prog++;}elseif($row[9] < $row[5] and $row[9] != 0){echo 'style="background-color:#c7c8c9"';} ?>>
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
<script>
    <?php if($prog == $a){ ?>
        $(document).ready(function(){
            manifest_ok.play();
        });
    <?php } ?>
</script>