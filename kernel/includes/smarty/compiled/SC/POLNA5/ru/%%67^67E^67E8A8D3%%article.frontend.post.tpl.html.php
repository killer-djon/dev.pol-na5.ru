<?php /* Smarty version 2.6.26, created on 2014-10-16 19:24:48
         compiled from article.frontend.post.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'article.frontend.post.tpl.html', 5, false),array('modifier', 'translate', 'article.frontend.post.tpl.html', 5, false),array('modifier', 'set_query_html', 'article.frontend.post.tpl.html', 6, false),array('modifier', 'escape', 'article.frontend.post.tpl.html', 10, false),array('modifier', 'date_format', 'article.frontend.post.tpl.html', 20, false),array('modifier', 'replace', 'article.frontend.post.tpl.html', 20, false),)), $this); ?>

	<div class="single-article">
	
		<h1>
			<?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['Article']['ArticleTitle'])) ? $this->_run_mod_handler('default', true, $_tmp, 'pgn_articles') : smarty_modifier_default($_tmp, 'pgn_articles')))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

			&nbsp;<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&rss=rss")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><img src="<?php echo @URL_IMAGES_COMMON; ?>
/rss-feed.png" alt="RSS 2.0"  style="padding-left:10px;"></a>
			
				<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['product_category_path']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
					<?php if ($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['CategoryID']): ?>
						<a class="all-articles" href='<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['CategoryID'])."&CategorySlug=".($this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['CategorySlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['product_category_path'][$this->_sections['i']['index']]['CategoryTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')))) ? $this->_run_mod_handler('default', true, $_tmp, "(no name)") : smarty_modifier_default($_tmp, "(no name)")); ?>
</a>
					<?php endif; ?>
					
				<?php endfor; endif; ?>
			
		</h1>
	
		<div class="single-content">
			
			<div class="single-date">
				Дата публикации: <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Article']['ArticleDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'January', "Января") : smarty_modifier_replace($_tmp, 'January', "Января")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'February', "Февраля") : smarty_modifier_replace($_tmp, 'February', "Февраля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'March', "Марта") : smarty_modifier_replace($_tmp, 'March', "Марта")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'April', "Апреля") : smarty_modifier_replace($_tmp, 'April', "Апреля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'May', "Мая") : smarty_modifier_replace($_tmp, 'May', "Мая")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'June', "Июня") : smarty_modifier_replace($_tmp, 'June', "Июня")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'July', "Июля") : smarty_modifier_replace($_tmp, 'July', "Июля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'August', "Августа") : smarty_modifier_replace($_tmp, 'August', "Августа")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'September', "Сентября") : smarty_modifier_replace($_tmp, 'September', "Сентября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'October', "Октября") : smarty_modifier_replace($_tmp, 'October', "Октября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'November', "Ноября") : smarty_modifier_replace($_tmp, 'November', "Ноября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'December', "Декабря") : smarty_modifier_replace($_tmp, 'December', "Декабря")); ?>

			</div>
			
			<div class="main-wrap-single">
				<div class="img">
					<?php if ($this->_tpl_vars['Article']['ArticleDefaultBigPicture']): ?>
						<img alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['Article']['ArticleTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo @URL_ARTICLES_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['Article']['ArticleDefaultBigPicture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" alt="" class="ArticlePost-image"/>
					<?php endif; ?>
				</div>
				<div class="article-text">
					<?php echo $this->_tpl_vars['Article']['ArticleDescription']; ?>

				</div>
			</div>
		</div>
	
	</div>
	
<div class="articles-list">	
	<?php if ($this->_tpl_vars['Article']['ArticleArticles']): ?>
		<h3 class="ArticlePost-caption"><?php echo 'Рекомендуемые прочитать'; ?>
</h3>
		<div class="post_block ArticlePost-article" style="padding:10px">
		<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['Article']['ArticleArticles']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['j']['show'] = true;
$this->_sections['j']['max'] = $this->_sections['j']['loop'];
$this->_sections['j']['step'] = 1;
$this->_sections['j']['start'] = $this->_sections['j']['step'] > 0 ? 0 : $this->_sections['j']['loop']-1;
if ($this->_sections['j']['show']) {
    $this->_sections['j']['total'] = $this->_sections['j']['loop'];
    if ($this->_sections['j']['total'] == 0)
        $this->_sections['j']['show'] = false;
} else
    $this->_sections['j']['total'] = 0;
if ($this->_sections['j']['show']):

            for ($this->_sections['j']['index'] = $this->_sections['j']['start'], $this->_sections['j']['iteration'] = 1;
                 $this->_sections['j']['iteration'] <= $this->_sections['j']['total'];
                 $this->_sections['j']['index'] += $this->_sections['j']['step'], $this->_sections['j']['iteration']++):
$this->_sections['j']['rownum'] = $this->_sections['j']['iteration'];
$this->_sections['j']['index_prev'] = $this->_sections['j']['index'] - $this->_sections['j']['step'];
$this->_sections['j']['index_next'] = $this->_sections['j']['index'] + $this->_sections['j']['step'];
$this->_sections['j']['first']      = ($this->_sections['j']['iteration'] == 1);
$this->_sections['j']['last']       = ($this->_sections['j']['iteration'] == $this->_sections['j']['total']);
?>
			
			<ul class="seo-text">
				<li class="menu-point">
					<span class="point"><?php echo $this->_sections['j']['index_next']; ?>
</span>
					<span class="text">
						<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['Article']['ArticleArticles'][$this->_sections['j']['index']]['CategoryID'])."&CategorySlug=".($this->_tpl_vars['Article']['ArticleArticles'][$this->_sections['j']['index']]['CategorySlug'])."&postID=".($this->_tpl_vars['Article']['ArticleArticles'][$this->_sections['j']['index']]['ArticleID'])."&postSlug=".($this->_tpl_vars['Article']['ArticleArticles'][$this->_sections['j']['index']]['ArticleSlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo $this->_tpl_vars['Article']['ArticleArticles'][$this->_sections['j']['index']]['ArticleTitle']; ?>
</a>
						<span class="date">
							от: <?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Article']['ArticleArticles'][$this->_sections['j']['index']]['ArticleDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'January', "Января") : smarty_modifier_replace($_tmp, 'January', "Января")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'February', "Февраля") : smarty_modifier_replace($_tmp, 'February', "Февраля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'March', "Марта") : smarty_modifier_replace($_tmp, 'March', "Марта")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'April', "Апреля") : smarty_modifier_replace($_tmp, 'April', "Апреля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'May', "Мая") : smarty_modifier_replace($_tmp, 'May', "Мая")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'June', "Июня") : smarty_modifier_replace($_tmp, 'June', "Июня")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'July', "Июля") : smarty_modifier_replace($_tmp, 'July', "Июля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'August', "Августа") : smarty_modifier_replace($_tmp, 'August', "Августа")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'September', "Сентября") : smarty_modifier_replace($_tmp, 'September', "Сентября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'October', "Октября") : smarty_modifier_replace($_tmp, 'October', "Октября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'November', "Ноября") : smarty_modifier_replace($_tmp, 'November', "Ноября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'December', "Декабря") : smarty_modifier_replace($_tmp, 'December', "Декабря")); ?>

						</span>
					</span>
				</li>
			</ul>
			
		<?php endfor; endif; ?>
		</div>
	<?php endif; ?>	
</div>