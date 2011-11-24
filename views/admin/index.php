<?php if (!defined('APPLICATION')) exit(); 
$Session = Gdn::Session();
echo '<h2>' . T('Your Permissions') . '</h2>';

if($Session->CheckPermission('JpChat.Settings.Manage'))
	echo Anchor(T('Chat Volunteer'), 'jpchat/settings/chat');
?>