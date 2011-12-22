<?php	if (!defined('APPLICATION')) exit();	
echo Wrap(T('Showing published pages'), 'h1');
echo '<table class="Pages">';
foreach ($this->AllPages->Result() as $Page) {
   if ($Page->PageID == -1) {
      continue;
   }
   $PageUrl = Url('/'.$Page->UrlCode, TRUE);
   $PageDate = $Page->DateUpdated > $Page->DateInserted ? $Page->DateUpdated : $Page->DateInserted;
   $InMenu = $Page->InMenu != 0 ? ' InMenu' : 'NotInMenu';
   echo "\n".'<tr id="page_'.$Page->PageID.'" class="'. $Page->Status.' ' .  $InMenu . '">';
      echo '<td><strong>'.$Page->Name.'&nbsp;&nbsp;&nbsp;</strong>' . Anchor($PageUrl, $PageUrl, array('target' => '_blank')) . '</td>';
      echo '<td class="AuthorDate"><strong>'. T('By:') . ' ' . $Page->InsertUserName.'</strong>   ' . Gdn_Format::Date($PageDate) . '</td>';
      echo '<td class="Buttons">'
      .Anchor(T('Edit'), '/edit/'.$Page->PageID, 'SmallButton EditButton')
      .Anchor( T('Save as Draft'), '/edit/status/'.$Page->PageID.'/draft/'.$Session->TransientKey(), 'SmallButton Draft StatusMessage')
      .Anchor( T('Delete'), '/edit/status/'.$Page->PageID.'/deleted/'.$Session->TransientKey(), 'SmallButton DeleteMessage')
      .'</td>';
      
   echo '</tr>';
}
echo '</table>';
?>