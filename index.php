<?php
$imagePlugin=true;
$ext=array("jpg","jpeg","png","gif","bmp","ico");
$exifImageType = array(1 => "image/gif",2 => "image/jpeg",3 => "image/png",6 => "image/bmp",17 => "image/ico");
$quickStartFolder="/EditDoc";
$quickStartFilename="EditDoc";


//BELOW THIS LINE NO VAR CHANGES!!
//--------------------------------
$appName="editdoc";

//Check for valid login
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled($appName);

//determine user language
$lang=OC_Preferences::getValue( OC_User::getUser(), 'core', 'lang', OC_L10N::findLanguage() );
//$lang="en_GB"; //force
$l=OC_L10N::get($appName);
$appPath="apps/".$appName;


//QUICK START
//-----------
if (empty($_GET)) {
 if (!\OC\Files\Filesystem::file_exists($quickStartFolder)){\OC\Files\Filesystem::mkdir($quickStartFolder);}
 echo '<script type="text/javascript">';
 echo 'var now=new Date(),h=now.getHours(),m=now.getMinutes(),s=now.getSeconds();';
 echo 'if(m<10) m="0"+m;if(s<10) s="0"+s;';
 echo 'var filename="'.$quickStartFilename.' - " + h + "'.$l->t('Hour Symbol').'" + m + "." + s + ".html";';
 echo 'window.location.href = "?filename=" + filename + "&dir='.$quickStartFolder.'";';
 echo '</script>';
}



//SHOW IMAGE OR THUMBNAIL
//-----------------------
//vars: 
//?image=[path to image file]@[username]
//&thumbnail (show thumbnail. Optional)
//&difo (disable Image Fix Orientation. Optional)
if (isset($_GET['image']) && !empty($_GET['image'])){
 //default thumbnail size
 $tH=122;$tW=91;
 if (isset($_GET['thumbnail'])) {$resize=true;}else{$resize=false;}

 //get custom image size
 if (isset($_GET['size']) && !empty($_GET['size']) && strrpos($_GET['size'],"x")==true){
  $s=explode("x",$_GET['size']);
  $tW=$s[0];
  $tH=$s[1];
  $resize=true;
 }

 //get image properties
 $file=$_GET['image'];
 $datadir=OC_Config::getValue('datadirectory');
 $realfile=\OC\Files\Filesystem::getLocalFile($file);
 $image=$realfile;

 $mimetype=$exifImageType[exif_imagetype($realfile)];
 if (\OC\Files\Filesystem::file_exists($file) && !empty($mimetype)) {

  $img = new Imagick();
  $img->readImage($image);

  //fix image orientation
  $o=exif_read_data($image)['Orientation'];
  if ($o>0 && !isset($_GET['difo'])) {
   $rotate=0;
   if ($o==3) $rotate=180;
   if ($o==6) $rotate=90;
   if ($o==8) $rotate=270;
   $img->rotateImage(new ImagickPixel(), $rotate);
  }

  //resize image
  if ($resize) {
   $info=getimagesize($realfile);
   if ($info[0]>$tW && $info[1]>$tH){
    $img->scaleImage($tW,0);
    if ($img->getImageGeometry()['height']> $tH) {$img->scaleImage(0,$tH);}
   }
  }

  $image=$img->getImage();

  //stream image
  header('Content-Type: '.$mimetype);
  echo $image;
 }
}



//SAVE FILE
//---------
if (isset($_POST['action']) && $_POST['action']=="savefile" && isset($_POST['mce_data'])) {
 $filepath=$_POST['dir']."/".$_POST['filename'];

 if(\OC\Files\Filesystem::isUpdatable($filepath)) {
  \OC\Files\Filesystem::file_put_contents($filepath,$_POST['mce_data']);
 }
}


