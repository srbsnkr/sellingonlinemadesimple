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

window.addEvent('load',function() {

    var _div  = document.createElement('div');
    _div.style.display = 'none';
    var _img     = document.createElement('IMG');
    _img.setAttribute('src', '../administrator/components/com_joomailermailchimpintegration/assets/images/loader_55.gif');
    _img.setAttribute('alt', 'preloader');
    _img.setAttribute('title', 'preloader');

    _div.appendChild(_img);
    document.body.appendChild(_div);

});

function joomailermailchimpintegration_ajax_loader() {

    var _div  = document.createElement('div');

    _div.id = 'joomailermailchimpintegration_ajax_loader';
    _div.style.width = '100%';
    _div.style.height = '100%';
    _div.style.background = '#000';
    _div.style.opacity = '0';
    _div.style.position = 'fixed';
    _div.style.top = '0';
    _div.style.left = '0';
    _div.style.zIndex = '999999';

    var _div2  = document.createElement('div');
    _div2.style.background = 'url(../administrator/components/com_joomailermailchimpintegration/assets/images/loader_55.gif) no-repeat';
    _div2.style.position = 'absolute';
    _div2.style.top = '48%';
    _div2.style.left = '48%';
    _div2.style.width = '54px';
    _div2.style.height = '55px';

    _div.appendChild(_div2);

    document.body.appendChild(_div);
    
    if( MooTools.version >= '1.3' ) {
	var Fadein = new Fx.Morph('joomailermailchimpintegration_ajax_loader', {'duration' : 250});
	Fadein.start({'opacity':0.2});
    } else {
	var Fadein = new Fx.Style('joomailermailchimpintegration_ajax_loader', 'opacity', {duration:250});
	Fadein.start(0.2);
    }
}

function hideSetupInfo(){
    var url = baseUrl + 'index.php?option=com_joomailermailchimpintegration&action=AJAX&controller=main&format=raw&task=hideSetupInfo';
    var data = new Object();
    doAjaxTask(url, data, function(postback){
	jQuery('#setupInfo').slideUp();
    });
    
}

