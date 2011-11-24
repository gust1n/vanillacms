<?php if (!defined('APPLICATION')) exit(); ?>
<?php if($this->YoutubeID) { ?>

   <?php if (IsMobile()) { ?>
      <center>
      <object width="240" height="150">
      	<param name="movie" value="http://www.youtube.com/v/<?php echo $this->YoutubeID; ?>&hl=sv_SE&fs=1&color1=0x3a3a3a&color2=0x999999"></param>
      	<param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>
      	<embed src="http://www.youtube.com/v/<?php echo $this->YoutubeID; ?>&hl=sv_SE&fs=1&color1=0x3a3a3a&color2=0x999999" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="240" height="150"></embed>
      </object>
      </center>
  <?php }else{ ?>
<center>
<object width="960" height="588">
	<param name="movie" value="http://www.youtube.com/v/<?php echo $this->YoutubeID; ?>&hl=sv_SE&fs=1&color1=0x3a3a3a&color2=0x999999"></param>
	<param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param>
	<embed src="http://www.youtube.com/v/<?php echo $this->YoutubeID; ?>&hl=sv_SE&fs=1&color1=0x3a3a3a&color2=0x999999" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="960" height="588"></embed>
</object>
</center>
<?php 
} } else if ($this->PhotoUrl && $this->Alt) {
	echo Img(C('Garden.Domain') . '/'.$this->PhotoUrl, array('alt' => $this->Alt));
}
?>
