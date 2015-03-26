<?php /* Smarty version 2.6.26, created on 2014-10-18 13:01:29
         compiled from search_simple.html */ ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/category.js"></script>
<div class="categories-page search-result">
<h3>Результаты поиска</h3>


<?php if ($this->_tpl_vars['products_to_show_count'] > 0): ?>

	<p><?php echo 'Найдено '; ?>
 <b><?php echo $this->_tpl_vars['products_found']; ?>
</b> <?php echo 'продукт(ов)'; ?>
</p>

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
			<option value='/search/?searchstring=<?php echo $_GET['searchstring']; ?>
&sort=<?php echo $this->_tpl_vars['string_product_sort'][$this->_sections['i']['index']][0]; ?>
&direction=ASC' <?php if ($_GET['sort'] == $this->_tpl_vars['string_product_sort'][$this->_sections['i']['index']][0]): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['string_product_sort'][$this->_sections['i']['index']][2]; ?>
</option>
		<?php endfor; endif; ?>
		</select>
	</div>
<?php endif; ?>

	
	<?php if (@CONF_ALLOW_COMPARISON_FOR_SIMPLE_SEARCH): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "comparison_products_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>


	<div class="catalog">
		<?php unset($this->_sections['i1']);
$this->_sections['i1']['name'] = 'i1';
$this->_sections['i1']['loop'] = is_array($_loop=$this->_tpl_vars['products_to_show']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i1']['max'] = (int)$this->_tpl_vars['products_to_show_count'];
$this->_sections['i1']['show'] = true;
if ($this->_sections['i1']['max'] < 0)
    $this->_sections['i1']['max'] = $this->_sections['i1']['loop'];
$this->_sections['i1']['step'] = 1;
$this->_sections['i1']['start'] = $this->_sections['i1']['step'] > 0 ? 0 : $this->_sections['i1']['loop']-1;
if ($this->_sections['i1']['show']) {
    $this->_sections['i1']['total'] = min(ceil(($this->_sections['i1']['step'] > 0 ? $this->_sections['i1']['loop'] - $this->_sections['i1']['start'] : $this->_sections['i1']['start']+1)/abs($this->_sections['i1']['step'])), $this->_sections['i1']['max']);
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
			<div class="block-catalog-brief"><?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "product_brief.html", 'smarty_include_vars' => array('product_info' => $this->_tpl_vars['products_to_show'][$this->_sections['i1']['index']])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?></div>
		<?php endfor; endif; ?>
	</div>

	<?php if ($this->_tpl_vars['search_navigator']): ?><div class="paginate"><?php echo $this->_tpl_vars['search_navigator']; ?>
</div><?php endif; ?>

	<?php if (@CONF_ALLOW_COMPARISON_FOR_SIMPLE_SEARCH): ?>
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "comparison_products_button.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>

<?php else: ?>
	<p><?php echo 'Ничего не найдено'; ?>

<?php endif; ?>

</div>