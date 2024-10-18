<div class="wrap">
<h1 class="wp-heading-inline">アンケート一覧</h1>
<a class="page-title-action" href="<?php echo View::getBaseUrl(); ?>&cmd=create">新規作成</a>
<hr class="wp-header-end" />
<!-- updated | error -->
<?php
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
	$rows = Theme::selectAll();
	if ( empty($rows) ) {
		echo 'アンケートを作りましょう';
	} else {
?>
<table class="wp-list-table widefat fixed striped table-view-list users">
	<thead>
		<tr>
			<th scope="col" class="sorted asc"><a href="<?php echo View::getBaseUrl(); ?>&orderby=tid&order=desc"><span>TID</span><span class="sorting-indicator"></span></a></th>
			<th scope="col" class="sortable desc"><a href="<?php echo View::getBaseUrl(); ?>&orderby=name&order=desc"><span>名称</span><span class="sorting-indicator"></span></a></th>
			<th scope="col" class="sortable desc"><a href="<?php echo View::getBaseUrl(); ?>&orderby=startTime&order=asc"><span>開始日</span><span class="sorting-indicator"></span></a></th>
			<th scope="col" class="sortable desc"><a href="<?php echo View::getBaseUrl(); ?>&orderby=endTime&order=asc"><span>終了日</span><span class="sorting-indicator"></span></a></th>
			<th scope="col" class="sortable desc"><a href="<?php echo View::getBaseUrl(); ?>&orderby=updateTime&order=asc"><span>更新日時</span><span class="sorting-indicator"></span></a></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach( $rows as $row ) { ?>
		<tr>
			<td><?php echo $row->tid; ?></td>
			<td class="has-row-actions"><?php echo $row->name; ?><br><div class="row-actions">
				<span><a href="<?php echo View::getBaseUrl().'&cmd=update&tid='.$row->tid; ?>">更新</a> | </span>
				<span><a href="<?php echo View::getBaseUrl().'&cmd=delete&tid='.$row->tid; ?>">削除</a> | </span>
				<span><a href="<?php echo View::getBaseUrl().'&cmd=result&tid='.$row->tid; ?>">結果</a> | </span>
				<span><a href="<?php echo View::getBaseUrl().'&cmd=copy&tid='.$row->tid; ?>">複製</a></span>
				</div>
			</td>
			<td><?php echo date('Y/m/d',strtotime($row->startTime)); ?></td>
			<td><?php echo date('Y/m/d',strtotime($row->endTime)); ?></td>
			<td><?php echo $row->updateTime; ?></td>
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
