<?php /* Smarty version 2.6.26, created on 2014-12-01 18:51:15
         compiled from prfilter/lnrside.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'prfilter/lnrside.html', 1, false),array('modifier', 'replace', 'prfilter/lnrside.html', 1, false),array('modifier', 'set_query_html', 'prfilter/lnrside.html', 1, false),array('modifier', 'string_repeat', 'prfilter/lnrside.html', 1, false),array('modifier', 'escape', 'prfilter/lnrside.html', 1, false),)), $this); ?>
<?php if ($this->_tpl_vars['prfilter']['template'] || ( ! $this->_tpl_vars['settings']['category'] && $this->_tpl_vars['settings']['isindex'] )): ?>	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/jquery.slider.js"></script>	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/jquery.poshytip.js"></script>	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/jquery.base64.min.js" charset="UTF-8"></script>	<?php $this->assign('prfilter_founded', ((is_array($_tmp=((is_array($_tmp='prfilter_founded')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('replace', true, $_tmp, '"', '\"') : smarty_modifier_replace($_tmp, '"', '\"'))); ?>	<script type="text/javascript" >		translate.founded = "<?php echo $this->_tpl_vars['prfilter_founded']; ?>
";		translate.nofounded = '<?php echo 'К сожалению, ничего не найдено.'; ?>
';		translate.unckeck = '<?php echo 'Отменить все параметры характеристики'; ?>
';		translate.hidetip = '<?php echo 'Закрыть'; ?>
';		translate.all = '<?php echo 'все'; ?>
';		translate.recomended = '<?php echo 'популярные'; ?>
';	</script>	<?php if (! $this->_tpl_vars['ajax']): ?><link href="<?php echo @URL_CSS; ?>
/prfilter.css" rel="stylesheet" type="text/css" /><?php endif; ?>		<script type="text/javascript" src="<?php echo @URL_JS; ?>
/prfilter/prfilter.js"></script>	<?php if (! $this->_tpl_vars['ajaxindex']): ?>	<div class="prfilter prfilter-<?php echo $this->_tpl_vars['settings']['position']; ?>
" position="<?php echo $this->_tpl_vars['settings']['position']; ?>
" colomns="<?php echo $this->_tpl_vars['settings']['colomns']; ?>
" excluded="<?php echo $this->_tpl_vars['prfilter']['excluded']; ?>
" descriptions="<?php echo $this->_tpl_vars['prfilter']['descriptions']; ?>
" descriptions_params="<?php echo $this->_tpl_vars['prfilter']['descriptions_params']; ?>
">			<form method="get" action='<?php echo ((is_array($_tmp="?")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' class="prfilter-form" >		<input type="hidden" name="category_slug" value="<?php echo $this->_tpl_vars['prfilter']['category_slug']; ?>
" />		<input type="hidden" name="psearch" value="yep" />		<?php if ($this->_tpl_vars['settings']['isindex']): ?><input type="hidden" name="ukey" value="category" /><?php endif; ?>		<?php if ($this->_tpl_vars['settings']['isselectcategory']): ?>			<div class="prfilter-options ">				<div class="prfilter-category-select-title"><?php echo 'Категория для поиска'; ?>
</div>				<select name="categoryID" class="<?php if (! $this->_tpl_vars['settings']['template']): ?>prfilter-category-select-notemplate<?php else: ?>prfilter-category-select<?php endif; ?>">				<option value='0'><?php echo 'Выберите категорию'; ?>
</option>				<?php unset($this->_sections['i']);
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
?>					<?php if ($this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['hasTemplate'] || $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['visible']): ?>					<option value='<?php echo $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['categoryID']; ?>
' category_slug="<?php echo $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['category_slug']; ?>
" <?php if (! $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['hasTemplate']): ?>disabled<?php endif; ?>/>						<?php echo ((is_array($_tmp="&nbsp;&nbsp;&nbsp;")) ? $this->_run_mod_handler('string_repeat', true, $_tmp, $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['level']) : smarty_modifier_string_repeat($_tmp, $this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['level'])); 
 echo ((is_array($_tmp=$this->_tpl_vars['categoryes'][$this->_sections['i']['index']]['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
					</option>					<?php endif; ?>				<?php endfor; endif; ?>				</select>			</div>		<?php else: ?>			<input type="hidden" name="categoryID" value="<?php echo $this->_tpl_vars['prfilter']['categoryID']; ?>
" />		<?php endif; ?>	<?php endif; ?>	<?php if ($this->_tpl_vars['settings']['isselectcategory'] && ! $this->_tpl_vars['settings']['template']): ?>		<div class="prfilter-templete-ajax" currentTempleID=""></div>	<?php else: ?>		<?php if ($this->_tpl_vars['prfilter']['groupbycategory']): ?>			<?php $_from = $this->_tpl_vars['prfilter']['template']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['c'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['c']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['pcategoryID'] => $this->_tpl_vars['category']):
        $this->_foreach['c']['iteration']++;
?>			<div class="prfilter-category">					<a href="javascript:void(0);"  class="prfilter-category-title"><span><?php echo $this->_tpl_vars['category']['name']; ?>
</span></a>				<div class="prfilter-options <?php if ($this->_tpl_vars['prfilter']['slidecategoryes'] || $this->_tpl_vars['category']['visibility'] == 'false'): ?>prfilter-notvisible<?php endif; ?>"  >				<?php $_from = $this->_tpl_vars['category']['options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['o'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['o']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['option']):
        $this->_foreach['o']['iteration']++;
?>							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "prfilter/option.html", 'smarty_include_vars' => array('option' => $this->_tpl_vars['option'],'slideoptions' => $this->_tpl_vars['prfilter']['slideoptions'],'settings' => $this->_tpl_vars['settings'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>					<?php endforeach; endif; unset($_from); ?>				</div>			</div>			<?php endforeach; endif; unset($_from); ?>		<?php else: ?>			<div class="prfilter-options filter-block">			<?php $_from = $this->_tpl_vars['prfilter']['template']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['c'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['c']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['pcategoryID'] => $this->_tpl_vars['category']):
        $this->_foreach['c']['iteration']++;
?>				<?php $_from = $this->_tpl_vars['category']['options']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['o'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['o']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['option']):
        $this->_foreach['o']['iteration']++;
?>							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "prfilter/option.html", 'smarty_include_vars' => array('option' => $this->_tpl_vars['option'],'slideoptions' => $this->_tpl_vars['prfilter']['slideoptions'],'settings' => $this->_tpl_vars['settings'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>					<?php endforeach; endif; unset($_from); ?>			<?php endforeach; endif; unset($_from); ?>			</div>		<?php endif; ?>	<?php endif; ?>	<?php if (! $this->_tpl_vars['settings']['isselectcategory'] || ( $this->_tpl_vars['settings']['isselectcategory'] && $this->_tpl_vars['settings']['template'] )): ?>		<div class="prfilter-submit">			<input type="submit" value="<?php echo 'Найти продукты'; ?>
" />			<a href="javascript:void(0);" class="prfilter-uncheck-all"><?php echo 'Отменить все'; ?>
</a>		</div>	<?php endif; ?>	<?php if (! $this->_tpl_vars['ajaxindex']): ?>		</form>	</div>	<?php endif; ?><?php endif; ?>