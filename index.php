<?php
if (!isset($_GET['filename'])) {
 header('location: /');
 die;
}

//Check for valid login
OC_Util::checkLoggedIn();
OC_Util::checkAppEnabled('editdoc');

//Get vars
$dir=$_GET['dir'];
$filename=$_GET['filename'];
$filepath=$dir."/".$filename;

//Create new file
if (!\OC\Files\Filesystem::file_exists($filepath)){
 \OC\Files\Filesystem::touch($filepath);
}

$readonly="false";
if (\OC\Files\Filesystem::isUpdatable($filepath)==0) {
 $readonly="true";
};
?>

<script type="text/javascript" src="<?php echo OCP\Util::linkTo('editdoc','js/vendor/tinymce4/tinymce.min.js');?>"></script>
<script type="text/javascript">
 tinymce.baseURL = "<?php echo \OCP\Util::linkTo('editdoc', 'js/vendor/tinymce4') ?>";
 tinymce.cssbaseURL = "<?php echo \OC::$server->getURLGenerator()->linkTo('editdoc','css/vendor/tinymce4') ?>";
 tinymce.init({
  selector: "textarea",
  statusbar: false,
  readonly:<?php echo $readonly?>,
  plugins: ["advlist autolink lists link charmap print preview anchor pagebreak hr code searchreplace directionality visualblocks insertdatetime table contextmenu paste emoticons textcolor autosave fullpage"],
  toolbar: "undo redo | print preview | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor emoticons code",
 });
</script>

<!--Close Button -->
<input type="image" title="Close" value="Close" onclick="parent.location.reload()" align="right" src="<?php echo OCP\Util::imagePath('editdoc','close.png')?>"/>

<!-- Save Button -->
<?php
 if (\OC\Files\Filesystem::isUpdatable($filepath)){
  echo '<input type="image" title="Save" onclick="document.getElementById(\'save\').submit()" align="left" src="'.OCP\Util::imagePath('editdoc','save.png').'"/>';
  echo '<center><b>'.$filename;
 }
 else
 {
  echo '<center><b>'.$filename.' (readonly)';
 }
?>

<!-- Refresh Button -->
<input type="image" title="Refresh" align="top" onclick="document.getElementById('refresh').submit()" src="<?php echo OCP\Util::imagePath('editdoc','refresh.png')?>"/></center>

<!--FORM -->
<form id="refresh" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
 <input type="hidden" name="filename" value="<?php echo $filename?>">
 <input type="hidden" name="dir" value="<?php echo $dir?>">
</form>

<!-- Filename and Save button -->
<form id="save" action="<?php include '_savefile.php'?>" method="post">
 <input type="hidden" name="filename" value="<?php echo $filename?>">
 <input type="hidden" name="dir" value="<?php echo $dir?>">

 <!--Load filename -->
 <textarea name="mce_data" style="width:100%;height:80%"><?php echo \OC\Files\Filesystem::file_get_contents($filepath)?></textarea>
</form>



