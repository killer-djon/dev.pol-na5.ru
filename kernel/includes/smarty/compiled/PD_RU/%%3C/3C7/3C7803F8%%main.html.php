<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:21
         compiled from main.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Photos</title>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.wbs.popup.js"></script>
<?php echo $this->_tpl_vars['head']; ?>


<script>
	$(document).ready(function(){
		$('#link-upload').click(function(){

			if (Helper)
			var album = Helper.getManager().getState().albumId; 
			var url = (album) ? 'backend.php?controller=album&action=uploadForm&albumId='+album : 'backend.php?controller=album&action=uploadForm';

			window.refresh = function(){
				Helper.getManager().setState({});
			}.bind(this);

			window.refreshToUpload = function(uploadCount){
				var page = (Math.ceil( ( parseInt(Data.total) + parseInt(uploadCount) )/Data.limit ) - 1 ) * Data.limit;
				Helper.getManager().setState({
					offset: page
				});				
			}.bind(this);
			
			$('#swfupload').wbsPopup({
				url: url,
				width: 600,
				height: 440,
				iframe: true,
				hidePopup: function(){
					Helper.getManager().setState({});
				},
				callback: {
					test: function (){}
				}
			});
			return false;
		});
		
		$('#album-link').click(function(){
			window.parent.location.hash = '';
			
		});
		
		$('#front_link').click(function(){
			go('<?php echo $this->_tpl_vars['frontend_link']; ?>
');
		});
		
	});

	function go(url) {
		window.open(url);
		/*
		 form = document.createElement ("form"); 
		 form.method = "GET";
		 form.action = url; 
		 form.target = "_blank"; 
		 document.body.appendChild(form); 
		 form.submit();
		 */
	}

	(function($){
	     $.extend({ 
	        // Returns a formated file size
	        filesizeformat: function(bytes, suffixes){
	            var b = parseInt(bytes, 10);
	            var s = suffixes || ['byte', 'bytes', 'KB', 'MB', 'GB'];
	            if (isNaN(b) || b == 0) { return '0 ' + s[0]; }
	            if (b == 1)             { return '1 ' + s[0]; }
	            if (b < 1024)           { return  b.toFixed(2) + ' ' + s[1]; }
	            if (b < 1048576)        { return (b / 1024).toFixed(2) + ' ' + s[2]; }
	            if (b < 1073741824)     { return (b / 1048576).toFixed(2) + ' '+ s[3]; }
	            else                    { return (b / 1073741824).toFixed(2) + ' '+ s[4]; }
	        }

	     });
	})(jQuery);
</script>

</head>
<body>

<?php if (( $this->_tpl_vars['menu'] == 'design' )): ?>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="des-editor-table">
<tr><td>
<?php endif; ?>
<div id="menu-level1">

	<ul class="menu-level">
		<li <?php if (( $this->_tpl_vars['menu'] == 'albums' )): ?>class="active-item"<?php endif; ?> ><span></span><a id="album-link" href="backend.php?controller=album#/Albums">�ружены.</a></li>
		<?php if (( $this->_tpl_vars['manage_collections'] != 0 )): ?><li <?php if (( $this->_tpl_vars['menu'] == 'collection' )): ?>class="active-item"<?php endif; ?> ><span></span><a href="backend.php?controller=album&action=collection"></a></li><?php endif; ?>
		<?php if (( $this->_tpl_vars['modify_design'] != 0 )): ?><li <?php if (( $this->_tpl_vars['menu'] == 'design' )): ?>class="active-item"<?php endif; ?> ><span></span><a  href="backend.php?controller=design">те порядок отображения альбомов, перетаскивая их с помощью мышки.</a></li><?php endif; ?>
		<?php if (( $this->_tpl_vars['modify_design'] != 0 )): ?><li <?php if (( $this->_tpl_vars['menu'] == 'settings' )): ?>class="active-item"<?php endif; ?> ><span></span><a  href="backend.php?controller=album&action=settings">Settings</a></li><?php endif; ?>
		</ul>
		<div class="public_galery"><span class="corn-bg"><a id="front_link" href="#">Public gallery</a></span></div>
	
	
</div>

<?php if (( $this->_tpl_vars['menu'] == 'design' )): ?>
</td></tr>
<tr><td class="editortd"> 
<?php endif; ?>

<?php echo $this->_tpl_vars['content']; ?>


<?php if (( $this->_tpl_vars['menu'] == 'design' )): ?>
</td> </tr> </table>
<?php endif; ?>


<div id="swfupload" style="display:none;"></div>
<div id="photo-move" style="display:none; background-color: #FFF;">
	<div class="window-header"><h2>пен только внутри вашего WebAsyst-аккаунта.:</h2></div>
	<p><select>
	</select></p>
	<p><input id="btn_move" type="button" value="угой альбом"> or <a href="#" onclick="$('#photo-move').wbsPopupClose(); return false;">close</a></p>
    <a href="#" onclick="$('#photo-move').wbsPopupClose(); return false;" class="div-popup-close"><img src="img/close.gif" width="16" height="16" /></a>
</div>

</body>
</html>