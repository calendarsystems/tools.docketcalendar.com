(function($) {
	jQuery.fn.inputmask = function($$options) {
		var $settings = $.extend( {}, $.fn.inputmask.defaults, $$options);
		return this.each(function() {
			var $this = $(this);
			var o = $.metadata ? $.extend( {}, $settings, $this.metadata()) : $settings;
			if($.inArray('digits', o.validators) != -1)
				$this.keyup(function(e){$this.val(stripAlphaChars($this.val()));});
			var stripAlphaChars = function(string) {
				var str = new String(string);
				str = str.replace(/[^0-9]/g, ''); 
				return str;
			}
		});
	};
	jQuery.fn.inputmask.defaults = {validators:[]};
})(jQuery);

(function($) {
    $.extend({ 
        wpjspButtons: function() {
            // Setup Color Picker
            $('.color-picker').ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    $(el).val(hex);
                    $(el).ColorPickerHide();
                    $(el).trigger('change');
                },onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                }
            }).bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
            });
            // Validate size (number) inputs
            $('.numbers').inputmask({
                validators: ['digits']
            });
            
            // Attach the delete button
            $('input[id^="delete"]').click(function() {
                $(this).parents('table[id*="scrollpane"]').remove();
                $.wpjspRename();
            });
            // Handle increment checkbox
            $('input[id^="increment"]').change(function() {
                var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                if( $(this).is(':checked') ) {
                    $('select#incrementlocation'+id).removeAttr('disabled');
                } else if( $(this).not(':checked') ) {
                    $('select#incrementlocation'+id).attr('disabled', true);
                }
            });
            // Wholepage Checkbox
            $('input[id^="fullpage"]').change(function() {
                var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                if( $(this).is(':checked') ) {
                    $('input#selector'+id+',input#element'+id+',select#selectortype'+id+',input#increment'+id+',input#incrementlocation'+id).attr('disabled', true);
                } else if( $(this).not(':checked') ) {
                    $('input#selector'+id+',input#element'+id+',select#selectortype'+id+',input#increment'+id).removeAttr('disabled');
                    if( $('input#increment'+id).is(':checked') )
                        $('input#incrementlocation'+id).removeAttr('disabled');
                }
            });
            // Handle arrows checkbox
            $('input[id^="arrows"]').change(function() {
                var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                if( $(this).is(':checked') ) {
                    $('select#arrowsvert'+id+',select#arrowshoriz'+id).removeAttr('disabled');
                    if( $('input[name="_wpjsp['+id+'][theme]"]:checked').val()=='customcolors' ) {
                        $('input#arrowcolor'+id).removeAttr('disabled');
                        if( $('input#arrowcolor'+id).val()!='' ) $('input#arrowhover'+id).removeAttr('disabled');
                    }
                } else if( $(this).not(':checked') )
                    $('select#arrowsvert'+id+',select#arrowshoriz'+id+',input#arrowcolor'+id+',input#arrowhover'+id).attr('disabled', true);
            });
            // Handle Theme Disables
            $.each(wpjspthemes,function(item,value) {
                value = value.toLowerCase();
                $('input[id^="'+value+'"]').change(function() {
                    if( $(this).is(':checked') ) {
                        if( value = $(this).attr('id') ) {
                            var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                            $('input[id$="'+id+'"].color-picker,input[id$="'+id+'"].numbers').not('input[id^="gutter"]').attr('disabled', true);
                        }
                    }
                });
            });
            $('input[id^="colors"]').change(function() {
                if( $(this).is(':checked') ) {
                    var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                    $('input[id$="'+id+'"].color-picker,input[id$="'+id+'"].numbers').removeAttr('disabled');
                    $(this).parents('table#scrollpane'+id).find('*[id$="'+id+'"]').not(this).each(function() {
                        $(this).trigger('change');
                    });
                }
            });
            $('input[id*="color"]').change(function() {
                var name = String($(this).attr('id').replace(/color[0-9]*/g,''));
                var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                if( $(this).val()=='' ) $('input#'+name+'hover'+id).attr('disabled',true);
                else if( $(this).val()!='' ) $('input#'+name+'hover'+id).removeAttr('disabled');
            });
            $('input[id^="capcolor"]').change(function() {
                var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                if( $(this).val()=='' ) $('input#caphoriz'+id+',input#capvert'+id).attr('disabled', true);
                else if( $(this).val()!='' ) $('input#caphoriz'+id+',input#capvert'+id).removeAttr('disabled');
            });
        },
        wpjspRename: function() {
            // Clever renamer...  :-D
            $('table[id^="scrollpane"]').each(function(i) {
                var id = parseFloat($(this).attr('id').replace(/[A-Za-z]*/g,''),10);
                $(this).find('*[id$="'+id+'"]').each(function() {
                    var id = $(this).attr('id');
                    var name = $(this).attr('name');
                    $(this).attr('id',String(id.replace(/[0-9]/g,''))+i);
                    if('undefined' !== typeof name)
                        $(this).attr('name','_wpjsp['+i+']'+String(name.replace(/^_[a-z]*\[[0-9]*\]/g, '')))
                });
                $(this).attr('id','scrollpane'+i);
                $(this).find('th.label').text('ScrollPane '+(i+1));
            });
        }
    });
})(jQuery);

var wpjspthemes;
jQuery(document).ready(function($) {
    $.ajaxSetup({async:false});
    // Set $.wpjspthemes as an array of theme folder names for later use
    var data = {action:'getthemes',wpjspclient:'true'};
    $.post(ajaxurl, data, function(response){wpjspthemes = response.split('|');});
    // AJAX Request for dynamic scrollpane adding
	$('#wpjsp-add').click(function() {
        //alert(wpjspthemes);
        var cnt = $('table[id^="scrollpane"]').length;
        var data = {action:'gethtml',wpjspclient:'true',wpjspincr:cnt};
        $.post(ajaxurl, data, function(response) {
            $('.wpjsp-scrollbars').append(response);
        });
        $.wpjspButtons();
        return false;
	});
    $.wpjspButtons();
});