//BROWSE IMAGE
//------------
if (isset($_GET['editor']) && !empty($_GET['editor'])){
 $user=OCP\User::getUser();
 $userfolder=\OC_User::getHome($user)."/files/";

 if (isset($_GET['dir']) && !empty($_GET['dir'])) {$subdir=$_GET['dir'];}else{$subdir='';}
 echo '<!DOCTYPE html>';
 echo '<html>';
 echo '<head>';
 echo '<link href="/'.$appPath.'/css/editdoc.css" rel="stylesheet" type="text/css">';

 echo '<!--[if lt IE 8]><style>';
 echo '.img-container span {';
 echo 'display: inline-block;';
 echo 'height: 100%;';
 echo '}';
 echo '</style><![endif]-->';
 echo '</head>';

 //header-start
 echo '<div id="navigator">';
 echo '<script type="text/javascript" src="/'.$appPath.'/js/jquery.min.js"></script>';

 echo '<script type="text/javascript">';

 echo 'function apply_img(file){';
 echo ' var window_parent=window.parent;';
 echo ' var track = $(\'#track\').val();';
 echo ' var target = window_parent.document.getElementsByClassName(\'mce-img_\'+track);';
 echo ' var closed = window_parent.document.getElementsByClassName(\'mce-filemanager\');';
 echo ' $(target).val(\'?image=\'+file);';
 echo ' $(closed).find(\'.mce-close\').trigger(\'click\');';
 echo '}';
 echo '</script>';

 echo '<input type="hidden" id="track" value="'.$_GET['editor'].'">';

 //navigator bar - start
 echo '<table border="0" style="background:#eee;">';
 echo '<tr><td width="10px"></td><td height="25px">';
 $link="?editor=".$_GET['editor']."&dir=";
 echo '<li><a href="'.$link.'" title="'.$l->t('Home').'"><img src="/'.$appPath.'/img/homebutton.png"></a></li><li>/</li>';
 $bc=explode('/',$subdir);
 $tmp_path='';
 if(!empty($bc))
 foreach($bc as $k=>$b){
  $tmp_path.=$b."/";
  if($k==count($bc)-1){
   echo '<li class="active">'.$b.'</li>';
  }elseif($b!=""){
   echo '<li><a href="'.$link.$tmp_path.'">'.$b.'</a></li><li>/</li>';
  }
 }
 echo '</td>';
 //refresh button
 echo '<td align="right"><a href="?editor='.$_GET['editor'].'&dir='.urlencode($subdir).'" id="refresh" title="'.$l->t('Refresh').'"><img src="/'.$appPath.'/img/refreshbutton.png"></a></td>';
 echo '<td width="20px"></td></tr></table>';
 echo '</div>'; //navigator
 //navigator bar - end

 //header - end

 //create thumbnails
 echo '<ul class="grid cs-style-2">';

 //create up-folder icon
 if ($subdir!="") {
  $src=substr($subdir, 0, strrpos( $subdir, '/'));
  echo '<li>';
  echo '<figure>';
  echo '<a title="'.$l->t('Back').'" href="?editor='.$_GET['editor'].'&dir='.$src.'">';
  echo '<div class="img-precontainer">';
  echo '<div class="img-container directory"><span></span>';
  echo '<img class="directory-img" src="/'.$appPath.'/img/folder_return.png" alt="folder">';
  echo '</div>';
  echo '</div>';
  echo '</a>';
  echo '</figure>';
  echo '</li>';
 }

 $data = array();
 $sortAttribute = 'name';
 $sortDirection = false;
 $dirInfo = \OC\Files\Filesystem::getFileInfo($subdir);
 $permissions = $dirInfo->getPermissions();

 $files = \OCA\Files\Helper::getFiles($subdir, $sortAttribute, $sortDirection);
 $data['files'] = \OCA\Files\Helper::formatFileInfos($files);

 foreach ($data['files'] as $o) {
  $type=$o['type'];
  $file=$o['name'];

  //show folders
  if ($type=="dir") {
   echo '<li>';
   echo '<figure>';
   echo '<a title="'.$l->t('Open').'" href="?editor='.$_GET['editor'].'&dir='.urlencode($subdir."/".$file).'">';
   echo '<div class="img-precontainer">';
   echo '<div class="img-container directory"><span></span>';
   echo '<img class="directory-img" src="/'.$appPath.'/img/folder.png" alt="folder">';
   echo '</div>';
   echo '</div>';
   echo '</a>';
   echo '<div class="box">';
   echo '<h4>'.$file.'</h4>';
   echo '</div>';
   echo '</figure>';
   echo '</li>';
  }
  //show files
  $file_ext = strtolower(substr(strrchr($file,'.'),1));
  if(in_array($file_ext, $ext) && $type=="file"){
   $thumbnail="/index.php/".$appPath."/?image=".urlencode($subdir."/".$file)."&thumbnail";
   echo '<li>';
   echo '<figure>';
   echo '<a href="javascript:void(\'\');" title="'.$l->t('Select').'" onclick="apply_img(\''.urlencode($subdir."/".$file).'\');">';
   echo '<div class="img-precontainer">';
   echo '<div class="img-container"><span></span>';
   echo '<img alt="'.$file.'" src="'.$thumbnail.'">';
   echo '</div>';
   echo '</div>';
   echo '</a>';
   echo '<div class="box">';
   echo '<h4>'.$file.'</h4>';
   echo '</div>';
   echo '</figure>';
   echo '</li>';
  }
 }
 echo '</ul>'; //grid cs-style-2
 echo '</html>';
}


