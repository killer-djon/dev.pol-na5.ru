
function DeleteSelectedFiles () {
	if (!confirmDeletion())
		return false;		
		
	thisForm = document.forms[0];
	var selectedDocuments = new Array ();
	for ( i = 0; i < thisForm.elements.length; i++ ) {
		cElem = thisForm.elements[i];
		if (cElem.type == 'checkbox' && cElem.checked  && cElem.getAttribute("filename") != null) {
			selectedDocuments.push (cElem.value);
		}
	}
	AjaxLoader.doRequest ("../ajax/files_delete.php", DeleteSelectedFilesHandler,
		{"documents[]" : selectedDocuments}, {scope: this});
}

function DeleteSelectedFilesHandler (response, options) {
	var result = Ext.decode(response.responseText);
	if(result.success) {
		foldersTree.loadSelectedNode ();
	} else {
		alert(result.errorStr);
		foldersTree.loadSelectedNode ();
	}
}


function checkCheckoutStatuses()
{
	thisForm = document.forms[0];

	var checkedFilenames = new Array ();
	for ( i = 0; i < thisForm.elements.length; i++ ) {
		var elem = thisForm.elements[i];
		if (elem.type == 'checkbox') {
			if (elem.name == "selectAllDocsCB" || !elem.checked) 
				continue;
			
			if (elem.getAttribute("checkstatus") == 1)  {
				var rightsElem = tree_MM_findObj( "filerights["+elem.value+"]" );
				var rights = rightsElem.value;
				if (rights < 7) {
					alert(DDStrings.dd_screen_deletelockedjs_message);
					return false;
				} else {
					checkedFilenames.push(elem.getAttribute("filename"));
				}
			}
		}
	}
	
	if (checkedFilenames.length > 0) {
		var res = confirm(DDStrings.dd_screen_deletelockedjs_confirm);
		return res ? "confirmed" : false;
	}
	return true;
}


function confirmDeletion()
{
	if ( !treeCheckSelection( DDStrings.dd_screen_emptydel_message  ) )
		return false;

	if ( !checkMinRights( 3 ) ) {
		alert( DDStrings.dd_screen_invrights_message  );
		return false;
	}
	
	var checkoutResult = checkCheckoutStatuses();
	if (!checkoutResult)
		return false;
	if (checkoutResult == "confirmed")
		return true;
		
	return confirm( DDStrings.dd_screen_confirmdel_message  );
}

function confirmCheckIn()
{
	if ( !treeCheckSelection( DDStrings.dd_screen_emptycheckin_message  ) )
		return false;

	if ( !checkMinRights( 1 ) ) {
		alert( DDStrings.dd_screen_invcheckinrights_message  );
		return false;
	}

	return confirm( DDStrings.dd_screen_confirmcheckin_message );
}

function confirmCheckOut()
{
	if ( !treeCheckSelection( DDStrings.dd_screen_emptycheckout_message  ) )
		return false;

	return true;
}

function confirmRestore()
{
	if ( !treeCheckSelection( DDStrings.dd_screen_emptyrestore_message  ) )
		return false;

	return true;
}

function confirmCopy()
{
	if ( !treeCheckSelection( DDStrings.dd_screen_emptycopy_message  ) )
		return false;

	return true;
}

function confirmMove()
{
	if ( !treeCheckSelection( DDStrings.dd_screen_emptymove_message  ) )
		return false;

	if ( !checkMinRights( 1 ) ) {
		alert( DDStrings.dd_screen_invrights_message  );
		return false;
	}

	return true;
}

function confirmFolderDeletion()
{
	return confirmDeletionAjax( DDStrings.dd_screen_flddelete_message  );
}

