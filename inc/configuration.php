<?php 
// Save/Update configuration value
global $wpdb;
if(sanitize_text_field(!empty($_POST['submit']))){
	  $configVal = sanitize_text_field($_POST['lisg_auto_forced']);
	  $ordersplitterproCondition = sanitize_text_field($_POST['lisg_ordersplitterpro']);
	  $optionVal = get_option( 'lisg_auto_forced' );
	  $option_name = 'lisg_auto_forced' ;
	  $option_name_order_splitter = 'lisg_ordersplitterpro' ;
      $new_value = $configVal;
      update_option( $option_name, $new_value );
      update_option( $option_name_order_splitter, $ordersplitterproCondition );
     echo "<div class='form-save-msg'>Changes Saved!</div>";
}
  $optionVal = get_option( 'lisg_auto_forced' );
  $ordersplitterpro = get_option( 'lisg_ordersplitterpro' );
?>

<?php
if (sanitize_text_field(!empty($_GET['vari'])) && sanitize_text_field($_GET['vari']) == 'yes') {} else {
    ?>
    <h1>General Configuration</h1>
    <div class="row">
        <div class="form-group">
            <form action="" method="post">
                <div><label for="sort" class="col-sm-2 control-label"> Enable split order </label>
                    <select class="form-control" name="lisg_auto_forced" id="sort">
                        <option value="no" <?php
                        if ($optionVal == 'no') {
                            echo 'selected';
                        }
                        ?>>No</option>
                        <option value="yes" <?php
                        if ($optionVal == 'yes') {
                            echo 'selected';
                        }
                        ?>>Yes</option>
                    </select> 
                </div> 
                <br>
                <div>

                    <label for="sort" class="col-sm-2 control-label"> Split Order Conditions </label>
                    <select class="form-control" name="lisg_ordersplitterpro" id="sort">
                        <option value="default" <?php
                        if ($ordersplitterpro == 'default') {
                            echo 'selected';
                        }
                        ?>>Default</option>
                        
						

                    </select> 
                                        <br><br>
                    <input type="submit" name="submit" value="save config">
                </div>
            </form>
        </div>
    </div>
<?php } ?>
