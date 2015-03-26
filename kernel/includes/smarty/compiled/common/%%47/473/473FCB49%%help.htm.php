<?php /* Smarty version 2.6.26, created on 2014-08-08 13:48:44
         compiled from help.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'help.htm', 9, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
	<head>
		<title><?php echo $this->_tpl_vars['pageTitle']; ?>
</title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $this->_tpl_vars['html_encoding']; ?>
">
	</head>

	<frameset cols="230,*" frameborder=NO border=1 framespacing=1>
		<frame src="helpheader.php?<?php if ($this->_tpl_vars['trans_sid']): 
 echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['session_name'])) ? $this->_run_mod_handler('cat', true, $_tmp, "=") : smarty_modifier_cat($_tmp, "=")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['session_id']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['session_id'])); 
 endif; 
 if ($this->_tpl_vars['selectedApp']): ?>&selectedApp=<?php echo $this->_tpl_vars['selectedApp']; 
 endif; 
 if ($this->_tpl_vars['section']): ?>&section=<?php echo $this->_tpl_vars['section']; 
 endif; ?>" name=toc frameborder=yes>
		<frame src="../../../MW/help/<?php echo $this->_tpl_vars['mwLang']; ?>
/mw.html" name=content scrolling=yes frameborder=yes>
	</frameset>

	<noframes>
		<body>
		</body>
	</noframes>

</html>