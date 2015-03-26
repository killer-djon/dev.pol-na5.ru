
Behaviour.register({
	'.remove_relatedproduct_handler': function(element){	
		element.onclick = function(){	
			var catBlock = getLayer("related-product-"+this.getAttribute('productID'));
			catBlock.parentNode.removeChild(catBlock);
			beforeUnloadHandler_contentChanged = true;
		};
	},
	'.remove_relatedpost_handler': function(element){	
		element.onclick = function(){	
			var catBlock = getLayer("related-posts-"+this.getAttribute('ArticleID'));
			catBlock.parentNode.removeChild(catBlock);
			beforeUnloadHandler_contentChanged = true;
		};
	},
	'#tags-container * input': function(element){
		
		element.onfocus = function(){
		
			focused_tags_field = this;
		}
	},
});


function prdset_addRelatedProduct(productID, name){
	if(getLayer('related-product-'+productID)){
		var objBlock = getLayer('related-product-'+productID);
		objBlock.parentNode.removeChild(objBlock);
		return false;
	}
	
	var objCatBlock = document.createElement('div');
	objCatBlock.id = 'related-product-'+productID;
	var objSpan = createTag('span', objCatBlock);
	objSpan.innerHTML = name;
	var objInput = createTag('input', objCatBlock);
	objInput.name = 'related_products[]';
	objInput.value = productID;
	objInput.style.display = "none";
	var objA = createTag('a', objCatBlock);
	objA.href = '#remove_relatedproduct';
	objA.className = "remove_relatedproduct_handler";
	objA.setAttribute('productID', productID);
	objA.onclick = function(){
		
		var catBlock = getLayer("related-product-"+this.getAttribute('productID'));
		catBlock.parentNode.removeChild(catBlock);
	};
	var objRemoveImg = createTag('img', objA);
	objRemoveImg.src = 'images/remove.gif';
	objRemoveImg.alt = translate.DELETE_BUTTON;
	objRemoveImg.border = 0;
	objRemoveImg.hspace = 6;
	
	getLayer("related-products-container").appendChild(objCatBlock);
	return true;
}

var relatedProductList = {
	'products_ids': {},
	
	'load': function(){
		
		var objInputs = getElementsByClass('',getLayer('related-products-container'),'input');
		for(var j=objInputs.length-1; j>=0; j--){
		
			this.products_ids[objInputs[j].value] = 1;
		}
	},
	
	'checkedProduct': function(objProductLink){
	
		if(this.products_ids[objProductLink.getAttribute('productID')]){
		
			var p = objProductLink.parentNode;
			var wnd = objProductLink.wnd;
			var objChecked = wnd.document.createElement('img');
			objChecked.src = 'images_common/checked.gif';
			objChecked.hspace = 4;
			p.insertBefore(objChecked, objProductLink);
		}else{
		
			var p = objProductLink.parentNode;
			var objChecked = getElementsByClass('', p, 'img');
			if(objChecked.length){
				p.removeChild(objChecked[0]);
			}
		}
	}
};


