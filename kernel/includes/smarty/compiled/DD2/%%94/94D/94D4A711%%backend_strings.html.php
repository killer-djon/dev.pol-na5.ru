<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:42
         compiled from backend_strings.html */ ?>
<script src='<?php echo $this->_tpl_vars['preproc']->getTemplatesUrl("js/locale.js"); ?>
'></script>
<script>
	WbsLocale.loadStrings ("common", 
		<?php echo $this->_tpl_vars['preproc']->assignStringsToJs('template_common',true); ?>

	);
	
	WbsLocale.loadStrings ("dd", 
		<?php echo $this->_tpl_vars['preproc']->assignStringsToJs('dd_template_backend',true); ?>

	);
</script>