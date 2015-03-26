<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../QN/html/cssbased/notelist.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'htmlsafe', '../../../QN/html/cssbased/notelist.htm', 18, false),array('modifier', 'truncate', '../../../QN/html/cssbased/notelist.htm', 18, false),array('modifier', 'cat', '../../../QN/html/cssbased/notelist.htm', 20, false),array('modifier', 'sureecho', '../../../QN/html/cssbased/notelist.htm', 31, false),array('modifier', 'strip', '../../../QN/html/cssbased/notelist.htm', 112, false),array('block', 'wbs_sortColumn', '../../../QN/html/cssbased/notelist.htm', 71, false),array('function', 'wbs_oddItem', '../../../QN/html/cssbased/notelist.htm', 83, false),)), $this); ?>
<?php if ($this->_tpl_vars['viewMode'] == 1): ?>

	<table class="List">

			<?php $this->assign('index', 0); ?>

			<?php $_from = $this->_tpl_vars['notes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['noteLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['noteLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['note_ID'] => $this->_tpl_vars['noteData']):
        $this->_foreach['noteLoop']['iteration']++;
?>
				<tr>
					<td width="10" >
						<input type="checkbox" name="document[<?php echo $this->_tpl_vars['noteData']['QN_ID']; ?>
]" value="<?php echo $this->_tpl_vars['noteData']['QN_ID']; ?>
">
						<input type=hidden name="noterights[<?php echo $this->_tpl_vars['noteData']['QN_ID']; ?>
]" value="<?php echo $this->_tpl_vars['noteData']['TREE_ACCESS_RIGHTS']; ?>
">
					</td>
					<td>
						<table class="ListItem">
						<thead>
							<tr>
								<th scope="col" class="<?php echo $this->_tpl_vars['subscrClass']; ?>
">
									<a href="<?php echo $this->_tpl_vars['noteData']['ROW_URL']; ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData']['QN_SUBJECT'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 150) : smarty_modifier_truncate($_tmp, 150)); ?>
