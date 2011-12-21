<?php if (!defined('APPLICATION')) exit(); 
$Session = Gdn::Session();

setcookie("admin", "1", time()+3600, '/');
if (is_object($this->Page)) {
	$HeaderText = T('Edit') . ': ' . $this->Page->Name;
	//echo '<style type="text/css">.PromtText{display:none}</style>';	
} else {
   $HeaderText = T('Add '. $this->Type);
   //echo "<script type='text/javascript'>jQuery(document).ready(function($) { $('#Form_Permission').attr('checked', true); });</script>";
}
/*
   TODO Move to Controller
*/
$IsCoreTemplate = false;
if (array_key_exists($this->Page->Template, C('VanillaCMS.CoreTemplates'))) {
   $IsCoreTemplate = true;
   echo '<input type="hidden" id="IsCoreTemplate" value="true" />';
} else {
   echo '<input type="hidden" id="IsCoreTemplate" value="false" />';
}
?>
<h1 id="MainHeader"><?php echo $HeaderText; ?></h1>

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
   'value' => $value
   ));

//UrlCode
echo '<div id="UrlCodeContainer">';
echo Wrap(T('Page Url:'), 'strong') . '  ';
echo Gdn::Request()->Url('&nbsp;', TRUE);
$UrlCodeExploded = explode('/', $this->Page->UrlCode);
$ThisUrlCode = $UrlCodeExploded[count($UrlCodeExploded) - 1];
$ParentUrlCode = substr($this->Page->UrlCode, 0, -strlen($ThisUrlCode));

if ($IsCoreTemplate) {
   echo Wrap($this->Page->UrlCode, 'span', array('id' => 'ParentUrlCode'));
   echo Wrap('', 'span', array('id' => 'UrlCode'));
} else {
   echo Wrap($ParentUrlCode, 'span', array('id' => 'ParentUrlCode'));
   echo Wrap($ThisUrlCode, 'span', array('id' => 'UrlCode'));
}

echo $this->Form->TextBox('UrlCode', array('value' => $ThisUrlCode));
echo Anchor(T('edit'), '#', 'EditUrlCode UrlToggle');
echo Anchor(T('OK'), '#', 'SaveUrlCode SmallButton UrlToggle');
echo Anchor(T('visit'), Gdn::Request()->Url($this->Page->UrlCode, TRUE), 'VisitLink', array('target' => '_blank', 'id' => 'VisitLink'));
echo '</div></div><div class="ParentNotOptional">';		


//Body
echo $this->Form->TextBox('Body', array('MultiLine' => TRUE, 'class' => 'Editor'));

?>
<div class="PostMeta"><h2><?php echo T('Custom Fields'); ?></h2>
   <div id="MetaAjaxResponse"></div>
   <table id="MetaList">
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
      <tbody id="TheList"></tbody>
   </table>
   <strong><?php echo T('Add Custom Field'); ?></strong>
   <table id="NewMeta">
      <thead>
         <tr>
            <th style="width:45%">
               <label style="width:150px" for="MetaKeySelect">
                  <?php echo T('Name');?>
               </label>
               <label style="width:70px" class="AssetShowHide" for="MetaAssetSelect">
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
                     foreach ($this->AvailableMetaKeys as $Key => $Module) {
                        echo '<option value="'.$Key.'">'.$Module['Name'].'</option>';                        
                     } ?>
                  </optgroup>
                  <optgroup label="<?php echo T('CMS Modules'); ?>"><?php   
                     foreach ($this->VanillaCMSModules as $key => $Module) {
                        echo '<option class="ShowAsset" value="'.$key.'">'.$Module['Name'].'</option>';
                     } ?>  
                  </optgroup>
                  <optgroup label="<?php echo T('Dashboard Modules'); ?>"><?php   
                     foreach ($this->DashboardModules as $key => $Module) {
                        echo '<option class="ShowAsset" value="'.$key.'">'.$Module['Name'].'</option>';
                     } ?>  
                  </optgroup>
                  <optgroup label="<?php echo T('Vanilla Modules'); ?>"><?php   
                     foreach ($this->VanillaModules as $key => $Module) {
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
   $Time = Gdn_Format::Date($this->Page->DateUpdated > $this->Page->DateInserted ? $this->Page->DateUpdated : $this->Page->DateInserted);
	$ButtonText = T('Publish');
	//$Button = $this->Form->Button('Update page', array('class' => 'Button SaveButton', 'type' => 'submit'));
} 
else {
   $Status = T('Published');
	//$Time = Gdn_Format::Date($this->Page->DateUpdated);
	$Time = Gdn_Format::Date($this->Page->DateUpdated > $this->Page->DateInserted ? $this->Page->DateUpdated : $this->Page->DateInserted);
	$ButtonText = T('Update');	
}

