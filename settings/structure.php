<?php if (!defined('APPLICATION')) exit();

// Use this file to construct tables and views necessary for your application.
// There are some examples below to get you started.

if (!isset($Drop))
   $Drop = FALSE;
   
if (!isset($Explicit))
   $Explicit = TRUE;
   
$SQL = Gdn::Database()->SQL();
$Construct = Gdn::Database()->Structure();

$Construct->Table('Page');
$PageTableExists = $Construct->TableExists();

$Construct->PrimaryKey('PageID')
   ->Column('Name', 'varchar(150)', FALSE, 'fulltext')
	->Column('UrlCode', 'varchar(150)')
	->Column('Status', 'varchar(20)', 'published')
	->Column('Type', 'varchar(20)', 'page')
	->Column('InsertUserID', 'int', FALSE, 'key')
	->Column('UpdateUserID', 'int', TRUE)
	->Column('DateInserted', 'datetime')
	->Column('DateUpdated', 'datetime', TRUE)
	->Column('ParentPageID', 'int', TRUE, 'key')
	->Column('TreeLeft', 'int', TRUE)
   ->Column('TreeRight', 'int', TRUE)
   ->Column('Depth', 'int', TRUE)
	->Column('InMenu', 'tinyint(1)', '0')
	->Column('AllowDiscussion', 'tinyint(1)', '0')
	->Column('RouteIndex', 'varchar(150)', NULL)
	->Column('Template', 'varchar(100)', 'default')
	->Column('Body', 'text', TRUE, 'fulltext')
	->Column('Format', 'varchar(20)', TRUE)
	->Column('Sort', 'int', TRUE)
	->Engine('MyISAM')
	->Set($Explicit, $Drop);
	
if ($SQL->GetWhere('Page', array('PageID' => -1))->NumRows() == 0) {
   $SQL->Insert('Page', array('PageID' => -1, 'TreeLeft' => 1, 'TreeRight' => 8, 'Depth' => 0, 'InsertUserID' => 1, 'UpdateUserID' => 1, 'DateInserted' => Gdn_Format::ToDateTime(), 'Name' => 'Root', 'UrlCode' => '', 'Body' => 'Root of category tree. Users should never see this.'));
}
if ($Drop || !$PageTableExists) {
   if (!class_exists('PageModel')) {
      include(PATH_APPLICATIONS . DS . 'vanillacms' . DS . 'models' . DS . 'class.pagemodel.php');
   }
   $PageModel = new PageModel();
   
   $SQL->Insert('Page', array('PageID' => 1, 'TreeLeft' => 2, 'TreeRight' => 7, 'Depth' => 1, 'InsertUserID' => 1, 'UpdateUserID' => 1, 'DateInserted' => Gdn_Format::ToDateTime(), 'Name' => 'Example Page', 'UrlCode' => 'example-page', 'InMenu' => 1, 'ParentPageID' => -1, 'Body' => '<strong>Hey there, World!</strong><p>This is your first page, enter the dashboard to edit or add pages!</p>'));
   
   $PageModel->SetRoute(1);
   
   $SQL->Insert('Page', array('PageID' => 2, 'TreeLeft' => 3, 'TreeRight' => 4, 'Depth' => 2, 'InsertUserID' => 1, 'UpdateUserID' => 1, 'DateInserted' => Gdn_Format::ToDateTime(), 'Name' => 'Discussions', 'UrlCode' => 'discussions', 'InMenu' => 1, 'ParentPageID' => 1, 'Template' => 'discussions', 'Body' => 'This is the example page of the Discussion template. This text should not be visible... Anywhere! (Unless you change the page template)'));
   
   $SQL->Insert('Page', array('PageID' => 3, 'TreeLeft' => 5, 'TreeRight' => 6, 'Depth' => 2, 'InsertUserID' => 1, 'UpdateUserID' => 1, 'DateInserted' => Gdn_Format::ToDateTime(), 'Name' => 'Inbox', 'UrlCode' => 'messages/all', 'InMenu' => 1, 'ParentPageID' => 1, 'Template' => 'messages/all', 'Body' => 'This is the example page of the Inbox template. This text should not be visible... Anywhere! (Unless you change the page template)'));
}


	
$Construct->Table('PageMeta')
	->PrimaryKey('PageMetaID')
   ->Column('PageID', 'int', FALSE, 'key')
	->Column('MetaKey', 'varchar(255)', NULL)
	->Column('MetaKeyName', 'varchar(255)', NULL)
	->Column('MetaValue', 'text', NULL)
	->Column('MetaAsset', 'varchar(255)', NULL)
	->Column('MetaAssetName', 'varchar(255)', NULL)
	->Column('GetData', 'tinyint(1)', '0')
	->Column('ApplicationFolder', 'varchar(255)', 'vanillacms')
	->Column('ConfigSetting', 'varchar(255)', NULL)
	->Engine('MyISAM')
	->Set($Explicit, $Drop);
	
// Add PageID column to discussion table for allowing discussions on pages.
$Construct->Table('Discussion')
   ->Column('PageID', 'int', NULL)
   ->Column('News', 'tinyint(1)', '0')
   ->Set();
	
// Insert some activity types
///  %1 = ActivityName
///  %2 = ActivityName Possessive
///  %3 = RegardingName
///  %4 = RegardingName Possessive
///  %5 = Link to RegardingName's Wall
///  %6 = his/her
///  %7 = he/she
///  %8 = RouteCode & Route

// X added a discussion

if ($SQL->GetWhere('ActivityType', array('Name' => 'NewPage'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'NewPage', 'FullHeadline' => '%1$s added a %8$s.', 'ProfileHeadline' => '%1$s added a %8$s.', 'RouteCode' => 'page', 'Public' => '1'));

if ($SQL->GetWhere('ActivityType', array('Name' => 'EditPage'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'EditPage', 'FullHeadline' => '%1$s edited a %8$s.', 'ProfileHeadline' => '%1$s edited a %8$s.', 'RouteCode' => 'page', 'Public' => '1'));

$PermissionModel = Gdn::PermissionModel();
$PermissionModel->Database = $Database;
$PermissionModel->SQL = $SQL;

// Define some global VanillaCMS permissions.
$PermissionModel->Define(array(
	'VanillaCMS.Pages.Manage'
));

// Set the initial administrator permissions.
$PermissionModel->Save(array(
   'Role' => 'Administrator',
   'VanillaCMS.Pages.Manage' => 1
   ), TRUE);