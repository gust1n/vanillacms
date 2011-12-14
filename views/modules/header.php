<div class="page pageWrapper rel line"><?php 

//print_r($this);
   //if ($this->Sender->SelfUrl == Gdn::Router()->GetDestination('DefaultController')) {
      echo '<h1 class="abs MainLogo"><a href="'.Gdn_Url::WebRoot('/').'"><img src="/themes/jesuspeople/design/images/jp_logo.png" alt="Jesus People" /></a></h1>';

      if ($this->Menu) {
         echo '<div class="ptm floatl">' . $this->Menu->ToString() . '</div>';
      }
   //}  ?>
   
   <div class="floatr">
      <div class="Search mbs"><?php
         $Form = Gdn::Factory('Form');
         $Form->InputPrefix = '';
         echo 
            $Form->Open(array('action' => Url('/search'), 'method' => 'get')),
            $Form->TextBox('Search', array('class' => 'InputBox PageSearch', 'Value' => 'Sök...')),
            $Form->Button('Go', array('Name' => '')),
            $Form->Close(); ?>
      </div> <?php
      $Session = Gdn::Session();
      $Authenticator = Gdn::Authenticator();
      $NameCount = 0;
      if ($Session->IsValid()) {
         $Profile = '<span class="linkWrap">'.T('Profile').'</span>';
         $CountNotifications = $Session->User->CountNotifications;
         if (is_numeric($CountNotifications) && $CountNotifications > 0) {
            $Profile .= '<span class="Count floatr">' . $CountNotifications . '</span>';
            $NameCount = $CountNotifications;
         }

         $Inbox = '<span class="linkWrap">'.T('Inbox').'</span>';
         $CountUnreadConversations = $Session->User->CountUnreadConversations;
         if (is_numeric($CountUnreadConversations) && $CountUnreadConversations > 0) {
            $Inbox .= '<span class="Count floatr">'.$CountUnreadConversations.'</span>';
            $NameCount = $NameCount + $CountUnreadConversations;
         }


         $UserOptions = '';
         if ($Session->CheckPermission('Garden.Settings.Manage')) {
            $_SESSION['KCFINDER'] = array();
            $_SESSION['KCFINDER']['disabled'] = false; //If admin, set cookie for editor filebrowser
            
				$UserOptions .= '<li class="Dashboard">' .Anchor('<span class="imgWrap"><i class="Img Sprite Small Dashboard"></i></span><span class="linkWrap">' . T('Dashboard') . '</span>', '/settings', 'Dashboard') . '</li>';
			}
			if ($Session->CheckPermission('VanillaCMS.Internal.View')) {
			   $UserOptions .= '<li class="Internal">' .Anchor('<span class="imgWrap"><i class="Img Sprite Small Internal"></i></span><span class="linkWrap">' . T('Internal') . '</span>', '/settings', 'Internal') . '</li>';
			}
			if ($Session->CheckPermission('JpChat.Schedule.View')) {
			   $UserOptions .= '<li class="Chat">' .Anchor('<span class="imgWrap"><i class="Img Sprite Small Chat"></i></span><span class="linkWrap">' . T('Chat') . '</span>', '/jpchat/settings/chat', 'Chat') . '</li>';
			}	
         $UserOptions .= '<li class="Profile">' .Anchor('<span class="imgWrap"><i class="Img Sprite Small Profile"></i></span>' .$Profile, '/profile/'. $Session->UserID . '/' . $Session->User->Name) . '</li>';	
         $UserOptions .= '<li class="Inbox">' .Anchor('<span class="imgWrap"><i class="Img Sprite Small Inbox"></i></span>' . $Inbox, '/messages/all', 'Inbox') . '</li>';	
         $UserOptions .= '<li class="SignOut">' .Anchor('<span class="imgWrap"><i class="Img Sprite Small SignOut"></i></span><span class="linkWrap">' .T('Sign Out') . '</span>', Gdn::Authenticator()->SignOutUrl(), 'SignOut') . '</li>';	
         ?>
         <ul id="UserMenu" class="Menu UserMenu floatr mts">
            <li class="textr">
               <?php //echo UserPhoto($Session->User, 'UserPhoto'); ?>
               <a id="UserMenuName" class="UserMenuName" href="/profile/<?php echo $Session->UserID . '/' . $Session->User->Name; ?>"><strong><?php
                  echo $Session->User->Name . '</strong>';
                   if($NameCount > 0) {
                      echo '<span class="Count">' . $NameCount . '</span>';
                   } ?>
                   <img class="img Sprite" src="/themes/jesuspeople/design/images/pixel.png" width="1" height="1" />
               </a>
               <ul class="textl">
                  <?php echo $UserOptions; ?>
               </ul>
            </li>
         </ul><?php
      } 
      else
      {
         echo Anchor(Img('/themes/jesuspeople/design/images/pixel.png', array('class' =>'Img SignIn Sprite Small')) . T('Sign In'), Gdn::Authenticator()->SignInUrl(), (SignInPopup() ? 'pat phs SignInPopup HeaderSignIn floatr bold mts' : ''));
         
      }?>
   </div>
</div>