<?php /* Smarty version 2.6.26, created on 2014-10-16 18:39:38
         compiled from /home/djon/htdocs/dev.pol-na5.ru/published/SC/html/scripts/templates/frontend/aux_page.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'component', '/home/djon/htdocs/dev.pol-na5.ru/published/SC/html/scripts/templates/frontend/aux_page.html', 6, false),)), $this); ?>
<h1><?php echo $this->_tpl_vars['page_name']; ?>
</h1>

<div class="textseo">

	<?php if ($_GET['ukey'] == 'specialoffers'): ?>
		<?php echo smarty_function_component(array('cpt_id' => 'product_lists','list_id' => 'rootproducts','block_height' => '','overridestyle' => ''), $this);?>

	<?php else: ?>
		<?php echo $this->_tpl_vars['aux_page']; ?>

	<?php endif; ?>

</div>
