//My JS
var arr_name = new Array("shop", "blog", "testimonials");
var i = 0;
jQuery("#rt-content-bottom").children().each(function(){	
	jQuery(this).children().addClass(arr_name[i++]);
})

var str = '<ul id="color-picker">';
var i = 1;
jQuery("#customPlugin37dropcustom_drop option").each(function(){
	var temp = jQuery(this).text();
	str += '<li class="custom-fields-item"><a href="#" id="color_'+ i +'">'+ temp + '</a></li>';
	i +=1;
});	
str += '</ul>';
jQuery("#customPlugin37dropcustom_drop").parent().next().html(str);

var str = '<ul id="avaible-size">';
var i = 1;
jQuery("#customPrice16 option").each(function(){
	var temp = jQuery(this).text();
	str += '<li class="custom-fields-item"><a href="#" id="size_'+ i +'">'+ temp + '</a></li>';
	i +=1;
});	
str += '</ul>';
jQuery("#customPrice16").parent().next().html(str);