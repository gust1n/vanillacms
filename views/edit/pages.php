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

		<?php		
		echo Wrap(T('Organize Pages'), 'h1') . '<ol class="Sortable">';
		
      $Right = array(); // Start with an empty $Right stack
      $LastRight = 0;
      $OpenCount = 0;
      $Loop = 0;
      //die(print_r($this->AllPages->Result()));
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

            echo "\n".'<li id="list_'.$Page->PageID.'">';
            // DEBUG: echo Wrap($Page->Name.' [countright: '.$CountRight.' lastcount: '.$LastRight.' opencount: '.$OpenCount.']', 'div');
            $PageUrl = Url('categories/'.rawurlencode($Page->UrlCode).'/', TRUE);
            echo Wrap(
               '<table'.($OpenCount > 0 ? ' class="Indented '.$Page->Status.'"' : '').'>
                  <tr>
                     <td>
                        <strong>'.$Page->Name.'</strong>
                        '.Anchor(htmlspecialchars(rawurldecode($PageUrl)), $PageUrl).'
                        './*Wrap("ID: {$Page->PageID}, PermID: {$Page->PermissionPageID}", 'div').*/'
                     </td>
                     <td class="Buttons">'
                        .Anchor(T('Edit'), 'vanilla/settings/editcategory/'.$Page->PageID, 'SmallButton')
                        .Anchor(T('Delete'), 'vanilla/settings/deletecategory/'.$Page->PageID, 'SmallButton')
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