/**
* Select table row on checked checkbox
*/
function checkEle(ele) {
	if ($("chk"+ele).checked) {
		addClass($("s"+ele), "selected")
		addClass($("btmctrl"+ele),"visible")
	} else {
		removeClass($("btmctrl"+ele), "visible")
		removeClass($("s"+ele), "selected")
	}
}
/**
* Hide statusbar if checkbox NOT checked
*/
function eleHide(ele) {
	if($("chk"+ele).checked == false){
		removeClass($("btmctrl"+ele),"visible")
    removeClass($("s"+ele),"selected");
	}
}
/**
*====================================
*INLINE TEXT EDITOR SECTION
*====================================
**/
function ChangeDesc (id, notNeedFocus) {
  if ($('morelink'+id)) {
    $('morelink'+id).style.display='none';
  }
  if ($('fl_desc_'+id)){
  	$('fl_desc_'+id).style.display='none';
  }
  $('pl_desc_'+id).style.display='block';
  
	var descBlock = document.getElementById("pl_desc_" + id);
	var realBlock = document.getElementById("real_desc_" + id);
	if (descBlock.mode == "edit")
		return;
		
	descBlock.oldValue = descBlock.innerHTML;
	realBlock.oldValue = realBlock.value;
		
	var height = descBlock.scrollHeight;
	
	var height = descBlock.scrollHeight;
	if (height < 80) height = 80;
	height -= 28;
	
	var realValue = realBlock.value;
	descBlock.innerHTML = "<textarea id='desc_textarea_" + id + "' style='font-family: \"Trebuchet MS\"; font-size: 14px; width: 95%; height: " + height + "px; '>" + realValue + "</textarea>";
	descBlock.innerHTML += "<BR><a class='Blue' style='text-decoration:none;font-size:16px;' onClick='SaveDesc(" + id + ")' href='#'>" + ddStrings.sv_screen_save_btn + "</a> &nbsp;&nbsp; <a class='Blue' href='#' style='text-decoration:none;font-size:16px;' onClick='CancelChangeDesc(" + id + ")'>" + ddStrings.sv_screen_cancel_btn + "</a>";
	descBlock.mode = "edit";$('desc_textarea_'+id).focus();
}
function CancelChangeDesc (id) {
  	if ($('fl_desc_'+id)){
  		$('fl_desc_'+id).style.display='block';
  		$('pl_desc_'+id).style.display='none';
  	}
	var descBlock = document.getElementById("pl_desc_" + id);
	var realBlock = document.getElementById("real_desc_" + id);
	descBlock.mode = "cancelled";
	descBlock.innerHTML = descBlock.oldValue;
	realBlock.value = realBlock.oldValue;
	if ($('fl_desc_'+id)) $('fl_desc_'+id).innerHTML = $('real_desc_'+id).value;
}
function SetDescBlockValue (id, value) {
	var descBlock = document.getElementById("pl_desc_" + id);
	descBlock.innerHTML = value.replace(/\n/g, "<br>\n");
	if (descBlock.innerHTML == "")
    		descBlock.innerHTML = "&lt;" + 'add description' + "&gt;";
//		descBlock.innerHTML = "&lt;" + PDStrings.pd_screen_adddescription_label + "&gt;";
}
var oldDesc;
function SaveDesc (id) {
	var descBlock = document.getElementById("pl_desc_" + id);
	var realBlock = document.getElementById("real_desc_" + id);
	var textarea =  document.getElementById("desc_textarea_" + id);
	descBlock.mode = "saved";
	var newValue = textarea.value;
	realBlock.value = newValue;
	SetDescBlockValue(id, newValue);
	DoSaveDesc(id, newValue);
	oldDesc = descBlock.oldValue;
	oldRealDesc = realBlock.oldValue;
}
function DoSaveDesc (id, value) {
	AjaxLoader.doRequest ("../ajax/file_changedesc.php", DoSaveDescHandler,
		{"id" : id, "description" : value}, {scope: this});
}
function DoSaveDescHandler (response, options) {
	var result = Ext.decode(response.responseText);
	if(result.success) {
		document.getElementById("real_desc_" + options.params.id).value = result.newDesc;
		if (document.getElementById("pl_desc_" + options.params.id).mode != "edit")
			SetDescBlockValue (options.params.id, result.newDesc);
	} else {
		alert(result.errorStr);
		document.getElementById("real_desc_" + options.params.id).innerHTML = oldRealDesc;
		document.getElementById("pl_desc_" + options.params.id).innerHTML = oldDesc;
	}
}

function deselectChboxes() {
  thisForm = document.forms[0];
  for ( i = 0; i < thisForm.elements.length; i++ ) {
    if (thisForm.elements[i].type == 'checkbox')
      thisForm.elements[i].checked = false;
  }
}

function deletefile(id) {
  deselectChboxes();
  $(id).checked = 'true';
  DeleteSelectedFiles();
  $(id).checked = false;
}
function emailfile(id) {
  deselectChboxes();
  $(id).checked = 'true';
  showSendEmailsDlg();
  $(id).checked = false;
}
function CheckOutfile(id) {
  deselectChboxes();
  $(id).checked = 'true';
  confirmCheckOut();
  $(id).checked = false;
}
function CheckInfile(id) {
  deselectChboxes();
  $(id).checked = 'true';
  confirmCheckIn();
  $(id).checked = false;
}