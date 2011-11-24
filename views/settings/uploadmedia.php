<?php if (!defined('APPLICATION')) exit(); ?>
<h2><?php echo T('Upload Media'); ?></h2>
<?php
echo $this->Form->Open(array('enctype' => 'multipart/form-data'));
echo $this->Form->Errors();
?>
<ul>
   <li>
      <p><?php echo T('Select an image on your computer (2mb max)'); ?></p>
      <?php echo $this->Form->Input('Picture', 'file'); ?>
   </li>
<li>
      
      <?php 
		echo $this->Form->Label('Short Description', 'Description');
		echo $this->Form->TextBox('Description', array('Multiline' => true)); ?>
   </li>
</ul>
<?php echo $this->Form->Close('Upload');