var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};

function unwrapExports (x) {
	return x && x.__esModule && Object.prototype.hasOwnProperty.call(x, 'default') ? x['default'] : x;
}

function createCommonjsModule(fn, basedir, module) {
	return module = {
	  path: basedir,
	  exports: {},
	  require: function (path, base) {
      return commonjsRequire(path, (base === undefined || base === null) ? module.path : base);
    }
	}, fn(module, module.exports), module.exports;
}

function commonjsRequire () {
	throw new Error('Dynamic requires are not currently supported by @rollup/plugin-commonjs');
}

var base83 = createCommonjsModule(function (module, exports) {
Object.defineProperty(exports, "__esModule", { value: true });
var digitCharacters = [
    "0",
    "1",
    "2",
    "3",
    "4",
    "5",
    "6",
    "7",
    "8",
    "9",
    "A",
    "B",
    "C",
    "D",
    "E",
    "F",
    "G",
    "H",
    "I",
    "J",
    "K",
    "L",
    "M",
    "N",
    "O",
    "P",
    "Q",
    "R",
    "S",
    "T",
    "U",
    "V",
    "W",
    "X",
    "Y",
    "Z",
    "a",
    "b",
    "c",
    "d",
    "e",
    "f",
    "g",
    "h",
    "i",
    "j",
    "k",
    "l",
    "m",
    "n",
    "o",
    "p",
    "q",
    "r",
    "s",
    "t",
    "u",
    "v",
    "w",
    "x",
    "y",
    "z",
    "#",
    "$",
    "%",
    "*",
    "+",
    ",",
    "-",
    ".",
    ":",
    ";",
    "=",
    "?",
    "@",
    "[",
    "]",
    "^",
    "_",
    "{",
    "|",
    "}",
    "~"
];
exports.decode83 = function (str) {
    var value = 0;
    for (var i = 0; i < str.length; i++) {
        var c = str[i];
        var digit = digitCharacters.indexOf(c);
        value = value * 83 + digit;
    }
    return value;
};
exports.encode83 = function (n, length) {
    var result = "";
    for (var i = 1; i <= length; i++) {
        var digit = (Math.floor(n) / Math.pow(83, length - i)) % 83;
        result += digitCharacters[Math.floor(digit)];
    }
    return result;
};

});

var utils = createCommonjsModule(function (module, exports) {
Object.defineProperty(exports, "__esModule", { value: true });
exports.sRGBToLinear = function (value) {
    var v = value / 255;
    if (v <= 0.04045) {
        return v / 12.92;
    }
    else {
        return Math.pow((v + 0.055) / 1.055, 2.4);
    }
};
exports.linearTosRGB = function (value) {
    var v = Math.max(0, Math.min(1, value));
    if (v <= 0.0031308) {
        return Math.round(v * 12.92 * 255 + 0.5);
    }
    else {
        return Math.round((1.055 * Math.pow(v, 1 / 2.4) - 0.055) * 255 + 0.5);
    }
};
exports.sign = function (n) { return (n < 0 ? -1 : 1); };
exports.signPow = function (val, exp) {
    return exports.sign(val) * Math.pow(Math.abs(val), exp);
};

});

var error = createCommonjsModule(function (module, exports) {
var __extends = (commonjsGlobal && commonjsGlobal.__extends) || (function () {
    var extendStatics = function (d, b) {
        extendStatics = Object.setPrototypeOf ||
            ({ __proto__: [] } instanceof Array && function (d, b) { d.__proto__ = b; }) ||
            function (d, b) { for (var p in b) if (b.hasOwnProperty(p)) d[p] = b[p]; };
        return extendStatics(d, b);
    };
    return function (d, b) {
        extendStatics(d, b);
        function __() { this.constructor = d; }
        d.prototype = b === null ? Object.create(b) : (__.prototype = b.prototype, new __());
    };
})();
Object.defineProperty(exports, "__esModule", { value: true });
var ValidationError = /** @class */ (function (_super) {
    __extends(ValidationError, _super);
    function ValidationError(message) {
        var _this = _super.call(this, message) || this;
        _this.name = "ValidationError";
        _this.message = message;
        return _this;
    }
    return ValidationError;
}(Error));
exports.ValidationError = ValidationError;

});

