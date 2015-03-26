<?php /* Smarty version 2.6.26, created on 2014-10-18 14:39:44
         compiled from chooser.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'chooser.tpl.html', 25, false),)), $this); ?>

<div class="chooser-block">
	<h3>Подбор товара <span class="small"> / Выберите материал</span></h3>
	<div class="top-menu">
		<ul class="topmenu horizontal">
			<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['root_categories']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				<?php if ($this->_tpl_vars['root_categories'][$this->_sections['i']['index']]['categoryID'] != 1): ?>
					<?php $this->assign('_category_url', "/chooser/?categoryID=".($this->_tpl_vars['root_categories'][$this->_sections['i']['index']]['categoryID'])); ?>
					<li class="menu-point <?php if ($this->_tpl_vars['categoryID'] == $this->_tpl_vars['root_categories'][$this->_sections['i']['index']]['categoryID']): ?>active<?php endif; ?>"><a href="<?php echo $this->_tpl_vars['_category_url']; ?>
" alt="<?php echo $this->_tpl_vars['root_categories'][$this->_sections['i']['index']]['name']; ?>
"><?php echo $this->_tpl_vars['root_categories'][$this->_sections['i']['index']]['name']; ?>
</a></li>
				<?php endif; ?>
			<?php endfor; endif; ?>
		</ul>
	</div>
	
	<?php if (! $this->_tpl_vars['categoryID'] || $this->_tpl_vars['categoryID'] == 0): ?>
	
		<div class="filter-block">
			Необходимо выбрать категорию 
		</div>
	
	<?php else: ?>
		
		<div class="filter-block">
			<h3>Фильтр <a href="#" class="show">Свернуть</a></h3>
			<form name="choose" id="choose" method="POST" action='<?php echo ((is_array($_tmp="?ukey=chooser")) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
'>
			<div class="filter option">
				<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['options']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
				
					<?php if ($this->_tpl_vars['options'][$this->_sections['i']['index']]['count_variants']): ?>
						<div class="options-row">
							<label for="optionRow_<?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['optionID']; ?>
"><?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['name_ru']; ?>
</label>
							<select name="optionRow_<?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['optionID']; ?>
" id="optionRow_<?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['optionID']; ?>
">
								<?php unset($this->_sections['j']);
$this->_sections['j']['name'] = 'j';
$this->_sections['j']['loop'] = is_array($_loop=$this->_tpl_vars['options'][$this->_sections['i']['index']]['variants']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
									<option value="<?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['variants'][$this->_sections['j']['index']]['variantID']; ?>
"><?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['variants'][$this->_sections['j']['index']]['option_value_ru']; ?>
</option>
								<?php endfor; endif; ?>
							</select>
							<span class="info-question"></span>
							<div class="option-description">
								<?php echo $this->_tpl_vars['options'][$this->_sections['i']['index']]['description_text_ru']; ?>

							</div>
						</div>
					<?php endif; ?>
					
				<?php endfor; endif; ?>
			</div>
			
			<div class="price option">
				<div class="blockoption label">Цена, в руб.: </div>
				<div class="blockoption input">
					<label for="priceFrom">от</label>
					<input name="priceFrom" type="text" size="6" pattern="[\d]+" placeholder="0" value="" />
				</div>
				<div class="blockoption input">
					<label for="priceFrom">до</label>
					<input name="priceTo" type="text" size="6" pattern="[\d]+" placeholder="1000" value="" />
				</div>
				<div class="blockoption submit">
					<input type="submit" name="submit_filter" value="Подобрать" />
				</div>
			</div>
			
		</div>
		
		</form>
	<?php endif; ?>
	
	<div class="result-products catalog">
		
	</div>
	
</div>