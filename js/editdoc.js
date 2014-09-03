$(document).ready(function() {

 if (typeof FileActions !== 'undefined') {
  OCA.Files.fileActions.register('text/html','EditDoc',OC.PERMISSION_READ,function() {return OC.imagePath('core','actions/play');}, function(filename) {startEditDoc($('#dir').val(),filename);})
 }

});

function startEditDoc(dir,filename){
 //Start Editor
 $("#editor").hide();
 $('#content table').hide();
 $("#controls").hide();
 var editor = OC.linkTo('editdoc', 'index.php')+'?dir='+encodeURIComponent(dir)+'&filename='+encodeURIComponent(filename);
 $("#content").html('<iframe style="width:100%;height:95%;" src="'+editor+'" />');
}

