<?php /* Smarty version 2.6.26, created on 2014-10-26 13:28:55
         compiled from checkout.confirmation.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'checkout.confirmation.html', 14, false),array('modifier', 'set_query_html', 'checkout.confirmation.html', 47, false),array('modifier', 'string_format', 'checkout.confirmation.html', 81, false),array('modifier', 'default', 'checkout.confirmation.html', 81, false),array('modifier', 'replace', 'checkout.confirmation.html', 116, false),array('function', 'cycle', 'checkout.confirmation.html', 39, false),)), $this); ?>
<div id="blck-content" class="shopping-cart-content">	
	<?php echo $this->_tpl_vars['MessageBlock']; ?>

	<?php if ($this->_tpl_vars['payment_error']): ?>
	<p>
		<font color="red"><b><?php echo 'Ошибка при оплате'; 
 if ($this->_tpl_vars['payment_error'] != 1): ?>: <?php echo $this->_tpl_vars['payment_error']; 
 endif; ?></b></font>
	</p>
	<?php endif; ?>

	<table cellspacing="0" cellpadding="15" class="checkout_table">
	<?php if (( $this->_tpl_vars['orderSum']['shipping_name'] && $this->_tpl_vars['orderSum']['shipping_name'] != "-" ) || ( $this->_tpl_vars['orderSum']['payment_name'] && $this->_tpl_vars['orderSum']['payment_name'] != "-" )): ?>
	<tr>
	<?php if ($this->_tpl_vars['orderSum']['shipping_name'] && $this->_tpl_vars['orderSum']['shipping_name'] != "-"): ?>
		<td>
			<?php echo 'Доставка'; ?>
 (<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['orderSum']['shipping_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 if ($this->_tpl_vars['orderSum']['shippingServiceInfo']): ?> - <?php echo ((is_array($_tmp=$this->_tpl_vars['orderSum']['shippingServiceInfo'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); 
 endif; ?></strong>):
			<p><strong><?php echo $this->_tpl_vars['orderSum']['shippingAddress']->getHTMLString(); ?>
</strong></p>
		</td>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['orderSum']['payment_name'] && $this->_tpl_vars['orderSum']['payment_name'] != "-"): ?>
		<td>
			<?php echo 'Оплата'; ?>
 (<strong><?php echo ((is_array($_tmp=$this->_tpl_vars['orderSum']['payment_name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</strong>):
			<p><strong><?php echo $this->_tpl_vars['orderSum']['billingAddress']->getHTMLString(); ?>
</strong></p>
		</td>
	<?php endif; ?>
	</tr>
	<?php endif; ?>

	<tr>
	<td colspan="2">	
		<div class="cart-products">
		<table id="cart_content_tbl" cellspacing="0" width="100%">
		 	<colgroup>
		        <col width="20%" />
		        <col width="20%" />
		        <col width="30%" />
		        <col width="30%" />
		    </colgroup>
	
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['orderSum']['sumOrderContent']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
 list-products'>
					<td class="image-cart">
						<?php if ($this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['thumbnail_url']): ?>
							<img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['thumbnail_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" width="<?php echo $this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['thumbnail_width']; ?>
" alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" />
						
						<?php endif; ?>
					</td>
					<td class="name-cart">
						<a href='<?php echo ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=$this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</a>
					</td>
					<td class="count-cart">
						<div class="counts">
							<span>Количество: </span>
							<?php echo $this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['quantity']; ?>

						</div>
					</td>
					<td class="total-cart">
						<div class="price">
							<div class="price-counters">
								Сумма: <span class="cost"><?php echo $this->_tpl_vars['orderSum']['sumOrderContent'][$this->_sections['i']['index']]['cost']; ?>
</span> Руб.
							</div>
							
						</div>
					</td>
				</tr>
		<?php endfor; endif; ?>
		</table>

			<div class="total-amounts">
				<div class='details'>
					<div class="totals checkout">
						<span>Всего к оплате: </span>
						<span class="total-price"><?php echo $this->_tpl_vars['orderSum']['total']; ?>
<span class="unit">Руб.</span> </span>
					</div>
				</div>
			</div>
		</div>
		<!--<table id="cart_content_tbl" cellspacing="0" width="100%">
		<tr><td colspan="4">&nbsp;</td></tr>
	
		<?php if ($this->_tpl_vars['orderSum']['discount'] > 0): ?>
		<tr>
			<td colspan="3"><?php echo 'Скидка, %'; 
 echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['orderSum']['discount_percent'])) ? $this->_run_mod_handler('string_format', true, $_tmp, ', %0.1f%%') : smarty_modifier_string_format($_tmp, ', %0.1f%%')))) ? $this->_run_mod_handler('default', true, $_tmp, 'nbsp;') : smarty_modifier_default($_tmp, 'nbsp;')); ?>
</td>
			<td align="center">-<?php echo $this->_tpl_vars['orderSum']['discount_str']; ?>
</td>
		</tr>
		<?php endif; ?>
				<?php if ($this->_tpl_vars['orderSum']['totalTaxUC']): ?>
		<tr>
			<td colspan="3"><?php echo 'Налог'; ?>
</td>
			<td align="center"><?php echo $this->_tpl_vars['orderSum']['totalTax']; ?>
</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td id="cart_total_label" colspan="3"><?php echo 'Итого'; ?>
</td>
			<td id="cart_total" align="center"><?php echo $this->_tpl_vars['orderSum']['total']; ?>
</td>
		</tr>
		</table>-->
		
		<form action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" name='MainForm' method="POST">
		<input type="hidden" name='totalUC' value='<?php echo $this->_tpl_vars['totalUC']; ?>
' >
		<input name="action" value="process_order" type="hidden" >
<?php if ($this->_tpl_vars['orderSum']['payment_form_html']): ?>		
			<div style="padding: 20px;" class="background1"><?php echo $this->_tpl_vars['orderSum']['payment_form_html']; ?>
</div>
<?php endif; ?>		
			<p>
				<?php echo 'Комментарии к заказу (заполняется по желанию)'; ?>
:<br />
				<textarea name="order_comment" cols="50" rows="3"></textarea>
			</p>
		
		<?php if ($this->_tpl_vars['OrdersNumOverflow']): ?>
			<p><input type="submit" name="submit" value="<?php echo 'Оформить заказ'; ?>
" disabled="disabled"></p>
			<div class="error_msg_f"><?php echo ((is_array($_tmp=@TRIAL_STRING_ORDERS_OVERFLOW)) ? $this->_run_mod_handler('replace', true, $_tmp, '[NUM]', @TRIAL_MAX_ORDERS_NUM) : smarty_modifier_replace($_tmp, '[NUM]', @TRIAL_MAX_ORDERS_NUM)); ?>
</div>
			<p><?php echo @TRIAL_STRING_LIMITATIONS; ?>

		<?php else: ?>
<script type="text/javascript">
<!--
<?php echo '
disable_button = function(){
	setTimeout(
		function(){
			var button = document.getElementById(\'checkout_button\');
			if(button)button.disabled = true;
				},50);
	};
'; ?>

//-->
</script>
			<p><input type="submit" class="checkout_buttons" id="checkout_button" name="submit" value="<?php echo 'Оформить заказ'; ?>
"  
			onclick="disable_button();"></p>
		<?php endif; ?>
		
		</form>
	</td>
	
</tr></table>
	
</div>	