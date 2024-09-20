<div class="wrap">
<h1 class="wp-heading-inline">アンケート <?php echo $title; ?></h1>
<hr class="wp-header-end" />

<?php
if( !empty($_SESSION['token']) ) {
	unset($_SESSION['token']);
}
$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(24));

$readOnly = '';
switch( $cmd ) {
	case 'do-create':
		$nextCmd = 'do-create';
		$submitCmd = 'アンケートを作成';
		break;
	case 'create':
		$nextCmd = 'do-create';
		$submitCmd = 'アンケートを作成';
		break;
	case 'update':
		$nextCmd = 'do-update';
		$submitCmd = 'アンケートを更新';
		break;
	case 'delete':
		$nextCmd = 'do-delete';
		$submitCmd = 'アンケートを削除';
		$readOnly = ' readonly="true" class="highlight"';
		break;
}

if( !empty($_SESSION['errors']) ) {
?>
<div class="error notice is-dismissible">
	<?php foreach( $_SESSION['errors'] as $message ): ?>
		<p><?php echo $message; ?></p>
	<?php endforeach; ?>
</div>
<?php
	unset($_SESSION['errors']);
}
if( !empty($_SESSION['message']) ) {
?>
<div class="updated notice is-dismissible">
	<p><?php echo $_SESSION['message'] ?></p>
</div>
<?php
	unset($_SESSION['message']);
}
?>
<script>
jQuery( function() {
	<?php if ( $cmd != 'delete') { ?>
		jQuery( "#sta-ymd" ).datepicker( {"dateFormat": "yy/mm/dd"} );
		jQuery( "#end-ymd" ).datepicker( {"dateFormat": "yy/mm/dd"} );
	<?php } ?>
});
</script>
<div id="app">
<form method="post" action="<?php echo View::getBaseUrl().'&cmd='.$nextCmd; ?>">
<table class="form-table">
	<input type="hidden" name="tid" value="<?php echo $theme->tid; ?>">
	<tr class="form-field form-required">
		<td class="form-title">名称【必須】</td>
		<td><input type="text" name="name" value="<?php echo $theme->name; ?>" <?php echo $readOnly; ?>></td>
	</tr>
	<tr class="form-field">
		<td>ヘッダ</td>
		<td><textarea name="header" <?php echo $readOnly; ?>><?php echo str_replace("[CR]","\n",$theme->header); ?></textarea></td>
	</tr>
	<tr class="form-field">
		<td>フッタ</td>
		<td><textarea name="footer" <?php echo $readOnly; ?>><?php echo str_replace("[CR]","\n",$theme->footer); ?></textarea></td>
	</tr>
	<tr class="form-field">
		<td>開始日【必須】</td>
		<td><input type="text" id="sta-ymd" name="sta-ymd" value="<?php
			if(!empty($theme->startTime)){ echo date('Y/m/d',strtotime($theme->startTime));} ?>" <?php echo $readOnly; ?>></td>
	</tr>
	<tr class="form-field">
		<td>終了日【必須】</td>
		<td><input type="text" id="end-ymd" name="end-ymd" value="<?php
			if(!empty($theme->endTime)){ echo date('Y/m/d',strtotime($theme->endTime));} ?>" <?php echo $readOnly; ?>></td>
	</tr>
<?php if ( $cmd != 'delete') { ?>
	<!-- 質問領域 -->
	<tr class="form-field  question" v-for="(item, index) in items">
		<td><table><tr><td>Q{{index+1}}</td>
			<td>
				<div v-show="index > 0" class="button button-up" v-on:click="upItem(index)">↑上↑</div><br/>
				<div class="button" v-on:click="deleteItem(index)">削除</div><br/>
				<div v-show="index < items.length-1" class="button button-down" v-on:click="downItem(index)">↓下↓</div><br/>
			</td></tr></table>
		</td>
		<td>
			<div class="plus" v-on:click="addItem(index)">質問を追加</div>
			<input type="hidden" :name="'qid'+index" :value="item.qid"></input>
			<table class="question-table">
				<tr><td class="form-title">質問</td>
					<td><textarea :name="'qheader'+index" <?php echo $readOnly; ?>
						:value="crToN(item.header)" @input="changeHeader(index, $event)"></textarea><br/>
					</td>
				</tr>
				<tr><td></td>
					<td><select :name="'atype'+index" <?php echo $readOnly; ?> v-model="item.atype">
							<option value="1">単一選択</option>
							<option value="2">複数選択</option>
							<option value="3">テキスト</option>
						</select>
					</td>
				</tr>
				<tr><td>選択肢</td>
					<td><textarea :name="'alist'+index" <?php echo $readOnly; ?>
						:value="crToN(item.alist)" @input="changeList(index, $event)"></textarea><br/>
					</td>
				</tr>
				<tr><td>コメント</td>
					<td><input type="checkbox" :name="'ctype'+index" <?php echo $readOnly; ?> v-model="item.ctype">
					</td>
				</tr>
				<tr><td>フッタ</td>
					<td><textarea :name="'qfooter'+index" <?php echo $readOnly; ?>
						:value="crToN(item.footer)" @input="changeFooter(index, $event)"></textarea><br/>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td></td>
		<td>
			<div class="plus" v-on:click="addlastItem">質問を追加</div>
		</td>
	</tr>
<?php } ?>
</table>
<input type="hidden" name="qsize" :value="items.length" />
<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" />
<p class="submit">
<input type="submit" class="button button-primary" value="<?php echo $submitCmd; ?>" />
</p>
</form>
</div>
<script type="module">
	import { createApp } from "<?php echo plugin_dir_url( __FILE__ ) . '../js/unpkg.com_vue@3.2.4_dist_vue.esm-browser.prod.js' ?>"

	const app = createApp({
		data() {
			return {
				items: [
<?php
	if ( empty($theme->questions) ) {
		echo '{ qid:\'\', header: \'\',atype:"",alist: \'\',footer:\'\',ctype:\'\'},';
	} else {
		foreach( $theme->questions as $q ) {
			$ctype = ( $q->cType == 1 ) ? 'true' : 'false';
			echo '{ qid:\''.$q->qid.'\', header: \''.$q->header.'\',atype:\''.$q->aType.'\',alist: \''.$q->aList.'\',footer:\''.$q->footer.'\',ctype: \''.$ctype.'\'},';
		}
	}
?>
				],
			}
		},
		methods: {
			addItem: function (index) {
				this.items.splice(index,0,{ qid:"", header: "", atype:"", alist:"" , ctype:"false" ,footer:"" });
			},
			addlastItem: function (event) {
				this.items.push({ qid:"", header: "", atype:"", alist:"", ctype:"false" ,footer:"" });
			},
			deleteItem: function (index) {
				this.items.splice(index,1);
			},
			upItem: function (index) {
				this.items.splice(index - 1, 2, this.items[index], this.items[index-1]);
			},
			downItem: function (index) {
				this.items.splice(index, 2, this.items[index+1], this.items[index]);
			},
			crToN: function (val) {
					return val.replaceAll('[CR]','\n');
			},
			changeHeader: function (index, e) {
				this.items[index].header = e.target.value.replaceAll('\n','[CR]');
			},
			changeList: function (index, e) {
				this.items[index].alist = e.target.value.replaceAll('\n','[CR]');
			},
			changeFooter: function (index, e) {
				this.items[index].footer = e.target.value.replaceAll('\n','[CR]');
			},
		},
	});
	app.mount("#app");
</script>
</div>
