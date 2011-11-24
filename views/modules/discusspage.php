<?php if (!defined('APPLICATION')) exit();  
   $Session = Gdn::Session();
   echo '<a name="DiscussThis"></a><h2 class="Discussions bb pbl">' . T('Discussions') . '</h2>'; 
   
if (is_object($this->DiscussionData) && $this->DiscussionData->NumRows() > 0) {
   echo '<ul class="DataList Discussions mbs">';
   //print_r($this->DiscussionData);
   
	   $this->ShowOptions = FALSE;

	   include($this->FetchViewLocation('discuss_page_discussions', 'vanillacms')); ?>
   </ul><?php
} else { ?>

	   <?php echo '<div class="ptm floatl">' . T('No discussions yet.') . '</div>'; 
}
	   if ($Session->IsValid()) {
   		echo Anchor(T('New Discussion'), 'post/discussion?PageID='.$this->PageID, 'Button bold ptm floatr');
   	} else {
   		echo Anchor(T('Sign In'), '/entry/?Target='.urlencode($this->SelfUrl), ''.(C('Garden.SignIn.Popup') ? 'JpPageSignIn SignInPopup Button ptm bold floatr' : ''));
   	}