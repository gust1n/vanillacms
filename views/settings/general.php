<?php if (!defined('APPLICATION')) exit();
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<style type="text/css" media="screen">
	.drop {width:200px;height:44px;border:2px solid green;font-size:25px;font-weight:bold;text-align:center;}
	.red {background:#DD0000;border:2px solid black;}
	.green {background:#0E0;border:2px solid green;}
</style>
<h1><?php echo T('Chat Settings'); ?></h1>
<ul>
	<li>
		<p class="Warning"><?php echo T("<strong>Heads Up!</strong> Changing this turns off or on the entire chat."); ?></p>
	</li>
   <li>
      <?php
		$var = C('Jpchat.MasterEnabled');
         if($var == '0')
			$class = "red drop";
		else
			$class = "green drop";
         $Options = array(FALSE => 'Chat Offline', TRUE => 'Chat Online');
		$Fields = array('TextField' => 'Code', 'ValueField' => 'Code', 'class' => $class);
                  echo $this->Form->Label('Chat Status', 'Jpchat.MasterEnabled');
                  echo $this->Form->DropDown('Jpchat.MasterEnabled', $Options, $Fields);
         
      ?>
</ul>
<?php echo $this->Form->Close('Save');