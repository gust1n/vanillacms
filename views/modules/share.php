<?php if (!defined('APPLICATION')) exit(); ?>
<script type="text/javascript">
	tweetmeme_style = 'compact';
</script>
<div class="SharePanel">
	<div class="TweetMemeButton">
		<script type="text/javascript" src="http://tweetmeme.com/i/scripts/button.js"></script>
	</div>
	<div class="FacebookLikeButton">
		<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo $this->FacebookUrl;?>&amp;layout=button_count&amp;show_faces=false&amp;width=60&amp;action=like&amp;font=lucida%2Bgrande&amp;colorscheme=light&amp;height=21" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:60px; height:21px;"></iframe>
	</div>
</div>