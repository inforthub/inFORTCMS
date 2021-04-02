loading = true;

Application = {};
Application.translation = {
    'en' : {
        'loading' : 'Loading'
    },
    'pt' : {
        'loading' : 'Carregando'
    },
    'es' : {
        'loading' : 'Cargando'
    }
};

Adianti.onClearDOM = function(){
	/* $(".select2-hidden-accessible").remove(); */
	$(".colorpicker-hidden").remove();
	$(".select2-display-none").remove();
	$(".tooltip.fade").remove();
	$(".select2-drop-mask").remove();
	/* $(".autocomplete-suggestions").remove(); */
	$(".datetimepicker").remove();
	$(".note-popover").remove();
	$(".dtp").remove();
	$("#window-resizer-tooltip").remove();
};


function showLoading() 
{ 
    if(loading)
    {
        __adianti_block_ui(Application.translation[Adianti.language]['loading']);
    }
}

Adianti.onBeforeLoad = function(url) 
{ 
    loading = true; 
    setTimeout(function(){showLoading()}, 400);
    if (url.indexOf('&static=1') == -1) {
        $("html, body").animate({ scrollTop: 0 }, "fast");
    }
};

Adianti.onAfterLoad = function(url, data)
{ 
    loading = false; 
    __adianti_unblock_ui( true );
    
    // Fill page tab title with breadcrumb
    // window.document.title  = $('#div_breadcrumbs').text();
};

// set select2 language
$.fn.select2.defaults.set('language', $.fn.select2.amd.require("select2/i18n/pt"));

/*********************************/
/*** inFORT-CMS - Modificações ***/
/*********************************/

