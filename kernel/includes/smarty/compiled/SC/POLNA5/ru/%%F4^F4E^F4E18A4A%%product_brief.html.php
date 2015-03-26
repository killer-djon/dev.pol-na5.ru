<?php /* Smarty version 2.6.26, created on 2014-10-16 18:20:59
         compiled from product_brief.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_brief.html', 4, false),array('modifier', 'escape', 'product_brief.html', 13, false),)), $this); ?>
<?php if ($this->_tpl_vars['product_info'] != NULL): 
 if ($this->_tpl_vars['product_info']['slug']): 
 $this->assign('_product_url', ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['product_info']['productID'])."&product_slug=".($this->_tpl_vars['product_info']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); 
 else: 
 $this->assign('_product_url', ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['product_info']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); 
 endif; 
 if ($this->_tpl_vars['widget']): 
 $this->assign('_form_action_url', "&view=noframe&external=1"); 
 endif; ?>
<!-- start product_brief.html -->
<form class="product_brief_block" action='<?php echo ((is_array($_tmp="?ukey=cart".($this->_tpl_vars['_form_action_url']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' method="post" rel="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" <?php if ($this->_tpl_vars['widget']): ?>target="_blank"<?php endif; ?>>
	<input name="action" value="add_product" type="hidden">
	<input name="productID" value="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" type="hidden">
	<input class="product_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['PriceWithOutUnit'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden">
	<input type="hidden" name="product_qty" class="product_qty" value="10" />
	<?php $this->assign('_cnt', ''); ?>
	
<div class="prdbrief_thumbnail">
	<!-- Thumbnail -->
				
<?php if ($this->_tpl_vars['product_info']['thumbnail']): ?>
<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'>
					<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"></a>
<?php elseif ($this->_tpl_vars['product_info']['picture']): ?>
<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'>
					<img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"></a>
<?php else: ?>
	<a <?php echo $this->_tpl_vars['target']; ?>
 href='javascript:void()' class="no-photo"><img src="/images/no_photo.png" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" /></a>					
<?php endif; ?>
				
	</div>
	
	<div class="prdbrief_name">
		<a <?php echo $this->_tpl_vars['target']; ?>
 href='<?php echo $this->_tpl_vars['_product_url']; ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
		<?php if ($this->_tpl_vars['product_info']['product_code'] && @CONF_ENABLE_PRODUCT_SKU): ?>
		<br><i><?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['product_code'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</i>
<?php endif; ?>
	</div>


    <?php if (@CONF_VOTING_FOR_PRODUCTS == 'True'): ?>
	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && $this->_tpl_vars['product_info']['customer_votes'] > 0): ?> 		<div class="sm-current-rating1">
			<div class="sm-current-rating1-back">&nbsp;</div>
			<div class="sm-current-rating1-front" style="width: <?php echo $this->_tpl_vars['product_info']['customers_rating']*13; ?>
px;">&nbsp;</div>
		</div>
	<?php endif; ?>
    <?php endif; ?>
	

	
	<div class="prdbrief_price">
		Цена: <?php if ($this->_tpl_vars['currencies_count'] != 0 && $this->_tpl_vars['product_info']['Price'] > 0): ?><span class="totalPrice"><?php echo $this->_tpl_vars['product_info']['PriceWithUnit']; ?>
</span> <span class="unit">Руб/м<sup>2</sup></span>
			  <?php else: ?>
<span class="totalPrice">&mdash;</span>
			  <?php endif; ?>
		
	</div>
	

	<?php if ($this->_tpl_vars['PAGE_VIEW'] != 'mobile' && ( $this->_tpl_vars['PAGE_VIEW'] != 'vkontakte' ) && ( $this->_tpl_vars['PAGE_VIEW'] != 'facebook' ) && $this->_tpl_vars['product_info']['allow_products_comparison'] && $this->_tpl_vars['show_comparison']): ?>  	<div class="prdbrief_comparison">
		<input id="ctrl-prd-cmp-<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" class="checknomarging ctrl_products_cmp" type="checkbox" value='<?php echo $this->_tpl_vars['product_info']['productID']; ?>
'>
		<label for="ctrl-prd-cmp-<?php echo $this->_tpl_vars['product_info']['productID']; ?>
"><?php echo 'Сравнить'; ?>
</label>
	</div>
	<?php endif; ?>
	
<?php if ($this->_tpl_vars['product_info']['ordering_available'] && $this->_tpl_vars['product_info']['Price'] > 0 && ( @CONF_SHOW_ADD2CART == 1 ) && ( @CONF_CHECKSTOCK == 0 || $this->_tpl_vars['product_info']['in_stock'] > 0 )): ?>
	<div class="prdbrief_add2cart">		
		<div class="carttext">
		<input value="" rel="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="submit" alt="<?php echo 'добавить в корзину'; ?>
" title="<?php echo 'добавить в корзину'; ?>
" class="add2cart_handler" />
	</div>
	</div>
<?php elseif (@CONF_SHOW_ADD2CART == 1 && @CONF_CHECKSTOCK && ! $this->_tpl_vars['product_info']['in_stock'] && $this->_tpl_vars['product_info']['ordering_available']): ?>
	<div class="prd_out_of_stock"><?php echo 'Нет на складе'; ?>
</div>
<?php endif; ?>
	
	
	
</form>
<!-- end product_brief.html -->

<?php endif; ?>