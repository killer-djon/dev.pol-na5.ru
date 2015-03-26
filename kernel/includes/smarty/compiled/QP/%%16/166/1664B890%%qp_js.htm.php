<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:47
         compiled from ../../../QP/html/cssbased/qp_js.htm */ ?>
<script language="JavaScript">
<!--

	window.CustomSplitterHeightHandler = function (splitterHeight)
	{

		if ( SplitterInfo.LeftPanelVisible )
		{
			var FoldersPanel = document.getElementById( 'FoldersHeadersPanel' );

			var Content = document.getElementById( 'SplitterLeftScrollableContent' );

			var TotalHeight = splitterHeight - FoldersPanel.offsetHeight;

			var OffsetHeight = Content.offsetHeight;

			SplitterInfo.LeftPanelContent.style.height = TotalHeight + 'px';
		}

		var RightPanelHeader = document.getElementById( 'RightPanelHeader' );
		var ListHeader = document.getElementById( 'ListHeaderContainer' );
		var ListFooter = document.getElementById( 'ListFooterContainer' );

		var ListHeaderHeight = 0;
		if ( ListHeader )
			ListHeaderHeight = ListHeader.offsetHeight;

		var ListFooterHeight = 0;
		if ( ListFooter )
		{
			ListFooterHeight = ListFooter.offsetHeight;
			ListFooter.style.visibility = 'visible';
		}

		rphHeight = (RightPanelHeader == null) ? 0 : RightPanelHeader.offsetHeight;
		SplitterInfo.RightPanelContent.style.height = splitterHeight - rphHeight - ListHeaderHeight - ListFooterHeight + 'px';
	}

	function confirmBookDeletion()
	{
		return confirm( "<?php echo $this->_tpl_vars['qpStrings']['app_bookdelete_message']; ?>
" );
	}

	function confirmDeletion()
	{
		var ret;

		ret = confirm( "<?php echo $this->_tpl_vars['qpStrings']['app_screen_pagedel_message']; ?>
" );

		<?php if ($this->_tpl_vars['hasItChilds']): ?>
			if ( ret )
				return confirm( "<?php echo $this->_tpl_vars['qpStrings']['app_screen_subpages_delete_message']; ?>
" );
			else
				return ret;
		<?php else: ?>
			return ret;
		<?php endif; ?>
	}

	function submitFolder( obj )
	{
		selected = obj.selectedIndex;
		if ( obj.options[selected].value == -1 ) {
			return false;
		}

		obj.form.submit();
	}

	function findCurrentFolder()
	{
		obj = document.getElementById("<?php echo $this->_tpl_vars['currentFolder']; ?>
");
		if ( obj ) {
			obj.scrollIntoView();
		}
	}

	RegisterOnLoad( function(){ SplitterScrollPanel( '<?php echo $this->_tpl_vars['currentFolder']; ?>
' ) } );

//-->
</script>