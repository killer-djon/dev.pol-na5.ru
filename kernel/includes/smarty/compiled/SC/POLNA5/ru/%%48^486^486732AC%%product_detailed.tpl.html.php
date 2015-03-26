<?php /* Smarty version 2.6.26, created on 2014-10-16 18:40:21
         compiled from product_detailed.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'product_detailed.tpl.html', 4, false),array('modifier', 'escape', 'product_detailed.tpl.html', 12, false),array('function', 'counter', 'product_detailed.tpl.html', 37, false),array('function', 'related_categories', 'product_detailed.tpl.html', 186, false),)), $this); ?>
<?php if ($this->_tpl_vars['product_info'] != NULL): ?>

<?php if ($this->_tpl_vars['product_info']['slug']): 
 $this->assign('_product_url', ((is_array($_tmp="?productID=".($this->_tpl_vars['product_info']['productID'])."&product_slug=".($this->_tpl_vars['product_info']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); 
 else: 
 $this->assign('_product_url', ((is_array($_tmp="?productID=".($this->_tpl_vars['product_info']['productID']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); 
 endif; ?>




	<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['selected_category']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h1>
	
	<div class="product-detailed">
		
		<h4><?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</h4>
		
		<div class="product-images">
			
			<div class="big-image">				
				<?php if (! $this->_tpl_vars['printable_version'] && $this->_tpl_vars['product_info']['big_picture'] && ( $this->_tpl_vars['product_info']['big_picture'] != $this->_tpl_vars['product_info']['picture'] )): ?>					
					<a href='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php if ($this->_tpl_vars['product_info']['big_picture']): 
 echo ((is_array($_tmp=$this->_tpl_vars['product_info']['big_picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 else: 
 echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>' class="lytebox" data-lyte-options="group:productimages" data-title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
						<img id='img-current_picture' border='0' src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
						<div class="lupa-img"></div>
					</a>
					
				<?php else: ?>
					<a href="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" class="lytebox" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
						<img id='img-current_picture' border='0' src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['page_title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >		
					</a>
				<?php endif; ?>		
			</div>
			
			<div class="all-images">
				<?php $_from = $this->_tpl_vars['all_product_pictures']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['frpict'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['frpict']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_picture']):
        $this->_foreach['frpict']['iteration']++;
?>
					<?php if ($this->_tpl_vars['_picture']['photoID'] != $this->_tpl_vars['product_info']['photoID']): ?>
						<?php echo smarty_function_counter(array('name' => '_pict_num','assign' => '_pict_num'), $this);?>

						
						<a class="lytebox" href='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php if ($this->_tpl_vars['_picture']['enlarged']): 
 echo ((is_array($_tmp=$this->_tpl_vars['_picture']['enlarged'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 else: 
 echo ((is_array($_tmp=$this->_tpl_vars['_picture']['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); 
 endif; ?>' data-lyte-options="group:productimages" data-title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
">
							<img src='<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['_picture']['thumbnail'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
' border='0' alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" title="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" >
							<div class="lupa-img"></div>
						</a>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>	
			</div>
			
		</div>
		
		
		<div class="product-info">
			
			<div class="sidebar left-sidebar">
				<h4>ХАРАКТЕРИСТИКИ</h4>
					<?php if ($this->_tpl_vars['product_info']['product_code']): ?>
						<div class="block-option">
							<div class="option-name">
								<?php echo 'Артикул'; ?>
:
							</div>
							
							<div class="option-value">	
								<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['product_code'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

							</div>
						</div>
					<?php endif; ?>	
				<?php echo smarty_function_counter(array('name' => 'select_counter','start' => 0,'skip' => 1,'print' => false,'assign' => 'select_counter_var'), $this);?>

				<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_extra']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
					
					<?php if (( $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_type'] == 0 ) && ( $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_value'] != '' )): ?>
					<div class="block-option"> 
						<div class="option-name"><?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['name']; ?>
:</div>
						<div class="option-value" rel="<?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['optionID']; ?>
"><?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_value']; ?>
</div>
					</div>	
					
					<?php else: ?>
					
					<?php unset($this->_sections['k']);
$this->_sections['k']['name'] = 'k';
$this->_sections['k']['loop'] = is_array($_loop=$this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_show_times']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['k']['show'] = true;
$this->_sections['k']['max'] = $this->_sections['k']['loop'];
$this->_sections['k']['step'] = 1;
$this->_sections['k']['start'] = $this->_sections['k']['step'] > 0 ? 0 : $this->_sections['k']['loop']-1;
if ($this->_sections['k']['show']) {
    $this->_sections['k']['total'] = $this->_sections['k']['loop'];
    if ($this->_sections['k']['total'] == 0)
        $this->_sections['k']['show'] = false;
} else
    $this->_sections['k']['total'] = 0;
if ($this->_sections['k']['show']):

            for ($this->_sections['k']['index'] = $this->_sections['k']['start'], $this->_sections['k']['iteration'] = 1;
                 $this->_sections['k']['iteration'] <= $this->_sections['k']['total'];
                 $this->_sections['k']['index'] += $this->_sections['k']['step'], $this->_sections['k']['iteration']++):
$this->_sections['k']['rownum'] = $this->_sections['k']['iteration'];
$this->_sections['k']['index_prev'] = $this->_sections['k']['index'] - $this->_sections['k']['step'];
$this->_sections['k']['index_next'] = $this->_sections['k']['index'] + $this->_sections['k']['step'];
$this->_sections['k']['first']      = ($this->_sections['k']['iteration'] == 1);
$this->_sections['k']['last']       = ($this->_sections['k']['iteration'] == $this->_sections['k']['total']);
?>
						<div class="block-option">
							<div class="option-name">
								<?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['name']; ?>

									<?php if ($this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['option_show_times'] > 1): ?>
									(<?php echo smarty_function_counter(array('name' => 'option_show_times'), $this);?>
):
								<?php else: ?>:<?php endif; ?>
							</div>
						
							<div class="option-value">
								<?php echo smarty_function_counter(array('name' => 'select_counter'), $this);?>

								<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>
									<?php echo $this->_tpl_vars['product_extra'][$this->_sections['i']['index']]['values_to_select'][$this->_sections['j']['index']]['option_value']; ?>

								<?php endfor; endif; ?>
							</div>							
							
						</div>
					<?php endfor; endif; ?>
					
					<?php endif; ?>
				<?php endfor; endif; ?>
								
				<div class="block-option second">
					<div class="option-name">
						Цена кв.м.:
					</div>
					<div class="option-value">
						<?php if ($this->_tpl_vars['currencies_count'] != 0 && $this->_tpl_vars['product_info']['Price'] > 0): ?>
						
						<?php if ($this->_tpl_vars['product_info']['list_price'] > 0 && $this->_tpl_vars['product_info']['list_price'] > $this->_tpl_vars['product_info']['Price'] && $this->_tpl_vars['product_info']['Price'] > 0): ?> 
						<span class="regularPrice"><?php echo $this->_tpl_vars['product_info']['list_priceWithUnit']; ?>
</span> 
						<?php endif; ?>
					
						<span class="totalPrice"><?php echo $this->_tpl_vars['product_info']['PriceWithUnit']; ?>
 <span class="unit">РУБ/М<sup>2</sup></span> </span>
					
												<?php if ($this->_tpl_vars['product_info']['list_price'] > 0 && $this->_tpl_vars['product_info']['list_price'] > $this->_tpl_vars['product_info']['Price'] && $this->_tpl_vars['product_info']['Price'] > 0): ?> 
						<div>
							<span class="youSaveLabel"><?php echo 'Вы экономите'; ?>
:</span>
							<span class="youSavePrice"><?php echo $this->_tpl_vars['product_info'][14]; ?>
 (<?php echo $this->_tpl_vars['product_info'][15]; ?>
%)</span>
						</div>
						<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>

				<div class="block-option second">
					<div class="option-name"><?php echo 'Кол-во квадратных м.'; ?>
: </div>
					
					<div class="option-value">
						<input type="text" name="count_per" value="10" size="3" /> Кв.м.
					</div>
				</div>
				<div class="option-separator block-option"></div>
				
				<div class="block-option second">
					<div class="option-name">Итого упаковок:</div>
					<div class="option-value counts_metr"></div>
				</div>
				
				<div class="block-option second">
					<div class="option-name">Итого кв.м.:</div>
					<div class="option-value counts_metr_total"></div>
				</div>
				
				<div class="block-option second">
					
					<div class="option-name">Всего к оплате:</div>
					<div class="option-value counts_price"><span class="totalPrice"><?php echo $this->_tpl_vars['product_info']['PriceWithUnit']; ?>
 </span> <span class="unit">РУБ</span> </div>
				</div>
					 
				<div class="add-to-cart">
					<?php if ($this->_tpl_vars['widget']): ?>
					<?php $this->assign('_form_action_url', "&view=noframe&external=1"); ?>
					<?php endif; ?>
					
					<form action='<?php echo ((is_array($_tmp="?ukey=cart".($this->_tpl_vars['_form_action_url']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' method="post" rel="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" <?php if ($this->_tpl_vars['widget']): ?>target="_blank"<?php endif; ?>>
						<input name="action" value="add_product" type="hidden" >
						<input type="hidden" name="product_qty" class="product_qty" value="" />
						<input name="productID" value="<?php echo $this->_tpl_vars['product_info']['productID']; ?>
" type="hidden" >
						<input class="_price" value="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['PriceWithOutUnit'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="hidden" >
						<input type="hidden" name="product_price" class="product_price" value="">
					<?php $this->assign('_cnt', ''); ?>	 

					
					<input value="" rel="<?php echo ((is_array($_tmp=$this->_tpl_vars['product_info']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" type="submit" alt="<?php echo 'добавить в корзину'; ?>
" title="<?php echo 'добавить в корзину'; ?>
" class="add2cart_handler" />
					</form>
				</div>
			
			</div>
			
			<div class="sidebar right-sidebar">
				<h4>ОПИСАНИЕ</h4>
				<div class="intro-text">
					<?php echo $this->_tpl_vars['product_info']['brief_description']; ?>

				</div>
				
				<h4>Заказ укладки</h4>
				<div class="offers-block">
					<a href="#" class="call-me" rel="send_ukladka"><img src="<?php echo @URL_IMAGES; ?>
/offers_img.png" alt="Заказать укладку" /></a>
					
				</div>
			</div>
			
		</div>
		
		
		<div class="related_products">
			<h3>Сопутствующие товары</h3>
			<?php echo smarty_function_related_categories(array('categories' => '1522, 1621'), $this);?>

			<?php if ($this->_tpl_vars['related_categories_count'] > 0): ?>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "related_categories.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>	
			<?php endif; ?>
			
		</div>
		
	</div>

<?php endif; ?>