<?php
/**
* @package Dynamic Price Updater
* @copyright Dan Parry (Chrome)
* @author Dan Parry (Chrome)
* @version 2.0a (roughly)
* @contact admin@chrome.me.uk
* @licence This module is released under the GNU/GPL licence... Really... Go look it up
*/
// define some running vars
define('DPU_PRODUCT_FORM', 'cart_quantity'); // this should never change
define('DPU_PRICE_ELEMENT_ID', 'productPrices'); // this is the ID of the element where your price is displayed
define('DPU_WEIGHT_ELEMENT_ID', 'productWeight'); // this is the ID where your weight is displayed
define('DPU_SIDEBOX_TITLE', 'Price Breakdown'); // the heading that shows in the Updater sidebox
define('DPU_SIDEBOX_ELEMENT_ID', 'whatsnew'); // set this to: 'false' - don't use or the ID of the sidebox you want the display to insert above (must be exact)
define('DPU_SHOW_LOADING_IMAGE', true); // true to show a small loading graphic so the user knows something is happening

$load = true; // if any of the PHP conditions fail this will be set to false and DPU won't be fired up
$pid = (!empty($_GET['products_id']) ? intval($_GET['products_id']) : 0);
if (0==$pid) {
	$load = false;
} elseif (zen_get_products_price_is_call($pid) || zen_get_products_price_is_free($pid) || STORE_STATUS > 0) {
	$load = false;
}
 $pidp = zen_get_products_display_price($pid);
 if (empty($pidp))	$load = false;

