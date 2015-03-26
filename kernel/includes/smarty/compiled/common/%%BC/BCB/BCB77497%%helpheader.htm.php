<?php /* Smarty version 2.6.26, created on 2014-08-08 13:48:44
         compiled from helpheader.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlsafe', 'helpheader.htm', 48, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head>
		<title><?php echo $this->_tpl_vars['pageTitle']; ?>
</title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_tpl_vars['html_encoding']; ?>
">
		<link href="../classic/help_toc.css" rel="stylesheet" type="text/css">

		<script language=JavaScript>
			var items = new Array();
			var selected = null;
			
			function goToItem (num, anchor) {
				selectItem("item" + num);
				var link = "link" + num;
				if (num > 0) {
					window.parent.frames["content"].location.href = document.getElementById( link).href + "#" + anchor;
				}
			}

			function selectItem( obj )
			{
				if ( this.selected != null ) {
					var prevObj = document.getElementById( this.selected );
					if ( prevObj != null )
						prevObj.className = "";
				}

				var thisObj = document.getElementById( obj );
				if ( thisObj != null )
					thisObj.className = "selected";

				this.selected = obj;
			}
		</script>

	</head>
	<body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0 bgcolor=#47A6CE onLoad="goToItem('<?php echo $this->_tpl_vars['selectedItem']; ?>
', '<?php echo $this->_tpl_vars['section']; ?>
')">

	<table border=0 cellpadding=3 width="100%">
		<?php $_from = $this->_tpl_vars['appLinks']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['itemKey'] => $this->_tpl_vars['appLink']):
?>

			<script language=JavaScript>
				items.push( "item<?php echo $this->_tpl_vars['itemKey']; ?>
" );
			</script>

			<tr>
				<td class=tocTree align=left id="item<?php echo $this->_tpl_vars['itemKey']; ?>
" onClick="selectItem('item<?php echo $this->_tpl_vars['itemKey']; ?>
')">
					<a id='link<?php echo $this->_tpl_vars['itemKey']; ?>
' href="<?php echo $this->_tpl_vars['appLink']['LINK']; ?>
" hidefocus="true" class="treeitem" target="content"><img src="../classic/images/page.gif" border="0"><?php echo ((is_array($_tmp=$this->_tpl_vars['appLink']['APP_NAME'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</a>
				</td>
			</tr>
		<?php endforeach; endif; unset($_from); ?>
	</table>

	</body>
</html>