/**
 * Copyright (C) 2011  freakedout (www.freakedout.de)
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
**/

function AJAXpreview( obj, intro, sidebar ){
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=create&format=raw&task=preview';
	
	var data = new Object();
	data["campaignName"]		= encodeURI( document.getElementById("campaign_name").value );
	data["subject"]				= encodeURI( document.getElementById("subject").value );
	data["from_name"]			= encodeURI( document.getElementById("from_name").value );
	data["from_email"]			= encodeURI( document.getElementById("from_email").value );
	data["reply_email"]			= encodeURI( document.getElementById("reply_email").value );
	data["confirmation_email"]	= encodeURI( document.getElementById("confirmation_email").value );
	data["template"]			= encodeURI( document.getElementById("template").value );
	
	data["intro"] = encodeURI( intro );
	data["sidebar"] = encodeURI( sidebar );
	
	if(obj.article){
		data["article"] = new Array();
		var x = 0;
		for (i=0; i<obj.article.length; i++){
			if (obj.article[i].checked==true){
				data["article"][x] = obj.article[i].value;
				var fullCB = $("article_full_" +obj.article[i].value);
				
				if ( fullCB.checked==true ) { 
					var full = 0;
				} else {
					var full = 1;
				}
				data["article_full_" +obj.article[i].value] = full;
				
				var readmoreCB = $("readmore_" +obj.article[i].value);
				if ( readmoreCB.checked==true ) { 
					var readmore = 1;
				} else {
					var readmore = 0;
				}
				data["readmore_" +obj.article[i].value] = readmore; 
				
				x++;
			}
		}
	}
	
	if ( document.getElementById("k2_installed").value == 1 && obj.k2article ) {
		data["k2_installed"] = 1;
		
		data["k2article"] = new Array();
		var x = 0;
		for (i=0; i<obj.k2article.length; i++){
			if (obj.k2article[i].checked==true){
				data["k2article"][x] = obj.k2article[i].value;
				
				var k2fullCB = $("k2article_full_" +obj.k2article[i].value);
				if ( k2fullCB.checked==true) { 
					var k2full = 0;
				} else {
					var k2full = 1;
				}
				data["k2article_full_" +obj.k2article[i].value] = k2full;
				
				var k2readmoreCB = $("k2readmore_" +obj.k2article[i].value);
				if ( k2readmoreCB.checked==true) { 
					var k2readmore = 1;
				} else {
					var k2readmore = 0;
				}
				data["k2readmore_" +obj.k2article[i].value] = k2readmore;
				
				x++;
			}
		}
	} else {
		data["k2_installed"] = 0;
	}
	
	if ( document.getElementById("jomsocial_installed").value == 1 && obj.jsProfiles ) {
		data["jomsocial_installed"] = 1;
		
		data["jsProfiles"] = new Array();
		var x = 0;
		for (i=0; i<obj.jsProfiles.length; i++){
			if (obj.jsProfiles[i].checked==true){
				data["jsProfiles"][x] = obj.jsProfiles[i].value;
				x++;
			}
		}
		var jsFields = new Array();
		var ob = document.getElementById("jsProfileFields");
		for (var i = 0; i < ob.options.length; i++) {
			if (ob.options[ i ].selected) {
				jsFields.push(ob.options[ i ].value);
			}
		}
		data["jsProfileFields"] = jsFields;

		if( obj.jsdiscussions.value == 1 ){
		    data["jsdisc"] = new Array();
		    var x = 0;
		    for (i=0; i<obj.jsdisc.length; i++){
			if (obj.jsdisc[i].checked==true){
			    data["jsdisc"][x] = obj.jsdisc[i].value;
			    x++;
			}
		    }
		} else {
		   data["jsdisc"] = 0;
		}
		
	} else {
		data["jomsocial_installed"] = 0;
	}
	
	if ( document.getElementById("aec_installed").value == 1 && obj.aec ) {
		data["aec_installed"] = 1;	
		data["aec"] = new Array();
		var x = 0;
		for (i=0; i<obj.aec.length; i++){
			if (obj.aec[i].checked==true){
				data["aec"][x] = obj.aec[i].value;
				x++;
			}
		}
	} else {
		data["aec_installed"] = 0;
	}
	
	if ( document.getElementById("ambra_installed").value == 1 && obj.ambra ) {
		data["ambra_installed"] = 1;
		
		data["ambra"] = new Array();
		var x = 0;
		for (i=0; i<obj.ambra.length; i++){
			if (obj.ambra[i].checked==true){
				data["ambra"][x] = obj.ambra[i].value;
				x++;
			}
		}
	} else {
		data["ambra_installed"] = 0;
	}
	
	if ( document.getElementById("vm_installed").value == 1 ) {
		if (document.getElementById("vm_sidebar").checked==true){
			data["vm_sb"] = 1;
			
			data["vm_sb_products"] = new Array();
			var x = 0;
			for (i=0; i<obj.vm_sb_products.length; i++){
				if (obj.vm_sb_products[i].checked==true){
					data["vm_sb_products"][x] = obj.vm_sb_products[i].value;
					x++;
				}
			}
			data["vm_sb_order"] = document.getElementById("vm_sidebar_order").value;
			if (document.getElementById("vm_sidebar_price").checked==true){
			data["vm_sb_pr"] = 1;
			}
			if (document.getElementById("vm_sidebar_curr_first").checked==true){
			data["vm_sb_cf"] = 1;
			}
			if (document.getElementById("vm_sidebar_img").checked==true){
			data["vm_sb_img"] = 1;
			}
			if (document.getElementById("vm_sidebar_link").checked==true){
			data["vm_sb_link"] = 1;
			}
			if (document.getElementById("vm_short_desc").checked==true){
			data["vm_short_desc"] = 1;
			}
			if (document.getElementById("vm_desc").checked==true){
			data["vm_desc"] = 1;
			}
		} else {
			data["vm_sb"] = 0;
		}
	}
	
	if (document.getElementById("tableofcontents").checked==true){
		data["toc"] = 1;
	if (document.getElementById("tableofcontents_type").checked==true){
		data["toc_type"] = 1;
	} else {
		data["toc_type"] = 0;
	}
	} else {
		data["toc"] = 0;
		data["toc_type"] = 0;
	}
	
	if (document.getElementById("populararticles").checked==true){
		data["popular"] = 1;
		//exclude
		selected = new Array();
		var ob = document.getElementById("popExclude");
		for (var i = 0; i < ob.options.length; i++) {
			if (ob.options[ i ].selected) {
				selected.push(ob.options[ i ].value);
			}
		}
		data["popEx"] = selected;
		//include
		selected = new Array();
		var ob = document.getElementById("popInclude");
		for (var i = 0; i < ob.options.length; i++) {
			if (ob.options[ i ].selected) {
				selected.push(ob.options[ i ].value);
			}
		}
		data["popIn"] = selected;
		
	} else {
		data["popular"] = 0;
		data["popEx"]   = 'false';
		data["popIn"]   = 'false';
	}
	
	if ( document.getElementById("k2_installed").value == 1 ) {
	
		if (document.getElementById("populark2").checked==true){
			data["populark2"] = 1;
			//exclude
			selected = new Array();
			var ob = document.getElementById("popk2Exclude");
			for (var i = 0; i < ob.options.length; i++) {
				if (ob.options[ i ].selected) {
					selected.push(ob.options[ i ].value);
				}
			}
			data["popk2Ex"] = selected;
			//include
			selected = new Array();
			var ob = document.getElementById("popk2Include");
			for (var i = 0; i < ob.options.length; i++) {
				if (ob.options[ i ].selected) {
					selected.push(ob.options[ i ].value);
				}
			}
			data["popk2In"] = selected;
		} else {
			data["populark2"] = 0;
			data["popk2Ex"]   = 'false';
			data["popk2In"]   = 'false';
		}
		
		if (document.getElementById("populark2_only").checked==true){
			data["populark2_only"] = 1;
		} else {
			data["populark2_only"] = 0;
		}
	}
	
	if ( document.getElementById("twitter").value != '' ) {
		data["twitter"] = document.getElementById("twitter").value;
	} else {
		data["twitter"] = 0;
	}
	if ( document.getElementById("facebook").value != '' ) {
		data["facebook"] = document.getElementById("facebook").value;
	} else {
		data["facebook"] = 0;
	}
	if ( document.getElementById("myspace").value != '' ) {
		data["myspace"] = document.getElementById("myspace").value;
	} else {
		data["myspace"] = 0;
	}
	
	if ( document.getElementById("gaEnabled").checked==true ) {
		data["gaEnabled"] = 1;
		data["gaSource"] = document.getElementById("gaSource").value;
		data["gaMedium"] = document.getElementById("gaMedium").value;
		data["gaName"] = document.getElementById("gaName").value;
		data["gaExcluded"] = encodeURI( document.getElementById("gaExcluded").value );
		
	} else {
		data["gaEnabled"] = 0;
	}
		
	doAjaxTask(url, data, function(postback){ if(postback.msg){
			    document.getElementById('preview').innerHTML = postback.msg + postback.html;
		      } else {
			    document.getElementById('preview').innerHTML = postback.html;
		      }
		      if(postback.js){
			      eval(postback.js);
		      }
		      document.getElementById('ajax-spin').style.background = '';
		      document.getElementById('preview').style.opacity = '';
		});
}
