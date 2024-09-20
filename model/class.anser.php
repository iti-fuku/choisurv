<?php

class Anser {

	public $tid;
	public $qid;
	public $user;
	public $anser;
	public $comment;
	public $updateTime;

	public static function fromSession($tid,$qid,$userName) {
		$anser = new Anser();

		$anser->tid = $tid;
		$anser->qid = $qid;
		$anser->user = $userName;
		if ( isset($_POST['q'.$qid]) ) {
			if ( is_array($_POST['q'.$qid]) ) {
				$isFirst = true;
				foreach ( $_POST['q'.$qid] as $ans ) {
					if ( $isFirst ) {
						$anser->anser = $ans;
						$isFirst = false;
					} else {
						$anser->anser = $anser->anser.','.$ans;
					}
				}
			} else {
				$anser->anser = $_POST['q'.$qid];
			}
		}
		if ( isset($_POST['q'.$qid.'comment']) ) {
			$anser->comment = $_POST['q'.$qid.'comment'];
		} else {
			$anser->comment = '';
		}

		$anser->upsert();

		return $anser;
	}

	public static function fromDb($tid,$qid,$userName) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix ."choisurv_anser WHERE tid = %d and qid = '%d' and user = %s", $tid, $qid, $userName), ARRAY_A);
		$anser = new Anser();
		if ( !empty($row) ) {
			$anser = Anser::fromRow($row);
		}
		return $anser;
	}

	// DBアクセス
	public function upsert() {
		global $wpdb;
		$tid = $wpdb->get_var($wpdb->prepare(
			"SELECT tid FROM ".$wpdb->prefix ."choisurv_anser WHERE tid = %d and qid=%d and user = %s",
			$this->tid,$this->qid,$this->user));

		if (is_null($tid)) {
			$this->insert();
		} else {
			$this->update();
		}
	}

	public function insert() {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix ."choisurv_anser",
			array(
				'tid' => $this->tid,
				'qid' => $this->qid,
				'user' => $this->user,
				'anser' => $this->anser,
				'comment' => $this->comment,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
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
		$wpdb->update( $wpdb->prefix ."choisurv_anser",
			array(
				'anser' => $this->anser,
				'comment' => $this->comment,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'tid' => $this->tid,
				'qid' => $this->qid,
				'user' => $this->user
			),
			array(
				'%s',
				'%s',
				'%s'
			),
			array(
				'%d',
				'%d',
				'%s'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		return true;
	}

	private static function fromRow($row) {
		$anser = new Anser();
		$anser->tid = $row['tid'];
		$anser->qid = $row['qid'];
		$anser->user = $row['user'];
		$anser->anser = $row['anser'];
		$anser->comment = $row['comment'];
		$anser->updateTime = date('Y/m/d H:i:s',strtotime($row['update_time']));
		return $anser;
	}

}
?>
