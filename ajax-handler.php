<?php
/*
* ajax handler
*/
/*^^^^^^^^^^^^^^^^^^^^^^^^^^ get product functions ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^ */
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
        return;
 //using https://gist.github.com/mikejolley/3097073#file-gistfile1-txt
 //to remove emajls
 function unhook_those_pesky_emails( $email_class ) {
 
		/**
		 * Hooks for sending emails during store events
		 **/
		remove_action( 'woocommerce_low_stock_notification', array( $email_class, 'low_stock' ) );
		remove_action( 'woocommerce_no_stock_notification', array( $email_class, 'no_stock' ) );
		remove_action( 'woocommerce_product_on_backorder_notification', array( $email_class, 'backorder' ) );
		
		// New order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_class->emails['WC_Email_New_Order'], 'trigger' ) );
		
		// Processing order emails
		remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_class->emails['WC_Email_Customer_Processing_Order'], 'trigger' ) );
		
		// Completed order emails
		remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			
		// Note emails
		remove_action( 'woocommerce_new_customer_note_notification', array( $email_class->emails['WC_Email_Customer_Note'], 'trigger' ) );
}
 
function get_product_by_sku( $sku ) {

     global $wpdb;
     global $product;
     $product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );
   
     if ( $product_id ) {
         $product=get_product( $product_id );
		 if($product->product_type=='variable'){
           $product=new WC_Product_Variable($product);
         }else{
          $product=new WC_Product_Simple($product);
         }
	   return $product;
	  }
	  else{
		 return null;
	  }
   }
	 /*
       *  get childs(variations)
       *  @product_id-string
       * return array 
       */
	   $select='';
      function get_childs($product_id){
	   global $wpdb;
	   $pr=new WC_Product_Variable($product_id);
	   if($pr->is_in_stock()==false){
	    return  array('msg'=>'hidden');
	   }else{//not hidden
        $zero=0;
        $children = get_posts( array(
            'post_parent'   => $product_id,
            'posts_per_page'=> -1,
            'post_type'     => 'product_variation',
            'fields'        => 'ids',
            'post_status'   => 'publish'
         ) );
		if(!$children){
		      $s=array('msg'=>'no');
		    return $s;
		   }else{ // Loop the variations
		    $option='';
		    $select='<select name="attributes" class="select-mini " onchange="finalChoice(this)" style="width:80%;" >
			          <option>---</option>';
		    foreach ( $children as $child ){
				$variation=new WC_Product_Variation($child);
				if($variation->get_stock_quantity()>0){
				$activ='no';
				$activ_price=0;
				
				//chec product in activity
				 if($variation->regular_price>$variation->price){
					$activ='yes';
					$activ_price=$variation->price;
				
				  }else{
				
				 }
				 //get attributes
				 $attributes=$variation->get_variation_attributes();
				 //print_r($attributes);
				 $size='';
				 foreach($attributes as $k=>$v){
				    $att_name=$k;
				 	$sizes=get_term_by('slug',$v,substr($k,10));
				 	$size.=$sizes->name.'/';
				 	$slug=$v;
				 }
				 $qt=$variation->get_stock_quantity();
				 $pr=$variation->price;
				 $size=trim(substr($size,0,-1));
				 $_qty=' <input type="number" title="0" value="1" min="1" max="'+$variation->get_stock_quantity()+'" class="input-mini mytext"  >';
				 $null='none';
				   //create option for product
				   $opt='<option value='.$child.' 
				           data-parent_id='.$product_id.'
         				   data-quantity='.$qt.' 
						   data-att_name='.$att_name.'
						   data-cart_item_key='.$null.'
						   data-slug='.$slug.' >'.$size.'</option>';
				    $option.=$opt;
				  }
				}
				 $select.=$option;
				$select.='</select>';
				return $select;
		  }
	    }//end not hidden
      }
     /*
	 * function get product
	 */
	 function wps_get_product($sku){
	   global $woocommerce;
           $variations='';
	   $info=null;
           $cart_item_key=null;
           $product=get_product_by_sku($sku);
	  if($product==null){
		 /*
		 * if sku is wrong
		 */
	 echo   json_encode(array('msg'=>'wrong'));
	}else{
	  if($product->is_in_stock()==false){
          /*
          * if product no stock,return massage
          */
	 echo   json_encode(array('msg'=>'hidden'));
	    
        }
        elseif($product!=null&&$product->is_in_stock()==true){
        $quantity=0;
       /* get total stock on simple product,need populate product info array
        * and add to cart 
        */
       if($product->product_type=='simple'){
        $quantity=$product->get_total_stock();
        $woocommerce->cart->add_to_cart($product->id);
        //get item key
        foreach($woocommerce->cart->cart_contents as $k=>$v){
          if($v['product_id']==$product->id){
            $cart_item_key=$k;
          }
        }
		$tax_label='';
		 $formatted_amount='';
		 foreach ( WC()->cart->get_tax_totals() as $code => $tax ){
			$tax_label=esc_html( $tax->label );
			$formatted_amount=wp_kses_post( $tax->formatted_amount );
			}
		$info=array(
		   'cart_item_key'=>$cart_item_key,
		   'subtotal'=>wc_price($woocommerce->cart->subtotal_ex_tax),
		   'tax_label'=>$tax_label,
		   'formatted_amount'=>$formatted_amount,
		   'order_total'=> wc_price($woocommerce->cart->subtotal)
		);
        $variations='';
       }elseif($product->product_type=='variable'){
        $variations=get_childs($product->id);
       }else{
        /*
        * if product not 'simple' or 'variable' return massage,
        * not working with another type
        */
        echo   json_encode(array('msg'=>'WPS working only with simple and variable product type!'));
      }
	  /* populate product info array */
      $pro=array(
       'sku'=>$product->get_sku(),
       'title'=>$product->post->post->post_title,
       'price'=>$product->get_price(),
       'info'=>$info,
       'image'=>$product->get_image(),
       'id'=>$product->id,
       'quantity'=>$quantity,
       'type'=>$product->product_type,
       'cart_item_key'=>$cart_item_key,
       'select'=>$variations,
       'msg'=>'yes'
       );
       echo json_encode($pro);
     }else{
       $msg=array('msg'=>'no');
       echo json_encode($msg);
     }
	 }
	} // end get product function
	
	/*
	* get labels and currency positions
	*/
	/******************* UPDATE CART ****************************************/
	
	 /*
	 * add to cart
	 */
	 function wps_add_to_cart($_type,$prID,$varID,$_quantity,$atName,$_slug,$item_key){
	 	 global $woocommerce;
		  //check type
		 if($_type!='variation'){
		 	  echo json_encode(array('massage'=>'not right type'));
		 	  return;
			} 
			/*
			* if $item_key not null
			* remove cuurent one and add
			* with new values
			*/
			if($item_key!=null or $item_key!=''){
			 $woocommerce->cart->set_quantity($item_key,'0');
			}
			$variation=new WC_Product_Variation($varID);
			$attributes=$variation->get_variation_attributes();
			$_var='';
			 foreach($attributes as $k=>$v){
			  $_var[$k]=$v;
			 }
		   //$_var=array();
		   //$_var[$atName]=$_slug;
		   $woocommerce->cart->add_to_cart($prID,$_quantity,$varID,$_var);//
		    $ikey=null;
		   foreach($woocommerce->cart->cart_contents as $k=>$v){
		      // give item key
			 
             if ($v['product_id']==$prID and $v['variation_id']==$varID){
		       $ikey=$k;
	          }
			 }
			   $tax_label='';
			   $formatted_amount='';
			     foreach ( WC()->cart->get_tax_totals() as $code => $tax ){
				   $tax_label=esc_html( $tax->label );
				   $formatted_amount=wp_kses_post( $tax->formatted_amount );
				 }
			   $newInfo=array(
			   'cart_item_key'=>$ikey,
			   	'subtotal'=>wc_price($woocommerce->cart->subtotal_ex_tax),
				'tax_label'=>$tax_label,
				'formatted_amount'=>$formatted_amount,
				'order_total'=> wc_price($woocommerce->cart->subtotal)
			   );
			   echo json_encode($newInfo);
            
		   }
		
	/*************** END UPDATE CART ****************************************/
	
	/**
	* 
	* change item
	* 
	*/
	function wps_change_item($item_key,$_quantity){
		 global $woocommerce;
		 $woocommerce->cart->set_quantity($item_key,$_quantity);
		 $tax_label='';
		 $formatted_amount='';
		 foreach ( WC()->cart->get_tax_totals() as $code => $tax ){
		   $tax_label=esc_html( $tax->label );
		   $formatted_amount=wp_kses_post( $tax->formatted_amount );
		 }
		 $newInfo=array(
			 'cart_item_key'=>$item_key,
			 'subtotal'=>wc_price($woocommerce->cart->subtotal_ex_tax),
			 'tax_label'=>$tax_label,
			 'formatted_amount'=>$formatted_amount,
			 'order_total'=> wc_price($woocommerce->cart->subtotal)
			  );
			   echo json_encode($newInfo);
	}
		
	function wps_empty_cart(){
	  global $woocommerce;
      $woocommerce->cart->empty_cart(); 
	  $arr=array('alert'=>'ok');
	  echo json_encode($arr);
     }
	/*
	*
	*/
	function  wps_delete_from_cart($cart_key){
	    global $woocommerce;
		$woocommerce->cart->set_quantity($cart_key,'0');
		 $tax_label='';
		 $formatted_amount='';
		 foreach ( WC()->cart->get_tax_totals() as $code => $tax ){
		   $tax_label=esc_html( $tax->label );
		   $formatted_amount=wp_kses_post( $tax->formatted_amount );
		 }
		 $newInfo=array(
			 'cart_item_key'=>$cart_key,
			 'subtotal'=>wc_price($woocommerce->cart->subtotal_ex_tax),
			 'tax_label'=>$tax_label,
			 'formatted_amount'=>$formatted_amount,
			 'order_total'=> wc_price($woocommerce->cart->subtotal)
			  );
		echo json_encode($newInfo);
	}
	 
	 //create order
	 function wps_create_order($_name){
	    global $woocommerce;
		add_action( 'woocommerce_email', 'unhook_those_pesky_emails' );
		$_name.='_#';
		$woocommerce->cart->calculate_totals();
		//$woocommerce->checkout->check_cart_items();
		
		$orid=$woocommerce->checkout->create_order( );
		$order = new WC_Order( $orid);
	    $order->payment_complete();   
		$wps_order_url=admin_url().'post.php?post='.$orid.'&action=edit';
		 //$_total=wc_format_decimal( $woocommerce->cart->subtotal, get_option( 'woocommerce_price_num_decimals' ) );
		 update_post_meta( $orid, '_order_total', $woocommerce->cart->subtotal);
		 update_post_meta($orid,'_customer_user','1');
		 $order->update_status( 'completed' );
		 $woocommerce->cart->empty_cart(); 
		 echo json_encode(
		  array(
		  'name'=>$_name,
		  'order_id'=>$orid,
		  'total'=>wc_price( $order->get_total()),
		  'wps_order_url'=>$wps_order_url,
		  'order_date'=>$order->order_date
		 ));
		remove_action('woocommerce_email', 'unhook_those_pesky_emails'); 
	 }
	 
	 function wps_refresh(){
	    global $wpdb;
	    $d=date('Y-m-d ');
		 $oresults=$wpdb->get_results(
		            "SELECT ID,post_title FROM $wpdb->posts 
					WHERE `post_type`='shop_order' 
					AND `post_date` LIKE '".$d."%'
					AND `post_status`='publish' ORDER BY ID DESC "); 
		$oSum=0;	
        $cpos=null;		
		$wps_order_url='';
		$_name='';
		$trows='';
		if($oresults){					 
		 foreach($oresults as $_oID){
            $_ord=new WC_Order( $_oID->ID);	
		    $oSum+=$_ord->get_total();
			$wps_order_url=admin_url().'post.php?post='.$_oID->ID.'&action=edit';
			$_name=$_ord->post_title;
			$_row='<tr> 
		        <td class="td-left"><a href="'.$wps_order_url.'" >'. __('Order','woocommerce').'_#'.$_oID->ID.'('.$_ord->order_date.')</a></td>
		        <td class="td-right">'. wc_price($_ord->get_total(),array('currency' => $_ord->get_order_currency())).'</td>
		        </tr>';
				$trows.=$_row;
           }		
        
	   }
	   echo json_encode(array(
	    'total_sum'=>wc_price($oSum,$cpos),
		'rows'=>$trows
	   ));
	  }
	  
	  function wps_update_refresh($status,$val){
	   // update_option('wps_time_interval',$val);
	   update_option('wps_refresh',$status);
	  }
	/**************************** dispatch *********************************/
	 global $woocommerce;	
    if(is_object( $woocommerce ) && version_compare( $woocommerce->version, '2.1', '>=' )){
  /*
  * operations:
  *   - get_product
  *   - get_variations
  *   - update_cart
  *   - empty_cart
  */
  $_name=__('Order','woocommerce');
  $_currency=get_option('woocommerce_currency');
   $operation= $_POST['operation'];  
   switch($operation){
    case 'get_product':
	
	 wps_get_product($_POST['text']);
	break;
	case 'get_variations':
	 $_variations=get_childs($_POST['text']);
	  echo json_encode($_variations);
	break;
	case 'update_cart':
	 wps_add_to_cart($_POST['type'],$_POST['parent_id'],$_POST['var_id'],$_POST['quantity'],$_POST['attribute_name'],$_POST['slug'],$_POST['cart_item_key']);
	break;
	case 'change_item':
	 wps_change_item($_POST['cart_item_key'],$_POST['quantity']);
	break;
	case 'payment':
	 wps_create_order($_name);
	break;
	case 'delete_from_cart':
	 wps_delete_from_cart($_POST['cart_item_key']);
	break;
	case 'empty_cart':
	$arr=array('alert'=>'ok');
	  
	 wps_empty_cart();
	break;
	case 'refresh':
	 wps_refresh();
	break;
	case 'update_refresh':
	 wps_update_refresh($_POST['status'],$_POST['value']);
	break;
  }
 }
?>
