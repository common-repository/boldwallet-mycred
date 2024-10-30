<?php
/*
* @package Boldwallet Mycred Plugin
*/

/*
Plugin Name: Boldwallet myCred
Plugin URI: http://bwalletpay.com
Description: Boldwallet Payment Gateway for myCred
Version: 1.2
Author: Premium ESOWP Limited
Author URI: http://premiumesowp.com
Licence: GPLv2 or Later
License URI:  http://www.gnu.org/licenses/gpl-2.0.tx
*/



//check access from wordpress
if(!function_exists('add_action')){

	die;
}

defined('ABSPATH') or die('hey , what are you doing here? you silly human');


add_action( 'plugins_loaded', 'mycred_boldwallet_plugins_loaded' );

function mycred_boldwallet_plugins_loaded() {
	add_filter( 'mycred_setup_gateways', 'Add_Boldwallet_to_Gateways' );

	function Add_Boldwallet_to_Gateways( $installed ) {

	 

	 $installed['boldwallet'] = array(
		 
		'title'    => get_option( 'boldwallet_display_name' ) ? get_option( 'boldwallet_display_name' ) : __( 'Boldwallet Payment Gateway', 'boldwallet-mycred' ),
		'callback' => array( 'myCred_boldwallet' )
	);
 
 return $installed;
 
}
}
define('boldlogo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/logo.png');
spl_autoload_register( 'mycred_boldwallet_plugin' );

