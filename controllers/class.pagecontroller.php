<?php if (!defined('APPLICATION')) exit();


class PageController extends VanillaCMSController {

   public $Uses = array('Form', 'PageModel', 'DiscussionModel', 'CommentModel');

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

      $this->Page = $this->PageModel->Get(array('UrlCode' => urldecode($UrlCode)));

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

         $this->Page->PageMeta = $this->PageModel->GetPageMeta($this->Page->PageID);
         
         foreach ($this->Page->PageMeta->Result() as $PageMeta) {
            
            if($PageMeta->MetaAsset) {

               /*
               if ($PageMeta->MetaKey == 'CustomHtmlModule') {    
               $CustomHtmlModule = new CustomHtmlModule($this, $PageMeta->MetaValue);
               $this->AddAsset($PageMeta->MetaAsset, $CustomHtmlModule);
               }*/

               $Temp = new $PageMeta->MetaKey($this, $PageMeta->MetaValue);
               $this->AddAsset($PageMeta->MetaAsset, $Temp);
               unset($Temp);
            }

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

      if($this->Page->ParentPageID) {
         $Pages = $this->PageModel->Get(array('ParentPageID' => $this->Page->ParentPageID, 'Exclude' => -1));
         $ParentID = $this->Page->ParentPageID;
      } else {
         $Pages = $this->PageModel->Get(array('ParentPageID' => $this->Page->PageID, 'Exclude' => -1));
         $ParentID = $this->Page->PageID;
      }
      if(isset($Pages)) {
         if($Pages->NumRows() > 1) {
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