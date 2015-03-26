<?php /* Smarty version 2.6.26, created on 2014-08-08 13:46:21
         compiled from album-include.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'csscombine', 'album-include.html', 19, false),array('block', 'jscombine', 'album-include.html', 27, false),)), $this); ?>
<!-- 
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/reset.css'>
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/backend.css'>
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/wbs-elements.css'>
<link rel='stylesheet' type='text/css' href='<?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/imgareaselect-default.css'>

<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/wbs-common.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/wbs.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/wbs-elements.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.cookie.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.hotkeys.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.jeditable.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.imgareaselect.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.wbs.pager.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/slider.js"></script>
<script type="text/javascript" src="<?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/backend.js"></script>
 -->
<?php $this->assign('path1', $this->_tpl_vars['p']->getCssUrl()); ?>
<?php $this->_tag_stack[] = array('csscombine', array('file' => ($this->_tpl_vars['path1'])."/album-combine.css")); $_block_repeat=true;smarty_block_csscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
 <?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/reset.css
 <?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/backend.css
 <?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/wbs-elements.css
 <?php echo $this->_tpl_vars['p']->getCssUrl(); ?>
/imgareaselect-default.css
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_csscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $this->assign('path2', $this->_tpl_vars['p']->getCommonUrl()); ?>
<?php $this->_tag_stack[] = array('jscombine', array('file' => ($this->_tpl_vars['path2'])."/js/pd-common-combine.js")); $_block_repeat=true;smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
 <?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/wbs-common.js
 <?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/wbs.js
 <?php echo $this->_tpl_vars['p']->getCommonUrl(); ?>
/js/wbs-elements.js
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>

<?php $this->assign('path3', $this->_tpl_vars['p']->getJsUrl()); ?>
<?php $this->_tag_stack[] = array('jscombine', array('file' => ($this->_tpl_vars['path3'])."/album-combine.js")); $_block_repeat=true;smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?>
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.cookie.js
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.hotkeys.js
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.jeditable.js
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.imgareaselect.js
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/jquery.wbs.pager.js
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/slider.js
 <?php echo $this->_tpl_vars['p']->getJsUrl(); ?>
/backend.js?673
<?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_jscombine($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>