if ($load) {
?>
<script language="javascript" type="text/javascript">
// Hidey codey <![CDATA[
// Set some global vars
var theFormName = '<?php echo DPU_PRODUCT_FORM; ?>';
var theForm = false;
var theURL = '<?php echo DIR_WS_CATALOG; ?>dpu_ajax.php';
var _secondPrice = 'cartAdd';
var objSP = false; // please don't adjust this
var DPURequest = [];
// Updater sidebox settings
var _sidebox = '<?php echo DPU_SIDEBOX_ELEMENT_ID; ?>';
var objSB = false; // this holds the sidebox object

<?php if (DPU_SHOW_LOADING_IMAGE) { // create the JS object for the loading image ?>
var loadImg = document.createElement('img');
loadImg.src = '<?php echo DIR_WS_IMAGES; ?>ajax-loader.gif';
loadImg.id = 'DPULoaderImage';
var loadImgSB = document.createElement('img');
loadImgSB.src = '<?php echo DIR_WS_IMAGES; ?>ajax-loader.gif';
loadImgSB.id = 'DPULoaderImageSB';
loadImgSB.style.margin = 'auto';
// loadImg.style.display = 'none';
<?php } ?>

function objXHR()
{ // scan the function clicked and act on it using the Ajax interthingy
	var url; // URL to send HTTP DPURequests to
	var timer; // timer for timing things
	var XHR; // XMLHttpDPURequest object
	var _responseXML; // holds XML formed responses from the server
	var _responseText; // holds any textual response from the server
	// var DPURequest = []; // associative array to hold DPURequests to be sent

	// DPURequest = new Array();
	this.createXHR();
}

objXHR.prototype.createXHR = function () { // this code has been modified from the Apple developers website
	this.XHR = false;
    // branch for native XMLHttpDPURequest object
    if(window.XMLHttpRequest) { // decent, normal, law abiding browsers
    	try { // make sure the object can be created
			this.XHR = new XMLHttpRequest();
        } catch(e) { // it can't
			this.XHR = false;
        }
    // branch for IE/Windows ActiveX version
    } else if(window.ActiveXObject) { // this does stuff too
       	try {
        	this.XHR = new ActiveXObject("Msxml2.XMLHTTP");
      	} catch(e) {
        	try {
          		this.XHR = new ActiveXObject("Microsoft.XMLHTTP");
        	} catch(e) {
          		this.XHR = false;
        	}
		}
    }
}

objXHR.prototype.getData = function(strMode, resFunc, _data) { // send a DPURequest to the server in either GET or POST
	strMode = (strMode.toLowerCase() == 'post' ? 'post' : 'get');
	var _this = this; // scope resolution
	this.createXHR();

	if (this.XHR) {
		this.XHR.onreadystatechange = function () {
			if (_this.XHR.readyState == 4) {
			// only if "OK"
				if (_this.XHR.status == 200) {
					_this._responseXML = _this.XHR.responseXML;
					_this._responseText = _this.XHR.responseText;
					_this.responseHandler(resFunc);
				} else {
					alert('Status returned - ' + _this.XHR.statusText);
				}
			}
		}
		this.XHR.open(strMode.toLowerCase(), this.url+(strMode.toLowerCase() == 'get' ? '?' + this.compileRequest() : ''), true);
		if (strMode.toLowerCase() == 'post')	this.XHR.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		this.XHR.send(_data);
	} else {
		var mess = "I couldn't contact the server!\n\nIf you use IE please allow ActiveX objects to run";
		alert (mess);
	}
}

objXHR.prototype.compileRequest = function () {
	// parse the DPURequest array into a URL encoded string
	var ret = ''; // return DPURequest string

	for (var e in DPURequest) {
		ret += e + '=' + DPURequest[e] + '&';
	}

	return (ret.substr(0, ret.length - 1));
}

objXHR.prototype.responseHandler = function (theFunction) { // redirect responses from the server to the right function
	DPURequest = new Array();
	eval('this.'+theFunction);
}

objXHR.prototype.getPrice = function () {
    <?php if (DPU_SHOW_LOADING_IMAGE) { ?>
    document.getElementById('<?php echo DPU_PRICE_ELEMENT_ID; ?>').appendChild(loadImg);
    loadImg.style.display = 'block';
    if (_sidebox !== false && document.getElementById('updaterSB')) {
        var theSB = document.getElementById('updaterSBContent');
        theSB.innerHTML = '';
        theSB.style.textAlign = 'center';
        theSB.appendChild(loadImgSB);
    }
    <?php } ?>
	this.url = theURL;
	var n=theForm.elements.length;
	var temp = '';
	for (var i=0; i<n; i++) {
		var el = theForm.elements[i];
		switch (el.type) { <?php // I'm not sure this even needed as a switch; testing needed ?>
			case 'select':
			case 'select-one':
			case 'text':
			case 'hidden':
				temp += el.name+'='+escape(el.value)+'&';

				break;
			case 'checkbox':
			case 'radio':
				if (true == el.checked)	temp += el.name+'='+escape(el.value)+'&';
				break;
		}
	}
	temp = temp.substr(0, temp.length - 1)
	this.getData('post', 'handlePrice()', temp);
}

objXHR.prototype.handlePrice = function () {
	var thePrice = document.getElementById('<?php echo DPU_PRICE_ELEMENT_ID; ?>');
	thePrice.removeChild(loadImg);
	
	// use the spans to see if there is a discount occuring up in this here house
	var test = thePrice.getElementsByTagName('span');
	var psp = false;
	
	for (var a=0,b=test.length; a<b; a++) {
		if (test[a].className == 'productSpecialPrice')	psp = test[a];
	}
	
	var type = this._responseXML.getElementsByTagName('responseType')[0].childNodes[0].nodeValue;
    if (_sidebox !== false && document.getElementById('updaterSB')) {
        var theSB = document.getElementById('updaterSBContent');
        theSB.style.textAlign = 'left';
        var sbContent = '';
        updateSidebox = true;
    } else {
        updateSidebox = false;
    }
	if (type == 'error') {
		this.showErrors();
	} else {
		var temp = this._responseXML.getElementsByTagName('responseText');
		for(var i=0, n=temp.length; i<n; i++) {
			var type = temp[i].getAttribute('type');
			
			switch (type) {<?php // the 'type' attribute defines what type of information is being provided ?>
				case 'priceTotal':				
					if (psp) {
						psp.innerHTML = temp[i].childNodes[0].nodeValue;
					} else {
						thePrice.innerHTML = temp[i].childNodes[0].nodeValue;
					}
					if (_secondPrice !== false)	updSP();
					break;
				case 'quantity':
					with (temp[i].childNodes[0]) {
						if (nodeValue != '') {
							if (psp) {
								psp.innerHTML += nodeValue;
							} else {
								thePrice.innerHTML += nodeValue;
							}
							
							updSP();
						}
					}
					break;
                case 'weight':
                    var theWeight = document.getElementById('<?php echo DPU_WEIGHT_ELEMENT_ID; ?>');
                    if (theWeight)  theWeight.innerHTML = temp[i].childNodes[0].nodeValue;
                    break;
                case 'sideboxContent':
                    if (updateSidebox) {
                        sbContent += temp[i].childNodes[0].nodeValue;
                    }
                    break;
			}
		}
	}
    if (updateSidebox)  theSB.innerHTML = sbContent;
}

function updSP() {
	// adjust the second price display; create the div if necessary
	var flag = false; // error tracking flag

	if (_secondPrice !== false) { // second price is active
		var centre = document.getElementById('productGeneral');
		var temp = document.getElementById('<?php echo DPU_PRICE_ELEMENT_ID; ?>');
		var itemp = document.getElementById(_secondPrice);

		if (objSP === false) { // create the second price object
			if (!temp || !itemp)	flag = true;

			if (!flag) {
				objSP = temp.cloneNode(true);
				objSP.id = temp.id + 'Second';
				itemp.parentNode.insertBefore(objSP, itemp.nextSibling);
			}
		}
		objSP.innerHTML = temp.innerHTML;
	}
}

function createSB() { // create the sidebox for the attributes info display
    if (_sidebox != 'false' && !(document.getElementById('updaterSB'))) {
        var temp = document.getElementById(_sidebox); // get a handle to the sidebox to insertBefore
        if (temp) {
            objSB = document.createElement('DIV'); // create the sidebox wrapper
            objSB.id = 'updaterSB';
            objSB.className = 'leftBoxContainer'; // set the CSS reference
            // create the heading bit
            var tempH = document.createElement('H3');
            tempH.id = 'updateSBHeading';
            tempH.className = 'leftBoxHeading';
            tempH.innerHTML = '<?php echo DPU_SIDEBOX_TITLE; ?>';
            objSB.appendChild(tempH);
            // create the content div
            var tempC = document.createElement('DIV');
            tempC.id = 'updaterSBContent';
            tempC.className = 'sideBoxContent';
            tempC.innerHTML = 'If you can read this Chrome has broken something';
            objSB.appendChild(tempC);

            temp.parentNode.insertBefore(objSB, temp);
        }
    }
}

objXHR.prototype.showErrors = function () {
	var errorText = this._responseXML.getElementsByTagName('responseText');
	var alertText = '';
	var n=errorText.length;
	for (var i=0; i<n; i++) {
		alertText += '\n- '+errorText[i].childNodes[0].nodeValue;
	}
	alert ('Error! Message reads:\n\n'+alertText);
}

var xhr = new objXHR;

function init() {
	var n=document.forms.length;
	for (var i=0; i<n; i++) {
		if (document.forms[i].name == theFormName) {
			theForm = document.forms[i];
			continue;
		}
	}

	var n=theForm.elements.length;
	for (var i=0; i<n; i++) {
		switch (theForm.elements[i].type) {
			case 'select':
			case 'select-one':
				theForm.elements[i].onchange = function () { xhr.getPrice(); }
				break;
			case 'text':
				theForm.elements[i].onkeyup = function () { xhr.getPrice(); }
				break;
			case 'checkbox':
			case 'radio':
				theForm.elements[i].onclick = function () { xhr.getPrice(); }
				break;
		}
	}

    if (_sidebox != 'false') createSB();
	xhr.getPrice();
}

<?php
// the following statements should allow multiple onload handlers to be applied
// I know this type of event registration is technically deprecated but I decided to use it because I haven't before
// There shouldn't be any fallout from the downsides of this method as only a single function is registered (and in the bubbling phase of each model)
// For backwards compatibility I've included the traditional DOM registration method ?>
try { // the IE event registration model
	window.attachEvent('onload', init);
} catch (e) { // W3C event registration model
	window.addEventListener('load', init, false);
} finally {
	window.onload = init;
}
// Endy hidey ]]></script><?php } ?>