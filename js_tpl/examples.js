/*
 * Common global javascript for the site
 * 
 * User: mifsudm
 * Date: 5/15/13
 * Time: 10:47 AM
 * To change this template use File | Settings | File Templates.
 */


// jquery function
jQuery(function($) {
  
  // Code Here
  
});







// Plugin style 
(function( yourPluginName, $, undefined ) {
    // guard to detect browser-specific issues early in development
    "use strict";
    // private var
    var _settings;
    // public var
    yourPluginName.someProperty = 'default value';
 
    // public method
    yourPluginName.init = function(settings) {
        _settings = $.extend({}, settings);
    }
}( window.yourPluginName = window.yourPluginName || {}, jQuery ));


