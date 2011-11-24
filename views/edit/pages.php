<?php if (!defined('APPLICATION')) exit();
/*
print_r($this->Pages);
return;*/

$Session = Gdn::Session();
//$PluginCount = $this->AvailablePages->NumRows();
//$EnabledCount = $this->EnabledPages->NumRows();
//$DisabledCount = $PluginCount - $EnabledCount;
?>
<h1><?php echo T('Pages');?></h1>

<div class="Info">
   <?php echo T('Easily add and edit your pages here!'); ?>
</div>
<div class="FilterMenu">
	<?php echo Anchor(T('Add Page'), 'edit/add/page', 'Button'); ?>
</div>

<div class="Tabs FilterTabs">
   <ul>
      <li<?php echo $this->Filter == 'all' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('All '.Wrap($this->UnpublishedCount + $this->PublishedCount)), 'edit/pages/all'); ?></li>
      <li<?php echo $this->Filter == 'published' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Enabled '.Wrap($this->PublishedCount)), 'edit/pages/published'); ?></li>
      <li<?php echo $this->Filter == 'draft' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Disabled '.Wrap($this->UnpublishedCount)), 'edit/pages/draft'); ?></li>
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
		   $Status = $Parent['Status'] == 'published' ? 'Enabled' : 'Disabled';
		   $State = strtolower($Status);
		   //echo $Parent['Status'];
		   if ($this->Filter == 'all' || $this->Filter == $State) {
		      $Alt = $Alt ? FALSE : TRUE;
		      $ScreenName = $Parent['Name'];
		      //$SettingsUrl = $State == 'enabled' ? ArrayValue('SettingsUrl', $PluginInfo, '') : '';
		      $RowClass = $Status;
		      if ($Alt) $RowClass .= ' Alt';
		      ?>
		      <tr class="More <?php echo $RowClass; ?>">
		         <td><?php 
		            echo Anchor($ScreenName, $Parent['UrlCode'], array('class' => 'ParentPage Page')); 
		            echo Anchor(T('Edit'), '/edit/'.$Parent['PageID'], 'SmallButton EditButton'); ?>
		         </td>
		         <td><?php echo $LastUpdated . ' ' . T('by') . ' ' . Anchor($LastUserName, 'profile/'.$LastUserID.'/'.$LastUserName); ?></td>
		         <td><?php
			         if ($Status == 'Enabled')
				          echo Anchor( T('Disable'), '/edit/status/'.$Parent['PageID'].'/draft/'.$Session->TransientKey(), 'SmallButton UnpublishMessage');
			         else
				          echo Anchor( T('Enable'), '/edit/status/'.$Parent['PageID'].'/published/'.$Session->TransientKey(), 'SmallButton PublishMessage');
			         echo Anchor(T('Delete'), '/edit/status/'.$Parent['PageID'].'/deleted/'.$Session->TransientKey(), 'DeleteMessage SmallButton'); ?>
		         </td>
		      </tr>
		      
		
		      <?php
		      if (array_key_exists('Children', $Parent)) {
		      
      		   foreach ($Parent['Children'] as $Child) { 
         		   $ChildLastUpdated = $Child->DateUpdated > $Child->DateInserted ? $Child->DateUpdated : $Child->DateInserted;
         			$ChildLastUserName = $Child->UpdateUserName ? $Child->UpdateUserName : $Child->InsertUserName;
         			$ChildLastUserID = $Child->UpdateUserID ? $Child->UpdateUserID : $Child->InsertUserID;
         		   $ChildStatus = $Child->Status == 'published' ? 'Enabled' : 'Disabled';?>
         		   <tr class="More <?php echo $ChildStatus; ?>">
         		      <td>
         		         <?php echo Anchor('- ' .$Child->Name, $Child->UrlCode, array('class' => 'Page')); 
         		          echo Anchor(T('Edit'), '/edit/'.$Child->PageID, 'SmallButton EditButton');?>
         		      </td>
         		      <td>
         		         <?php echo $ChildLastUpdated . ' ' . T('by') . ' ' . Anchor($ChildLastUserName, 'profile/'.$ChildLastUserID.'/'.$ChildLastUserName); ?>
         		      </td>
         		      <td><?php
      			         if ($ChildStatus == 'Enabled')
      				         echo Anchor( T('Disable'), '/edit/status/'.$Child->PageID.'/draft/'.$Session->TransientKey(), 'SmallButton UnpublishMessage');
      			         else
      				         echo Anchor( T('Enable'), '/edit/status/'.$Child->PageID.'/published/'.$Session->TransientKey(), 'SmallButton PublishMessage');
      			         echo Anchor( T('Delete'), '/edit/status/'.$Child->PageID.'/deleted/'.$Session->TransientKey(), 'SmallButton DeleteMessage'); ?>
      		         </td>
         		<?php }
         		
		      }
      		
		    
		} //END LOOP
         
	}
		?>

</table>

