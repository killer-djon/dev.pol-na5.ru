<?php /* Smarty version 2.6.26, created on 2014-11-10 14:48:46
         compiled from news.frontend.post.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'escape', 'news.frontend.post.tpl.html', 10, false),array('modifier', 'set_query', 'news.frontend.post.tpl.html', 17, false),)), $this); ?>
<h1><?php echo 'Блог / Новости'; 
 if ($this->_tpl_vars['rss_link']): ?>&nbsp;<a href="<?php echo @URL_ROOT; ?>
/<?php echo $this->_tpl_vars['rss_link']; ?>
"><img src="<?php echo @URL_IMAGES_COMMON; ?>
/rss-feed.png" alt="RSS 2.0"  style="padding-left:10px;"></a><?php endif; ?></h1>

<div class="post_block">
	<h2 class="post_title"><?php echo $this->_tpl_vars['news_posts'][0]['title']; ?>
</h2>
	
	<div class="post_date"><?php echo $this->_tpl_vars['news_posts'][0]['add_date']; ?>
</div>
	
	<div class="post_content">
	<?php if ($this->_tpl_vars['news_posts'][0]['picture_exists']): ?>
		<img alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['news_posts'][0]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['news_posts'][0]['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" hspace="20" vspace="20" align="left" />
	<?php endif; ?>
	<?php echo $this->_tpl_vars['news_posts'][0]['textToPublication']; ?>

	</div>
</div>
<br />
<div class="news_viewall">
    <a href="<?php echo ((is_array($_tmp="?ukey=news")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><?php echo 'Смотреть все'; ?>
...</a>
</div>