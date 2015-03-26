PDApplacation = newClass(null, {
	constructor: function() {

		this.readerAlbums = this.createStore('backend.php?controller=ajax&action=albumList');
		this.readerImages = this.createStore('backend.php?controller=ajax&action=imageList');
		this.readerImageOne = this.createStore('backend.php?controller=ajax&action=imageOne');
		
		this.albumManager = new AlbumManager({
			elem: jQuery('album-content-body').get(0),
			readerAlbums: this.readerAlbums,
			readerImages: this.readerImages,
			readerImageOne: this.readerImageOne
		});
		
		
	},
	createStore: function(url_) {
		// Create data reader
		var reader = new WbaReader ({
			url: url_,
			baseParams: {}
		});		
		reader.addListener("success", function(responseData) {
		});
		
		return reader;
	},
	refreshData: function () {
	}
});

//Right object default
Rights = {
	create_album: -1,
	manage_album: -1,
	manage_collections: -1,
	modify_design: -1, 
	album: -1,
	
	isRead: function(){
		return this.album >= 1
	},
	isWrite: function(){
		return this.album >= 3
	},
	isFull: function(){
		return this.album == 7
	}	
};

$().ajaxComplete(function(request, settings){
	if ( settings.responseText.match(/Allowed memory/) ) {
		alert($.sprintf('Недостаточно памяти для выполнения операции (memory_limit=%s)', document.memory_limit));
	}
});

WbaReader = newClass(null, {
	constructor: function(param) {
		this.callbackList = new Array();
		this.url = param.url;
		this.baseParams = param.baseParams; 
	},
	
	addListener: function(eventName, callback) {
		this.callbackList.push(callback);
	},
	
	load: function(param) {
		var url = this.url;
		jQuery.post(this.url, param, function( data, textStatus ){

//			if ( typeof(data) == 'string' ) {
//				console.debug(data);
//			}
			
			if ( data.redirectUrl ) {
				if ( window.parent )
					window.parent.location =  data.redirectUrl;
				else
					window.location =  data.redirectUrl;
				return false;
			}
			
			if ( data.status == 'ERR' ) {
				alert( data.error );
				return false;
			}
			
			for(var i=0; i<this.callbackList.length; i++) {
				this.callbackList[i](data);
			}
		}.bind(this), "json");
	}
});
StateName = {
	Alubms : 'Albums',
	ImageTile : 'ImageTile',
	ImageDetail : 'ImageDetail',	
	ImageOne : 'ImageOne',
	Back : 'Back'
};

AlbumManager = newClass(null, {
	constructor: function (config) {		
		this.config = config;
		this.contentElem = config.elem;
		
		this.resizer = new WAResize();
		
		//hashe image
		this.imageHashe = new Object();
		//album load list
		this.albumList = new Array();

		Helper.setManager(this);
		
		this.config.readerAlbums.addListener("changed", this.storeChangeHandle, this);
		this.config.readerImages.addListener("changed", this.storeChangeHandle, this);
		this.config.readerImageOne.addListener("changed", this.storeChangeHandle, this);
		
		//////// inicialize
		if ( !jQuery.cookie('imageSize') ) {
			document.imageSize = 144;
			jQuery.cookie('imageSize', 144);
		}
		else {
			document.imageSize = jQuery.cookie('imageSize');
		}
		
		$().ajaxStart(function(){
			Helper.getManager().startProccess();
		});
		$().ajaxSuccess(function(e, data){
			Helper.getManager().stopProccess();
		});
		
		WBS.History.init();
		
		WBS.History.on('change', this.hashUrlChange);
		
		this.templateBroker = new TemplateBroker();
		this.moduleBroker = new ModuleBroker();
		
		this.initKey();
		
		var limit = $.cookie('limit') || 50;
		
		this.state = { limit: limit, offset: 0 };
		
		//this.setState({ stateName: StateName.Alubms });
		
		this.hashUrlChange( WBS.History._getToken() );
	},
	
	startProccess: function() {
		if (window.parent.showHideLoading)
		window.parent.showHideLoading(true);		
		
		$('.sidebar-btn').addClass('sidebar-btn-disable');
	},
	stopProccess: function() {
		if (window.parent.showHideLoading && !document.isProcessSleep) {
			window.parent.showHideLoading(false);			
		}
		
		
		$('.sidebar-btn').removeClass('sidebar-btn-disable');
	},
	initKey: function() {
		WbsData.set({'isKeyEnable': true});
		
		var keySpace = function(){		
			if ( WbsData.get().isKeyEnable ) {
				if ( Helper.getManager().getState().stateName == StateName.ImageOne ) {
					if (WbsData.get('nextIdPhoto', null))
						Helper.getManager().setState({
							viewName: "ImageOn",
							imageId: WbsData.get('nextIdPhoto')
						});
				}
				else if (Helper.getManager().getState().stateName == StateName.ImageTile) {
					var offset = Helper.getManager().getState().offset;
					var limit = Helper.getManager().getState().limit;
					var total = Data.total;
					if ( offset < total - limit )
					Helper.getManager().setState({							
						offset: parseInt(offset) + parseInt(limit)
						
					});
				}
			}
		}.bind(this);		
		jQuery(document).bind('keydown', 'space' , keySpace);

		var keyRight = function (){
			
			if ( WbsData.get().isKeyEnable ) {
				if ( Helper.getManager().getState().stateName == StateName.ImageOne ) {
					if (WbsData.get('nextIdPhoto', null))
						Helper.getManager().setState({
							viewName: "ImageOn",
							imageId: WbsData.get('nextIdPhoto')
						});
				}
				else if (Helper.getManager().getState().stateName == StateName.ImageTile) {
					var offset = Helper.getManager().getState().offset;
					var limit = Helper.getManager().getState().limit;
					var total = Data.total;
					if ( offset < total - limit ) {
						if ( $('#arrange-link').hasClass('opened') )
							Helper.getManager().setStateParam({arrange: true});

						Helper.getManager().setState({							
							offset: parseInt(offset) + parseInt(limit)
						});
					}
				}
			}

		}.bind(this);

		jQuery(document).bind('keydown', 'right' , keyRight);
		jQuery(document).bind('keydown', 'Ctrl+right' , keyRight);
		
		
		var keyLeft = function(){
			if ( WbsData.get().isKeyEnable 	) {
				if ( Helper.getManager().getState().stateName == StateName.ImageOne ) {
					if (WbsData.get('prevIdPhoto', null))
						Helper.getManager().setState({
							viewName: "ImageOn",
							imageId: WbsData.get('prevIdPhoto')
						});
				}
				else if (Helper.getManager().getState().stateName == StateName.ImageTile) {
					var offset = Helper.getManager().getState().offset;
					var limit = Helper.getManager().getState().limit;
					
					if ( offset > 0 ) {
						if ( $('#arrange-link').hasClass('opened') )
							Helper.getManager().setStateParam({arrange: true});

						Helper.getManager().setState({							
							offset: offset - limit
						});
					}
				}
			}			
		}.bind(this);
		
		jQuery(document).bind('keydown', 'left' , keyLeft);		
		jQuery(document).bind('keydown', 'Ctrl+left' , keyLeft);
		
//		jQuery(document).bind('keydown', 'Esc' , this.keyEsc);
		
	},	
	getState: function () {
		return this.state;
	},
	keyEsc: function() {
		switch (Helper.getManager().getState().stateName) {
			case StateName.Alubms: {
				break;
			}
			case StateName.ImageTile: {
				Helper.getManager().setState({
					stateName: StateName.Alubms 
				});
				break;
			}
			case StateName.ImageOne: {
				Helper.getManager().setState({
					stateName: StateName.ImageTile 
				});
				break;
			}		
		}
		return false;
	},
	
	hashUrlChange: function(token) {
		if (!token) token = '#';
			if ( token == document.myState ) {
				return false;
			}
			
			var album = /albums/.exec(token);
			var imageTile = /ImageTile\/([0-9]+)/.exec(token);
			var imageOne = /ImageOne\/([0-9]+)/.exec(token);
			var page = /page\/([0-9]+)/.exec(token);
			page = (page) ? page [1] : 0;
			
			if (album) {
				Helper.getManager().setState({
					stateName: StateName.Alubms,
					offset: page
				});				
			}
			else if ( imageTile ) {
				Helper.getManager().setState({
					stateName: StateName.ImageTile,
					albumId: imageTile[1],
					offset: page
				});				
			}
			else if ( imageOne ) {
				Helper.getManager().setState({
					stateName: StateName.ImageOne,
					imageId: imageOne[1]
				});
			}
			else {
				Helper.getManager().setState({
					stateName: StateName.Alubms
				});
			}
	}.bind(this),

	openUploadDlg: function() {
		this.fup.selectFiles();
	},
	getUploadDlg: function () {
		if (this.uploadDlg == null)
			this.uploadDlg = new WbsUploadDlg({
				contentElemId: 'dlg-upload-content',
				cls:"upload-dlg", 
				contentElemId: "dlg-upload-content",
				uploadURL: "../../../../PD/ajax/file_upload.php",
				ieUploadURL: "../../PD/ajax/file_upload.php",
				swfURL: "../../common/html/res/swfupload/swfupload.swf",
				sessID: jQuery.cookie('PHPSESSID')
			});
		return this.uploadDlg;
	},

	storeChangeHandle: function (store) {
		Helper.getManager().updateState(store);
	},
	
	updateState: function(store) {
		Data = store;
		if (store.RIGHTS) {
			$.extend(Rights, store.RIGHTS);
			
		}
		if (store.RIGHT) {
			Rights.album = store.RIGHT;
		}
		
		if ( store ) {
			this.templateBroker.updateState(this.state, store);
			this.moduleBroker.updateState(this.state, store);
		} else {
			this.templateBroker.updateState(this.state);
		}
		
		if ( this.callback ) this.callback();
		this.callback = null;
	},
	
	setStateParam: function(param) {
		for (i in param) {
			this.state[i] = param[i];
		}
	},

	setState: function(param, callback) {
		param = param || {};
		if( (param.stateName == StateName.ImageTile) && (param.albumId)  ) {
			this.setStateParam( { offset: 0 } );			
		}

		if ($.isFunction(callback) ) {
			this.callback = callback;
		}
		else {
			this.extParam = callback;
		}
		
		for (i in param) {
			this.state[i] = param[i];
		}
		 
		if ( this.state.stateName == StateName.Alubms ) {
			this.config.readerAlbums.load(this.state);
			
			var page = (this.state.offset > 0) ? '/page/'+this.state.offset : '';
			
			var url = "/"+this.state.stateName + page;
			document.myState = url;
			WBS.History.add(url);
		}
		else if ( this.state.stateName == StateName.ImageTile ) {
			if ( this.lastStateName == StateName.ImageOne ) {
				var sort = parseInt(Data.data.c.PL_SORT);
				var offset = parseInt(this.state.offset);
				var limit = parseInt(this.state.limit);
				if ( sort < offset || sort >= parseInt(offset) + parseInt(limit) ) {
					this.state.offset = Math.floor( sort/limit ) * limit;
				}
			}
			
			this.config.readerImages.load(this.state);
			
			var page = (this.state.offset > 0) ? '/page/'+this.state.offset : '';
			
			var url = "/"+this.state.stateName+"/"+this.state.albumId + page;
			document.myState = url;
			WBS.History.add(url);
		}
		else if ( this.state.stateName == StateName.ImageDetail ) {
			this.config.readerImages.load(this.state);
			
			var page = (this.state.offset > 0) ? '/page/'+this.state.offset : '';
			var url = "/"+this.state.stateName+"/"+this.state.albumId + page;
			document.myState = url;
			WBS.History.add(url);
		}
		else if ( this.state.stateName == StateName.ImageOne ) {
			this.config.readerImageOne.load(this.state);
			
			var url = "/"+this.state.stateName+"/"+this.state.imageId;
			document.myState = url;
			WBS.History.add(url);
		}
		else if ( this.state.stateName == StateName.Back ) {
			this.updateState();
		}
				
		if ( param.stateName )
			this.lastStateName = param.stateName;
			
		$('img.image-one').imgAreaSelect({ hide: true });
		Semaphore.stopProccess();
	}
});

ImageInfo = {
	_lastImagePos: null,
	lastImageId: function(id) {
		if ( id ) {
			jQuery.cookie('lastImageId', id);
		}
		else {
			var val = jQuery.cookie('lastImageId');
			return (val) ? val : null;
		}
	},
	lastImagePos: function(num) {
		if ( num ) {
			this._lastImagePos = num;
		}
		else {
			return this._lastImagePos;
		}
	}
};

