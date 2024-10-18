<?php

class User {

	public $tid;
	public $user;
	public $status;
	public $comment;
	public $updateTime;

	public static function fromSession($tid,$userName) {
		$user = new User();

		$user->tid = $tid;
		$user->user = $userName;
		$user->status = $_POST['status'];

		$user->upsert();

		return $user;
	}

	public static function fromDb($tid,$userName) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix ."choisurv_user WHERE tid = %d and user = %s", $tid, $userName), ARRAY_A);
		$user = new User();
		if ( !empty($row) ) {
			$user = User::fromRow($row);
		}
		return $user;
	}

	public static function reset($theme) {
		$user = new User();

		$user->tid = $theme->tid;
		$user->user = $_GET['user'];
		$user->status = '0';

		$user->update();
	}

	// DBアクセス
	public function upsert() {
		global $wpdb;
		$tid = $wpdb->get_var($wpdb->prepare(
			"SELECT tid FROM ".$wpdb->prefix ."choisurv_user WHERE tid = %d and user = %s",
			$this->tid,$this->user));

		if (is_null($tid)) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	public function insert() {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix ."choisurv_user",
			array(
				'tid' => $this->tid,
				'user' => $this->user,
				'status' => $this->status,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'%d',
				'%s',
				'%d',
				'%s'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		return true;
	}

	public function update() {
		global $wpdb;
		$wpdb->update( $wpdb->prefix ."choisurv_user",
			array(
				'status' => $this->status,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'tid' => $this->tid,
				'user' => $this->user
			),
			array(
				'%d',
				'%s'
			),
			array(
				'%d',
				'%s'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		return true;
	}

	public static function selectAll($tid) {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix ."choisurv_user where tid = %d ORDER BY user", $tid), ARRAY_A );
		$results = array();
		foreach( $rows as $row ) {
			$user = User::fromRow($row);
			$results[] = $user;
		}
		return $results;
	}

	private static function fromRow($row) {
		$user = new User();
		$user->tid = $row['tid'];
		$user->user = $row['user'];
		$user->status = $row['status'];
		$user->updateTime = date('Y/m/d H:i:s',strtotime($row['update_time']));
		return $user;
	}

}
?>
