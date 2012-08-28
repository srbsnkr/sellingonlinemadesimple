//My JS
var arr_name = new Array("shop", "blog", "testimonials");
var i = 0;
jQuery("#rt-content-bottom").children().each(function(){	
	jQuery(this).children().addClass(arr_name[i++]);
})

function build_list(id, custom_id, frefix){
	var str = '<ul id="'+ custom_id +'">';
	
	var i = 1;	
	jQuery('#'+ id +' option').each(function(){		
		var text = jQuery(this).text();
		var value = jQuery(this).val();
		str += '<li class="custom-fields-item"><a href="#" data="'+ value +'" class="'+ value +'" id="'+ frefix + i +'">'+ text + '</a></li>';
		i +=1;
	});	
	str += '</ul>';
	jQuery('#'+ id).parent().next().html(str);
}
build_list('customPrice16', 'avaible-size', 'size_');
build_list('customPlugin37dropcustom_drop', 'color-picker', 'color_');


function set_value(id, id_select, obj){
	jQuery("#" + id + " .active").removeClass("active");
	var color_value = jQuery(obj).attr("data");
	jQuery(obj).parent().addClass("active");
	jQuery("#"+ id_select).val(color_value);				
}

jQuery("a[id^=color_]").live('click', function(){
	set_value("color-picker", "customPlugin37dropcustom_drop", this);	
	return false;
});

jQuery("a[id^=size_]").live('click', function(){
	set_value("avaible-size", "customPrice16", this);
	return false;
});

jQuery("select#virtuemart_currency_id").change(function(){
	jQuery("input[name='submit_currency']").click();
});