function tfieldlist_reset_fields(row, clear_fields)
{
    var uniqid = parseInt(Math.random() * 100000000);
    $(row).attr('id', uniqid);
    var fields = $(row).find('input,select,div');
    var newids = [];
    
    $.each(fields, function(index, field)
    {
        var field_id = $(field).attr('id');
        var field_component = $(field).attr('widget');
        var field_role = $(field).attr('role');
        
        if (typeof field_id !== "undefined")
        {
            var field_id_parts = field_id.split('_');
            field_id_parts.pop();
            var field_prefix = field_id_parts.join('_');
            var new_id = field_prefix + '_' + uniqid;
            var parent = $(field).parent();
            
            if (newids.indexOf(new_id) >= 0 )
            {
                var new_id = field_prefix + parseInt(Math.random() * 100) + '_' + uniqid;
            }
            newids.push(new_id);
            
            if (field_component == 'tdate' || field_component == 'ttime' || field_component == 'tdatetime')
            {
                // realocate in dom
                $(field).attr('id', new_id);
                if (clear_fields) {
                    $(field).val('');
                }
                
                if (typeof $(field).attr('exitaction') !== 'undefined') {
                    $(field).attr('exitaction', $(field).attr('exitaction').replace(field_id, new_id));
                }
                if (typeof $(field).attr('onblur') !== 'undefined') {
                    $(field).attr('onblur', $(field).attr('onblur').replace(field_id, new_id));
                }
                
                grandparent = $(parent).parent();
                field = $(field).detach()
                $(parent).remove();
                grandparent.append(field);
                
                var script_filter = field_component;
                if (field_component == 'ttime') {
                  var script_filter = 'tdatetime';
                }
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(grandparent, script_filter, function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='tentry')
            {
                $(field).attr('id', new_id);
                if (clear_fields) {
                    $(field).val('');
                }
                
                if (typeof $(field).attr('exitaction') !== 'undefined') {
                    $(field).attr('exitaction', $(field).attr('exitaction').replace(field_id, new_id));
                }
                if (typeof $(field).attr('onblur') !== 'undefined') {
                    $(field).attr('onblur', $(field).attr('onblur').replace(field_id, new_id));
                }
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(parent, 'tentry', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='tspinner')
            {
                $(field).attr('id', new_id);
                if (clear_fields) {
                    $(field).val('');
                }
                
                if (typeof $(field).attr('exitaction') !== 'undefined') {
                    $(field).attr('exitaction', $(field).attr('exitaction').replace(field_id, new_id));
                }
                if (typeof $(field).attr('onblur') !== 'undefined') {
                    $(field).attr('onblur', $(field).attr('onblur').replace(field_id, new_id));
                }
                
                grandparent = $(parent).parent();
                field = $(field).detach()
                $(parent).remove();
                grandparent.append(field);
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(grandparent, 'tspinner', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='tcolor')
            {
                // realocate in dom
                $(field).attr('id', new_id);
                if (clear_fields) {
                    $(field).val('');
                }
                
                if (typeof $(field).attr('exitaction') !== 'undefined') {
                    $(field).attr('exitaction', $(field).attr('exitaction').replace(field_id, new_id));
                }
                if (typeof $(field).attr('onblur') !== 'undefined') {
                    $(field).attr('onblur', $(field).attr('onblur').replace(field_id, new_id));
                }
                
                grandparent = $(parent).parent();
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(grandparent, 'tcolor', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='thidden')
            {
                $(field).attr('id', new_id);
                if ($(field).attr('uniqid') == 'true') {
                    $(field).val(parseInt(Math.random() * 10000000000));
                }
                else if (clear_fields) {
                    $(field).val('');
                }
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(parent, 'thidden', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='tpicture')
            {
                $(field).attr('id', new_id);
                if ($(field).attr('uniqid') == 'true') {
                    $(field).val(parseInt(Math.random() * 10000000000));
                }
                else if (clear_fields) {
                    $(field).html('');
                }
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(parent, 'tpicture', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='tdbmultisearch' || field_component =='tdbuniquesearch')
            {
                $(field).attr('id', new_id);
                $(field).removeData('select2-id').removeAttr('data-select2-id').find('option').removeAttr('data-select2-id');
                $(row).removeData('select2-id').removeAttr('data-select2-id');
                
                // remove select2 container previously processed
                $(parent).find('.select2-container').remove();
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(parent, 'tdbmultisearch', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
                
                if (clear_fields) {
                    setTimeout(function() { $(field).val('').trigger('change.select2'); }, 10 );
                }
            }
            else if (field_component =='tmultisearch' || field_component =='tuniquesearch')
            {
                $(field).attr('id', new_id);
                $(field).removeData('select2-id').removeAttr('data-select2-id').find('option').removeAttr('data-select2-id');
                $(row).removeData('select2-id').removeAttr('data-select2-id');
                
                $(parent).find('.select2-container').remove();
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(parent, 'tmultisearch', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
                
                if (clear_fields) {
                    setTimeout(function() { $(field).val('').trigger('change.select2'); }, 10 );
                }
            }
            else if (field_component =='tcombo')
            {
                $(field).attr('id', new_id);
                
                if (clear_fields) {
                    $(field).val('');
                }
                if (field_role == 'tcombosearch') {
                    // clear data attributes
                    $(field).removeData('select2-id').removeAttr('data-select2-id').find('option').removeAttr('data-select2-id');
                    $(row).removeData('select2-id').removeAttr('data-select2-id');
                    $(parent).find('.select2-container').remove();
                }
                
                if (typeof $(field).attr('changeaction') !== 'undefined') {
                    $(field).attr('changeaction', $(field).attr('changeaction').replace(field_id, new_id));
                }
                if (typeof $(field).attr('onchange') !== 'undefined') {
                    $(field).attr('onchange', $(field).attr('onchange').replace(field_id, new_id));
                }
                
                var re = new RegExp(field_id, 'g');
                tfieldlist_execute_scripts(parent, 'tcombo', function(script_content) {
                    script_content = script_content.replace(re, new_id);
                    return script_content;
                });
            }
            else if (field_component =='tfile')
            {
                $(field).attr('id', new_id);
                
                if (clear_fields) {
                    $(field).val('');
                }
                
                var parent = $(field).closest('td');
                var file_wrapper_id = parent.find('.div_file').attr('id');
                var new_file_wrapper_id = 'div_file_' + uniqid;
                
                parent.find('.tfile_row_wrapper').remove();
                parent.find('.file-response-icon').remove();
                parent.find('.div_file').attr('id', new_file_wrapper_id);
                $(field).css('padding-left', '5px');
                
                if (typeof $(field).attr('changeaction') !== 'undefined') {
                    $(field).attr('changeaction', $(field).attr('changeaction').replace(field_id, new_id));
                }
                if (typeof $(field).attr('onchange') !== 'undefined') {
                    $(field).attr('onchange', $(field).attr('onchange').replace(field_id, new_id));
                }
                
                var re  = new RegExp(field_id, 'g');
                var re2 = new RegExp(file_wrapper_id, 'g');
                tfieldlist_execute_scripts(parent, 'tfile', function(script_content) {
                    script_content = script_content.replace(re,  new_id);
                    script_content = script_content.replace(re2, new_file_wrapper_id);
                    return script_content;
                });
            }
        }
        
        // fora do if pois não troca ID (não possui ID), só reinicia value
        if (field_component =='tradiobutton')
        {
            $(field).parent().removeClass('active');
        }
        else if (field_component =='tcheckbutton')
        {
            $(field).parent().removeClass('active');
        }
    });
}
