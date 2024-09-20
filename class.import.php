<?php

class Import {

	private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::initHooks();
		}
	}

	public static function initHooks() {

		self::$initiated = true;
		add_action( 'admin_enqueue_scripts', array( 'Import', 'loadResources' ) );
		//add_action( 'wp_enqueue_scripts', array( 'Import', 'loadResources' ) );
	}

	public static function loadResources() {
		// 固有スタイル
		wp_register_style( 'style.css', plugin_dir_url( __FILE__ ) . 'css/style.css' );
		wp_enqueue_style( 'style.css');
		// datepicker
		wp_enqueue_script('jquery-ui-datepicker');
		wp_register_style('jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css' );
		wp_enqueue_style('jquery-ui');
	}

}