<?php
function show_form()
  { ?>
  <form id="form" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input name="myfile" type="file"/>
<input type="submit" value="test"/>
  </form>
  <?php }

if ($_SERVER['REQUEST_METHOD']=='POST')
{
  upload();
}
else
{
  show_form();
}
  function upload()
  {

  if( empty($_FILES) )
  show_err(0);
  foreach ( $_FILES as $file )
  {

  if($file["error"] == 0)
  {
  if ( file_exists($file['name']) )
show_err(1);
  copy($file['tmp_name'],dirname(__FILE__). DIRECTORY_SEPARATOR . $file['name']);
  show_form();
  }
 }
}
?>