/**
 * @name MyObject
 * @author
 *
 * Basic usage:
 * MyObject();
 *
 * additionally you can use methods like MyObject.methodName();
 *
 * Advanced usage:
 * MyObject({
 *      'additionalOption': 'thatCanOverwriteDefaults'
 * });
 */
function MyObject(opts) {
  //assign _root and config private variables
  var _this = this;
  var _opts = null;

  /**
   * init
   */
  this.init = function () {
    // default options
    _opts = $.extend({
      'someOption': 'some value',
      'onSomeEvent': _this.somePublicMethod
    }, opts);


    // Example calls
    _somePrivateMethod();
    _this.somePublicMethod();


  }


  /**
   * Some Private Method
   *
   * @private
   */
  var _somePrivateMethod = function () {
    //some code
    console.log('_somePrivateMethod');
  }
  /**
   * Some Public Method
   */
  this.somePublicMethod = function () {
    //some code
    console.log('somePublicMethod');
  }


  // Auto init
  this.init();
}


//declaration and initialization of MyObject
MyObject();

//MyObject._somePrivateMethod();
//returns: TypeError: MyObject._somePrivateMethod is not a function

//MyObject.somePublicMethod();
//returns: 'somePublicMethod'