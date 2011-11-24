<?php if (!defined('APPLICATION')) exit(); 
$Session = Gdn::Session();
$ShowRouteInfo = 0;
$HeaderText = T('Add page');
if (is_object($this->Page)) {
	$HeaderText = T('Edit');
	echo '<style type="text/css">.PromtText{display:none}</style>';
	if ($this->Page->Route == 1) {
		$ShowRouteInfo = 1;
	}
	
	if ($this->Page->IsParentOnly == 1) {
	   echo "<script type='text/javascript'>jQuery(document).ready(function($) {
	     $('.ParentNotOptional').hide();
	   });
	   </script>";
	   
	    
	  }
	
} else {
   echo "<script type='text/javascript'>jQuery(document).ready(function($) { $('#Form_Permission').attr('checked', true); });</script>";
}
if ($this->Form->Errors()) {
	echo '<style type="text/css">.PromtText{display:none}</style>';
}		
?>
<h1 id="MainHeader"><?php 
   echo $HeaderText . ':&nbsp;' . $this->Page->Name; 
   //Parent only
   echo '<span id="ParentOnly">' . $this->Form->CheckBox('IsParentOnly', HoverHelp(T('Parent Page'), T('Parent Page means that this is JUST a parent page and has no content of its own')), array('value' => '1')) . '</span>';
?></h1>

<?php
echo '<div id="MainContent"><label id="NamePromtText" class="PromtText" for="Form_Name">'.T('Enter page name...').'</label><div style="width:100%;height:50px">';
echo $this->Form->TextBox('Name', array('class' => 'InputBox PageName', 'autocomplete' => 'off'));

//UrlCode
echo '<div id="UrlCode">';
echo Wrap(T('Page Url:'), 'strong');
echo ' ';
echo Gdn::Request()->Url('&nbsp;', TRUE);

echo Wrap($this->Form->GetValue('UrlCode'));
echo $this->Form->TextBox('UrlCode');
echo Anchor(T('edit'), '#', 'Edit');
echo Anchor(T('OK'), '#', 'Save SmallButton');
echo '</div></div><div class="ParentNotOptional">';		


//Body
echo $this->Form->TextBox('Body', array('MultiLine' => TRUE, 'class' => 'WymEditor'));

?>
<div class="PostMeta"><h2><?php echo T('Custom Fields'); ?></h2>
   <div id="MetaAjaxResponse"></div>
   <table id="MetaList" <?php if(!$this->PageMetaData) echo 'style="display:none"';?>>
      <thead>
         <tr>
            <th style="width:220px">
                  <?php echo T('Name');?>
            </th>
            <th>
                  <?php echo T('Asset');?>
            </th>
            <th>
                  <?php echo T('Content');?>
            </th>
         </tr>
      </thead>
      <tbody id="TheList">
         <?php
         if ($this->PageMetaData) {
            foreach ($this->PageMetaData->Result() as $PageMeta) {
               echo '<tr><td>';
               echo $PageMeta->MetaKeyName;
               echo Anchor('['.T('Delete').']', '/vanillacms/settings/deletemeta', 'DeleteMeta');
               echo '<a href="#editmeta" class="EditMeta">['.T('Edit').']</a>';
               //echo Anchor('['.T('Edit').']', '#', 'EditMeta');
               echo '<input type="hidden" id="Form_MetaKey[ ]" name="Page/MetaKey[ ]" value="'.$PageMeta->MetaKey.'|'.$PageMeta->MetaKeyName.'|'.$PageMeta->MetaValue.'|'.$PageMeta->MetaAsset.'|'.$PageMeta->MetaAssetName.'" />';
               echo '</td><td>';
               echo $PageMeta->MetaAssetName;
               echo '</td><td>';
               echo $PageMeta->MetaValue;
               echo '</td></tr>';
            }
         }
         
         ?>
      </tbody>
   </table>
   <strong>Lägg till nytt eget fält:</strong>
   <table id="NewMeta">
      <thead>
         <tr>
            <th style="width:45%">
               <label style="width:150px" for="MetaKeySelect">
                  <?php echo T('Name');?>
               </label>
               <label style="width:70px" class="AssetShowHide" for="MetaAsset">
                  <?php echo T('Asset');?>
               </label>
            </th>
            <th>
               <label for="MetaValue">
                  <?php echo T('Content');?>
               </label>
            </th>
         </tr>
      </thead>
      <tbody>
         <tr>
            <td style="vertical-align:middle" id="NewMetaLeft" class="Left">
               <select style="margin-right:5px" id="MetaKeySelect" name="MetaKeySelect">
                  <optgroup label="<?php echo T('Extra Info'); ?>"><?php
                     foreach ($this->AvailableMetaKeys as $Key => $Value) {
                        echo '<option value="'.$Key.'">'.$Value.'</option>';
                     } ?>
                  </optgroup>
                  <optgroup label="<?php echo T('Modules'); ?>"><?php
                     foreach ($this->AvailableModules as $Key => $Value) {
                        if ($Key == 'CustomHtmlModule') {
                           echo '<option class="ShowAsset BBCode" value="'.$Key.'">'.$Value.'</option>';
                        } else
                        echo '<option class="ShowAsset" value="'.$Key.'">'.$Value.'</option>';
                     } ?>
                     
                  </optgroup>
               </select>
               <select class="AssetShowHide" id="MetaAssetSelect"><?php
                  foreach ($this->AvailableAssets as $Key => $Value) {
                     echo '<option value="'.$Key.'">'.$Value.'</option>';
                  } ?>
                  
               </select>
               <div style="width:100%;height:14px">&nbsp;</div>
               <a class="Button" id="NewMetaSubmit">Lägg till</a>
            </td>
            <td>
               <textarea id="MetaValue" style="width:99%" name="MetaValue" rows="4" cols="25" tabindex="8"></textarea>
            </td>
         </tr>
      </tbody>
   </table>
   </div>
