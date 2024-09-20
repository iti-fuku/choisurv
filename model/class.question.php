<?php

class Question {

	public $qid;
	public $tid;
	public $display;
	public $header;
	public $footer;
	public $aType;
	public $aList;
	public $cType;
	public $updateTime;

	public $anser;

	// セッション情報から取得
	public static function fromSession($index) {
		$question = new Question();

		$question->qid = $_POST['qid'.$index];
		$question->display = $index;
		$question->header = str_replace("\r\n","[CR]",stripslashes($_POST['qheader'.$index]));
		$question->footer = str_replace("\r\n","[CR]",stripslashes($_POST['qfooter'.$index]));
		$question->aType = $_POST['atype'.$index];
		$question->aList = str_replace("\r\n","[CR]",$_POST['alist'.$index]);
		if ( isset($_POST['ctype'.$index]) ) {
			$question->cType = 1;
		} else {
			$question->cType = 0;
		}

		return $question;
	}

	public function isEmpty() {
		return empty($this->header);
	}

	public function isUpdate($oldQuestion) {
		if ( $this->display == $oldQuestion->display
			&& $this->header == $oldQuestion->header
			&& $this->footer == $oldQuestion->footer
			&& $this->aType == $oldQuestion->aType
			&& $this->aList == $oldQuestion->aList
			&& $this->cType == $oldQuestion->cType ) {
			return false;
		}
		return true;
	}

	// セッション情報から回答を取得
	public function setAnserFromSession($tid,$user) {
		$this->anser = Anser::fromSession($tid,$this->qid,$user);
	}

	// セッション情報から回答を取得
	public function setAnserFromDb($tid, $user) {
		$this->anser = Anser::fromDB($tid,$this->qid,$user);
	}

	// DBアクセス
	public function insert() {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix ."choisurv_question",
			array(
				'qid' => '',
				'tid' => $this->tid,
				'display' => $this->display,
				'header' => $this->header,
				'footer' => $this->footer,
				'atype' => $this->aType,
				'alist' => $this->aList,
				'ctype' => $this->cType,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		$this->id = $wpdb->insert_id;
		return true;
	}

	public function update() {
		global $wpdb;
		$wpdb->update( $wpdb->prefix ."choisurv_question",
			array(
				'display' => $this->display,
				'header' => $this->header,
				'footer' => $this->footer,
				'atype' => $this->aType,
				'alist' => $this->aList,
				'ctype' => $this->cType,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'qid' => $this->qid
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%d',
				'%s'
			),
			array(
				'%d'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		return true;
	}

	public function delete() {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix ."choisurv_question",
			array(
				'qid' => $this->qid
			),
			array(
				'%d'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		return true;
	}

	public static function selectAll($tid) {
		global $wpdb;
		$rows = $wpdb->get_results( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix ."choisurv_question where tid = %d ORDER BY display", $tid), ARRAY_A );
		$results = array();
		foreach( $rows as $row ) {
			$question = Question::fromRow($row);
			$results[] = $question;
		}
		return $results;
	}

	private static function fromRow($row) {
		$question = new Question();
		$question->qid = $row['qid'];
		$question->tid = $row['tid'];
		$question->display = $row['display'];
		$question->header = $row['header'];
		$question->footer = $row['footer'];
		$question->aType = $row['atype'];
		$question->aList = $row['alist'];
		$question->cType = $row['ctype'];
		$question->updateTime = date('Y/m/d H:i:s',strtotime($row['update_time']));
		return $question;
	}

}
?>
