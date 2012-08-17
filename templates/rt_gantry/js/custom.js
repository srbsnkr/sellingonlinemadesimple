//My JS
var arr_name = new Array("shop", "blog", "testimonials");
var i = 0;
jQuery("#rt-content-bottom").children().each(function(){	
	jQuery(this).children().addClass(arr_name[i++]);
})