//LOAD TINYMCE EDITOR
//-----------------------------
if (isset($_GET['filename']) && !empty($_GET['filename'])) {
 //Get vars
 if (isset($_GET['dir']) && !empty($_GET['dir'])) {$dir=$_GET['dir'];}else{$dir='/';}
 $filename=$_GET['filename'];
 $filepath=$dir."/".$filename;

 //Create new file
 if (!\OC\Files\Filesystem::file_exists($filepath)){
  \OC\Files\Filesystem::touch($filepath);
 }

 //Check readonly
 if (\OC\Files\Filesystem::isUpdatable($filepath)==0){$readonly=true;}else{$readonly=false;}

 //Check load image plugin
 if ($imagePlugin) {$image="image";}else{$image="";}

 echo '<script type="text/javascript" src="'.OCP\Util::linkTo($appPath.'/tinymce4/','tinymce.min.js').'"></script>';
 echo '<script type="text/javascript">';
 echo 'tinymce.init({';
 echo ' selector: "textarea",';
 echo ' statusbar: false,';
 echo ' document_base_url: "/index.php/'.$appPath.'/",';
 echo ' relative_urls: true,';
 echo ' readonly: "'.$readonly.'",';
 echo ' language: "'.$lang.'",';
 echo ' selectImageTitle: "'.$l->t('Browse and select an image').'",';
 echo ' selectButtonText: "'.$l->t('Select Image').'",';
 echo ' keepAspectRatioText: "'.$l->t('Keep Aspect Ratio').'",';
 echo ' dir: "'.$dir.'",';
 echo ' plugins: ["advlist autolink lists link charmap print preview anchor pagebreak hr code searchreplace directionality visualblocks insertdatetime table contextmenu paste textcolor emoticons autosave '.$image.'"],';
 echo ' toolbar: "undo redo | print preview | styleselect | fontselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor emoticons '.$image.'",';
 echo '});';
 echo '</script>';

 //Close Button
 echo '<input type="image" title="'.$l->t('Close').'" onclick="if (parent.location.href==self.location.href) {window.location.href=\'/index.php/apps/files?dir='.$dir.'\';}else{parent.location.reload();}" align="right" src="/'.$appPath.'/img/close.png"/>';

 //Save Button
 if ($readonly){
  echo '<center><b>'.$filename.' (readonly)';
 }
 else
 {
  echo '<input type="image" title="'.$l->t('Save').'" onclick="document.getElementById(\'save\').submit()" align="left" src="/'.$appPath.'/img/save.png"/>';
  echo '<center><b>'.$filename;
 }

 //Refresh Button
 echo '<input type="image" title="'.$l->t('Refresh').'" align="top" onclick="document.getElementById(\'refresh\').submit()" src="/'.$appPath.'/img/refresh.png"/></center>';

 //Form Refresh 
 echo '<form id="refresh" action="'.$_SERVER['PHP_SELF'].'" method="get">';
 echo '<input type="hidden" name="filename" value="'.$filename.'">';
 echo '<input type="hidden" name="dir" value="'.$dir.'">';
 echo '</form>';

 //Form Save and load file
 echo '<form id="save" method="post">';
 echo '<input type="hidden" name="action" value="savefile">';
 echo '<input type="hidden" name="filename" value="'.$filename.'">';
 echo '<input type="hidden" name="dir" value="'.$dir.'">';
 echo '<textarea name="mce_data" style="width:100%;height:80%">'.\OC\Files\Filesystem::file_get_contents($filepath).'</textarea>';
 echo '</form>';
}

?>
