/*!
 * jQuery Sticky Footer 2.0
 * Corey Snyder
 * http://tangerineindustries.com
 *
 * Released under the MIT license
 *
 * Copyright 2013 Corey Snyder.
 *
 * Date: Thu Jan 22 2013 13:34:00 GMT-0630 (Eastern Daylight Time)
 * Modification for jquery 1.9+ Tue May 7 2013
 * Modification for non-jquery, removed all, now classic JS Wed Jun 12 2013
 * Modification for Foundation 5 auto height issues
 * Modification for new DOM change event listener
 */

window.onload = function() {
  stickyFooter();
  observer.observe(target, config);
};

//check for changes to the DOM
var target = document.body;
 
// create an observer instance
var observer = new MutationObserver(mutationObjectCallback);
function mutationObjectCallback(mutationRecordsList) {
  
    stickyFooter();
};
 
// configuration of the observer:
var config = { attributes: true, childList: true, characterData: true };
 

//check for resize event
window.onresize = function() {
  stickyFooter();
}

//lets get the marginTop for the <footer>
function getCSS(element, property) {

  var elem = document.getElementsByTagName(element)[0];
  var css = null;
  
  if (elem.currentStyle) {
    css = elem.currentStyle[property];
  
  } else if (window.getComputedStyle) {
  css = document.defaultView.getComputedStyle(elem, null).
  getPropertyValue(property);
  }
  
  return css;

}

function stickyFooter() {
  observer.disconnect();
  document.body.setAttribute("style","height:auto");
      
  if (document.getElementsByTagName("footer")[0].getAttribute("style") != null) {
    document.getElementsByTagName("footer")[0].removeAttribute("style");
  }
  
  if (window.innerHeight != document.body.offsetHeight) {
    var offset = window.innerHeight - document.body.offsetHeight;
    var current = getCSS("footer", "margin-top");
    
    if (isNaN(current) == true) {
      document.getElementsByTagName("footer")[0].setAttribute("style","margin-top:0px;");
      current = 0;
    } else {
      current = parseInt(current);
    }
            
    if (current+offset > parseInt(getCSS("footer", "margin-top"))) {      
      document.getElementsByTagName("footer")[0].setAttribute("style","margin-top:"+(current+offset)+"px;");
    }
  }
  
  document.body.setAttribute("style","height:100%");
  
  //reconnect
  observer.observe(target, config);
}

/*
! end sticky footer
*/