</a>
								</th>
								<td class="AlignRight NoBreak"><?php echo ((is_array($_tmp=$this->_tpl_vars['noteData']['QN_MODIFYUSERNAME'])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')); 
 echo $this->_tpl_vars['noteData']['QN_MODIFYDATETIME']; ?>
</td>
							</tr>
						</thead>
						<tbody>

						<?php if ($this->_tpl_vars['noteData']['ATTACHEDFILES'] || $this->_tpl_vars['noteData']['QN_CONTENT']): ?>
						<tr>
							<td colspan=2>
								<?php if ($this->_tpl_vars['noteData']['QN_CONTENT']): ?>
									<span class=smallFont>
										<?php if ($this->_tpl_vars['contentLimit'] != 0): ?>
											<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData']['QN_CONTENT'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('truncate', true, $_tmp, $this->_tpl_vars['contentLimit'], "...") : smarty_modifier_truncate($_tmp, $this->_tpl_vars['contentLimit'], "...")))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>

										<?php else: ?>
											<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData']['QN_CONTENT'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>

										<?php endif; ?>
									</span>
								<?php endif; ?>
								<?php if ($this->_tpl_vars['noteData']['ATTACHEDFILES']): ?>
									<?php if ($this->_tpl_vars['noteData']['QN_CONTENT']): ?><br><?php endif; ?>
									<span class=smallFont><?php echo ((is_array($_tmp=$this->_tpl_vars['qnStrings']['qn_screen_files_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")); 
 echo $this->_tpl_vars['noteData']['ATTACHEDFILES']; ?>
</span>
								<?php endif; ?>
							</td>
						</tr>
						<?php endif; ?>
						</table>
					</td>
				</tr>
		<?php endforeach; else: ?>
			<tr class="NoRecords">
				<td colspan="3">
					<?php echo ((is_array($_tmp=$this->_tpl_vars['qnStrings']['qnp_screen_norecords_label'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>

				</td>
			</tr>
		<?php endif; unset($_from); ?>

		</table>

<?php else: ?>
		<table class="Grid">
		<thead>
			<tr>
				<?php if ($this->_tpl_vars['numDocuments']): ?>
					<th align="left" width="10px" style="width: 10px"><input type="checkbox" name="selectAllDocsCB" onClick="treeSelectAll(this)"></th>
				<?php endif; ?>

				<?php $_from = $this->_tpl_vars['visibleColumns']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['columnID']):
?>
					<?php $this->assign('nameIndex', $this->_tpl_vars['qn_columnNames'][$this->_tpl_vars['columnID']]); ?>
					<?php $this->assign('columnName', $this->_tpl_vars['qnStrings'][$this->_tpl_vars['nameIndex']]); ?>

					<?php if ($this->_tpl_vars['columnID'] != 'ATTACHEDFILES'): ?>
						<th align="left">
							<?php $this->_tag_stack[] = array('wbs_sortColumn', array('ajax' => true,'status' => $this->_tpl_vars['sorting'],'field' => $this->_tpl_vars['columnID'])); $_block_repeat=true;smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo $this->_tpl_vars['columnName']; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
						</th>
					<?php else: ?>
						<th align="left"><?php echo $this->_tpl_vars['columnName']; ?>
</th>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>

			</tr>
		</thead>
		<tbody>

			<?php $_from = $this->_tpl_vars['notes']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['noteLoop'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['noteLoop']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['note_ID'] => $this->_tpl_vars['noteData']):
        $this->_foreach['noteLoop']['iteration']++;
?>
				<tr class="<?php echo smarty_function_wbs_oddItem(array('index' => $this->_foreach['noteLoop']['iteration']), $this);?>
">
					<?php if ($this->_tpl_vars['numDocuments']): ?>
						<td align=left valign=top width="10px" style="width: 10px"><input type="checkbox" name="document[<?php echo $this->_tpl_vars['noteData']['QN_ID']; ?>
]" value="<?php echo $this->_tpl_vars['noteData']['QN_ID']; ?>
"><input type=hidden name="noterights[<?php echo $this->_tpl_vars['noteData']['QN_ID']; ?>
]" value="<?php echo $this->_tpl_vars['noteData']['TREE_ACCESS_RIGHTS']; ?>
"></td>
					<?php endif; ?>

					<?php $_from = $this->_tpl_vars['visibleColumns']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['columnID']):
?>
						<?php if ($this->_tpl_vars['columnID'] == 'QN_CONTENT'): ?>
							<td valign=top>
								<a href="<?php echo $this->_tpl_vars['noteData']['ROW_URL']; ?>
">
									<?php if ($this->_tpl_vars['contentLimit'] != 0): ?>
										<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData']['QN_CONTENT'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('truncate', true, $_tmp, $this->_tpl_vars['contentLimit'], "...") : smarty_modifier_truncate($_tmp, $this->_tpl_vars['contentLimit'], "...")))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>

									<?php else: ?>
										<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData']['QN_CONTENT'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>

									<?php endif; ?>
								</a>
							</td>
						<?php elseif ($this->_tpl_vars['columnID'] == 'ATTACHEDFILES'): ?>
							<td valign=top class=InlineLinks><?php echo ((is_array($_tmp=$this->_tpl_vars['noteData'][$this->_tpl_vars['columnID']])) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>
</td>
						<?php elseif ($this->_tpl_vars['columnID'] == 'QNF_NAME'): ?>
							<td valign=top>
								<a href="<?php echo $this->_tpl_vars['noteData']['FOLDER_URL']; ?>
">
									<?php if ($this->_tpl_vars['contentLimit'] != 0): ?>
										<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData'][$this->_tpl_vars['columnID']])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('truncate', true, $_tmp, $this->_tpl_vars['contentLimit'], "...") : smarty_modifier_truncate($_tmp, $this->_tpl_vars['contentLimit'], "...")))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>

									<?php else: ?>
										<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData'][$this->_tpl_vars['columnID']])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>

									<?php endif; ?>
								</a>
							</td>
						<?php else: ?>
							<td valign=top><a href="<?php echo $this->_tpl_vars['noteData']['ROW_URL']; ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['noteData'][$this->_tpl_vars['columnID']])) ? $this->_run_mod_handler('strip', true, $_tmp) : smarty_modifier_strip($_tmp)))) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)))) ? $this->_run_mod_handler('sureecho', true, $_tmp) : smarty_modifier_sureecho($_tmp)); ?>
</a></td>
						<?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				</tr>
			<?php endforeach; else: ?>
				<tr class="NoRecords">
						<td colspan="<?php echo $this->_tpl_vars['numVisibleColumns']; ?>
" class="default"><?php echo ((is_array($_tmp=$this->_tpl_vars['qnStrings']['qnp_screen_norecords_label'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</td>
				</tr>
			<?php endif; unset($_from); ?>
		</tbody>
		</table>
<?php endif; ?>