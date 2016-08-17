(function() {
    tinymce.create('tinymce.plugins.JsQR', {
        init : function(ed, url) {
 
            ed.addButton('jsqrcode_button', {
                title : 'Add QR code shortcode',
                onclick : function() {
						tb_show( 'Javascript QRcode Shortcode', '#TB_inline?width=' + 500 + '&height=' + 400 + '&inlineId=jsqrcode-form' );
					},
                image : url + '/qrcode.png'
            });
        },
        // ... Hidden code
    });
    // Register plugin
    tinymce.PluginManager.add( 'jsqr', tinymce.plugins.JsQR );
	
	jQuery(function(){
		// creates a form to be displayed everytime the button is clicked
		// you should achieve this using AJAX instead of direct html code like this
		var form = jQuery('<div id="jsqrcode-form">\
		<table width="100%" border="0" cellspacing="2" cellpadding="2">\
  <tr>\
    <td>\
	<label for="jsqrcode-msg">Enter QR Text</label><br>\
	<textarea id="jsqrcode-msg" name="msg" cols="50" rows="5"></textarea></td>\
  </tr>\
  <tr>\
    <td><label for="jsqrcode-size">Size</label><input type="text" size="10" id="jsqrcode-size" name="size" value="150">px<br>\
	<label for="jsqrcode-ecc">ECC</label><select id="jsqrcode-ecc" name="ecc">\
	<option value="L">L</option>\
	<option value="M">M</option>\
	<option value="H" selected="selected">H</option>\
	<option value="Q">Q</option>\
	</select>\
	</td>\
  </tr>\
  <tr>\
    <td><input type="button" id="jsqrcode-submit" name="submit" value="Insert QR Code"></td>\
  </tr>\
</table>\
</div>');
		
		var table = form.find('table');
		form.appendTo('body').hide();
		
		// handles the click event of the submit button
		form.find('#jsqrcode-submit').click(function(){
			// defines the options and their default values
			// again, this is not the most elegant way to do this
			// but well, this gets the job done nonetheless
			var options = { 
				'msg' : '',
				'size': '150',
				'ecc' : 'H'
				};
			var shortcode = '[jsqr';
			
			for( var index in options) {
				var value = table.find('#jsqrcode-' + index).val();
				
				// attaches the attribute to the shortcode only if it's different from the default value
				if ( value !== options[index] )
					shortcode += ' ' + index + '="' + value + '"';
			}
			
			shortcode += '/]';
			
			// inserts the shortcode into the active editor
			tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
			
			// closes Thickbox
			tb_remove();
		});
	});
})();