var decode_1 = createCommonjsModule(function (module, exports) {
Object.defineProperty(exports, "__esModule", { value: true });



/**
 * Returns an error message if invalid or undefined if valid
 * @param blurhash
 */
var validateBlurhash = function (blurhash) {
    if (!blurhash || blurhash.length < 6) {
        throw new error.ValidationError("The blurhash string must be at least 6 characters");
    }
    var sizeFlag = base83.decode83(blurhash[0]);
    var numY = Math.floor(sizeFlag / 9) + 1;
    var numX = (sizeFlag % 9) + 1;
    if (blurhash.length !== 4 + 2 * numX * numY) {
        throw new error.ValidationError("blurhash length mismatch: length is " + blurhash.length + " but it should be " + (4 + 2 * numX * numY));
    }
};
exports.isBlurhashValid = function (blurhash) {
    try {
        validateBlurhash(blurhash);
    }
    catch (error) {
        return { result: false, errorReason: error.message };
    }
    return { result: true };
};
var decodeDC = function (value) {
    var intR = value >> 16;
    var intG = (value >> 8) & 255;
    var intB = value & 255;
    return [utils.sRGBToLinear(intR), utils.sRGBToLinear(intG), utils.sRGBToLinear(intB)];
};
var decodeAC = function (value, maximumValue) {
    var quantR = Math.floor(value / (19 * 19));
    var quantG = Math.floor(value / 19) % 19;
    var quantB = value % 19;
    var rgb = [
        utils.signPow((quantR - 9) / 9, 2.0) * maximumValue,
        utils.signPow((quantG - 9) / 9, 2.0) * maximumValue,
        utils.signPow((quantB - 9) / 9, 2.0) * maximumValue
    ];
    return rgb;
};
var decode = function (blurhash, width, height, punch) {
    validateBlurhash(blurhash);
    punch = punch | 1;
    var sizeFlag = base83.decode83(blurhash[0]);
    var numY = Math.floor(sizeFlag / 9) + 1;
    var numX = (sizeFlag % 9) + 1;
    var quantisedMaximumValue = base83.decode83(blurhash[1]);
    var maximumValue = (quantisedMaximumValue + 1) / 166;
    var colors = new Array(numX * numY);
    for (var i = 0; i < colors.length; i++) {
        if (i === 0) {
            var value = base83.decode83(blurhash.substring(2, 6));
            colors[i] = decodeDC(value);
        }
        else {
            var value = base83.decode83(blurhash.substring(4 + i * 2, 6 + i * 2));
            colors[i] = decodeAC(value, maximumValue * punch);
        }
    }
    var bytesPerRow = width * 4;
    var pixels = new Uint8ClampedArray(bytesPerRow * height);
    for (var y = 0; y < height; y++) {
        for (var x = 0; x < width; x++) {
            var r = 0;
            var g = 0;
            var b = 0;
            for (var j = 0; j < numY; j++) {
                for (var i = 0; i < numX; i++) {
                    var basis = Math.cos((Math.PI * x * i) / width) *
                        Math.cos((Math.PI * y * j) / height);
                    var color = colors[i + j * numX];
                    r += color[0] * basis;
                    g += color[1] * basis;
                    b += color[2] * basis;
                }
            }
            var intR = utils.linearTosRGB(r);
            var intG = utils.linearTosRGB(g);
            var intB = utils.linearTosRGB(b);
            pixels[4 * x + 0 + y * bytesPerRow] = intR;
            pixels[4 * x + 1 + y * bytesPerRow] = intG;
            pixels[4 * x + 2 + y * bytesPerRow] = intB;
            pixels[4 * x + 3 + y * bytesPerRow] = 255; // alpha
        }
    }
    return pixels;
};
exports.default = decode;

});

var decode = /*@__PURE__*/unwrapExports(decode_1);

export default decode;
