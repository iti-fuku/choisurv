<div class="wrap">
<h1 class="wp-heading-inline">アンケート 結果</h1>
<hr class="wp-header-end" />

<?php
if( !empty($_SESSION['token']) ) {
	unset($_SESSION['token']);
}
$_SESSION['token'] = bin2hex(openssl_random_pseudo_bytes(24));

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
<div>
<?php
	$rows = User::selectAll($theme->tid);
	if ( empty($rows) ) {
		echo '回答者はまだいません';
	} else {
?>
<span><a href="<?php echo View::getBaseUrl().'&cmd=download&tid='.$theme->tid; ?>">ダウンロード</a></span>
<table class="wp-list-table widefat fixed striped table-view-list users">
	<thead>
		<tr>
			<th scope="col"><span>user</span></th>
			<th scope="col"><span>status</span></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $rows as $row ) { ?>
		<tr>
			<td><?php echo $row->user; ?></td>
			<td><?php if ( $row->status == '2' ) { ?>
					回答済　<span><a href="<?php echo View::getBaseUrl().'&cmd=reset&tid='.$row->tid.'&user='.$row->user; ?>">戻す</a></span>
				<?php } else {
					echo '一時保存';
				} ?></td>
		</tr>
		<?php } ?>
	</tbody>

	<tfoot>
	<!-- theadと同じ -->
	</tfoot>
</table>
<?php } ?>
</div>
</div>
