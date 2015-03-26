<?php /* Smarty version 2.6.26, created on 2014-08-08 13:58:51
         compiled from index/Index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'jscombine', 'index/Index.html', 16, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<title>Help Desk</title>

<link type="text/css" rel="stylesheet" href="<?php echo $this->_tpl_vars['url']['css']; ?>
reset.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo $this->_tpl_vars['url']['css']; ?>
base/ui.all.css">
<link type="text/css" rel="stylesheet" href="<?php echo $this->_tpl_vars['url']['css']; ?>
base/wbs-theme.css"/>
<link type="text/css" rel="stylesheet" href="<?php echo $this->_tpl_vars['url']['css']; ?>
style.css"/>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery/jquery-1.4.2.min.js"></script>
<!--[if lt IE 8]>
<script src="<?php echo $this->_tpl_vars['url']['js']; ?>
lib/plugins/IE8.js"></script>
<![endif]-->
<?php $this->_tag_stack[] = array('jscombine', array('file' => ($this->_tpl_vars['url']['js'])."wbs/wbsst.js")); $_block_repeat=true;smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery-ui/ui/jquery.ui.core.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery-ui/ui/jquery.ui.widget.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery-ui/ui/jquery.ui.mouse.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery-ui/ui/jquery.ui.button.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery-ui/ui/jquery.ui.position.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/jquery-ui/ui/jquery.ui.dialog.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/plugins/jquery.scrollTo-min.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/plugins/jquery.history.js
<?php echo $this->_tpl_vars['url']['js']; ?>
lib/plugins/jquery.autocomplete.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.splitter.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.portlet.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.popup.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.menu.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.colorpicker.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.editor.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.core.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.controller.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.request.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.widgets.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.pages.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.searchlist.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.layout.js
<?php echo $this->_tpl_vars['url']['js']; ?>
wbs/wbs.ui.grid.js
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<style>
.ui-layout {overflow: hidden; float: left;}
.ui-layout-panel {float: left;}
.ui-layout-clear {clear: both;}
.ui-grid-row-unread {font-weight: bold;}
#topPanel {width: 100%;overflow: auto;}
#topPanel > div {width: 630px;display: block;}
.ui-portlet {width: 99.5%;}
#rightPanel {height: 100%;}
#textarea {width: 100%;height: 100%;}
#container, body, html {overflow: hidden;}
#grid {width: 100%;height: 100%}
#bottomPanel {width: 100%;position: relative;}
#grid.tr-cursor-pointer tbody tr {cursor: pointer;}
#knowledge, #splitter3 {display: none;}
.ui-table, .ui-table .ui-state-default {border-top: none;}
#grid {border-top: solid #C3CBD2 1px;}
</style>
<!--[if IE 8]>
<style>
    .act-items a:hover {background: none;}
</style>
<![endif]-->
</head>
<body>
<div id="container"></div>
<div class="ui-layout-markup" style="display:none">
    <div id="mainLayout" class="fill:all stack:h">
        <div id="leftPanel" class="fill:v splitter:v width:200 widthConstraint:140-500">
            <div id="leftMenu" class="overflow:auto widget:leftMenu fill:all flex:v">
                <div id="requestsPanel" class="fill:none widget:requests"></div>
<?php if ($this->_tpl_vars['user_is_admin']): ?>
                <div id="settingsPanel" class="fill:none widget:settings"></div>
<?php endif; ?>
            </div>
        </div>
        <div id="splitter1" class="widget:splitter1 fill:v width:5"></div>
        <div id="rightPanel" class="fill:h">
<?php if ($this->_tpl_vars['show_limit_msg']): ?>
            <div id="alertPanel" class="fill:h height:<?php echo $this->_tpl_vars['limit_msg_size']; ?>
% stack:v widget:alert"></div>
<?php else: ?>
            <div id="topPanel" class="nowrap:true fill:none stack:h">
                <div id="menuBtnDiv" class="widget:menuBtn align:left"></div>
                <div id="searchRequest" class="widget:searchRequest align:left"></div>
                <div id="checkEmail" class="widget:checkEmail width:100%"></div>
            </div>
<?php endif; ?>
            <div id="topPanelBorder" class="widget:border fill:h height:2"></div>
            <div id="contentPanel" class="fill:all"></div>
        </div>
    </div>
    <div id="mainPage" class="fill:all">
        <div id="toolbar" class="fill:h stack:h">
            <div id="title" class="widget:title align:left"></div>
        </div>
        <div id="gridPanel" class="overflow:hidden fill:h">
            <div id="grid" class="stack:v widget:grid"></div>
        </div>
        <div id="splitter2" class="widget:splitter2 fill:h height:5"></div>
        <div id="bottomPanel" class="fill:v stack:h">
            <div id="textarea" class="widget:textarea fill:v splitter:v"></div>
            <!--div id="splitter3" class="widget:splitter3 fill:v width:3"></div>
            <div id="knowledge" class="widget:knowledge fill:all heightOffset:-40 overflow:auto"></div-->
        </div>
    </div>
    <div id="pageContainer" class="fill:all overflow:auto"></div>
    <div id="classesContainer" class="fill:all">
        <div id="toolbar" class="fill:h stack:h">
            <div id="title"></div>
        </div>
        <div id="content" class="fill:all">
        </div>
    </div>
</div>
<?php if ($this->_tpl_vars['show_limit_msg']): ?>
<script>
    $.wbs.widgets.alert = function(el){
        el.append('<div class="alert-msg"><p>ACCOUNT LIMIT: Number of requests can not exceed <?php echo $this->_tpl_vars['limit']; ?>
</p><p></p>' +
        '<p><?php echo $this->_tpl_vars['limit_msg_link']; ?>
</p></div>');
    };
    $('#container').click(function(){
        if (!(($("#rightPanel > #alertPanel:visible").css('position') != 'absolute') &&
        ($("#rightPanel > #alertPanel:visible").css('margin') == '') &&
        $("#rightPanel > #alertPanel:visible").height() > 0.2 * $(window).height())) {
            $('body').empty();
            location.reload();
        }
    })
</script>
<?php endif; ?>
<?php if (! $this->_tpl_vars['has_requests']): ?>
<div id="first-message" style="display:none;" class="first-message"><div class="v-align">
	<p class="p-hed">To receive requests by email:</p>
	<p>Send an email to <a href="mailto:<?php echo $this->_tpl_vars['default_email']; ?>
"><?php echo $this->_tpl_vars['default_email']; ?>
</a> and it will appear in request list on this page.</p>
	<p>In <a href="#/sources/">Settings / Email boxes</a> section you can setup other email addresses to receive requests.</p>
	<p class="p-hed">To receive requests via web form:</p>
	<p>In <a href="#/forms/">Settings / Forms</a> section create a web form and embed it into your website.</p>
</div></div>
<?php endif; ?>
<script>
<?php if ($this->_tpl_vars['use_limit'] == 1): ?>
$.wbs.set('use_limit', 1);
<?php endif; ?>
$.wbs.set('current-user-id', <?php echo $this->_tpl_vars['user_id']; ?>
);
$.wbs.set('users', <?php echo $this->_tpl_vars['users']; ?>
);
$.wbs.set('data', <?php echo $this->_tpl_vars['data']; ?>
);
$.wbs.set('last_req_id', <?php echo $this->_tpl_vars['last_req_id']; ?>
);
$.wbs.set('last_log_id', <?php echo $this->_tpl_vars['last_log_id']; ?>
);
$.wbs.set('unread_count', <?php echo $this->_tpl_vars['unread_count']; ?>
);
$.wbs.controller.onLoad();
$(document).ready(function () {
	$.wbs.controller.init(<?php echo $this->_tpl_vars['settings']; ?>
);
	<?php if (! $this->_tpl_vars['has_sources']): ?>
	$.wbs.widgets.showAlertMsg($('#topPanel'), "<span id='no_sources'>You have no sources to get new requests: email box or web form</span>");
	$('#checkEmail .refresh-link, .empty-bin-link').css('top', 165);
	<?php endif; ?>
});
</script>
</body>
</html>