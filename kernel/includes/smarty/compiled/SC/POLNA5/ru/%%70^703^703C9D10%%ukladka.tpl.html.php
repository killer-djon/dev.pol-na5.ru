<?php /* Smarty version 2.6.26, created on 2014-10-16 18:20:59
         compiled from ukladka.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'set_query_html', 'ukladka.tpl.html', 3, false),)), $this); ?>
<div id="send_ukladka" title="Заказать укладку" data-width="600">
	<p class="validateTips">Поля отмеченные знаком <font color="red">*</font>обязательны для заполнения</p>
	<form name="form-send_ukladka" method="post" action="<?php echo ((is_array($_tmp='')) ? $this->_run_mod_handler('set_query_html', true, $_tmp) : smarty_modifier_set_query_html($_tmp)); ?>
">
		<input type="hidden" name="action" value="ukladka" />
		
		<div class="rowElem">
			<label for="user_name">Ваше имя <font color="red">*</font>:</label>
			<input type="text" id="user_name" title="Ваше имя" name="user_name" value="" />
		</div>
		<div class="rowElem">
			<label for="user_email">Ваш Email <font color="red">*</font>:</label>
			<input type="text" id="user_email" name="user_email" value="" />
		</div>
		<div class="rowElem">
			<label for="user_phone">Номер телефона :</label>
			<input type="text" id="user_phone" name="user_phone" value="" />
		</div>
		<div class="rowElem">
			<label for="user_msg">Коментарий к заказу:</label>
			<textarea name="user_msg" id="user_msg" rows="3"></textarea>
		</div>
	</form>
	
</div>