<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session();
if (!function_exists('WriteDiscussion')) {
   include($this->FetchViewLocation('discuss_page_helper_functions'));   
}

$Alt = '';
foreach ($this->DiscussionData->Result() as $Discussion) {
   
   $Alt = $Alt == ' Alt' ? '' : ' Alt';
   WriteDiscussion($Discussion, $this, $Session, $Alt);
}