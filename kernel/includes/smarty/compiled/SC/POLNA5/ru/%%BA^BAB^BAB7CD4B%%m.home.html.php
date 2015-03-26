<?php /* Smarty version 2.6.26, created on 2014-10-17 00:38:08
         compiled from m.home.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'component', 'm.home.html', 2, false),)), $this); ?>
<h1><?php echo @CONF_DEFAULT_TITLE; ?>
</h1>
<?php echo smarty_function_component(array('cpt_id' => 'root_categories'), $this);?>