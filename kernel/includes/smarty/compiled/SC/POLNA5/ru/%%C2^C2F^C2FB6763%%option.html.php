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
 endif; ?><!-- width:<?php echo $this->_tpl_vars['option_width']; ?>
%; --><div class="prfilter-option" optionID="<?php echo $this->_tpl_vars['option']['optionID']; ?>
" current="<?php echo $this->_tpl_vars['option']['current']; ?>
" style="">		<?php if ($this->_tpl_vars['option']['optionID'] == 'productname'): ?>		<a href="javascript:void(0);"  class="prfilter-option-title"><i></i><span><?php echo 'По названию'; ?>
</span></a>		<div class="prfilter-variants" visibility="<?php echo $this->_tpl_vars['visibility']; ?>
">			<input type="text" name="opname" value="<?php echo $this->_tpl_vars['option']['current']; ?>
" />		</div>	<?php elseif ($this->_tpl_vars['option']['optionID'] == 'price'): ?>		<a href="javascript:void(0);"  class="prfilter-option-title"><i></i><span><?php echo 'По цене'; ?>
</span></a>		<div class="prfilter-variants prfilter-variants-slider" visibility="<?php echo $this->_tpl_vars['visibility']; ?>
">			<div class="prfilter-variant">							<div name="price" from="<?php echo $this->_tpl_vars['option']['slider_from']; ?>
" to="<?php echo $this->_tpl_vars['option']['slider_to']; ?>
" dimension="<?php echo $this->_tpl_vars['option']['slider_prefix']; ?>
" step="1" showLimits="<?php echo $this->_tpl_vars['option']['limits']; ?>
" showLabels="<?php echo $this->_tpl_vars['option']['labels']; ?>
" class="prfilter-slider"></div>				<?php echo 'от'; ?>
 <input type="text" name="opprice_from" value="<?php echo $this->_tpl_vars['option']['current_from']; ?>
" slider="from" />				<?php echo 'до'; ?>
 <input type="text" name="opprice_to" value="<?php echo $this->_tpl_vars['option']['current_to']; ?>
" slider="to" />				<?php echo $this->_tpl_vars['option']['slider_prefix']; ?>
				</div>		</div>	<?php elseif ($this->_tpl_vars['option']['optionID'] == 'instock'): ?>		<div class="prfilter-variant"><label><input type="checkbox" name="opinstock" value="yep" <?php if ($this->_tpl_vars['option']['current']): ?>checked="checked"<?php endif; ?> /> <?php echo 'В наличии'; ?>
</label></div>	<?php else: ?>			<?php if ($this->_tpl_vars['option']['optionType'] == 'simple'): ?>			<a href="javascript:void(0)" class="prfilter-shownotrecomended prfilter-notvisible"><?php echo 'все'; ?>
</a>			<a href="javascript:void(0);"  class="prfilter-option-title"><i></i><span><?php echo $this->_tpl_vars['option']['name']; ?>
</span></a>			<div class="prfilter-variants" visibility="<?php echo $this->_tpl_vars['visibility']; ?>
">				<!-- SELECT -->				<?php if ($this->_tpl_vars['option']['type'] == 'select' && $this->_tpl_vars['option']['optionType'] == 'simple'): ?>					<div class="prfilter-variant">						<select name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
">							<option value=""><?php echo 'не важно'; ?>
</option>							<?php $_from = $this->_tpl_vars['option']['variants']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['variant']):
        $this->_foreach['v']['iteration']++;
