<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();

if($this->Page->AllowDiscussion == 1) {
   
   $this->AddAsset('Panel', '<h3><a href="#DiscussThis" class="LargeButton mtm">' . T('Discuss this!') . '</a></h3>');
   //$DiscussPageModule = new DiscussPageModule;

   $DiscussPageModule = new DiscussPageModule($this, $this->Page->PageID);
   $this->AddAsset('AfterContent', $DiscussPageModule);
}


if (isset($this->CustomCss)) {
	echo '<style type="text/css" media="screen">' . $this->CustomCss . '</style>';
} 

if(isset($this->Page->Route))
	$Domain = C('Garden.Domain') . '/' . $this->Page->UrlCode;
else
	$Domain = C('Garden.Domain') . '/' . T('page') . '/' . $this->Page->UrlCode;

if ($this->Page->Template != 'start') {	   
   echo '<h1 class="pbl pll mbl">'. $this->Page->Name . '</h1>';
}
echo '<div id="PageContent" class="PageContent mbl"><div class="innerBorder pbm">';
   echo $this->Page->Body;
   echo '<div id="AfterContent">';
      echo $this->RenderAsset('AfterContent'); ?>
   </div><!-- #AfterContent -->
</div></div><!--End PageContent-->

