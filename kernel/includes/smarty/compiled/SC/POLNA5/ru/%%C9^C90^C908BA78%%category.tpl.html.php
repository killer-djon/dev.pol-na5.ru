<?php /* Smarty version 2.6.26, created on 2014-10-16 18:20:59
         compiled from category.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'category.tpl.html', 6, false),array('modifier', 'set_query_html', 'category.tpl.html', 16, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/category.js"></script>

<div class="categories-page">
	
	<h1><span><?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</span> <a href="/psearch/" class="chooser"></a></h1>
	
		<?php if ($this->_tpl_vars['subcategories_to_be_shown']): ?>
		<div class="subcategories">
			<h4>Коллекция</h4>
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
				
				<?php if (!($this->_sections['i']['index'] % 3)): ?><ul class="sections-catalog"><?php endif; ?>
				
				<?php if ($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][3]): ?>
					<?php $this->assign('_sub_category_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][0])."&category_slug=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][3]))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
				<?php else: ?>
					<?php $this->assign('_sub_category_url', ((is_array($_tmp="?categoryID=".($this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][0]))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
				<?php endif; ?>
				
					<li class="section-title"><a href="<?php echo $this->_tpl_vars['_sub_category_url']; ?>
"><?php echo $this->_tpl_vars['subcategories_to_be_shown'][$this->_sections['i']['index']][1]; ?>
</a></li>
				
				<?php if (!(( $this->_sections['i']['index']+1 ) % 3)): ?></ul><?php endif; ?>
							 
			<?php endfor; endif; ?>
		</div>	
		<?php endif; ?>

	
	
<?php if ($this->_tpl_vars['products_to_show']): ?>

<?php if ($this->_tpl_vars['string_product_sort']): ?>
	<div class="products_sort">
		Сортировать по: <select name="sorting" onchange="window.location = this.value">
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['string_product_sort']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
			<option value='/category/<?php echo $this->_tpl_vars['selected_category']['slug']; ?>
/?sort=<?php echo $this->_tpl_vars['string_product_sort'][$this->_sections['i']['index']][0]; ?>
&direction=ASC' <?php if ($_GET['sort'] == $this->_tpl_vars['string_product_sort'][$this->_sections['i']['index']][0]): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['string_product_sort'][$this->_sections['i']['index']][2]; ?>
</option>
		<?php endfor; endif; ?>
		</select>
	</div>
<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "comparison_products_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	
<div class="catalog">

	<?php $_from = $this->_tpl_vars['products_to_show']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['product_brief'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['product_brief']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['product_item']):
        $this->_foreach['product_brief']['iteration']++;
?>
	
		<div class="block-catalog-brief"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "product_brief.html", 'smarty_include_vars' => array('product_info' => $this->_tpl_vars['product_item'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
	
	<?php endforeach; endif; unset($_from); ?>

</div>

 
<?php if ($this->_tpl_vars['catalog_navigator']): ?><div class="paginate"><?php echo $this->_tpl_vars['catalog_navigator']; ?>
</div><?php endif; ?>


<?php else: ?>

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

<?php endif; ?>
</div>