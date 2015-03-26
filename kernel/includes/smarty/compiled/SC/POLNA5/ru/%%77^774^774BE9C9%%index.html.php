<?php /* Smarty version 2.6.26, created on 2014-10-16 18:20:59
         compiled from index.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'component', 'index.html', 4, false),array('modifier', 'set_query', 'index.html', 35, false),)), $this); ?>
	<div class="header">
		<div class="head-menu">
			<div class="menu-content">
				<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'auxpages_navigation','select_pages' => 'selected','auxpages' => '11:12:13:14:15:16:17','view' => 'horizontal','overridestyle' => ':sl6q05'), $this);?>
<!-- cpt_container_end -->
				
				<div class="personal-cabinet">
					<ul class="point-cabinet horizontal">
						<li class="cabinet-point"><a href="#" class="">Личный кабинет</a>
							<ul class="sub-menu vertical">
								<li><a href="" class="">Войти</a></li>
								<li class="separator horizontal"></li>	
								<li><a href="" class="">Регистрация</a></li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
		</div>
		
		<div class="header-content">
			<div class="content">
				<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'logo','file' => 'laminat_logo.png','overridestyle' => ':ito282'), $this);?>
<!-- cpt_container_end -->
				<div class="contact-phones">
					<span class="phone">+7 (499) 130-13-69</span>
					<span class="phone">+7 (925) 390-13-35</span>
					<a href="#" class="call-me" rel="callme_back">Перезвоните мне</a>
				</div>
				
				<div class="block ukladka">
					<a href="" class="call-me" rel="send_ukladka"><h3>Заказать укладку</h3>
					<div>Вы можете у нас</div></a>
				</div>
				
				<div class="block basket">
					<a class="<?php echo $this->_tpl_vars['checkout_class']; ?>
 cartContainer" rel="nofollow" href="<?php echo ((is_array($_tmp="?ukey=cart")) ? $this->_run_mod_handler('set_query', true, $_tmp) : smarty_modifier_set_query($_tmp)); ?>
"><!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'shopping_cart_info','overridestyle' => ':gb48s3'), $this);?>
<!-- cpt_container_end --></a>
				</div>
				
			</div>
			
			<div class="top-menu">
				<ul class="topmenu horizontal">
					<li class="menu-point"><a href="/" class="">Главная</a></li>
					<li class="menu-point"><a href="/category/" class="">Каталог</a></li>
					<li class="menu-point"><a href="/specialoffers/" class="">Акции</a></li>
					<li class="menu-point"><a href="/oplata-i-dostavka/" class="">Оплата и доставка</a></li>
					<li class="menu-point"><a href="/feedback/" class="">Контакты</a></li>
				</ul>

<div class="search-form"><!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'product_search','overridestyle' => ':yxwt7y'), $this);?>
<!-- cpt_container_end --> </div>
			</div>
			
		</div>
		
	</div>
	
	<div class="wrap-content">
	
		<div class="top-banner">
			<div class="lenta"></div>
			
			<div class="content">
				<div class="text-action">
					
					<div class="top">
						При покупке ламината площадью более 50 кв.м. <br />
						<span>Вы получаете скидку в 20% </span> <br />
						<font style="font-size: 11px;">* - Подробности у наших менеджеров или в разделе <a href="/specialoffers/">Акции и скидки</a></font>
					</div>
					
				</div>
				
				<div class="img-sctions">
					<img src="<?php echo @URL_IMAGES; ?>
/actions.png" alt="" />
				</div>
				
			</div>
		</div>
	
		<!-- start main content -->
		
		<div class="page">
			
			<div class="left-sidebar">
				
				<div class="left-menu">
					<h3>Каталог</h3>
					<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'category_tree','overridestyle' => ':ap8qnr'), $this);?>
<!-- cpt_container_end -->
				</div>
				
				<div class="left-news">
					<h3>Новости</h3>
					
					<div class="news-block">
					
						<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'articles_short_list','articles_by_categoryes' => 'false','articles_count_categoryes' => '3','articles_count_without_categoryes' => '3','articles_sub_categoryes' => '','articles_categoryes' => 'selected','articles_categoryes_select' => '2','articles_colomns' => '1','articles_order' => 'date','overridestyle' => ':0ij22x'), $this);
 echo smarty_function_component(array('cpt_id' => 'custom_html','code' => '1pxjc3ai','overridestyle' => ':c3zyqg'), $this);?>
<!-- cpt_container_end -->

					</div>
				</div>
				
				<div class="left-news">
					<h3>Полезные статьи</h3>
					<div class="news-block">
						<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'articles_short_list','articles_by_categoryes' => 'false','articles_count_categoryes' => '3','articles_count_without_categoryes' => '4','articles_sub_categoryes' => '','articles_categoryes' => 'selected','articles_categoryes_select' => '3','articles_colomns' => '1','articles_order' => 'date','overridestyle' => ':mvm6jo'), $this);
 echo smarty_function_component(array('cpt_id' => 'custom_html','code' => 's9ztq81p','overridestyle' => ':4sarer'), $this);?>
<!-- cpt_container_end -->
					</div>
				</div>	
				
			</div>
			
			<div class="right-sidebar">
				<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'maincontent','overridestyle' => ''), $this);?>
<!-- cpt_container_end -->
				
				
				
			</div>
			
		</div>
		
		<!-- end main content -->
	
	</div>
	
	<div class="wrap-footer">
		
		<div class="footer-content">
			
			<div class="socline">
				<div class="title">Мы в социальных сетях:</div>
				<div class="soc-block">
					<div class="block vkontakte"></div>
					<div class="block odnoklassniki"></div>
					<div class="block facebook"></div>
				</div>
				<div>ОАО "Ламинат" Все права защищены</div>
			</div>
			<div class="contact-info">
				<div class="vcard">
				 <div>
				   <span class="category">ТВК</span>
				   <span class="fn org">"Экспострой"</span>
				 </div>
				 <div class="adr">
				   <span class="locality">г. Москва</span>,
				   <span class="postal-code">117218</span>,
				   <span class="street-address">Нахимовский проспект, 24</span>
				 </div>
				 <div>Телефон: <span class="tel">+7 (499) 130-13-69</span></div>
				 <div>Мы работаем: <span class="workhours">пн-сб 10:00-20:00, вс 10:00-19:00</span>
				   <span class="url">
				     <span class="value-title" title=""> </span>
				   </span>
				 </div>
				</div>

			</div>
			<div class="footer-menu">
				<!-- cpt_container_start --><?php echo smarty_function_component(array('cpt_id' => 'auxpages_navigation','select_pages' => 'selected','auxpages' => '11:12:13:14:15:16:17','view' => 'vertical','overridestyle' => ':sl6q05'), $this);?>
<!-- cpt_container_end -->
			</div>
			
		</div>
		
	</div>