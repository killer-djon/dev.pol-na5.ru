<?php /* Smarty version 2.6.26, created on 2014-10-16 18:40:21
         compiled from related_categories.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'related_categories.tpl.html', 9, false),array('modifier', 'escape', 'related_categories.tpl.html', 17, false),array('function', 'category_picture', 'related_categories.tpl.html', 12, false),)), $this); ?>
<div class="catalog">
	
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['related_categories']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php $this->assign('_subcat', ($this->_tpl_vars['related_categories'][$this->_sections['i']['index']]['category'])); ?>
		<?php $this->assign('_subcat_photo', ($this->_tpl_vars['related_categories'][$this->_sections['i']['index']]['pictures'])); ?>
		
		<div class="block-catalog">
			
			<a href='<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['_subcat']['categoryID'])."&category_slug=".($this->_tpl_vars['_subcat']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' class="img-catalog">
				
				<?php $_from = $this->_tpl_vars['_subcat_photo']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_photoID']):
?>
					<?php echo smarty_function_category_picture(array('limit' => 5,'categoryID' => $this->_tpl_vars['_photoID']), $this);?>

					
				<?php endforeach; endif; unset($_from); ?>
				
				<div class="product-name">
					<div class="title"><?php echo ((is_array($_tmp=$this->_tpl_vars['_subcat']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div>	
					<!-- <div><?php echo $this->_tpl_vars['_subcat']['countryName']; ?>
</div> -->
				</div>
			</a>
		</div>
		
	<?php endfor; endif; ?>
	
</div>