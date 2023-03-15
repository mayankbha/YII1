var helpers = new (function () {
  this.generateRandomData = function (numElems, type, prediction) {
    var types = { linear, logarithmic, power, exponential };
    var fn = (types[type] || types.exponential)();
    var delta = 0;
    return new Array(numElems).fill(0).map(function (v, i) {
      return !i || (prediction == 'last' && i >= demo.NUM_NORMAL_ELEMS)
        ? null
        : fn(i) + helpers.rnd(delta);
    });
  };

  function linear() {
    var A = helpers.rnd(10) + 10,
      B = helpers.rnd(100),
      rnd = f(demo.NUM_ELEMS_4);
    function f(x) {
      return A * x + B;
    }
    return function (x) {
      return f(x) + Math.random() * rnd;
    };
  }
  function exponential() {
    var A = Math.random() * 10,
      B = Math.random() * 0.2 + 1e-2,
      rnd = f(demo.NUM_ELEMS_2);
    function f(x) {
      return A * Math.exp(x * B);
    }
    return function (x) {
      return f(x) + Math.random() * rnd;
    };
  }
  function logarithmic() {
    var A = helpers.rnd(200),
      B = helpers.rnd(50) + 1,
      rnd = f(0.5);
    function f(x) {
      return A + B * Math.log(x);
    }
    return function (x) {
      return f(x) + Math.random() * rnd;
    };
  }
  function power() {
    var A = Math.random() * 2 + 0.2,
      B = Math.random() * 2 + 0.2,
      rnd = f(demo.NUM_ELEMS_4);
    function f(x) {
      return A * Math.pow(x, B);
    }
    return function (x) {
      return f(x) + Math.random() * rnd;
    };
  }

  this.generateColors = function (numElems, id, alpha) {
    var normal = this.color(id, alpha);
    var predicted = 'rgba(136,136,136,' + alpha + ')';
    var colors = new Array(numElems);
    for (var i = 0; i < numElems; i++)
      colors[i] = i < demo.NUM_NORMAL_ELEMS ? normal : predicted;
    return colors;
  };

  this.rnd = function (max) {
    return Math.trunc(max * Math.random());
  };

  this.formatJson = function (cfg) {
    var s = JSON.stringify(cfg, jsonReplacer, 2)
      .replace(/"(\w+)":/g, '$1:')
      .replace(/\{[^{}}]+\}/gm, strReplacer)
      .replace(/\[[^\[\]]+\]/gm, strReplacer);
    return s;
    function jsonReplacer(k, v) {
      return k == '$$hashKey' ? undefined : v;
    }
    function strReplacer(g) {
      return g.length < 80 ? g.replace(/\s+/g, ' ') : g;
    }
  };

  this.color = function (id, alpha) {
    const c = new Color({ h: id * 16, s: 100, l: 50 });
    c.alpha(alpha);
    return c.rgbaString();
  }
})();
