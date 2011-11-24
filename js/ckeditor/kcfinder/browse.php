<?php

/** This file is part of KCFinder project
  *
  *      @desc Browser calling script
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */

//include "../../../library/core/class.session.php";

//echo $_SESSION['admin'];
$Go = true;
if (isset($_COOKIE['admin'])) {
   if ($_COOKIE["admin"] == 1) {
      $Go = false;
   }
}

//print_r($_COOKIE);
//print_r($_COOKIE['Vanilla']);


require "core/autoload.php";
$browser = new browser();
$browser->action($Go);
$_CONFIG['disabled'] = false;
?>