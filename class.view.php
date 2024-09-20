<?php

class View {

	public static function themeList() {
		if ( Log::isDebug() ) {
			$file = CHOISURV_PLUGIN_DIR . 'view/log.php';
			include($file);
		}
		$file = CHOISURV_PLUGIN_DIR . 'view/themeList.php';
		include($file);
	}

	public static function themeEdit($title,$cmd,$theme) {
		if ( Log::isDebug() ) {
			$file = CHOISURV_PLUGIN_DIR . 'view/log.php';
			include($file);
		}
		$file = CHOISURV_PLUGIN_DIR . 'view/themeEdit.php';
		include($file);
	}

	public static function themeResult($theme) {
		if ( Log::isDebug() ) {
			$file = CHOISURV_PLUGIN_DIR . 'view/log.php';
			include($file);
		}
		$file = CHOISURV_PLUGIN_DIR . 'view/result.php';
		include($file);
	}

	public static function anser($theme) {
		if ( Log::isDebug() ) {
			$file = CHOISURV_PLUGIN_DIR . 'view/log.php';
			include($file);
		}
		$file = CHOISURV_PLUGIN_DIR . 'view/anser.php';
		include($file);
	}

	public static function getBaseUrl() {
		return (is_ssl() ? 'https' : 'http') . '://' . $_SERVER["HTTP_HOST"] . $_SERVER['SCRIPT_NAME'] . '?page=choisurvAdmin';
	}
}
?>
