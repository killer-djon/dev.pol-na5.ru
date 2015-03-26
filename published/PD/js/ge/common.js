/** @see http://code.google.com/p/trimpath/wiki/JsonLibrary */
toJsonString = function(arg) {
    return toJsonStringArray(arg).join('');
}
toJsonStringArray = function(arg, out) {
    out = out || new Array();
    var u; // undefined

    switch (typeof arg) {
    case 'object':
        if (arg) {
            if (arg.constructor == Array) {
                out.push('[');
                for (var i = 0; i < arg.length; ++i) {
                    if (i > 0)
                        out.push(',\n');
                    toJsonStringArray(arg[i], out);
                }
                out.push(']');
                return out;
            } else if (typeof arg.toString != 'undefined') {
                out.push('{');
                var first = true;
                for (var i in arg) {
                    var curr = out.length; // Record position to allow undo when arg[i] is undefined.
                    if (!first)
                        out.push(',\n');
                    toJsonStringArray(i, out);
                    out.push(':');                    
                    toJsonStringArray(arg[i], out);
                    if (out[out.length - 1] == u)
                        out.splice(curr, out.length - curr);
                    else
                        first = false;
                }
                out.push('}');
                return out;
            }
            return out;
        }
        out.push('null');
        return out;
    case 'unknown':
    case 'undefined':
    case 'function':
        out.push(u);
        return out;
    case 'string':
        out.push('"')
        out.push(arg.replace(/(["\\])/g, '\\$1').replace(/\r/g, '').replace(/\n/g, '\\n'));
        out.push('"');
        return out;
    default:
        out.push(String(arg));
        return out;
    }
}

function newClass(parent, prop) {
	  // Dynamically create class constructor.
	  var clazz = function() {
	    // Stupid JS need exactly one "operator new" calling for parent
	    // constructor just after class definition.
	    if (clazz.preparing) return delete(clazz.preparing);
	    // Call custom constructor.
	    if (clazz.constr) {
	      this.constructor = clazz; // we need it!
	      clazz.constr.apply(this, arguments);
	    }
	  }
	  clazz.prototype = {}; // no prototype by default
	  if (parent) {
	    parent.preparing = true;
	    clazz.prototype = new parent;
	    clazz.prototype.constructor = parent;
	    clazz.constr = parent; // BY DEFAULT - parent constructor
	  }
	  if (prop) {
	    var cname = "constructor";
	    for (var k in prop) {
	      if (k != cname) clazz.prototype[k] = prop[k];
	    }
	    if (prop[cname] && prop[cname] != Object)
	      clazz.constr = prop[cname];
	  }
	  clazz.prototype.superclass = function() {return this.constructor.prototype};
	  return clazz;
	}		
Function.prototype.bind = function(object) {
var __method = this;
return function() {
return __method.apply(object, arguments);
}
}

function createDiv(className) {
	return createElem("div", className);
}

function createElem(tag, className, attributes) {
	var elem = document.createElement(tag);
	jQuery(elem).addClass(className);
	return elem;	
}