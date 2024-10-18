<?php
/*
Plugin Name: Choisurv Plugin
Description: アンケート作成
Version: 1.0
Author: fuku
*/
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CHOISURV_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once( CHOISURV_PLUGIN_DIR . 'class.import.php' );
require_once( CHOISURV_PLUGIN_DIR . 'class.common.php' );
require_once( CHOISURV_PLUGIN_DIR . 'class.view.php' );
require_once( CHOISURV_PLUGIN_DIR . 'model/class.theme.php' );
require_once( CHOISURV_PLUGIN_DIR . 'model/class.question.php' );
require_once( CHOISURV_PLUGIN_DIR . 'model/class.user.php' );
require_once( CHOISURV_PLUGIN_DIR . 'model/class.anser.php' );
require_once( CHOISURV_PLUGIN_DIR . 'model/class.log.php' );
add_action( 'init', array( 'Import', 'init' ) );
add_action(	'admin_init','download');

if ( is_admin() ) {
	require_once( CHOISURV_PLUGIN_DIR . 'class.install.php' );
}

// セッション開始
if( session_status() !== PHP_SESSION_ACTIVE ) {
	session_start();
}

// ----------------------------------------------------------------------------
// ユーザ
// ----------------------------------------------------------------------------
// ショートコード作成
// 記事を作成するときは、
// ショートコード [chisurv tid="2"] のようにアンケート番号を指定する
add_shortcode('choisurv', 'choisurvAnser');

// 投稿ページのショートコードで指定された PHP ファイルを読み込む関数
function choisurvAnser($attr = array()) {
	ob_start();
	if ( isset($attr['tid']) ) {
		$user = wp_get_current_user();

		$theme = Theme::fromDb($attr['tid']);
		if( isset($_POST['status'] ) ) {
			// 回答を更新する
			$theme->setAnserFromSession($user->user_login);
		} else {
			// 登録済の回答を読み込む
			$theme->setAnserFromDb($user->user_login);
		}
		View::anser($theme);
	} else {
		echo 'ショートコードに tid が設定されていません。';
	}
	return ob_get_clean();
}

// ----------------------------------------------------------------------------
// 管理者
// ----------------------------------------------------------------------------
// 管理者メニュー追加
add_action('admin_menu', 'choisurvOptionPage');

function choisurvOptionPage() {
	add_menu_page(
		'アンケート',
		'アンケート',
		'manage_options',
		'choisurvAdmin',
		'choisurvAdminCallback',
		'dashicons-feedback',
		6
	);
}

function download() {
	if( !empty($_GET['cmd']) ) {
		switch( $_GET['cmd'] ) {
		case 'download':
			$theme = Theme::fromDb($_GET['tid']);
			$theme->download();
			break;
		}
	}
}

function choisurvAdminCallback() {
	Install::init();
	Log::clear();

	$cmd = "none";
	if( !empty($_GET['cmd']) ) {
		switch( $_GET['cmd'] ) {
		case 'do-create':
			$theme = Theme::fromSession();
			if ( !empty($theme->errors) ) {
				$_SESSION['errors'] = $theme->errors;
				View::themeEdit('作成','create',$theme);
			} else {
				if ( $theme->insert() ) {
					$_SESSION['message'] = '作成しました';
					View::themeEdit('更新','update',$theme);
				} else {
					$_SESSION['errors'] = $theme->errors;
					View::themeEdit('作成','create',$theme);
				}
			}
			break;
		case 'do-update':
			$theme = Theme::fromSession();
			if ( !empty($theme->errors) ) {
				$_SESSION['errors'] = $theme->errors;
				View::themeEdit('更新','update',$theme);
			} else {
				$theme->update();
				$_SESSION['message'] = '更新しました';
				View::themeEdit('更新','update',$theme);
			}
			break;
		case 'do-delete':
			$theme = Theme::fromSession();
			$theme->delete();
			$_SESSION['message'] = '削除しました';
			View::themeList();
			break;
		case 'create':
			$theme = new Theme();
			View::themeEdit('作成','create',$theme);
			break;
		case 'update':
			$theme = Theme::fromDb($_GET['tid']);
			View::themeEdit('更新','update',$theme);
			break;
		case 'delete':
			$theme = Theme::fromDb($_GET['tid']);
			View::themeEdit('削除','delete',$theme);
			break;
		case 'result':
			$theme = Theme::fromDb($_GET['tid']);
			View::themeResult($theme);
			break;
		case 'reset':
			$theme = Theme::fromDb($_GET['tid']);
			User::reset($theme);
			View::themeResult($theme);
			break;
		case 'copy':
			$theme = Theme::fromDb($_GET['tid']);
			$theme->clearId();
			View::themeEdit('作成','create',$theme);
			break;
		default:
			break;
		}
	} else {
		View::themeList();
	}
}
?>
