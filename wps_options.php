<?php
 function wps_options(){ 
 global $wpdb;?>
  <?php
  $_okk=false;
   $options_array=get_option('text_field-group');
   if($options_array){
    $_today=$options_array['today_orders_name'];
    $_search=$options_array['search_by_name'];
    $_total=$options_array['total_sum_name'];
  }else{
    $_today='Today orders';
	$_search='Search by Sku';
	$_total='Total sum';
  }
 
  $is_isset=false;
  if(isset($_POST['today_orders_name']) ){
   if($_POST['today_orders_name']!=''){
     $_today= $_POST['today_orders_name'];
     $is_isset=true;
	}
   }
   if(isset($_POST['search_by_name'])){
    if($_POST['search_by_name']!=''){
     $_search=$_POST['search_by_name'];
     $is_isset=true;
	}
   }
  if(isset($_POST['total_sum_name'])){
    if($_POST['total_sum_name']!=''){
     $_total=$_POST['total_sum_name'];
     $is_isset=true;
	}
   }
   if($is_isset==true){
     $_ok=update_option('text_field-group',
	                    array('today_orders_name'=> $_today,
	                          'search_by_name'=>$_search,
							  'total_sum_name'=>$_total
	                          ));
		 if($_ok==true){ ?>
       <div class="updated">
	    <p><?php echo 'Options successfully updated. '; ?></p>
       </div>
	   <?php
     }else if($_ok==false){
        ?>
       <div class="updated">
	    <p><?php echo 'Error-Options not updated. '; ?></p>
       </div>
	   <?php
     }						  
		}					  
  /********* refresh ********************/	
  	if(isset($_POST['refsubmit'])){
   if(isset($_POST['interval'])){
     $_okk=update_option('wps_time_interval',($_POST['interval']*60000));
	//update_option('wps_refresh','yes');
	
  }else{
  //update_option('wps_refresh','no');
 }  
	
    if($_okk==true){ ?>
       <div class="updated">
	    <p><?php echo 'Refresh interval successfully updated. '; ?></p>
       </div>
	   <?php
     }else if($_okk==false){
        ?>
       <div class="updated">
	    <p><?php echo 'Refresh interval not updated. '; ?></p>
       </div>
	   <?php
     }	
}						  
  ?>
  <!--container-->
  <div class="wrap"   >
     <!--first row-->
    <div class="row" >
	  <div class="header-opt"><h3>Woo POS Sync Options</h3></div>
	</div>
	<!--end first row-->
	<!--second  row-->
	 <div class="row" >
	 
	  <div class="col-md-6" >
	  <div class="panel panel-default "  >
     <div class="panel-heading "  >
	 <h5>Text settings</h5>
	 </div>
	 <div class="panel-body "  >
	  <form method="post" action="" >
		<table >
		<tr valign="top">
        <th scope="row"><p>Search by sku[text]</p></th>
        <td><input type="text" name="search_by_name" value="" /></td>
		<td><image class="td-im" src=<?php echo plugin_dir_url(__FILE__).'images/search.png'; ?> /></td>
        </tr>
        <tr valign="top">
        <th scope="row"><p>Today orders[text]<p></th>
        <td><input type="text" name="today_orders_name" value="" /></td>
		<td><image class="td-im" src=<?php echo plugin_dir_url(__FILE__).'images/today.png'; ?> /></td>
        </tr>
		<tr valign="top">
        <th scope="row"><p>Total sum[text]<p></th>
        <td><input type="text" name="total_sum_name" value="" /></td>
		<td><image class="td-im" src=<?php echo plugin_dir_url(__FILE__).'images/total.png'; ?> /></td>
        </tr>
      </table>
	 <input  name="mySubmit" type="submit" class="button button-primary" value="<?php _e('Save changes','woocommerce')  ?>" />
		
	  </form></div>
	  </div>
	 </div>
	 </div>
	 <!--end second row-->
	<div class="divider"></div>
	<!--refresh row-->
	<div class="row" >
	 
	   <div class="col-md-6" >
	    <div class="panel panel-default "  >
		  <div class="panel-heading "  >
	       <h5>Refresh settings</h5>
	      </div>
		   <div class="panel-body "  >
		  
			  <input type="checkbox" name="ref_check" id="refcheck" value="0" <?php 
			  $ref=get_option('wps_refresh');
			 if($ref=='yes'){
			  echo 'checked'; 
			  }else{ 
			  }?>>Enable refresh<br>
          <div class="divider"></div><div class="divider"></div>
		 <form id="form" method="post" action=""  >
		    <table>
			 <tr  valign="top">
              <th scope="row"><p>Set interval</p></th>
              <td><input id="_interval" type="number" name="interval" step="0.5" value="<?php 
			  $ti= get_option('wps_time_interval');
			  $ti=$ti/60000;
			  echo $ti;
			  ?>" min="1" max="10" />minutes</td>
			  <td><input  name="refsubmit" type="submit" class="button button-primary btn-r" value="<?php _e('Save changes','woocommerce')  ?>" /></td>
		     </tr>
			</table>
			</form>
		   </div>
		</div>
	   </div>
	</div>
	<!--end refresh row-->
  </div>
  
 
 <!--end container-->
 <?php }
?>