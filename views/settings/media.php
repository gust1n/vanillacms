<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
?>
<h1><?php echo T('Media'); ?></h1>
<div class="FilterMenu"><?php echo Anchor(T('Upload'), 'vanillacms/settings/uploadmedia', 'Button'); ?></div>
<table>
	<tr id="0" class="AltColumns">
		<th><?php echo T('Name'); ?></th>
		<th class="Alt"><?php echo T('Description'); ?></th>
		<th><?php echo T('Uploaded by'); ?></th>
	</tr>
<?php
foreach ($this->MediaData as $Media) {
	echo '<tr id="'. $Media->MediaID . '">';
		echo '<td>media_' . $Media->Name . '<div>' . Anchor('View', C('Garden.Domain') . '/uploads/media_' . $Media->Name) . ' | ' . Anchor('Delete', '/vanillacms/settings/deletemedia/' . $Media->MediaID . '/'.$Session->TransientKey(), 'DeleteMessage') . '</div></td>';
		echo '<td class="Alt">' . $Media->Description . '</td>';
		echo '<td>' . $Media->UserName . '</td>';
		
		echo '</tr>';
}

?>
</table>