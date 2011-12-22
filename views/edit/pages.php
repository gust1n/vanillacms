<?php if (!defined('APPLICATION')) exit();
$Session = Gdn::Session(); ?>

<h1><?php echo T('Pages');?></h1>

<div class="Info">
   <?php echo T('Easily add and edit your pages here!'); ?>
</div>
<div class="FilterMenu">
	<?php echo Anchor(T('Add Page'), 'edit/add/page', 'Button'); ?>
</div>

<div class="Tabs FilterTabs">
   <ul>
      <li class="All<?php echo $this->Filter == 'all' ? ' Active' : ''; ?>"><?php echo Anchor(T('All '.Wrap($this->UnpublishedCount + $this->PublishedCount)), 'edit/pages/all'); ?></li>
      <li class="Published<?php echo $this->Filter == 'published' ? ' Active' : ''; ?>"><?php echo Anchor(T('Published '.Wrap($this->PublishedCount)), 'edit/pages/published'); ?></li>
      <li class="Drafts<?php echo $this->Filter == 'draft' ? ' Active' : ''; ?>"><?php echo Anchor(T('Drafts '.Wrap($this->UnpublishedCount)), 'edit/pages/draft'); ?></li>
      <li class="Trash<?php echo $this->Filter == 'trash' ? ' Active' : ''; ?>"><?php echo Anchor(T('Trash '.Wrap($this->TrashCount)), 'edit/pages/trash', array('class' => 'TrashTab')); ?></li>
   </ul>
</div>
<?php 
echo $this->Form->Errors(); 
$ViewLocation = $this->FetchViewLocation($this->Filter);
include($ViewLocation);
?>