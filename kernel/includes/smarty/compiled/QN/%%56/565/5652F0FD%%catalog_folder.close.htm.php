<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../common/html/cssbased/pageelements/ajax/catalog_folder.close.htm */ ?>
<?php if ($this->_tpl_vars['hideFoldersHint'] == ""): ?>
	<?php $this->assign('hideFoldersHint', $this->_tpl_vars['kernelStrings']['app_treehidefld_hint']); ?>
<?php endif; ?>

<a title="<?php echo $this->_tpl_vars['hideFoldersHint']; ?>
" href='javascript:void(0)' onClick='SplitterHideLeftPanel()'><span class="SplitterClosePanelBtn">&nbsp;</span></a>