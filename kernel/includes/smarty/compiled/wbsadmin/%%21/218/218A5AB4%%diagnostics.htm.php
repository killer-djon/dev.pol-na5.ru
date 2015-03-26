<?php /* Smarty version 2.6.26, created on 2014-02-24 12:51:26
         compiled from diagnostics.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'diagnostics.htm', 21, false),array('modifier', 'escape', 'diagnostics.htm', 102, false),array('modifier', 'default', 'diagnostics.htm', 145, false),array('modifier', 'strip_tags', 'diagnostics.htm', 175, false),array('modifier', 'date_format', 'diagnostics.htm', 203, false),array('modifier', 'replace', 'diagnostics.htm', 210, false),array('function', 'cycle', 'diagnostics.htm', 34, false),)), $this); ?>
<!-- diagnostic.html -->
<?php if ($this->_tpl_vars['errorStr']): ?>
<div id="message-block" class="error_block">
<?php echo $this->_tpl_vars['errorStr']; ?>

</div>
<?php endif; ?>
<?php if ($this->_tpl_vars['messageStr']): ?>
<div id="message-block" class="success_block">
<?php echo $this->_tpl_vars['messageStr']; ?>

</div>
<?php endif; ?>

<?php if (! $this->_tpl_vars['fatalError']): ?>


<?php if ($this->_tpl_vars['diagnosticResult']): ?>
<table cellspacing="0" cellpadding="5" border="0">
<?php $_from = $this->_tpl_vars['diagnosticResult']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['GroupID'] => $this->_tpl_vars['diagnosticResultGroup']):
?>
<tr><td colspan="3" class="diagnostics-group-header"><?php echo ((is_array($_tmp="test_section_".($this->_tpl_vars['GroupID']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td></tr>
<?php $_from = $this->_tpl_vars['diagnosticResultGroup']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['subGroup'] => $this->_tpl_vars['diagnosticResultGroupItem']):
?>
<tr class="diagnostics-subgroup-header">
<td colspan="3" title="<?php echo $this->_tpl_vars['subGroup']; ?>
">

<img 
src="../classic/images/<?php if ($this->_tpl_vars['diagnosticResultGroupItem']['result']): ?>success<?php else: ?>failed<?php endif; ?>.gif" 
alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['diagnosticResultGroupItem']['value'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" 
title="<?php echo ((is_array($_tmp=$this->_tpl_vars['diagnosticResultGroupItem']['value'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
">

<?php echo ((is_array($_tmp=$this->_tpl_vars['diagnosticResultGroupItem']['description'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
</tr>
<?php $_from = $this->_tpl_vars['diagnosticResultGroupItem']['data']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['param'] => $this->_tpl_vars['result']):
?>
<tr class="list-item  <?php echo smarty_function_cycle(array('values' => "background1,background2",'name' => 'lines'), $this);?>
">
	<td class="diagnostic-param"><?php echo ((is_array($_tmp=$this->_tpl_vars['param'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
	<?php if ($this->_tpl_vars['result']['result'] === -2): ?>
	<td colspan="2"><?php echo $this->_tpl_vars['result']['value']; ?>
</td>
	<?php else: ?>
	<td>
	<?php echo ''; 
 if ($this->_tpl_vars['result']['result'] === 1 || $this->_tpl_vars['result']['result'] === 2): 
 echo '<img src="../../../common/html/classic/images/checked.gif" alt="'; 
 echo ((is_array($_tmp='test_success')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '">'; 
 elseif ($this->_tpl_vars['result']['result'] === 0): 
 echo '<img src="../classic/images/failed.gif" alt="'; 
 echo ((is_array($_tmp='test_failed')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo '">'; 
 else: 
 echo '&nbsp;'; 
 endif; 
 echo ''; 
 echo $this->_tpl_vars['result']['value']; 
 echo ''; ?>

	</td>
	<td class="comment"><?php if ($this->_tpl_vars['result']['result'] == 1): ?>&nbsp;<?php else: 
 echo ((is_array($_tmp=$this->_tpl_vars['result']['info'])) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
&nbsp;<?php endif; ?></td>
	<?php endif; ?>
</tr>
<?php endforeach; endif; unset($_from); ?>
<tr><td colspan="3">&nbsp;</td></tr>
<?php endforeach; endif; unset($_from); ?>
<?php endforeach; endif; unset($_from); ?>
</table>
<?php endif; ?>




<?php if ($this->_tpl_vars['logsInfo']): ?>
<table class="list" style="width:auto;">
<tr>
<td>&nbsp;</td>
<td><?php echo ((is_array($_tmp='diagnostic_file_name')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
<td><?php echo ((is_array($_tmp='diagnostic_file_size')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
<td><?php echo ((is_array($_tmp='diagnostic_file_permision')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
<td colspan="4"><?php echo ((is_array($_tmp='diagnostic_file_action')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</td>
</tr>
<?php $_from = $this->_tpl_vars['logsInfo']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['logsInfoName'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['logsInfoName']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['logsInfoItem']):
        $this->_foreach['logsInfoName']['iteration']++;
?>
<tr class="list-item <?php echo smarty_function_cycle(array('values' => "background1,background2",'name' => 'lines'), $this);
 if ($this->_tpl_vars['logsInfoItem']['id'] == $this->_tpl_vars['id']): ?> selected<?php endif; ?>">
<td><?php if ($this->_tpl_vars['logsInfoItem']['icon']): ?><img src="<?php echo $this->_tpl_vars['logsInfoItem']['icon']['src']; ?>
" alt="<?php echo $this->_tpl_vars['logsInfoItem']['icon']['alt']; ?>
" height="16" width="16"><?php endif; ?></td>
	<td title="<?php echo $this->_tpl_vars['logsInfoItem']['fullpath']; ?>
" style="pointer:hand;">
		<a href="?section=<?php echo $this->_tpl_vars['section']; ?>
&amp;action=view&amp;id=<?php echo $this->_tpl_vars['logsInfoItem']['id']; ?>
">
			<?php echo $this->_tpl_vars['logsInfoItem']['name']; ?>

		</a>
		<span class="file-description">&nbsp;<?php echo $this->_tpl_vars['logsInfoItem']['description']; ?>
</span>
	</td>

	<td title="<?php echo ((is_array($_tmp='diagnostic_file_download')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" align="right">
		<?php echo $this->_tpl_vars['logsInfoItem']['printSize']; ?>

	</td>

	<td align="right" style="color:<?php if ($this->_tpl_vars['logsInfoItem']['writable']): ?>green<?php else: ?>red<?php endif; ?>"><?php echo $this->_tpl_vars['logsInfoItem']['perm']; ?>
</td>

	<td class="action_link">
	<a href="?section=<?php echo $this->_tpl_vars['section']; ?>
&amp;action=view&amp;id=<?php echo $this->_tpl_vars['logsInfoItem']['id']; ?>
" title="<?php echo ((is_array($_tmp='diagnostic_file_view')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" style="font-weight: bold;"><?php echo ((is_array($_tmp='diagnostic_file_view')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
	</td>

	<td class="action_link">
	<a href="?section=<?php echo $this->_tpl_vars['section']; ?>
&amp;action=download&amp;id=<?php echo $this->_tpl_vars['logsInfoItem']['id']; ?>
" title="<?php echo ((is_array($_tmp='diagnostic_file_download')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php echo ((is_array($_tmp='diagnostic_file_download')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
	</td>
	<td class="action_link">
		<?php if ($this->_tpl_vars['allowDelete'] || true): ?>
		<a href="?section=<?php echo $this->_tpl_vars['section']; ?>
&amp;action=delete&amp;id=<?php echo $this->_tpl_vars['logsInfoItem']['id']; ?>
" onclick="return confirm('<?php echo ((is_array($_tmp=((is_array($_tmp='diagnostic_file_confirm_delete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
')" title="<?php echo ((is_array($_tmp='diagnostic_file_delete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php echo ((is_array($_tmp='diagnostic_file_delete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
		<?php else: ?>
		&nbsp;
		<?php endif; ?>
	</td>
	<td class="action_link">
		<?php if (true || $this->_tpl_vars['logsInfoItem']['id'] && $this->_tpl_vars['allowEdit']): ?>
		<a href="?section=<?php echo $this->_tpl_vars['section']; ?>
&amp;action=rename&amp;id=<?php echo $this->_tpl_vars['logsInfoItem']['id']; ?>
" title="<?php echo ((is_array($_tmp='diagnostic_file_rotate')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php echo ((is_array($_tmp='diagnostic_file_rotate')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
		<?php else: ?>
		&nbsp;
		<?php endif; ?>
	</td>
</tr>
<?php endforeach; endif; unset($_from); ?>
</table>
<?php endif; ?>





<?php if ($this->_tpl_vars['directoryContent']): ?>
<form method="post" action="?section=<?php echo $this->_tpl_vars['section']; ?>
" name="file-manager"> 
<input type="hidden" name="action" value="chmod">
<input type="hidden" name="path" value="<?php echo $this->_tpl_vars['directoryContent']['fullpath']; ?>
">
<table class="list" cellspacing="2" cellpadding="1" width="100%" border="0">
<thead>
	<tr>
		<th colspan="8" align="left">
		<?php $this->assign('fullpathName', ""); ?>
		<?php $_from = $this->_tpl_vars['directoryContent']['path']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_navigator'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_navigator']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['path']):
        $this->_foreach['_navigator']['iteration']++;
?>
		<?php $this->assign('fullpathName', ($this->_tpl_vars['fullpathName']).($this->_tpl_vars['path']['name'])); ?>
		<?php if (($this->_foreach['_navigator']['iteration'] == $this->_foreach['_navigator']['total'])): ?>
		<?php echo $this->_tpl_vars['path']['name']; ?>

		<?php else: ?>
		<a class="path-navigator" href="?section=filemanager&amp;path=<?php echo $this->_tpl_vars['path']['encoded']; ?>
" title="<?php echo $this->_tpl_vars['fullpathName']; ?>
"><?php echo $this->_tpl_vars['path']['name']; ?>
</a> &rarr;
		<?php $this->assign('upLevel', "?section=filemanager&amp;path=".($this->_tpl_vars['path']['encoded'])); ?>
		<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
		<span class="file-description"><?php echo ((is_array($_tmp=@$this->_tpl_vars['directoryContent']['description'])) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</span>
		</th>
		<th>
		<?php if ($this->_tpl_vars['upLevel']): ?>
		<a href="<?php echo $this->_tpl_vars['upLevel']; ?>
"><?php echo ((is_array($_tmp='diagnostic_up_one_level')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
		<?php else: ?>
		<?php endif; ?>
		</th>
	</tr>

	<tr>
	<td colspan="9">&nbsp;</td>
	</tr>


	<tr class="formSection">
		<th>&nbsp;</th>
		<th align="left"><?php echo ((is_array($_tmp='diagnostic_file_name')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		<span class="file-description" style="float:right;display:inline;"><?php echo ((is_array($_tmp=((is_array($_tmp='diagnostic_file_description')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</span></th>
		<th><?php echo ((is_array($_tmp='diagnostic_file_size')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
		<th colspan="3"><?php echo ((is_array($_tmp='diagnostic_file_action')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>		
		<th align="center"><?php echo ((is_array($_tmp='diagnostic_file_owner')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <span class="file-owner-id">(UID)</span></th>
		<th align="center"><?php echo ((is_array($_tmp='diagnostic_file_ownergroup')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <span class="file-owner-id">(GID)</span></th>
		<th align="left"><?php echo ((is_array($_tmp='diagnostic_file_permision')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</th>
	</tr>
</thead>

<?php $_from = $this->_tpl_vars['directoryContent']['dirs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_dirs'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_dirs']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['directory']):
        $this->_foreach['_dirs']['iteration']++;
?>
<tr class="list-item <?php echo smarty_function_cycle(array('values' => "background1,background2",'name' => 'lines'), $this);?>
" valign="top">
<td><a href="?section=filemanager&amp;path=<?php echo $this->_tpl_vars['directory']['encodedpath']; ?>
"><?php if ($this->_tpl_vars['directory']['icon']): ?><img src="<?php echo $this->_tpl_vars['directory']['icon']['src']; ?>
" alt="<?php echo $this->_tpl_vars['directory']['icon']['alt']; ?>
" title="<?php echo ((is_array($_tmp='diagnostic_folder_view')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php endif; ?></a></td>
<td><a href="?section=filemanager&amp;path=<?php echo $this->_tpl_vars['directory']['encodedpath']; ?>
" title="<?php if ($this->_tpl_vars['directory']['description']): 
 echo ((is_array($_tmp=$this->_tpl_vars['directory']['description'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); 
 else: 
 echo ((is_array($_tmp=((is_array($_tmp='diagnostic_folder_view')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); 
 endif; ?>"><?php echo $this->_tpl_vars['directory']['name']; ?>
</a>
<span class="file-description">&nbsp;<?php echo $this->_tpl_vars['directory']['description']; ?>
</span></td>
<td align="right"><?php echo $this->_tpl_vars['directory']['size']; ?>
</td>
<td colspan="3">&nbsp;</td>
<td align="center" style="white-space:nowrap;"><?php echo $this->_tpl_vars['directory']['owner']['name']; ?>
 <span class="file-owner-id">(<?php echo $this->_tpl_vars['directory']['owner']['uid']; ?>
)</span></td>
<td align="center" style="white-space:nowrap;"><?php echo $this->_tpl_vars['directory']['owner']['gname']; ?>
 <span class="file-owner-id">(<?php echo $this->_tpl_vars['directory']['owner']['gid']; ?>
)</span></td>
<td>
	<div style="display:block;white-space:nowrap;" class="input-enabled">
		<span style="color:<?php if ($this->_tpl_vars['directory']['writable']): ?>green<?php else: ?>red<?php endif; ?>"><?php echo $this->_tpl_vars['directory']['perm']; ?>
</span>
		&nbsp;<a href="#" onclick="var block=(this.parentNode).parentNode;invertDisplayForChildren(block);changeVisibilityByClasses('input-enabled-master','input-disabled-master');return false;" style="font-size: 85%;"><i><?php echo ((is_array($_tmp='diagnostic_file_edit_permision')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i></a>
	</div>
	<div style="display:none;white-space:nowrap;" class="input-disabled">
		<input disabled="disabled" class="individual" type="text" size="3" maxlength="3" name="path_<?php echo $this->_tpl_vars['directory']['encodedpath']; ?>
" value="<?php echo $this->_tpl_vars['directory']['perm']; ?>
">
		<a href="#" onclick="focusControl('chmod');return false;" style="font-size: 85%;" title="<?php echo ((is_array($_tmp='diagnostic_file_edit_permision_link_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"><?php echo ((is_array($_tmp='diagnostic_file_edit_permision_link')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
		<br>
		<input disabled="disabled" class="individual item-checkbox" name="recursive_<?php echo $this->_tpl_vars['directory']['encodedpath']; ?>
" id="item_recursive_<?php echo $this->_tpl_vars['directory']['id']; ?>
" type="checkbox" value="1" onClick="groupCheckBox('master-checkbox','item-checkbox')">
		<label for="item_recursive_<?php echo $this->_tpl_vars['directory']['id']; ?>
" style="font-size: 85%;"><?php echo ((is_array($_tmp='diagnostic_file_edit_permision_recursive')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label>
					
	</div>
</td>
</tr>
<?php endforeach; endif; unset($_from); ?>

<?php $_from = $this->_tpl_vars['directoryContent']['files']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['_files'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['_files']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['file']):
        $this->_foreach['_files']['iteration']++;
?>
<tr class="list-item <?php echo smarty_function_cycle(array('values' => "background1,background2",'name' => 'lines'), $this);?>
" valign="top">
<td <?php if ($this->_tpl_vars['file']['version']): 
 if ($this->_tpl_vars['file']['version']['mtime']): ?>title="<?php echo ((is_array($_tmp=$this->_tpl_vars['file']['version']['mtime'])) ? $this->_run_mod_handler('date_format', true, $_tmp, '%H:%M:%S %m.%Y') : smarty_modifier_date_format($_tmp, '%H:%M:%S %m.%Y')); ?>
"<?php endif; 
 if ($this->_tpl_vars['file']['version']['color']): ?> style="background-color:<?php echo $this->_tpl_vars['file']['version']['color']; ?>
;font-weight:bolder;cursor:default;"<?php endif; 
 endif; ?>>
	<?php if ($this->_tpl_vars['file']['icon']): ?><img src="<?php echo $this->_tpl_vars['file']['icon']['src']; ?>
" alt="<?php echo $this->_tpl_vars['file']['icon']['alt']; ?>
" height="16" width="16"><?php else: ?>&nbsp;<?php endif; ?>
</td>
<td>
	
	<?php $this->assign('defaultAction', $this->_tpl_vars['file']['allowedactions']['default']); ?>
	<?php if ($this->_tpl_vars['defaultAction']['link']): ?>
		<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['defaultAction']['link'])) ? $this->_run_mod_handler('replace', true, $_tmp, "%url%", "?section=".($this->_tpl_vars['section'])) : smarty_modifier_replace($_tmp, "%url%", "?section=".($this->_tpl_vars['section']))); ?>
" title="<?php echo ((is_array($_tmp=((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['defaultAction']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)); ?>
" target="<?php echo $this->_tpl_vars['defaultAction']['target']; ?>
">
			<?php echo $this->_tpl_vars['file']['name']; ?>

		</a>
	<?php else: ?>
		<?php echo $this->_tpl_vars['file']['name']; ?>

	<?php endif; ?>
	<span class="file-description">&nbsp;<?php echo $this->_tpl_vars['file']['description']; ?>
</span>
</td>
<td align="right">
	<?php echo $this->_tpl_vars['file']['printSize']; ?>

</td>
<?php $this->assign('allowedActions', $this->_tpl_vars['file']['allowedactions']['default']); ?>
<td class="action_link">
	<?php if ($this->_tpl_vars['allowedActions']['link']): ?>
	<b>
		<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['allowedActions']['link'])) ? $this->_run_mod_handler('replace', true, $_tmp, "%url%", "?section=".($this->_tpl_vars['section'])) : smarty_modifier_replace($_tmp, "%url%", "?section=".($this->_tpl_vars['section']))); ?>
" title="<?php echo ((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['allowedActions']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" target="<?php echo $this->_tpl_vars['allowedActions']['target']; ?>
">
			<?php echo ((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['allowedActions']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
	</b>
	<?php else: ?>
		&nbsp;
	<?php endif; ?>
</td>
<?php if ($this->_tpl_vars['allowedActions']['name'] == 'download'): ?>
<td class="action_link">&nbsp;</td>
<?php else: ?>
<?php $this->assign('allowedActions', $this->_tpl_vars['file']['allowedactions']['download']); ?>
<td class="action_link">
	<?php if ($this->_tpl_vars['allowedActions']['link']): ?>
		<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['allowedActions']['link'])) ? $this->_run_mod_handler('replace', true, $_tmp, "%url%", "?section=".($this->_tpl_vars['section'])) : smarty_modifier_replace($_tmp, "%url%", "?section=".($this->_tpl_vars['section']))); ?>
" title="<?php echo ((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['allowedActions']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" target="<?php echo $this->_tpl_vars['allowedActions']['target']; ?>
">
			<?php echo ((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['allowedActions']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
	<?php else: ?>
		&nbsp;
	<?php endif; ?>
</td>
<?php endif; ?>
<?php $this->assign('allowedActions', $this->_tpl_vars['file']['allowedactions']['delete']); ?>
<td class="action_link">
	<?php if ($this->_tpl_vars['allowedActions']['link']): ?>
		<a href="<?php echo ((is_array($_tmp=$this->_tpl_vars['allowedActions']['link'])) ? $this->_run_mod_handler('replace', true, $_tmp, "%url%", "?section=".($this->_tpl_vars['section'])) : smarty_modifier_replace($_tmp, "%url%", "?section=".($this->_tpl_vars['section']))); ?>
"  onclick="return confirm('<?php echo ((is_array($_tmp=((is_array($_tmp='diagnostic_file_confirm_delete')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)))) ? $this->_run_mod_handler('escape', true, $_tmp, 'quotes') : smarty_modifier_escape($_tmp, 'quotes')); ?>
')" title="<?php echo ((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['allowedActions']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
" target="<?php echo $this->_tpl_vars['allowedActions']['target']; ?>
">
			<?php echo ((is_array($_tmp="diagnostic_file_".($this->_tpl_vars['allowedActions']['name']))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
	<?php else: ?>
		&nbsp;
	<?php endif; ?>
</td>
 
<td align="center" style="white-space:nowrap;"><?php echo $this->_tpl_vars['file']['owner']['name']; ?>
<span class="file-owner-id"> (<?php echo $this->_tpl_vars['file']['owner']['uid']; ?>
)</span></td>
<td align="center" style="white-space:nowrap;"><?php echo $this->_tpl_vars['file']['owner']['gname']; ?>
<span class="file-owner-id"> (<?php echo $this->_tpl_vars['file']['owner']['uid']; ?>
)</span></td>
<td>
	<div style="display:block" class="input-enabled">
		<span style="color:<?php if ($this->_tpl_vars['file']['writable']): ?>green<?php else: ?>red<?php endif; ?>"><?php echo $this->_tpl_vars['file']['perm']; ?>
</span>
		&nbsp;<a href="#" onclick="var block=(this.parentNode).parentNode;invertDisplayForChildren(block);changeVisibilityByClasses('input-enabled-master','input-disabled-master');return false;" style="font-size: 85%;"><i><?php echo ((is_array($_tmp='diagnostic_file_edit_permision')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i></a>
	</div>
	<div style="display:none" class="input-disabled">
		<input disabled="disabled" class="individual" type="text" size="3
		" maxlength="3" name="path_<?php echo $this->_tpl_vars['file']['encodedpathname']; ?>
" value="<?php echo $this->_tpl_vars['file']['perm']; ?>
">
		<a href="#" onclick="focusControl('chmod');return false;" style="font-size: 85%;" title="<?php echo ((is_array($_tmp='diagnostic_file_edit_permision_link_title')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
">
			<?php echo ((is_array($_tmp='diagnostic_file_edit_permision_link')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

		</a>
	</div>
</td>
</tr>

<?php endforeach; endif; unset($_from); ?>
<tr>
<td>&nbsp;</td>
<td colspan="5"><?php echo ((is_array($_tmp='diagnostic_files_count')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:&nbsp;<?php echo $this->_foreach['_files']['total']; ?>
&nbsp;&nbsp;&nbsp;&nbsp;<?php echo ((is_array($_tmp='diagnostic_folders_count')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
:&nbsp;<?php echo $this->_foreach['_dirs']['total']; ?>
</td>
<td colspan="2"></td>
<td>
	<div style="display:block" class="input-enabled-master">
		<a href="#" onclick="var block=(this.parentNode).parentNode;invertDisplayForChildren(block);changeVisibilityByClasses('input-enabled','input-disabled');return false;" style="font-weight: bold;"><i><?php echo ((is_array($_tmp='diagnostic_file_bulk_chmod')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</i></a>
	</div>
	<div style="display:none" class="input-disabled-master">
		<input type="submit" name="chmod" value="<?php echo ((is_array($_tmp='diagnostic_file_save_permision')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
">
	</div>
</td>
</tr>
</table>
</form>

<?php endif; ?>


<?php endif; ?>

<?php if ($this->_tpl_vars['file2view']): ?>
<div style="display:none;" id="modal-window">
	<form method="post" action="?section=<?php echo $this->_tpl_vars['section']; ?>
" name="file-editor">
		<input type="hidden" name="path" value="<?php echo $this->_tpl_vars['directoryContent']['fullpath']; ?>
">
		<span id="modal-window-title"><?php echo $this->_tpl_vars['file2view']['fullpath']; ?>
</span>
		<textarea id="modal-window-content" <?php if (! $this->_tpl_vars['file2view']['editable']): ?>readonly="readonly" <?php endif; ?>name="content"><?php echo ((is_array($_tmp=$this->_tpl_vars['file2view']['content'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
</textarea>
		<div id="modal-window-control-bar">
		
			<?php if ($this->_tpl_vars['file2view']['navigator']): ?><span style="font-size: 80%;"><?php echo $this->_tpl_vars['file2view']['navigator']; ?>
 &nbsp;&nbsp;|&nbsp;&nbsp;</span><?php endif; ?>
			
			<?php if ($this->_tpl_vars['file2view']['editable'] && ! $this->_tpl_vars['file2view']['navigator']): ?>
			
				<input type="submit" name="save" value="<?php echo ((is_array($_tmp='btn_save')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
">
				<input type="hidden" name="action" value="save">
				<input type="hidden" name="id" value="<?php echo $this->_tpl_vars['file2view']['id']; ?>
">
			<?php elseif ($this->_tpl_vars['file2view']['editable']): ?>
			
				<em style="font-size: 80%;"><?php echo ((is_array($_tmp='diagnostic_file_too_big')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</em>
			<?php endif; ?>
			<a name="modal-window-close-button" id="modal-window-close-button" style="cursor: pointer; font-size: 80%; text-decoration: underline;"><?php echo ((is_array($_tmp='btn_close')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
		</div>
	</form>	
</div>

<script type="text/javascript">
<!--
var Dialog = new modalWindow();
Dialog.show('modal-window','modal-window-title','modal-window-content','modal-window-control-bar','modal-window-close-button');
//-->
</script>
<?php endif; ?>



<?php if ($this->_tpl_vars['section'] == 'cache'): ?>
<form method="post" action="?section=<?php echo $this->_tpl_vars['section']; ?>
" name="cache-manager">

<input type="hidden" name="action" value="resetcache">
<table class="list" cellspacing="0" cellpadding="1" border="0">

<tr class="list-item background1">
	<td valign="top"><input type="checkbox" name="system" value="1" id="system" checked="checked"></td>
	<td><label for="system"><?php echo ((is_array($_tmp='diagnostic_reset_cache_system')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
	<span class="cache-description"><?php echo ((is_array($_tmp='diagnostic_reset_cache_system_desc')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span></td>
</tr>
<tr class="list-item background1">
	<td valign="top"><input type="checkbox" name="smarty" value="1" id="smarty" checked="checked"></td>
	<td><label for="smarty"><?php echo ((is_array($_tmp='diagnostic_reset_cache_smarty')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
	<span class="cache-description"><?php echo ((is_array($_tmp='diagnostic_reset_cache_smarty_desc')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span></td>
</tr>
<tr class="list-item background1">
	<td valign="top"><input type="checkbox" name="localization" value="1" id="localization" checked="checked"></td>
	<td><label for="localization"><?php echo ((is_array($_tmp='diagnostic_reset_cache_localization')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
	<span class="cache-description"><?php echo ((is_array($_tmp='diagnostic_reset_cache_localization_desc')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span></td>	
</tr>
<tr class="list-item background1">
	<td valign="top"><input type="checkbox" name="updatestate" value="1" id="updatestate" checked="checked"></td>
	<td><label for="updatestate"><?php echo ((is_array($_tmp='diagnostic_reset_cache_updatestate')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</label><br>
	<span class="cache-description"><?php echo ((is_array($_tmp='diagnostic_reset_cache_updatestate_desc')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span></td>	
</tr>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
<tr><td colspan="2"><input type="submit" value="<?php echo ((is_array($_tmp='diagnostic_reset_cache')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
"></td></tr>
</table>
</form>
<?php endif; ?>
<!-- /diagnostic.html -->