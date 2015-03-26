<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../common/html/cssbased/pageelements/tree_page_list.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', '../../../common/html/cssbased/pageelements/tree_page_list.htm', 13, false),)), $this); ?>
<table id="TreePageListTable">
	<thead>
		<tr>
			<td class="<?php if ($this->_tpl_vars['showFolderID']): ?>treeNoBottomPadding<?php endif; ?>" valign=middle>
				<?php if ($this->_tpl_vars['ajax']): ?>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/ajax/list_pages.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php else: ?>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/list_pages.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php endif; ?>
			</td>
			<?php if ($this->_tpl_vars['thisFolderRights'] != ""): ?>
				<td class="treeNumDocumentsCell <?php if ($this->_tpl_vars['showFolderID']): ?>treeNoBottomPadding<?php endif; ?>" style="padding-right: 5px;" valign=top align=right>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['kernelStrings']['app_treefldaccessrights_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")); ?>

					<?php $this->assign('rightShort', $this->_tpl_vars['tree_access_mode_names'][$this->_tpl_vars['thisFolderRights']]); ?>
					<?php echo $this->_tpl_vars['kernelStrings'][$this->_tpl_vars['rightShort']]; ?>
 - 
					<?php $this->assign('longKeyindex', $this->_tpl_vars['tree_access_mode_long_names'][$this->_tpl_vars['thisFolderRights']]); ?>
					<?php echo $this->_tpl_vars['kernelStrings'][$this->_tpl_vars['longKeyindex']]; ?>

				</td>
			<?php endif; ?>
		</tr>
	</thead>
		<?php if ($this->_tpl_vars['showFolderID']): ?>
		<tbody>
			<tr>
				<td height=5 colspan=2></td>
			</tr>
			<tr>
				<td colspan=2 class="treeNoTopPadding"><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['kernelStrings']['app_tree_folderid_label'])) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['currentFolder']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['currentFolder'])); ?>
</td>
			</tr>
		</tbody>
		<?php endif; ?>
</table>