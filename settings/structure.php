<?php if (!defined('APPLICATION')) exit();

// Use this file to construct tables and views necessary for your application.
// There are some examples below to get you started.

if (!isset($Drop))
   $Drop = FALSE;
   
if (!isset($Explicit))
   $Explicit = TRUE;

$SQL = $Database->SQL();
$Construct = $Database->Structure();

$Construct->Table('Page')
	->PrimaryKey('PageID')
   ->Column('Name', 'varchar(100)', FALSE, 'fulltext')
	->Column('UrlCode', 'varchar(50)')
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
	//->Column('Route', 'tinyint(1)', '0')
	//->Column('Share', 'tinyint(1)', '0')
	//->Column('IsParentOnly', 'tinyint(1)', '0')
	->Column('RouteIndex', 'varchar(30)', NULL)
	//->Column('DiscussionID', 'int', '0', 'key')
	//->Column('Quote', 'varchar(300)', TRUE)
	//->Column('Modules', 'varchar(400)', 'a:0:{}')
	//->Column('YoutubeID', 'varchar(50)', NULL)
	->Column('Template', 'varchar(100)', 'default')
	//->Column('CustomCss', 'varchar(500)', NULL)
	->Column('Body', 'text', TRUE, 'fulltext')
	->Column('Format', 'varchar(20)', TRUE)
	->Column('Sort', 'int', TRUE)
	->Engine('MyISAM')
	->Set($Explicit, $Drop);
	
$Construct->Table('PageMeta')
	->PrimaryKey('PageMetaID')
   ->Column('PageID', 'int', FALSE, 'key')
	->Column('MetaKey', 'varchar(255)', NULL)
	->Column('MetaKeyName', 'varchar(255)', NULL)
	->Column('MetaValue', 'text', NULL)
	->Column('MetaAsset', 'varchar(255)', NULL)
	->Column('MetaAssetName', 'varchar(255)', NULL)
	->Engine('MyISAM')
	->Set($Explicit, $Drop);
	
// Add PageID column to discussion table for allowing discussions on pages.
$Construct->Table('Discussion')
   ->Column('PageID', 'int', NULL)
   ->Column('News', 'tinyint(1)', '0')
   ->Set();
	
/*
$Construct->Table('Media')
	->PrimaryKey('MediaID')
	->Column('Name', 'varchar(255)')
	->Column('Type', 'varchar(50)')
	->Column('Description', 'varchar(255)')
	->Column('InsertUserID', 'int', NULL, 'key')
	->Column('DateInserted', 'datetime', NULL)
	->Set($Explicit, $Drop);*/

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
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'NewPage', 'FullHeadline' => T('%1$s added a %8$s.'), 'ProfileHeadline' => T('%1$s added a %8$s.'), 'RouteCode' => 'page', 'Public' => '1'));

if ($SQL->GetWhere('ActivityType', array('Name' => 'EditPage'))->NumRows() == 0)
   $SQL->Insert('ActivityType', array('AllowComments' => '1', 'Name' => 'EditPage', 'FullHeadline' => '%1$s edited a %8$s.', 'ProfileHeadline' => '%1$s edited a %8$s.', 'RouteCode' => 'page', 'Public' => '1'));

$PermissionModel = Gdn::PermissionModel();
$PermissionModel->Database = $Database;
$PermissionModel->SQL = $SQL;

// Define some global VanillaCMS permissions.
$PermissionModel->Define(array(
	'VanillaCMS.Pages.Manage',
	'VanillaCMS.Internal.View'
));

/*
$PermissionModel->Define(array(
   'VanillaCMS.Page.View' => 1,
   'VanillaCMS.Page.Edit' => 0),
   'tinyint',
   'Page',
   'PageID'
   );*/

//$SQL->Update('User', array('Permissions' => ''))->Put();   

// Set the initial guest permissions.
/*
$PermissionModel->Save(array(
   'Role' => 'Guest',
   'JunctionTable' => 'Page',
   'JunctionColumn' => 'PageID',
   'VanillaCMS.Page.Edit' => 1
   ), TRUE);

$PermissionModel->Save(array(
   'Role' => 'Confirm Email',
   'JunctionTable' => 'Page',
   'JunctionColumn' => 'PageID',
   'VanillaCMS.Page.Edit' => 1
   ), TRUE);

$PermissionModel->Save(array(
   'Role' => 'Applicant',
   'JunctionTable' => 'Page',
   'JunctionColumn' => 'PageID',
   'VanillaCMS.Page.Edit' => 1
   ), TRUE);

// Set the intial member permissions.
$PermissionModel->Save(array(
   'Role' => 'Member',
   'JunctionTable' => 'Page',
   'JunctionColumn' => 'PageID',
   'Vanilla.Discussions.Add' => 1,
   'VanillaCMS.Page.Edit' => 1,
   'Vanilla.Comments.Add' => 1
   ), TRUE);
   
// Set the initial moderator permissions.
$PermissionModel->Save(array(
   'Role' => 'Moderator',
   'Vanilla.Spam.Manage' => 1,
   ), TRUE);
   
// Set the initial administrator permissions.
$PermissionModel->Save(array(
   'Role' => 'Administrator',
   'Vanilla.Settings.Manage' => 1,
   'Vanilla.Categories.Manage' => 1,
   'VanillaCMS.Page.Edit' => 1,
   ), TRUE);

/*
$PermissionModel->Save(array(
   'Role' => 'Administrator',
   'JunctionTable' => 'Category',
   'JunctionColumn' => 'PageID',
   'JunctionID' => $GeneralCategoryID,
   'Vanilla.Discussions.Add' => 1,
   'Vanilla.Discussions.Edit' => 1,
   'Vanilla.Discussions.Announce' => 1,
   'Vanilla.Discussions.Sink' => 1,
   'Vanilla.Discussions.Close' => 1,
   'Vanilla.Discussions.Delete' => 1,
   'VanillaCMS.Page.Edit' => 1,
   'Vanilla.Comments.Add' => 1,
   'Vanilla.Comments.Edit' => 1,
   'Vanilla.Comments.Delete' => 1
   ), TRUE);*/
