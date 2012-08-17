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

function ajax_download(template, message, jversion){
    var url="index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=templates&task=ajax_download&format=raw&template="+template;
    var data = new Object();
    doAjaxTask(url, data, function(postback){
						if( jversion == 15 ){
						    var resp=Json.evaluate(postback);
						} else {
						    var resp = postback;
						}
						if (resp.error === true ){
						    jQuery('#'+template).html( message );
						}else{
						    var link = '<a href="'+resp.url+'" >'+template+'.zip</a>';
						    window.open(resp.url,'Download');
						    jQuery('#'+template).html(link);
						}
    });
}
