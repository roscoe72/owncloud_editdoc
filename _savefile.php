<?php
//Check for valid login
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('editdoc');

if(isset($_POST['mce_data'])){
 $filepath=$_POST['dir']."/".$_POST['filename'];

 if(\OC\Files\Filesystem::isUpdatable($filepath)) {
  \OC\Files\Filesystem::file_put_contents($filepath,$_POST['mce_data']);
 }
}

?>
