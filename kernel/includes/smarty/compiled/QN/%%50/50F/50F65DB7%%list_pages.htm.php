<?php /* Smarty version 2.6.26, created on 2014-04-02 17:15:53
         compiled from ../../../common/html/cssbased/pageelements/ajax/list_pages.htm */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'cat', '../../../common/html/cssbased/pageelements/ajax/list_pages.htm', 1, false),array('function', 'count', '../../../common/html/cssbased/pageelements/ajax/list_pages.htm', 3, false),array('function', 'math', '../../../common/html/cssbased/pageelements/ajax/list_pages.htm', 16, false),)), $this); ?>
<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['numDocumentsLabel'])) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")))) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['numDocuments']) : smarty_modifier_cat($_tmp, $this->_tpl_vars['numDocuments'])))) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')); ?>

<?php if ($this->_tpl_vars['showPageSelector']): ?>
	<?php echo smarty_function_count(array('var' => $this->_tpl_vars['pages'],'assign' => 'pageNum'), $this);?>


	<?php echo ((is_array($_tmp=$this->_tpl_vars['kernelStrings']['app_pages_text'])) ? $this->_run_mod_handler('cat', true, $_tmp, ": ") : smarty_modifier_cat($_tmp, ": ")); ?>


		<?php if ($this->_tpl_vars['pageNum'] <= 10): ?>
			<?php $_from = $this->_tpl_vars['pages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['page_item']):
?>
				<?php if ($this->_tpl_vars['currentPage'] != $this->_tpl_vars['page_item'][0]): ?>
					<a class=activelink href="javascript:void(0)" onClick="AjaxLoader.loadPage('<?php echo $this->_tpl_vars['page_item'][1]; ?>
')"><?php echo $this->_tpl_vars['page_item'][0]; ?>
</a> 
				<?php else: ?>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['page_item'][0])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')); ?>

				<?php endif; ?>
			<?php endforeach; endif; unset($_from); ?>
		<?php else: ?>
			<?php echo smarty_function_math(array('equation' => "cnt-1",'cnt' => $this->_tpl_vars['pageNum'],'assign' => 'lastIndex'), $this);?>

			<?php echo smarty_function_math(array('equation' => "cur-5",'cur' => $this->_tpl_vars['currentPage'],'assign' => 'startValue'), $this);?>

			<?php echo smarty_function_math(array('equation' => "cur+4",'cur' => $this->_tpl_vars['currentPage'],'assign' => 'endValue'), $this);?>


			<?php if ($this->_tpl_vars['startValue'] < 0): ?>
				<?php $this->assign('startValue', 0); ?>
			<?php endif; ?>

			<?php if ($this->_tpl_vars['endValue'] > $this->_tpl_vars['lastIndex']): ?>
				<?php $this->assign('endValue', $this->_tpl_vars['lastIndex']); ?>
			<?php endif; ?>

			<?php echo smarty_function_math(array('equation' => "max - min",'max' => $this->_tpl_vars['endValue'],'min' => $this->_tpl_vars['startValue'],'assign' => 'diff'), $this);?>

			<?php if ($this->_tpl_vars['diff'] < 10): ?>
				<?php echo smarty_function_math(array('equation' => "10-diff",'diff' => $this->_tpl_vars['diff'],'assign' => 'correction'), $this);?>

				<?php if ($this->_tpl_vars['startValue'] == 0): ?>
					<?php echo smarty_function_math(array('equation' => "cur+correction-1",'cur' => $this->_tpl_vars['endValue'],'correction' => $this->_tpl_vars['correction'],'assign' => 'endValue'), $this);?>

					<?php if ($this->_tpl_vars['endValue'] > $this->_tpl_vars['lastIndex']): ?>
						<?php $this->assign('endValue', $this->_tpl_vars['lastIndex']); ?>
					<?php endif; ?>
				<?php endif; ?>
				
				<?php if ($this->_tpl_vars['endValue'] == $this->_tpl_vars['lastIndex']): ?>
					<?php echo smarty_function_math(array('equation' => "cur-correction+1",'cur' => $this->_tpl_vars['startValue'],'correction' => $this->_tpl_vars['correction'],'assign' => 'startValue'), $this);?>

					<?php if ($this->_tpl_vars['startValue'] < 0): ?>
						<?php $this->assign('startValue', 0); ?>
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>

			<?php echo smarty_function_math(array('equation' => "max - min + 1",'max' => $this->_tpl_vars['endValue'],'min' => $this->_tpl_vars['startValue'],'assign' => 'iterations'), $this);?>


			<?php if ($this->_tpl_vars['startValue'] > 0): ?>
				<a class=activelink onClick="AjaxLoader.loadPage(this.href); return false;" href="<?php echo $this->_tpl_vars['pages'][0][1]; ?>
