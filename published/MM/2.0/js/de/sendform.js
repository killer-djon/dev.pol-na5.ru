function ge(id) { return document.getElementById(id) }
function pge(id) { return parent.document.getElementById(id) }

function fillSendForm(id)
{
	sendDisplayOn({'sendFormDiv':1});

	if(parent.editorIsLoaded) {
		xinha_editors[_editor_textarea].setHTML('');
	} else {
		ge('messageData[MMM_CONTENT]').innerHTML = 'Loading...';
	}

	ge('uploadFilesList').innerHTML = '';

	ge('status').value = '';

	ge('messageData[MMM_TO]').value = '';
	ge('messageData[MMM_CC]').value = '';
	ge('messageData[MMM_BCC]').value = '';
	ge('messageData[MMM_LISTS]').value = '';
	ge('messageData[MMM_SUBJECT]').value =  '';
	ge('userConfirmSend').value = 0;

	if(parent.currentAction == 'template')
		displayFormElements(false);
	else
		displayFormElements(true);

	var obj = ge('SendSubContacts');
	if(obj) {
		if(typeof(sendSubContactsBuffer)=='undefined')
			sendSubContactsBuffer = obj.innerHTML;
		else
			ge('SendSubContacts').innerHTML = sendSubContactsBuffer;
	}

	if(parent.currentAction == 'reply' || parent.currentAction == 'reply_all')
		ge('messageData[MMM_SUBJECT]').value = 'Re: ' + parent.msgParams['subject'];

	if(parent.currentAction == 'reply')
		ge('messageData[MMM_TO]').value = parent.msgParams['replyTo'];
	else if(parent.currentAction == 'reply_all')
		ge('messageData[MMM_TO]').value = parent.msgParams['all_emails'];
	else if(parent.currentAction == 'forward') {
		ge('messageData[MMM_SUBJECT]').value =  'Fw: ' + parent.msgParams['subject'];
		parent.currentMID = parent.msgParams['mid'];
		id = parent.currentMID;
	}
	else if(parent.currentAction=='draft' || parent.currentAction=='template' || parent.currentAction=='fromTemplate')
	{
		var obj = ge('bufferIframe');
		obj.src = reloadMessageURL + '?mid=' + parent.currentMID + '&action=' + parent.currentAction;

		doClear = false;
		parent.msgParams = Array();
		parent.msgParams['content'] = '';
	}

	if( parent.editorIsLoaded && parent.currentAction && parent.currentAction != 'draft' &&
		parent.currentAction != 'template' && parent.currentAction != 'fromTemplate')
		fillEditorContent();

	if(typeof(id) == 'undefined')
	{
		doClear = true;
		id = '';
		ge('currentFID').value = '';
	}
	ge('currentMID').value = id;

	currentContactPage = 0;

	cancelSendLaterMenu();

	var obj = ge('prioritySelect').options[1].selected = true; 	

	var cb_folder = pge('cb_' + id)
	if(cb_folder)
		cb_folder.checked = true;

	var obj = ge('messageData[MMM_FROM]');
	if(obj && parent.msgParams && parent.msgParams['replyFrom']) {
		for(var i=0; i<obj.options.length; i++) {
			if(obj.options[i].value == parent.msgParams['replyFrom']) {
				obj.selectedIndex = i;
				obj.options[i].selected = true;
			}
		}
	}
}
function fillEditorContent()
{
	parent.editorIsLoaded = true;

	if(parent.currentAction == 'reply' || parent.currentAction == 'reply_all')
		xinha_editors[_editor_textarea].setHTML(
			'<br><br><blockquote style="padding-left: 5px; margin-left: 0; border-left: 2px solid gray">' +
			'----- Original Message -----<br>' +
			'<b>Von:</b> ' + stripAddress(parent.msgParams['from']) + '<br>' +
			'<b>An:</b> ' + stripAddress(parent.msgParams['to']) + '<br>\n' +
			'<b>Date:</b> ' + stripAddress(parent.msgParams['date']) + '<br>\n' +
			'<b>Betreff:</b> ' + parent.msgParams['subject'] + '<br><br>' +
			parent.msgParams['content'] + '</blockquote>'
		);
	else if(parent.currentAction == 'forward' || parent.currentAction == 'draft' ||
		parent.currentAction == 'template' || parent.currentAction == 'fromTemplate')
		xinha_editors[_editor_textarea].setHTML(parent.msgParams['content']);
	else
		xinha_editors[_editor_textarea].setHTML('');

	parent.resizeIframe();

	if(parent.currentAction=='forward') {

		var obj = ge('bufferIframe');
		obj.src = reloadMessageURL + '?mid=' + parent.currentMID + '&action=forward';
		ge('status').value = 'doForward';
		if(!isNaN(parent.currentMID)) {
			ge('currentMID').value = '';
		}
	}

	if(typeof(doClear) == 'undefined') {
		uploadedFilesBuff = ge('uploadFilesDiv').innerHTML;
	}
	else if(doClear)
	{
		doClear = false;
		if(typeof(uploadedFilesBuff) != 'undefined')
			ge('uploadFilesDiv').innerHTML = uploadedFilesBuff;

		ge('action').value = 'clear';
		document.SendForm.submit();
	}
}

