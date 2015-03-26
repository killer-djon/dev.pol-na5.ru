<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:42
         compiled from /home/djon/htdocs/dev.pol-na5.ru/published/common/templates/elements/mainscreen_complex.htm */ ?>

<script src='<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/ext-small.js"); ?>
'></script>
<script src='<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/build/widgets/Resizable-min.js"); ?>
'></script>
<script>Ext.BLANK_IMAGE_URL = '<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/resources/images/default/s.gif"); ?>
'</script>

<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['preproc']->getTemplatesUrl("elements/mainscreen_complex.css"); ?>
'>

<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/resources/css/tree.css"); ?>
'>
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/resources/css/resizable.css"); ?>
'>
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/resources/css/core.css"); ?>
'>
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['preproc']->getCommonUrl("html/res/ext/resources/css/window.css"); ?>
'>


<?php $this->assign('cacheEnabled', true); ?>
<?php if ($this->_tpl_vars['cacheEnabled']): ?>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/mainscreen_complex.js'); ?>
"></script>
<?php else: ?>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_application.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_observable.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_nav_bar.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_tree.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_record.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_recordset.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_reader.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_data_store.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_viewmode_selector.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_popwindow.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_popmenu.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_table.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_views.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_pager.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_flex_container.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_dlg.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_editable.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_button.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/js/wbs_rights.js'); ?>
"></script>
	<script src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('templates/elements/total.js'); ?>
"></script>
<?php endif; ?>