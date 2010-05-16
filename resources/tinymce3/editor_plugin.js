(function() {
     tinymce.create('tinymce.plugins.DeckboxPlugin', {
	 /**
	  * Initializes the plugin, this will be executed after the plugin has been created.
	  * This call is done before the editor instance has finished it's initialization so use the onInit event
	  * of the editor instance to intercept that event.
	  *
	  * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
	  * @param {string} url Absolute URL to where the plugin is located.
	  */
         init : function(ed, url) {
	     ed.addButton('deckbox', {
		 title : 'Magic the Gathering card',
	         image : url + '/../../images/mtg.png',
	         onclick : function() {
                     var txt = ed.selection.getContent();
                     ed.selection.setContent('<a class="deckbox_link" href="http://deckbox.org/mtg/' + txt + '">' + txt + '</a>');
		 }
             });
	 },

         /**
          * Creates control instances based in the incomming name. This method is normally not
          * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
          * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
          * method can be used to create those.
          *
          * @param {String} n Name of the control to create.
          * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
          * @return {tinymce.ui.Control} New control instance or null if no control was created.
          */
         createControl : function(n, cm) {
	     return null;
         },

         /**
          * Returns information about the plugin as a name/value array.
          * The current keys are longname, author, authorurl, infourl and version.
          *
          * @return {Object} Name/value array containing information about the plugin.
          */
         getInfo : function() {
	     return {
	         longname : "Deckbox Mtg card tooltips",
	         author : 'Sebastian Zaha',
	         authorurl : 'http://deckbox.org',
	         infourl : 'http://deckbox.org/help/tooltips',
	         version : "1.0.1"
	     };
         }
    });

    // Register plugin
    tinymce.PluginManager.add('deckbox', tinymce.plugins.DeckboxPlugin);
})();
