<?php /* Smarty version 2.6.26, created on 2014-10-17 15:17:04
         compiled from articles.frontend.list.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'articles.frontend.list.tpl.html', 2, false),array('modifier', 'translate', 'articles.frontend.list.tpl.html', 2, false),array('modifier', 'set_query_html', 'articles.frontend.list.tpl.html', 3, false),array('modifier', 'date_format', 'articles.frontend.list.tpl.html', 13, false),array('modifier', 'replace', 'articles.frontend.list.tpl.html', 13, false),array('modifier', 'escape', 'articles.frontend.list.tpl.html', 21, false),)), $this); ?>
<h1 class="ArticleList-category-title">
	<?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['Category']['CategoryTitle'])) ? $this->_run_mod_handler('default', true, $_tmp, 'pgn_articles') : smarty_modifier_default($_tmp, 'pgn_articles')))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>

	<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&rss=rss")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><img src="<?php echo @URL_IMAGES_COMMON; ?>
/rss-feed.png" alt="RSS 2.0"  style="padding-left:10px;"></a>
</h1>

<div class="articles-list">
	
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['Articles']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	
		<div class="article">
			<div class="article-date">
				<div class="date-box">
					<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "<span class='day'>%e</span> <span class='month'>%B</span>") : smarty_modifier_date_format($_tmp, "<span class='day'>%e</span> <span class='month'>%B</span>")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'January', "Янв") : smarty_modifier_replace($_tmp, 'January', "Янв")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'February', "Фев") : smarty_modifier_replace($_tmp, 'February', "Фев")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'March', "Мар") : smarty_modifier_replace($_tmp, 'March', "Мар")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'April', "Апр") : smarty_modifier_replace($_tmp, 'April', "Апр")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'May', "Мая") : smarty_modifier_replace($_tmp, 'May', "Мая")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'June', "Июн") : smarty_modifier_replace($_tmp, 'June', "Июн")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'July', "Июл") : smarty_modifier_replace($_tmp, 'July', "Июл")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'August', "Авг") : smarty_modifier_replace($_tmp, 'August', "Авг")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'September', "Сен") : smarty_modifier_replace($_tmp, 'September', "Сен")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'October', "Окт") : smarty_modifier_replace($_tmp, 'October', "Окт")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'November', "Ноя") : smarty_modifier_replace($_tmp, 'November', "Ноя")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'December', "Дек") : smarty_modifier_replace($_tmp, 'December', "Дек")); ?>

				</div>
			</div>
			
			<div class="article-content">
				<div class="article-img">
					<?php if ($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleDefaultSmallPicture']): ?>
						<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['CategoryID'])."&CategorySlug=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['CategorySlug'])."&postID=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleID'])."&postSlug=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleSlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
">
						<img alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleTitle'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo @URL_ARTICLES_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleDefaultSmallPicture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" align="left" style="margin:10" class="ArticleList-articles-img"/>
						</a>
					<?php endif; ?>
				</div>
				
				<div class="article-title">
					<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['CategoryID'])."&CategorySlug=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['CategorySlug'])."&postID=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleID'])."&postSlug=".($this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleSlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo $this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleTitle']; ?>
</a>
				</div>
				
				<div class="article-intro">
					<?php echo $this->_tpl_vars['Articles'][$this->_sections['i']['index']]['ArticleBriefDescription']; ?>

				</div>
				
			</div>
		</div>
	<?php endfor; else: ?>
		<center><?php echo 'пустой список'; ?>
</center>
	<?php endif; ?>
	
	
		
	<?php if ($this->_tpl_vars['NavigatorHtml']): ?><br><br><div><?php echo $this->_tpl_vars['NavigatorHtml']; ?>
</div><?php endif; ?>

</div>