</div>
<?php 


echo "</div>";
//SidePanel
$ToPanel = '<div class="Box Info"><label for="status">' . T('Status') . ': </label>';
if (!is_object($this->Page)) {
	$ToPanel .= '<span id="status">'. T('New Page') . '</span><div class="clear"></div>';
	$ToPanel .= $this->Form->Button('Save and publish', array('class' => 'Button SaveButton', 'type' => 'submit'));
} elseif (is_object($this->Page) && $this->Page->Enabled != 1) {
	$ToPanel .= '<span id="status">'. T('Not published') . '</span><div class="clear"></div>';
	//$ToPanel .= $this->Form->Button('Publish Page', array('class' => 'Button SaveButton', 'type' => 'submit'));
	$ToPanel .= Anchor( T('Enable'), '/vanillacms/settings/enablepage/'.$this->Page->PageID.'/'.$Session->TransientKey(), 'SmallButton');
	$ToPanel .= $this->Form->Button('Save', array('class' => 'Button SaveButton', 'type' => 'submit'));
} 
else {
	$ToPanel .= '<span id="status">' . T('Saved ') . Gdn_Format::Date($this->Page->DateUpdated) . '</span><div class="clear"></div>';
	$ToPanel .= Anchor( T('Disable'), '/vanillacms/settings/disablepage/'.$this->Page->PageID.'/'.$Session->TransientKey(), 'SmallButton');
	$ToPanel .= $this->Form->Button('Save', array('class' => 'Button SaveButton', 'type' => 'submit'));
}
$ToPanel .= '<div class="clear"></div></div>';

$ToPanel .= '<div class="Box" id="PageAttributes"><h2>' . T('Page Options (optional)') . '</h2>';

$ToPanel .= $this->Form->CheckBox('InMenu', T('Show in Main Menu'), array('value' => '1')) . '<div class="ParentNotOptional"><ul><li>';
$ToPanel .= $this->Form->CheckBox('AllowDiscussion', T('Allow Discussion'), array('value' => '1')) . '</li><li>';
//$ToPanel .= $this->Form->CheckBox('Share', T('Share panel'), array('value' => '1')) . '</li><li>';

$ToPanel .= $this->Form->Label(T('Parent Page'), 'ParentPageID') . $this->Form->Dropdown('ParentPageID', $this->ParentPagesOptions, array('default' => '0')) . '</li><li>';


//$ToPanel .= $this->Form->Label('Associate Discussion', 'DiscussionID') . $this->Form->Dropdown('DiscussionID', $this->DiscussionsOptions, array('default' => '0'));

$ToPanel .= $this->Form->Label('Template', 'Template') . $this->Form->Dropdown('Template', $this->TemplateOptions, array('default' => 'default')) . '</li>';

//$ToPanel .= $this->Form->Label(T('Youtube video #ID'), 'YoutubeID') . $this->Form->TextBox('YoutubeID');

//$ToPanel .=  $this->Form->Label('Custom Css', 'CustomCss') . $this->Form->TextBox('CustomCss', array('class' => 'InputBox CustomCss'));

$ToPanel .= '</li></ul></div></div>';

if(count($this->PermissionData) > 0) {
   $ToPanel .= '<div class="Box"><h2>'.T('Roles & Permissions').'</h2>';
   //$ToPanel .= $this->Form->CheckBoxGridGroups($this->PermissionData, 'Permission');
   $ToPanel .= '<table id="Permissions"><thead><tr><th style="width:80px">Roll</th><th>Redigera</th><th>Besök</th></tr></thead><tbody>';
   foreach ($this->PermissionData as $Data) {
      $ToPanel .=  '<tr><td class="RoleName">'.$Data['_Info']['Name'].'</td>';
      
      if ($Data['Page.Edit']['PostValue']) {
         $Attributes = array('value' => $Data['Page.Edit']['PostValue']);
         if($Data['Page.Edit']['Value'] == 1)
            $Attributes['checked'] = 'checked';

         //echo $Data['Page.View']['PostValue'];

         $ToPanel .=  '<td>'.$this->Form->CheckBox('Permission[]', '',$Attributes).'</td>';
         unset($Attributes);
      } else {
         $ToPanel .=  '<td></td>';
      }
      
      
      $Attributes = array('value' => $Data['Page.View']['PostValue']);
      if($Data['Page.View']['Value'] == 1)
         $Attributes['checked'] = 'checked';
      
      //echo $Data['Page.View']['PostValue'];
      
      $ToPanel .=  '<td>'.$this->Form->CheckBox('Permission[]', '',$Attributes).'</td></tr>';
      
      /*
      $Attributes = array('value' => $CheckBox['PostValue']);
            if($CheckBox['Value'])
               $Attributes['checked'] = 'checked';
               
            $Result .= $this->CheckBox($FieldName.'[]', '', $Attributes);*/
      
      
      //print_r($Data);
   }
   
}
   
$ToPanel .= '</tbody></table></div></div>';
$this->AddAsset('Panel2', $ToPanel);
?>