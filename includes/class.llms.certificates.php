<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Frontend scripts class
*
* Initializes front end scripts
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Certificates {

	public $emails;

	public $email_content;

	private $_from_address;

	private $_from_name;

	private $_content_type;

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	function __construct() {
		LLMS_log('made to to LLMS_Certificates contruct');
		
		$this->init();

		///add_action( 'lifterlms_email_header', array( $this, 'email_header' ) );
		//add_action( 'lifterlms_email_footer', array( $this, 'email_footer' ) );

		//add_action( 'lifterlms_created_person_notification', array( $this, 'person_new_account' ), 10, 3 );

		add_action( 'lifterlms_lesson_completed_certificate', array( $this, 'lesson_completed' ), 10, 3 );


		//add_action( 'lifterlms_created_person_notification', array( $this, 'person_new_account' ), 10, 3 );

		//do_action( 'lifterlms_email', $this );
	}

	function init() {
		// Include email classes
		include_once( 'class.llms.certificate.php' );

		//$this->emails['LLMS_Email_Person_Reset_Password']   = include( 'emails/class.llms.email.person.reset.password.php' );
		//$this->emails['LLMS_Email_Person_New']      = include( 'emails/class.llms.email.person.new.php' );

		$this->emails['LLMS_Certificate_User']      = include_once( 'certificates/class.llms.certificate.user.php' );

		//$this->emails = apply_filters( 'lifterlms_email_classes', $this->emails );
	}

	function get_emails() {
		return $this->emails;
	}

	function get_from_name() {
		if ( ! $this->_from_name )
			$this->_from_name = get_option( 'lifterlms_email_from_name' );

		return wp_specialchars_decode( $this->_from_name );
	}

	function get_from_address() {
		if ( ! $this->_from_address )
			$this->_from_address = get_option( 'lifterlms_email_from_address' );

		return $this->_from_address;
	}

	function get_content_type() {
		return $this->_content_type;
	}

	function email_header( $email_heading ) {
		llms_get_template( 'emails/header.php', array( 'email_heading' => $email_heading ) );
	}

	function email_footer() {
		llms_get_template( 'emails/footer.php' );
	}

	function wrap_message( $email_heading, $message, $plain_text = false ) {
		// Buffer
		ob_start();

		do_action( 'lifterlms_email_header', $email_heading );

		echo wpautop( wptexturize( $message ) );

		do_action( 'lifterlms_email_footer' );

		// Get contents
		$message = ob_get_clean();

		return $message;
	}

	function send( $to, $subject, $message, $headers = "Content-Type: text/html\r\n", $attachments = "", $content_type = 'text/html' ) {

		// Set content type
		$this->_content_type = $content_type;

		// Filters for the email
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Send
		wp_mail( $to, $subject, $message, $headers, $attachments );

		// Unhook filters
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}

	function person_new_account( $person_id, $new_person_data = array(), $password_generated = false ) {
		LLMS_log('LLMS_Emails: person_new_account function executed');
		if ( ! $person_id )
			return;

		$user_pass = ! empty( $new_person_data['user_pass'] ) ? $new_person_data['user_pass'] : '';
		$email = $this->emails['LLMS_Email_Person_New'];
		$email->trigger( $person_id, $user_pass, $password_generated );
	}

	function lesson_completed( $person_id, $email_id, $lesson_id ) {
		LLMS_log('LLMS_Certificate: lesson_completed exectured! yay!');
		// LLMS_log('LLMS_Emails: lesson_completed function executed');
		if ( ! $person_id )
			return;
		LLMS_log('person id from lesson completed certificates ' . $person_id );
		// $user_pass = ! empty( $new_person_data['user_pass'] ) ? $new_person_data['user_pass'] : '';
		// //LLMS_log($this->emails);
		$certificate = $this->emails['LLMS_Certificate_User'];
		$certificate->trigger( $person_id, $email_id, $lesson_id );
	}

}



