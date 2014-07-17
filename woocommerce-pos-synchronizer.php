<?php
/*
*Plugin Name: Woocommerce-POS-Synchronizer
 * Plugin URI: 
 * Description: Performs  synchronization  between Woocommerce  and point of sale in real time. Provides proper stock quantity and status when you sell simultaneously in both places.
 * Author: Emil Petrow
 * Author URI: 
 * Version: 1.0
 * Stable tag: 1.0
 * License: GPL v2 - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
//work only  if woocommerce instal
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
        return;
		
//get user role
if(!function_exists('wp_get_current_user')) {
    include(ABSPATH . "wp-includes/pluggable.php"); 
}
 function _activation(){
	//set default time interval for refresh
	  add_option('wps_time_interval','120000','','no');
	  add_option('wps_refresh','yes','','no');
	}
	function _deactivation(){
	   delete_option('wps_time_interval');
	   delete_option('wps_refresh');
	  
	}
   register_activation_hook( __FILE__ , '_activation');
   register_deactivation_hook( __FILE__ , '_deactivation');
   WooPOSSync::on_load();
   
   /*
   *  create plugin base class
   */
   class WooPOSSync{
     private static $instance = null;
	
	  public static function instance()
    {
        is_null(self::$instance) && self::$instance = new self;
        return self::$instance;
    }
	 public static function on_load() {
        
        add_action( 'init', array( __CLASS__, 'init' ) );
    }
	
	static function init(  ) {
	  add_action('admin_menu',  array(self::instance(),'add_woopossync_menu'));
	  add_action( 'admin_enqueue_scripts',  array(self::instance(),'add_woopossync_scripts') );
	  add_action('wp_ajax_wps_ajax',  array(__CLASS__, 'ajax_request'));
	  add_action('init',  array(__CLASS__,'iwps_empty_cart'));
	  //ensure access only for aministrator and shop manager
	  if(self::get_current_user_role()=='Shop_manager'||self::get_current_user_role()=='Administrator'){
		include("wps_options.php");
		include("wps_sale_page.php");   
	 }
    }
	
	 /*
     * Empty WooCommerce cart if not
     */
     function iwps_empty_cart(){
	  global $woocommerce;
	   $woocommerce->cart->empty_cart(); 
	  }
	//get current userrole
	public static function get_current_user_role() {
	  global $wp_roles;
	  $current_user = wp_get_current_user();
	  $roles = $current_user->roles;
      $role = array_shift($roles);
	  return isset($wp_roles->role_names[$role]);// ? translate_user_role($wp_roles->role_names[$role] ) : false;
    }
	//add menu page
	public function add_woopossync_menu(){
	  add_menu_page('Woo-POS-Sync', 'Woo-POS-Sync', 'manage_options', 'pos-sync-page', 'wps_sale_page','',71);
	  add_submenu_page('pos-sync-page','POS-Sync-Options', 'POS-Options','manage_options', 'wps-options','wps_options');//, 'POS-Options'
	}
	//add scripts
	public function add_woopossync_scripts(){
 	$wps_screen=get_current_screen();
	 $dir =plugin_dir_url(__FILE__);
	if($wps_screen->id=='toplevel_page_pos-sync-page'){
	  $_interval=get_option('wps_time_interval');
	  $_refresh=get_option('wps_refresh');
	 // $dir =plugin_dir_url(__FILE__);
	  $_extradata=array(
	    'dir'=>$dir,
		'interval'=>$_interval,
		'refresh'=>$_refresh
	  );
	  // Loads Bootstrap minified CSS file.
     wp_enqueue_style('bootstrapwp', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css', false ,'3.1.1');
     wp_enqueue_script('jfunc',$dir.'js/wps_functions.js', array(), '1.0.0', true);//jscriptfunc
	  $_dir=array('dir'=>$dir);
         wp_localize_script('jfunc','exdata',$_extradata);
		 wp_localize_script('jfunc','adminajax',array('url'=>admin_url( 'admin-ajax.php' )));
         wp_enqueue_style( 'mstyle', $dir.'styles/wps_style.css',false, '1.0', 'all'  );
		  
	 }
	if($wps_screen->id=='woo-pos-sync_page_wps-options'){
	 wp_enqueue_style('bootstrapwp', '//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css', false ,'3.1.1');
		   wp_enqueue_style( 'mstyle', $dir.'styles/wps_style.css',false, '1.0', 'all'  );
		   wp_enqueue_script('jopt',$dir.'js/wps_settings.js', array(), '1.0.0', true);
		   wp_localize_script('jopt','adminajax',array('url'=>admin_url( 'admin-ajax.php' )));
		  }
	}
	
	public static function ajax_request()
	{
		require_once(dirname(__FILE__).'/ajax-handler.php');
		// IMPORTANT: don't forget to "exit"
		die();
	}
	
   }
?>
