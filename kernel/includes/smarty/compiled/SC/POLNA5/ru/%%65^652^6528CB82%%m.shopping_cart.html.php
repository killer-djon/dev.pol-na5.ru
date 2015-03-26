<?php /* Smarty version 2.6.26, created on 2014-10-24 18:51:32
         compiled from m.shopping_cart.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'm.shopping_cart.html', 6, false),array('modifier', 'set_query', 'm.shopping_cart.html', 9, false),array('modifier', 'set_query_html', 'm.shopping_cart.html', 26, false),array('modifier', 'escape', 'm.shopping_cart.html', 43, false),array('modifier', 'string_format', 'm.shopping_cart.html', 80, false),array('function', 'cycle', 'm.shopping_cart.html', 41, false),)), $this); ?>
<div id="blck-content">	

	<table cellpadding="0" cellspacing="0" width="100%">
	<tr>
		<td id="cart_page_title">
			<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
		</td>
		<?php if ($this->_tpl_vars['cart_content']): ?>
		<td id="cart_clear"><a href='<?php echo ((is_array($_tmp="clear_cart=yes")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
'><?php echo 'Очистить корзину'; ?>
</a></td>
		<?php endif; ?>
	</tr>
	</table>

<?php echo $this->_tpl_vars['MessageBlock']; ?>


<?php if ($this->_tpl_vars['cart_content']): ?>

	<?php if ($this->_tpl_vars['make_more_exact_cart_content']): ?>
		<p><?php echo 'В вашей корзине обнаружены продукты, добавленные при предыдущем пользовании нашего магазина. Пожалуйста, уточните содержимое заказа перед оформлением.'; ?>
</p>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['cart_amount'] < @CONF_MINIMAL_ORDER_AMOUNT): ?>
		<p id="id_too_small_order_amount" class="error_message"<?php if (! $this->_tpl_vars['minOrder']): ?> style="display:none;"<?php endif; ?>>			<?php echo 'Сумма заказа должна быть не менее '; ?>
 <?php echo $this->_tpl_vars['cart_min']; ?>
</p>
	<?php endif; ?>
	
	<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" method="post">
	<input type="hidden" name="update" value="1" >
	<input type="hidden" name="shopping_cart" value="1" >
	
	<table id="cart_content_tbl" cellspacing="0">
	<tr id="cart_content_header">
		<td></td>
		<td align="center"><?php echo 'Кол-во'; ?>
</td>
		<td align="center"><?php echo 'Стоимость'; ?>
</td>
		<td></td>
	</tr>

	<?php $this->assign('ProductsNum', 0); ?>
	<?php unset($this->_sections['i']);
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['cart_content']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['name'] = 'i';
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

	<tr class='row_<?php echo smarty_function_cycle(array('values' => "odd,even"), $this);?>
'>
		<td align="center" valign="top">
			<?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_url']): ?><img alt="" src="<?php echo ((is_array($_tmp=$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" width="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_width']; ?>
" /><br /><?php endif; ?>
			<a href='<?php echo ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['name']; ?>
</a>
		</td>
		<td align="center">
			<?php $this->assign('ProductsNum', $this->_tpl_vars['ProductsNum']+$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['quantity']); ?>

			<?php if ($this->_tpl_vars['session_items']): 
 $this->assign('_prdid', $this->_tpl_vars['session_items'][$this->_sections['i']['index']]); ?>
			<?php else: 
 $this->assign('_prdid', $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['id']); ?>
			<?php endif; ?>
			
			<input class="cart_product_quantity" type="text" maxlength="10" name="count_<?php echo $this->_tpl_vars['_prdid']; ?>
" value="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['quantity']; ?>
" size="3" >
			
			<?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['min_order_amount']): ?>
			<div class="cart_product_min_order_quantity">
				<?php echo 'Минимальный заказ'; ?>
 
				<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['min_order_amount']; ?>
 
				<?php echo 'шт.'; ?>

			</div>
			<?php endif; ?>
		</td>
		<td align="center" nowrap="nowrap">
			<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['cost']; ?>

		</td>
		<td align="center">
			<a href='<?php echo ((is_array($_tmp="remove=".($this->_tpl_vars['_prdid']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' title='<?php echo 'Удалить'; ?>
'>
			<img src="<?php echo @URL_IMAGES; ?>
/remove.gif" alt='<?php echo 'Удалить'; ?>
' />
			</a>
		</td>
	</tr>
	<?php endfor; endif; ?>

<?php if ($this->_tpl_vars['discount_prompt'] != 0): ?>

	<tr><td colspan="4">&nbsp;</td></tr>
	<?php if ($this->_tpl_vars['discount_prompt'] == 1 && $this->_tpl_vars['discount_percent'] != 0): ?>
	<tr>
		<td colspan="2" class="cart_discount_label">
			<?php echo 'Скидка, %'; ?>
,&nbsp;<?php echo ((is_array($_tmp=$this->_tpl_vars['discount_percent'])) ? $this->_run_mod_handler('string_format', true, $_tmp, '%0.1f%%') : smarty_modifier_string_format($_tmp, '%0.1f%%')); ?>

		</td>
		<td align="center" nowrap="nowrap">
			-<?php echo $this->_tpl_vars['discount_value']; ?>
	
		</td>
		<td></td>
	</tr>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['discount_prompt'] == 2): ?>
	<tr>
		<td colspan="4">
			<?php echo 'Зарегистрированные пользователи магазина получают скидки при заказах. Пожалуйста, свяжитесь с менеджером для получения дополнительной информации'; ?>

		</td>
	</tr>
	<?php endif; ?>

	<?php if ($this->_tpl_vars['discount_prompt'] == 3 && $this->_tpl_vars['discount_percent'] != 0): ?>
	<tr>
		<td colspan="2" class="cart_discount_label">
			<?php echo 'Скидка, %'; ?>
 <?php echo $this->_tpl_vars['discount_percent']; ?>

			<div class="cart_apply_for_discounts_extra">
			<?php echo 'Зарегистрированные пользователи интернет-магазина могут получить дополнительные скидки. Свяжитесь с нами для получения дополнительной информации.'; ?>

			</div>
		</td>
		<td align="center" nowrap="nowrap">
			-<?php echo $this->_tpl_vars['discount_value']; ?>
%
		</td>
		<td></td>
	</tr>
	<?php endif; ?>

<?php endif; ?>

	<tr>
		<td id="cart_total_label" colspan="1">
			<?php echo 'Итого'; ?>

		</td>
		<td align="center">
			<input type="submit" class="small" name="recalculate" value='<?php echo 'Пересчитать'; ?>
' >
		</td>
		<td id="cart_total" align="center"><?php echo $this->_tpl_vars['cart_total']; ?>
</td>
		<td></td>
	</tr>
	
	<tr>
		<td colspan="4" align="right" id="cart_checkout_btn">
			<input class="btn_checkout" name="checkout" id="btn-checkout" type="submit" value='<?php echo 'Оформить заказ'; ?>
' >
		</td>
	</tr>
	</table>

	</form>
<?php else: ?>
	<p style="text-align: center;"><?php echo 'Ваша корзина пуста'; ?>
</p>
<?php endif; ?>
</div>