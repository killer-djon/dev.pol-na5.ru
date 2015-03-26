Ext.ux.AsgmtMenu = function (config) {
	Ext.ux.AsgmtMenu.superclass.constructor.call(this, config);
  this.plain = true;
  var ci = new Ext.ux.AsgmtItem(config);
  this.add(ci);
  
  this.palette = ci.palette;
}

Ext.extend(Ext.ux.AsgmtMenu, Ext.menu.Menu, {
	update: function (usersCount, usersStr, recordId, canEdit) {
		this.palette.update(usersCount, usersStr, recordId, canEdit);
	}
});





Ext.ux.AsgmtItem = function(config){
    Ext.ux.AsgmtItem.superclass.constructor.call(this, new Ext.AsgmtPalette(config), config);
    
    this.palette = this.component;
    //this.relayEvents(this.palette, ["select"]);
    //if(this.selectHandler){
    //    this.on('select', this.selectHandler, this.scope);
};
Ext.extend(Ext.ux.AsgmtItem, Ext.menu.Adapter);







Ext.AsgmtPalette = function(config){
    Ext.AsgmtPalette.superclass.constructor.call(this, config);
};
Ext.extend(Ext.AsgmtPalette, Ext.Component, {
    value : null,
    ctype: "Ext.AsgmtPalette",
    
    onRender : function(container, position){
    	var el = document.createElement("div");
      el.className = "asgmt-tooltip";
      this.contentEl = el;
      container.dom.insertBefore(el, position);
      this.el = Ext.get(el);
    },
    	
    update: function (usersCount, usersStr, recordId, canEdit) {
    	var html = "<ul>";
      
      if (usersCount > 0)
      	html += usersStr;
      else
      	html += "<li>" + pmStrings.pm_noassignments_label + "</li>";
      html += "</ul>";
      
      if (canEdit)
      	html += "<a href='javascript:void(0)' onClick='document.worksGrid.openWorkWindow(" + recordId + ")'>" + pmStrings.pm_assignmentsedit_label + "</a>";

      this.contentEl.innerHTML = html;
    }
});