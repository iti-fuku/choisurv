<?php

class Question {

	public $qid;
	public $tid;
	public $display;
	public $header;
	public $footer;
	public $aType;
	public $aList;
	// 質問タイプ 0bit:コメント、1bit:任意
	public $cType;
	// 0bit:コメント(true|false)
	public $cType0;
	// 1bit:任意(true|false)
	public $cType1;
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
		$question->cType = 0;
		// 回答タイプ
		if ( isset($_POST['ctype0'.$index]) ) {
			$question->cType0 = 'true';
			$question->cType = 1;
		} else {
			$question->cType0 = 'false';
		}
		if ( isset($_POST['ctype1'.$index]) ) {
			$question->cType1 = 'true';
			$question->cType += 2;
		} else {
			$question->cType1 = 'false';
		}

		return $question;
	}

	// コピー用にIDクリア
	public function clearId() {
		$this->qid = '';
		$this->tid = '';
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

	// 質問タイプの値を取得
	public function getCType($index) {
		if ( ( ( $this->cType >> $index ) & 1 ) == 1 ) {
			return 'true';
		}
		return 'false';
	}

	// 未回答チェック
	public function isNeedsAnser() {
		if ( $this->cType1 == 'false' ) {
			if ( $this->anser->anser == "" ) {
				return true;
			}
		}
		return false;
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
		$question->cType0 = $question->getCType(0);
		$question->cType1 = $question->getCType(1);
		$question->updateTime = date('Y/m/d H:i:s',strtotime($row['update_time']));
		return $question;
	}

}
?>