function prepareSendForm()
{
	var obj = ge('SendSubContacts');
	if(obj) obj.style.display = 'none';
	ge('SendCC').style.display = 'none';
	ge('SendBCC').style.display = 'none';
	ge('SendLists').style.display = 'none';
	ge('showCCLink').style.display = '';
	ge('showBCCLink').style.display = '';
	obj = ge('showListsLink');
	if(obj) obj.style.display = '';
	var obj = ge('SendSubContacts');
	if(obj) obj.style.display = '';
	hideMenu('SendSubContacts');
	self.scrollTo(0, 0);
}

function stripAddress(str)
{
	if(typeof(str) != 'undefined')
		return str.replace(/</g,'< ').replace(/>/g,' >')
	else
		return '';
}
function uploadFile()
{
	doClear = true;
	ge('uploadFileInput').style.display = 'none';
	ge('uploadFilePgBar').innerHTML = ge('sendLoadingBar').innerHTML;
	ge('uploadFilePgBar').style.display = '';
	ge('action').value = 'file';
	document.SendForm.submit();
}
function deleteUploadedFile(file)
{
	ge('uploadFileInput').style.display = 'none';
	ge('uploadFilePgBar').style.display = '';
	ge('action').value = 'delete';
	ge('deleteFile').value = file;
	document.SendForm.submit();
}

function doSendMessage(showSendingBar)
{
	var re = /[\.\-_a-z0-9]+?@[\.\-a-z0-9]+?\.[a-z0-9]{2,}/i;
	var toDraft = ge('toDraft').value;

	sendPgBarBuffer = (typeof(sendPgBarBuffer) == 'undefined') ? ge('messageSentDiv').innerHTML : sendPgBarBuffer;
	ge('messageSentDiv').innerHTML = sendPgBarBuffer;

	var obj = ge('messageData[MMM_FROM]'); 	

	if(obj.selectedIndex == -1 || !obj.options[obj.selectedIndex].value.match(re))
		alert('Incorrect sender address');
	else if(toDraft == 0 &&
		!ge('messageData[MMM_TO]').value.match(re) &&
		!ge('messageData[MMM_CC]').value.match(re) &&
		!ge('messageData[MMM_BCC]').value.match(re) &&
		!ge('messageData[MMM_LISTS]').value
	)
		alert('Incorrect recipient address');
	else
	{
		if(toDraft == 0)
		{
			if(showSendingBar || !ge('messageData[MMM_LISTS]').value)
				ge('sendPgBarLabel').innerHTML = 'Sending...';
			else
				ge('sendPgBarLabel').innerHTML = 'Preparing recipients list...';
		}
		hideMenu('SendSubContacts');

		pge('ToolbarIn').style.display = 'none';

		sendDisplayOn({'messageSentDiv':1});
		pge('infoContainer').style.display = 'none';

		ge('action').value = 'send';
		document.SendForm.submit();
	}
}

