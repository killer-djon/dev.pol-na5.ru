<?php /* Smarty version 2.6.26, created on 2014-10-26 17:23:10
         compiled from news.frontend.list.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', 'news.frontend.list.tpl.html', 7, false),array('modifier', 'set_query_html', 'news.frontend.list.tpl.html', 7, false),array('modifier', 'escape', 'news.frontend.list.tpl.html', 13, false),)), $this); ?>
<h3>Новости</h3>

<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['news_posts']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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

<div class="post_block">

	<h2 class="post_title"><a href="<?php echo ((is_array($_tmp=((is_array($_tmp="?ukey=news&blog_id=")) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['NID']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['NID'])))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
"><?php echo $this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['title']; ?>
</a></h2>
	
	<div class="post_date"><?php echo $this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['add_date']; ?>
</div>
	
	<div class="post_content">
	<?php if ($this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['picture_exists']): ?>
		<img alt="<?php echo ((is_array($_tmp=$this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['title'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
" src="<?php echo @URL_PRODUCTS_PICTURES; ?>
/<?php echo ((is_array($_tmp=$this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['picture'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'url') : smarty_modifier_escape($_tmp, 'url')); ?>
" hspace="20" vspace="20" align="left" />
	<?php endif; ?>
	<?php echo $this->_tpl_vars['news_posts'][$this->_sections['i']['index']]['textToPublication']; ?>

	</div>
	
</div>
<?php endfor; else: ?>

<?php echo 'пустой список'; ?>


<?php endif; ?>
<?php if ($this->_tpl_vars['LastPage'] > 1): ?>
	<p>
	<?php if ($this->_tpl_vars['CurrentPage'] > 1): ?>
		&nbsp; <a class="no_underline" href='<?php echo ((is_array($_tmp="?ukey=news&news_page=".($this->_tpl_vars['CurrentPage']-1))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>&lt;&lt; <?php echo 'пред'; ?>
</a>
	<?php endif; ?>
	<?php $_from = $this->_tpl_vars['ListerRange']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['_page']):
?>
		&nbsp; <?php if ($this->_tpl_vars['_page'] != $this->_tpl_vars['CurrentPage']): ?><a class="no_underline" href='<?php echo ((is_array($_tmp="?ukey=news&news_page=".($this->_tpl_vars['_page']))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo $this->_tpl_vars['_page']; ?>
</a> <?php else: 
 echo $this->_tpl_vars['_page']; 
 endif; ?>
	<?php endforeach; endif; unset($_from); ?>
	<?php if ($this->_tpl_vars['CurrentPage'] < $this->_tpl_vars['LastPage']): ?>
		&nbsp; <a class="no_underline" href='<?php echo ((is_array($_tmp="?ukey=news&news_page=".($this->_tpl_vars['CurrentPage']+1))) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'><?php echo 'след'; ?>
 &gt;&gt;</a>
	<?php endif; ?>
	</p>
<?php endif; ?>