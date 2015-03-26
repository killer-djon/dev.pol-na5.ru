<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:42
         compiled from frm_upload.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'translate', 'frm_upload.htm', 21, false),)), $this); ?>
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('html/res/swfupload_new/swfupload.css'); ?>
" type="text/css"/>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['preproc']->getCommonUrl('html/res/swfupload_new/swfupload.min.js'); ?>
"></script>
<script type="text/javascript" src="templates/frm_upload.js"></script>
<style>
	.progressWrapper {width: auto; }
	.progressContainer {background: white !important; border: 0 !important; padding: 0px !important; margin: 0px !important; width: 250px; }
	a.progressCancel {display: none !important;}
	
	.swfupload {
		position:absolute;
		z-index:1; cursor: pointer;
	}
</style>


<script>
	document.sessionId = "<?php echo $this->_tpl_vars['sessionId']; ?>
";	
	
	WbsUploadDlg.prototype.onAfterInit = function() {
		this.uploader.messages = new Array ();
		this.uploader.messages[500] = "<?php echo ((is_array($_tmp='dd->updlg_file_error_unknown')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		this.uploader.messages[502] = "<?php echo ((is_array($_tmp='dd->updlg_file_error_fileslimit')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		this.uploader.messages[503] = "<?php echo ((is_array($_tmp='dd->updlg_file_error_systemquota')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		this.uploader.messages[504] = "<?php echo ((is_array($_tmp='dd->updlg_file_error_userquota')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		this.uploader.messages[505] = "<?php echo ((is_array($_tmp='dd->updlg_file_error_checkout')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		
		this.fullMessages = new Array ();
		//this.fullMessages[502] = '<?php echo ((is_array($_tmp='dd->app_doclimit_message')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
';
		this.fullMessages[502] = '<?php echo ((is_array($_tmp='dd->updlg_file_error_fileslimit')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
';
		this.fullMessages[503] = '<?php echo ((is_array($_tmp='dd->updlg_file_error_systemquota')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
';
		this.fullMessages[504] = '<?php echo ((is_array($_tmp='dd->updlg_file_error_userquota')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
';
		
		this.uploader.strings.pendingStr = "<?php echo ((is_array($_tmp='dd->updlg_statuspending_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		this.uploader.strings.uploadingStr = "<?php echo ((is_array($_tmp='dd->updlg_statusuploading_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
		this.uploader.strings.completeStr = "<?php echo ((is_array($_tmp='dd->updlg_statuscomplete_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
";
	}
</script>

<style>
	#FilesArea {background: #FFF; border: 1px solid #DDD; text-align: left; padding-left: 20px; padding-right: 20px; height: 50px; overflow: hidden; width: 300px; }
	#FilesArea #blockSelectedFiles {margin-top:5px;}
	#FilesArea #blockNoSelectedFiles {margin-top: 0px; }
	#FilesArea #blockAfterUpload {margin-top:5px; text-align: left}
	#blockLimitError b {color: red; }
	#errorFilesBlock {color: red; font-weight: bold}
	
	#completeStatusLabel {color: red; font-weight: bold; font-size: 15pt; padding-top: 10px; text-align: center}
</style>


<div id='frm-upload-block' style='display: none; background: #F0F0F0; border-top: 1px solid #BBB; clear: both; overflow: hidden; zoom: 1; padding: 10px; margin-bottom: -3px'>
	<div class="content-inner" id='dlg-upload-content'>
		<div id='FilesArea' style='float: left'>
			<div id='blockNoSelectedFiles'>
				
			</div>
			
			<div id='completeStatusLabel'></div>
			
			<div id='blockSelectedFiles' style='display: none'>
				<!--a style='float:right' id='selectMoreLink' href='javascript:void(0)' onClick='document.ddApplication.getUploadDlg().selectFiles()'><?php echo ((is_array($_tmp='dd->updlg_selectmore_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a-->
				<div id="flashUI1">
					<div class="flash" id="fsUploadProgress1">
					</div>
				</div>
			</div>
		</div>
		
		<div id='uploaderFooter' style='padding-left: 10px; padding-right: 50px; float: left; '>
				<div>
					<div id='blockLimitError' style='padding-bottom: 5px'>
						<div id='limitErrorMessage' style='font-weight: bold; padding-bottom: 5px'></div>
						<div id="limitErrorActions">
						<?php if ($this->_tpl_vars['currentUser']->hasAccessToApp('AA')): ?>
							<input type='button' onClick='location.href = "../../AA/html/scripts/change_plan.php"' value='<?php echo ((is_array($_tmp='template_common->lbl_upgrade_account')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
'>
							<?php echo ((is_array($_tmp='template_common->lbl_or')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <a href='javascript:void(0)' onClick='document.ddApplication.getUploadDlg().close();'><?php echo ((is_array($_tmp='template_common->action_cancel')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
						<?php else: ?>
							<b style='color: black'><?php echo ((is_array($_tmp='template_common->lbl_upgrade_account')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); 
 echo ((is_array($_tmp='template_common->lbl_contact_administrator')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</b>
						<?php endif; ?>
						</div>
					</div>
					
					<div style='height: 20px; '>
						<div id='errorFilesBlock'>
							<span id='errorFilesCount'>0</span> <?php echo ((is_array($_tmp='dd->updlg_filesnotuploaded_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

						</div>
					</div>
				</div>
				
				<div id='uploadingBlock'><span id='filesUploadingStr'><?php echo ((is_array($_tmp='dd->updlg_uploading_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span> <span id='queueFilesCount'></span>&nbsp;<span id='filesSelectedStr'><?php echo ((is_array($_tmp='dd->updlg_filesselected_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</span> <input id='btnCancel' style='font-size: 1em'  value='<?php echo ((is_array($_tmp='template_common->action_cancel')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
' type='button'></div>
				<div style='margin-top: 3px' id='blockAfterUpload'>
					<span id='completedFilesCount'>0</span> <?php echo ((is_array($_tmp='dd->updlg_filesuploaded_label')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
 <a href='javascript:void(0)' style='padding-left: 50px; color: blue' onClick='document.ddApplication.getUploadDlg().close();'><?php echo ((is_array($_tmp='template_common->action_close')) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
					<BR>
				</div>
				<input id='btnStart' style='font-size: 1.3em; display: none' type='button' value='Start'> 
				<!--span style='float:left' id='btnStart'></span>
				<span style='float:left; margin-left: 10px' id='btnCancel'></span-->
		</div>
	</div>
</div>