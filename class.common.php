<?php

class Common {

	public static function toDateTime( $ymd, $hms ) {
		if ( !empty($ymd) && !empty($hms) ) {
			return $ymd.' '.$hms;
		}
		return null;
	}

	public static function getNow() {
		return date('Y/m/d H:i:s');
	}
}
?>
