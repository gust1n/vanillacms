<?php if (!defined('APPLICATION')) exit();

echo $this->Form->Open(array('class' => 'mrla mtl pas'));
echo $this->Form->Errors();

echo '<h3 class="textc">' . T('Send an email!') . '</h3>';
	echo '<ul><li class="pbl">';
	echo $this->Form->Label(T('Your Name'), 'Name');
	echo $this->Form->TextBox('Name', array('class' => 'InputBox EmailWidth w90'));
	echo '</li><li class="pbl">';
	echo $this->Form->Label(T('Your Email'), 'Email');
	echo $this->Form->TextBox('Email', array('class' => 'InputBox EmailWidth w90'));
	echo '</li><li>';
	echo $this->Form->Label(T('Message'), 'Body');
	echo $this->Form->TextBox('Body', array('MultiLine' => TRUE, 'class' => 'EmailWidth mbs w90'));
   echo '</li><li>';

echo $this->Form->Close(T('Send!'));
?>
</li></ul>