TemplateBroker = newClass(null, {
	constructor: function () {
		this.lastTemplate = null;
		this.lastTemplateScrollH = null;
		this.activeTemplate = null;
		
		this.imageOneTemplate = null;
		
	},
	updateState: function(state, data) {

		switch ( state.stateName ) {
			case StateName.Alubms : {
				
//				alert('1');
				
				var elem = $('<div></div>').get('0');
				this._render(elem, state);
//				alert('2');
				TemplateAlbums.render(elem, data);
//				alert('3');
				var $addPanel = $('<div id="album-add-panel" class="color-top-panel" style="overflow: hidden;"> </div>');
//				var $addPanel = $('<div id="album-add-panel"> </div>');
				if ( Rights.manage_album == 1 ) {
					$addPanel.append('<div class="albom-manage"><a href="#" id="btn-album-arrange">Управление альбомами</a></div>');
				}
				if ( Rights.create_album == 1 ) {
					$addPanel.append('<input value="Новый альбом" id="btn-album-add" type="button" />');
				}
				$addPanel.append("<span id=\"album-arrange-tile\" style=\"display: none;\">Задайте порядок отображения альбомов, перетаскивая их с помощью мышки.</span>");
//				alert('4');
				$('#album-content-body > div').before($addPanel);
//				alert('5');
				
//				$('title').text('album');
//				alert('6');
				break;
			}
			case StateName.ImageTile : {
				var elem = createDiv("");
				TemplateImageTile.render(elem, data);
				this._render(elem, state);
				
//				$('title').text('images');
				break;
			}
			case StateName.ImageDetail : {
				var elem = createDiv("");
				TemplateImageDetail.render(elem, data);
				this._render(elem, state);
				break;
			}
			case StateName.ImageOne : {
				var elem = createDiv("");
				if ( !this.imageOneTemplate ) {
					this.imageOneTemplate = new TemplateImageOn();
					ViewImageOn.template = this.imageOneTemplate;
				}
				this.imageOneTemplate.render(elem, data);
				this._render(elem, state);
								
//				$('title').text('image');
				ImageInfo.lastImageId(data.data.c.PL_ID);
				ImageInfo.lastImagePos(data.data.c.PL_SORT);
				break;
			}
			case StateName.Back :  {
				this._renderBack();
				break;
			}
		}
		$('#album-content-body').scrollTop(0);
		if ( state.stateName == StateName.ImageTile ) {
			$('#album-content-body').scrollTop( this.lastTemplateScrollH );
		}
		$('#album-content-body').css({"background-color":""});
		jQuery('#pending-comment').text(data.pendingComment);
	},	
	_render: function (content, state) {
		this.lastState = state.stateName;
		if (state.stateName == StateName.ImageOne) {
			this.lastTemplateScrollH = $('#album-content-body').get(0).scrollTop;
		}
		
		if ( this.activeTemplate  ) {
			this.lastTemplate = this.activeTemplate;			
		}
		
		if ( this.activeTemplate ) {
			$('#album-content-body').scrollTop(0);
			$('#album-content-body').children().remove();
		}
		$('#album-content-body').append(content);
		
		this.activeTemplate = content;
	},
	_renderAndHash: function (content) {
		if ( this.activeTemplate ) {
			this.lastTemplateScrollH = $('#album-content-body').get(0).scrollTop;
			$('#album-content-body').get(0).removeChild(this.activeTemplate);
			this.lastTemplate = this.activeTemplate;			
		}
		
		$('#album-content-body').get(0).appendChild(content);
		this.activeTemplate = content;
	},
	_renderBack: function () {		
		if (this.lastTemplate) {
			$('#album-content-body').get(0).removeChild(this.activeTemplate);
			$('#album-content-body').get(0).appendChild(this.lastTemplate);			
			this.activeTemplate = this.lastTemplate;
			$('#album-content-body').get(0).scrollTop = this.lastTemplateScrollH;
		}
	}
});

ModuleBroker = newClass(null, {
	constructor: function () {
		this.rightImages = new Array();
		this.footer = new Array();
	},
	updateState: function(state, data) {
		if ( !this.rightImages[state.stateName] ) {
			this.rightImages[state.stateName] = new ModuleRightImages({
				manager: Helper.getManager(),
				elem: $('album-util-panel')
			});
		}
		var elem = this.rightImages[state.stateName].render(state, data);
		
		if ( !this.footer[state.stateName] ) {
			this.footer[state.stateName] = new ModuleFooter({
				manager: Helper.getManager(),
				elem: $('#footer-info-panel').get(0)
			});
		}
		
		var elem = this.footer[state.stateName].render(state, data);
		
		var mip = new ModuleInfoPanel();
		mip.render(state, data);
		

	}	
});

TemplateAlbums = {
	render: function(elem, data) {
		this.contentElem = elem
		this.data =  data.data;
		
		$(this.contentElem).empty();
		var $ulContener = $('<ul class="album-list"> </ul>');
		$(this.contentElem).append($ulContener);
		if ( this.data.length == 0 ) {
			$('<div class="center">В фотогалерее нет ни одного альбома.<br /><br /><em>Создайте новый альбом, чтобы загрузить фотографии.</em> </div>')
				.appendTo($ulContener);
			return;
		}
		
		var listLi = new Array();
		for(var i=0; i<this.data.length; i++) {
			var $li = $('<li > </li>');
			
			$li.attr('uid', this.data[i].PF_ID)
			
			$li.append( this._createAlbumItem(this.data[i]) );
			
			$ulContener.append($li);
			
			listLi.push($li.get(0));
		}
		
		var albumNotThumb = [];
		for (var albumIndex in this.data) {
			var album = this.data[albumIndex];
			if ( (!album.PF_THUMB || album.PF_THUMB == 0 ) && album.PHOTOS_COUNT != 0 )
				albumNotThumb.push(album);
		}
		
		if ( albumNotThumb.length > 0 ) {
			var thumbFunction = function() {
				var album = albumNotThumb.pop();
				$.ajax({
					url: 'backend.php?controller=ajax&action=changeNewThumbAlbum&albumId='+album.PF_ID ,
					success: function(){
						var $elem = $('li[uid='+album.PF_ID+'] img');
						$elem.attr('src', $elem.attr('src')+'&v='+Math.random());
						
						if ( albumNotThumb.length > 0 )
							thumbFunction();
					},
					async: false
				});
				
			}.bind(this);
			
			setTimeout(function(){thumbFunction()}, 1000);
		}
	},
	_createAlbumItem: function(record) {
		Helper.getManager().albumList.push({
			name: record.PF_NAME,
			id: record.PF_ID
		});
		
		var $div = $('<div  class="album-thumb" > </div>');
		var $divimage = $('<div > </div>');
		
		$div.append( $divimage );		
		
		
		if ( record.RIGHT >= 1 ) {
			var title = record.PF_NAME_H;
		}
		else {
			var title = 'У вас нет прав доступа к этому альбому.';
		}
			
		var $img = $('<img style="width: 170px; height: 173px;" title="'+title+'" alt="'+record.PF_NAME_H+'"/>');
		var img_src = 'image.php?filename=';
		img_src += 'dGh1bWIuanBn';
		img_src += '&albumId=';
		img_src += record.PF_ID+'&v='+record.PF_THUMB;
		
		$img.attr("src", img_src);
		
		$divimage.append($img);
		$img.addClass("album");
		
		if ( record.RIGHT >= 1 ) {
			$img.get(0).onclick = function (){
				if( document.arrangeAction ) {
						document.arrangeAction = false;
						return false;
					}
				Helper.getManager().setState({
					stateName: StateName.ImageTile,
					albumId: this.id
				});
			}.bind({
				t: this,
				id: record.PF_ID
			});
			$img.addClass("cursor-point");
			var title = record.PF_NAME_H;
		}
		else {
			$div.css({opacity: 0.5});
		}
		
		
		var $albumName = $('<div class="album-name" title="'+title+'"> </div>');
		var $albumNameFull = $('<div class="album-name-full" style="display: none;" ></div>');
		
		var albumName = record.PF_NAME;
		var albumNameFull = record.PF_NAME;
		if ( record.PF_NAME.length > 20-3 ) {
			albumName = albumName.substr(0, 20-3) + '...';
		}
		
		$albumName.text( albumName );
		
		if ( record.RIGHT >= 1 ) {
			if ( albumName.length > 0 ) {
				$albumName.click(function(){
					if( document.arrangeAction ) {
						document.arrangeAction = false;
						return false;
					}
					Helper.getManager().setState({
							stateName: StateName.ImageTile,
							albumId: this.id
						});
				}.bind({
					t: this,
					id: record.PF_ID
				}));
			}
			$albumName.css({cursor: 'pointer'});
		}
		
		var $albumPhotosCount = $('<div class="album-photos-count" > </div>');
		$albumPhotosCount.text(record.PHOTOS_COUNT );
		$div.append($albumPhotosCount);
		
		var $divpublished = $('<div class="album-published" > </div>');
		$div.append( $divpublished);
		if (record.PF_STATUS == 1) {
			$divpublished.append('<span class="publish_yes" title="Альбом опубликован"> </span>');
		}
		if (record.PF_STATUS == 3) {
			$divpublished.append('<span class="publish_hide" title="Альбом опубликован приватно"> </span>');
		}
		
		$div.append($albumName);
		$div.append($albumNameFull);
		
		
		
		if ( record.RIGHT > 1 ) {
			$div.append( $('<a href="#" class="delete-album" style="display: none;">Удалить</a>').click(function(res){
				if ( confirm( 'Все фотографии в альбоме будут удалены, восстановить их будет нельзя. Удалить?' ) ) {
					$.post('backend.php?controller=ajax&action=deleteAlbum', {albumId: record.PF_ID}, function(res){
						if ( res.status == 'OK' )
							$("li[uid="+record.PF_ID+"]").remove();
					}, 'json');
				}
				return false;
			}) );
			$('.delete-album').css({
				position : 'relative',
				'z-index' : 2
			});
		}
		
		
		var $albumDate = $('<div class="album-date" > </div>');
		var date = record.PF_DATESTR || '';
		$albumDate.attr('title', date);
		if ( date.length > 50 ) {
			date = date.substr(0, 50-3) + '...';
		}			
		$albumDate.text( date );		
		$div.append($albumDate);
		
		return $div.get(0);
	}
};

ViewImageOn = {
	size: 970,
	template: {}
};