function sendMessage()
{
	ge('toDraft').value = 0;
	doSendMessage();
}

function confirmSendMessage()
{
	ge('userConfirmSend').value = 1;
	doSendMessage(1);
}

function saveToDraft(toTemplate)
{
	if(typeof(parent.currentMID) != 'undefined')
		ge('currentMID').value = parent.currentMID;
	else
		ge('currentMID').value = '';

	ge('toDraft').value = 1;
	ge('toTemplate').value = toTemplate;
	if(toTemplate) {
		ge('status').value = 100;
	}
	doSendMessage();
}

function subSel(key, name)
{
	for(var i=1; i<=send_sublen; i++)
	{
		if(i == key)
			ge('ssm'+name+i).className = 'OverList';
		else
			ge('ssm'+name+i).className = 'OutList';
	}
}
function showMenu(id)
{
	ge(id).style.display = '';
	var obj = ge('SendSubContacts');
	if(obj)
		obj.style.display = 'none';
}
function hideMenu(id)
{
	var obj = ge(id);
	if(obj)
		obj.style.display = 'none'
}
function delayedHideMenu(id) { hideMenuTimeout = setTimeout("hideMenu('" + id + "')", 300) }

function addAddress(inp_id, sub_id, new_addr)
{
	var inp_addr = ge(inp_id).value;
	var addr = splitAddress(inp_addr);
	var str = addr[0];

	if(testString(new_addr, inp_addr))
		str = inp_addr;
	else
	{
		if(str)
			str += new_addr + ', ';
		else
			str = new_addr + ', ';
	}
	ge(inp_id).value = str;
	if(sub_id)
	{
		ge(inp_id).focus();
		hideMenu(sub_id);
	}
}

function enterAddress(event, inp_id, sub_id)
{
	if(event.keyCode==13)
	{
		if(typeof(curSendAddr) != 'undefined' && curSendAddr)
			addAddress(inp_id, sub_id, curSendAddr);
		return false;
	}
	return true;
}

function testString(needle, haystack)
{
	var re = new RegExp(needle, 'i');
	return re.test(haystack);
}

function splitAddress(str)
{
	var old_str = str.replace(/^(.+?[,;]\s*)?([^,;]+)?$/, "$1");
	var new_str = str.replace(/^(.+?[,;]\s*)?([^,;]+)?$/, "$2");
	return Array(old_str, new_str);
}

function switchSubmenu(sub_id)
{
	var sub = ge(sub_id);
	if(sub.style.display) sub.style.display = '';
	else sub.style.display = 'none';
}

function htmlDecode(str)
{
	str=str.replace(/&amp;/gi, '&');
	str=str.replace(/&lt;/gi, '<');
	str=str.replace(/&gt;/gi, '>');
	return str;
}
function htmlEncode(str)
{
	str=str.replace(/</gi, '&lt;');
	str=str.replace(/>/gi, '&gt;');
	str=str.replace(/&/gi, '&amp;');
	return str;
}

