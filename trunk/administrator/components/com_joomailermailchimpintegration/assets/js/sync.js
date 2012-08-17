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

function AjaxAdd( y, done, finished, addedUsers, errors, errorMsg, failed ){
	
	$('ajax_response').style.display = 'none';
	jQuery('#ajax_response').html('');
	
	if( !y )		{ y          = 0; }
	if( !done )		{ done       = 0; }
	if( !finished  ){ finished   = 0; }
	if( !addedUsers){ addedUsers = ''; }
	if( !errors    ){ errors     = 0; }
	if( !errorMsg  ){ errorMsg   = false; }
	if( !failed)	{ failed = ''; }
	
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=ajax_sync';
	var form = document.getElementById('adminForm');
	var step = 10;
//	console.log(data);

	var data = new Object();
	data["listid"] = document.getElementById('listid').value;
	data["done"]   = done;
	data["addedUsers"] = addedUsers;
	data["errors"] = errors;
	data["errorMsg"] = errorMsg;
	
	data["cid"] = new Array();
	x = 0;
	y += step;
	z = 0;
	for(i=0; i<form.elements.length; i++)
	{
		if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
			if( x>=(y-step) && x<y ){
				
				data["cid"][z] = form.elements[i].value;
				z++;
			}
			x++;
		}
	}
	data["total"]  = x;
	
	if (document.adminForm.listid.value == ""){
		noListSelected();
	} else if( x==0 ) {
		noUsersSelected();
	} else {
		if( y == step ) {	AJAXinit( document.getElementById('boxchecked').value ); }
		doAjaxTask(url, data, function(postback){ jQuery('#ajax_response').html(postback.msg);
		
												  if( !postback.finished){
													  AjaxAdd( y, postback.done, postback.finished, postback.addedUsers, postback.errors, postback.errorMsg, postback.failed); 
												  } else { 
													  AJAXsuccess(postback.finalMessage);
													  setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000);
													  markAdded(postback.addedUsers);
												  }
												  
												  });
	}
}

//function AjaxAddAll( offset, done, finished, errors, errorMsg, addedUsers, failed ){
function AjaxAddAll( offset, done, finished, errors, errorMsg, addedUsers, failed ){
	
	if( !offset )   { offset     = -1; }
	if( !done )     { done       = 0; }
	if( !finished  ){ finished   = 0; }
	if( !errors    ){ errors     = 0; }
	if( !errorMsg  ){ errorMsg   = false; }
	if( !addedUsers){ addedUsers = ''; }
	if( !failed)    { failed = ''; }
	
	var data = new Object();
	data["listid"] = document.getElementById('listid').value;
	data["total"]  = document.getElementById('total').value;
	data["step"]   = 500;
	data["done"]   = done;
	data["errors"] = errors;
	data["errorMsg"] = errorMsg;
	data["addedUsers"] = addedUsers;
	data["failed"]     = failed;
	
	if (document.adminForm.listid.value == ""){
		noListSelected();
	} else {
	
		if(done==0) { AJAXinit( data["total"] ); }
		else if ( finished ) { setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000); }
		
		if( data["total"] > 100 && offset == -1 ) { offset = 0; }
		data["offset"]  = offset;
		
		if( (done+errors) < data["total"] && !finished ) {
			
			var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=ajax_sync_all';
			doAjaxTask(url, data, function(postback){ 
										if(postback.abortAJAX==0){
											jQuery('#ajax_response').html(postback.msg);
										//	AjaxAddAll( offset+10, postback.done, postback.finished, postback.errors, postback.errorMsg, postback.addedUsers, postback.failed); 
											if( !postback.finished){
												AjaxAddAll( offset+10, postback.done, postback.finished, postback.errors, postback.errorMsg, postback.addedUsers, postback.failed); 
											} else { AJAXsuccess(postback.finalMessage);
												setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000);
												markAdded(postback.addedUsers);
											}
										}
								  } 
					  );
		} else {
			return false;
		}
	}
}

