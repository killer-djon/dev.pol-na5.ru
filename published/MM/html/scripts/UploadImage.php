<?php
	session_start();
	require_once '../../../common/html/scripts/tmp_functions.php';

	$limitedext = array(".gif",".jpg",".png",".jpeg");
	$errorStr = '';

	$fileName = '';
	if(isset($_FILES['UploadImageFile']))
	{
		$file = $_FILES['UploadImageFile'];
		$size = getUploadedFilesSize();

		$ext = strrchr($file['name'], '.');
		if (!in_array($ext, $limitedext))
			$errorStr = "The file doesn't have correct extension.";
		elseif ($size + $file['size'] > $_size_limit)
			$errorStr = 'The file is to big.';
		else {
			$file['body'] = file_get_contents($file['tmp_name']);
			addUploadedFile($file, 'images');
		}
		$fileName = base64_encode($file['name']);
	}
	if($errorStr) {
?>
<script type="text/javascript">
	function i18n(str) { return (parent.Xinha._lc(str, 'Xinha')) }

	parent.document.getElementById('UploadImageInput').style.display = '';
	parent.document.getElementById('UploadImageInfo').innerHTML =
	  "<span class=\"ErrorStr\">" + i18n("<?php echo $errorStr ?>") + "</span>";
</script>
<?php
	} else {
?>
<script type="text/javascript">
	var url = '/common/html/scripts/preview.php?file=<?=$fileName?>';
	var img = new Image();
	img.src = url;
	img.onLoad = imgWait();

	function imgWait() { waiting = window.setInterval('imgIsLoaded()', 500) }
	function imgIsLoaded() {
		if(img.width > 0) {
			window.clearInterval(waiting)
			parent.document.body.removeChild(parent.document.body.lastChild);
			parent._editor.insertHTML('<img height="'+img.height+'" width="'+img.width+'" src="'+url+'">');
		}
	}
</script>
<?php
	}
?>