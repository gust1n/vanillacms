<?php if (!defined('APPLICATION')) exit(); ?>
	<h3 id="RecentDiscussionsHeader" class="pbl bg dark"><i class="prm Img Sprite Medium RecentDiscussions"></i><span class="linkWrap"><?php echo T('Recent Discussions'); ?></span></h3>
	<div id="RecentDiscussions" class="prl">
	<?php

	foreach ($this->_CommentsData as $Comment) {
		if(!$Comment->LastUserID)
			$Comment->LastUserID = $Comment->FirstUserID;
		if($Comment->LastName)
		   $Name = $Comment->LastName;
		else
		   $Name = $Comment->FirstName;
		echo '<div class="RecentDiscussion mbs bb pls">';
		echo '<h4>' . Anchor($Comment->Name, 'discussion/' .  $Comment->DiscussionID) . '</h4>';
		echo '<div class="Meta"><a class="Comments prs" href="discussion/' . $Comment->DiscussionID . '"><span class="linkWrap">';		
		printf(Plural($Comment->CountComments - 1, '%s svar', '%s svar'), $Comment->CountComments - 1); 
		echo '</span><span class="imgWrap floatl"><i class="Img Sprite Tiny Comments"></i></span></a>';
		echo T('Last by') . '&nbsp;<a href="profile/' . $Comment->LastUserID . '/' . $Name .'">' . $Name . '</a><span class="Date pls"><span class="imgWrap"><i class="Img Sprite Tiny Time"></i></span><span class="linkWrap">' .  Gdn_Format::Date($Comment->DateLastComment) . '</span></span>';
		echo '</div></div>';
	}
	
	?>
</div>