ModuleRightImages = newClass(null, {
	constructor: function(config) {
		this.config = config;
		this.contentElem = config.elem;
		this.manager = config.manger;
		var manager = config.manger;
		
		if ( Helper.getManager().getState().stateName == StateName.Alubms )
		{
			

		}
		else if (  Helper.getManager().getState().stateName == StateName.ImageTile ) {

		}
	},
	render: function(state, data) {
		jQuery('#album-util-panel > div').css("display", "none");
		
		var album = data.album;
		this.album = album;
		var record = data.data['c'];
		this.record = record;
		
		var exif = data.exif;
		
		var $panel = $('#rigth-info-panel');
		
		switch ( state.stateName ) {
//------------------------			
			case StateName.Alubms : {
				$panel.hide();
				$('#main-content').css('margin-right', 0);

				$('#util-album').css("display", "");
				break;
			}
//------------------------			
			case StateName.ImageTile : {
				$panel.show();
				$('#main-content').css('margin-right', 200);
				$('#util-image-tile').css("display", "");
				$panel.empty();

				var $block1 = $('<div class="rigth-panel-block album_settings"> </div>');
				$panel.append($block1);

				$('<a href="#" class="iconlink" id="slideshow-link"><span class="icon"></span><b>Слайдшоу</b></a>').click(function(){
					window.open( document.hostUrl+'PD/backend.php?controller=album&action=slideShow&albumId='+album.PF_ID );
					return false;
				}).appendTo($block1);

				$block1.append('<p id="rigth-album-datastr">'+album.PF_DATESTR+'</p>');

	            var $desc = $('<p id="rigth-album-desc" class="rigth-album-desc"></p>').appendTo($block1);
	            $desc.append(album.PF_DESC+'<br>');
	            
	            if (  Rights.isWrite() ) {
	            	if (album.PF_DESC.length > 0)
						$desc.append('<a id="album-desc-edit" href="#"><span></span>редактировать</a>')
					else
						$desc.append('<a id="album-desc-edit" href="#"><span></span>Редактировать описание альбома</a>')
	            }
	             
	            if (  Rights.isWrite() ) {
					var $pl1 = $('<p class="panel-link" > </p>');
					var $pl2 = $('<p class="panel-link" > </p>');
					var $view = $('<a id="view-link" class="opened" href="#"><span></span><span class="dott-border">Просмотр</span></a>');
					var $arrange = $('<a id="arrange-link" class="closed" href="#"><span></span><span class="dott-border">Управление и ссылки</span></a>');
					 					
					document.arrangeOn = function(){
						document.arrange = new WaArrange({
							listElements: jQuery('.album-list li').get(),
							albumId: this.album.PF_ID,
							updateUrl: "backend.php?controller=ajax&action=sortImage",
							cssClass: 'image-src'
						});
						$('#album-content-body').css({"background-color":"#5e5e5e"});
						
						$('.image-contener').append('<div class="image-action"><input type="checkbox"/></div>')
							.css({'background-color': '#7c7c7c'});
							
						$('.image-contener').find('input').click(function(){
							var $img = $(this).parent().parent().find('img');
							if ( $(this).attr('checked')) {
								$(this).parent().parent().css({'background-color': '#d3a300'});
							}
							else {
								
								$(this).parent().parent().css({'background-color': '#7C7C7C'});
							}
						});
						$('.image-src').removeClass('static').addClass('move');
						
						$('.arrange-utils').show();
						$('.arrange-close').show();
						
						$('#arrange-link').removeClass('closed').addClass('opened');
						$('#view-link').removeClass('opened').addClass('closed');
						return false;
					};
					document.arrangeOff = function(){
						Helper.getManager().setStateParam({arrange: false});
						Helper.getManager().setState({});
						$('#album-content-body').css({"background-color":"#fff"});					
						$('.arrange-utils').hide();
						
						$('#arrange-link').removeClass('opened').addClass('closed');
						$('#view-link').removeClass('closed').addClass('opened');
						return false;						
					};
					$arrange.clickw(function(e){
						document.arrangeOn.apply(this, [e]);
						return false;
					}.bind(this));
					$view.clickw(function(){
						document.arrangeOff.apply(this);
						return false;
					});
					
					$pl1.append($view);
					$pl2.append($arrange);
					$block1.append($pl1);
					$block1.append($pl2);
					
					if (Helper.getManager().state['arrange']) {
						
						document.arrangeOn.apply(this);
					}
	            }
	            var style = '';
	            if (!Helper.getManager().state['arrange']) { 
					style = 'style="display: none;"';
	            }
	            Helper.getManager().setStateParam({arrange: false});
				var $arrangeUtils = $('<div class="arrange-utils" '+style+' />');
				$arrangeUtils.append("<p>Задайте порядок сортировки перетаскиванием фото.</p><br>");
				$arrangeUtils.append('<p>С выбранными фото (<span class="selectCount">0</span>):</p>');
				
				$('<p class="selectall"><a href="#">выбрать все</a><p>').appendTo($arrangeUtils).click(function(){
					
					if ( $('.album-list input[type=checkbox]:checked').size() < $('.album-list input[type=checkbox]').size()) { 
						$('.album-list input[type=checkbox]').attr('checked', 'checked');
						$('.album-list .image-contener').css({'background-color': '#d3a300'});
						$('.selectCount').text( $('.album-list input[type=checkbox]').size() );
					}
					else {
						$('.album-list input[type=checkbox]').attr('checked', '');
						$('.album-list .image-contener').css({'background-color': '#7C7C7C'});
						$('.selectCount').text( 0 );
					}
					
					return false;
				});
				
				$('<a class="albumlinks" href="#"><span></span><b>Ссылки на фотографии</b></a>').appendTo($arrangeUtils).click(function(){
					var images = [];
					$('ul.album-list li').filter(function(){
						if ( $('input[@type$="checkbox"]:checked', this).size() > 0 )
							return 1
						else
							return 0;
					}).each(function(){
						images.push( $(this).attr('uid') );
					});

					$('#swfupload').wbsPopup({
						url: "backend.php?controller=album&action=linkToImage&images="+images.join(','),
						width: 760,
						height: 440,
						iframe: true,
						hidePopup: function(){
						},
						callback: {
						}
					});
					return false;
				});
				
				if ( Rights.manage_collections ) {
					$arrangeUtils.append('<a class="createlink" href="#"><span></span><b>Создать коллекцию из выбранных фото</b></a>');
				}
				
				$('<a class="transfer" href="#"><span></span><b>Переместить в другой альбом</b></a>').appendTo($arrangeUtils).click(function(){					
					var $list = $('ul.album-list li').filter(function(){
						if ( $('input[@type$="checkbox"]:checked', this).size() > 0 )
							return 1
						else
							return 0;
					});
					if ($list.size() == 0) {
						alert('Не выбрано ни одной фотографии.');
						return false;
					}
					
					var albumList = null;					
					$.ajax({						
						url: "backend.php?controller=ajax&action=albumListCompact",
						async: false,
						dataType: "json",
						success: function(obj){
							albumList = obj.data;
						}
					});
					
					
					$('#photo-move select').empty();
					for ( index in albumList ) {
						$('#photo-move select').append('<option value="'+albumList[index].PF_ID+'" title="'+albumList[index].PF_NAME_FULL+'">'+albumList[index].PF_NAME+'</option>');
					}
					
					$('#photo-move').wbsPopup({						
						width: 400,
						height: 300,
						iframe: false,
						hidePopup: function(){
						}
					});
					
					$('#photo-move #btn_move').unbind().click(function(){
						
						var $self = $(this);
						
						$self.attr('disabled', 'disabled');
						$self.after('<img class="progress" src="img/ajload.gif" />');
						
						var imgList = [];
						
						var $list = $('ul.album-list li').filter(function(){
							if ( $('input[@type$="checkbox"]:checked', this).size() > 0 )
								return 1
							else
								return 0;
						});
						$list.each(function(){
							imgList.push( $(this).attr('uid') );
						});						
						$.post("backend.php?controller=ajax&action=photomove",
							{
								albumId: $('#photo-move option:selected').val(),
								'photolist[]': imgList
							},
							function(){
								Helper.getManager().setState({
									viewName: StateName.ImageTile									
								});
								$self.attr('disabled', '');
								$('.progress').remove();

								$('#photo-move').wbsPopupClose();
							}
						);
						return false;
					});
					
					return false;
				});
				var $deleteSelect = $('<a class="albumdelete" href="#"><span></span><b>Удалить фотографии</b></a>');

				$deleteSelect.clickw(function(){
					
					
					var $list = $('ul.album-list li').filter(function(){
						if ( $('input[@type$="checkbox"]:checked', this).size() > 0 )
							return 1
						else
							return 0;
					});
					if ($list.size() == 0) {
						alert('Не выбрано ни одной фотографии.');
						return false;
					}
					if ( !confirm('Удалить выбранные фотографии?') )
						return false;
					$list.each(function(){
						var imageId = $(this).attr('uid');
						
						$.ajax({
							type: "POST",
							url: "backend.php?controller=ajax&action=deleteImage",
							data: {imageId: imageId},
							async: false,
							dataType: "json",
							success: function(){
								$('.selectCount').text( $('input[@type$="checkbox"]:checked', this).size() );
							}
						});
						
						$(this).remove();
						
					});
					
					return false;
				});
				$arrangeUtils.append($deleteSelect);
				$block1.append($arrangeUtils);
				
				$('.createlink').clickw(function(){
					
					var list = $('ul.album-list li').filter(function(){
						if ( $('input[@type$="checkbox"]:checked', this).size() > 0 )
							return 1
						else
							return 0;
					}).get();
					
					var listId = '';
					
					for ( field in list ) {
						 listId += $(list[field]).attr('uid') + ',' ;
					}
					
					$('#pp').wbsPopup({
						url: 'backend.php?controller=album&action=createCollection&albumId='+album.PF_ID+'&imageList='+listId,
						width: 760,
						height: 440,
						iframe: true,
						hidePopup: function(id){
							
							document.notSend = true;
						},
						loadComplite: function(id){
							
							if ( document.notSend ) {document.notSend = false; return;}
							window.location.href = document.hostUrl+'PD/backend.php?controller=album&action=collection&id='+id;
						}
					});						
					return false;
				});
				
				if ( Rights.isWrite() ) {
					var $block3 = $('<div class="rigth-panel-block"> </div>');
					$panel.append($block3);
				
					$('#rigth-album-desc').click(function(){
						$('#pp').wbsPopup({
							url: 'backend.php?controller=album&action=changeDescElement&type=album&albumId='+this.album.PF_ID,
							width: 500,
							height: 440,
							iframe: true,
							hidePopup: function(){
								Helper.getManager().setState();
							}
						});
						return false;
					}.bind(this));
					
					$('<a href="#" id="descr-edit" class="iconlink"><span class="icon"></span><b>Редактировать описания фото</b></a>').click(function(){
						$('#pp').wbsPopup({
							url: 'backend.php?controller=album&action=changePhotoDesc&albumId='+this.album.PF_ID,
							width: 760,
							height: 440,
							iframe: true,
							hidePopup: function(){
							}
						});	
						return false;
					}.bind(this)).appendTo($block3);
					
					if ( Rights.manage_collections ) {
	
						var $links = $('<a id="creat-collection" class="iconlink" href="#"><span class="icon"> </span><b>Создать коллекцию</b></a>');				
						$block3.append($links);
						
						$links.clickw(function(){
							var list = $('ul.album-list li').filter(function(){
								if ( $('input[@type$="checkbox"]:checked', this).size() > 0 )
									return 1
								else
									return 0;
							}).get();
							
							var listId = '';
							
							for ( field in list ) {
								 listId += $(list[field]).attr('uid') + ',' ;
							}
							
							$('#pp').wbsPopup({
								url: 'backend.php?controller=album&action=createCollection&albumId='+album.PF_ID+'&imageList='+listId,
								width: 760,
								height: 440,
								iframe: true,
								hidePopup: function(id){
									
									document.notSend = true;
								},
								loadComplite: function(id){
									
									if ( document.notSend ) {document.notSend = false; return;}
									window.location.href = document.hostUrl+'PD/backend.php?controller=album&action=collection&id='+id;
								}
							});						
							return false;				
						});
					}
						
					var $flickr = $('<a id="flickr-import" href="#" class="iconlink"><span class="icon"></span><b>Импорт из Flickr<img src="img/new_window_icon.gif" width="13" height="11" alt="" /></b></a>');
					$flickr.clickw(function(){
						window.open( document.hostUrl+'/PD/backend.php?controller=flickr&action=auth&album='+album.PF_ID);
//						$('#pp').wbsPopup({
//							url: 'backend.php?controller=flickr&action=auth&album='+album.PF_ID,
//							iframe: true,
//							hidePopup: function(){
//								Helper.getManager().setState({});
//							}
//						});
						return false;
					});
					$block3.append($flickr);
					
					if ( Rights.isFull() )
					$('<a id="delete-album" href="#" class="iconlink"><span class="icon"></span><b>Удалить альбом</b></a>')
						.click(function(){
	
							if ( confirm( 'Все фотографии в альбоме будут удалены, восстановить их будет нельзя. Удалить?' ) ) {
								$.post('backend.php?controller=ajax&action=deleteAlbum', {albumId: album.PF_ID}, function(){
									Helper.getManager().setState({
										stateName: StateName.Alubms
									});
								});
							}
							return false;
						}).appendTo($block3);
				}
				break;
			}
//------------------------			
			case StateName.ImageOne : {
				$('#rigth-info-panel').show();
				$('#main-content').css('margin-right', 200);

				$('#util-image-tile').css("display", "");
				
				$panel.empty();
				$block1 = $('<div class="rigth-panel-block preview-info"> </div>');
				$panel.append($block1);
				
				var $desc = $('<p id="rigth-album-desc" class="rigth-album-desc"></p>').
					appendTo($block1);
				$desc.append(record.PL_DESC+'<br/>');
					
				if ( Rights.isWrite() ) {
					if ( record.PL_DESC.length > 0 )
						$desc.append('<a href="#">редактировать</a>');
					else 
						$desc.append('<a href="#">Редактировать описание фото</a>');
						
					$('#rigth-album-desc a').click(function(){
						$('#pp').wbsPopup({
							url: 'backend.php?controller=album&action=changeDescElement&type=image&imageId='+this.record.PL_ID,
							width: 500,
							height: 440,
							iframe: true,
							hidePopup: function(){
								Helper.getManager().setState();
							}
						});
						return false;
					}.bind(this));				
				}				
				
				
				var $ul = $('<ul class="size_list"></ul>');
				
				var w = parseInt(ViewImageOn.originalW); 
				var h = parseInt(ViewImageOn.originalH);
				var k = w/h;
				
				if (w > h) {
					$ul.append('<li id="size_96" s="96"><a href="#">96x96</a></li>');
					$ul.append('<li id="size_144" s="144"><a href="#">144x144</a></li>');
					
					if ( w > 256 )
					$ul.append('<li id="size_256" s="256"><a href="#">256x'+Math.floor(256/k)+'</a></li>');
					if ( w > 512 )
					$ul.append('<li id="size_512" s="512"><a href="#">512x'+Math.floor(512/k)+'</a></li>');
					if ( w > 750 )
					$ul.append('<li id="size_750" s="750"><a href="#">750x'+Math.floor(750/k)+'</a></li>');
					if ( w > 970 )
					$ul.append('<li id="size_970" s="970"><a href="#">970x'+Math.floor(970/k)+'</a></li>');
				}
				else {
					$ul.append('<li id="size_96" s="96"><a href="#">96x96</a></li>');
					$ul.append('<li id="size_144" s="144"><a href="#">144x144</a></li>');

					if ( h > 256 )
					$ul.append('<li id="size_256" s="256"><a href="#">'+Math.floor(256*k)+'x256</a></li>');
					if ( h > 512 )
					$ul.append('<li id="size_512" s="512"><a href="#">'+Math.floor(521*k)+'x512</a></li>');
					if ( h > 750 )
					$ul.append('<li id="size_750" s="750"><a href="#">'+Math.floor(750*k)+'x750</a></li>');
					if ( h > 970 )
					$ul.append('<li id="size_970" s="970"><a href="#">'+Math.floor(970*k)+'x970</a></li>');
				}
				//$ul.append('<li id="size_orig" s="'+record.PL_WIDTH+'"><a href="#">'+record.PL_WIDTH+'x'+record.PL_HEIGHT+'</a> (оригинал)</li>');
				
				$block1.append($ul);
				$('li', $ul).removeClass();
				$('#size_'+ $.cookie('sizeOneView') , $ul).addClass('size_active');
				if ( $('#size_'+ $.cookie('sizeOneView')).size() == 0 ) {
					$('.size_list li:last').addClass('size_active');
				}
				
				var changeLink = function(size, record, data) {
					if ( !size ) return false;
					
					var main = String(record.IMG_URL + '/' + size).split('/');
					var main_size = main.pop();
					var main_url = main.pop();
					
					$('#link_main').val(data.urlToView + main_url + '/' + main_size);
					$('#link_main_a').attr('href', data.urlToView + main_url + '/' + main_size);
					var alt = '';
					if ( record.PL_DESC_H.length > 0 ) {
						alt = 'alt=" '+record.PL_DESC_H+' "';
					}					
					
					var url = String(record.IMG_URL_R).replace(/\.[0-9]+\.jpg/i, '.'+size+'.jpg');
					$('#link_url').val(url);
					$('#link_html').val('<img src="'+url+'" '+alt+'/>');
					$('#link_bb').val('[IMG]'+url+'[/IMG]');					
				};
				
				var size = $('.size_active').attr('s');
				
				$('li a', $ul).click(function(){
					if ( $('.imgareaselect-border2:visible').size() > 0 ) {
						$('img.image-one').imgAreaSelect({ 
							hide: true						
						});
					}
					$('.crop-span').hide();
					Semaphore.stopProccess();
					
					var size = $(this).parent().attr('s');
					if (size <= 144) 
						$('.crop').hide();
					else
						$('.crop').show();
					
					jQuery.cookie('sizeOneView', size);
					
					var $img = $('.image-one');
					
					//document.isProcessSleep = true;
					Helper.getManager().startProccess();
					$img.load(function(){						
						Helper.getManager().stopProccess();
					});
					$img.attr('src',  $img.attr('src').replace(/size=([0-9])+/, 'size='+size)  );

					var w = ViewImageOn.originalW; 
					var h = ViewImageOn.originalH;	
					var k = w/h;
					
					if ((size in {96:'', 144:''})) {
						ViewImageOn.template.data.PL_WIDTH = size; 
						ViewImageOn.template.data.PL_HEIGHT = size;
					}
					else  {
						if ( Data.data.c && Data.data.c.sizes && Data.data.c.sizes[ size ] ) {
							ViewImageOn.template.data.PL_WIDTH = Data.data.c.sizes[ size ].w;
							ViewImageOn.template.data.PL_HEIGHT = Data.data.c.sizes[ size ].h;
						}
						else
						if ( w > h ) {
							ViewImageOn.template.data.PL_WIDTH = size; 
							ViewImageOn.template.data.PL_HEIGHT = size/k;
						}
						else {
							ViewImageOn.template.data.PL_WIDTH = size * k; 
							ViewImageOn.template.data.PL_HEIGHT = size;
						}
					}
					changeLink(size, record, data);
					//ViewImageOn.template.resize();
					
					$('.size_list li').removeClass();
					$('#size_'+size).addClass('size_active');
					
					$('#link_main, #link_url, #link_html, #link_bb').css('font-weight', 'bold');
					setTimeout(function(){
						$('#link_main, #link_url, #link_html, #link_bb').css('font-weight', 'normal');
					}, 700);
					
					return false;
				});
				
				//var $block4 = $('<div class="rigth-panel-block photourls"> </div>');
				
				var $photourls = $('<div class="photourls"></div>');
				$block1.append($photourls);
				
				$photourls.append('<h4 class="photourls-h"> </h4>');
				
				$photourls.append('<a id="link_main_a" href="" target="_blank"><img src="img/new_window_icon.gif" /></a><label for="link_main"><b>Ссылка (для ICQ, email):</b></label>');
				$photourls.append('<p><input id="link_main" type="text" value="" readonly="true" /> </p>');
				
				$photourls.append('<label for="link_html">HTML (для сайтов и блогов):</label>');
				$photourls.append('<p><input id="link_html" type="text" value="" readonly="true" /> </p>');
				
				$photourls.append('<label for="link_bb">BBcode (для форума):</label>');
				$photourls.append('<p><input id="link_bb" type="text" value="" readonly="true" /> </p>');
				
				$photourls.append('<label for="link_url">URL:</label>');
				$photourls.append('<p><input id="link_url" type="text" value="" readonly="true"/> </p>');
				
				$('.rigth-panel-block input').clickToSelect();
				var size = ($.cookie('sizeOneView')) ? $.cookie('sizeOneView') : 970;
				changeLink( size , record, data);
				
				var $block1_5 = $('<div class="rigth-panel-block original"> </div>');			
				$panel.append($block1_5 );
				
				$block1_5.append('<h4>Оригинал</h4>');
				$block1_5.append('<p class="photo-size">'+parseInt(record.PL_W)+'x'+parseInt(record.PL_H)+', '+$.filesizeformat(record.PL_FILESIZE)+'</p>');
				var link = record.HASH;
				$block1_5.append('<p class="org-link"><a href="'+link+'" target="_blank"><img src="img/new_window_icon.gif" /></a><input id="oreginal-link" type="text" value="'+link+'"/></p>');
				$('#oreginal-link').clickToSelect();
					
				$('#link_main, #link_url, #link_html, #link_bb, #oreginal-link')
					.focus(function(){
						WbsData.set({'isKeyEnable': false});
					})
					.blur(function(){
						WbsData.set({'isKeyEnable': true});
					});
				
				var $block2 = $('<div class="rigth-panel-block actions"> </div>');			
				$panel.append($block2);
				
				if ( Rights.isWrite() ) {
					
						Semaphore.init();				
						
						var $leftRotate = $('<a href="#" class="sidebar-btn rleft"><span class="active" alt="Поворот влево" title="Поворот влево" >&nbsp;</span></a>');
						this.$leftRotate = $leftRotate;
						Semaphore.addButton($leftRotate);
						$leftRotate.clickw(function(){							
							if ( !Semaphore.startProccess($(this)) ) return false;
							startProgress();
							$.post('backend.php?controller=ajax&action=rotateImage', 
								{
									imageId: this.record.PL_ID,
									imageFile: this.record.PL_DISKFILENAME,
									albumId: this.record.PF_ID,
									width: this.record.PL_WIDTH,
									height: this.record.PL_HEIGHT,
									rotate: -90
								}, 
							function () {						
								Helper.getManager().setState({stateName: StateName.ImageOne});
								Semaphore.stopProccess();
								stopProgress();
							}.bind(this));
							
							return false;
						}.bind(this));
						
						$block2.append($leftRotate);
						$('.rleft span').hover(function(){
							$(this).removeClass('active').addClass('hovered');
						}, function(){
							$(this).removeClass('hovered').addClass('active');
						});
						
						var $rigthRotate = $('<a href="#" class="sidebar-btn rright"><span class="active" alt="Поворот вправо" title="Поворот вправо">&nbsp;</span></a>');
						this.$rigthRotate = $rigthRotate;
						Semaphore.addButton($rigthRotate);
						$rigthRotate.clickw(function(){							
							if ( !Semaphore.startProccess($(this)) ) return false;
							startProgress();
							$.post('backend.php?controller=ajax&action=rotateImage', 
								{
									imageId: this.record.PL_ID,
									imageFile: this.record.PL_DISKFILENAME,
									albumId: this.record.PF_ID,
									width: this.record.PL_WIDTH,
									height: this.record.PL_HEIGHT,
									rotate: 90
								}, 
							function () {						
								Helper.getManager().setState({stateName: StateName.ImageOne});
								Semaphore.stopProccess();
								stopProgress();
							}.bind(this));
							
							return false;
						}.bind(this));
						$block2.append($rigthRotate);
						$('.rright span').hover(function(){
							$(this).removeClass('active').addClass('hovered');
						}, function(){
							$(this).removeClass('hovered').addClass('active');
						});
						
		//				$cropdiv = $('<div class="rigth-panel-crop" />');
						if ( $.cookie('sizeOneView') <= 144 )
							var hide = 'style="display: none"';
						else 
							var hide = '';
						var $cropbtn = $('<a href="#" class="sidebar-btn crop" '+hide+'><span class="active" alt="Вырезать" title="Вырезать">&nbsp;</span></a>');
						this.$cropbtn = $cropbtn;
						Semaphore.addButton($cropbtn);
						$cropbtn.clickw(function(){
							if ( !Semaphore.startProccess($(this)) ) return false;
							
							this.imageAreaSelect = {
								imageId: this.record.PL_ID,
								imageFile: this.record.PL_DISKFILENAME,
								albumId: this.record.PF_ID,
								x: $('img.image-one').width()*0.1,
								y: $('img.image-one').height()*0.1,
								width: $('img.image-one').width() - 2*$('img.image-one').width()*0.1,
								height: $('img.image-one').height() - 2*$('img.image-one').height()*0.1
							};
							
							$('img.image-one').imgAreaSelect({
								isStopNewSelect: true,
								handles: true, 
								x1: $('img.image-one').width()*0.1,
								y1: $('img.image-one').height()*0.1,
								x2: $('img.image-one').width() - $('img.image-one').width()*0.1,
								y2: $('img.image-one').height() - $('img.image-one').height()*0.1,								
								minWidth: 2,
								minHeight: 2,
								keys: false,
								onSelectChange: function(o, param){
									this.imageAreaSelect = {
										imageId: this.record.PL_ID,
										imageFile: this.record.PL_DISKFILENAME,
										albumId: this.record.PF_ID,
										x: param.x1,
										y: param.y1,
										width: param.width,
										height: param.height
									};
								}.bind(this)
							});
							
							$(window).resize(function(){
								$('img.image-one').imgAreaSelect({ hide: true });
								Semaphore.stopProccess();
								$('.crop-span').hide();
							});
							
							$('.crop-span').show();
							return false;
						}.bind(this));		
						
						WBS.History.on('change', function(){
							$('img.image-one').imgAreaSelect({ hide: true });
							Semaphore.stopProccess();
						});
						
						$block2.append($cropbtn);
						
						var size_ = $('.size_active').attr('s');
						if (size_ <= 144)  {
							$('.crop').hide();
						}
						else
							$('.crop').show();

						
						$('.crop span').hover(function(){
							$(this).removeClass('active').addClass('hovered');
						}, function(){
							$(this).removeClass('hovered').addClass('active');
						});
						
						var $cropSpan = $('<span class="crop-span" style="display: none"/>');
						
						var $cropDoneBtn = $('<input class="sidebar-btn crop-done" value="Вырезать" type="button"/>');
						this.$cropDoneBtn = $cropDoneBtn;
						
						var crop_done = function(){
							if ( $('.imgareaselect-border2:visible').size() > 0 ) {
							
								startProgress();
								
								var k = this.record.PL_W / $('.image-one').width();
								this.imageAreaSelect.x *= k;
								this.imageAreaSelect.y *= k;
								this.imageAreaSelect.width *= k;
								this.imageAreaSelect.height *= k;
								
								$.post('backend.php?controller=ajax&action=cropImage', this.imageAreaSelect, function () {
									$('.image-one').imgAreaSelect({ hide: true });
									Helper.getManager().setState({stateName: StateName.ImageOne}, function(){
										var size = $('.size_active').attr('s');
										if (size <= 144) 
											$('.crop').hide();
										else
											$('.crop').show();
									});
									Semaphore.stopProccess();
									stopProgress();
								}.bind(this));
								$('.crop-span').hide();
								
								//reset crop
								$('img.image-one').imgAreaSelect({ hide: true });
								Semaphore.stopProccess();

							}
							return false;
						}.bind(this);
						
						$cropDoneBtn.clickw(crop_done);
						$(document).unbind('keydown', 'return' , crop_done)
							.bind('keydown', 'return' , crop_done);
							
//						var self = this;
//						$(document).keydown(function(e){
//							alert(e.keyCode);
//							if ( e.keyCode == '13' )
//								crop_done.apply(self);
//						});
							
						
						$cropSpan.append($cropDoneBtn);
						//$cropSpan.append('<span class="splitter">|</span>');
						
						var $cropCancelBtn = $('<a href="#" class="sidebar-btn crop-cancel" >Отменить</a>');
						this.$cropCancelBtn = $cropCancelBtn;
						$cropCancelBtn.clickw(function(){
							//if(this.$cropCancelBtn.hasClass('sidebar-btn-disable') )return false;
							$('.image-one').imgAreaSelect({ hide: true });
							$('.crop-span').hide();
							Semaphore.stopProccess();
							return false;
						});
						$cropSpan.append($cropCancelBtn);
						
						$block2.append($cropSpan );
						
		//				var $block5 = $('<div class="rigth-panel-block albumface"> </div>');
						
						if (this.album.PF_THUMB != this.record.PL_ID) {
							var st = '<b>Поставить на обложку альбома</b>';
							var class_ = 'thumb_btn';
						}
						else {
							var name = '';
							if(	Data.album.PF_NAME.length > 30) 
								name = Data.album.PF_NAME.substr(0, 30) + '...';
							else
								name = Data.album.PF_NAME;
								
							var st = $.sprintf('Фото используется как обложка альбома "%s"', name);
							var class_ = 'thumb_btn_des';
						}
						
						$block2.append('<div class="btn_thumb_album"></div>');
						$('<a href="#" class="'+class_+'"><span> </span>'+st+'</a>').click(function(){					
							$.post('backend.php?controller=ajax&action=changeThumbAlbum', {
								'albumId': this.album.PF_ID,
								'thumbId': this.record.PL_ID,
								'thumbPath': this.record.PL_DISKFILENAME
							}, function(data, textStatus){
								if ( data.status == "OK" ) {									
									var name = '';
									if(	Data.album.PF_NAME.length > 30) 
										name = Data.album.PF_NAME.substr(0, 30) + '...';
									else
										name = Data.album.PF_NAME;
									
									$('.thumb_btn').html($.sprintf('<span> </span><b class="lightb">Фото используется как обложка альбома "%s".</b>', name)).removeClass().addClass('thumb_btn_des');
								}							}, "json");				
							return false;
						}.bind(this)).appendTo($block2);
						$panel.append($block2 );
					
				}
				
				if ( Rights.isWrite() ) {		
					$($block2).append('<br class="clearleft" />');
					$('<a id="delete-image" href="#" class="iconlink"><span class="icon"></span><b>Удалить фотографию</b></a>')
						.click(function(){
	
							if ( confirm( 'Фотография будет удалена без возможности восстановления. Удалить?' ) ) {
								$.post('backend.php?controller=ajax&action=deleteImage', {imageId: this.record.PL_ID}, function(){
									Helper.getManager().setState({
										stateName: StateName.ImageTile
									});
								});
							}
							return false;
						}.bind(this)).appendTo($block2);
				}
				

				$block3 = $('<div class="rigth-panel-block details"> </div>');
				$panel.append($block3 );
				
				$block3.append('<p class="exif-info"><b>Подробности (EXIF)</b><p>');
	
				if ( exif.PE_DATETIME && exif.PE_DATETIME.length > 0 ) {
					$block3.append('<p>Снято: <span>'+exif.PE_DATETIME+'</span></p>');
				}
				if ( this.record.PL_UPLOADDATETIME && this.record.PL_UPLOADDATETIME.length > 0 ) {
					$block3.append('<p>Загружено: <span>'+this.record.PL_UPLOADDATETIME+'</span></p>');
				}

				if ( this.record.PL_W || exif.PL_H ) {
					$block3.append('<p>Размеры: <span>'+parseInt(this.record.PL_W)+'x'+parseInt(this.record.PL_H)+'</span></p>');
				}
				
				if ( exif.PE_MODEL && exif.PE_MODEL.length > 0 ) {
					$block3.append('<p>Фотоаппарат: <span>'+exif.PE_MODEL+'</span></p>');
				}
				if ( exif.PE_EXPOSURETIME && exif.PE_EXPOSURETIME.length > 0 ) {
					var expo = ( /1\//.test(exif.PE_EXPOSURETIME) ) ? exif.PE_EXPOSURETIME : exif.PE_EXPOSURETIME + ' с' ;
					$block3.append('<p>Выдержка: <span>'+expo+'</span></p>');
				}
				if ( exif.PE_FNUMBER && exif.PE_FNUMBER.length > 0 ) {
					$block3.append('<p>Диафрагма: <span>'+exif.PE_FNUMBER+'</span></p>');
				}
				if ( exif.PE_FOCALLENGTH && exif.PE_FOCALLENGTH.length > 0 ) {
					$block3.append('<p>Фок. расстояние: <span>'+exif.PE_FOCALLENGTH+'</span></p>');
				}
				if ( exif.PE_ISOSPEEDRATINGS && exif.PE_ISOSPEEDRATINGS.length > 0 ) {
					$block3.append('<p>ISO: <span>'+exif.PE_ISOSPEEDRATINGS+'</span></p>');
				}
				
				$('p span', $block3).each(function(){
					if ($(this).text() == '' || $(this).text() == 'undefined' )
						$(this).parent().remove();
				});
				break;
			}
		}
		
	},
	
	_addItem: function(item) {
		addClass(item, "album-util-panel-item");
		this.contentElem.appendChild(div);
	}
});
ModuleRightImages['resetProccess'] = function() {
		$('img.image-one').imgAreaSelect({ 
			hide: true						
		});
		$('.crop-span').hide();
		Semaphore.stopProccess();
	};


var startProgress = function(){
	var $image = $('.image-one');
	var $bg = $('<div id="bg"> </div>').css({
		position: 'absolute',
		top: $image.offset().top,
		left: $image.offset().left,
		width: $image.width(),
		height: $image.height(),
		'background-color': '#FFF',
		'z-index': 600,
		opacity: 0.5
	});
	
	var $bganimate = $('<div id="bganimate"> <img src="img/proccess.gif" /></div>').css({
		top: $image.offset().top + $image.height() / 2 - 50 / 2,
		left: $image.offset().left + $image.width() / 2 - 50 / 2,
		width: 41,
		height: 41,
		display: 'block',
		position: 'absolute',
		'z-index': 610,
		'background-color': '#000',
		'padding-left': 9,
		'padding-top': 9
	});
	
	$bg.appendTo('body');
	$bganimate.appendTo('body');
};

var stopProgress = function(){
	$('#bg').remove();
	$('#bganimate').remove();
};


ModuleInfoPanel = newClass(null, {
	render: function(state, data) {
		
		switch ( state.stateName ) {
			case StateName.Alubms : {
				Helper.getManager().resizer.setHResize1();
				jQuery("#hor-info-panel").css("display", "none");
				Helper.getManager().resizer.resize();
				
				jQuery('#btn-album-add').clickw(function(){
					$(this).attr('disabled', 'disabled');
					$(this).after('<img src="img/ajload.gif" />');
					jQuery.post('backend.php?controller=ajax&action=createAlbum',function(){
						Helper.getManager().setState({}, function(){
							
							$('.album-list .album-name:first').editable("backend.php?controller=ajax&action=cangeAlbumName", { 
						        name : 'albumName',  
								submitdata: { albumId: $('.album-list li:first').attr('uid') },
								tooltip   : albumNameFull,
								style  : "inherit",
								onblur: 'ignore',
								cancel: ' ',
								width: '',
								height: '',
								cssclass: 'inline-select',
								submit: 'Сохранить',
								postSubmitText: '',
								fieldCount: 20,
								callback: function(){
									$('.album-list .album-name:first').unbind().click(function(){
										Helper.getManager().setState({
											stateName: StateName.ImageTile,
											albumId: $(this).parent().parent().attr('uid')
										});
									});
								}
							}).click();
							
							$('.album-list .album-thumb:first').find('.album-photos-count').hide();
						});
					});
				});
				
				document.iSbtnAlbumAdd = true;
				
				
				jQuery('#btn-album-arrange').toggle(
					function(){
						$('.album-name a').click();
						document.arrange = new WaArrange({
							listElements: jQuery('.album-list li').get(),
							updateUrl: "backend.php?controller=ajax&action=sortAlbum",
							cssClass: 'album'								
						});
						jQuery('#btn-album-arrange').text("Вернуться к просмотру альбомов ");
						jQuery('#album-content-body').css({"background-color":"#D8E0E5"});
						$('.delete-album').show();
						
						$('.album-thumb').addClass('album-edit');
						
						$('.album-list img.album').css({cursor: 'move'});
						$('.album-date').hide();
						
						$('#btn-album-add').hide();
						$('#album-arrange-tile').show();
					},
					function(){
						jQuery('#btn-album-arrange').text("Управление альбомами");
						document.arrange = null;
						Helper.getManager().setState({});
						jQuery('#album-content-body').css({"background-color":"#fff"});
						$('.delete-album').hide();
						
						$('#btn-album-add').show();
						$('#album-arrange-tile').hide();
					}
				);
				break;
			}
			case StateName.ImageOne :
			case StateName.ImageDetail :
			case StateName.ImageTile : {
				Helper.getManager().resizer.setHResize2();
				
				jQuery("#hor-info-panel").css("display", "");
				Helper.getManager().resizer.resize();
				
				var album = data.album;
				this.album = data.album;
				
				if ( !Rights.isWrite() ) {
					$('.upload-box').hide();
				}
				else {
					$('.upload-box').show();
				}
				
				jQuery("#content-header-bar").css("display", "");
				jQuery("#content-header").css("height", "80");
				
				var albumName = album.PF_NAME;
				var albumNameFull = album.PF_NAME;

				$('#album-info').empty()
					.append('<span id="album-name"></span>')
					.append('<span id="album-name-full" style="display: none;"></span>');
				
				$('#album-name').next().text(albumNameFull);
				if ( albumName.length > 40 ) {
					albumName = albumName.substr(0, 40-3) + '...';
				}
				
				$('#album-name').text( albumName );		
				
				 if (  Rights.isWrite() ) {
					var $rename = $('<span class="album-rename">ред.</span>').click(function(){ 
						$(this).hide();
						$('#album-name').click() 
					});
					
					$('#album-name').next().after($rename);
				 }
				
				if ( Rights.isWrite() ) {
					$('#album-name')
					.editable("backend.php?controller=ajax&action=cangeAlbumName", { 
				        name : 'albumName',  
						submitdata: {albumId: album.PF_ID},
						tooltip   : albumNameFull,
						style  : "inherit",
						onblur: 'ignore',
						cancel: 'отменить',
						width: '',
						height : '',
						cssclass: 'album-name-edit',
						submit: 'Сохранить',
						fieldCount: 40,
						postSubmitText: 'или',
						startEvent: function(){
							WbsData.set({'isKeyEnable': false});
							$('.album-rename').hide();
						},
						endEvent: function(){
							WbsData.set({'isKeyEnable' : true});
//							$('#album-name').html($('#album-name').text() + '<span class="album-rename">ред.</span>');
							$('.album-rename').show();
						},
						cancelEvent: function() {
							WbsData.set({'isKeyEnable' : true});
							$('.album-rename').show();
						}
						
					});
				}
				else {
					$('#album-name').unbind();
				}
				
				//$('#date-str').get(0).innerHTML = album.PF_DATESTR;
				
				var frontLink = document.mainUrl+album.PF_LINK;
				if ( frontLink.length > 45 ) {
					frontLink = frontLink.slice(0, 22) + '...' + frontLink.slice(-20, frontLink.length);
				}
				
					if (album.PF_STATUS == 0 || album.PF_STATUS ==2) {
						$('#album-access').get(0).innerHTML = "<span class='publish-no'>Альбом не опубликован.</span>";
						$('#album-url').empty();
						
						if ( Rights.isFull() ) 							
						$('#btn-album-setting').text('Опубликовать');
					}
					else if(album.PF_STATUS == 1) {
						$('#album-access').get(0).innerHTML = "<span class='publish-yes'>Альбом опубликован:</span>";
						$('#album-url').empty().append( '<a target="_blank" href="'+document.mainUrl+album.PF_LINK+'">'+frontLink+' <img src="img/new_window_icon.gif" /></a>');
						
						if ( Rights.isFull() )
						$('#btn-album-setting').text('Настройки');
					}
					else if(album.PF_STATUS == 3) {
						$('#album-access').get(0).innerHTML = "<span class='publish-lock'>Альбом опубликован приватно:</span>";
						$('#album-url').empty().append( $('<a target="_blank" href="'+document.mainUrl+album.PF_LINK+'">'+frontLink+' <img src="img/new_window_icon.gif" /></a>'));
						
						if ( Rights.isFull() )
						$('#btn-album-setting').text('Настройки');
					}
						
					if ( !document.iSbtnAlbumSetting ) {
						$('#btn-album-setting').unbind();
						$('#btn-album-setting').get(0).onclick = function(){						
							$('#pp').wbsPopup({
								url: 'backend.php?controller=album&action=albumSetting&albumId='+Helper.getManager().getState({}).albumId,
								width: 760,
								height: 440,
								iframe: true,
								hidePopup: function(){
									Helper.getManager().setState({});
								}
							});						
							return false;
						};
						document.iSbtnAlbumSetting = true;
					}
					
					if ( Rights.isWrite() ) {
						$('#btn-album-setting').show();			
					}
					else {
						$('#btn-album-setting').hide();
					}
				
				$('#album-date-create').text( album.PF_CREATEDATETIME );
				var name = album.PF_CREATEUSERNAME;
				if (name.length > 20)
					name = name.slice(0, 17)+'...';
				$('#album-user').text( name );
				
				var access = '';
				if ( Rights.album == '7' )
					access = 'полные'
				else if ( Rights.album == '3' )
					access = 'чтение и запись'
				else if ( Rights.album == '1' )
					access = 'чтение'
				else 
					access = 'чтение'
				
				$('#album-right').text( access );
				break;	
			}
//			case StateName.ImageOne : {
//				Helper.getManager().resizer.setHResize2();
//				jQuery("#hor-info-panel").css("display", "");
//				Helper.getManager().resizer.resize();
//				
//				jQuery("#content-header-bar").css("display", "");
//				jQuery("#content-header").css("height", "80");
//				break;
//			}
		}
	}
});

ModuleFooter = newClass(null, {
	constructor: function(config) {
		this.config = config;
		this.contentElem = config.elem;
		this.manager = config.manager;
		
	},
	render: function(state, data) {
		
		switch ( state.stateName ) {
			case StateName.Alubms : {
				removeChildNodes(this.contentElem);
//				if ( Rights.manage_album ) {
//					var imageCount = createElem("div", "image-count");
//					imageCount.innerHTML = data.imageCount+' фото';
//					this.contentElem.appendChild(imageCount);
//				}
				
				var imagePager = createElem("div", "image-pager");
				this.contentElem.appendChild(imagePager);
				
				var pager = new WaPager({
					elem: imagePager,
					manager: Helper.getManager()
				});
				pager.render(state, data);
				
				
				break;
			}
			case StateName.ImageDetail :
			case StateName.ImageTile : {
				this.data = data[0];
				removeChildNodes(this.contentElem);
//				this.contentElem = createDiv("");
				var imageSlider = createElem("div", "image-slider");
				var imagePager = createElem("div", "image-pager");
				
				var imagePageSelect = createElem("div", "image-page-select");
				this.contentElem.appendChild(imageSlider);
				
				var $back_button = $('<div class="back_album"></div>');
				
				$('<a href="#" title="К списку альбомов"><img style="width: 25px; height: 25px" src="img/back.gif" alt="К списку альбомов"/>Альбомы</a>').appendTo($back_button).click(function(){
					Helper.getManager().setState({ stateName: StateName.Alubms });
					return false;
				});
				$(this.contentElem).append($back_button);
				this.contentElem.appendChild(imagePager);
				
				var limit  = Helper.getManager().getState().limit;
				
				$(this.contentElem).append('<div class="image-count"><span class="image-page-count">'+limit+'</span>фото на странице</div>');
				$('.image-page-count').clickw(function(e){
					var menu = new PDPAgePop({isPd: true});
					menu.show(e);
					return false;
				});
				
				this.contentElem.appendChild(imagePageSelect);				
				
				var pager = new WaPager({
					elem: imagePager,
					manager: Helper.getManager()
				});
				pager.render(state, data);
				
				if ( document.slider && document.slider != undefined && document.slider.destroy ) {
					document.slider.destroy();
				}
				var slider = new WsbSlider({
					elem: imageSlider,
					widthCss: 150,
					startValue: 144,
					defaultValue: jQuery.cookie('imageSize') ,
					endValue: 512,
					onChange: function(e){
//						for (var i = 0; i <document.imgSizeProp.length; i++) {
//							var obj = document.imgSizeProp[i];
//							
////							console.debug(obj.elem);
//							
//							if ( Number(obj.data.PL_WIDTH) > Number(obj.data.PL_HEIGHT) ){
//								jQuery(obj.img).css("width", e.pos);
//							}
//							else {
//								jQuery(obj.img).css("height", e.pos);
//							}
//							
//							$(obj.elem).css({
//								width: parseInt(e.pos) + parseInt(8), 
//								height: parseInt(e.pos) + parseInt(8)
//							});
//							$('.image-contener i').css({height: parseInt(e.pos) + parseInt(8)});
//						}
						var images = $('.image-src').get();
						for ( var i = 0; i < images.length; i++ ) {
							var $image = $(images[i]);
							
							if ( Number(document.imageGeometry[ $image.attr('gid') ].w) > Number(document.imageGeometry[ $image.attr('gid') ].h) ){
								$image.css("width", e.pos);
//								$image.css("height", 'auto');
							}
							else {
//								$image.css("width", 'auto');
								$image.css("height", e.pos);
							}
							
							$image.parent().css({
								width: parseInt(e.pos) + parseInt(8), 
								height: parseInt(e.pos) + parseInt(8)
							});
							$('.image-contener i').css({height: parseInt(e.pos) + parseInt(8)});
						}
					},
					onChangeComplite: function(e) {		
						var imageArr = $('.image-src').get(); 
						for (var i = 0; i <imageArr.length; i++) {
							var obj = imageArr[i];
							var src  = $(obj).attr('src');
							if ( e.pos <= 96) {
								if ( src )
								src = src.replace(/size\=[0-9]+/, "size=96");
								document.imgSizeProp[i].img_src = src;
								$(obj).attr('src-c', src);
								$(obj).attr('size-c', obj.orig_size);
								$(obj).attr('src', src);
							}
							else if ( e.pos <= 144) {
								if ( src )
								src = src.replace(/size\=[0-9]+/, "size=144");
								document.imgSizeProp[i].img_src = src;
								$(obj).attr('src-c', src);
								$(obj).attr('size-c', obj.orig_size);
								$(obj).attr('src', src);
							}
							else if ( e.pos <= 256 && obj.orig_size >= 256) {
								if ( src )
								src = src.replace(/size\=[0-9]+/, "size=256");
								document.imgSizeProp[i].img_src = src;
								$(obj).attr('src-c', src);
								$(obj).attr('size-c', obj.orig_size);
								$(obj).attr('src', src);
							}
							else if ( e.pos <= 512 && obj.orig_size >= 512) {
								if ( src )
								src = src.replace(/size\=[0-9]+/, "size=512");
								document.imgSizeProp[i].img_src = src;
								$(obj).attr('src-c', src);
								$(obj).attr('size-c', obj.orig_size);
								$(obj).attr('src', src);
							}
						}
						
						jQuery.cookie('imageSize',e.pos);
						document.imageSize = e.pos;
						
						for (var i = 0; i <document.imgSizeProp.length; i++) {
							document.imgSizeProp[i].loadSrc();
						}

					}
				});	
				document.slider = slider;
				
				var offset = Helper.getManager().getState().offset;
				var limit = Helper.getManager().getState().limit;
				
				$(imagePageSelect).wbsPager({
					current: offset/limit + 1,
					count: Math.ceil(data.total/limit) ,
					limit: 3,
					pagegoto: function(num) {
						if ( $('#arrange-link').hasClass('opened') )
							Helper.getManager().setStateParam({arrange: true});
						Helper.getManager().setState({
							offset: num*limit - limit
						});
					}
				});
				
				return this.contentElem;
				break;
			}
			case StateName.ImageOne : {
				this.data = data.data['c'];
				this.datal = data.data['l'];
				this.datar = data.data['r'];
				
				removeChildNodes(this.contentElem); 
//				this.contentElem = createDiv("");
				var data_ = data.data['c'];
				$('<div class="back-to-album"><img src="img/back.gif" width="25" height="25" alt="В альбом" /><a href="#">В альбом</a></div>').appendTo(this.contentElem).find('a').click(function(){
					
					Helper.getManager().setState({
						stateName: StateName.ImageTile,
						albumId: data_.PF_ID
					});
							
					return false;
				});
				
				var imageSelector = createElem("div", "image-selector");
				
				var aLeft = createElem("a");
				$(aLeft).attr("href", '#');
				aLeft.onclick = function() {
					if (this.datal && this.datal.PL_ID != undefined)
					Helper.getManager().setState({
						viewName: "ImageOn",
						imageId: this.datal.PL_ID
					});
					return false;
				}.bind(this);				
				
				aLeft.innerHTML = "&larr;";
					
				var span = createElem("span");
				span.innerHTML = $.sprintf('Фото %s из %s', parseInt(this.data.PL_SORT) + 1, data.imageCount);
				
				var aRight = createElem("a");
				$(aRight).attr("href", '#');
				aRight.onclick = function() {
					if (this.datar && this.datar.PL_ID != undefined)
					Helper.getManager().setState({
						viewName: "ImageOn",
						imageId: this.datar.PL_ID
					});
					return false;
				}.bind(this);
				aRight.innerHTML = "&rarr;";
				
				imageSelector.appendChild(aLeft);
				imageSelector.appendChild(span);
				imageSelector.appendChild(aRight);

				this.contentElem.appendChild(imageSelector);
				return this.contentElem;
				break;
			}
			
		}
	}
});

PDPAgePop = newClass (WbsPopmenu, {
	constructor: function(config) {
//		this.manager = manager;
		
		document.isArrangemod = function(){
			if ( $('#arrange-link').hasClass('opened') )
				Helper.getManager().setStateParam({arrange: true});
		};
		
		var items = [
			{label: "10", onClick: function() { document.isArrangemod.apply(); Helper.getManager().setState({limit:10, offset: 0}); $.cookie('limit', 10); }},
			{label: "20", onClick: function() { document.isArrangemod.apply(); Helper.getManager().setState({limit:20, offset: 0}); $.cookie('limit', 20); }},
			{label: "50", onClick: function() { document.isArrangemod.apply(); Helper.getManager().setState({limit:50, offset: 0}); $.cookie('limit', 50); }},
			{label: "100", onClick: function() { document.isArrangemod.apply(); Helper.getManager().setState({limit:100, offset: 0}); $.cookie('limit', 100); }},
			{label: "150", onClick: function() { document.isArrangemod.apply(); Helper.getManager().setState({limit:150, offset: 0}); $.cookie('limit', 150); }}
		];
		
		this.superclass().constructor.call(this, {items: items, isPd: config.isPd});
	}
});

TemplateImageTile = {
	render: function(elem, data) {
		this.contentElem = elem;
		
		$(elem).css({height: '100%'});
		
		var records = data.data;
		
		this.ulContener = createElem('ul', 'album-list');
		
		var liList = new Array();
		
		document.imgSizeProp = new Array();
		document.imageGeometry = new Array();
		
		for(var i=0; i<records.length; i++) {
			var li = createElem('li');
			jQuery(li).attr("uid", records[i].PL_ID );
			
			var img = new ImageItem(records[i], i);
			li.appendChild( img.elem );
			this.ulContener.appendChild(li);
			liList.push(li);
		}
		
		for (var i = 0; i <document.imgSizeProp.length; i++) {
			document.imgSizeProp[i].loadSrc();
		}
		
		this.contentElem.appendChild(this.ulContener);
		
	}
};
ImageItem = newClass(null, {
	constructor: function (record, i) {
		
		this.isLoaded = false;
		this.num = i;
		
		this.data = record;
		var div = createElem("div", "image-contener");
		this.elem = div;
		

		if (document.imageSize) {
			$(div).css({
				width:  parseInt(document.imageSize) + parseInt(6), 
				height:  parseInt(document.imageSize) + parseInt(6)
			});
			
			
			
			if ( $.browser.msie && ($.browser.version == '6.0' || $.browser.version == '7.0'))
				$('<i></i>').css({height: document.imageSize}).appendTo(div);
			
		}
		
		var img = createElem("img", "image-src");
		this.img_src = 'image.php?filename=';
		this.img_src += record.PL_DISKFILENAME;
		this.img_src += '&albumId=';
		this.img_src += record.PF_ID;
		
		img.data = this.data;		
		img.orig_size = Math.max(this.data.PL_WIDTH, this.data.PL_HEIGHT);
		
		$(img).addClass('static');
		
		var size = jQuery.cookie('imageSize');
		var size_str = '144';
		if ( size > 144 && img.orig_size >= 256) {
			size_str = 256;
		}
		if ( size > 256 && img.orig_size >= 512) {
			size_str = 512;
		}


		this.img_src += '&size='+size_str+'&v='+record.PL_ROTATE;
		this.img = img;
		this.img.srcurl = this.img_src;

		div.appendChild(img);
		
		var $img = $(img);
		if ( record.PL_ID == ImageInfo.lastImageId() ) {
			$img.addClass('oldImage');
		}

		jQuery("#album-content-body").bind("scroll", function(){
			this.scrollHandle();
		}.bind(this));
		
		document.imgSizeProp.push( this );		
		document.imageGeometry[record.PL_ID] = 
		{
			w: this.data.PL_WIDTH,
			h: this.data.PL_HEIGHT
		};
		$(img).attr('gid', record.PL_ID);
		
		img.owner_obj = this;
//		console.debug(img.owner_obj);
		img.onclick = function () {
			
			document.isProcessSleep = true;
			if ( this.isArrange ) return false;
						
			var position = $(this).offset();
			var width = $(this).width();
			var height = $(this).height();
			var k = width / height;
			
			var $img = $('<img id="previewimg"/>').attr('src', $(this).attr('src')).css({
				position: 'absolute',
				left: position.left,
				top: position.top,
				width: width,
				height: height,					
				'z-index': 102
			});
			$('body').append($img);
			this.$img = $img;
			
			var w = $('#album-content-body').width();
			var h = $('#album-content-body').height();
			var offset = $('#album-content-body').offset();
			
			var $backg = $('<div id="backg" ></div>').css({
				position: 'absolute',
				width: w,
				height: h,
				left: offset.left,					
				top: offset.top,
				opacity: 0,
				backgroundColor: '#FFFFFF',
				'z-index': 100
			})
			$('body').append($backg);
			
			
			var size = jQuery.cookie('sizeOneView');
			size1 = (size) ? size : 970;
			size2 = maxSizeByImg(Math.max(this.data.PL_WIDTH, this.data.PL_HEIGHT), [256, 512, 750, 970]);
			size = Math.min(size1, size2);
		
			if ( size > 144 ) {
				var k = this.data.PL_WIDTH / this.data.PL_HEIGHT;
				if (this.data.PL_WIDTH > this.data.PL_HEIGHT) {
					this.data.PL_WIDTH = parseInt(size);
					this.data.PL_HEIGHT = parseInt(size/k);
				}
				else {
					this.data.PL_HEIGHT = parseInt(size);
					this.data.PL_WIDTH = parseInt(size*k);			
				}
			}
			else {
				this.data.PL_WIDTH = size;
				this.data.PL_HEIGHT = size;
			}
				
			var iwidth = this.data.PL_WIDTH;
			var iheight = this.data.PL_HEIGHT;
	
			w = Math.min(w, 970);
			h = Math.min(h, 970);
			if ( w- width > h- height ) {
				w = Math.min(iwidth, w);
			}
			else {
				h = Math.min(iheight, h);
			}
	
			if ( h - w / k > w - h * k ) {
				var nW = w;
				var nH = w / k;
			}
			else {
				
				var nW = k * h;
				var nH = h;
			}				
			

			
			document.isAnim = true;
			$img.animate({ 
				top: $('#album-content-body').offset().top + $('#album-content-body').height()/2 - nH/2, 
				left: $('#album-content-body').width()/2 - nW/2, 
				width: nW, 
				height: nH
			}, 300 , function(){
				
				if (!document.isAnim) return false;
				Helper.getManager().setState({		 
					stateName: StateName.ImageOne,
					imageId: $(this).parent().parent().attr('uid')
				},function(){
					$img.click(function(){
						$(this).unbind().remove();
						$('.image-one').remove();
						$('#backg').remove();					
						document.isAnim = false;
						if ( Helper.getManager().getState().stateName != StateName.ImageTile )
						Helper.getManager().setState({					 
							stateName: StateName.ImageTile
						})				
					});
				});
			}.bind(this));

			document.imageOneLoad = function(){
				$('#backg').remove();
				this.animate({ opacity: 0}, 600, function(){
					$('#previewimg').remove();
					
					document.isProcessSleep = false;
					Helper.getManager().stopProccess();
				});
			}.bind($img);

			$backg.animate({opacity: 1}, 200);

			return false;
		}.bind(img);

	},
	scrollHandle: function () {
		this.loadSrc();
	},
	getActualSrc: function(src, orig_size) {
		var size = jQuery.cookie('imageSize');
		
		if ( size <= 144) {
			size = 144;
			src = src.replace(/size\=[0-9]+/, "size="+size);
		}
		else if ( size <= 256 && orig_size >= 256) {
			size = 256;		
			src = src.replace(/size\=[0-9]+/, "size="+size);
		}
		else if ( size <= 512 && orig_size >= 512) {
			size = 512;		
			src = src.replace(/size\=[0-9]+/, "size="+size);
		}
		return src;
	},
	loadSrc: function() {
		if ( !this.isLoaded ) {
			if ( Helper.getManager().imageHashe[this.data.PL_ID] ) {
				
				if ( $(this.img).attr('src-c') )
					this.img.setAttribute("src", $(this.img).attr('src-c') );
				else
					this.img.setAttribute("src", this.img.srcurl);
				
				jQuery(this.img).css("opacity", 1);							
			}
			else {				
				jQuery(this.img).css("opacity", 0);
				var h = jQuery("#main").height();
				var w = jQuery('#album-content-body').width();
				var wImage = jQuery.cookie('imageSize');
				
				var top = 0;
				
				var z = Math.ceil(this.num*wImage/w )*wImage;
				if ( z <= (h + parseInt(jQuery('#album-content-body').scrollTop() )) ) {
					this.img.setAttribute("src", this.getActualSrc(this.img.srcurl, $(this.img).attr('size-c')));
					this.img.onload = function(){
						Helper.getManager().imageHashe[this.data.PL_ID] = this.data.PL_ID;
						jQuery(this.img).animate({ opacity: 1 }, 500 );
						
					}.bind(this);
					this.isLoaded = true;
				}
			}
		}
		var param = jQuery.cookie('imageSize');
		if ( Number(this.data.PL_WIDTH) > Number(this.data.PL_HEIGHT) ){
			this.img.style.width = (param) + 'px';
		}
		else {
			this.img.style.height = (param) + 'px';			
		}
		
		this.elem.style.width = (parseInt(param) + parseInt(8)) + 'px';
		this.elem.style.height = (parseInt(param) + parseInt(8)) + 'px';
		
	}
});

TemplateImageDetail = {
	render: function(elem, data) {
		
		this.elem = elem;
		var records = data.data;
		var ulContener = createElem('ul', 'album-list-detail');
		
		for(var i=0; i<records.length; i++) {
			var li = createElem('li');
			li.appendChild( this._createImageItem(records[i]) );
			ulContener.appendChild(li);
		}		
		this.elem.appendChild(ulContener);
		
		return elem;
	},
	_createImageItem: function(record) {
		
		var div = createElem("div", "image-thumb");
		addClass(div, "galery");
		div.style.width = document.imageSize;
		div.style.height = document.imageSize;
		
		var img = createElem("img", "galery-img");
		var img_src = 'image.php?filename=';
		img_src += record.PL_DISKFILENAME;
		img_src += '&albumId=';
		img_src += record.PF_ID;
		img_src += '&size=256';
		
		img.setAttribute("src", img_src);
		
		img.onload = function (){
			div.appendChild(img);
			if (jQuery(this).width() > jQuery(this).height() ) {
				document.imgSizeProp.push( {target: this, prop: "width"} );
				this.style.width = document.imageSize;
			}
			else {
				document.imgSizeProp.push( {target: this, prop: "height"} );
				this.style.height = document.imageSize;
			}
		}.bind(img);
		
		
		img.style.width = document.imageSize; 
		
		var detailDiv = createDiv("");
		detailDiv.innerHTML = "описание";
		div.appendChild(detailDiv);
		return div;
	}	
};

WAResize = newClass(null, {
	HEADER_HEIGHT: 50,	
	constructor: function (config) {
		jQuery(window).resize(function(){
			this.resize();
		}.bind(this));
	},
	setHResize1: function(){
		this.HEADER_HEIGHT = 41;
	},
	setHResize2: function(){
		this.HEADER_HEIGHT = 102;
	},
	resize: function () {
		var size = jQuery(window).height() - this.HEADER_HEIGHT;
		jQuery('.resize').height( size );
		
		jQuery('#album-content-body').height( size - 26 );
	}
});

TemplateImageOn = newClass(null, {
	constructor: function(config) {
		this.data = null;
		jQuery(window).resize(function(){
			this.resize();
		}.bind(this) );
	},
	
	resize: function () {
		
		var imgW = this.data.PL_WIDTH;
		var imgH = this.data.PL_HEIGHT;
		var imgK = imgW / imgH;
		var k = imgW / imgH;
		
		
		var size = Math.min(parseInt(jQuery.cookie('sizeOneView')), parseInt($('.size_list li:last').attr('s')));
		
		var w = $('#album-content-body').width();
		var h = $('#album-content-body').height();	
		w = Math.min(w, size);
		h = Math.min(h, size);
		
		if ( size == 96 || size == 144 ) {
			w = size;
			h = size;
		}
		else if ( (Data.data.c && Data.data.c.sizes && Data.data.c.sizes[ size ]) &&			
		           Data.data.c.sizes[ size ].w < w && Data.data.c.sizes[ size ].h < h ) {
			w = Data.data.c.sizes[ size ].w;
			h = Data.data.c.sizes[ size ].h;
		}
		else {
			if ( Data.data.c.PL_WIDTH < w && Data.data.c.PL_HEIGHT < h ) {
				w = Data.data.c.PL_WIDTH;
				h = Data.data.c.PL_HEIGHT;
			}
			else {
				if ( h - w / imgK > w - h * imgK ) {
					h = parseInt( w / imgK );
				}
				else {
					w = parseInt( imgK * h );
				}
			}
		}
		
		if (!isNaN(parseInt(w)) && !isNaN(parseInt(h))) {
			$(this.image).width(parseInt(w));
			$(this.image).height(parseInt(h));
			
			$(this.image).css({ 
				marginTop: $('#album-content-body').height()/2 - h/2 
			});
		}
		
	},
	
	render: function(elem, data) {
		if (data.data.r && data.data.r.PL_ID != undefined) {
			WbsData.set({nextIdPhoto: data.data.r.PL_ID});
		}
		else {
			WbsData.set({nextIdPhoto: null});
		}
		
		if (data.data.l && data.data.l.PL_ID != undefined)
			WbsData.set({prevIdPhoto: data.data.l.PL_ID});
		else
			WbsData.set({prevIdPhoto: null});
					
		this.contentElem = elem;
		data = data.data;
		this.data = data['c'];
		if (data['r'])
		this.dataNext = data['r'];
		if (data['l'])
		this.dataPrev = data['l'];
		
		ViewImageOn.originalW = this.data.PL_WIDTH;
		ViewImageOn.originalH = this.data.PL_HEIGHT;
		
		var k = this.data.PL_WIDTH / this.data.PL_HEIGHT;
		
		var div = createDiv("album-content-body");
		div.style.textAlign = "center";
		
		var size = jQuery.cookie('sizeOneView');
		if (size) {
			size1 = size;
		}
		else {
			size1 = 970;
			jQuery.cookie('sizeOneView', 970);
		}
		size2 = maxSizeByImg(Math.max(this.data.PL_WIDTH, this.data.PL_HEIGHT), [256, 512, 750, 970]);
		size = Math.min(size1, size2);
		
		if ( size > 144 ) {
			if (parseInt(this.data.PL_WIDTH) > parseInt(this.data.PL_HEIGHT)) {
				this.data.PL_WIDTH = parseInt(size);
				if ( this.data.sizes && this.data.sizes[size]) {
					this.data.PL_HEIGHT = this.data.sizes[size].h;
				}
				else {
					this.data.PL_HEIGHT = parseInt(this.data.PL_WIDTH / k);
				}
			}
			else {
				this.data.PL_HEIGHT = parseInt(size);
				if ( this.data.sizes && this.data.sizes[size]) {
					this.data.PL_WIDTH = this.data.sizes[size].w;
				}
				else {
					this.data.PL_WIDTH = parseInt(this.data.PL_HEIGHT * k);
				}
			}
		}
		else {
			this.data.PL_WIDTH = size;
			this.data.PL_HEIGHT = size;
		}
		
		var img_src = 'image.php?filename=';
		img_src += this.data.PL_DISKFILENAME;
		img_src += '&albumId=';
		img_src += this.data.PF_ID;
		img_src += '&size='+ size  +'&v='+this.data.PL_ROTATE;
		
		this.contentElem.appendChild(div);
		
		var $image = $('<img class="image-one" />');
		this.image = $image.get(0);
		$(div).append($image);
		if ( $.browser.mozilla )
			$image.hide();
		
//		setTimeout(function(){
			
			
//		}, 1000);
		
		$image.click ( function () {
			Helper.getManager().setState({
				stateName: StateName.ImageTile,
				albumId: this.data.PF_ID,
				offset: offset
			});
		}.bind(this));
		$image.load (function () {
			this.resize();
			if ( $.browser.mozilla )
				$image.show();
				
			if (document.imageOneLoad)
				document.imageOneLoad();
		}.bind(this));
		
		$image.attr('src', img_src);
		
		if (!document.oldImageId ) {
			if(this.dataPrev) {
				var img_src3 = 'image.php?filename=';
				img_src3 += this.dataPrev.PL_DISKFILENAME;
				img_src3 += '&albumId=';
				img_src3 += this.dataPrev.PF_ID;
				img_src3 += '&size='+size+'&v='+this.dataPrev.PL_ROTATE;
				new Image().src = img_src3;
			}
			if(this.dataNext) {
				var img_src2 = 'image.php?filename=';
				img_src2 += this.dataNext.PL_DISKFILENAME;
				img_src2 += '&albumId=';
				img_src2 += this.dataNext.PF_ID;
				img_src2 += '&size='+size+'&v='+this.dataNext.PL_ROTATE;
				new Image().src = img_src2;
			}
		}
		else if (this.dataNext && this.dataNext.PL_ID == document.oldImageId) {
			if (this.dataPrev) {
				var img_src3 = 'image.php?filename=';
				img_src3 += this.dataPrev.PL_DISKFILENAME;
				img_src3 += '&albumId=';
				img_src3 += this.dataPrev.PF_ID;
				img_src3 += '&size='+size+'&v='+this.dataPrev.PL_ROTATE;
				new Image().src = img_src3;
			}
		}
		else if (this.dataPrev && this.dataPrev.PL_ID == document.oldImageId) {
			if (this.dataNext) {
				var img_src2 = 'image.php?filename=';
				img_src2 += this.dataNext.PL_DISKFILENAME;
				img_src2 += '&albumId=';
				img_src2 += this.dataNext.PF_ID;
				img_src2 += '&size='+size+'&v='+this.dataNext.PL_ROTATE;
				new Image().src = img_src2;
			}
		}
		
		document.oldImageId = this.data.PL_ID;
		
		var offset = (Helper.getManager().getState().offset) ? Helper.getManager().getState().offset : 0;	
	}
	
});

function maxSizeByImg(size, sizeLimits) {
	var _return = 144;
	for ( key in sizeLimits ) {
		if ( size > sizeLimits[key] ) {
			_return = sizeLimits[key];
		}
		else
			break;
	}
	return _return;
};

/**
 * @class WaArrange
 */
WaArrange = newClass(null, {
	constructor: function (config) {

		this.listElements = config.listElements;
		this.updateUrl = config.updateUrl;
		
		this.makeFunction = config.makeFunction;
		this.cssClass = config.cssClass;
		
		document.isMoveSorted = false;
		$('.oldImage').removeClass('oldImage');

		for(var i=0; i<this.listElements.length; i++) {
			if ( $.browser.msie  ) {
				$('.'+this.cssClass, this.listElements[i]).get(0).attachEvent('ondragstart', function(){
					window.event.cancelBubble = true;
			        window.event.returnValue = false;
			        document.arrangeAction = true;
				});
			}
			
			$('.'+this.cssClass, this.listElements[i]).get(0).isArrange = true;
			$('.'+this.cssClass, this.listElements[i]).mousedown( function(e){this.elementMouseDownHandle(e)}.bind(this) );
			
			$(this.listElements[i]).bind("mousemove", this.sorted);	
			$(this.listElements[i]).find('.image-contener').attr('title', "Щелкните, чтобы выбрать, перетащите, чтобы задать порядок сортировки");
			
		}
		$(document).mouseup( function(e){this.elementMouseUpHandle(e)}.bind(this)  );
	},
	
	elementSelect: function($elem){
		
		var $count = $('.selectCount');
		 
		var count = ( $count.text().length > 0 ) ? $count.text() : 0;
		
		var $ck = $('input', $elem);
		
 		if ( $ck.attr('checked') ) {
			$ck.attr('checked',  '' );
			$elem.find('.image-contener').css({'background-color': '#7C7C7C'});
			
			var c = count - 1;
			c = ( c < 1 ) ? 0 : c ;
			
			$count.text( count - 1 );
		}
		else {
			$ck.attr('checked',  'checked' );
			
			$elem.find('.image-contener').css({'background-color': '#d3a300'});
			
			$count.text( parseInt(count) + 1 );
			$count.show();
		}		
	},
	
	elementMouseDownHandle: function (e) {

		e = e || window.event;
		var target = e.target || e.srcElement;
		
		if ( Helper.getManager().getState({}).stateName == StateName.Alubms )
			var $self = $(target).parent().parent().parent()
		else
			var $self = $(target).parent().parent();
		
			
		$self.attr("id", "sel");
		
		var $sorted = $("<div id='sorted'></div>");
		$sorted.css("position", "absolute");
		$sorted.css("cursor", "move");
		$sorted.css("border", "5px dashed yellow");
		$sorted.css("z-index", "100");
		if ( !jQuery.browser.msie ) {
			$sorted.css("width", $self.width() - 10);
			$sorted.css("height", $self.height() - 10);
		} else {
			$sorted.css("width", $self.width() );
			$sorted.css("height", $self.height() );
		}
		$self.prepend($sorted);
		
//		this.elementSelect($self);
		
		this.isClick = true;
		document.isArrangeClick = true;
		document.arrangeAction = true;
		if (!$.browser.msie)
			e.originalEvent.preventDefault();		
		
		
		return false;
	},
	
	elementMouseUpHandle: function(e) {
		if ( this.isClick ) {
			e = e || window.event;
			var target = target = e.target || e.srcElement;
			var $self = $(target).parent();
				
			jQuery("#sorted").remove();
			jQuery("#sel").removeAttr( "id" );		

			var list = new Array();		
			jQuery(".album-list li").each(function(i, el) {
				list.push( jQuery(el).attr('uid') );
			
			});
			if (document.isMoveSorted) {
				jQuery.post(this.updateUrl, { 
					data: list.toString(), 
					albumId: Helper.getManager().getState({}).albumId,
					limit: Helper.getManager().getState({}).limit, 
					offset: Helper.getManager().getState({}).offset }, 
					function(){
						document.isMoveSorted = false;
					}
				);
			}
			else {
				this.elementSelect($self);
			}
			if ( $.browser.msie  ) {
				$(target).get(0).attachEvent('ondragstart', function(){
						window.event.cancelBubble = true;
				        window.event.returnValue = false;
				        document.arrangeAction = true;
				});
			}
				
			this.isClick = false;
			document.isArrangeClick = false;
			
			return false;
		}
	},
	
	sorted: function(e) {
		e = e || window.e;
		if (document.isArrangeClick) {

			if ( jQuery(this).attr("id") != "sel" ) {
				var orig = jQuery("#sel").clone(true);
				
//				orig.find('img').obj_owner = jQuery("#sel").find(img).get(0).obj_owner;
				
				var target = jQuery(this);
	
				var list = jQuery(".album-list li").get();
				for(var i=0; i<list.length; i++) {
					if ( list[i] == jQuery('#sel').get(0) )  { 
						var isL = true;
						break;
					}				
					if ( list[i] == this ) { 
						var isL = false; 
						break;
					}
				}
				jQuery("#sel").remove();
				if ( isL ) {
					jQuery(this).after(orig);
					document.isMoveSorted = true;
					
				}
				else {
					jQuery(this).before(orig);
					document.isMoveSorted = true;
				
				}
			}
			
		}
		if (!$.browser.msie)
			e.originalEvent.preventDefault();
		
	}
});

/**
 * @class WaPager 
 */
WaPager = newClass (null, {
	constructor: function(param) {
		this.contentElem = param.elem;
		this.data = param.data;
		this.manager = param.manager;
	},
	render: function(state, data){
		this.data = data;
		
		removeChildNodes(this.contentElem);
		var div = createDiv("");
		var start = parseInt(  data.offset ) + 1;
		var end = parseInt( data.offset ) + Math.min( parseInt( data.limit ), parseInt( data.total ) );
		if ( !data.limit ) {
			start = 1;
			end =  data.total;
		}
		if ( end > data.total ) end = data.total;
		
		if (data.total == 0) {
			if( Helper.getManager().getState().stateName == StateName.Alubms )
				div.innerHTML = "<div class='page-button-div'>Создайте новый альбом, чтобы загрузить фотографии.</div>";
			else
				div.innerHTML = "<div class='page-button-div'>В этом альбоме еще нет фотографий.</div>";
		} else		
		if( Helper.getManager().getState().stateName == StateName.Alubms ) {
			div.innerHTML = "<div class='page-button-div'>"+$.sprintf("%d фото, %d альбом(ов)",data.imageCount,data.total)+"</div>";
		}
		else
			div.innerHTML = "<div class='page-button-div'>Фото с<span>" +start+"</span>&mdash;<span>"+ end +"</span> из <span>"+data.total+"</span></div>";
//		var pageButtonDiv = createDiv("page-button-div");
//		pageButtonDiv.style.float = "left";
//		div.appendChild( pageButtonDiv );
		
		var leftPageA = createElem("a");

		this.contentElem.appendChild(div);
	}
});
Helper = function() {
	var manager = null;
	return {
		setManager: function (manager_) {
			manager = manager_;
		},
		getManager: function () {
			return manager;
		}
	}
}();

Data = {};

Semaphore = function() {
	var $buttonArray = new Array();
	var isProccess = false;
	return {
		init: function() {
			$buttonArray = new Array();
		},
		addButton: function($button) {
			$buttonArray.push($button.children('span'));
		},
		startProccess: function($button) {
			if ( isProccess ) return false;
			for (var key in $buttonArray) {
				if ( $buttonArray[key] === $button) continue;
				$buttonArray[key].removeClass('active').addClass('desactive');
			}
			isProccess = true;
			return true;
		},
		stopProccess: function($button) {
			isProccess = false;
			
			for (var key in $buttonArray) {
				if ( $buttonArray[key] === $button) continue;
				$buttonArray[key].removeClass('desactive').addClass('active');
			}
		}
	}
}();

PDAlbum = {};
PDImage = {};

function removeChildNodes(ctrl)
{
  while (ctrl.childNodes[0])
  {
    ctrl.removeChild(ctrl.childNodes[0]);
  }
}

/**
 * widget View Mode Selector
 */
(function($) {
	$.fn.viewModeSelector = function (param) {
		
		var $vmSelector = $('<div class="viewmode-select"><span>Просмотр:</span></div>');
		
		return this.each(function() {
			$(this).append($vmSelector);
			
			for (i in param) {
				var item = param[i]
				
				var $elem = $('<div class="mode-elem" > </div>');
				$elem.append('<img src="'+item.ico+'">');
				$vmSelector.append($elem);
				$elem.clickw(function(){
					var $img = $(this).children('img');
					var src = $img.attr('src');
					
					src = src.replace(/\.gif/ig, '_on.gif');
					src = src.replace(/\.png/ig, '_on.png');
					
					$img.attr('src', src);
				});
				
			}
		});
	};
})(jQuery);

/**
 * widget Window More function
 */
(function($) {
	
	var $morewin = $('<div>sss </div>');
	
	$.fn.moreWindow = function(option){
		var settings = $.extend({
			
		}, option);

		this.each(function() {
			var $target = $(this);
			
			$morewin.css({
				position: 'absolute',
				left: $target.offset().left,
				top: parseInt($target.offset().top) + $target.width(),
				border: '1px soled red'			
			});
		});
		
	};	
})(jQuery);

(function($) {
	$.fn.clickw = function(callback){
		this.each(function() {
			this.onclick = callback;
		});
	};	
})(jQuery);

(function($) {
	 $.extend($.fn, { 
        // Select a text range in a textarea
        selectRange: function(start, end){
            // use only the first one since only one input can be focused
            if ($(this).get(0).createTextRange) {
                var range = $(this).get(0).createTextRange();
                range.collapse(true);
                range.moveEnd('character',   end);
                range.moveStart('character', start);
                range.select();
            }
            else if ($(this).get(0).setSelectionRange) {
                $(this).bind('focus', function(e){
                    e.preventDefault();
                }).get(0).setSelectionRange(start, end);
            }
            return $(this);
        },
        
        clickToSelect: function() {
        	this.each(function(){
        		$(this).click(function(){
        			var $self = $(this);
        			$self.selectRange( 0, $self.val().length );
        		});
        	});
        }
	 });
})(jQuery);

function htmlspecialchars_(text)
{
   var chars = Array("&", "<", ">", '"', "'");
   var replacements = Array("&amp;", "&lt;", "&gt;", "&quot;", "'");
   for (var i=0; i<chars.length; i++)
   {
       var re = new RegExp(chars[i], "gi");
       if(re.test(text))
       {
           text = text.replace(re, replacements[i]);
       }
   }
   return text;
} 
function htmlspecialchars_decode_(text)
{
   var chars = Array("&amp;", "&lt;", "&gt;", "&quot;", "'");
   var replacements = Array("&", "<", ">", '"', "'");
   for (var i=0; i<chars.length; i++)
   {
       var re = new RegExp(chars[i], "gi");
       if(re.test(text))
       {
           text = text.replace(re, replacements[i]);
       }
   }
   return text;
} 

(function($){
	var formats = {
		'%': function(val) {return '%';},
		'b': function(val) {return  parseInt(val, 10).toString(2);},
		'c': function(val) {return  String.fromCharCode(parseInt(val, 10));},
		'd': function(val) {return  parseInt(val, 10) ? parseInt(val, 10) : 0;},
		'u': function(val) {return  Math.abs(val);},
		'f': function(val, p) {return  (p > -1) ? Math.round(parseFloat(val) * Math.pow(10, p)) / Math.pow(10, p): parseFloat(val);},
		'o': function(val) {return  parseInt(val, 10).toString(8);},
		's': function(val) {return  val;},
		'x': function(val) {return  ('' + parseInt(val, 10).toString(16)).toLowerCase();},
		'X': function(val) {return  ('' + parseInt(val, 10).toString(16)).toUpperCase();}
	};

	var re = /%(?:(\d+)?(?:\.(\d+))?|\(([^)]+)\))([%bcdufosxX])/g;

	var dispatch = function(data){
		if(data.length == 1 && typeof data[0] == 'object') { //python-style printf
			data = data[0];
			return function(match, w, p, lbl, fmt, off, str) {
				return formats[fmt](data[lbl]);
			};
		} else { // regular, somewhat incomplete, printf
			var idx = 0; // oh, the beauty of closures :D
			return function(match, w, p, lbl, fmt, off, str) {
				return formats[fmt](data[idx++], p);
			};
		}
	};

	$.extend({
		sprintf: function(format) {
			var argv = Array.apply(null, arguments).slice(1);
			return format.replace(re, dispatch(argv));
		},
		vsprintf: function(format, data) {
			return format.replace(re, dispatch(data));
		}
	});
})(jQuery);
