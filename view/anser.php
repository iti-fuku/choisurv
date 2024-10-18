

<style>
table.question-table {
	width: 100%;
}
table.question-table td {
	vertical-align: top;
}
p {
	margin: 0px 0px 10px 0px;
}
textarea {
	width: 95%;
}
.button-primary, .button-secondary {
	display: inline-block;
	text-decoration: none;
	font-size: 13px;
	line-height: 2.15384615;
	min-height: 30px;
	margin: 0;
	padding: 0 10px;
	cursor: pointer;
	border-width: 1px;
	border-style: solid;
	-webkit-appearance: none;
	border-radius: 3px;
	white-space: nowrap;
	box-sizing: border-box
}
.button-primary {
	color: #fff;
	background: #2271b1;
	border-color: #2271b1;
}
.button-primary.focus,.button-primary.hover,.button-primary:focus,.button-primary:hover {
	background: #135e96;
	border-color: #135e96;
	color: #fff
}
.button-primary.focus,.button-primary:focus {
	box-shadow: 0 0 0 1px #fff,0 0 0 3px #2271b1
}
.button-primary-disabled,.button-primary:disabled,.button-primary[disabled] {
	color: #a7aaad!important;
	background: #f6f7f7!important;
	border-color: #dcdcde!important;
	box-shadow: none!important;
	text-shadow: none!important;
	cursor: default
}
.button-secondary {
	color: #2271b1;
	border-color: #2271b1;
	background: #f6f7f7;
}
.button-secondary:hover {
	background: #f0f0f1;
	border-color: #0a4b78;
	color: #0a4b78
}
.button-secondary:focus {
	background: #f6f7f7;
	border-color: #3582c4;
	color: #0a4b78;
	box-shadow: 0 0 0 1px #3582c4;
	outline: 2px solid transparent;
	outline-offset: 0
}
.error-message {
	color:#f00;
}
.message {
	color:#00f;
}
</style>
<script>
btnClick = function(cmd) {
	document.getElementById("submit-button").value = cmd;
}
</script>
<?php if ( strcmp(Common::getNow(),$theme->startTime) < 0 ) { ?>
	<!-- 回答開始前 -->
	回答開始日：<?php echo date('Y/m/d',strtotime($theme->startTime)); ?>
<?php } else { ?>
	<!-- 回答開始後 -->
	回答締切日：<?php echo date('Y/m/d',strtotime($theme->endTime)); ?>
<form method="post" action="">
	<input type="hidden" name="tid" value="<?php echo $theme->tid; ?>" />
	<input type="hidden" id="submit-button" name="status" />
	<?php
		// 回答確認時に未回答の質問があればエラーを表示
		$isNeeds = false;
		if ( $theme->user->status == '1' ) {
			foreach( $theme->questions as $q ) {
				if ( $q->isNeedsAnser() ) {
					$isNeeds = true;
					$theme->user->status = '0';
				}
			}
			if ( $isNeeds ) {
				echo '<span class="error-message">未回答の質問があります</span>';
			} else {
				echo '<span class="message">まだ回答は完了していません。<br/>回答内容を確認し「確定」ボタンを押してください</span>';
			}
		}
	?>
	<p><?php echo str_replace("[CR]","<br/>",$theme->header); ?></p>
	<table class="question-table">
	<!-- $theme->user->status 0:未回答、一時保存、1:回答確認、2:回答済 -->
	<?php
		$index = 1;
		foreach( $theme->questions as $q ) {
			echo '<tr><td>Q'.$index.'：';
			if ( $isNeeds && $q->isNeedsAnser() ) {
				echo '<br/><span class="error-message">未回答です</span>';
			}
			echo '</td><td><p>'.str_replace("[CR]","<br/>",$q->header).'</p>';
			echo '<p>';
			$aList = explode('[CR]', $q->aList);
			if ( $q->aType == '1' ) {
				$aindex = 1;
				foreach( $aList as $a ) {
					if ( $theme->user->status == '1' || $theme->user->status == '2' ) {
						if ( $q->anser->anser == $a ) {
							echo '<input type="hidden" name="q'.$q->qid.'" value="'.$a.'" />';
							echo '<span style="">'.$a.'</span>';
							echo '<br/>';
						}
					} else {
						$append = "";
						if ( $q->anser->anser == $a ) {
							$append .= " checked";
						}
						echo '<input type="radio" id="q'.$q->qid.'_'.$aindex.'" name="q'.$q->qid.'" value="'.$a.'"'.$append.' />';
						echo '<label for="q'.$q->qid.'_'.$aindex.'">'.$a.'</label>';
						echo '<br/>';
					}
					$aindex++;
				}
			} else if ( $q->aType == '2' ) {
				if ( $theme->user->status == '1' || $theme->user->status == '2' ) {
					echo '<input type="hidden" name="q'.$q->qid.'" value="'.$q->anser->anser.'" />'
					.'<span style="">'.$q->anser->anser.'</span>';
				} else {
					$aindex = 1;
					$selectList = explode(',', $q->anser->anser);
					foreach( $aList as $a ) {
						$isSelect = false;
						foreach( $selectList as $sel ) {
							if ( $sel == $a ) {
								$isSelect = true;
							}
						}
						$append = "";
						if ( $isSelect ) {
							$append .= " checked";
						}
						echo '<input type="checkbox" id="q'.$q->qid.'_'.$aindex.'" name="q'.$q->qid.'[]" value="'.$a.'"'.$append.' />'
							.'<label for="q'.$q->qid.'_'.$aindex.'">'.$a.'</label>';
						$aindex++;
						echo '<br/>';
					}
				}
			} else {
				if ( $theme->user->status == '1' || $theme->user->status == '2' ) {
					echo '<input type="hidden" name="q'.$q->qid.'" value="'.$q->anser->anser.'" />'
					.'<span style="">'.str_replace("\n","<br/>",$q->anser->anser).'</span>';
				} else {
					echo '<textarea name="q'.$q->qid.'">'.$q->anser->anser.'</textarea>';
				}
			}
			if ( $q->cType == 1 ) {
				if ( $theme->user->status == '1' || $theme->user->status == '2' ) {
					echo '<br/><input type="hidden" name="q'.$q->qid.'comment" value="'.$q->anser->comment.'" />'
					.'<span style="">'.$q->anser->comment.'</span>';
				} else {
					echo '<br/><textarea name="q'.$q->qid.'comment">'.$q->anser->comment.'</textarea>';
				}
			}
			echo '</p>';
			if ( !empty($q->footer) ) {
				echo '<p>'.str_replace("[CR]","<br/>",$q->footer).'</p>';
			}
			echo '</td></tr>';
			$index++;
		}
	?>
	</table>
	<p><?php echo str_replace("[CR]","<br/>",$theme->footer); ?></p>
<p class="submit">
<?php if ( $theme->user->status == '2' ) { 	?>
	回答済です。
<?php } else if ( strcmp($theme->endTime,Common::getNow()) < 0 ) { ?>
	締切済です。
<?php } else if ( $theme->user->status == '1' ) { 	?>
	<input type="submit" class="button button-secondary" onclick="btnClick('0');return true;" value="一時保存" />
	<input type="submit" class="button button-primary" onclick="btnClick('2');return true;" value="確定" />
<?php } else { ?>
	<input type="submit" class="button button-secondary" onclick="btnClick('0');return true;" value="一時保存" />
	<input type="submit" class="button button-primary" onclick="btnClick('1');return true;" value="回答" />
<?php } ?>
</p>
</form>
<?php } ?>
