<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:51
         compiled from issuelist_rightpanelheader.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'truncate', 'issuelist_rightpanelheader.htm', 3, false),)), $this); ?>
<div>
    <b id='RightPanelHeaderCaption'>
        <?php echo ((is_array($_tmp=$this->_tpl_vars['curFilterName'])) ? $this->_run_mod_handler('truncate', true, $_tmp, 30) : smarty_modifier_truncate($_tmp, 30)); ?>

    </b>
</div>
<?php if ($this->_tpl_vars['project_ids']): ?>
<table border="0" cellpadding="0" cellspacing="5" class='HeaderLinks'>
<tr>
  <td valign="top">
      <a href='javascript:void(0)'><img src="../img/add-issue.gif" border="0"></a>
  </td>
  <td>
  	&nbsp;<a onClick='issueAddDialog()' href='javascript:void(0)'><?php echo $this->_tpl_vars['itStrings']['ami_addissue_title']; ?>
</a>
  </td>
  <td width="10">&nbsp;</td>
  
  <!--td valign="top">
      <a href='javascript:void(0)'><img src="../img/customize-workflow.gif" border="0"></a>
  </td>
  <td>
  	&nbsp;<a href='javascript:void(0)'>Customize Workflow</a>
  </td-->
</tr>
</table>
<?php endif; ?>
