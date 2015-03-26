<?php /* Smarty version 2.6.26, created on 2014-10-20 01:24:13
         compiled from shopping_cart.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'shopping_cart.html', 5, false),array('modifier', 'set_query', 'shopping_cart.html', 7, false),array('modifier', 'set_query_html', 'shopping_cart.html', 15, false),array('modifier', 'escape', 'shopping_cart.html', 34, false),array('function', 'cycle', 'shopping_cart.html', 31, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>
<div id="blck-content" class="shopping-cart-content">	
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/JsHttpRequest.js"></script>

	<h1><?php echo ((is_array($_tmp=$this->_tpl_vars['CurrentDivision']['name'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</h1>
	<?php if ($this->_tpl_vars['cart_content']): ?>
		<a class="clear-cart" href='<?php echo ((is_array($_tmp="?ukey=cart&view&clear_cart=yes")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
'><?php echo 'Очистить корзину'; ?>
</a>
		
	<?php endif; ?>
	<?php echo $this->_tpl_vars['MessageBlock']; ?>

	<?php if ($this->_tpl_vars['cart_content']): ?>
	
		<div class="cart-products">
			
			<form action="<?php echo ((is_array($_tmp='?ukey=cart&view')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" name="ShoppingCartForm" method="post" target="_self">
				<input type="hidden" name="update" value="1" >
				<input type="hidden" name="shopping_cart" value="1" >
				
				
			<table id="cart_content_tbl" cellspacing="0" cellpadding="0" border="0" width="100%">
			    <colgroup>
			        <col width="20%" />
			        <col width="20%" />
			        <col width="30%" />
			        <col width="30%" />
			    </colgroup>
				
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
 list-products'>
						<td class="image-cart">
							<?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_url']): ?>
								<img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_url'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" width="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['thumbnail_width']; ?>
" />
							<?php else: ?>
								<?php if ($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['filename']): ?>
									<img src="<?php echo ((is_array($_tmp=$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['filename'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" width="85px" height="85px" />
								<?php endif; ?>
							<?php endif; ?>
						</td>
						<td class="name-cart">
							<a href='<?php echo ((is_array($_tmp="?ukey=product&productID=".($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['productID'])."&product_slug=".($this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['name']; ?>
</a>
						</td>
						<td class="count-cart">
							<div class="counts">
								<span>Количество: </span>
								<?php $this->assign('ProductsNum', $this->_tpl_vars['ProductsNum']+$this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['quantity']); ?>
								<?php if ($this->_tpl_vars['session_items']): 
 $this->assign('_prdid', $this->_tpl_vars['session_items'][$this->_sections['i']['index']]); ?>
									<?php else: 
 $this->assign('_prdid', $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['id']); ?>
								<?php endif; ?>
								<input type="hidden" name="cart_product_price" value="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['costUC']; ?>
" >
								<input class="cart_product_quantity digit" type="text" maxlength="10" name="count_<?php echo $this->_tpl_vars['_prdid']; ?>
" value="<?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['quantity']; ?>
" size="5" >  Кв.м.
							</div>
						</td>
						<td class="total-cart">
							<div class="price">
								<div class="price-counters">
									Сумма: <span class="cost"><?php echo $this->_tpl_vars['cart_content'][$this->_sections['i']['index']]['cost']; ?>
</span> Руб.
								</div>
								<div class="removed">
									<a href='<?php echo ((is_array($_tmp="?ukey=cart&view&remove=".($this->_tpl_vars['_prdid']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' title='<?php echo 'Удалить'; ?>
'>
										<img src="<?php echo @URL_IMAGES; ?>
/remove_from_basket.png" alt='<?php echo 'Удалить'; ?>
' />
									</a>
								</div>
							</div>
						</td>
					</tr>
				<?php endfor; endif; ?>
				
			</table>
		</div>
	
	
		<div class="total-amounts">
			<div class='details'>
				<div class="checkout-button">					
					<input type="submit" class="btn_checkout" name="checkout" value="" id="btn-checkout" type="submit" tabindex="1005" >
				</div>
				<div class="totals">
					<span>Всего к оплате: </span>
					<span class="total-price"><span><?php echo $this->_tpl_vars['cart_total']; ?>
</span> <span class="unit">Руб.</span> </span>
				</div>
			</div>
		</div>
		</form>
	<?php else: ?>

		<p style="text-align: center;"><?php echo 'Ваша корзина пуста'; ?>
</p>
	<?php endif; ?>

</div>

<script type="text/javascript" language="javascript">
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ! $_GET['external']): ?> 	
	<?php echo '
	function adjust_cart_window(){
		
		var wndSize = getWindowSize(parent);
		
		var scr_h = wndSize[1] - 100;
		var wnd_h = getLayer(\'blck-content\').offsetHeight + 85;
		parent.resizeFadeIFrame(null, Math.min(scr_h, wnd_h));
	}
	'; ?>

	adjust_cart_window();
	
	<?php if ($this->_tpl_vars['ProductsNum']): ?>
		parent.document.getElementById('shpcrtgc').innerHTML="<?php echo $this->_tpl_vars['ProductsNum']; ?>
 <?php echo 'продукт(ов)'; ?>
";
		parent.document.getElementById('shpcrtca').innerHTML='<?php echo $this->_tpl_vars['cart_total']; ?>
';
	<?php else: ?>
		parent.document.getElementById('shpcrtgc').innerHTML="<?php echo '(пусто)'; ?>
";
		parent.document.getElementById('shpcrtca').innerHTML="&nbsp;";
	<?php endif; ?>
<?php endif; ?>
		
	<?php if ($this->_tpl_vars['jsgoto']): ?>
		document.getElementById('btn-checkout').disabled = true;
		if (!top)closeFadeIFrame(true);
	    if (top)top.location = "<?php echo $this->_tpl_vars['jsgoto']; ?>
";
	    else document.location.href = "<?php echo $this->_tpl_vars['jsgoto']; ?>
";
	<?php endif; ?>

<?php echo '
function onApplyButtonClick()
{
    var coupon_code = document.getElementById(\'discount_coupon_code\').value;
    document.getElementById(\'wrong_coupon_lbl\').style.display = \'none\';
    document.getElementById(\'processing_coupon_lbl\').style.display = \'\';
    document.forms[\'ShoppingCartForm\'].recalculate.disabled = true;
    document.forms[\'ShoppingCartForm\'].checkout.disabled = true;
    
    var req = new JsHttpRequest();
    req.onreadystatechange = function()
    {
        if (req.readyState != 4)return;
        
        document.getElementById(\'processing_coupon_lbl\').style.display = \'none\';
        document.forms[\'ShoppingCartForm\'].recalculate.disabled = false;
        document.forms[\'ShoppingCartForm\'].checkout.disabled = false;
        if(req.responseJS.applied == \'N\')
        {
            document.getElementById(\'wrong_coupon_lbl\').style.display = \'\';
            return;
        };
        
        document.getElementById(\'coupon_form\').style.display = \'none\';
        document.getElementById(\'coupon_info\').style.display = \'\';
        document.getElementById(\'coupon_info_code\').innerHTML = coupon_code;
        document.getElementById(\'cart_total\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ! $_GET['external']): 
 echo '
            parent.document.getElementById(\'shpcrtca\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 endif; 
 echo '
        if(req.responseJS.new_coupon_show != \'\')
        {
            document.getElementById(\'coupon_discount_value\').innerHTML = req.responseJS.new_coupon_show;
        };
    };
    
    try
    {
        req.open(null, set_query(\'&ukey=cart&caller=1&initscript=ajaxservice\'), true);
        req.send({\'action\': \'try_apply_discount_coupon\', \'coupon_code\': coupon_code});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};

function onDeleteCouponClick()
{
    var req = new JsHttpRequest();
    req.onreadystatechange = function()
    {
        if (req.readyState != 4)return;
        document.getElementById(\'coupon_form\').style.display = \'\';
        document.getElementById(\'wrong_coupon_lbl\').style.display = \'none\';
        document.getElementById(\'coupon_info\').style.display = \'none\';
        document.getElementById(\'discount_coupon_code\').value = document.getElementById(\'coupon_info_code\').innerHTML; 
        document.getElementById(\'cart_total\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 if ($this->_tpl_vars['PAGE_VIEW'] == 'noframe' && ! $_GET['external']): 
 echo '
            parent.document.getElementById(\'shpcrtca\').innerHTML = req.responseJS.new_total_show_value;
        '; 
 endif; 
 echo '
    };
    
    try
    {
        req.open(null, set_query(\'&ukey=cart&caller=1&initscript=ajaxservice\'), true);
        req.send({\'action\': \'remove_doscount_coupon\'});
    }
    catch ( e )
    {
      catchResult(e);
    }
    finally { ;}
};

function noenter(event)
{
    if(event.keyCode == 13)
    {
        document.getElementById(\'discount_coupon_code\').blur();
        return false;
    };
};
 
'; ?>

</script>