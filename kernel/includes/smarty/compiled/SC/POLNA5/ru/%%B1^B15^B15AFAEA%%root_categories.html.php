<?php /* Smarty version 2.6.26, created on 2014-10-16 19:05:21
         compiled from root_categories.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'root_categories.html', 9, false),array('modifier', 'escape', 'root_categories.html', 14, false),array('function', 'category_picture', 'root_categories.html', 10, false),)), $this); ?>
<div class="catalog">
	
	<?php $_from = $this->_tpl_vars['root_categories']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_fr'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_fr']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_cat']):
        $this->_foreach['_fr']['iteration']++;
?>
		<div class="catalog-delimiter">
			<?php $_from = $this->_tpl_vars['root_categories_subs'][$this->_tpl_vars['_cat']['categoryID']]; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['sub_cat_list'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sub_cat_list']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['_subcat']):
        $this->_foreach['sub_cat_list']['iteration']++;
?>
				
				<div class="block-catalog">
			
					<a href='<?php echo ((is_array($_tmp="?categoryID=".($this->_tpl_vars['_subcat']['categoryID'])."&category_slug=".($this->_tpl_vars['_subcat']['slug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
' class="img-catalog">
						<?php echo smarty_function_category_picture(array('limit' => 5,'categoryID' => $this->_tpl_vars['_subcat']['categoryID']), $this);?>

					
					
						<div class="product-name">
							<div class="title"><?php echo ((is_array($_tmp=$this->_tpl_vars['_subcat']['name'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</div>	
							<!-- <div><?php echo $this->_tpl_vars['_subcat']['countryName']; ?>
</div> -->
							<div><?php echo $this->_tpl_vars['_cat']['name']; ?>
</div>
						</div>
					</a>
				</div>
				
			<?php endforeach; endif; unset($_from); ?>
		</div>
	<?php endforeach; endif; unset($_from); ?>
	
	
	
</div>