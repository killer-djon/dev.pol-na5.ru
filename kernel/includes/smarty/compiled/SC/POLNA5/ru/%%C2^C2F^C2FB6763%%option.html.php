<?php /* Smarty version 2.6.26, created on 2014-12-01 18:51:15
         compiled from prfilter/option.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'prfilter/option.html', 1, false),array('modifier', 'escape', 'prfilter/option.html', 1, false),)), $this); ?>
<?php if ($this->_tpl_vars['option']['current']): 
 $this->assign('visibility', 'true'); 
 elseif ($this->_tpl_vars['slideoptions']): 
 $this->assign('visibility', 'false'); 
 else: 
 $this->assign('visibility', ($this->_tpl_vars['option']['visibility'])); 
 endif; 
 if ($this->_tpl_vars['settings']['position'] == 'center'): 
 echo smarty_function_math(array('equation' => "(x/y)-2",'x' => 100,'y' => $this->_tpl_vars['settings']['colomns'],'assign' => 'option_width'), $this);
 else: 
 echo smarty_function_math(array('equation' => "(x/y)",'x' => 100,'y' => $this->_tpl_vars['settings']['colomns'],'assign' => 'option_width'), $this);
 endif; ?>
%; -->
" current="<?php echo $this->_tpl_vars['option']['current']; ?>
" style="">	
</span></a>
">
" />
</span></a>
">
" to="<?php echo $this->_tpl_vars['option']['slider_to']; ?>
" dimension="<?php echo $this->_tpl_vars['option']['slider_prefix']; ?>
" step="1" showLimits="<?php echo $this->_tpl_vars['option']['limits']; ?>
" showLabels="<?php echo $this->_tpl_vars['option']['labels']; ?>
" class="prfilter-slider"></div>
 <input type="text" name="opprice_from" value="<?php echo $this->_tpl_vars['option']['current_from']; ?>
" slider="from" />
 <input type="text" name="opprice_to" value="<?php echo $this->_tpl_vars['option']['current_to']; ?>
" slider="to" />
	
</label></div>
</a>
</span></a>
">
">
</option>
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['variant']):
        $this->_foreach['v']['iteration']++;
?><option value="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" variant="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" <?php if ($this->_tpl_vars['variant']['current']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['variant']['option_value']; ?>
</option><?php endforeach; endif; unset($_from); ?>

if ($this->_foreach['v']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['variant']):
        $this->_foreach['v']['iteration']++;
?>
%;" recomended="<?php echo $this->_tpl_vars['variant']['recomended']; ?>
" variantID="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
">
 $this->assign('withimage', true); 
 else: 
 $this->assign('withimage', false); 
 endif; ?>
" name="op<?php echo $this->_tpl_vars['option']['optionID']; 
 if ($this->_tpl_vars['option']['type'] == 'checkbox'): ?>_<?php echo $this->_tpl_vars['variant']['variantID']; 
 endif; ?>" value="<?php if ($this->_tpl_vars['option']['type'] == 'checkbox'): ?>yep<?php elseif ($this->_tpl_vars['option']['type'] == 'radio'): 
 echo $this->_tpl_vars['variant']['variantID']; 
 endif; ?>" variant="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" id="var<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" <?php if ($this->_tpl_vars['variant']['current']): ?>checked="checked"<?php endif; ?> />		
/filters/<?php echo $this->_tpl_vars['variant']['picture']; ?>
" alt="<?php echo $this->_tpl_vars['variant']['option_value']; ?>
" title="<?php echo $this->_tpl_vars['variant']['option_value']; ?>
" /><?php endif; ?>
 echo $this->_tpl_vars['variant']['option_value']; 
 endif; ?>
"> <?php echo $this->_tpl_vars['variant']['option_value']; ?>
</label>
%;"><label><input type="radio" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
" <?php if (! $this->_tpl_vars['option']['current']): ?>checked="checked"<?php endif; ?> value="" /> <?php echo 'не важно'; ?>
</label></div><?php endif; ?>
</span></a>
">
" from="<?php echo $this->_tpl_vars['option']['slider_from']; ?>
" to="<?php echo $this->_tpl_vars['option']['slider_to']; ?>
" dimension="<?php echo ((is_array($_tmp=$this->_tpl_vars['option']['slider_prefix'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" step="<?php echo $this->_tpl_vars['option']['slider_step']; ?>
" showLimits="<?php echo $this->_tpl_vars['option']['limits']; ?>
" showLabels="<?php echo $this->_tpl_vars['option']['labels']; ?>
"  class="prfilter-slider"></div>
 <input type="text" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
_from" value="<?php echo $this->_tpl_vars['option']['current_from']; ?>
" slider="from" />
 <input type="text" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
_to" value="<?php echo $this->_tpl_vars['option']['current_to']; ?>
" slider="to" />

 $this->assign('withimage', true); 
 else: 
 $this->assign('withimage', false); 
 endif; ?>
" value="yep" variant="s_<?php echo $this->_tpl_vars['option']['optionID']; ?>
" id="s_var<?php echo $this->_tpl_vars['option']['optionID']; ?>
" <?php if ($this->_tpl_vars['option']['current']): ?>checked="checked"<?php endif; ?> />	
/filters/<?php echo $this->_tpl_vars['option']['picture']; ?>
" alt="<?php echo $this->_tpl_vars['option']['name']; ?>
" title="<?php echo $this->_tpl_vars['option']['name']; ?>
" /><?php endif; ?>
 echo $this->_tpl_vars['option']['name']; 
 endif; ?>
" optionID="<?php echo $this->_tpl_vars['option']['optionID']; ?>
"><?php echo $this->_tpl_vars['option']['name']; ?>
</label>