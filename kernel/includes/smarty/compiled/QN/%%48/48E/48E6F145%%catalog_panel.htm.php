<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../QN/html/cssbased/catalog_panel.htm */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "../../../common/html/cssbased/pageelements/ajax/catalog_folder.htm", 'smarty_include_vars' => array('hideRecyced' => 1,'showFolderControls' => 1,'addFolderLabel' => $this->_tpl_vars['kernelStrings']['app_treeaddfolder_title'],'deleteFolderLabel' => $this->_tpl_vars['kernelStrings']['app_treedelfolder_text'],'enableDD' => true,'enableEdit' => true)));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script>
	document.onFoldersTreeLoad = function () {
		foldersTree.searchPrefix = "quicknotes.php?searchString=";
		foldersTree.linkPrefix = 'quicknotes.php?curQNF_ID=';
		
		var treeDDManager = new TreeDDManager ();
		treeDDManager.init (
			foldersTree, 
			{
				nodeMovedUrl: "../ajax/folder_move.php", 
				treeLoaderUrl: '../ajax/tree.folders.php', 
				linkPrefix: 'quicknotes.php?curQNF_ID='
			}
		);
		
		var treeEditManager = new TreeEditManager ();
		treeEditManager.init (
			foldersTree,
			{
				renameUrl: "../ajax/folder_rename.php"				
			}		
		);
		
		<?php if ($this->_tpl_vars['lastSearchString']): ?>
			var searchNode = foldersTree.getSearchNode ("<?php echo $this->_tpl_vars['lastSearchString']; ?>
");
			<?php if ($this->_tpl_vars['searchString']): ?>
				searchNode.select ();
			<?php endif; ?>
		<?php endif; ?>
	}
</script>