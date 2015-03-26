<?php /* Smarty version 2.6.26, created on 2014-10-16 23:39:49
         compiled from m.category.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query', 'm.category.tpl.html', 2, false),array('modifier', 'set_query_html', 'm.category.tpl.html', 5, false),array('modifier', 'escape', 'm.category.tpl.html', 5, false),)), $this); ?>
	<a href="<?php echo ((is_array($_tmp="?")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Главная'; ?>
</a> <?php echo $this->_tpl_vars['BREADCRUMB_DELIMITER']; ?>

	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_category_path']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<?php if ($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['categoryID'] != 1): ?>
	<a href='<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['categoryID'])."&category_slug=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a><?php if (! $this->_sections['i']['last']): 
 echo $this->_tpl_vars['BREADCRUMB_DELIMITER']; 
 endif; ?>
	<?php endif; ?>
	<?php endfor; endif; ?>

		<?php if ($this->_tpl_vars['selected_category']['description']): ?><div><?php echo $this->_tpl_vars['selected_category']['description']; ?>
</div><?php endif; ?>
	
	<br />

<center>

<?php if ($this->_tpl_vars['subcategories_to_be_shown']): ?>
<div class="background1" style="padding:6px;text-align:center;">
<?php endif; ?>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['subcategories_to_be_shown']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
 <a href="<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][0]))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo $this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][1]; ?>
</a>
 (<?php echo $this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][2]; ?>
)
<?php endfor; endif; ?>

<?php if ($this->_tpl_vars['products_to_show']): ?>

	<?php if ($this->_tpl_vars['string_product_sort']): ?><p id="cat_product_sort"><?php echo $this->_tpl_vars['string_product_sort']; ?>
</p><?php endif; ?>
	
	
	<?php if ($this->_tpl_vars['catalog_navigator']): ?><p><?php echo $this->_tpl_vars['catalog_navigator']; ?>
</p><?php endif; ?>
		
	 <?php unset($this->_sections['i1']);
$this->_sections['i1']['name'] = 'i1';
$this->_sections['i1']['loop'] = is_array($_loop=$this->_tpl_vars['products_to_show']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i1']['show'] = true;
$this->_sections['i1']['max'] = $this->_sections['i1']['loop'];
$this->_sections['i1']['step'] = 1;
$this->_sections['i1']['start'] = $this->_sections['i1']['step'] > 0 ? 0 : $this->_sections['i1']['loop']-1;
if ($this->_sections['i1']['show']) {
    $this->_sections['i1']['total'] = $this->_sections['i1']['loop'];
    if ($this->_sections['i1']['total'] == 0)
        $this->_sections['i1']['show'] = false;
} else
    $this->_sections['i1']['total'] = 0;
if ($this->_sections['i1']['show']):

            for ($this->_sections['i1']['index'] = $this->_sections['i1']['start'], $this->_sections['i1']['iteration'] = 1;
                 $this->_sections['i1']['iteration'] <= $this->_sections['i1']['total'];
                 $this->_sections['i1']['index'] += $this->_sections['i1']['step'], $this->_sections['i1']['iteration']++):
$this->_sections['i1']['rownum'] = $this->_sections['i1']['iteration'];
$this->_sections['i1']['index_prev'] = $this->_sections['i1']['index'] - $this->_sections['i1']['step'];
$this->_sections['i1']['index_next'] = $this->_sections['i1']['index'] + $this->_sections['i1']['step'];
$this->_sections['i1']['first']      = ($this->_sections['i1']['iteration'] == 1);
$this->_sections['i1']['last']       = ($this->_sections['i1']['iteration'] == $this->_sections['i1']['total']);
?>
		<?php if (!($this->_sections['i1']['index'] % @CONF_COLUMNS_PER_PAGE)): ?><tr><?php endif; ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "product_brief.html", 'smarty_include_vars' => array('product_info' => $this->_tpl_vars['products_to_show'][$this->_sections['i1']['index']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
		<br />
		<br />
	  <?php endfor; endif; ?>
	 
	<?php if ($this->_tpl_vars['catalog_navigator']): ?><p><?php echo $this->_tpl_vars['catalog_navigator']; ?>
</p><?php endif; ?>

<?php else: ?>
<p>
	<?php if ($this->_tpl_vars['search_with_change_category_ability'] && ! $this->_tpl_vars['advanced_search_in_category']): ?>
		&nbsp;
	<?php else: ?>
		<?php if ($this->_tpl_vars['advanced_search_in_category']): ?>
			&nbsp;&nbsp;&nbsp;&nbsp;< <?php echo 'Ничего не найдено'; ?>
 >
		<?php else: ?>
			&nbsp;&nbsp;&nbsp;&nbsp;< <?php echo 'Нет продуктов'; ?>
 >
		<?php endif; ?>
	<?php endif; ?>
</p>
<?php endif; ?>

</center>


</div>