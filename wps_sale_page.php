<?php

function wps_sale_page()
{
 global $wpdb;
global $woocommerce;
//check if woocommerce exist on the site and his  version
if(is_object( $woocommerce ) && version_compare( $woocommerce->version, '2.1', '>=') ){
 ?>
  <div class="container" style="background-color:#ffffff!important;"  >
  <div class="" id="notice">
	    		
       </div>
      <!-- search row -->
<div class="row" >
   <!-- search -->
  <div class="col-md-3" id="searchdiv"   >
    <div class="input-group">
      <input type="text" class="form-control" id="skusearch" >
      <span class="input-group-btn">
        <button class="btn btn-default" id="btn" type="button"><?php 
		$tx='Search by Sku';
		 $_srh=get_option('text_field-group');
		 if($_srh['search_by_name']!=null){
		   echo $_srh['search_by_name'];
		 }else echo $tx;
		 ?></button>
      </span>
    </div><!-- /input-group col-md-offset-3 -->
  </div><!-- /.col-md-3 -->
   <!-- end search -->
    </div>
   <!-- end search row -->
   <div class="row" id="action_board" ><!-- sale table row -->
    <div class="col-md-8" >
     <table class="table table-hover table-condensed tproducts" style="margin-left:-15px;" id="tbl">
		   <tbody id="trt" >
			<tr>
			<th class="col-md-1 del"></th>
			<th class="wimg"></th>
			<th class="sku"><?php _e('Sku','woocommerce') ?></th>
			<th class="name"><?php _e('Name','woocommerce') ?></th>
			<th class="variant"><?php _e('Variations','woocommerce') ?></th>
			<th class="counts"><?php _e('Qty','woocommerce') ?></th>
			<th class="price"><?php _e('Price','woocommerce') ?></th>
			<th class="sum"><?php _e('Total','woocommerce') ?></th>
			</tr>
		  </tbody>
		</table></div>
	<div class="col-md-4 col-md-offset-0 " >
		    <!-- checkout 
  <div class="col-sm-4 col-md-offset-1">-->
    <div class="panel panel-default _checkout" id="_checkout" >
     <div class="panel-heading phead"  >
      <?php _e('Checkout','woocommerce') ?>
     </div>
   <div class="panel-body "  >
        <table class="p-body">
         <tr >
          <td class="ch-td"><label  class="control-label "  ><?php _e( 'Cart Subtotal', 'woocommerce' ) ?></label></td>
           <td > <label class="control-label " id="subtotal" >0</label></td>
         </tr>
          <tr>
            <td class="ch-td" ><label  class="control-label "  ><?php _e( 'Tax', 'woocommerce' ) ?></label></td>
             <td  > <label class="control-label" id="taxlabel" >0</label></td>
          </tr>
           <tr>
              <td class="ch-td" ><label  class="control-label " ><?php _e( 'Order Total', 'woocommerce' ) ?></label></td>
             
             <td > <label class="control-label " id="sm" >0</label></td>
              
           </tr>
		   <tr class="tr-btn">
		    <td class="td-btn"><button type="button" class="btn btn-warning wps-btn" id="void"  >Void</button></td>
			<td class="td-btn"> <button type="button" class="btn btn-danger wps-btn-total "  id="endbtn"  >Total</button></td>
		   </tr>
        </table>
   
   </div>
  
 </div>
		 <div class="col-md-14"><!--orders-->
		   <div class="panel panel-default _checkout" >
		  <?php
		   //date_default_timezone_set('UTC');
		  	 $d=date('Y-m-d ');
		  
		          $oresults=$wpdb->get_results(
		                     "SELECT ID,post_title FROM $wpdb->posts 
							 WHERE `post_type`='shop_order' 
							 AND `post_date` LIKE '".$d."%'
							 AND `post_status`='publish' ORDER BY ID DESC "); 
			$oSum=0;	
            $cpos=null;			
		  if($oresults){					 
			  	foreach($oresults as $_oID){
              $_ord=new WC_Order( $_oID->ID);	
			  			  
			  $oSum+=$_ord->get_total();
             }		
       $cpos=array('currency' => $_ord->get_order_currency());		 
     }			 
							 ?>
     <div class="panel-heading phead"  >
	 <table  class="headtbl">
	  <th class="head-left"><?php
	  
	  $val= get_option('text_field-group'); 
        if($val){	  
	     echo $val['today_orders_name'];
		 $tSum=$val['total_sum_name'];
       }
       else	echo 'Today sales' ;  ?></th>
	  <th class="head-right" id="total_sum" ><?php 
         if($tSum!=null){
		   echo $tSum;
		 }else echo 'Total sum';
	  ?>-<?php echo wc_price($oSum,$cpos); ?></th>
	 </table>
      
     </div>
   <div class="panel-body" id="o_panel"  >
        <table class="headtbl" id="order_tbl">
         <?php
		if($oresults){
		   $oid='';					
	       foreach($oresults as $_order){	
		   //print_r( $_order->ID);
            $oid=$_order->ID;	
            $order = new WC_Order($oid);	
			$wps_order_url=get_bloginfo('url').'/wp-admin/post.php?post='.$oid.'&action=edit';
			$_name=$_order->post_title;
		
		 ?>
		 <tr  > 
		  <td class="td-left"><a href="<?php echo $wps_order_url; ?>" ><?php echo _e('Order','woocommerce').'_#'.$oid; ?>(<?php echo $order->order_date; ?>)</a></td>
		  <td class="td-right"><?php echo wc_price($order->get_total(),array('currency' => $order->get_order_currency())); ?></td>
		 </tr>
		 <?php } } ?>
        </table>
   
         </div>
	</div><!-- end orders-->
     </div>
      </div> 
      </div>
	<!--end sale table row -->
	    <!-- loading icons -->
	  <div id="cart_update" class="loading" >
           <!-- <div style="width:370px;height:220px;background:white;opacity:0.9;z-index:-1;" ></div>-->
            <div >
          <img  src=<?php echo plugin_dir_url(__FILE__).'images/ajax-loader.gif'; ?> title="Cart Updating" />
          <p>Cart Update...</p></div>
          </div>
	    <div id="loading" class="loading" >
           
          <img src=<?php echo plugin_dir_url(__FILE__).'images/ajax-loader.gif'; ?> title="Loading" />
		  <p>Loading...</p>
		  </div>
           <div id='paid'  >
         <img src=<?php echo plugin_dir_url(__FILE__).'images/paid.jpg'; ?> title="Paid" />
		  </div>
		  <div id="sale_procces" class="loading" >
         
            <div >
          <img  src=<?php echo plugin_dir_url(__FILE__).'images/ajax-loader.gif'; ?>  />
          <p>Sale processing ...</p></div>
          </div>
		      <!-- end loadings -->
   </div>
   <!--end container -->
    <?php
   }else{
      ?>
	   <div class="updated">
        <p><?php echo 'Woocommerce need to be version 2.1 or higher'; ?></p>
       </div>
	  <?php
   }
}


?>