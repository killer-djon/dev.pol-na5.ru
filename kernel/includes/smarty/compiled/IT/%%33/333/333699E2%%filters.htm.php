<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from filters.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'filters.htm', 25, false),array('modifier', 'htmlsafe', 'filters.htm', 30, false),)), $this); ?>
<script>
	var currentSelectedTdId = "<?php echo $this->_tpl_vars['ISSF_ID']; ?>
";
	function selectList (href, linkElem) {
		res = AjaxLoader.loadPage(href); 
		if (!res)
			return false;
		var tdElem = linkElem.parentNode;
		if (document.getElementById(currentSelectedTdId) != null)
			document.getElementById(currentSelectedTdId).className = "treeCatalogRow";
		tdElem.className = "treeCatalogRowCurrent";
		currentSelectedTdId = tdElem.id;
		return false;
	}
</script>


<?php if ($this->_tpl_vars['ISSF_ID'] == ""): ?>
	<?php $this->assign('ISSF_ID', 'NO'); ?>
<?php endif; ?>
<div style="padding-left: 10px;">
	<table border=0 cellpadding=0 cellspacing=0>
		<?php $_from = $this->_tpl_vars['filters']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['filterKey'] => $this->_tpl_vars['filterData']):
?>
			<?php if ($this->_tpl_vars['filterData'] != ""): ?>
				<tr>
					<?php if (((is_array($_tmp=$this->_tpl_vars['filterKey'])) ? $this->_run_mod_handler('cat', true, $_tmp, "-") : smarty_modifier_cat($_tmp, "-")) != ((is_array($_tmp=$this->_tpl_vars['ISSF_ID'])) ? $this->_run_mod_handler('cat', true, $_tmp, "-") : smarty_modifier_cat($_tmp, "-"))): ?>
						<?php $this->assign('is_current', 1); ?>
					<?php else: ?>
						<?php $this->assign('is_current', 0); ?>
					<?php endif; ?>
					<td id='<?php echo $this->_tpl_vars['filterKey']; ?>
' class="<?php if ($this->_tpl_vars['is_current'] != 0): ?>treeCatalogRow<?php else: ?>treeCatalogRowCurrent<?php endif; ?>"><a href="<?php echo $this->_tpl_vars['filterData']['url']; ?>
" onClick='return selectList(this.href, this)'><?php echo ((is_array($_tmp=$this->_tpl_vars['filterData']['name'])) ? $this->_run_mod_handler('htmlsafe', true, $_tmp, true, true) : smarty_modifier_htmlsafe($_tmp, true, true)); ?>
</a></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
	</table>
</div>