"><nobr><?php echo ((is_array($_tmp=$this->_tpl_vars['pages'][0][0])) ? $this->_run_mod_handler('cat', true, $_tmp, "...") : smarty_modifier_cat($_tmp, "...")); ?>
</nobr></a> 
			<?php endif; ?>

			<?php unset($this->_sections['pageIdx']);
$this->_sections['pageIdx']['name'] = 'pageIdx';
$this->_sections['pageIdx']['loop'] = is_array($_loop=$this->_tpl_vars['pages']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['pageIdx']['start'] = (int)$this->_tpl_vars['startValue'];
$this->_sections['pageIdx']['max'] = (int)$this->_tpl_vars['iterations'];
$this->_sections['pageIdx']['show'] = true;
if ($this->_sections['pageIdx']['max'] < 0)
    $this->_sections['pageIdx']['max'] = $this->_sections['pageIdx']['loop'];
$this->_sections['pageIdx']['step'] = 1;
if ($this->_sections['pageIdx']['start'] < 0)
    $this->_sections['pageIdx']['start'] = max($this->_sections['pageIdx']['step'] > 0 ? 0 : -1, $this->_sections['pageIdx']['loop'] + $this->_sections['pageIdx']['start']);
else
    $this->_sections['pageIdx']['start'] = min($this->_sections['pageIdx']['start'], $this->_sections['pageIdx']['step'] > 0 ? $this->_sections['pageIdx']['loop'] : $this->_sections['pageIdx']['loop']-1);
if ($this->_sections['pageIdx']['show']) {
    $this->_sections['pageIdx']['total'] = min(ceil(($this->_sections['pageIdx']['step'] > 0 ? $this->_sections['pageIdx']['loop'] - $this->_sections['pageIdx']['start'] : $this->_sections['pageIdx']['start']+1)/abs($this->_sections['pageIdx']['step'])), $this->_sections['pageIdx']['max']);
    if ($this->_sections['pageIdx']['total'] == 0)
        $this->_sections['pageIdx']['show'] = false;
} else
    $this->_sections['pageIdx']['total'] = 0;
if ($this->_sections['pageIdx']['show']):

            for ($this->_sections['pageIdx']['index'] = $this->_sections['pageIdx']['start'], $this->_sections['pageIdx']['iteration'] = 1;
                 $this->_sections['pageIdx']['iteration'] <= $this->_sections['pageIdx']['total'];
                 $this->_sections['pageIdx']['index'] += $this->_sections['pageIdx']['step'], $this->_sections['pageIdx']['iteration']++):
$this->_sections['pageIdx']['rownum'] = $this->_sections['pageIdx']['iteration'];
$this->_sections['pageIdx']['index_prev'] = $this->_sections['pageIdx']['index'] - $this->_sections['pageIdx']['step'];
$this->_sections['pageIdx']['index_next'] = $this->_sections['pageIdx']['index'] + $this->_sections['pageIdx']['step'];
$this->_sections['pageIdx']['first']      = ($this->_sections['pageIdx']['iteration'] == 1);
$this->_sections['pageIdx']['last']       = ($this->_sections['pageIdx']['iteration'] == $this->_sections['pageIdx']['total']);
?>
				<?php $this->assign('page_item', $this->_tpl_vars['pages'][$this->_sections['pageIdx']['index']]); ?>

				<?php if ($this->_tpl_vars['currentPage'] != $this->_tpl_vars['page_item'][0]): ?>
					<a class=activelink onClick="AjaxLoader.loadPage(this.href); return false;" href="<?php echo $this->_tpl_vars['page_item'][1]; ?>
"><?php echo $this->_tpl_vars['page_item'][0]; ?>
</a> 
				<?php else: ?>
					<?php echo ((is_array($_tmp=$this->_tpl_vars['page_item'][0])) ? $this->_run_mod_handler('cat', true, $_tmp, ' ') : smarty_modifier_cat($_tmp, ' ')); ?>

				<?php endif; ?>
			<?php endfor; endif; ?>
		<?php endif; ?>

		<?php if ($this->_tpl_vars['endValue'] < $this->_tpl_vars['lastIndex']): ?>
				<a class=activelink onClick="AjaxLoader.loadPage(this.href); return false;" href="<?php echo $this->_tpl_vars['pages'][$this->_tpl_vars['lastIndex']][1]; ?>
"><nobr><?php echo ((is_array($_tmp="...")) ? $this->_run_mod_handler('cat', true, $_tmp, $this->_tpl_vars['pages'][$this->_tpl_vars['lastIndex']][0]) : smarty_modifier_cat($_tmp, $this->_tpl_vars['pages'][$this->_tpl_vars['lastIndex']][0])); ?>
</nobr></a> 
		<?php endif; ?>

<?php endif; ?>