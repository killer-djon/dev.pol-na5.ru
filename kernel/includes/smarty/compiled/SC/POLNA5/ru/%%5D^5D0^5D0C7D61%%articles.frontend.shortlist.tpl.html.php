<?php /* Smarty version 2.6.26, created on 2014-10-16 18:20:59
         compiled from articles.frontend.shortlist.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'math', 'articles.frontend.shortlist.tpl.html', 8, false),array('modifier', 'set_query_html', 'articles.frontend.shortlist.tpl.html', 13, false),array('modifier', 'default', 'articles.frontend.shortlist.tpl.html', 17, false),array('modifier', 'translate', 'articles.frontend.shortlist.tpl.html', 17, false),array('modifier', 'date_format', 'articles.frontend.shortlist.tpl.html', 26, false),array('modifier', 'replace', 'articles.frontend.shortlist.tpl.html', 26, false),array('modifier', 'strip_tags', 'articles.frontend.shortlist.tpl.html', 40, false),array('modifier', 'truncate', 'articles.frontend.shortlist.tpl.html', 40, false),)), $this); ?>

 <?php if ($this->_tpl_vars['groupByCategory']): ?>	
	<?php unset($this->_sections['c']);
$this->_sections['c']['name'] = 'c';
$this->_sections['c']['loop'] = is_array($_loop=$this->_tpl_vars['ShortArticles']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['c']['show'] = true;
$this->_sections['c']['max'] = $this->_sections['c']['loop'];
$this->_sections['c']['step'] = 1;
$this->_sections['c']['start'] = $this->_sections['c']['step'] > 0 ? 0 : $this->_sections['c']['loop']-1;
if ($this->_sections['c']['show']) {
    $this->_sections['c']['total'] = $this->_sections['c']['loop'];
    if ($this->_sections['c']['total'] == 0)
        $this->_sections['c']['show'] = false;
} else
    $this->_sections['c']['total'] = 0;
if ($this->_sections['c']['show']):

            for ($this->_sections['c']['index'] = $this->_sections['c']['start'], $this->_sections['c']['iteration'] = 1;
                 $this->_sections['c']['iteration'] <= $this->_sections['c']['total'];
                 $this->_sections['c']['index'] += $this->_sections['c']['step'], $this->_sections['c']['iteration']++):
$this->_sections['c']['rownum'] = $this->_sections['c']['iteration'];
$this->_sections['c']['index_prev'] = $this->_sections['c']['index'] - $this->_sections['c']['step'];
$this->_sections['c']['index_next'] = $this->_sections['c']['index'] + $this->_sections['c']['step'];
$this->_sections['c']['first']      = ($this->_sections['c']['iteration'] == 1);
$this->_sections['c']['last']       = ($this->_sections['c']['iteration'] == $this->_sections['c']['total']);
?>
	
	<?php if ($this->_sections['c']['index']%$this->_tpl_vars['colomns'] == 0): 
 endif; ?>
		<!-- <td valign=top style="padding-bottom:10px;" width='<?php echo smarty_function_math(array('equation' => "100/x",'x' => $this->_tpl_vars['colomns']), $this);?>
%'> -->
		
			<h2 class="ArticleShort-category-title">
			<?php if ($this->_tpl_vars['ShortArticles'][$this->_sections['c']['index']]['CategoryID']): ?>
				<?php $this->assign('_category_url', ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['ShortArticles'][$this->_sections['c']['index']]['CategoryID'])."&CategorySlug=".($this->_tpl_vars['ShortArticles'][$this->_sections['c']['index']]['CategorySlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
			<?php else: ?>
				<?php $this->assign('_category_url', ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=1&CategorySlug=".(@CONF_ARTCLE_ROOT_URL))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp))); ?>
			<?php endif; ?>
			<a href="<?php echo $this->_tpl_vars['_category_url']; ?>
"><?php echo ((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['ShortArticles'][$this->_sections['c']['index']]['CategoryTitle'])) ? $this->_run_mod_handler('default', true, $_tmp, 'pgn_articles') : smarty_modifier_default($_tmp, 'pgn_articles')))) ? $this->_run_mod_handler('translate', true, $_tmp) : smarty_modifier_translate($_tmp)); ?>
</a>
			</h2>
			
			
			<?php $_from = $this->_tpl_vars['ShortArticles'][$this->_sections['c']['index']]['Articles']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['a'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['a']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['Article']):
        $this->_foreach['a']['iteration']++;
?>	
				<div class="news">
					
					<div class="news-date"><?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Article']['ArticleDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'January', "Января") : smarty_modifier_replace($_tmp, 'January', "Января")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'February', "Февраля") : smarty_modifier_replace($_tmp, 'February', "Февраля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'March', "Марта") : smarty_modifier_replace($_tmp, 'March', "Марта")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'April', "Апреля") : smarty_modifier_replace($_tmp, 'April', "Апреля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'May', "Мая") : smarty_modifier_replace($_tmp, 'May', "Мая")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'June', "Июня") : smarty_modifier_replace($_tmp, 'June', "Июня")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'July', "Июля") : smarty_modifier_replace($_tmp, 'July', "Июля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'August', "Августа") : smarty_modifier_replace($_tmp, 'August', "Августа")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'September', "Сентября") : smarty_modifier_replace($_tmp, 'September', "Сентября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'October', "Октября") : smarty_modifier_replace($_tmp, 'October', "Октября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'November', "Ноября") : smarty_modifier_replace($_tmp, 'November', "Ноября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'December', "Декабря") : smarty_modifier_replace($_tmp, 'December', "Декабря")); ?>

						
					</div>
				
					<div class="news-title">
						<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['Article']['CategoryID'])."&CategorySlug=".($this->_tpl_vars['Article']['CategorySlug'])."&postID=".($this->_tpl_vars['Article']['ArticleID'])."&postSlug=".($this->_tpl_vars['Article']['ArticleSlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" style="color: inherit; font-weight: inherit;" class="ArticleShort-articles-title"><?php echo $this->_tpl_vars['Article']['ArticleTitle']; ?>
</a>

					</div>
					
					
					<div class="news-intro">
						<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['Article']['ArticleBriefDescription'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 300, '..', false, false) : smarty_modifier_truncate($_tmp, 300, '..', false, false)); ?>

					</div>
					
					
				</div>
			<?php endforeach; endif; unset($_from); ?>

	<?php endfor; endif; ?>
	
 <?php else: ?>
 
 	<?php unset($this->_sections['a']);
$this->_sections['a']['name'] = 'a';
$this->_sections['a']['loop'] = is_array($_loop=$this->_tpl_vars['ShortArticles']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['a']['show'] = true;
$this->_sections['a']['max'] = $this->_sections['a']['loop'];
$this->_sections['a']['step'] = 1;
$this->_sections['a']['start'] = $this->_sections['a']['step'] > 0 ? 0 : $this->_sections['a']['loop']-1;
if ($this->_sections['a']['show']) {
    $this->_sections['a']['total'] = $this->_sections['a']['loop'];
    if ($this->_sections['a']['total'] == 0)
        $this->_sections['a']['show'] = false;
} else
    $this->_sections['a']['total'] = 0;
if ($this->_sections['a']['show']):

            for ($this->_sections['a']['index'] = $this->_sections['a']['start'], $this->_sections['a']['iteration'] = 1;
                 $this->_sections['a']['iteration'] <= $this->_sections['a']['total'];
                 $this->_sections['a']['index'] += $this->_sections['a']['step'], $this->_sections['a']['iteration']++):
$this->_sections['a']['rownum'] = $this->_sections['a']['iteration'];
$this->_sections['a']['index_prev'] = $this->_sections['a']['index'] - $this->_sections['a']['step'];
$this->_sections['a']['index_next'] = $this->_sections['a']['index'] + $this->_sections['a']['step'];
$this->_sections['a']['first']      = ($this->_sections['a']['iteration'] == 1);
$this->_sections['a']['last']       = ($this->_sections['a']['iteration'] == $this->_sections['a']['total']);
?>
		<div class="news">
			
			<div class="news-date">
				<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['ArticleDate'])) ? $this->_run_mod_handler('date_format', true, $_tmp, "%e %B %Y") : smarty_modifier_date_format($_tmp, "%e %B %Y")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'January', "Января") : smarty_modifier_replace($_tmp, 'January', "Января")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'February', "Февраля") : smarty_modifier_replace($_tmp, 'February', "Февраля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'March', "Марта") : smarty_modifier_replace($_tmp, 'March', "Марта")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'April', "Апреля") : smarty_modifier_replace($_tmp, 'April', "Апреля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'May', "Мая") : smarty_modifier_replace($_tmp, 'May', "Мая")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'June', "Июня") : smarty_modifier_replace($_tmp, 'June', "Июня")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'July', "Июля") : smarty_modifier_replace($_tmp, 'July', "Июля")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'August', "Августа") : smarty_modifier_replace($_tmp, 'August', "Августа")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'September', "Сентября") : smarty_modifier_replace($_tmp, 'September', "Сентября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'October', "Октября") : smarty_modifier_replace($_tmp, 'October', "Октября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'November', "Ноября") : smarty_modifier_replace($_tmp, 'November', "Ноября")))) ? $this->_run_mod_handler('replace', true, $_tmp, 'December', "Декабря") : smarty_modifier_replace($_tmp, 'December', "Декабря")); ?>

			</div>
			
			<div class="news-title">
				<a href="<?php echo ((is_array($_tmp="?ukey=".(@CONF_ARTCLE_URL)."&CategoryID=".($this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['CategoryID'])."&CategorySlug=".($this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['CategorySlug'])."&postID=".($this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['ArticleID'])."&postSlug=".($this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['ArticleSlug']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
" style="color: inherit; font-weight: inherit;" class="ArticleShort-articles-title"><?php echo $this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['ArticleTitle']; ?>
</a>			
				
			</div>	
				
			
			
			<div class="news-intro">
				<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['ShortArticles'][$this->_sections['a']['index']]['ArticleBriefDescription'])) ? $this->_run_mod_handler('strip_tags', true, $_tmp) : smarty_modifier_strip_tags($_tmp)))) ? $this->_run_mod_handler('truncate', true, $_tmp, 80, '..', false, false) : smarty_modifier_truncate($_tmp, 80, '..', false, false)); ?>

			</div>
			
		</div>
	<?php endfor; endif; ?>
	
<?php endif; ?>