function AjaxAddSugar( run, range, offset, done, newContacts, updatedContacts, finished, errors, errorMsg){

    if( range == 'selection' && document.getElementById('boxchecked').value == 0 ) {
	noUsersSelected();
	$('ajax_response').style.display = 'none';
	jQuery('#ajax_response').html('');
	return;
    } else {

	if( !run )      { var run        =  1; }
	if( !offset )   { var offset     = -1; }
	if( !done )     { var done       =  0; }
	if( !newContacts )      { var newContacts        = 0; }
	if( !updatedContacts )  { var updatedContacts    = 0; }
	if( !finished  ){ var finished   = 0; }
	if( !errors    ){ var errors     = 0; }
	if( !errorMsg  ){ var errorMsg   = false; }

	var data = new Object();
	data["range"]  = range;

	data["step"]   = 25;
	data["done"]   = done;
	data["new"]    = newContacts;
	data["updated"]= updatedContacts;
	data["errors"] = errors;
	data["errorMsg"] = errorMsg;

	if( range == 'selection' ){
	    var form = document.getElementById('adminForm');
	    data["cid"] = new Array();
	    var x = 0;
	    var z = 0;
	    for(i=0; i<form.elements.length; i++)
	    {
		if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
		    if( x <= (run*data["step"]) ){
			data["cid"][z] = form.elements[i].value;
			z++;
		    }

		    x++;
		}
	    }
	    data["total"]  = x;
	} else {
	    data["total"]  = document.getElementById('total').value;
	}

	if(done==0) { AJAXinit( data["total"] ); }
	else if ( finished ) { setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000); }

	if( data["total"] > 100 && offset == -1 ) { offset = 0; }
	data["offset"]  = offset;

	if( (done+errors) < data["total"] && !finished ) {

		var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=ajax_sync_sugar';
		doAjaxTask(url, data, function(postback){
		    if(postback.abortAJAX==0){
			jQuery('#ajax_response').html(postback.msg);
			if( !postback.finished){
			    AjaxAddSugar( run, range, offset+25, postback.done, postback.newContacts, postback.updated, postback.finished, postback.errors, postback.errorMsg);
			} else { AJAXsuccess(postback.finalMessage);
			    if( postback.fatalError ){ $('system-message-inner').addClass('error'); } else { $('system-message-inner').removeClass('error'); }
			    setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000);
			}
		    }
	        });
	} else {
		return;
	}
    }
}


function AjaxAddHighrise( run, range, offset, done, newContacts, updatedContacts, finished, errors, errorMsg){

    if( range == 'selection' && document.getElementById('boxchecked').value == 0 ) {
	noUsersSelected();
	$('ajax_response').style.display = 'none';
	jQuery('#ajax_response').html('');
	return;
    } else {

	if( !run )		{ var run        =  1; }
	if( !offset )		{ var offset     = -1; }
	if( !done )		{ var done       =  0; }
	if( !newContacts )      { var newContacts     = 0; }
	if( !updatedContacts )  { var updatedContacts = 0; }
	if( !finished  )	{ var finished   = 0; }
	if( !errors    )	{ var errors     = 0; }
	if( !errorMsg  )	{ var errorMsg   = false; }

	var data = new Object();
	data["range"]  = range;

	data["step"]   = 1;
	data["done"]   = done;
	data["new"]    = newContacts;
	data["updated"]= updatedContacts;
	data["errors"] = errors;
	data["errorMsg"] = errorMsg;

	if( range == 'selection' ){
	    var form = document.getElementById('adminForm');
	    data["cid"] = new Array();
	    var x = 0;
	    var z = 0;
	    for(i=0; i<form.elements.length; i++)
	    {
		if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
		    if( x == (run-1) ){
			data["cid"][z] = form.elements[i].value;
			z++;
		    }
		    x++;
		}
	    }
	    data["total"]  = x;
	} else {
	    data["total"]  = document.getElementById('total').value;
	}

	if(done==0) { AJAXinit( data["total"] ); }
	else if ( finished ) { setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000); }

	if( data["total"] > 100 && offset == -1 ) { offset = 0; }
	data["offset"]  = offset;

	if( (done+errors) < data["total"] && !finished ) {

	    var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=sync_highrise';
	    doAjaxTask(url, data, function(postback){
		if(postback.abortAJAX==0){
		    jQuery('#ajax_response').html(postback.msg);
		    if( !postback.finished){
			run++;
			AjaxAddHighrise( run, range, offset+data["step"], postback.done, postback.newContacts, postback.updated, postback.finished, postback.errors, postback.errorMsg);
		    } else { AJAXsuccess(postback.finalMessage);
			if( postback.fatalError ){ $('system-message-inner').addClass('error'); } else { $('system-message-inner').removeClass('error'); }
			setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000);
		    }
		}
	    });
	} else {
	    return;
	}
    }
}


function AjaxAddLeads( y, done, finished, addedUsers, errors, errorMsg, failed ){
	
	if( !y )		{ y          = 0; }
	if( !done )		{ done       = 0; }
	if( !finished  ){ finished   = 0; }
	if( !addedUsers){ addedUsers = ''; }
	if( !errors    ){ errors     = 0; }
	if( !errorMsg  ){ errorMsg   = false; }
	if( !failed)	{ failed = ''; }
	
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=ajax_sync_leads';
	var form = document.getElementById('adminForm');
	var step = 50;
//	console.log(data);

	var data = new Object();
	data["listid"] = document.getElementById('listid').value;
	data["done"]   = done;
	data["addedUsers"] = addedUsers;
	data["errors"] = errors;
	data["errorMsg"] = errorMsg;
	
	data["cid"] = new Array();
	x = 0;
	y += step;
	z = 0;
	for(i=0; i<form.elements.length; i++)
	{
		if(form.elements[i].type=='checkbox' && form.elements[i].checked==true && form.elements[i].name!='toggle'){
			if( x>=(y-step) && x<y ){
				
				data["cid"][z] = form.elements[i].value;
				z++;
			}
			x++;
		}
	}
	data["total"]  = x;
	
	if (document.adminForm.listid.value == ""){
		noListSelected();
	} else if( x==0 ) {
		noUsersSelected();
	} else {
		if( y == step ) {	AJAXinit( document.getElementById('boxchecked').value ); }
		doAjaxTask(url, data, function(postback){ jQuery('#ajax_response').html(postback.msg);
												  if( !postback.finished){
													  AjaxAddLeads( y, postback.done, postback.finished, postback.addedUsers, postback.errors, postback.errorMsg, postback.failed); 
												  } else { 
													  AJAXsuccess(postback.finalMessage);
													  setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000);
													  markAdded(postback.addedUsers);
												  }
												  });
	}
}

