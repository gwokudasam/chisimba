
    // basic tabs 1, built from existing content
    var tabs = new Ext.TabPanel({
        el: 'contextbrowser',
        width:700,
		activeTab: 0,
        frame:true,
        defaults:{autoHeight: true},

        items:[	
			{items:[usergrid], title: 'My Courses'},
            {items:[myBorderPanel], title: 'Other Courses'},
            {items:[grid], title: 'Search Courses'}
			]
    });


Ext.onReady(function(){
	tabs.render();
	contextdata.load({params:{start:0, limit:10}});
    usercontextdata.load({params:{start:0, limit:15}});
	othercontextdata.load({params:{start:0, limit:10}});
	
});
