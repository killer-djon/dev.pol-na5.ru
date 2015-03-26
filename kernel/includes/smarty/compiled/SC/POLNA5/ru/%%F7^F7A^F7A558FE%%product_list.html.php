<?php /* Smarty version 2.6.26, created on 2014-10-17 15:23:13
         compiled from product_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_list.html', 6, false),array('modifier', 'escape', 'product_list.html', 16, false),array('modifier', 'set_query', 'product_list.html', 72, false),)), $this); ?>
<script type="text/javascript" src="/published/SC/html/scripts/js/category.js"></script>
<div class="catalog">
	<?php $_from = $this->_tpl_vars['__products']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_product']):
?>
	
		<?php if ($this->_tpl_vars['_product']['slug']): ?>
			<?php $this->assign('_product_url', ((is_array($_tmp="?productID=".($this->_tpl_vars['_product']['productID'])."&product_slug=".($this->_tpl_vars['_product']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
		<?php else: ?>
			<?php $this->assign('_product_url', ((is_array($_tmp="?productID=".($this->_tpl_vars['_product']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['widget']): 
 $this->assign('_form_action_url', "&view=noframe&external=1"); 
 endif; ?>
		
		<div class="block-catalog-brief">
			<form class="product_brief_block" action='<?php echo ((is_array($_tmp="?ukey=cart".($this->_tpl_vars['_form_action_url']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' method="post" rel="<?php echo $this->_tpl_vars['_product']['productID']; ?>
" <?php if ($this->_tpl_vars['widget']): ?>target="_blank"<?php endif; ?>>
			<input name="action" value="add_product" type="hidden">
			<input name="productID" value="<?php echo $this->_tpl_vars['_product']['productID']; ?>
" type="hidden">
			<input class="product_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['Pricet'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden">
			<input type="hidden" name="product_qty" class="product_qty" value="10" />
			
			<div class="prdbrief_thumbnail">
			<!-- Thumbnail -->
						
				<?php if ($this->_tpl_vars['_product']['thumbnail']): ?>
				<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'>
									<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"></a>
				<?php elseif ($this->_tpl_vars['_product']['filename']): ?>
				<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'>
									<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"></a>
				<?php else: ?>
					<a <?php echo $this->_tpl_vars['target']; ?>
 href='javascript:void()' class="no-photo"><img src="<?php echo @URL_IMAGES; ?>
/no_photo.png" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" /></a>					
				<?php endif; ?>
						
			</div>
			
			<div class="prdbrief_name">
				<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
								<?php if ($this->_tpl_vars['_product']['product_code'] && @CONF_ENABLE_PRODUCT_SKU): ?>
						<br><i><?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['product_code'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>
				<?php endif; ?>
			</div>
			
			<div class="prdbrief_price">
				Цена: 
				<?php if ($this->_tpl_vars['_product']['Price'] > 0): ?><span class="totalPrice2"><?php echo $this->_tpl_vars['_product']['Price']; ?>
</span> 
					<span class="unit">Руб/м<sup>2</sup></span>
				<?php else: ?>
					<span class="totalPrice">&mdash;</span>
				<?php endif; ?>
				
			</div>
			<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && ( $this->_tpl_vars['PAGE_VIEW'] != 'vkontakte' ) && ( $this->_tpl_vars['PAGE_VIEW'] != 'facebook' )): ?> 
			<div class="prdbrief_comparison">
				<input id="ctrl-prd-cmp-<?php echo $this->_tpl_vars['_product']['productID']; ?>
" class="checknomarging ctrl_products_cmp" type="checkbox" value='<?php echo $this->_tpl_vars['_product']['productID']; ?>
'>
				<label for="ctrl-prd-cmp-<?php echo $this->_tpl_vars['_product']['productID']; ?>
"><?php echo 'Сравнить'; ?>
</label>
			</div>
			<?php endif; ?>
			
			<?php if ($this->_tpl_vars['_product']['ordering_available'] && $this->_tpl_vars['_product']['Price'] > 0 && ( @CONF_SHOW_ADD2CART == 1 ) && ( @CONF_CHECKSTOCK == 0 || $this->_tpl_vars['_product']['in_stock'] > 0 )): ?>
			<div class="prdbrief_add2cart">		
				<div class="carttext">
				<input value="" rel="<?php echo ((is_array($_tmp=$this->_tpl_vars['_product']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="submit" alt="<?php echo 'добавить в корзину'; ?>
" title="<?php echo 'добавить в корзину'; ?>
" class="add2cart_handler" />
			</div>
			</div>
		<?php elseif (@CONF_SHOW_ADD2CART == 1 && @CONF_CHECKSTOCK && ! $this->_tpl_vars['_product']['in_stock'] && $this->_tpl_vars['_product']['ordering_available']): ?>
			<div class="prd_out_of_stock"><?php echo 'Нет на складе'; ?>
</div>
		<?php endif; ?>
			</form>
		</div>
	<?php endforeach; endif; unset($_from); ?>
	
	<div class="comparison">
		<form action='<?php echo ((is_array($_tmp="?categoryID=&category_slug=&ukey=product_comparison")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
' method="post">
			<input type="hidden" value='' class="comparison_products" name='comparison_products' >
			<input value='' class="hndl_submit_prds_cmp compar-button" onclick='submitProductsComparison();' type="button" >
		</form>
	</div>
</div>