function abortAJAX(){
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=abortAJAX';
	var data = new Object();
	doAjaxTask(url, data, function(postback){ $('ajax_response').style.display = 'none';
						  jQuery('#ajax_response').html('');
						  window.location.reload();
						});
}
function abortAJAXnoRefresh(){
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=abortAJAX';
	var data = new Object();
	doAjaxTask(url, data, function(postback){ $('ajax_response').style.display = 'none';
						  jQuery('#ajax_response').html('');
						});
}

function formToArray( form ){
    var str = new Object;
    for(i=0; i<form.elements.length; i++)
    {
	if(form.elements[i].type=='checkbox' && form.elements[i].checked==true){
		str[form.elements[i].id+'['+i+']'] = form.elements[i].value;
	}
    }
    return str;
}

function markAdded( uids ) {
	for(i=0; i<uids.length; i++){
		if( document.getElementById('row_'+uids[i]) ){
			$('row_'+uids[i]).setStyle('color', '#009F07');
			$('link_'+uids[i]).setStyle('color', '#009F07');
		}
	}
}

function markAdded2( listId ) {
	
	var form = document.getElementById('adminForm');
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=get_subs';
	
	var data = new Object();
	data["listid"] = listId;
	
	$('addUsersLoader').setStyle('visibility', '');
	
	doAjaxTask(url, data, function(postback){ 
		for(i=0; i<form.elements.length; i++) {
			if(form.elements[i].type=='checkbox' && form.elements[i].name!='toggle'){
				if( in_array(form.elements[i].value, postback.uids)) {
					for(k=0; k< postback.uids.length; k++){
						if(document.getElementById('row_'+postback.uids[k])) {
							$('row_'+postback.uids[k]).setStyle('color', '#009F07');
							$('link_'+postback.uids[k]).setStyle('color', '#009F07');
						}
					}
				} else {
					$('row_'+form.elements[i].value).setStyle('color', '');
					$('link_'+form.elements[i].value).setStyle('color', '');
				}
			}
		}
		getTotal( listId );
		$('addUsersLoader').setStyle('visibility', 'hidden');
	    }
	);

}

function getTotal( listId ){

	$('addUsersLoader').setStyle('visibility', '');
	var data = new Object();
	data["listid"] = listId;
	
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=sync&format=raw&task=getTotal';
	doAjaxTask(url, data, function(postback){ 
	    document.getElementById("total").value = postback.total;
	    $('addUsersLoader').setStyle('visibility', 'hidden');
	});
}

/* 
 * The in_array function is taken from the GPL licensed php.js library
 * More info at: http://phpjs.org
 */

function in_array (needle, haystack, argStrict) {

    var key = '', strict = !!argStrict; 
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;            
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {                
				return true;
            }
        }
    }
     return false;
}


function AjaxAddHotness( offset, done, finished, errors, errorMsg, addedUsers, failed ){

	if( !offset )   { offset     = 0; }
	if( !done )     { done       = 0; }
	if( !finished  ){ finished   = 0; }
	if( !errors    ){ errors     = 0; }
	if( !errorMsg  ){ errorMsg   = false; }
	if( !addedUsers){ addedUsers = ''; }
	if( !failed)    { failed     = ''; }

	var data = new Object();
	data["listid"] = document.getElementById('listId').value;
	data["total"]  = document.getElementById('total').value;
	data["step"]   = 500;
	data["done"]   = done;
	data["errors"] = errors;
	data["errorMsg"] = errorMsg;
	data["addedUsers"] = addedUsers;
	data["failed"]     = failed;

	if (document.adminForm.listId.value == ""){
		noListSelected();
	} else {
	
	    if(done==0) { AJAXinit( data["total"] ); }
	    else if ( finished ) { setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000); }

//	    if( data["total"] > 100 && offset == -1 ) { offset = 0; }
	    data["offset"]  = offset;

	    if( (done+errors) < data["total"] && !finished ) {

		var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=send&format=raw&task=ajax_sync_hotness';
		doAjaxTask(url, data, function(postback){
		    if(postback.abortAJAX==0){
			jQuery('#ajax_response').html(postback.msg);
			if( !postback.finished ){
			    AjaxAddHotness( offset+1, postback.done, postback.finished, postback.errors, postback.errorMsg, postback.addedUsers, postback.failed);
			} else {
			    if(postback.failed != 1){
				addInterests( data["listid"] );
			    }
			    AJAXsuccess(postback.finalMessage);
			    setTimeout("$('ajax_response').style.display = 'none'; jQuery('#ajax_response').html('');",1000);
			}
		    }
		  }
	        );
	    } else {
		return false;
	    }
	}
}