function mycred_boldwallet_plugin() {
	if ( ! class_exists( 'myCRED_Payment_Gateway' ) ) {

		return;
	}


if ( ! class_exists( 'myCred_Boldwallet' ) ) {

		class myCred_Boldwallet extends myCRED_Payment_Gateway {



function __construct( $gateway_prefs ) {

				$types            = mycred_get_types();
				$default_exchange = [];

				foreach ( $types as $type => $label ) {

					$default_exchange[ $type ] = 1;
				}

				parent::__construct( [

					'id'       => 'boldwallet_mycred',
					'label'    => 'Boldwallet Payment',
					'gateway_logo_url' => boldlogo,
					'defaults' => [
						'master_key'            => NULL,
						'logo'             => boldlogo,
						'service_key'            => NULL,
						'boldwallet_display_name' => __( 'Boldwallet payment gateway', 'boldwallet-mycred' ),
						'currency'           => 'NGN',
						'exchange'           => $default_exchange,
						'item_name'          => __( 'Purchase of myCRED %plural%', 'mycred' ),
					],
				], $gateway_prefs );
			}


  			
			function preferences() {
				 

				$prefs = $this->prefs;
				?>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'master_key' ); ?>"><?php _e( 'Master key', 'boldwallet-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'master_key' ); ?>"
                                   name="<?php echo $this->field_name( 'master_key' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['master_key']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                  <label class="subheader"
                       for="<?php echo $this->field_id( 'service_key' ); ?>"><?php _e( 'Service key', 'boldwallet-mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'service_key' ); ?>"
                                   name="<?php echo $this->field_name( 'service_key' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['service_key']; ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                <label class="subheader"
                       for="<?php echo $this->field_id( 'boldwallet_display_name' ); ?>"><?php _e( 'Title', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'boldwallet_display_name' ); ?>"
                                   name="<?php echo $this->field_name( 'boldwallet_display_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['boldwallet_display_name'] ? $prefs['boldwallet_display_name'] : __( 'Boldwallet', 'boldwallet-mycred' ); ?>"
                                   class="long"/>
                        </div>
                    </li>
                </ol>

                 

                <label class="subheader"
                       for="<?php echo $this->field_id( 'item_name' ); ?>"><?php _e( 'Item Name', 'mycred' ); ?></label>
                <ol>
                    <li>
                        <div class="h2">
                            <input id="<?php echo $this->field_id( 'item_name' ); ?>"
                                   name="<?php echo $this->field_name( 'item_name' ); ?>"
                                   type="text"
                                   value="<?php echo $prefs['item_name']; ?>"
                                   class="long"/>
                        </div>
                        <span class="description"><?php _e( 'Description of the item being purchased by the user.', 'mycred' ); ?></span>
                    </li>
                </ol>

                <label class="subheader"><?php _e( 'Exchange Rates', 'mycred' ); ?></label>
                <ol>
                    <li>
						<?php $this->exchange_rate_setup(); ?>
                    </li>
                </ol>
				<?php
			}



public function sanitise_preferences( $data ) {
				$new_data['master_key']            = sanitize_text_field( $data['master_key'] );
				$new_data['boldwallet_display_name'] = sanitize_text_field( $data['boldwallet_display_name'] );
				$new_data['currency']           = sanitize_text_field( $data['currency'] );
				$new_data['item_name']          = sanitize_text_field( $data['item_name'] );
				$new_data['service_key']            = sanitize_text_field( $data['service_key'] );

				if ( isset( $data['exchange'] ) ) {

					foreach ( (array) $data['exchange'] as $type => $rate ) {

						if ( $rate != 1 && in_array( substr( $rate, 0, 1 ), [
								'.',
								',',
							] ) ) {

							$data['exchange'][ $type ] = (float) '0' . $rate;
						}
					}
				}

				$new_data['exchange'] = $data['exchange'];

				update_option( 'idpay_display_name', $new_data['idpay_display_name'] );

				return $data;
			}

        /**
		 * Results Handler
		 * @since 1.8
		 * @version 1.0
		 */
		public function returning() {
			 
			
			 
			
		if(isset($_REQUEST['npo_point']) && isset($_REQUEST['userid']) && isset($_REQUEST['bwuniqueid']) && isset($_REQUEST['masterkey'])&& isset($_REQUEST['ref_status']) && isset($_REQUEST['servicekey'])){
			
			$ref_status = sanitize_text_field($_REQUEST['ref_status']);
			$npo_point = sanitize_text_field($_REQUEST['npo_point']);
			$userid = sanitize_text_field($_REQUEST['userid']);
			$masterkey = sanitize_text_field($_REQUEST['masterkey']);
			$bwuniqueid = sanitize_text_field($_REQUEST['bwuniqueid']);
			$servicekey = (int)sanitize_text_field($_REQUEST['servicekey']);
			 $tlink = '';
			 if($ref_status == 'sandbox'){
				$tlink = 'https://bwalletpay.com/mycred/sandbox_verify'; 
				 }
			 if($ref_status == 'live'){
				$tlink = 'https://bwalletpay.com/mycred/live_verify'; 
				 }
			  
		$org_pending_payment = $pending_payment = $this->get_pending_payment( $bwuniqueid );
			//$response = $this->call_gateway_endpoint($tlink ,$boldwallet_args);
			
			
			$boldwallet_args    = array(
	'method' => 'POST',
	'body'   => array(
		'servicekey'  => $servicekey,
		'bwuniqueid' => $bwuniqueid,
		'masterkey'  => $masterkey,
		'userid'     => $userid
	)
);
			
			$response = wp_remote_post( $tlink, $boldwallet_args );
			
			
		if ( is_wp_error( $response ) ) {
						$log = $response->get_error_message();
						$this->log_call( $pending_post_id, $log );
						wp_die( $log );
						exit;
					}
					$http_status = wp_remote_retrieve_response_code( $response );
					$result      = wp_remote_retrieve_body( $response );
					$result      = json_decode( $result );	
			
			 if ( $http_status != 200 ) {
					 	$log = sprintf( __( 'An error occurred while verifying the transaction. status: %s, code: %s, message: %s', 'boldwallet_mycred' ), $http_status, $result->message, $result->status );
						 $this->log_call( $pending_post_id, $log );
						wp_redirect( $this->get_cancelled() );
						exit;

					} else {
			
			 
			
			$stat_st = array("0"=>"Pending","1"=>"Successfull","2"=>"Declined","3"=>"Cancelled");
			if($result->status_request == "1"){
			 
       				if($result->status == "1"){
			
			 
			
			$log = sprintf( __( 'Payment succeeded. Status: %s, Track id: %s', 'boldwallet_mycred' ), $stat_st[$result->status], $result->bwuniqueid);
			
			 
			 echo mycred_add( 'approved_points_payments', $userid, $npo_point, 'Points for approved for payment' );
			  $this->log_call( $pending_post_id, $log );
								$this->trash_pending_payment( $pending_post_id );
								 wp_redirect( $this->get_thankyou() );
								exit;
					
			
					}else{
						
					 $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s' , 'boldwallet_mycred' ), $stat_st[$result->status], $result->bwuniqueid );
						 	$this->log_call( $pending_post_id, $log );
							 wp_redirect( $this->get_cancelled() );
							exit;	
							
						 
			}
						}else{
					 $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s' , 'boldwallet_mycred' ),$stat_st[$result->status], $result->bwuniqueid );
						 	$this->log_call( $pending_post_id, $log );
							 wp_redirect( $this->get_cancelled() );
							exit;	
						
						}
			
			
			
					}
			}
		 
			//wp_die( "successful payment");
	 //$this->redirect_to     = "ll"; 
		}
 
 
 
        /**
		 * Process Handler
		 * @since 1.8
		 * @version 1.0
		 */



      public function process() {

       	 
			
		if(isset($_REQUEST['npo_point']) && isset($_REQUEST['userid']) && isset($_REQUEST['bwuniqueid']) && isset($_REQUEST['masterkey'])&& isset($_REQUEST['ref_status']) && isset($_REQUEST['servicekey'])){
			
			$ref_status = sanitize_text_field($_REQUEST['ref_status']);
			$npo_point = sanitize_text_field($_REQUEST['npo_point']);
			$userid = sanitize_text_field($_REQUEST['userid']);
			$masterkey = sanitize_text_field($_REQUEST['masterkey']);
			$bwuniqueid = sanitize_text_field($_REQUEST['bwuniqueid']);
			$servicekey = (int)sanitize_text_field($_REQUEST['servicekey']);
			 $tlink = '';
			 if($ref_status == 'sandbox'){
				$tlink = 'https://bwalletpay.com/mycred/sandbox_verify'; 
				 }
			 if($ref_status == 'live'){
				$tlink = 'https://bwalletpay.com/mycred/live_verify'; 
				 }
			  
		$org_pending_payment = $pending_payment = $this->get_pending_payment( $bwuniqueid );
			//$response = $this->call_gateway_endpoint($tlink ,$boldwallet_args);
			
			
			$boldwallet_args    = array(
	'method' => 'POST',
	'body'   => array(
		'servicekey'  => $servicekey,
		'bwuniqueid' => $bwuniqueid,
		'masterkey'  => $masterkey,
		'userid'     => $userid
	)
);
			
			$response = wp_remote_post( $tlink, $boldwallet_args );
			
			
		if ( is_wp_error( $response ) ) {
						$log = $response->get_error_message();
						$this->log_call( $pending_post_id, $log );
						wp_die( $log );
						exit;
					}
					$http_status = wp_remote_retrieve_response_code( $response );
					$result      = wp_remote_retrieve_body( $response );
					$result      = json_decode( $result );	
			
			 if ( $http_status != 200 ) {
					 	$log = sprintf( __( 'An error occurred while verifying the transaction. status: %s, code: %s, message: %s', 'boldwallet_mycred' ), $http_status, $result->message, $result->status );
						 $this->log_call( $pending_post_id, $log );
						wp_redirect( $this->get_cancelled() );
						exit;

					} else {
			
			 
			
			$stat_st = array("0"=>"Pending","1"=>"Successfull","2"=>"Declined","3"=>"Cancelled");
			if($result->status_request == "1"){
			 
       				if($result->status == "1"){
			
			 
			
			$log = sprintf( __( 'Payment succeeded. Status: %s, Track id: %s', 'boldwallet_mycred' ), $stat_st[$result->status], $result->bwuniqueid);
			
			 
			 echo mycred_add( 'approved_points_payments', $userid, $npo_point, 'Points for approved for payment' );
			  $this->log_call( $pending_post_id, $log );
								$this->trash_pending_payment( $pending_post_id );
								 wp_redirect( $this->get_thankyou() );
								exit;
					
			
					}else{
						
					 $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s' , 'boldwallet_mycred' ), $stat_st[$result->status], $result->bwuniqueid );
						 	$this->log_call( $pending_post_id, $log );
							 wp_redirect( $this->get_cancelled() );
							exit;	
							
						 
			}
						}else{
					 $log = sprintf( __( 'Payment failed. Status: %s, Track id: %s' , 'boldwallet_mycred' ),$stat_st[$result->status], $result->bwuniqueid );
						 	$this->log_call( $pending_post_id, $log );
							 wp_redirect( $this->get_cancelled() );
							exit;	
						
						}
			
			
			
					}
			}
		 
			//wp_die( "successful payment");
	 //$this->redirect_to     = "ll"; 
	  
	  
	  
	  }
   
            /*
		  	 *
			 * Prep Sale
			 *
			 * @since   1.8
			 * @version 1.0
			 *
			 */
		
		public function prep_sale( $new_transaction = FALSE ) {
		 
		 
		 if ( empty( $this->prefs['currency'] ) ||  empty( $this->prefs['service_key'] ) ||    empty( $this->prefs['master_key'] ))  wp_die( 'Please setup this payment gateway before trying to make a purchase.' );
			
		//get_current_user_id()	
		// Point type
				$type   = $this->get_point_type();
				$mycred = mycred( $type );

				// Amount of points
				$amount = $mycred->number( $_REQUEST['amount'] );

				// Get cost of that points
				$cost = $this->get_cost( $amount, $type );
				$cost = abs( $cost );

				$to   = $this->get_to();
				$from = $this->current_user_id;	
			    $user_id = $from;
			    $master_key  = $this->prefs['master_key'];
				$service_key  = $this->prefs['service_key'];
			    $endpoint = "https://bwalletpay.com/mycred";
				
			if ( $this->gifting ) {

					$user_id                                   = get_userdata( $this->recipient_id );
					$redirect_fields['detail_description'] = __( 'Recipient', 'mycred' );
					$redirect_fields['detail_text']        = $user->display_name;

				}
			
			if ( isset( $_REQUEST['revisit'] ) )
				$this->transaction_id = strtoupper( $_REQUEST['revisit'] );

			// If this is a new request
			else {

				// Add a new pending payment
				$post_id   = $this->add_pending_payment( array( $to, $from, $amount, $cost, $this->prefs['currency'], $type ) );

				// The pending payment is a custom post type where the title is the unique purchase request ID
				$this->transaction_id = get_the_title( $post_id );

			}
			  
			$cancel_url = $this->get_cancelled( $this->transaction_id );
			$this->redirect_fields = [
					//'pay_to_email'        => $this->prefs['account'],
					'transaction_id'      => $this->transaction_id,
					'success_url'          => $this->get_thankyou(),
					'cancel_url'          => $cancel_url,
					'return_url'          => $this->callback_url(),
					'return_url_text'     => get_bloginfo( 'name' ),
					'hide_login'          => 1,
					'merchant_fields'     => 'sales_data',
					'user_id'             => $user_id,
					'master_key'          => $master_key,
					'service_key'          => $service_key,
					'amount'              => $this->cost,
					'description'         => $this->amount." Points From ".get_bloginfo( 'name' ),
					'points'              =>$this->amount,
					'currency'            => $this->prefs['currency'],
					'detail1_description' => __( 'Item Name', 'mycred' ),
					'detail1_text'        => $item_name,
					'bwuniqueid'        => str_replace(" ","",time().$user_id.uniqid().$this->transaction_id),
				];
			
			 $this->redirect_to = $endpoint;
				
			}
			
			
		/**
		 * AJAX Buy Handler
		 * @since 1.8
		 * @version 1.0
		 */
		public function ajax_buy() {

			// Construct the checkout box content
			 $content  = $this->checkout_header();
			$content .= $this->checkout_logo();
			$content .= $this->checkout_order();
			$content .= $this->checkout_cancel();
			 $content .= $this->checkout_footer();

			// Return a JSON response
			//$this->send_json( $content );

		}

		/**
		 * Checkout Page Body
		 * This gateway only uses the checkout body.
		 * @since 1.8
		 * @version 1.0
		 */
		public function checkout_page_body() {

			 echo $this->checkout_header();
			echo $this->checkout_logo( false );

			echo $this->checkout_order();
			echo $this->checkout_cancel();

			 echo $this->checkout_footer();

		}
			
			
   }

}








}




?>