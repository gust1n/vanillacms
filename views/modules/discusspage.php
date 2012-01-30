<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
$this->FireEvent('BeforeCommentsRender');

if (!function_exists('WriteComment')) {
	include($this->FetchViewLocation('discuss_page_helper_functions', 'vanillacms'));
} 
   
$CurrentOffset = $this->Offset; 
$CountComments = $this->DiscussionData->NumRows();?>

<div class="Tabs HeadingTabs DiscussionTabs <?php echo $PageClass; ?>">
   <div class="SubTab"><?php echo $CountComments . ' Comments'; ?></div>
</div>
<?php
// Only prints individual comment list items
$CommentData = $this->DiscussionData->Result();

echo '<ul class="DataList MessageList Discussion '.$PageClass.'">'; 
	foreach ($CommentData as $Comment) {
	   ++$CurrentOffset;
	   $this->CurrentComment = $Comment;
	   WriteComment($Comment, $this, $Session, $CurrentOffset);
	}
echo '</ul>'; ?>
<div class="MessageForm CommentForm">
	<div class="Tabs CommentTabs">
	   <ul>
	      <li class="Active"><?php echo Anchor(T('Write Comment'), '#', 'WriteButton TabLink'); ?></li>
	      <?php $this->FireEvent('AfterCommentTabs'); ?>
	   </ul>
	</div>
<?php

   $CommentForm = Gdn::Factory('Form');
   $CommentForm->SetModel($this->CommentModel);
   $CommentForm->AddHidden('PageID', $this->PageID);
	$CommentForm->AddHidden('DiscussionID', $this->DiscussionID);
   echo $CommentForm->Open(array('class' => ''));
   echo $CommentForm->TextBox('Body', array('MultiLine' => TRUE, 'value' => ''));
   echo $CommentForm->Close('Comment');
?></div>
   