function loadProductList(_node, _wnd, offset){
  var req = new JsHttpRequest();
  var node = _node;
  var wnd = _wnd;
  var productID = getLayer('product-id').value;
			  
  req.onreadystatechange = function(){
		
	if (req.readyState != 4)return;
	
	node.hideLoadingMsg();
	if(req.responseText)alert(req.responseText);
	
	if(!req.responseJS.products || !req.responseJS.products.length)return;
		
	var objLi = getLayer(node.getID()+'_end', wnd);
	if(wnd.productsBubble && wnd.productsBubble.length){ 
		for(var _lp=wnd.productsBubble.length-1; _lp>=0; _lp--){
			if(wnd.productsBubble[_lp].parentNode)wnd.productsBubble[_lp].parentNode.removeChild(wnd.productsBubble[_lp]);
		}
		wnd.productsBubble = null;
	}

		var objDiv = wnd.document.createElement('div');
		objDiv.className = "productsBubble";
		
		chooseRelatedProduct = function(){
			
			var res = prdset_addRelatedProduct(this.getAttribute('productID'),this.getAttribute('productName'));
			relatedProductList.products_ids[this.getAttribute('productID')] = res?1:null;
			relatedProductList.checkedProduct(this);
		};
		with(req.responseJS){
			
			relatedProductList.load();
			var productID = getLayer('product-id').value;
			var cnt = 0;
			for(var k=0,k_max=products.length; k<k_max; k++){
			
				if(productID == products[k].productID)continue;
				cnt++;
				var _objDiv = createTag('div', objDiv,wnd);
				var objA = createTag('a', _objDiv,wnd);
				objA.className = 'relProduct';
				objA.innerHTML = products[k].name;
				objA.setAttribute('productID', products[k].productID);
				objA.setAttribute('productName', products[k].name);
				objA.onclick = chooseRelatedProduct;
				objA.wnd = wnd;
				
				relatedProductList.checkedProduct(objA);
			}
		}
		
		if(cnt>0){
			var objHidePrdsLink = wnd.document.createElement('input');
			objHidePrdsLink.type = 'button';
			objHidePrdsLink.style.fontSize = '70%';
			objHidePrdsLink.style.margin = '2px';
			objHidePrdsLink.onclick = function(){
				if(wnd.productsBubble && wnd.productsBubble.length){
					for(var _lp=wnd.productsBubble.length-1; _lp>=0; _lp--){
						if(wnd.productsBubble[_lp].parentNode)wnd.productsBubble[_lp].parentNode.removeChild(wnd.productsBubble[_lp]);
					}
					wnd.productsBubble = null;
				}
			};
			objHidePrdsLink.value = translate.prdset_btn_hide_products;
			objLi.innerHTML = '';
			objLi.style.display = 'inline';
			objLi.appendChild(objHidePrdsLink);
			objLi.parentNode.insertBefore(objDiv, objLi.nextSibling);
			wnd.productsBubble = [];
			wnd.productsBubble.push(objDiv);
			wnd.productsBubble.push(objHidePrdsLink);
			
			with(req.responseJS){
				if(req.responseJS.prev_offset || req.responseJS.next_offset){
				
					var objOffsetBlock = wnd.document.createElement('div');
					objOffsetBlock.style.marginLeft = '30px';
					if(req.responseJS.prev_offset){
						var objPrevOffset = wnd.document.createElement('input');
						objPrevOffset.type = 'button';
						objPrevOffset.style.fontSize = '70%';
						objPrevOffset.style.margin = '2px';
						objPrevOffset.setAttribute('offset',prev_offset);
						objPrevOffset.wnd = wnd;
						objPrevOffset.node = node;
						objPrevOffset.onclick = function(){
							loadProductList(this.node, this.wnd, this.getAttribute('offset'));
						};
						objPrevOffset.value = translate.prdset_btn_prev_products;
						objOffsetBlock.appendChild(objPrevOffset);
					}
					if(req.responseJS.next_offset){
						var objNextOffset = wnd.document.createElement('input');
						objNextOffset.type = 'button';
						objNextOffset.setAttribute('offset',next_offset);
						objNextOffset.style.fontSize = '70%';
						objNextOffset.style.margin = '2px';
						objNextOffset.wnd = wnd;
						objNextOffset.node = node;
						objNextOffset.onclick = function(){
							loadProductList(this.node, this.wnd, this.getAttribute('offset'));
						};
						objNextOffset.value = translate.prdset_btn_next_products;
						objOffsetBlock.appendChild(objNextOffset);
					}
					objLi.parentNode.insertBefore(objOffsetBlock, objDiv.nextSibling);
					wnd.productsBubble.push(objOffsetBlock);
				}
			}
		}
	};
  
	node.showLoadingMsg(translate.prdset_msg_loading_products);
	try {
		req.open(null, wnd.document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
		req.send({'action': 'getCategoryProducts', 'categoryID': node.getSetting('categoryID'), 'productID': productID, 'offset': offset});
	} catch ( e ) {
		catchResult(e);
	} finally {;}
}









function prdset_addRelatedPost(ArticleID, ArticleTitle){
	if(getLayer('related-posts-'+ArticleID)){
		var objBlock = getLayer('related-posts-'+ArticleID);
		objBlock.parentNode.removeChild(objBlock);
		return false;
	}
	
	var objCatBlock = document.createElement('div');
	objCatBlock.id = 'related-posts-'+ArticleID;
	var objSpan = createTag('span', objCatBlock);
	objSpan.innerHTML = ArticleTitle;
	var objInput = createTag('input', objCatBlock);
	objInput.name = 'ArticleArticles[]';
	objInput.value = ArticleID;
	objInput.style.display = "none";
	var objA = createTag('a', objCatBlock);
	objA.href = '#remove_relatedpost';
	objA.className = "remove_relatedpost_handler";
	objA.setAttribute('ArticleID', ArticleID);
	objA.onclick = function(){
		
		var catBlock = getLayer("related-posts-"+this.getAttribute('ArticleID'));
		catBlock.parentNode.removeChild(catBlock);
	};
	var objRemoveImg = createTag('img', objA);
	objRemoveImg.src = 'images/remove.gif';
	objRemoveImg.alt = translate.DELETE_BUTTON;
	objRemoveImg.border = 0;
	objRemoveImg.hspace = 6;
	
	getLayer("related-posts-container").appendChild(objCatBlock);
	return true;
}

var relatedPostsList = {
	'posts_ids': {},
	
	'load': function(){	
		var objInputs = getElementsByClass('',getLayer('related-posts-container'),'input');
		for(var j=objInputs.length-1; j>=0; j--){
		
			this.posts_ids[objInputs[j].value] = 1;
		}
	},
	'checkedPost': function(objProductLink){
		if(this.posts_ids[objProductLink.getAttribute('ArticleID')]){
		
			var p = objProductLink.parentNode;
			var wnd = objProductLink.wnd;
			var objChecked = wnd.document.createElement('img');
			objChecked.src = 'images_common/checked.gif';
			objChecked.hspace = 4;
			p.insertBefore(objChecked, objProductLink);
		}else{
		
			var p = objProductLink.parentNode;
			var objChecked = getElementsByClass('', p, 'img');
			if(objChecked.length){
				p.removeChild(objChecked[0]);
			}
		}
	}
};


function loadPostList(_node, _wnd, offset){
  var req = new JsHttpRequest();
  var node = _node;
  var wnd = _wnd;
  var ArticleID = getLayer('ArticleID').value;
			  
  req.onreadystatechange = function(){
		
	if (req.readyState != 4)return;
	
	node.hideLoadingMsg();
	if(req.responseText)alert(req.responseText);
	
	if(!req.responseJS.articles || !req.responseJS.articles.length)return;
		
	var objLi = getLayer(node.getID()+'_end', wnd);
	if(wnd.productsBubble && wnd.productsBubble.length){ 
		for(var _lp=wnd.productsBubble.length-1; _lp>=0; _lp--){
			if(wnd.productsBubble[_lp].parentNode)wnd.productsBubble[_lp].parentNode.removeChild(wnd.productsBubble[_lp]);
		}
		wnd.productsBubble = null;
	}
		var objDiv = wnd.document.createElement('div');
		objDiv.className = "productsBubble";
		
		chooseRelatedPost = function(){
			
			var res = prdset_addRelatedPost(this.getAttribute('ArticleID'),this.getAttribute('ArticleTitle'));
			relatedPostsList.posts_ids[this.getAttribute('ArticleID')] = res?1:null;
			relatedPostsList.checkedPost(this);
		};
		with(req.responseJS){
			
			
			relatedPostsList.load();
			var ArticleID = getLayer('ArticleID').value;
			var cnt = 0;
			for(var k=0,k_max=articles.length; k<k_max; k++){
			
				if(ArticleID == articles[k].ArticleID)continue;
				cnt++;
				var _objDiv = createTag('div', objDiv,wnd);
				var objA = createTag('a', _objDiv,wnd);
				objA.className = 'relArticles';
				objA.innerHTML = articles[k].ArticleTitle;
				objA.setAttribute('ArticleID', articles[k].ArticleID);
				objA.setAttribute('ArticleTitle', articles[k].ArticleTitle);
				objA.onclick = chooseRelatedPost;
				objA.wnd = wnd;
				
				relatedPostsList.checkedPost(objA);
			}
		}	
		if(cnt>0){
			var objHidePrdsLink = wnd.document.createElement('input');
			objHidePrdsLink.type = 'button';
			objHidePrdsLink.style.fontSize = '70%';
			objHidePrdsLink.style.margin = '2px';
			objHidePrdsLink.onclick = function(){
				if(wnd.productsBubble && wnd.productsBubble.length){
					for(var _lp=wnd.productsBubble.length-1; _lp>=0; _lp--){
						if(wnd.productsBubble[_lp].parentNode)wnd.productsBubble[_lp].parentNode.removeChild(wnd.productsBubble[_lp]);
					}
					wnd.productsBubble = null;
				}
			};
			objHidePrdsLink.value = translate.prdset_btn_hide_products;
			objLi.innerHTML = '';
			objLi.style.display = 'inline';
			objLi.appendChild(objHidePrdsLink);
			objLi.parentNode.insertBefore(objDiv, objLi.nextSibling);
			wnd.productsBubble = [];
			wnd.productsBubble.push(objDiv);
			wnd.productsBubble.push(objHidePrdsLink);
			
			with(req.responseJS){
				if(req.responseJS.prev_offset || req.responseJS.next_offset){
				
					var objOffsetBlock = wnd.document.createElement('div');
					objOffsetBlock.style.marginLeft = '30px';
					if(req.responseJS.prev_offset){
						var objPrevOffset = wnd.document.createElement('input');
						objPrevOffset.type = 'button';
						objPrevOffset.style.fontSize = '70%';
						objPrevOffset.style.margin = '2px';
						objPrevOffset.setAttribute('offset',prev_offset);
						objPrevOffset.wnd = wnd;
						objPrevOffset.node = node;
						objPrevOffset.onclick = function(){
							loadPostList(this.node, this.wnd, this.getAttribute('offset'));
						};
						objPrevOffset.value = translate.prdset_btn_prev_products;
						objOffsetBlock.appendChild(objPrevOffset);
					}
					if(req.responseJS.next_offset){
						var objNextOffset = wnd.document.createElement('input');
						objNextOffset.type = 'button';
						objNextOffset.setAttribute('offset',next_offset);
						objNextOffset.style.fontSize = '70%';
						objNextOffset.style.margin = '2px';
						objNextOffset.wnd = wnd;
						objNextOffset.node = node;
						objNextOffset.onclick = function(){
							loadPostList(this.node, this.wnd, this.getAttribute('offset'));
						};
						objNextOffset.value = translate.prdset_btn_next_products;
						objOffsetBlock.appendChild(objNextOffset);
					}
					objLi.parentNode.insertBefore(objOffsetBlock, objDiv.nextSibling);
					wnd.productsBubble.push(objOffsetBlock);
				}
			}
		}
	};
  
	node.showLoadingMsg(translate.prdset_msg_loading_products);
	try {
		req.open(null, wnd.document.location.href.replace(/\#.*$/, '')+"&caller=1&initscript=ajaxservice", true);
		req.send({'action': 'getCategoryArticles', 'categoryID': node.getSetting('categoryID'), 'ArticleID': ArticleID, 'offset': offset});
	} catch ( e ) {
		catchResult(e);
	} finally {;}
}




