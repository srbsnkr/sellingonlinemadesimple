//My JS
var arr_name = new Array("shop", "blog", "testimonials");
var i = 0;
jQuery("#rt-content-bottom").children().each(function(){	
	jQuery(this).children().addClass(arr_name[i++]);
});

function build_list(id, custom_id, frefix){
	var str = '<ul id="'+ custom_id +'">';
	
	var i = 1;	
	jQuery('#'+ id +' option').each(function(){		
		var text = jQuery(this).text();
		var value = jQuery(this).val();
		str += '<li class="custom-fields-item"><a href="#" data="'+ value +'" class="'+ value.toLowerCase() +'" id="'+ frefix + i +'">'+ text + '</a></li>';
		i +=1;
	});	
	str += '</ul>';
	jQuery('#'+ id).parent().next().html(str);
}
var id_list_color = jQuery("select[id^='customPlugin']").attr("id");
var id_list_size = jQuery("select[id^='customPrice']").attr("id");
build_list(id_list_size, 'avaible-size', 'size_');
build_list(id_list_color, 'color-picker', 'color_');


function set_value(id, id_select, obj){
	jQuery("#" + id + " .active").removeClass("active");
	var color_value = jQuery(obj).attr("data");
	jQuery(obj).parent().addClass("active");
	jQuery("#"+ id_select).val(color_value);				
}

jQuery("a[id^=color_]").live('click', function(){	
	var image = jQuery(this).attr("id").replace("color_","");	
	var name = jQuery(".product-field-display-hidden").eq(image-1).text();
	var url_host = location.href;
	url_host = url_host.substring(0,url_host.search("index.php"));
	jQuery("img#medium-image").attr("src",url_host + "/images/stories/virtuemart/product/" + name);
	set_value("color-picker", id_list_color, this);	
	return false;
});

jQuery("a[id^=size_]").live('click', function(){
	set_value("avaible-size", id_list_size, this);
	return false;
});

jQuery("select#virtuemart_currency_id").change(function(){
	jQuery("input[name='submit_currency']").click();
});

jQuery("ul#color-picker li:first").addClass("active");
jQuery("ul#avaible-size li:first").addClass("active");

//edit module login in top menu
if(jQuery("#login-form div:first").hasClass("login-greeting")){
	var text = jQuery("#login-form .login-greeting").text();
	jQuery(".item551 a span.titreck").text(text);
	jQuery(".item552 a span.titreck").html("");
}
//--end edit