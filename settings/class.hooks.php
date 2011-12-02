<?php if (!defined('APPLICATION')) exit();
/*
This file is part of VanillaCMS.
*/

class VanillaCMSHooks implements Gdn_IPlugin {

   /**
    * Adds Pages to menu.
    * 
    * 
    * @package Vanilla CMS
    * 
    */ 
   public function Base_Render_Before(&$Sender) {
      
      $Sender->AddCssFile('vcms.global.css', 'vanillacms');
      $Sender->AddJsFile('jquery.menu.js');
      $Sender->AddJsFile('vcms.global.js', 'vanillacms');
      
      $PageModel = new PageModel();
      $ParentsQuery = $PageModel->GetAllParents();

      $Parents = $ParentsQuery->Result(DATASET_TYPE_ARRAY); 

      $i = 0;
      foreach ($Parents as $Parent) {
         if ($Parent['Status'] == 'published' && $Parent['InMenu'] == 1) {            
            $Sender->Menu->AddLink($Parent['UrlCode'], $Parent['Name'], $Parent['UrlCode'], FALSE);
         }

         $ChildrenQuery = $PageModel->GetPublishedChildren($Parent['PageID']);

         foreach ($ChildrenQuery->Result() as $Child) {
            if ($Child->InMenu == 1) {
                  $Sender->Menu->AddLink($Parent['UrlCode'], '<span class="PageName">' . $Child->Name . '</span>', $Child->UrlCode, FALSE);
            }  
         }
         unset($Children); 
         $i++;
      }
      
      //Removes the default conversations and discussions menu items from vanilla and conversations applications
      $Sender->Menu->RemoveGroup('Conversations');
      $Sender->Menu->RemoveLinks('Discussions');
   }

  /*
   public function DiscussionsController_Render_Before(&$Sender)
     {
        $Sender->FetchViewLocation('test', 'vanilla', 'vanillacms');
        $Sender->View = 'test';
        
     }*/
  

  /*
   public function DiscussionController_BeforeCommentMeta_Handler(&$Sender)
     {
        //echo $Sender->EventArguments['Author']->Photo;


        $PermissionModel = new RoleModel();
        $Roles = $PermissionModel->GetByUserID($Sender->EventArguments['Author']->UserID);
        $Moderator = FALSE;
        // print_r($Roles->Result());
        foreach ($Roles->Result() as $Role) {
           if($Role->RoleID == 32) {
              $Moderator = TRUE;
           }   
        }
        if ($Moderator == TRUE) {
           echo '<div class="Moderator">';
           echo '<div class="ModeratorImgContainer">' . UserPhoto($Sender->EventArguments['Author'], 'ModeratorImg') . 'Moderator</div>';
        }
        else
           echo UserPhoto($Sender->EventArguments['Author'], 'Show');
     }

     public function DiscussionController_AfterCommentMeta_Handler(&$Sender)
     {
        $PermissionModel = new RoleModel();
        $Roles = $PermissionModel->GetByUserID($Sender->EventArguments['Author']->UserID);

        foreach ($Roles->Result() as $Role) {
           if($Role->RoleID == 32) {
              // echo UserPhoto($Sender->EventArguments['Author'], 'ModeratorImg');
              echo '<span class="Moderator">'.$Role->Name.'</span></div>';

           }
        } 
         
     }*/
  
   public function ProfileController_Render_Before($Sender) {
      $Sender->AddCSSFile('plugins/Voting/design/voting.css');
      $Sender->AddJSFile('plugins/Voting/voting.js');
   }

   public function MessageController_AfterGetAssetData_Handler(&$Sender)
   {
      $AssetData = &$Sender->EventArguments['AssetData'];
      $AssetData['Content'] = T('Above Main Content');
      $AssetData['Above Page'] = T('Above Page');
      $AssetData['Panel'] = T('Below Sidebar');
   }