?><option value="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" variant="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" <?php if ($this->_tpl_vars['variant']['current']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['variant']['option_value']; ?>
</option><?php endforeach; endif; unset($_from); ?>						</select>					</div>				<?php else: ?>								<!-- CHECKBOX & RADIO -->					<?php echo smarty_function_math(array('equation' => "x/y",'x' => 100,'y' => $this->_tpl_vars['option']['colomns'],'assign' => 'colomn_width'), $this);?>
					<?php $_from = $this->_tpl_vars['option']['variants']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['v'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['v']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['variant']):
        $this->_foreach['v']['iteration']++;
?>						<div class="prfilter-variant <?php if (! $this->_tpl_vars['variant']['recomended'] && ! $this->_tpl_vars['variant']['checked']): ?>prfilter-notvisible<?php endif; ?>" style="width:<?php echo $this->_tpl_vars['colomn_width']; ?>
%;" recomended="<?php echo $this->_tpl_vars['variant']['recomended']; ?>
" variantID="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
">						<?php if ($this->_tpl_vars['option']['optionType'] == 'simple'): ?>							<?php if (( $this->_tpl_vars['option']['images'] == 'imagetext' || $this->_tpl_vars['option']['images'] == 'image' ) && $this->_tpl_vars['variant']['picture']): 
 $this->assign('withimage', true); 
 else: 
 $this->assign('withimage', false); 
 endif; ?>																					<label withimage="<?php if ($this->_tpl_vars['withimage']): ?>yep<?php else: ?>nope<?php endif; ?>" <?php if ($this->_tpl_vars['withimage'] && $this->_tpl_vars['variant']['current']): ?>class="selected"<?php endif; ?>>									<input type="<?php echo $this->_tpl_vars['option']['type']; ?>
" name="op<?php echo $this->_tpl_vars['option']['optionID']; 
 if ($this->_tpl_vars['option']['type'] == 'checkbox'): ?>_<?php echo $this->_tpl_vars['variant']['variantID']; 
 endif; ?>" value="<?php if ($this->_tpl_vars['option']['type'] == 'checkbox'): ?>yep<?php elseif ($this->_tpl_vars['option']['type'] == 'radio'): 
 echo $this->_tpl_vars['variant']['variantID']; 
 endif; ?>" variant="<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" id="var<?php echo $this->_tpl_vars['variant']['variantID']; ?>
" <?php if ($this->_tpl_vars['variant']['current']): ?>checked="checked"<?php endif; ?> />										<!-- IMAGE OR TEXT -->								<?php if ($this->_tpl_vars['withimage']): ?><img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/filters/<?php echo $this->_tpl_vars['variant']['picture']; ?>
" alt="<?php echo $this->_tpl_vars['variant']['option_value']; ?>
" title="<?php echo $this->_tpl_vars['variant']['option_value']; ?>
" /><?php endif; ?>								<?php if ($this->_tpl_vars['option']['images'] == 'text' || ! $this->_tpl_vars['variant']['picture']): 
 echo $this->_tpl_vars['variant']['option_value']; 
 endif; ?>							</label>								<!-- IMAGE PARAM WITHOUT IMAGE -->								<?php if ($this->_tpl_vars['option']['images'] == 'imagetext' && $this->_tpl_vars['variant']['picture']): ?>								<label class="prfilter-variant-labelimagetext" withimage="nope" for="var<?php echo $this->_tpl_vars['variant']['variantID']; ?>
"> <?php echo $this->_tpl_vars['variant']['option_value']; ?>
</label>							<?php endif; ?>						<?php endif; ?>						</div>					<?php endforeach; endif; unset($_from); ?>							<!-- RADIO LAST -->								<?php if ($this->_tpl_vars['option']['type'] == 'radio' && $this->_tpl_vars['option']['optionType'] == 'simple'): ?><div class="prfilter-variant" style="width:<?php echo $this->_tpl_vars['colomn_width']; ?>
%;"><label><input type="radio" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
" <?php if (! $this->_tpl_vars['option']['current']): ?>checked="checked"<?php endif; ?> value="" /> <?php echo 'не важно'; ?>
</label></div><?php endif; ?>				<?php endif; ?>				</div>		<?php elseif ($this->_tpl_vars['option']['optionType'] == 'slider'): ?>			<!-- SLIDER -->					<a href="javascript:void(0);"  class="prfilter-option-title"><i></i><span><?php echo $this->_tpl_vars['option']['name']; ?>
</span></a>			<div class="prfilter-variants prfilter-variants-slider" visibility="<?php echo $this->_tpl_vars['visibility']; ?>
">				<div class="prfilter-variant">						<div name="slider_<?php echo $this->_tpl_vars['option']['optionID']; ?>
" from="<?php echo $this->_tpl_vars['option']['slider_from']; ?>
" to="<?php echo $this->_tpl_vars['option']['slider_to']; ?>
" dimension="<?php echo ((is_array($_tmp=$this->_tpl_vars['option']['slider_prefix'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" step="<?php echo $this->_tpl_vars['option']['slider_step']; ?>
" showLimits="<?php echo $this->_tpl_vars['option']['limits']; ?>
" showLabels="<?php echo $this->_tpl_vars['option']['labels']; ?>
"  class="prfilter-slider"></div>					<?php echo 'от'; ?>
 <input type="text" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
_from" value="<?php echo $this->_tpl_vars['option']['current_from']; ?>
" slider="from" />					<?php echo 'до'; ?>
 <input type="text" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
_to" value="<?php echo $this->_tpl_vars['option']['current_to']; ?>
" slider="to" />					<?php echo $this->_tpl_vars['option']['slider_prefix']; ?>
				</div>			</div>		<?php else: ?>				<!-- SINGLE -->			<div class="prfilter-variant">				<?php if ($this->_tpl_vars['option']['picture'] && ( $this->_tpl_vars['option']['images'] == 'imagetext' || $this->_tpl_vars['option']['images'] == 'image' )): 
 $this->assign('withimage', true); 
 else: 
 $this->assign('withimage', false); 
 endif; ?>				<label withimage="<?php if ($this->_tpl_vars['withimage']): ?>yep<?php else: ?>nope<?php endif; ?>" <?php if ($this->_tpl_vars['withimage'] && $this->_tpl_vars['option']['current']): ?>class="selected"<?php endif; ?>>						<input type="checkbox" name="op<?php echo $this->_tpl_vars['option']['optionID']; ?>
" value="yep" variant="s_<?php echo $this->_tpl_vars['option']['optionID']; ?>
" id="s_var<?php echo $this->_tpl_vars['option']['optionID']; ?>
" <?php if ($this->_tpl_vars['option']['current']): ?>checked="checked"<?php endif; ?> />						<!-- IMAGE OR TEXT -->					<?php if ($this->_tpl_vars['withimage']): ?><img src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/filters/<?php echo $this->_tpl_vars['option']['picture']; ?>
" alt="<?php echo $this->_tpl_vars['option']['name']; ?>
" title="<?php echo $this->_tpl_vars['option']['name']; ?>
" /><?php endif; ?>					<?php if ($this->_tpl_vars['option']['images'] == 'text' || ! $this->_tpl_vars['option']['picture']): 
 echo $this->_tpl_vars['option']['name']; 
 endif; ?>				</label>				<!-- IMAGE PARAM WITHOUT IMAGE -->					<?php if ($this->_tpl_vars['option']['images'] == 'imagetext' && $this->_tpl_vars['option']['picture']): ?>					<label class="prfilter-variant-labelimagetext" withimage="nope" for="s_var<?php echo $this->_tpl_vars['option']['optionID']; ?>
" optionID="<?php echo $this->_tpl_vars['option']['optionID']; ?>
"><?php echo $this->_tpl_vars['option']['name']; ?>
</label>				<?php endif; ?>			</div>		<?php endif; ?>	<?php endif; ?></div>