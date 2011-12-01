<?php if (!defined('APPLICATION')) exit(); 
$Session = Gdn::Session();

foreach ($this->AvailableModules as $key => $Module) {
   echo '<input type="hidden" id="'.$key.'_ShowAssets" value="' . $Module['ShowAssets'] . '" />';
   echo '<input type="hidden" id="'.$key.'_HelpText" value="' . $Module['HelpText'] . '" />';
   echo '<input type="hidden" id="'.$key.'_ContentType" value="' . $Module['ContentType'] . '" />';
}
foreach ($this->AvailableModules as $key => $value) {
   //echo $key;
}


setcookie("admin", "1", time()+3600, '/');
if (is_object($this->Page)) {
	$HeaderText = T('Edit') . ': ' . $this->Page->Name;
	echo '<style type="text/css">.PromtText{display:none}</style>';

	
	if ($this->Page->IsParentOnly == 1) {
	   echo "<script type='text/javascript'>jQuery(document).ready(function($) {
	     $('.ParentNotOptional').hide();
	   });
	   </script>";
	   
	    
	  }
	
} else {
   $HeaderText = T('Add '. $this->Type);
   echo "<script type='text/javascript'>jQuery(document).ready(function($) { $('#Form_Permission').attr('checked', true); });</script>";
}		
?>
<h1 id="MainHeader"><?php 
   echo $HeaderText; 
   //Parent only
   echo '<span id="ParentOnly">' . $this->Form->CheckBox('IsParentOnly', HoverHelp(T('Parent Page'), T('Parent Page means that this is JUST a parent page and has no content of its own')), array('value' => '1')) . '</span>';
?></h1>

<?php
echo '<div id="MainContent"><label id="NamePromtText" class="PromtText" for="Form_Name">'.T('Enter page name...').'</label><div style="width:100%;height:50px">';
$value = T('Enter page name.....');
if ($this->Page->Name) {
   $value = $this->Page->Name;
}
echo $this->Form->TextBox('Name', 
   array(
   'class' => 'InputBox PageName', 
   'autocomplete' => 'off', 
   'value' => $value,
   'onfocus' => "if (this.value == '$value') {this.value = '';}",
   'onblur' => "if (this.value == '') {this.value = '$value';}"
   ));
/*<input id="s" name="s" type="text" value="' . $value . '" onfocus="if (this.value == \'' . $value . '\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'' . $value . '\';}" size="'. $search_form_length .'" tabindex="1" />*/

//UrlCode
echo '<div id="UrlCode">';
echo Wrap(T('Page Url:'), 'strong');
echo ' ';
echo Gdn::Request()->Url('&nbsp;', TRUE);

echo Wrap($this->Form->GetValue('UrlCode'));
echo $this->Form->TextBox('UrlCode');
echo Anchor(T('edit'), '#', 'EditUrlCode UrlToggle');
echo Anchor(T('OK'), '#', 'SaveUrlCode SmallButton UrlToggle');
echo Anchor(T('visit'), Gdn::Request()->Url($this->Page->UrlCode, TRUE), 'VisitLink', array('target' => '_blank'));
echo '</div></div><div class="ParentNotOptional">';		


//Body
echo $this->Form->TextBox('Body', array('MultiLine' => TRUE, 'class' => 'Editor'));

?>
<div class="PostMeta"><h2><?php echo T('Custom Fields'); ?></h2>
   <div id="MetaAjaxResponse"></div>
   <table id="MetaList" <?php if(!property_exists($this, 'PageMetaData')) echo 'style="display:none"';?>>
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
         if (property_exists($this, 'PageMetaData')) {
            foreach ($this->PageMetaData->Result() as $PageMeta) {
               echo '<tr><td>';
               echo $PageMeta->MetaKeyName;
               echo Anchor('['.T('Delete').']', '/vanillacms/edit/deletemeta', 'DeleteMeta');
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
   <strong><?php echo T('Add Custom Field'); ?></strong>
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
               <label for="MetaValue" id="MetaValueLabel"><?php echo T('Content');?></label>
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
                     foreach ($this->AvailableModules as $key => $Module) {
                        echo '<option class="ShowAsset" value="'.$key.'">'.$Module['Name'].'</option>';
                     } ?>
                     
                  </optgroup>
               </select>
               <select class="AssetShowHide" id="MetaAssetSelect"><?php
                  foreach ($this->AvailableAssets as $Key => $Value) {
                     echo '<option value="'.$Key.'">'.$Value.'</option>';
                  } ?>
                  
               </select>
               <div style="width:100%;height:14px">&nbsp;</div>
               <a class="Button" id="NewMetaSubmit"><?php echo T('Add');?></a>
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
$ToPanel = '<div class="Box Info"><h2>' . T('Publish') . ': </h2>';
if (!is_object($this->Page)) {
	$Status = T('New Page');
	$Time = T('Unsaved');
	$ButtonText = T('Publish');
} elseif ($this->Page->Status == 'draft') {
	$Status = T('Draft');
   $Time = Gdn_Format::Date($this->Page->DateUpdated);
	$ButtonText = T('Publish');
	//$Button = $this->Form->Button('Update page', array('class' => 'Button SaveButton', 'type' => 'submit'));
} 
else {
   $Status = T('Published') . Anchor( T('Disable'), '/edit/status/'.$this->Page->PageID.'/draft/'.$Session->TransientKey(), 'UnpublishMessage');
	$Time = Gdn_Format::Date($this->Page->DateUpdated);
	$ButtonText = T('Update');	
}
$ToPanel .= T('Status').': <span class="Publish Status">' . $Status . '</span><div class="clear"></div>';
$ToPanel .= T('Last Saved').': <span class="Publish Time">' . $Time . '</span><div class="clear"></div>';
//$ToPanel .= $this->Form->Button('Save Draft', array('class' => 'Button Draft', 'type' => 'submit'));
$ToPanel .= '<input type="submit" id="Form_SaveDraft" name="draft" value="'.T('Save as Draft').'" class="Button Draft">';
$ToPanel .= '<input type="submit" id="Form_SaveDraft" name="published" value="'.$ButtonText.'" class="Button SaveButton">';
//$ToPanel .= $this->Form->Button($ButtonText, array('class' => 'Button SaveButton', 'type' => 'submit'));
$ToPanel .= '</div>';

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

/*
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
  /* }
   $ToPanel .= '</tbody></table></div></div>';
}*/
   

$this->AddAsset('Panel2', $ToPanel);
?>