$ToPanel .= T('Status').': <span class="Publish Status">' . $Status . '</span><div class="clear"></div>';
$ToPanel .= T('Last Upated').': <span class="Publish Time">' . $Time . '</span><div class="clear"></div>';
//$ToPanel .= $this->Form->Button('Save Draft', array('class' => 'Button Draft', 'type' => 'submit'));
$ToPanel .= '<input type="submit" id="Form_SaveDraft" name="draft" value="'.T('Save as Draft').'" class="Button Draft" />';
$ToPanel .= '<input type="submit" id="Form_SaveDraft" name="published" value="'.$ButtonText.'" class="Button SaveButton" />';
//$ToPanel .= $this->Form->Button($ButtonText, array('class' => 'Button SaveButton', 'type' => 'submit'));
$ToPanel .= '</div>';
$ToPanel .= '<div class="Box" id="PageAttributes"><h2>' . T('Page Options (optional)') . '</h2>';
$ToPanel .= $this->Form->CheckBox('InMenu', T('Show in Main Menu'), array('value' => '1')) . '<ul><li class="ParentNotOptional">';
$ToPanel .= $this->Form->CheckBox('AllowDiscussion', T('Allow Discussion'), array('value' => '1')) . '</li><li>';
$ToPanel .= $this->Form->Label(T('Parent Page'), 'ParentPageID') . '<select id="Form_ParentPageID" name="Page/ParentPageID" default="-1">';
$ToPanel .= '<option value="-1" data-url="">'.T('None').'</option>';            

$Right = array(); // Start with an empty $Right stack
$LastRight = 0;
$OpenCount = 0;
$Loop = 0;

foreach ($this->AvailableParents->Result() as $Page) {
   if ($Page->PageID > 0) {
      // Only check stack if there is one
      $CountRight = count($Right);
      if ($CountRight > 0) {  
         // Check if we should remove a node from the stack
         while (array_key_exists($CountRight - 1, $Right) && $Right[$CountRight - 1] < $Page->TreeRight) {
            array_pop($Right);
            $CountRight--;
         }  
      }  
      
      // Are we opening a new list?
      if ($CountRight > $LastRight) {
         $OpenCount++;
         //$ToPanel .= "\n<ol>";
      } elseif ($OpenCount > $CountRight) {
         // Or are we closing open list and list items?
         while ($OpenCount > $CountRight) {
            $OpenCount--;
            //$ToPanel .= "</li>\n</ol>\n";
         }
         $ToPanel .= '</option>';
      } elseif ($Loop > 0) {
         // Or are we closing an open list item?
         $ToPanel .= "</option>";
      }

      $Space = '';
      $i = 1;
      while ($i < $Page->Depth) {
         $Space = $Space . '&nbsp;&nbsp;&nbsp;';
         $i++;
      }
      $attr = '';
      if ($Page->PageID == $this->Page->ParentPageID) {
         $attr = 'selected = "selected"';
      }
      
      $ToPanel .= "\n".'<option value="'.$Page->PageID.'" data-url="'.$Page->UrlCode.'" '.$attr.'>' . $Space . $Page->Name;
      
      // Add this node to the stack  
      $Right[] = $Category->TreeRight;
      $LastRight = $CountRight;
      $Loop++;
   }
}
if ($OpenCount > 0)
   $ToPanel .= "</li>\n</ol>\n</li>\n";
else
   $ToPanel .= "</option>\n";


//$ToPanel .= '</ol>';
$ToPanel .= '</select>';
            //$this->Form->Dropdown('ParentPageID', , array('default' => '0', 'IncludeNull' => true)) . 
$ToPanel .= '<li>';

//$ToPanel .= $this->Form->Label('Associate Discussion', 'DiscussionID') . $this->Form->Dropdown('DiscussionID', $this->DiscussionsOptions, array('default' => '0'));

$ToPanel .= $this->Form->Label('Template', 'Template');
$ToPanel .= '<select id="Form_Template" name="Page/Template">';
$ToPanel .= '<optgroup label="'.T('Theme Templates').'">';
$Selected = '';
foreach ($this->TemplateOptions as $UrlCode => $Name) {
   if ($this->Page->UrlCode == $UrlCode) {
      $Selected = 'selected="selected"';
   }
   $ToPanel .= '<option value="'.$UrlCode.'" '.$Selected.'>' . $Name . '</option>';
   $Selected = '';
}
$ToPanel .= '</optgroup><optgroup label="'.T('Core Templates').'">';
foreach ($this->CoreTemplates as $UrlCode => $Name) {
   if ($this->Page->UrlCode == $UrlCode) {
      $Selected = 'selected="selected"';
   }
   $ToPanel .= '<option value="'.$UrlCode.'" '.$Selected.' class="CoreTemplate">' . $Name . '</option>';
   $Selected = '';
}
$ToPanel .= '</optgroup></select></li>';

//$ToPanel .= $this->Form->Label(T('Youtube video #ID'), 'YoutubeID') . $this->Form->TextBox('YoutubeID');

//$ToPanel .=  $this->Form->Label('Custom Css', 'CustomCss') . $this->Form->TextBox('CustomCss', array('class' => 'InputBox CustomCss'));

$ToPanel .= '</ul></div>';

/*
if(count($this->PermissionData) > 0) {
   $ToPanel .= '<div class="Box"><h2>'.T('Roles &amp; Permissions').'</h2>';
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