<?php

class Install {

	public static function init() {
		global $wpdb;
		// テーブル存在チェック
		$res = $wpdb->get_results( "show tables from ".$wpdb->dbname." like '%choisurv_theme'", ARRAY_A);
		if ( count($res) == 0 ) {
			self::create();
		}
	}

	private static function create() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$sql = "
CREATE TABLE ".$wpdb->prefix ."choisurv_theme (
 tid bigint(20) unsigned NOT NULL auto_increment,
 name varchar(100) NOT NULL default '',
 header varchar(1000) NOT NULL default '',
 footer varchar(1000) NOT NULL default '',
 start_time datetime NOT NULL default '0000-00-00 00:00:00',
 end_time datetime NOT NULL default '0000-00-00 00:00:00',
 update_time datetime NOT NULL default '0000-00-00 00:00:00',
 PRIMARY KEY  (tid)
) $charset_collate;";
		$wpdb->query( $sql );
		$sql = "
CREATE TABLE ".$wpdb->prefix ."choisurv_question (
 qid bigint(20) unsigned NOT NULL auto_increment,
 tid bigint(20) unsigned NOT NULL default 0,
 display bigint(20) unsigned NOT NULL default 0,
 header varchar(1000) NOT NULL default '',
 footer varchar(1000) NOT NULL default '',
 atype int(5) NOT NULL default 0,
 alist varchar(200) NOT NULL default '',
 ctype int(5) NOT NULL default 0,
 update_time datetime NOT NULL default '0000-00-00 00:00:00',
 PRIMARY KEY  (qid)
) $charset_collate;";
		$wpdb->query( $sql );
		$sql = "
CREATE TABLE ".$wpdb->prefix ."choisurv_anser (
 tid bigint(20) unsigned NOT NULL default 0,
 qid bigint(20) unsigned NOT NULL default 0,
 user varchar(200) NOT NULL default '',
 anser varchar(1000) NOT NULL default '',
 comment varchar(1000) default NULL,
 update_time datetime NOT NULL default '0000-00-00 00:00:00',
 PRIMARY KEY  (tid,qid,user)
) $charset_collate;";
		$wpdb->query( $sql );
		$sql = "
CREATE TABLE ".$wpdb->prefix ."choisurv_user (
 tid bigint(20) unsigned NOT NULL default 0,
 user varchar(200) NOT NULL default '',
 status int(5) NOT NULL default 0,
 update_time datetime NOT NULL default '0000-00-00 00:00:00',
 PRIMARY KEY  (tid,user)
) $charset_collate;";
		$wpdb->query( $sql );
	}
}

?>
