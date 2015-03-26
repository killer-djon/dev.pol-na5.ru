<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:42
         compiled from dlg_copymove.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'dlg_copymove.htm', 4, false),)), $this); ?>
<div class="hidden wbs-dlg-content-inner" id='dlg-copymove-content'>
	<div style='margin-top: 5px; margin-bottom: 5px' id='copymove-desc'></div>
	
	<?php echo ((is_array($_tmp='template_common->lbl_to_folder')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:
	<select id='copymove-folders-select'></select>
</div>