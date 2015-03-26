<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../QN/html/cssbased/notelist_header.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', '../../../QN/html/cssbased/notelist_header.htm', 6, false),array('block', 'wbs_sortColumn', '../../../QN/html/cssbased/notelist_header.htm', 7, false),)), $this); ?>
		<?php if ($this->_tpl_vars['numDocuments']): ?>
		<table class="ListHeader" id="ListHeaderContainer">
		<tr>
				<td><input type="checkbox" name="selectAllDocsCB" onClick="treeSelectAll(this)"/></td>
				<td align=right>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['qnStrings']['qn_screen_sorting_title'])) ? $this->_run_mod_handler('cat', true, $_tmp, ":&nbsp;") : smarty_modifier_cat($_tmp, ":&nbsp;")); ?>

					<?php $this->_tag_stack[] = array('wbs_sortColumn', array('ajax' => true,'status' => $this->_tpl_vars['sorting'],'field' => 'QN_SUBJECT')); $_block_repeat=true;smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo $this->_tpl_vars['qnStrings']['app_subject_field']; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> |
					<?php $this->_tag_stack[] = array('wbs_sortColumn', array('ajax' => true,'status' => $this->_tpl_vars['sorting'],'field' => 'QN_MODIFYUSERNAME')); $_block_repeat=true;smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo $this->_tpl_vars['qnStrings']['app_author_field']; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?> |
					<?php $this->_tag_stack[] = array('wbs_sortColumn', array('ajax' => true,'status' => $this->_tpl_vars['sorting'],'field' => 'QN_MODIFYDATETIME')); $_block_repeat=true;smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); 
 echo $this->_tpl_vars['qnStrings']['app_date_field']; 
 $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_wbs_sortColumn($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

				</td>
		</tr>
		</table>
		<?php endif; ?>