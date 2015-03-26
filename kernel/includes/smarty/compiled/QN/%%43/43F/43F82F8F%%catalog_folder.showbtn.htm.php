<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../common/html/cssbased/pageelements/ajax/catalog_folder.showbtn.htm */ ?>
<?php if ($this->_tpl_vars['showFoldersHint'] == ""): ?>
	<?php $this->assign('showFoldersHint', $this->_tpl_vars['kernelStrings']['app_treeshowfld_hint']); ?>
<?php endif; ?>
<a href="javascript:void(0)" id='SplitterShowLeftPanelBtn' onClick='SplitterShowLeftPanel()' title="<?php echo $this->_tpl_vars['showFoldersHint']; ?>
" style='padding: 0px; margin-right: 2px; text-decoration:none;<?php if (! $this->_tpl_vars['treePanelHide']): ?>display:none<?php endif; ?>'><span style='margin-top: 4px; height: 23px; background-position: top left' class="SplitterHeaderTreeBtn"/></a>