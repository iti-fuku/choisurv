<?php

class Theme {

	public $tid;
	public $name;
	public $header;
	public $footer;
	public $startTime;
	public $endTime;
	public $updateTime;
	public $questions;
	public $errors;

	public $user;

	// セッション情報から取得
	public static function fromSession() {
		$theme = new Theme();
		$theme->questions = array();
		$theme->errors = array();
		if( $_SESSION['token'] === $_POST['token'] ) {

			$theme->tid = $_POST['tid'];

			if( !empty($_POST['name']) ) {
				$theme->name = $_POST['name'];
			} else {
				$theme->errors[] = '名称を入力してください。';
			}

			$theme->header = str_replace("\r\n","[CR]",stripslashes($_POST['header']));
			$theme->footer = str_replace("\r\n","[CR]",stripslashes($_POST['footer']));

			if( !empty($_POST['sta-ymd']) ) {
				$theme->startTime = Common::toDateTime( $_POST['sta-ymd'], '00:00:00' );
			} else {
				$theme->errors[] = '開始日を入力してください。';
			}
			if( !empty($_POST['end-ymd']) ) {
				$theme->endTime = Common::toDateTime( $_POST['end-ymd'], '23:59:59' );
			} else {
				$theme->errors[] = '終了日を入力してください。';
			}

			$qsize = $_POST['qsize'];
			for( $index=0 ; $index<$qsize ; $index++ ) {
				$question = Question::fromSession($index);
				if ( $question->isEmpty() ) {
					break;
				}
				array_push($theme->questions,$question);
			}

			if ( empty($theme->questions) ) {
				$theme->errors[] = '質問を入力してください。';
			}

		} else {
			$theme->errors[] = 'セッション情報が不正です。';
		}
		return $theme;
	}

	public static function fromDb($tid) {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare("SELECT * FROM ".$wpdb->prefix ."choisurv_theme WHERE tid = %d", $tid), ARRAY_A);
		$theme = Theme::fromRow($row);
		$theme->questions = Question::selectAll($tid);
		return $theme;
	}

	// セッション情報から回答を取得
	public function setAnserFromSession($userName) {
		$this->user = User::fromSession($this->tid, $userName);
		foreach($this->questions as $question) {
			$question->setAnserFromSession($this->tid, $userName);
		}
	}

	// DBから回答を取得
	public function setAnserFromDb($userName) {
		$this->user = User::fromDb($this->tid, $userName);
		if ( $this->user->status == '1' ) {
			// 確認待ちの場合、一時保存状態に変更する.
			$this->user->status = '0';
		}
		foreach($this->questions as $question) {
			$question->setAnserFromDb($this->tid, $userName);
		}
	}

	// ダウンロード
	public function download() {
		nocache_headers();

		// open file (Stream)
		$stream = fopen('php://output', 'w');
		// BOM
		fwrite($stream, "\xEF\xBB\xBF");

		// ヘッダ
		$header = ['user','status'];
		$index = 0;
		foreach( $this->questions as $question ) {
			$index = $index + 1 ;
			$header[] = 'Q'.$index;
			$header[] = 'コメント';
		}
		fputcsv($stream,$header);

		// ダウンロード設定
		$filename = 'result-'.date('YmdHis',current_time('timestamp')).'.csv';
		header( "Content-type: text/plain; charset=Shift_JIS" );
		header( 'Content-Disposition: attachment; filename='.$filename.'' );

		// データ
		$users = User::selectAll($this->tid);
		if ( empty($users) ) {
			fputcsv($stream,array(Common::toSjis('回答者はまだいません')));
		} else {
			foreach( $users as $user ) {
				$row = [ $user->user ];
				if ( $user->status == '2' ) {
					$row[] = '回答済';
				} else {
					$row[] = '一時保存';
				}
				foreach( $this->questions as $question ) {
					$anser = Anser::fromDb($this->tid, $question->qid, $user->user);
					$row[] = $anser->anser;
					$row[] = $anser->comment;
				}
				fputcsv($stream,$row);
			}
		}
		fclose($stream) ;
		exit;
	}

	// DBアクセス
	public function insert() {
		global $wpdb;
		$wpdb->insert( $wpdb->prefix ."choisurv_theme",
			array(
				'tid' => '',
				'name' => $this->name,
				'header' => $this->header,
				'footer' => $this->footer,
				'start_time' => $this->startTime,
				'end_time' => $this->endTime,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}
		$this->tid = $wpdb->insert_id;
		foreach( $this->questions as $question ) {
			$question->tid = $this->tid;
			if ( !$question->insert() ) {
				$this->errors[] = $question->errors;
				return false;
			}
		}
		return true;
	}

	public function update() {
		global $wpdb;
		$wpdb->update( $wpdb->prefix ."choisurv_theme",
			array(
				'name' => $this->name,
				'header' => $this->header,
				'footer' => $this->footer,
				'start_time' => $this->startTime,
				'end_time' => $this->endTime,
				'update_time' => date('Y/m/d H:i:s')
			),
			array(
				'tid' => $this->tid
			),
			array(
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s'
			),
			array(
				'%d'
			));
		if( !empty($wpdb->last_error) ) {
			$this->errors[] = $wpdb->last_error;
			return false;
		}

		$oldQuestions = Question::selectAll($this->tid);
		foreach( $this->questions as $question ) {
			// 旧質問検索
			$hitOldQuestion = null;
			for($index = 0 ; $index < count($oldQuestions); $index++){
				$oldQuestion = $oldQuestions[$index];
				if ($question->qid == $oldQuestion->qid) {
					$hitOldQuestion = $oldQuestion;
					array_splice($oldQuestions,$index,1);
					break;
				}
			}
			// 旧質問がなければinsert
			if ( $hitOldQuestion == null ) {
				$question->tid = $this->tid;
				if ( !$question->insert() ) {
					$this->errors[] = $question->errors;
					return false;
				}
			// 旧質問があり、更新されていればupdate
			} else {
				if ( $question->isUpdate($hitOldQuestion) ) {
					if ( !$question->update() ) {
						$this->errors[] = $question->errors;
						return false;
					}
				}
			}
		}
		// 旧質問が消えていればdelete
		foreach($oldQuestions as $oldQuestion) {
			if ( !$oldQuestion->delete() ) {
				$this->errors[] = $oldQuestion->errors;
				return false;
			}
		}
		return true;
	}

	public function delete() {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix ."choisurv_theme",
			array(
				'tid' => $this->tid
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

	public static function selectAll() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix ."choisurv_theme ORDER BY tid DESC", ARRAY_A );
		$results = array();
		foreach( $rows as $row ) {
			$theme = Theme::fromRow($row);
			$results[] = $theme;
		}
		return $results;
	}

	private static function fromRow($row) {
		$theme = new Theme();
		$theme->tid = $row['tid'];
		$theme->name = $row['name'];
		$theme->header = $row['header'];
		$theme->footer = $row['footer'];
		$theme->startTime = date('Y/m/d H:i:s',strtotime($row['start_time']));
		$theme->endTime = date('Y/m/d H:i:s',strtotime($row['end_time']));
		$theme->updateTime = date('Y/m/d H:i:s',strtotime($row['update_time']));
		return $theme;
	}
}
?>
