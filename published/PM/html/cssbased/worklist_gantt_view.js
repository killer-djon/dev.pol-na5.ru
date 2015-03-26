var GanttGridView = function(config){
  Ext.apply(this, config);
	GanttGridView.superclass.constructor.call(this);
};

Ext.extend(GanttGridView, Ext.grid.GridView, {
	renderHeaders : function(){
	  this.templates.hcell = new Ext.Template(
    '<td class="x-grid3-hd x-grid3-cell x-grid3-td-{id}" style="{style}"><div {attr} class="x-grid3-hd-inner x-grid3-hd-{id}" unselectable="on" style="{istyle}">', this.grid.enableHdMenu ? '<a class="x-grid3-hd-btn" href="#"></a>' : '',
    '<span id="x-grid3-td-value-{id}">{value}</span><img class="x-grid3-sort-icon" src="', Ext.BLANK_IMAGE_URL, '" />',
    "</div></td>"
    );
      
    var cm = this.cm, ts = this.templates;
    var ct = ts.hcell;

    var cb = [], sb = [], p = {};

    for(var i = 0, len = cm.getColumnCount(); i < len; i++){
        p.id = cm.getColumnId(i);
        p.value = cm.getColumnHeader(i) || "";
        p.style = this.getColumnStyle(i, true);
        if(cm.config[i].align == 'right'){
            p.istyle = 'padding-right:16px';
        }
        cb[cb.length] = ct.apply(p);
    }
    return ts.header.apply({cells: cb.join(""), tstyle:'width:'+this.getTotalWidth()+';'});
	}
});