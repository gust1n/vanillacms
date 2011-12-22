<?php	if (!defined('APPLICATION')) exit();	
echo Wrap(T('Organize Pages by dragging and dropping') . '<span class="Progress"></span>', 'h1') . '<ol class="Sortable">';

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
         $StatusChangerText = T('Save as Draft');
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
         .Anchor( T('Trash'), '/edit/status/'.$Page->PageID.'/trash/'.$Session->TransientKey(), 'SmallButton DeleteMessage')
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