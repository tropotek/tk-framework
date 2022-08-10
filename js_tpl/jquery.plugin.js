/*
 * Plugin: pluginName
 * Version: 1.0
 * Date: 11/05/17
 *
 * @author Michael Mifsud <http://www.tropotek.com/>
 * @see http://www.tropotek.com/
 * @license Copyright 2007 Michael Mifsud
 * @source http://stefangabos.ro/jquery/jquery-plugin-boilerplate-revisited/
 */

/**
 * TODO: Change every instance of "pluginName" to the name of your plugin!
 * Description:
 *   {Add a good description so you can identify the plugin when reading the code.}
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
;(function($) {

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

    // this will hold the merged default, and user-provided options
    // plugin's properties will be available through this object like:
    // plugin.settings.propertyName from inside the plugin or
    // element.data('pluginName').settings.propertyName from outside the plugin,
    // where "element" is the element the plugin is attached to;
    plugin.settings = {};

    // to avoid confusions, use "plugin" to reference the 
    // current instance of the object
    var plugin = this;

    var $element = $(element); // reference to the jQuery version of DOM element

    // the "constructor" method that gets called when the object is created
    plugin.init = function() {

      // the plugin's final properties are the merged default and 
      // user-provided options (if any)
      plugin.settings = $.extend({}, defaults, $element.data(), options);

      // TODO: code goes here
      console.log(plugin.settings);

    };

    // private methods
    // these methods can be called only from inside the plugin like:
    // methodName(arg1, arg2, ... argn)

    // a private method. for demonstration purposes only - remove it!
    var foo_private_method = function() {

      // code goes here

    };  // END init()

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
      if (undefined === $(this).data('pluginName')) {

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




// An optimised ready to use version of the above
/*
;(function($) {
  var pluginName = function(element, options) {
    var plugin = this;
    plugin.settings = {};
    var $element = $(element);

    // plugin settings
    var defaults = {
      foo: 'bar',
      onFoo: function() {}
    };

    // plugin vars
    var foo = '';

    // constructor method
    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, $element.data(), options);

      // TODO: code goes here
      console.log(plugin.settings);



    };  // END init()

    // private methods
    //var foo_private_method = function() { };

    // public methods
    //plugin.foo_public_method = function() { };

    // call the "constructor" method
    plugin.init();
  };

  // add the plugin to the jQuery.fn object
  $.fn.pluginName = function(options) {
    return this.each(function() {
      if (undefined === $(this).data('pluginName')) {
        var plugin = new pluginName(this, options);
        $(this).data('pluginName', plugin);
      }
    });
  }

})(jQuery);
*/
