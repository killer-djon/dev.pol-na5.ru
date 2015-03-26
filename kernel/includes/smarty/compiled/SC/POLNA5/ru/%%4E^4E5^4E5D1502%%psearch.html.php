<?php /* Smarty version 2.6.26, created on 2014-10-20 02:18:52
         compiled from prfilter/psearch.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'string_repeat', 'prfilter/psearch.html', 63, false),array('modifier', 'escape', 'prfilter/psearch.html', 63, false),)), $this); ?>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/jquery.slider.js"></script>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/jquery.poshytip.js"></script>
<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/jquery.base64.min.js" charset="UTF-8"></script>
<script type="text/javascript" >
<?php echo '
	var xhr;
	$(document).on(\'change\', \'select.prfilter-psearch-category\', function() { 
		$(\'input,select,option,div,span,img, .prfilter-info-description-category, .prfilter-info-description-param, .prfilter-option-title, .prfilter-uncheck-all\').each(function(){ $(this).poshytip(\'destroy\');});
		var select = $(this), 
		categoryID = select.val(), 
		category_slug = select.find(\'option:selected\').attr(\'category_slug\'), 
		output = $(\'.prfilter-psearch-templete\'),
		currentTempleID = output.attr(\'currentTempleID\');	
		if(xhr)xhr.abort();
		select.attr(\'disabled\',true);
		output.fadeTo(\'fast\',.5); 
		xhr = $.ajax({   
			url: set_query(\'?ukey=psearch\'),
			data: "action=get_template_ajax&categoryID="+categoryID+"&category_slug="+category_slug,
			type: "POST",
			dataType : "json",
			timeout:8000,
			success: function(returnData){ 
				if(currentTempleID != returnData.templateID){
					output.attr(\'currentTempleID\',returnData.templateID);
					if(returnData.html!=\'\'){
						output.find(\'.prfilter\').unbind().removeClass(\'jsed\').removeClass(\'jsedr\');
					}
					output.html(returnData.html);
				}else{
					var priceSlider = output.find(\'div.prfilter-slider[name="price"]\');
					if(priceSlider.length>0){
						priceSlider.attr(\'to\',returnData.maxPrice);
						priceSlider.slider(\'setMax\',returnData.maxPrice);
						prfilter_CheckedSelected(priceSlider);
					}
				}
				output.fadeTo(\'fast\',1); 
				select.attr(\'disabled\',false);
			},
			error: function(){ 
				output.fadeTo(\'fast\',1); 
				select.attr(\'disabled\',false);
			},
		});
	});
'; ?>

</script>
<link href="<?php echo @URL_CSS; ?>
/prfilter.css" rel="stylesheet" type="text/css" />

<h3>
Подбор товара
<span class="small"> / Выберите материал</span>
</h3>
	
<div class="prfilter-psearch">
	<div class="prfilter-psearch-title"><?php echo 'Категория для поиска'; ?>
</div>
	<select class="prfilter-psearch-category">
	<option value='0'><?php echo 'Выберите категорию'; ?>
</option>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['categoryes']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php if ($this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['hasTemplate'] || $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['visible']): ?>
		<option value='<?php echo $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['categoryID']; ?>
' category_slug="<?php echo $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['category_slug']; ?>
" <?php if (! $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['hasTemplate']): ?>disabled<?php endif; ?>/>
			<?php echo ((is_array($_tmp="&nbsp;&nbsp;&nbsp;")) ? $this->_run_mod_handler('string_repeat', true, $_tmp, $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['level']) : smarty_modifier_string_repeat($_tmp, $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['level'])); 
 echo ((is_array($_tmp=$this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>

		</option>
		<?php endif; ?>
	<?php endfor; endif; ?>
	</select>
</div>

<div class="prfilter-psearch-templete"  currentTempleID=""></div>