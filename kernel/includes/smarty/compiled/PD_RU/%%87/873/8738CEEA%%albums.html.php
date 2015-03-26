<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:21
         compiled from albums.html */ ?>
<script>
	jQuery(document).ready(function(){
		document.pdApplacation = new PDApplacation();
		document.sessionId = "<?php echo $this->_tpl_vars['sessionId']; ?>
";

		document.mainUrl = "<?php echo $this->_tpl_vars['mainUrl']; ?>
";
		document.hostUrl = "<?php echo $this->_tpl_vars['hostUrl']; ?>
";
		document.memory_limit = "<?php echo $this->_tpl_vars['memory_limit']; ?>
";
	});

</script>

<div id="hor-info-panel" style="display: none;">
	<div class="info_block">
		<div>ить альбом: <span id="album-date-create" ></span></div>
		<div>by: <span id="album-user" ></span></div>
		<div>: <span id="album-right" ></span></div>
	</div>
	<div class="main-header">
		<!--<span class="all-albums">
			<a href="javascript: window.parent.location.hash = '';">&laquo;</a>
		</span>-->
		<div id="album-info">
			<span id="album-name"></span>
			<span id="album-name-full" style="display: none;"></span>
		</div>
	</div>
	<span id="date-str" ></span>
	
	<div class="upload-box"><img src="img/upload.gif" width="16" height="16"/><a href="#" id="link-upload">Upload photos</a></div>
	
	<div class="publish-info">
		<span id="album-access"></span>
		<span id="album-url"></span>		
		<span><a href="#" id="btn-album-setting" ></a></span>
	</div>
</div>

<div id="main" class="resize">
	<div id="rigth-info-panel" style="display: none;">rigth-info-panel</div>
	<div id="main-content" >
		<div id="footer-info-panel"> </div>
		<div id="album-content-body" style="height: 100%;">
			
			
			<!-- js generic content -->
			
		</div>
	</div>
</div>

<!-- Fields required for history management -->
<form id="history-form" class="x-hidden" style="display: none;">
    <input type="hidden" id="x-history-field" />
    <iframe id="x-history-frame"></iframe>
</form>

<!-- popup div -->
<div id="pp" style="display:none;"></div>