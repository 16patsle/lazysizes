(function () {
    'use strict';

    const digitCharacters = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z", "#", "$", "%", "*", "+", ",", "-", ".", ":", ";", "=", "?", "@", "[", "]", "^", "_", "{", "|", "}", "~"];

    const decode83 = str => {
      let value = 0;

      for (let i = 0; i < str.length; i++) {
        const c = str[i];
        const digit = digitCharacters.indexOf(c);
        value = value * 83 + digit;
      }

      return value;
    };

    const utils = {};

    utils.sRGBToLinear = value => {
      const v = value / 255;

      if (v <= 0.04045) {
        return v / 12.92;
      } else {
        return ((v + 0.055) / 1.055) ** 2.4;
      }
    };

    utils.linearTosRGB = value => {
      const v = Math.max(0, Math.min(1, value));

      if (v <= 0.0031308) {
        return Math.round(v * 12.92 * 255 + 0.5);
      } else {
        return Math.round((1.055 * v ** (1 / 2.4) - 0.055) * 255 + 0.5);
      }
    };

    utils.signPow = (val, exp) => {
      return (val < 0 ? -1 : 1) * Math.abs(val) ** exp;
    };

    class ValidationError extends Error {
      constructor(message) {
        super(message);
        this.name = 'ValidationError';
        this.message = message;
      }

    }
    /**
     * Returns an error message if invalid or undefined if valid
     * @param blurhash
     */


    const validateBlurhash = blurhash => {
      if (!blurhash || blurhash.length < 6) {
        throw new ValidationError('The blurhash string must be at least 6 characters');
      }

      const sizeFlag = decode83(blurhash[0]);
      const numY = Math.floor(sizeFlag / 9) + 1;
      const numX = sizeFlag % 9 + 1;

      if (blurhash.length !== 4 + 2 * numX * numY) {
        throw new ValidationError(`blurhash length mismatch: length is ${blurhash.length} but it should be ${4 + 2 * numX * numY}`);
      }
    };

    const decodeDC = value => {
      const intR = value >> 16;
      const intG = value >> 8 & 255;
      const intB = value & 255;
      return [utils.sRGBToLinear(intR), utils.sRGBToLinear(intG), utils.sRGBToLinear(intB)];
    };

    const decodeAC = (value, maximumValue) => {
      const quantR = Math.floor(value / (19 * 19));
      const quantG = Math.floor(value / 19) % 19;
      const quantB = value % 19;
      const rgb = [utils.signPow((quantR - 9) / 9, 2.0) * maximumValue, utils.signPow((quantG - 9) / 9, 2.0) * maximumValue, utils.signPow((quantB - 9) / 9, 2.0) * maximumValue];
      return rgb;
    };

    const decode = (blurhash, width, height, punch) => {
      validateBlurhash(blurhash);
      punch = punch | 1;
      const sizeFlag = decode83(blurhash[0]);
      const numY = Math.floor(sizeFlag / 9) + 1;
      const numX = sizeFlag % 9 + 1;
      const quantisedMaximumValue = decode83(blurhash[1]);
      const maximumValue = (quantisedMaximumValue + 1) / 166;
      const colors = new Array(numX * numY);

      for (let i = 0; i < colors.length; i++) {
        if (i === 0) {
          const value = decode83(blurhash.substring(2, 6));
          colors[i] = decodeDC(value);
        } else {
          const value = decode83(blurhash.substring(4 + i * 2, 6 + i * 2));
          colors[i] = decodeAC(value, maximumValue * punch);
        }
      }

      const bytesPerRow = width * 4;
      const pixels = new Uint8ClampedArray(bytesPerRow * height);

      for (let y = 0; y < height; y++) {
        for (let x = 0; x < width; x++) {
          let r = 0;
          let g = 0;
          let b = 0;

          for (let j = 0; j < numY; j++) {
            for (let i = 0; i < numX; i++) {
              const basis = Math.cos(Math.PI * x * i / width) * Math.cos(Math.PI * y * j / height);
              const color = colors[i + j * numX];
              r += color[0] * basis;
              g += color[1] * basis;
              b += color[2] * basis;
            }
          }

          const intR = utils.linearTosRGB(r);
          const intG = utils.linearTosRGB(g);
          const intB = utils.linearTosRGB(b);
          pixels[4 * x + 0 + y * bytesPerRow] = intR;
          pixels[4 * x + 1 + y * bytesPerRow] = intG;
          pixels[4 * x + 2 + y * bytesPerRow] = intB;
          pixels[4 * x + 3 + y * bytesPerRow] = 255; // alpha
        }
      }

      return pixels;
    };

    // @ts-check

    /**
     * @type {CanvasDataObject[]}
     */
    const canvases = [];
    /**
     * Object containing a canvas and related objects and data
     * @typedef {Object} CanvasDataObject
     * @property {HTMLCanvasElement|OffscreenCanvas} [element] The canvas element
     * @property {CanvasRenderingContext2D|OffscreenCanvasRenderingContext2D} [ctx] The canvas rendering context
     * @property {ImageData} [imageData] The ImageData object used to render images
     * @property {boolean} [used] Whether this canvas is currently used
     */

    /**
     * Get a canvas from the pool, or create a new one if all are in use.
     * @param {number} width The requested canvas width
     * @param {number} height The requested canvas height
     * @returns {CanvasDataObject} An object with a usable canvas
     */

    function getCanvas(width, height) {
      const unusedCanvases = canvases.filter(canvas => canvas && canvas.used === false);
      let canvas = unusedCanvases[0];

      if (canvas) {
        canvas.ctx.clearRect(0, 0, canvas.element.width, canvas.element.height);
      } else {
        canvas = {};

        if (typeof window !== 'undefined' && window.document) {
          canvas.element = document.createElement('canvas');
        } else {
          canvas.element = new OffscreenCanvas(width, height);
        }

        canvas.ctx = canvas.element.getContext('2d');
        canvases.push(canvas);
      }

      canvas.element.width = width;
      canvas.element.height = height;
      canvas.imageData = canvas.ctx.createImageData(width, height);
      canvas.used = true;
      return canvas;
    }

    // @ts-check
    /**
     * Decode Blurhash string and return as blob.
     * @param {string} blurhash An encoded Blurhash string
     * @param {number} width The width of the image
     * @param {number} height The height of the image
     * @param {getBlurhashCallback} callback Function to call with the decoded image blob when finished
     */

    function getBlurhash(blurhash, width, height, callback) {
      const pixels = decode(blurhash, width, height);
      const canvas = getCanvas(width, height);
      canvas.imageData.data.set(pixels);
      canvas.ctx.putImageData(canvas.imageData, 0, 0);

      if (typeof HTMLCanvasElement !== 'undefined' && canvas.element instanceof HTMLCanvasElement) {
        // @ts-ignore
        if (canvas.element.msToBlob) {
          // @ts-ignore
          callback(canvas.element.msToBlob());
        } else {
          canvas.element.toBlob(blob => {
            canvas.used = false;
            callback(blob);
          });
        }
      } else if (typeof OffscreenCanvas !== 'undefined' && canvas.element instanceof OffscreenCanvas) {
        canvas.element.convertToBlob().then(blob => {
          canvas.used = false;
          callback(blob);
        });
      }
    }
    /**
     * Callback for when Blurhash image creation is complete
     * @callback getBlurhashCallback
     * @param {Blob} blob The decoded image blob
     */

    onmessage = event => {
      const [blurhash, width, height] = event.data;
      getBlurhash(blurhash, width, height, blob => {
        postMessage(blob);
      });
    };

}());
