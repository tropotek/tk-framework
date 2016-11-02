/*
 * Plugin: Example
 * Version: 1.0
 *
 * @author Michael Mifsud <info@tropotek.com>
 * @link http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 */



/**
 * This plugin template is from http://stefangabos.ro/jquery/jquery-plugin-boilerplate-revisited/
 *
 * jQuery Plugin Boilerplate
 * A boilerplate for jumpstarting jQuery plugins development
 * version 1.1, May 14th, 2011
 * by Stefan Gabos
 * 
 * <code>
 *   $(document).ready(function() {
 *     // attach the plugin to an element
 *     $('#element').pluginName({'foo': 'bar'});
 *
 *     // call a public method
 *     $('#element').data('pluginName').foo_public_method();
 *
 *     // get the value of a property
 *     $('#element').data('pluginName').settings.foo;
 *   
 *   });
 * </code>
 */

// remember to change every instance of "pluginName" to the name of your plugin!
(function($) {

  /**
   *
   * @param Element element
   * @param options
   */
  var pluginName = function(element, options) {

    // plugin's default options
    // this is private property and is  accessible only from inside the plugin
    var defaults = {

      foo: 'bar',

      // if your plugin is event-driven, you may provide callback capabilities
      // for its events. execute these functions before or after events of your 
      // plugin, so that users may customize those particular events without 
      // changing the plugin's code
      onFoo: function() {}

    };

    // to avoid confusions, use "plugin" to reference the 
    // current instance of the object
    var plugin = this;

    // this will hold the merged default, and user-provided options
    // plugin's properties will be available through this object like:
    // plugin.settings.propertyName from inside the plugin or
    // element.data('pluginName').settings.propertyName from outside the plugin, 
    // where "element" is the element the plugin is attached to;
    plugin.settings = {};

    var $element = $(element); // reference to the jQuery version of DOM element

    // the "constructor" method that gets called when the object is created
    plugin.init = function() {

      // the plugin's final properties are the merged default and 
      // user-provided options (if any)
      plugin.settings = $.extend({}, defaults, options);

      // code goes here

    };

    // private methods
    // these methods can be called only from inside the plugin like:
    // methodName(arg1, arg2, ... argn)

    // a private method. for demonstration purposes only - remove it!
    var foo_private_method = function() {

      // code goes here

    };

    // public methods
    // these methods can be called like:
    // plugin.methodName(arg1, arg2, ... argn) from inside the plugin or
    // element.data('pluginName').publicMethod(arg1, arg2, ... argn) from outside 
    // the plugin, where "element" is the element the plugin is attached to;

    // a public method. for demonstration purposes only - remove it!
    plugin.foo_public_method = function() {

      // code goes here

    };

    // fire up the plugin!
    // call the "constructor" method
    plugin.init();

  };

  // add the plugin to the jQuery.fn object
  $.fn.pluginName = function(options) {

    // iterate through the DOM elements we are attaching the plugin to
    return this.each(function() {

      // if plugin has not already been attached to the element
      if (undefined == $(this).data('pluginName')) {

        // create a new instance of the plugin
        // pass the DOM element and the user-provided options as arguments
        var plugin = new pluginName(this, options);

        // in the jQuery version of the element
        // store a reference to the plugin object
        // you can later access the plugin and its methods and properties like
        // element.data('pluginName').publicMethod(arg1, arg2, ... argn) or
        // element.data('pluginName').settings.propertyName
        $(this).data('pluginName', plugin);

      }

    });

  }

})(jQuery);


/** --------------------------------------------------------------- **/

/**
 * Template From http://www.capricasoftware.co.uk/corp/template.php
 *
 * In your application, outside of the plug-in, initialise the plug-in
 * like this:
 *
 *  $(selector).example();
 *  $(selector).example({
 *     propertyA: "something",
 *        propertyB: false,
 *        callbackC: function() {},
 *        callbackD: function(p1, p2, p3) {}
 *     });
 *
 * Invoke public methods like this:
 *
 * $(selector).example("publicFunctionA");
 * $(selector).example("publicFunctionB", "param1", 12345);
 *
 */
;(function ($) {

  var methods = {
    /**
     * init
     */
    init: function (options) {
      return this.each(function () {
        var $plugin = $(this);
        var defaultOptions = {
          // Setup any default options here
          option1: 'defaultValue1'
        };
        // We prefer to reference everything through the $plugin object
        if (options) {
          $.extend(defaultOptions, options);
        }
        $plugin.options = defaultOptions;

        // Code here


      }); // END each()
    }

    // functions here....


  };

  $.fn.example = function (method) {
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      $.error("Method " + method + " does not exist on jQuery.example");
    }
  };

})(jQuery);


// --------------------------------------------------- //

;(function ($) {
  $.fn.extend({
    //pass the options variable to the function
    pluginname: function (options) {
      //Set the default values, use comma to separate the settings, example:
      var defaults = {
        canvasId: 'demo1',
        url : '/ajax/test.json',
        callback : function () {}
      }
      var options = $.extend(defaults, options);

      return this.each(function () {
        var o = options;
        //Assign current element to variable, in this case is UL element
        var obj = $(this);
        // Get all li children from this object
        var items = $("li", obj);


        // call callback
        o.callback(this);

        //code to be inserted here
        //you can access the value like this
        vd(o.canvasId);
      });

    }
  });


  // a public method. for demonstration purposes only - remove it!
  $.fn.pluginname.publicMethod = function() {
    // code goes here
  };

  // Private function for debugging.
  vd = function(obj) {
    if (window.console && window.console.log) {
      window.console.log(obj);
    }
  };

})(jQuery);




