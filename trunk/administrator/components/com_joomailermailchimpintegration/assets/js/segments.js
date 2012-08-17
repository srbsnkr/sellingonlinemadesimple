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

window.addEvent('domready', function() {
if(document.getElementById('segmenttype1')){
	$('segmenttype1').addEvent('change', function(e){
		getSegmentFields( 'segmentTypeConditionDiv_1', 1 );
	});
	$('segmentTypeConditionDetail_1').addEvent('change', function(e){
		getSegmentFields( 'segmentTypeConditionDiv_1', 1 );
	});
}
	$$('#addCondition').addEvent('click', function(e){
		addCondition();
	});
});

function addCondition(){
	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=send&format=raw&task=addCondition';
	var data = new Object();
	data["listId"] = document.getElementById('listId').value;
	data["conditionCount"] = document.getElementById('conditionCount').value;
	var next = parseInt(data["conditionCount"])+1;
	
	for(x=2;x<11;x++){
		if($('segment'+x).innerHTML == ''){
			jQuery('#segment'+x).html( AJAXloader );
			$('segment'+x).style.display = '';
			break;
		}
	}
	
	doAjaxTask(url, data, function(postback){
	    jQuery('#segment'+x).html(postback.html);
	    document.getElementById('conditionCount').value = next;
	    if( next >= 10 ) { document.getElementById('addCondition').style.display = 'none'; }
	    if(postback.js){
		eval( postback.js );
		eval( $j('.calendar').attr('src', '../administrator/components/com_joomailermailchimpintegration/assets/images/calendar.png'));
	    }
	});
}

function removeCondition( nr ){
	
	jQuery('#segment'+nr).html('');
	$('segment'+nr).style.display = 'none';
	
	var conditionCount = parseInt(document.getElementById('conditionCount').value);

	document.getElementById('conditionCount').value = conditionCount-1;
	if( (conditionCount-1) < 10 ) { document.getElementById('addCondition').style.display = ''; }
}



function getSegmentFields( id, nr ){

	var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=send&format=raw&task=getSegmentFields';
	
	var data = new Object();
	data["campaign"] = document.getElementById('time').value;
	data["listId"] = document.getElementById('listId').value;
	var segmentType = document.getElementById('segmenttype'+nr);
	data["type"] = segmentType.value;
	data["condition"] = document.getElementById('segmentTypeCondition_'+nr).value;
	data["nr"] = nr;
	
	if(document.getElementById('segmentTypeConditionDetail_'+nr) != undefined) {
		data["conditionDetail"] = document.getElementById('segmentTypeConditionDetail_'+nr).value;
	}

	doAjaxTask(url, data, function(postback){ 	
	    jQuery('#'+id).html(postback.html);
	    if(postback.js){
		eval(postback.js);
		eval( $j('.calendar').attr('src', '../administrator/components/com_joomailermailchimpintegration/assets/images/calendar.png'));
	    }
	});
}

var segmentsTested = 0;

function testSegments(){
	segmentsTested = 1;
	document.getElementById('ajax-spin').style.visibility = "visible";
	if( document.getElementById('listId').value == '' ){
		alert( selectListAlert );
		document.getElementById('ajax-spin').style.visibility = "hidden";
	} else {
	
		var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=send&format=raw&task=testSegments';
		
		var data = new Object();
		data["listId"]    = document.getElementById('listId').value;
		data["match"]     = document.getElementById('match').value;
		data["condCount"] = parseInt(document.getElementById('conditionCount').value);
		
		data["conditionDetailValue"] = '';
		data["type"] = '';
		data["condition"] = '';
		data["conditionDetail"] = '';
		data["conditionDetailValue"] = '';
		
		for(i=1;i<=data["condCount"];i++){
			
			var type = document.getElementById('segmenttype'+i).value;
			data["type"]      += document.getElementById('segmenttype'+i).value +'|*|';
			data["condition"] += document.getElementById('segmentTypeCondition_'+i).value +'|*|';
			if( type == 'date' ){
				data["conditionDetail"] += document.getElementById('segmentTypeConditionDetail_'+i).value +'|*|';
				data["conditionDetailValue"] += document.getElementById('segmentTypeConditionDetailValue_'+i).value +'|*|';
			} else if( !isNaN(type) ){
				var ob = document.getElementById('segmentTypeConditionDetailValue_'+i);
				for (var x = 0; x < ob.options.length; x++) {
					if (ob.options[ x ].selected == true) {
						data["conditionDetailValue"] += ob.options[ x ].value+',';
					}
				}
				data["conditionDetailValue"] = data["conditionDetailValue"].slice(0,-1) +'|*|';
				data["conditionDetail"] += '#|*|';
			} else {
				data["conditionDetailValue"] += document.getElementById('segmentTypeConditionDetailValue_'+i).value +'|*|';
				data["conditionDetail"] += '#|*|';
			}
		}
		
		
	
		doAjaxTask(url, data, function(postback){
		    document.getElementById('ajax-spin').style.visibility = "hidden";
		    jQuery('#testResponse').html(postback.msg);
		    creditCount = postback.creditCount;
		    $('credits').innerHTML = postback.creditCount;
		    $('testResponse').style.display = 'block';
		    currentCredits = postback.creditCount;
		});
	}
}

function addInterests( listId ){
	var staticOptions = 10;
	if(listId){
		var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=send&format=raw&task=addInterests';
		var condCount = parseInt(document.getElementById('conditionCount').value);
		
		var data = new Object();
		data["listId"] = listId;
		
		doAjaxTask(url, data, function(postback){ 
		    if( postback != undefined ){
			for(x=1;x<=10;x++){
				var element = document.getElementById('segmenttype'+x);
				if( element && element.innerHTML != '' ){
					var opt = element.options;
					if( element.length>staticOptions ){
						for ( i=element.length; i>staticOptions; i-- ){
							element.remove(i-1);
						}
					}
					for ( var i=0; i<postback.counter; i++ ){
						var newOption = new Option(postback.name[i], postback.id[i], false, false);
						try {
							element.add(newOption, null);
						} catch (err) {
							element.add(newOption); // IE only
						}
					}
				}
			}	
		    }
		});
	} else {
		for(x=1;x<=10;x++){
			var element = $('segmenttype'+x);
			if( element && element.innerHTML != '' ){
				var optionCount = element.length;
				if( optionCount>staticOptions ){
					for(i=optionCount-1;i>=staticOptions;i--){
						element.remove(i);
					}
				}
			}
		}
	}
}

function rating( nr, val, store ){
	if(store) { $('segmentTypeConditionDetailValue_'+nr).value = val; }
	for( i=1; i<=5; i++){
		if( i<=val){
			$$('#segmentTypeConditionDiv_'+nr+' .rating_'+i).addClass('active');
		} else {
			$$('#segmentTypeConditionDiv_'+nr+' .rating_'+i).removeClass('active');
		}
	}
}
function restoreRating( nr ){
	var rating = $('segmentTypeConditionDetailValue_'+nr).value;
	for( i=1; i<=5; i++){
		if( i<=rating){
			$$('#segmentTypeConditionDiv_'+nr+' .rating_'+i).addClass('active');
		} else {
			$$('#segmentTypeConditionDiv_'+nr+' .rating_'+i).removeClass('active');
		}
	}
}
