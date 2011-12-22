<?php	if (!defined('APPLICATION')) exit();	
echo '<h1>' . T('Showing trash can') . Anchor(T('Empty Trash'),'edit/delete/all/' . $Session->TransientKey(), array('class' => 'SmallButton DeleteMessage EmptyTrash')). '</h1>';
echo '<table class="Pages trash">';
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
      .Anchor( T('Restore'), '/edit/status/'.$Page->PageID.'/published/'.$Session->TransientKey(), 'SmallButton Publish StatusMessage')
      .Anchor( T('Delete Permanently'), '/edit/delete/'.$Page->PageID.'/'.$Session->TransientKey(), 'SmallButton DeleteMessage')
      .'</td>';
      
   echo '</tr>';
}
echo '</table>';
?>