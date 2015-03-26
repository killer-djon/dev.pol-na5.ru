<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from ../../../IT/html/cssbased/issuelist_js.htm */ ?>
<script language="JavaScript">
<!--
	document.viewMode = "<?php echo $this->_tpl_vars['viewMode']; ?>
";
	
	var itStrings = {
		il_show_history: "<?php echo $this->_tpl_vars['itSrings']['il_show_history']; ?>
",
		il_hide_history: "<?php echo $this->_tpl_vars['itSrings']['il_hide_history']; ?>
"
	};
	
	window.CustomSplitterHeightHandler = function (splitterHeight)
	{

		if ( SplitterInfo.LeftPanelVisible )
		{
			var FoldersPanel = $( 'FoldersHeadersPanel' );
			var Content = $( 'SplitterLeftScrollableContent' );
			var TotalHeight = splitterHeight - FoldersPanel.offsetHeight;

			SplitterInfo.LeftPanelContent.style.height = TotalHeight + 'px';
		}

		var RightPanelHeader = $( 'RightPanelHeader' );
		var ListHeader = $( 'ListHeaderContainer' );
		var ListFooter = $( 'ListFooterContainer' );

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

	function MM_findObj(n, d) { //v4.0
	  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	  d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	  if(!x && document.getElementById) x=document.getElementById(n); return x;
	}

	function confirmCopy() {
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['itStrings']['il_emptycopy_message']; ?>
' ) )
			return false;
			

		return true;
	}

	function confirmMove() {
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['itStrings']['il_emptymove_message']; ?>
' ) )
			return false;
			

		return true;
	}

	function confirmRemind() {
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['itStrings']['il_emptyremind_message']; ?>
' ) )
			return false;
			

		return true;
	}

	function confirmDelete() {
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['itStrings']['il_emptydelete_message']; ?>
' ) )
			return false;

		return confirm( '<?php echo $this->_tpl_vars['itStrings']['il_deleteconfirm_message']; ?>
' );
	}

	function confirmSend() {
		if ( !treeCheckSelection( '<?php echo $this->_tpl_vars['itStrings']['il_emptysend_message']; ?>
' ) )
			return false;

		thisForm = document.forms[0];

		// Check task belonging
		//
		prevPWValue = null;
		for ( i = 0; i < thisForm.elements.length; i++ ) 
			if (thisForm.elements[i].type == 'checkbox') 
				if (thisForm.elements[i].checked) {
					el = thisForm.elements[i];

					if ( el.name.substr( 0, 8 ) != 'document' )
						continue;

					len = el.name.length;

					I_ID = el.name.substr( 9, len-10 );
					PWName = "PW["+I_ID+"]";

					obj = MM_findObj(PWName);
					if ( prevPWValue == null )
						prevPWValue = obj.value;

					if ( prevPWValue != obj.value ) {
						alert('<?php echo $this->_tpl_vars['itStrings']['il_invtaskssend_message']; ?>
');
						return false;
					}
					prevPWValue = obj.value;
			}

		<?php if (! $this->_tpl_vars['userIsProjman']): ?>
		// Check status belonging
		//
		prevTSValue = null;
		for ( i = 0; i < thisForm.elements.length; i++ ) 
			if (thisForm.elements[i].type == 'checkbox') 
				if (thisForm.elements[i].checked) {
					el = thisForm.elements[i];
					if ( el.name.substr( 0, 8 ) != 'document' )
						continue;

					len = el.name.length;

					I_ID = el.name.substr( 9, len-10 );
					TSName = "TS["+I_ID+"]";

					obj = MM_findObj(TSName);
					if ( prevTSValue == null )
						prevTSValue = obj.value;

					if ( prevTSValue != obj.value ) {
						alert('<?php echo $this->_tpl_vars['itStrings']['il_invissuestatussend_message']; ?>
');
						return false;
					}
					prevTSValue = obj.value;
			}
			

		// Check ADC 
		//
		for ( i = 0; i < thisForm.elements.length; i++ ) 
			if (thisForm.elements[i].type == 'checkbox') 
				if (thisForm.elements[i].checked) {
					el = thisForm.elements[i];
					if ( el.name.substr( 0, 8 ) != 'document' )
						continue;

					len = el.name.length;

					I_ID = el.name.substr( 9, len-10 );
					ADCName = "ADC["+I_ID+"]";

					obj = MM_findObj(ADCName);

					if ( obj.value == 0 ) {
						alert('<?php echo $this->_tpl_vars['itStrings']['il_invissuesforbsend_message']; ?>
');
						return false;
					}
			}
		<?php endif; ?>

		return prevPWValue.split("|");
		//return prevPWValue.substr(prevPWValue.indexOf("|")+1);
	}

	function selectIssues( PW_ID, thisObj )
	{
		thisForm = document.forms[0];

		for ( i = 0; i < thisForm.elements.length; i++ ) 
			if (thisForm.elements[i].type == 'hidden') {
				el = thisForm.elements[i];
				len = el.name.length;

				if ( el.name.substr( 0, 4 ) == "TASK" ) {
					elPW_ID = el.value;

					if ( elPW_ID != PW_ID )
						continue;
					
					I_ID = el.name.substr( 5, len-6 );
					
					docName = "document["+I_ID+"]";
					obj = MM_findObj(docName);
					
					if ( obj )
						obj.checked = thisObj.checked;
				}
			}
	}

	function checkAdd()
	{
		<?php if ($this->_tpl_vars['workComplete']): ?>
			alert( "<?php echo $this->_tpl_vars['itStrings']['il_addcompltask_message']; ?>
" );
			return false;
		<?php endif; ?>

		return true;
	}
	
	function toggleWork (workId) {
		workEl = document.getElementById("WorkTable" + workId);
		var collapse = !(Ext.get(workEl).hasClass("collapsed"));
		
		var issuesElems = Ext.get("IssuesWorksBlock").query(".Work" + workId);
		for (var i = 0; i < issuesElems.length; i++)
			expandCollapseElem(issuesElems[i], collapse);
		expandCollapseElem(workEl, collapse);
	}
	
	function expandCollapseElem(elem, collapse) {
		if (collapse)
				Ext.get(elem).addClass("collapsed");
			else
				Ext.get(elem).removeClass("collapsed");
	}
	
	function toggleWorkHistory (PW_ID, I_ID) {
		if (!toggleComment(I_ID,0)) return;
		var cp = getCookieProvider ();
		var currentHistoryStatuses = cp.get ("currentHistoryStatuses", {});
			
		var status = null;
		var obj = document.getElementById("history-" + PW_ID + "_" + I_ID);
		var link = document.getElementById("history-link-" + PW_ID + "_" + I_ID);
		if (obj.style.display == "none") {
			obj.style.display = "block";
			link.innerHTML = "<?php echo $this->_tpl_vars['itStrings']['il_hide_history']; ?>
";
			status = "show";
		} else {
			obj.style.display = "none";
			link.innerHTML = "<?php echo $this->_tpl_vars['itStrings']['il_show_history']; ?>
";
			status = "hide";
		}
		currentHistoryStatuses[PW_ID + "_" + I_ID] = status;
		cp.set ("currentHistoryStatuses",currentHistoryStatuses);
	}
	
	function startsShowWorksHistory () {
		var cp = getCookieProvider ();
		var currentHistoryStatuses = cp.get ("currentHistoryStatuses", {});
		
		
		var objs = Ext.query (".it_TransitionLog");
		for (var i= 0; i < objs.length; i++) {
			var obj = objs[i]
			var issueId = obj.getAttribute("issueId");
			var link = document.getElementById ("history-link-" + issueId);
			var needShow = (currentHistoryStatuses[issueId] != null) ? (currentHistoryStatuses[issueId] == "show") : document.showTransitionLog;
				
			if (needShow) {
				link.innerHTML = "<?php echo $this->_tpl_vars['itStrings']['il_hide_history']; ?>
";
				obj.style.display = "block";
			} else {
				link.innerHTML = "<?php echo $this->_tpl_vars['itStrings']['il_show_history']; ?>
";
				obj.style.display = "none";
			}
		}
	}
	
	function clearHistoryStatuses() {
		var cp = getCookieProvider ();
		cp.set ("currentHistoryStatuses",{});
	}
	
	function getCookieProvider () {
		if (document.it_cookieProvider)
			return document.it_cookieProvider;
		document.it_cookieProvider = new Ext.state.CookieProvider({expires: new Date(new Date().getTime()+(1000*60*60*24*30))})
		return document.it_cookieProvider;
	}
	
	
	
	function openIssuesWork (P_ID, PW_ID) {
		AjaxLoader.doRequest("../../../PM/html/ajax/project_works.php",
			function(response, options) {
    		var result = Ext.decode(response.responseText);
    		
    		
    		var reader = new Ext.data.JsonReader({id: "PW_ID", root: 'works'}, Work);  
    		var works = reader.read(response);
    		var work = works.records[0];
    		    		    		
    		var workDialog = getWorkDialog (work);
				workDialog.setMode("modify", work);
				workDialog.showDialog ();	
				workDialog.form.getForm().loadRecord(work);
				workDialog.afterLoad ();
			},		
			{projectId: P_ID, workId: PW_ID}		
		);
		
		
		/*var workDialog = getWorkDialog ();
		workDialog.setMode("modify", work);
		workDialog.showDialog ();	
		workDialog.form.getForm().loadRecord(work);
		workDialog.afterLoad ();*/
	};
	
	document.worksStore = new Ext.data.SimpleStore ({fields: ['P_PW', 'PW_DESC', 'P_ID', "PW_ID"], data: <?php echo $this->_tpl_vars['works_data_js']; ?>
	});
	document.P_ID = "<?php echo $this->_tpl_vars['P_ID']; ?>
";
	document.projectId = "<?php echo $this->_tpl_vars['P_ID']; ?>
";;
	document.showTransitionLog = <?php if ($this->_tpl_vars['showTransitionsData']): ?>true<?php else: ?>false<?php endif; ?>;
	RegisterOnLoad( startsShowWorksHistory );
	
	document.afterPageLoad = function () 
	{
		startsShowWorksHistory ();
	}
	
	function toggleIssueSelected (pId, pwId, iId, changeCheckbox) {
		var table = Ext.get("ISSUEBLOCK_" + pId + "_" + pwId + "_" + iId);
		var checkbox = document.getElementById("check_" + pId + "_" + pwId + "_" + iId);
		if(changeCheckbox)
			checkbox.checked = !checkbox.checked;
		if (!table)
			return;
		if (!checkbox.checked)
			table.removeClass("selected");
		else
			table.addClass("selected");
	}
	
	function setIssueSelected(pId, pwId, iId, selected) {
		var checkbox = document.getElementById("check_" + pId + "_" + pwId + "_" + iId);
		var table = Ext.get("ISSUEBLOCK_" + pId + "_" + pwId + "_" + iId);
		if (!selected && table.hasClass("selected") && !checkbox.checked)
			table.removeClass("selected");
		else {
			table.addClass("selected");
		}
	}
	
	function refreshList () {
		res = AjaxLoader.loadPage ("issuelist.php?");
	}
	
	function toggleIssueRestricted (pId, pwId, iId) {
		var descfull = document.getElementById("descfull_" + pId + "_" + pwId + "_" + iId);
		var descshort = document.getElementById("descshort_" + pId + "_" + pwId + "_" + iId);
		descfull.style.display = (descfull.style.display == "none") ? "inline" : "none";
		descshort.style.display = (descfull.style.display == "none") ? "inline" : "none";
	}
	
	document.addedIssues = new Array ();
	function updateIssueBlock (P_ID, PW_ID, I_ID, html ) {
		var blockId = "ISSUEBLOCK_" + P_ID + "_" + PW_ID + "_" + I_ID;
		
		var obj = document.getElementById(blockId);
		var justAddedObj = document.getElementById("JustAddedIssues");
				
		if (obj == null) {
			var newBlock = document.createElement('div');
			newBlock.id = blockId;
      newBlock.innerHTML = html;			
      newBlock.className = "it_IssueList";
      
      justAddedObj.style.display = "block";
			justAddedObj.appendChild (newBlock);
			document.addedIssues.push (blockId);
			
			var newIssueTable = document.getElementById(P_ID + "_" + PW_ID + "_" + I_ID);
			newIssueTable.className += " selected";
			
			changeIssuesCount(+1);
			return;
		}
		if (html == null || html.length < 1) { 
			changeIssuesCount(-1);
			obj.innerHTML = "";
		} else {
			obj.innerHTML = html;
		}
		//document.getElementById(P_ID + "_" + PW_ID + "_" + I_ID).innerHTML = html;
	}
	
	function refreshIssueBlock (P_ID, PW_ID, I_ID, html, callback) {
		if (html == null) {
			updateIssueBlock (P_ID, PW_ID, I_ID, html);
			if (callback)
				callback();
			return;
		}
		
		AjaxLoader.doRequest ("../ajax/issue_gethtml.php", 
			function(response, options) {
    			var result = Ext.decode(response.responseText);
    			updateIssueBlock(result.P_ID, result.PW_ID, result.I_ID, result.html);
    			if (callback)
						callback();
    	}, {
				P_ID: P_ID, PW_ID: PW_ID, I_ID: I_ID, viewMode: document.viewMode, viewP_ID: document.P_ID
			}, {scope: this});
	}	
	
	
	function deleteIssue(P_ID, PW_ID, I_ID )
	{
		thisForm = document.forms[0];

		for ( i = 0; i < thisForm.elements.length; i++ )  {
			if (thisForm.elements[i].type == 'checkbox') {
				el = thisForm.elements[i];
				el.checked = false;
				if (el.id == "check_" + P_ID + "_" + PW_ID + "_" + I_ID)
					el.checked = true;
				else {
					// Temporary fix
					el.value = 0;
				}
			}
		}

		if (!confirmDelete ())
			return false;
			
		processAjaxButton ("deleteissuesbtn");
	}

function addComment(sn) {
	var comment = $('COMMENT-'+sn).value.replace(/(^\s+)|(\s+$)/g, "")
	if (comment.length > 0)
		AjaxLoader.doRequest ("../ajax/issue_addcomment.php", DoAddCommentHandler, {
						"ITL_ID" :  $('ITL_ID-'+sn).value,
						"U_ID_SENDER": $('U_ID_SENDER-'+sn).value,
						"ITS_STATUS" : $('ITS_STATUS-'+sn).value,
						"I_ID" : $('I_ID-'+sn).value,
						"COMMENT": comment
						}, {scope: this})
	else toggleComment(sn,0) //diasble comment-form if comment-body is emty
}
function DoAddCommentHandler (response, options) {
	var result = Ext.decode(response.responseText)
	if (result.success) {
		//insert table row with comment
		var table=$('H'+result.I_ID)
		var row = table.insertRow(table.rows.length)
		var cell1=row.insertCell(0)
		var cell2=row.insertCell(1)
		cell1.innerHTML="<span class='Date'>"+result.ITL_DATETIME+"</span>"
		cell2.innerHTML="<b><?php echo $this->_tpl_vars['itStrings']['it_Comment']; ?>
</b> <span class='Sender'>"+result.U_ID_SENDER+"</span><br>"+result.ITL_OLDCONTENT
		//clear'n'disable comment-form
		$('COMMENT-'+result.I_ID).value=""
		toggleComment(result.I_ID,0)
	} else {
		//alert if error
		alert(result.errorStr)
	}
}

function getOffsetChange() {
	var offsetChangeObj = document.getElementById("OffsetChange");
	if (!offsetChangeObj)
		return 0;
	return offsetChangeObj.value;
}
function moveOffsetChange(val) {
	var offsetChangeObj = document.getElementById("OffsetChange");
	if (!offsetChangeObj)
		return false;
	offsetChangeObj.value = offsetChangeObj.value*1 + val*1;
}

function showNextPage (offset, lastOutedP_ID, lastOutedPW_ID) {
	var offsetChange = getOffsetChange();
	offset = offset*1 + offsetChange*1;
	
	var showDiv = document.getElementById ("NextRecordsBlock");
	showDiv.innerHTML = "<img src='../../../common/html/res/images/progress.gif'> <span style='font-size: 12pt; font-weight: bold'><?php echo $this->_tpl_vars['itStrings']['il_loading_label']; ?>
</span><BR><BR>";
	
	AjaxLoader.doRequest("issuelist.php",
		function(response, options) {
  		var newDiv = document.createElement("div");
  		var txt = response.responseText;
  		newDiv.innerHTML = txt;
  		showDiv.parentNode.replaceChild(newDiv, showDiv);
		},		
		{ajaxAccess: 1, onlyIssues: 1, offset: offset, lastOutedP_ID : lastOutedP_ID, lastOutedPW_ID : lastOutedPW_ID}
	);
}

function changeIssuesCount (changeValue) {
	var obj;
	if (obj = document.getElementById("issuesCount")) {
		var val = parseInt(obj.innerHTML);
		obj.innerHTML = val + changeValue;	
	}
	if (obj = document.getElementById("totalIssuesCount")) {
		var val = parseInt(obj.innerHTML);
		obj.innerHTML = val + changeValue;	
	}	
}

/**
* enable/diasble comment-form
*/
function toggleComment(id,mode) {
	function empty(id) {
		$('ACF-'+id).style.display='none'
		$('ACD-'+id).style.display='block'
		$("COMMENT-"+id).value = ''
	}
	//disable coments
	if (mode == 0) {
		var comment = $('COMMENT-'+id).value.replace(/(^\s+)|(\s+$)/g, "")
		if (comment.length == 0) empty(id)
		else if (confirm('<?php echo $this->_tpl_vars['itStrings']['it_del_Comment_Confirm']; ?>
')) empty(id)
			else return false
	}
	//enable coments
	if (mode == 1) {
		$('ACF-'+id).style.display='block'
		$('ACD-'+id).style.display='none'
		$("COMMENT-"+id).focus()
	}
	return true;
}
/**
* Activate comments
*/
function CommentForm (idr,idt) {
	if ($('history-'+idr+'_'+idt).style.display == 'none')
		toggleWorkHistory (idr, idt)
	toggleComment(idt, 1)
}
//-->
</script>