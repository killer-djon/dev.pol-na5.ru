<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../QN/html/cssbased/notelist_footer.htm */ ?>
<div id="ListFooterContainer" style="visibility: hidden">
	<?php if (! $this->_tpl_vars['statisticsMode']): ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/tree_page_list.htm", 'smarty_include_vars' => array('ajax' => true,'showFolderID' => 0)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php else: ?>
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/tree_summary_info.htm", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	<?php endif; ?>
</div>