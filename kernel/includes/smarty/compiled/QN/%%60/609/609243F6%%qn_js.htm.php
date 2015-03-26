<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../QN/html/cssbased/qn_js.htm */ ?>
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

	function checkMinRights( rights )
	{
		thisForm = document.forms[0];

		for ( i = 0; i < thisForm.elements.length; i++ )
			if (thisForm.elements[i].type == 'checkbox')
				if ( thisForm.elements[i].name != "selectAllDocsCB" && thisForm.elements[i].checked ) {
					QN_ID = thisForm.elements[i].value;

					var rightsObj = tree_MM_findObj( "noterights["+QN_ID+"]" );
					if (!rightsObj)
						return false;

					if ( rightsObj.value < rights )
						return false;
				}

		return true;
	}

	function confirmFolderDeletion()
	{
		return confirmDeletionAjax( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_flddelete_message']; ?>
' );
	}

	function alertDelete()
	{
		 alert( '<?php echo $this->_tpl_vars['kernelStrings']['app_treenoflddelrights_message']; ?>
' );
		 return false;
	}

	function alertMove()
	{
		alert( '<?php echo $this->_tpl_vars['kernelStrings']['app_treenomovetosubfldrights_message']; ?>
' );
		return false;
	}

	function alertCopy()
	{
		alert( '<?php echo $this->_tpl_vars['kernelStrings']['app_treenocopyfldrights_message']; ?>
' );
		return false;
	}

	function alertAdd()
	{
		alert( '<?php echo $this->_tpl_vars['kernelStrings']['app_treenofldrights_message']; ?>
' );
		return false;
	}

	function alertAddRoot()
	{
		alert( '<?php echo $this->_tpl_vars['kernelStrings']['app_treenorootrights_message']; ?>
' );
		return false;
	}

	function alertModify()
	{
		alert( '<?php echo $this->_tpl_vars['kernelStrings']['app_treeinvcurfldrights_message']; ?>
' );
		return false;
	}

	function confirmCopy()
	{
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_emptycopy_message']; ?>
' ) )
			return false;

		return true;
	}

	function confirmMove()
	{
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_emptymove_message']; ?>
' ) )
			return false;

		if ( !checkMinRights( 1 ) ) {
			alert( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_invrights_message']; ?>
' );
			return false;
		}

		return true;
	}

	function confirmDeletion()
	{
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_emptydel_message']; ?>
' ) )
			return false;

		if ( !checkMinRights( 1 ) ) {
			alert( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_invrights_message']; ?>
' );
			return false;
		}

		return confirm( '<?php echo $this->_tpl_vars['qnStrings']['qn_screen_confirmdel_message']; ?>
' );
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
