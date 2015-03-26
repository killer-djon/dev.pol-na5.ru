<?php /* Smarty version 2.6.26, created on 2014-10-16 18:20:59
         compiled from frame.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'frame.html', 44, false),)), $this); ?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<base href="<?php echo @CONF_FULL_SHOP_URL; ?>
">
<?php if ($this->_tpl_vars['rss_link']): ?>	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php echo @URL_ROOT; ?>
/<?php echo $this->_tpl_vars['rss_link']; ?>
"><?php endif; ?>
	<script type="text/javascript">
<?php if (@CONF_WAROOT_URL): ?>		var WAROOT_URL = '<?php echo @BASE_WA_URL; ?>
';//ok<?php endif; ?>

<?php if (@CONF_ON_WEBASYST): ?>		var CONF_ON_WEBASYST = '<?php echo @CONF_ON_WEBASYST; ?>
';<?php endif; ?>
	</script>
	
<!-- Head start -->
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "head.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- Head end -->

<?php if ($this->_tpl_vars['overridestyles']): ?>	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/overridestyles.css" type="text/css"><?php endif; ?>
	<script type="text/javascript" src="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/head.js"></script>
	<link rel="stylesheet" href="<?php echo $this->_tpl_vars['URL_THEME_OFFSET']; ?>
/main.css" type="text/css">
	<link rel="stylesheet" href="<?php echo @URL_CSS; ?>
/general.css" type="text/css">
<?php if (! $this->_tpl_vars['page_not_found404']): ?>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/functions.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/behavior.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/widget_checkout.js"></script>
	<script type="text/javascript" src="<?php echo @URL_JS; ?>
/frame.js"></script>	
	<script src="<?php echo @URL_ROOT; ?>
/3rdparty/jquery-1.9.1.js"></script>
	
	<link rel="stylesheet" href="<?php echo @URL_ROOT; ?>
/3rdparty/jqueryui/css/smoothness/jquery-ui-1.10.3.custom.min.css" type="text/css" />
	<script src="<?php echo @URL_ROOT; ?>
/3rdparty/jqueryui/js/jquery-ui-1.10.3.custom.min.js"></script>
	
	<link rel="stylesheet" href="<?php echo @URL_ROOT; ?>
/3rdparty/lytebox/lytebox.css" type="text/css" />
	<script type="text/javascript" src="<?php echo @URL_ROOT; ?>
/3rdparty/lytebox/lytebox.js"></script>
	
	<script type="text/javascript" src="<?php echo @URL_ROOT; ?>
/3rdparty/cycle.jquery.js"></script>
	<script type="text/javascript" src="<?php echo @URL_ROOT; ?>
/3rdparty/commons_product.js"></script>

	<script type="text/javascript">
<!--		
<?php echo $this->_tpl_vars['current_currency_js']; ?>

var ORIG_URL = '<?php echo @CONF_FULL_SHOP_URL; ?>
';
var ORIG_LANG_URL = '<?php echo ((is_array($_tmp="?")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
';
window.currDispTemplate = defaultCurrency.display_template;
var translate = {};
translate.cnfrm_unsubscribe = '<?php echo 'Вы уверены, что хотите удалить вашу учетную запись в магазине?'; ?>
';
translate.err_input_email = '<?php echo 'Введите правильный электронный адрес'; ?>
';
translate.err_input_nickname = '<?php echo 'Пожалуйста, введите Ваш псевдоним'; ?>
';
translate.err_input_message_subject = '<?php echo 'Пожалуйста, введите тему сообщения'; ?>
';
translate.err_input_price = '<?php echo 'Цена должна быть положительным числом'; ?>
';
<?php echo 'function position_this_window(){
	var x = (screen.availWidth - 600) / 2;
	window.resizeTo(600, screen.availHeight - 100);
	window.moveTo(Math.floor(x),50);
}'; ?>
		
<?php if ($this->_tpl_vars['PAGE_VIEW'] == 'printable'): ?>Behaviour.addLoadEvent(function(){position_this_window();setTimeout(window.print(),1000);});<?php endif; ?>
//-->
</script>
<?php endif; ?>




<!-- metrics informers -->



<!-- end of metrics -->

	</head>
	<body <?php echo $this->_tpl_vars['GOOGLE_ANALYTICS_SET_TRANS']; 
 if ($this->_tpl_vars['main_body_style']): 
 echo $this->_tpl_vars['main_body_style']; 
 endif; 
 if ($this->_tpl_vars['PAGE_VIEW'] == 'printable'): ?> style="background-color:#FFFFFF;background-image:none;"<?php endif; 
 if ($this->_tpl_vars['page_not_found404']): ?> class="body-page-404"<?php endif; ?>>
<!--  BODY -->
<?php if ($this->_tpl_vars['main_body_tpl']): 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => $this->_tpl_vars['main_body_tpl'], 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 else: 
 if ($this->_tpl_vars['page_not_found404']): 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "404.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 else: 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "index.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 endif; 
 endif; 
 if (! $_GET['productwidget'] && ! $this->_tpl_vars['productwidget'] && ! $this->_tpl_vars['printable_version'] && $this->_tpl_vars['show_powered_by']): ?>
<div id="powered_by">
<?php if ($this->_tpl_vars['show_powered_by_link']): ?>
	<?php echo 'Работает на основе <a href="http://www.shop-script.ru/" style="font-weight: normal">скрипта интернет-магазина</a> <em>WebAsyst Shop-Script</em>'; ?>

<?php else: ?>
	<?php echo 'Работает на основе <em>WebAsyst Shop-Script</em>'; ?>

<?php endif; ?>
</div><?php endif; ?>

<!--  END -->
<?php if (! $this->_tpl_vars['page_not_found404'] && ! $this->_tpl_vars['printable_version']): 
 echo $this->_tpl_vars['GOOGLE_ANALYTICS_CODE']; ?>

<?php endif; ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "phones.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 
 $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "ukladka.tpl.html", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
<?php echo '
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter23321767 = new Ya.Metrika({id:23321767,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
'; ?>

</script>
<noscript><div><img src="//mc.yandex.ru/watch/23321767" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->

<script>
<?php echo '
  (function(i,s,o,g,r,a,m){i[\'GoogleAnalyticsObject\']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,\'script\',\'//www.google-analytics.com/analytics.js\',\'ga\');

  ga(\'create\', \'UA-46313933-1\', \'pol-na5.ru\');
  ga(\'send\', \'pageview\');
'; ?>

</script>


	</body>
</html>