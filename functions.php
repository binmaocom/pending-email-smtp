<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
class WPC_PendingEmailFake {
	function send(){
		return true;
	}
}
global $wp_mail_real;
class WPC_PendingEmail {

	function __construct() {
		global $wp_mail_real;
		$wp_mail_real = false;
		add_action( 'wp_pending_email_plugin_activate', array($this, 'installDatabaseTables') );

		add_filter( 'wp_mail', array($this, 'wp_mail' ), 1, 1 );
		add_filter( 'pre_wp_mail', array($this, 'pre_wp_mail' ), 9999, 2 );

		add_action( 'bi_add_cron_hook_3_seconds', array( $this, 'bi_cron_exec' ) );
		add_filter( 'cron_schedules', function ( $schedules ) {
		    $schedules['every-3-seconds'] = array(
		        'interval' => 3,
		        'display'  => __( 'Every 3 seconds' )
		    );
		    return $schedules;
		} );

		add_action('phpmailer_init', array($this, 'phpmailer_init'), 1 , 1);
	}
	function phpmailer_init(&$phpmailer){
		global $wp_mail_real;
		if($wp_mail_real == false){
			remove_all_actions( 'phpmailer_init' );
			$phpmailer = new WPC_PendingEmailFake();
		}
	}
	function get_table_name(){
		global $wpdb;
		return $wpdb->prefix.'pending_email';
	}
	function wp_mail($atts) {
		global $wp_mail_real;
		if($wp_mail_real == false){
			$data_data = array(
				'hook_name'=>'',
				'post_id'=>'',
				'data_post'=>'',
				'connect_id'=>'',
				'old_status'=>'',
				'new_status'=>'',
				'Note'=>1
			);
		  	$data_data['hook_name'] = 'send_email';
		  	$data_data['connect_id'] = 0;
		  	$data_data['Note'] = 1;
		  	$data_data['data_post'] = base64_encode(json_encode($atts));
			global $wpdb;
			$table = $this->get_table_name();
			$format = array('%s','%d', '%s', '%d', '%s', '%s','%d');
			$wpdb->insert($table,$data_data,$format);

			if( ! wp_next_scheduled( 'bi_add_cron_hook_3_seconds' ) ) {  
			    wp_schedule_event( time(), 'every-3-seconds', 'bi_add_cron_hook_3_seconds' );  
			}
		}
		return $atts;
	}
	function pre_wp_mail($arg1, $atts) {
		global $wp_mail_real;
		if($wp_mail_real == false){			
			return true;
		}
		return null;
	}
	function bi_cron_exec(){
		$list_posts = $this->get_data();
		if($list_posts){
			foreach($list_posts as $post_data){
				$this->update_data($post_data->ID, 2);
			}
			foreach($list_posts as $post_data){
				if(isset($post_data->hook_name) && !empty($post_data->hook_name)){
					switch ($post_data->hook_name) {
						case 'send_email':
						  	$atts = json_decode(base64_decode($post_data->data_post), true);
						  	$to = $atts['to'];
						  	$subject = $atts['subject'];
						  	$message = $atts['message'];
						  	$headers = $atts['headers'];
						  	$attachments = $atts['attachments'];
						  	global $wp_mail_real;
						  	$wp_mail_real = true;
						  	wp_mail($to, $subject, $message, $headers, $attachments);
							break;
						
						default:
							# code...
							break;
					}
				}
			}
			if( ! wp_next_scheduled( 'bi_add_cron_hook_3_seconds' ) ) {  
			    wp_schedule_event( time(), 'every-3-seconds', 'bi_add_cron_hook_3_seconds' );  
			}				
		}else{
			if(current_time('H') == 3){
				$list_delete = $this->get_data(2);
				if($list_delete && count($list_delete)){
					global $wpdb;
					$table = $this->get_table_name();
			        $result = $wpdb->query("TRUNCATE TABLE $table ");
			        echo json_encode($result);exit;
			    }
			}
		}
		echo json_encode($list_posts);exit;
	}
	function installDatabaseTables() {
        global $wpdb;
        $table = $this->get_table_name();
        $result = $wpdb->query("CREATE TABLE IF NOT EXISTS `$table` (
				`ID` BIGINT NOT NULL AUTO_INCREMENT,
				`hook_name` VARCHAR(50) NOT NULL DEFAULT '',
				`post_id` BIGINT(20) NULL,
				`data_post` LONGTEXT NULL,
				`connect_id` BIGINT(20) NULL,
				`old_status` VARCHAR(50) NULL,
				`new_status` VARCHAR(50) NULL,
				`Note`  INT(15) NULL,
				`timestamp` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`ID`) 
			) DEFAULT CHARACTER SET = utf8 DEFAULT COLLATE utf8_general_ci;");
        return $result;
    }

	function insert_data( $data ){
		global $wpdb;
		$table = $this->get_table_name();
		$format = array('%s','%d', '%s', '%d', '%s', '%s','%d');
		$wpdb->insert($table,$data,$format);
		return $wpdb->insert_id;
	}

	function get_data($note = 1){
		global $wpdb;
		$table = $this->get_table_name();
		$allposts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM $table WHERE Note = '$note' ORDER BY ID ASC limit 0,2") );
		return $allposts;
	}

	function update_data( $id, $value ){
		global $wpdb;
		$table = $this->get_table_name();
		$data = [ 'Note' => $value ]; // NULL value.
		$where = [ 'ID' => $id ]; // NULL value in WHERE clause.
		$wpdb->update( $table, $data, $where ); 
	}

}

$WPC_PendingEmail = new WPC_PendingEmail();