   public function MessageController_AfterGetLocationData_Handler($Sender) {
      $ControllerData = &$Sender->EventArguments['ControllerData'];
      //First fix the default ones to translate, order, remove:
      $ControllerData['[Base]'] = T('Every Page');
      unset($ControllerData['[NonAdmin]']);
      $ControllerData['[Admin]'] = T('All Dashboard Pages');
      $ControllerData['Dashboard/Settings/Index'] = T('Dashboard Home');
      $ControllerData['Dashboard/Profile/Index'] = T('Profile Page');
      $ControllerData['Vanilla/Discussions/Index'] = T('Discussions Page');
      $ControllerData['Vanilla/Discussion/Index'] = T('Comments Page');
      $ControllerData['Dashboard/Entry/Signin'] = T('Sign In Page');
      $ControllerData['Dashboard/Entry/Register'] = T('Register Page');

   }

   public function DiscussionModel_BeforeGet_Handler($Sender) { //Hook the PageID into the Discussion model
      $PageID = GetValue('PageID', $Sender);
      if (is_numeric($PageID) && $PageID > 0)
         $Sender->SQL->Where('PageID', $PageID);
   }

   public function DiscussionController_BeforeCommentBody_Handler($Sender) {
      $Discussion = GetValue('Object', $Sender->EventArguments);
      $PageID = GetValue('PageID', $Discussion);
      if (GetValue('Type', $Sender->EventArguments) == 'Discussion' && is_numeric($PageID) && $PageID > 0) {
         $Data = Gdn::Database()->SQL()->Select('Name, UrlCode')->From('Page')->Where('PageID', $PageID)->Get()->FirstRow();
         if ($Data) {
            echo '<div class="DismissMessage Info">'.sprintf(T('This discussion is related to %s.'), Anchor($Data->Name,$Data->UrlCode)).'</div>';
         }
      }
   }

   public function PostController_Render_Before($Sender) {
      $Session = Gdn::Session();

      // Pass the PageID (if related to page) to the form
      $PageID = GetIncomingValue('PageID');
      if ($PageID > 0 && is_object($Sender->Form))
         $Sender->Form->AddHidden('PageID', $PageID);
   }

   // Make sure to use the PageID when saving discussions if present in the url
   public function DiscussionModel_BeforeSaveDiscussion_Handler($Sender) {
      $PageID = GetIncomingValue('PageID');
      if (is_numeric($PageID) && $PageID > 0) {
         $FormPostValues = GetValue('FormPostValues', $Sender->EventArguments);
         $FormPostValues['PageID'] = $PageID;
         $Sender->EventArguments['FormPostValues'] = $FormPostValues;
      }
   }

   public function Setup() {
      $Database = Gdn::Database();
      $Config = Gdn::Factory(Gdn::AliasConfig);
      $Drop = Gdn::Config('VanillaCMS.Version') === FALSE ? TRUE : FALSE;
      $Explicit = TRUE;
      $Validation = new Gdn_Validation(); // This is going to be needed by structure.php to validate permission names
      include(PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'settings' . DS . 'structure.php');
      $ApplicationInfo = array();
      include(CombinePaths(array(PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'settings' . DS . 'about.php')));
      $Version = ArrayValue('Version', ArrayValue('VanillaCMS', $ApplicationInfo, array()), 'Undefined');

      $Save = array(
         'VanillaCMS.Version' => $Version
         );
      SaveToConfig($Save);
   }

   public function SidaController_AfterCommentMeta_Handler(&$Sender) {
      echo $Sender->RenderAsset('AfterCommentMeta');
   }

  
   public function SearchModel_Search_Handler($Sender) {
        $PageModel = new PageModel();
        $PageModel->Search($Sender);
     }
  

   

   public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {

      $Menu = &$Sender->EventArguments['SideMenu'];
      $Menu->AddLink('Appearance', T('Pages'), 'edit/pages', 'VanillaCMS.Pages.Manage');
      //$Menu->AddLink('Site Settings', T('Media Library'), 'vanillacms/settings/media', 'VanillaCMS.Pages.Manage');
   }
}