function jsClearEntity(str)
{
	str = str.replace(/'/g, "'+String.fromCharCode(39)+'");
	str = str.replace(/"/g, "'+String.fromCharCode(34)+'");
	return str;
}

function stripTags(str) {return str.replace(/<.*?>/g,'')}

currentInput = false;

function switchContactsMenu(inpId, partId, thisObj)
{
	var src = ge('SendSubContacts');
		if(src.style.display)
		{
			setComboBoxesVisibility(false);

			try {clearTimeout(hideMenuTimeout)} catch(e) {};

			var inp = ge(inpId);
			var pos = getAbsolutePos(inp);
			src.style.top = pos.y + inp.offsetHeight + 'px';
			src.style.left = pos.x + 'px';
			var offsAdd = isIE() ? 5 : 15;
			ge('SendSubContacts').style.width = inp.offsetWidth + offsAdd + 'px';
			src.style.display = '';

			noHideFlag = true;

			currentInput = inpId;
			if(thisObj)
				thisObj.className = 'control_btn_down';
		}
		else
		{
			src.style.display = 'none';
			setComboBoxesVisibility(true);
			if(thisObj)
				thisObj.className = 'control_btn';
		}
	switchSubContacts(partId)
}

function setComboBoxesVisibility(visible)
{
	if(visible)
	{
		var e = document.getElementsByTagName('div');
		for(i=0; i<e.length; i++)
			if(e[i].className == 'control_btn_down')
				e[i].className = 'control_btn';
	}

	if(!isIE())
		return;

	var e = document.getElementsByTagName('select');

	for(i=0; i<e.length; i++)
	{
		if(e[i] == null) continue;
		if(e[i].getAttribute('nohide') != null)
			continue;
		if(!e[i].id)
		e[i].style.visibility = (!visible)? 'hidden':'visible';
	}
}

function isIE() {return window.navigator.appName == 'Microsoft Internet Explorer'}

function getAbsolutePos(el)
{
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if(el.offsetParent)
	{
		var tmp = getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
}

document.onclick = windowClickEventExplorer;
noHideFlag = false;
function windowClickEventExplorer(e)
{
	$(".autocomplete").hide();

	if(noHideFlag)
	{
		noHideFlag = false;
		return;
	}
	e = e||event;
	var target = e.target||e.srcElement;
	var parent = target;

	var obj = ge('SendSubContacts');
	while(parent.parentNode && parent!=obj)
		parent = parent.parentNode;
	if((!parent || parent!=obj) && obj)
	{
		obj.style.display = 'none';
		setComboBoxesVisibility(true);
	}
}

window.onload = keyPressInit;
function keyPressInit()
{                                                                                                                                      
    if(document.addEventListener) {                                                                                                                     
        document.addEventListener('keypress', function(e){windowKeyEventExplorer(e)}, true);                                                        
    }                                                                                                                                                   
    else if(document.attachEvent) {                                                                                                                     
	document.attachEvent('onkeydown', windowKeyEventExplorer);                                                                                  
    }                                                                                                                                                   
}                                                                                                                                                           
function windowKeyEventExplorer(e)                                                                                                                          
{                                                                                                                                                           
    if(e.keyCode == 9 || e.keyCode == 27)                                                                                                               
    {                                                                                                                                                   
        ge('SendSubContacts').style.display = 'none';                                                                                               
	setComboBoxesVisibility(true);                                                                                                              
    }                                                                                                                                           
}               


function backToSendForm()
{
	sendDisplayOn({'sendFormDiv':1}, {'sendFormToolbar':1, 'sendFormIframe':1});
	ge('userConfirmSend').value = 0;
	pge('ToolbarIn').style.display = '';
}

function doSearchContact()
{
	ge('bufferIframe').src =
		'sendform.php?currentObject=' + ge('currentObject').value +
		'&searchString=' + ge('searchSendContact').value;
	ge('contactsForInclude').innerHTML = ge('sendLoadingBar').innerHTML;
	currentContactPage = 0;
}

var sendDisplayElements = Array('sendFormDiv', 'messageSentDiv');

function sendDisplayOn(thisElements, parentElements)
{
	if(thisElements)
		for(var i=0; i<sendDisplayElements.length; i++)
		{
			if(typeof(thisElements[sendDisplayElements[i]]) == 'undefined')
				ge(sendDisplayElements[i]).style.display = 'none';
			else
				ge(sendDisplayElements[i]).style.display = '';
		}
	if(parentElements)
		parent.displayOn(parentElements);
}

function switchList(listID, isBox)
{
	var obj = ge('lists_td_'+listID);
	if(!obj) {
		return;
	}
	var listName = obj.innerHTML + ', ';
	var inp = ge('messageData[MMM_LISTS]');
	var box = ge('lists_box_'+listID);
	var row = ge('lists_tr_'+listID);
	var val = htmlEncode(inp.value);
	var re = new RegExp(listName);

	if(isBox) {
		box.checked = !box.checked;
	}

	if(box.checked)
	{
		ge('listsBox[' + listID + ']').value = 0;
		box.checked = false;
		val = val.replace(re, '');
		if(listID % 2)
		{
			row.saveClass = 'even';
			row.className = 'even';
		}
		else
		{
			row.saveClass = 'odd';
			row.className = 'odd';
		}
	}
	else
	{
		ge('listsBox[' + listID + ']').value = 1;
		box.checked = true;
		val += listName;
		row.saveClass = 'checked';
		row.className = 'checked';
	}
	inp.value = htmlDecode(val);
}

function switchSubContacts(doOn)
{
	if(doOn == 'ssc_contacts')
	{
		ge('ssc_lists').style.display = 'none';
		ge('ssc_contacts').style.display = '';
	} else {
		ge('ssc_contacts').style.display = 'none';
		ge('ssc_lists').style.display = '';
	}
}
function showMoreContacts()
{
	currentContactPage++;
	ge('bufferIframe').src =
		'sendform.php?currentObject=' + ge('currentObject').value +
		'&currentPage=' + currentContactPage +
		'&searchString=' + ge('searchSendContact').value;
	ge('contactsForInclude').innerHTML = ge('sendLoadingBar').innerHTML;
	cancelEventExplorer = true;
	noHideFlag = true;
}

function selectContactsFolder()
{
	ge('bufferIframe').src = 'sendform.php?currentObject=' + ge('currentObject').value +
		'&searchString=' + ge('searchSendContact').value;
	ge('contactsForInclude').innerHTML = ge('sendLoadingBar').innerHTML;
	currentContactPage = 0;
}

function hideSendFormLink(name)
{
	if(name == 'showCCLink')
	{
		ge(name).style.display = 'none';
		ge('SendCC').style.display = ''
	}
	else if(name == 'showBCCLink')
	{
		ge(name).style.display = 'none';
		ge('SendBCC').style.display = ''
	}
	else if(name == 'showListsLink')
	{
		ge(name).style.display = 'none';
		ge('SendLists').style.display = ''
	}
	parent.resizeIframe();
}

var date_format = 'm/d/Y';
function showSendLaterMenu()
{
	ge('sendLaterLink').style.display = 'none';
	ge('sendLaterMenu').style.display = '';
	ge('messageData[WHEN]').value = 'later';
	var obj = new Date();
	obj.setTime(obj.getTime() + 3600000 + time_diff);

	var m = obj.getMonth() + 1;
	m = m < 10 ? '0' + m : m;
	var d = obj.getDate();
	d = d < 10 ? '0' + d : d;
	var h = obj.getHours();
	h = h < 10 ? '0' + h : h;

	ge('messageData[WHENDATE]').value =
		date_format.replace(/m/i, m).replace(/d/i, d).replace(/Y/i, obj.getFullYear());
	ge(h+':00').selected = true;
}

function cancelSendLaterMenu()
{
	ge('sendLaterMenu').style.display = 'none';
	ge('sendLaterLink').style.display = '';
	ge('messageData[WHEN]').value = 'now';
	pge('infoContainer').style.display = 'none';
}

function insertVariable(str)
{
	xinha_editors[_editor_textarea].insertHTML(str);
	ge('insertVariableDiv').style.display = 'none';
}

function getAbsolutePos(el)
{
	var r = { x: el.offsetLeft, y: el.offsetTop };
	if(el.offsetParent)
	{
		var tmp = getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
}

var sendFormElements = Array('SendFrom', 'SendTo', 'SendLinks', 'composeMessageFooter');

function displayFormElements(on)
{
	for(var i=0; i<sendFormElements.length; i++)
	{
		if(on)
		{
			ge(sendFormElements[i]).style.display = '';
			ge('composeTemplateFooter').style.display = 'none';
		}
		else
		{
			ge(sendFormElements[i]).style.display = 'none';
			ge('composeTemplateFooter').style.display = '';
		}
	}
}

function dump(obj, obj_name)
{
	var result = "";
	for(var i in obj)
		result += obj_name + "." + i + " = " + obj[i] + "\n";
	return result;
}
