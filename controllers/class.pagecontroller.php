<?php if (!defined('APPLICATION')) exit();


class PageController extends VanillaCMSController {

   public $Uses = array('Form', 'PageModel', 'DiscussionModel', 'CommentModel');

   public function Initialize() {
      parent::Initialize();
      //$this->MasterView = 'fullwidth';
      $this->ControllerName = 'pagecontroller';
      $this->CssClass = 'Page';
   }

   public function Index($UrlCode = '', $ChildUrlCode = '')
   {
      if(isset($UrlCode)) {

         $this->Page = $this->PageModel->GetPublishedByUrlCode(urldecode($UrlCode));

         if(isset($this->Page)){   
            
            $this->AddCssFile('page.css');

            if($this->Page->IsParentOnly == 1) {
               if ($ChildUrlCode)
                  $this->Page = $this->PageModel->GetPublishedByUrlCode(urldecode($UrlCode . '/' . $ChildUrlCode));
               else
                  $this->Page = $this->PageModel->GetPublishedChildren($this->Page->PageID)->FirstRow();

               if (!$this->Page)
                  Redirect('dashboard/home/filenotfound');
            } 

            if ($this->Page->ParentPageID) {
               $Parent = $this->PageModel->GetByID($this->Page->ParentPageID);
               $this->Menu->HighlightRoute('/' . $Parent->UrlCode);
            } else {
               $this->Menu->HighlightRoute('/' . $this->Page->UrlCode);
            }

            if ($this->Head) {
               if ($this->Page->Name == 'Start') {
                  $this->Head->Title(T('Welcome to Jesus People!'));
               }
               else
                  $this->Head->Title(C('Garden.Title') . ' - ' . $this->Page->Name);

               if (isset($this->Page->Description)) {
                  $this->Head->AddTag('meta', array('name' => 'description', 'content'=>$this->Page->Description));
               }
            }

            if ($this->Page->Template == 'start') {
               $this->AddCssFile('font_blackjack.css');
               $this->AddCssFile('http://fonts.googleapis.com/css?family=Covered+By+Your+Grace:regular');
               
               $this->AddCssFile('font_chunkfive.css');
            }

            //Add sidemenu IF there is subpages (has to be before modules for correct sorting)
            $this->AddSideMenu($this->Page->UrlCode);

            $this->Page->PageMeta = $this->PageModel->GetPageMeta($this->Page->PageID);
            //print_r($this->Page->PageMeta->Result());



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
               if($this->Page->Route)
                  $Url = C('Garden.Domain') . '/' . $this->Page->UrlCode;
               else
                  $Url = C('Garden.Domain') . '/' . T('page') . '/' . $this->Page->UrlCode;

               $ShareModule->FacebookUrl = $Url;
               $this->AddModule($ShareModule);
            }

            //Discussion

            if ($this->Page->AllowDiscussion == 1) {
               $this->AddJSFile('plugins/Voting/voting.js');
            }

            $DiscussionModel = new DiscussionModel();
            $DiscussionModel->PageID = $this->Page->PageID; // Let the model know we want to filter to a particular addon (we then hook into the model in the addons hooks file).
            $this->DiscussionData = $DiscussionModel->Get(0, 50);

            $this->MasterView = $this->Page->Template;            

            $this->Render();	
         }
         else {
            Redirect('dashboard/home/filenotfound');
         }
           
      }
      else {
         Redirect('dashboard/home/filenotfound');
      }
         
   }

   public function AddSideMenu($CurrentUrl = '') {
      $SideMenu = new SideMenuModule($this);
      $SideMenu->HtmlId = 'PageSideMenu';
      $SideMenu->CssClass = 'mbl';
      $SideMenu->AutoLinkGroups = FALSE;
      $SideMenu->HighlightRoute($CurrentUrl);

      if($this->Page->IsParentOnly) {
         $Pages = $this->PageModel->GetPublishedChildren($this->Page->PageID);
         $ParentID = $this->Page->PageID;
      } 
      elseif($this->Page->ParentPageID) {
         $Pages = $this->PageModel->GetPublishedChildren($this->Page->ParentPageID);
         $ParentID = $this->Page->ParentPageID;
      }
      if(isset($Pages)) {
         if($Pages->NumRows() > 1) {
            $Parent = $this->PageModel->GetByID($ParentID);
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