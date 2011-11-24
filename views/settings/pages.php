<?php if (!defined('APPLICATION')) exit();
/*
print_r($this->Pages);
return;*/

$Session = Gdn::Session();
//$PluginCount = $this->AvailablePages->NumRows();
//$EnabledCount = $this->EnabledPages->NumRows();
//$DisabledCount = $PluginCount - $EnabledCount;
?>
<h1><?php echo T('Pages'); ?></h1>
<div class="Info">
   <?php echo T('Easily add and edit all the pages on Jesus People here'); ?>
</div>
<div class="FilterMenu">
	<?php echo Anchor(T('Add Page'), 'vanillacms/settings/addpage', 'Button'); ?>
</div>

<div class="Tabs FilterTabs">
   <ul>
      <li<?php echo $this->Filter == 'all' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('All '.Wrap($PluginCount)), 'vanillacms/settings/pages/all'); ?></li>
      <li<?php echo $this->Filter == 'enabled' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Enabled '.Wrap($EnabledCount)), 'vanillacms/settings/pages/enabled'); ?></li>
      <li<?php echo $this->Filter == 'disabled' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Disabled '.Wrap($DisabledCount)), 'vanillacms/settings/pages/disabled'); ?></li>
   </ul>
</div>
<?php echo $this->Form->Errors(); ?>
<table id="PageTable" class="FormTable AltRows">
	<thead>
		<tr id="0">
			<th width="350px"><?php echo T('Page'); ?></th>
			<th class="Alt"><?php echo T('Last Edited'); ?></th>
			<th class="Alt"><?php echo T('Options'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$Alt = FALSE;
		foreach ($this->Pages as $Parent) {
			$LastUpdated = $Parent['DateUpdated'] > $Parent['DateInserted'] ? $Parent['DateUpdated'] : $Parent['DateInserted'];
			$LastUserName = $Parent['UpdateUserName'] ? $Parent['UpdateUserName'] : $Parent['InsertUserName'];
			$LastUserID = $Parent['UpdateUserID'] ? $Parent['UpdateUserID'] : $Parent['InsertUserID'];
		   $Css = $Parent['Enabled'] == 1 ? 'Enabled' : 'Disabled';
		   $State = strtolower($Css);
		   if ($this->Filter == 'all' || $this->Filter == $State) {
		      $Alt = $Alt ? FALSE : TRUE;
		      $ScreenName = $Parent['Name'];
		      //$SettingsUrl = $State == 'enabled' ? ArrayValue('SettingsUrl', $PluginInfo, '') : '';
		      $RowClass = $Css;
		      if ($Alt) $RowClass .= ' Alt';
		      ?>
		      <tr class="More <?php echo $RowClass; ?>">
		         <td><?php 
		            echo Anchor($ScreenName, $Parent['UrlCode'], array('class' => 'ParentPage Page')); 
		            echo Anchor(T('Edit'), '/vanillacms/settings/editpage/'.$Parent['PageID'], 'SmallButton EditButton'); ?>
		         </td>
		         <td><?php echo $LastUpdated . ' ' . T('by') . ' ' . Anchor($LastUserName, 'profile/'.$LastUserID.'/'.$LastUserName); ?></td>
		         <td><?php
			         if ($Parent['Enabled'] == 1)
				         echo Anchor( T('Disable'), '/vanillacms/settings/disablepage/'.$Parent['PageID'].'/'.$Session->TransientKey(), 'SmallButton');
			         else
				         echo Anchor( T('Enable'), '/vanillacms/settings/enablepage/'.$Parent['PageID'].'/'.$Session->TransientKey(), 'SmallButton');
			         echo Anchor(T('Delete'), '/vanillacms/settings/deletepage/'.$Parent['PageID'].'/'.$Session->TransientKey(), 'DeleteMessage SmallButton'); ?>
		         </td>
		      </tr>
		      
		
		      <?php
      		foreach ($Parent['Children'] as $Child) { 
      		   $ChildLastUpdated = $Child->DateUpdated > $Child->DateInserted ? $Child->DateUpdated : $Child->DateInserted;
      			$ChildLastUserName = $Child->UpdateUserName ? $Child->UpdateUserName : $Child->InsertUserName;
      			$ChildLastUserID = $Child->UpdateUserID ? $Child->UpdateUserID : $Child->InsertUserID;
      		   $ChildCss = $Child->Enabled == 1 ? 'Enabled' : 'Disabled';?>
      		   <tr class="More <?php echo $ChildCss; ?>">
      		      <td>
      		         <?php echo Anchor('- ' .$Child->Name, $Child->UrlCode, array('class' => 'Page')); 
      		          echo Anchor(T('Edit'), '/vanillacms/settings/editpage/'.$Child->PageID, 'SmallButton EditButton');?>
      		      </td>
      		      <td>
      		         <?php echo $ChildLastUpdated . ' ' . T('by') . ' ' . Anchor($ChildLastUserName, 'profile/'.$ChildLastUserID.'/'.$ChildLastUserName); ?>
      		      </td>
      		      <td><?php
   			         if ($Child->Enabled == 1)
   				         echo Anchor( T('Disable'), '/vanillacms/settings/disablepage/'.$Child->PageID.'/'.$Session->TransientKey(), 'SmallButton');
   			         else
   				         echo Anchor( T('Enable'), '/vanillacms/settings/enablepage/'.$Child->PageID.'/'.$Session->TransientKey(), 'SmallButton');
   			         echo Anchor(T('Delete'), '/vanillacms/settings/deletepage/'.$Child->PageID.'/'.$Session->TransientKey(), 'DeleteMessage SmallButton'); ?>
   		         </td>
      		<?php }
      		
		    
		} //END LOOP
         
	}
		?>

</table>

