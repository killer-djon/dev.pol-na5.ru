<?php /* Smarty version 2.6.26, created on 2014-10-16 23:39:49
         compiled from m.frame.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'component', 'm.frame.html', 26, false),)), $this); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" 
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<meta name="viewport" content="width=320"> <!--  user-scalable=no,initial-scale=1.0 -->
		<base href="<?php echo @CONF_FULL_SHOP_URL; ?>
">


		<!-- Head start -->
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "head.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<!-- Head end -->
		<?php if ($this->_tpl_vars['overridestyles']): ?><link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/overridestyles.css" type="text/css" /><?php endif; ?>
		
		<link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/main.css" type="text/css" />
		<link rel="stylesheet" href="<?php echo @URL_CSS; ?>
/general.css" type="text/css" />
	</head>
	<body style="padding:6px;background: #fff;">
		<?php if ($this->_tpl_vars['page_not_found404']): ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "404.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php else: ?>
			<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['main_body_tpl'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		<?php endif; ?>

		<div style="padding:6px;border-color:#777777;border-style:solid;border-width:0px;border-top-width:1px;text-align:center;">
		<?php echo smarty_function_component(array('cpt_id' => 'divisions_navigation','divisions' => 'mobile','view' => 'horizontal'), $this);?>

		</div><?php if (! $_GET['productwidget'] && ! $this->_tpl_vars['productwidget'] && ! $this->_tpl_vars['printable_version'] && $this->_tpl_vars['show_powered_by']): ?>
		<div id="powered_by"><?php if ($this->_tpl_vars['show_powered_by_link']): 
 echo 'Работает на основе <a href="http://www.shop-script.ru/" style="font-weight: normal">скрипта интернет-магазина</a> <em>WebAsyst Shop-Script</em>'; 
 else: 
 echo 'Работает на основе <em>WebAsyst Shop-Script</em>'; 
 endif; ?></div>
<?php endif; ?>
	</body>
</html>