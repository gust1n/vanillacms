<?php if (!defined('APPLICATION')) exit();


class PageController extends VanillaCMSController {

   public $Uses = array('Form', 'PageModel', 'PageMetaModel', 'DiscussionModel', 'CommentModel');

   public function Initialize() {
      parent::Initialize();
      $this->ControllerName = 'pagecontroller';
      $this->CssClass = 'Page';
   }

   public function Index($UrlCode = null, $ChildUrlCode = null)
   {
      if(!isset($UrlCode)) {
         Redirect('dashboard/home/filenotfound');
      }

      $this->Page = $this->PageModel->Get(array('UrlCode' => urldecode($UrlCode), 'Status' => 'published'));

      if(isset($this->Page)){   
            
         $this->AddCssFile('page.css');

         if (isset($ChildUrlCode)) {
            $this->Page = $this->PageModel->Get(array('UrlCode' => urldecode($UrlCode . '/' . $ChildUrlCode)));
         }

         if ($this->Page->ParentPageID > 0) {            
            $Parent = $this->PageModel->Get(array('PageID' => $this->Page->ParentPageID));
            $this->Menu->HighlightRoute('/' . $Parent->UrlCode);
         } else {
            $this->Menu->HighlightRoute('/' . $this->Page->UrlCode);
         }

         if ($this->Head) {
            if ($this->Page->Name == 'Start') {
               $this->Head->Title(T('Welcome to Jesus People!'));
            }
            else {
               $this->Head->Title(C('Garden.Title') . ' - ' . $this->Page->Name);
            }
         }

         if (isset($this->Page->Description)) {
            $this->Head->AddTag('meta', array('name' => 'description', 'content'=>$this->Page->Description));
         }
         
         //Add sidemenu IF there is subpages (has to be before modules for correct sorting)
         $this->AddSideMenu($this->Page->UrlCode);

         $this->Page->PageMeta = $this->PageMetaModel->Get($this->Page->PageID);
         foreach ($this->Page->PageMeta->Result() as $PageMeta) {
            
            if($PageMeta->MetaAsset) { //if is to be added to an asset
               if ($PageMeta->ApplicationFolder) {
                  //Temporarily set to different applicaiton folder for rendering external modules
                  $this->ApplicationFolder = $Module->ApplicationFolder; 
               }

               //Temporarily set config, due to how the external modeules load we have to make this #€%*&# ugly (but working) solution
               if ($PageMeta->ConfigSetting) {
                  SaveToConfig($PageMeta->ConfigSetting, TRUE, FALSE); //Set only to last this request
               }
               
               if ($PageMeta->ApplicationFolder == 'vanillacms') {
                  $Module = new $PageMeta->MetaKey($this, $PageMeta->MetaValue);
               } else {
                  $Module = new $PageMeta->MetaKey($this);
                  if ($PageMeta->GetData == 1) {
                     $Module->GetData($PageMeta->MetaValue);
                  }  
               }
               $this->AddAsset($PageMeta->MetaAsset, $Module);
               unset($Module);
                              
            }
            //or else we have these special ones
            if ($PageMeta->MetaKey == 'MetaDescription') {
               $this->Head->AddTag('meta', array('name' => 'description', 'content'=>$PageMeta->MetaValue));
            }
            if ($PageMeta->MetaKey == 'MetaKeywords') {
               $this->Head->AddTag('meta', array('name' => 'keywords', 'content'=>$PageMeta->MetaValue));
            }
            if ($PageMeta->MetaKey == 'CustomCss') {
               $this->CustomCss = $PageMeta->MetaValue;
            }
            

         }
         
         $this->ApplicationFolder = 'vanillacms'; //Restore to default location for proper rendering
         
         if (isset($this->Page->Share) && $this->Page->Share == 1) {
            $ShareModule = new ShareModule($this);
            if($this->Page->Route) {
               $Url = C('Garden.Domain') . '/' . $this->Page->UrlCode;
            } 
            else {
               $Url = C('Garden.Domain') . '/' . T('page') . '/' . $this->Page->UrlCode;
            }
            $ShareModule->FacebookUrl = $Url;
            $this->AddModule($ShareModule);
         }
         
         //Discussion
         if ($this->Page->AllowDiscussion == 1) {
            $this->AddJSFile('plugins/Voting/voting.js');
            $DiscussionModel = new DiscussionModel();
            $DiscussionModel->PageID = $this->Page->PageID; // Let the model know we want to filter to a particular addon (we then hook into the model in the addons hooks file).
            $this->DiscussionData = $DiscussionModel->Get(0, 50);
         }
         
         $this->MasterView = $this->Page->Template;            

         $this->Render();    
      }
               
   }

   public function AddSideMenu($CurrentUrl = '') {
      $SideMenu = new SideMenuModule($this);
      $SideMenu->HtmlId = 'PageSideMenu';
      $SideMenu->CssClass = 'mbl';
      $SideMenu->AutoLinkGroups = FALSE;
      $SideMenu->HighlightRoute($CurrentUrl);

      if($this->Page->ParentPageID != -1) {
         $Pages = $this->PageModel->Get(array('ParentPageID' => $this->Page->ParentPageID, 'Exclude' => -1, 'Status' => 'published'));
         $ParentID = $this->Page->ParentPageID;
      } else {
         $Pages = $this->PageModel->Get(array('ParentPageID' => $this->Page->PageID, 'Exclude' => -1, 'Status' => 'published'));
         $ParentID = $this->Page->PageID;
      }
      if(isset($Pages)) {
         if($Pages->NumRows() > 0) {
            $Parent = $this->PageModel->Get(array('PageID' => $ParentID));
            $SideMenu->AddItem($Parent->Name, '<i class="rel Img Sprite Medium CurrentPage"></i>' . $Parent->Name, FALSE, array('class' => 'PageSideMenu mtm'));

            foreach($Pages->Result() as $Child) {
               //echo $Child->UrlCode;
               if ($Child->UrlCode == $CurrentUrl) {
                  $SideMenu->AddLink($Parent->Name, $Child->Name, $Child->UrlCode,FALSE, array('class' => 'bold SimpleButton'));
               }
               else {
                  $SideMenu->AddLink($Parent->Name, $Child->Name, $Child->UrlCode, FALSE, array('class' => 'SimpleButton'));
               }
            }
            $this->EventArguments['SideMenu'] = &$SideMenu;
           // $this->FireEvent('AfterAddSideMenu');
            if (IsMobile()) {
               $this->AddModule($SideMenu, 'SubMenu');
            }
            else {
               $this->AddModule($SideMenu, 'Panel');
            }

         }
      }

   }
}