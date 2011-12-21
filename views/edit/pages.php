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
      <li<?php echo $this->Filter == 'published' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Published '.Wrap($this->PublishedCount)), 'edit/pages/published'); ?></li>
      <li<?php echo $this->Filter == 'draft' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Drafts '.Wrap($this->UnpublishedCount)), 'edit/pages/draft'); ?></li>
      <li<?php echo $this->Filter == 'deleted' ? ' class="Active"' : ''; ?>><?php echo Anchor(T('Deleted '.Wrap($this->DeletedCount)), 'edit/pages/deleted'); ?></li>
   </ul>
</div>
<?php echo $this->Form->Errors(); ?>

		<?php		
		echo Wrap(T('Organize Pages') . '<span class="Progress"></span>', 'h1') . '<ol class="Sortable">';
		
      $Right = array(); // Start with an empty $Right stack
      $LastRight = 0;
      $OpenCount = 0;
      $Loop = 0;
      foreach ($this->AllPages->Result() as $Page) {
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
               echo "\n<ol>";
            } elseif ($OpenCount > $CountRight) {
               // Or are we closing open list and list items?
               while ($OpenCount > $CountRight) {
                  $OpenCount--;
                  echo "</li>\n</ol>\n";
               }
               echo '</li>';
            } elseif ($Loop > 0) {
               // Or are we closing an open list item?
               echo "</li>";
            }

            echo "\n".'<li id="page_'.$Page->PageID.'">';
            $PageUrl = Url('/'.$Page->UrlCode, TRUE);
            $PageDate = $Page->DateUpdated > $Page->DateInserted ? $Page->DateUpdated : $Page->DateInserted;
            $Status = '';
            if ($Page->Status != 'published') {
               $Status = '(' . $Page->Status . ')';
               $StatusChangerText = T('Publish');
               $StatusChanger = 'published';
            } else {
               $StatusChangerText = T('Unpublish');
               $StatusChanger = 'draft';
            }
            if ($Page->InMenu != 1) {
               $Status .= '(' . T('Not visible in menu') . ')';
            }
            $InMenu = $Page->InMenu != 0 ? ' InMenu' : 'NotInMenu';
            
            echo Wrap(
               '<table class="'.($OpenCount > 0 ? 'Indented ' : ''). $Page->Status.' ' .  $InMenu . '">
                  <tr>
                     <td>
                        <strong>'.$Page->Name.'&nbsp;&nbsp;&nbsp;</strong>
                        '.Anchor($PageUrl, $PageUrl, array('target' => '_blank')).'
                     </td>
                        <td class="AuthorDate">
                           <strong>'. T('By:') . ' ' . $Page->InsertUserName.'</strong>
                           '.Gdn_Format::Date($PageDate).'
                        </td>
                     <td class="Buttons">'
                        .Anchor(T('Edit'), '/edit/'.$Page->PageID, 'SmallButton EditButton')
                        .Anchor( T($StatusChangerText), '/edit/status/'.$Page->PageID.'/'.$StatusChanger.'/'.$Session->TransientKey(), 'SmallButton StatusMessage')
                        .Anchor( T('Delete'), '/edit/status/'.$Page->PageID.'/deleted/'.$Session->TransientKey(), 'SmallButton DeleteMessage')
                     .'</td>
                  </tr>
               </table>'
            ,'div');

            // Add this node to the stack  
            $Right[] = $Page->TreeRight;
            $LastRight = $CountRight;
            $Loop++;
         }
      }
      if ($OpenCount > 0)
         echo "</li>\n</ol>\n</li>\n";
      else
         echo "</li>\n";

      echo '</ol>';	
?>

