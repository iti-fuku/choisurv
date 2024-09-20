<?php

class Log {

	public static $logs = array();

	public static function clear() {
		Log::$logs = array();
	}

	public static function debug($text) {
		Log::$logs[] = $text;
	}

	public static function get() {
		$text = "";
		foreach( Log::$logs as $log ) {
			$text .= $log . '<br/>';
		}
		return $text;
	}

	public static function isDebug() {
		return true;
	}
}
?>
