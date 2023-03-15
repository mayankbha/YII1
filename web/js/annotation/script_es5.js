"use strict";

function _typeof(obj) { "@babel/helpers - typeof"; if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _get(target, property, receiver) { if (typeof Reflect !== "undefined" && Reflect.get) { _get = Reflect.get; } else { _get = function _get(target, property, receiver) { var base = _superPropBase(target, property); if (!base) return; var desc = Object.getOwnPropertyDescriptor(base, property); if (desc.get) { return desc.get.call(receiver); } return desc.value; }; } return _get(target, property, receiver || target); }

function _superPropBase(object, property) { while (!Object.prototype.hasOwnProperty.call(object, property)) { object = _getPrototypeOf(object); if (object === null) break; } return object; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

function _createSuper(Derived) { var hasNativeReflectConstruct = _isNativeReflectConstruct(); return function _createSuperInternal() { var Super = _getPrototypeOf(Derived), result; if (hasNativeReflectConstruct) { var NewTarget = _getPrototypeOf(this).constructor; result = Reflect.construct(Super, arguments, NewTarget); } else { result = Super.apply(this, arguments); } return _possibleConstructorReturn(this, result); }; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _isNativeReflectConstruct() { if (typeof Reflect === "undefined" || !Reflect.construct) return false; if (Reflect.construct.sham) return false; if (typeof Proxy === "function") return true; try { Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); return true; } catch (e) { return false; } }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _toConsumableArray(arr) { return _arrayWithoutHoles(arr) || _iterableToArray(arr) || _unsupportedIterableToArray(arr) || _nonIterableSpread(); }

function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _iterableToArray(iter) { if (typeof Symbol !== "undefined" && iter[Symbol.iterator] != null || iter["@@iterator"] != null) return Array.from(iter); }

function _arrayWithoutHoles(arr) { if (Array.isArray(arr)) return _arrayLikeToArray(arr); }

function _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }

function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }

function _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === "string") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === "Object" && o.constructor) n = o.constructor.name; if (n === "Map" || n === "Set") return Array.from(o); if (n === "Arguments" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }

function _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }

function _iterableToArrayLimit(arr, i) { var _i = arr && (typeof Symbol !== "undefined" && arr[Symbol.iterator] || arr["@@iterator"]); if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i["return"] != null) _i["return"](); } finally { if (_d) throw _e; } } return _arr; }

function _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }

/**
 * TODO tracker:
 * - when deleting annotations, need to deleted related popup annotations?
 */
function lpdf(p, i) {
  return String.fromCharCode.apply(null, p.slice(i, i + 100));
}

var viewerParams;
var scripts = document.getElementsByTagName('script');
var myurl = scripts[scripts.length - 1].src;
pdfjsLib.GlobalWorkerOptions.workerSrc = myurl.substr(0, myurl.lastIndexOf('/')) + '/pdf.worker.js';
var base_url = typeof baseUrl != 'undefined' ? baseUrl : ''; //let pdfUrl = 'output.pdf'

var coordinates = [];
var annotations = undefined;
var selectedAnnot = undefined;
var detectAngle = true;
var sidesorts = {
  title: 'Sort by:',
  items: [{
    text: 'Page position',
    fn: function fn(a, b) {
      return a.page == b.page && a.rect && b.rect && a.rect[3] && b.rect[3] ? b.rect[3] - a.rect[3] : a.page - b.page;
    }
  }, {
    text: 'Date',
    fn: function fn(a, b) {
      return a.page == b.page ? a.updateDate && b.updateDate ? getPdfDate(a.updateDate) - getPdfDate(b.updateDate) : 0 : a.page - b.page;
    }
  }, {
    text: 'Date (desc)',
    checked: true,
    fn: function fn(a, b) {
      return a.page == b.page ? a.updateDate && b.updateDate ? getPdfDate(b.updateDate) - getPdfDate(a.updateDate) : 0 : a.page - b.page;
    }
  }],
  onchange: refreshSidebar
};

function attachMenu(el, def, name) {
  //$('.menu').filter((i, el) => el.dataset.name == name).each((i, el) => el.remove())
  if (Array.isArray(def)) def = {
    items: def
  };
  var menu = $('<ul class="menu" />').appendTo(el);
  menu.attr('data-name', name);
  if (def.title) menu.append($('<li class="menu-title"/>').html(def.title));
  var checkable = def.items.filter(function (i) {
    return i.checked;
  }).length;
  if (checkable) menu.addClass('checkable');
  def.items.forEach(function (i) {
    var check = function check() {
      def.checked = i;
      li.addClass('checked');

      if (i.parentText) {
        if (!el.find('.menu-info').length) el.append($('<span class="menu-info"/>'));
        el.find('.menu-info').html(i.parentText);
      }
    };

    var li = $('<li/>').html(i.text).appendTo(menu);
    if (checkable) li.prepend($('<i class="fa fa-check"/>'));
    if (i.checked) check();
    li.on('click', function (e) {
      e.stopPropagation();

      if (checkable) {
        menu.find('> li').removeClass('checked');
        check();
      }

      if (def.onchange) def.onchange();
      menu.hide(100);
    });
  });
  el.
  /*css({ position: 'relative' }).*/
  click(function (e) {
    var box = el[0].getBoundingClientRect();
    menu.css({
      left: box.left + 'px',
      top: box.top + 'px'
    });
    if (menu.is(':visible')) $('body').off('mousedown.menu');else $('body').on('mousedown.menu', function (e) {
      if (elByClassAt(e.pageX, e.pageY, 'menu')) return;
      $('.menu').hide(100);
    });
    menu.toggle(200);
  });
}
/* constants and defs */


var stamps = [{
  stamps: ['Approved', 'Final', 'Completed'],
  color: '#416A1C',
  stops: ['#eeffee', '#69ff69']
}, {
  stamps: ['Draft', 'Confidential', 'ForPublicRelease', 'NotForPublicRelease', 'ForComment', 'PreliminaryResults', 'InformationOnly', 'AsIs', 'Departmental', 'Experimental'],
  color: '#244D7E',
  stops: ['#eeeeff', '#6969ff']
}, {
  stamps: ['NotApproved', 'Void', 'Expired', 'Sold', 'TopSecret'],
  color: '#A52A2A',
  stops: ['#ffeeee', '#ff6969']
}].map(function (x) {
  return x.stamps.map(function (s) {
    return {
      name: s,
      color: x.color,
      stops: x.stops
    };
  });
}).flat().reduce(function (dict, x) {
  dict[x.name] = x;
  return dict;
}, {});

function setupStampsOptions(select) {
  Object.keys(stamps).sort().forEach(function (stamp) {
    return select.append($('<option/>').val(stamp).text(camelSplit(stamp)));
  });
}

var commentTypes = ["Note", "Comment", "Key", "Help", "NewParagraph", "Paragraph", "Insert"];
var lineEndings = ["None", "Square", "Circle", "Diamond", "OpenArrow", "ClosedArrow"]; //, "Butt", "ROpenArrow", "RClosedArrow", "Slash"]

var types = {
  Text: {
    defaultColor: [1, 1, 0]
  },
  FreeText: {
    defaultColor: [1, 1, 0],
    font: true
  },
  Squiggly: {
    defaultColor: [0, 0, 1],
    text: true
  },
  Underline: {
    defaultColor: [1, 0, 0],
    text: true
  },
  StrikeOut: {
    defaultColor: [0, 0, 0],
    text: true
  },
  Highlight: {
    defaultColor: [1, 1, 0],
    text: true
  },
  Square: {
    defaultColor: [0, 0, 0],
    fill: true,
    border: true
  },
  Circle: {
    defaultColor: [0, 0, 0],
    fill: true,
    border: true
  },
  Stamp: {
    defaultColor: [0, 0, 0],
    nocolor: true
  },
  Ink: {
    defaultColor: [0, 0, 0],
    border: true
  },
  PolyLine: {
    defaultColor: [0, 0, 0],
    fill: true,
    border: true
  },
  Polygon: {
    defaultColor: [0, 0, 0],
    fill: true,
    border: true
  },
  Link: {
    defaultColor: [0, 0, 0]
  },
  Widget: {
    defaultColor: [0, 0, 0]
  },
  Caret: {
    defaultColor: [0, 0, 0]
  },
  Popup: {
    defaultColor: [0, 0, 0]
  }
};

function fixType(type) {
  return type[0] == '/' ? type.substr(1) : type;
}

function getType(type) {
  return types[fixType(type)] || {};
}
/* document & annotations */


var pdfFactory = undefined;

function isEditable(a) {
  return !a.object_id || viewerParams.authorAnnotation.isAdmin || a.userId == viewerParams.authorAnnotation.id;
}

function deleteAnnotation(a) {
  return pdfFactory.deleteAnnotation(a.id || a.object_id);
}

function createAnnotation(copy) {
  if (copy.object_id && !isEditable(copy)) return;
  var type = fixType(copy.type);
  var color = fixClrObj(copy.color || getType(type).defaultColor);
  var annot;

  if (type == 'FreeText') {
    copy.image = createTextImage(copy); //delete annot.rotate

    annot = pdfFactory.createFreeTextAnnotation(copy.page, copy.rect, copy.contents, copy.author, color);
    annot.rect_diff = [2, 2, 2, 2]; // make padding for text

    annot.defaultStyling = copy.defaultStyling;
  } else if (type == 'Stamp') annot = pdfFactory.createStampAnnotation(copy.page, copy.rect, copy.contents, copy.author, fixType(copy.stampType), color);else if (type == 'Ink') annot = pdfFactory.createInkAnnotation(copy.page, copy.rect || [], copy.contents, copy.author, copy.inkList, color);else if (type == 'PolyLine' || type == 'Polygon') annot = pdfFactory["create".concat(type, "Annotation")](copy.page, copy.rect || [], copy.contents, copy.author, copy.vertices, color);else if (type == 'Circle' || type == 'Square') {
    var rect = normalizePdfRect(copy.rect, type == 'Circle');
    annot = pdfFactory["create".concat(type, "Annotation")](copy.page, rect, copy.contents, copy.author, color);
  } else if (type == 'Text') {
    annot = pdfFactory.createTextAnnotation(copy.page, copy.rect, copy.contents, copy.author, color);
    annot.iconName = copy.iconName || annot.iconName;
  } else if (type == 'Highlight' || type == 'Underline' || type == 'Squiggly' || type == 'StrikeOut') {
    if (!copy.rect || copy.rect.length == 0) copy.rect = normalizePdfRect(rectFromArray(copy.quadPoints));
    annot = pdfFactory["create".concat(type, "Annotation")](copy.page, copy.rect, copy.contents, copy.author, color, copy.quadPoints);
  } else throw "Unsupported annotation type";

  annot.opacity = copy.opacity;

  if (annot.border && copy.border && (copy.border.border_width !== undefined || Array.isArray(copy.border))) {
    annot.border.border_width = copy.border.border_width !== undefined ? copy.border.border_width : copy.border[2];
  }

  if (copy.fill) annot.fill = fixClrObj(copy.fill);
  if (copy.rotate !== undefined) annot.rotate = copy.rotate;
  if (copy.line_ending) annot.line_ending = copy.line_ending;
  if (copy.appearance) annot.appearance = copy.appearance;
  if (copy.appearance_object) annot.appearance_object = copy.appearance_object;
  if (copy.image) annot.image = copy.image;
  if (viewerParams.authorAnnotation.id && annot.id.slice(-1)[0] == ')') annot.id = annot.id.slice(0, -1) + '_userid_' + viewerParams.authorAnnotation.id + ')';
}

function changeAnnotation(a, mod) {
  if (!isEditable(a)) return;
  var copy = Object.assign({}, a, mod); // fix appearance stream color

  if (mod.color && copy.appearance_object) {
    if (!copy.appearance) {
      alert('Not supported (yet)');
      return;
    }

    var idx = copy.appearance.indexOf(' RG');
    var start = copy.appearance.substr(0, idx); // check if it's our stream - starts with RG color def

    if (start.split(' ').filter(function (x) {
      return x;
    }).filter(function (s) {
      return isNaN(parseFloat(s));
    }).length == 0) copy.appearance = mod.color.join(' ') + ' ' + copy.appearance.substr(idx);
  }

  deleteAnnotation(a).then(function () {
    createAnnotation(copy);
    saveAndReload();
  });
}
/* viewer */


var pdfViewer = undefined;

function getPageDiv(page) {
  return pdfViewer._pages[page].div;
}

function getPageRotate(page) {
  return pdfViewer.getPageView(page).pdfPage.rotate;
}

function computePageOffset(page) {
  var pg = getPageDiv(page);
  var border = 9; // empiric

  var rect = pg.getBoundingClientRect();
  var bodyElt = document.body;
  return {
    top: rect.top + bodyElt.scrollTop + border,
    left: rect.left + bodyElt.scrollLeft + border
  };
}

function pointToPdf(x, y, page) {
  var ost = computePageOffset(page);
  return pdfViewer._pages[page].viewport.convertToPdfPoint(x - ost.left, y - ost.top);
}

function rectToPdf(rec, page) {
  if (Array.isArray(rec) && rec.length == 0) return [];
  var p1 = pointToPdf(rec.left, rec.top, page);
  var p2 = pointToPdf(rec.right, rec.bottom, page);
  return normalizePdfRect(p1.concat(p2));
}

function pdfrectToViewportSize(rect, page) {
  var viewport = pdfViewer._pages[page].viewport;

  var _viewport$convertToVi = viewport.convertToViewportPoint(rect[0], rect[1]),
      _viewport$convertToVi2 = _slicedToArray(_viewport$convertToVi, 2),
      x1 = _viewport$convertToVi2[0],
      y1 = _viewport$convertToVi2[1];

  var _viewport$convertToVi3 = viewport.convertToViewportPoint(rect[2], rect[3]),
      _viewport$convertToVi4 = _slicedToArray(_viewport$convertToVi3, 2),
      x2 = _viewport$convertToVi4[0],
      y2 = _viewport$convertToVi4[1];

  return {
    width: Math.abs(x2 - x1),
    height: Math.abs(y2 - y1)
  };
}

function pointToViewport(x, y, page) {
  var ost = computePageOffset(page);
  return pdfViewer._pages[page].viewport.convertToPdfPoint(x - ost.left, y - ost.top);
} // for Circle especially we need lower y first
// TODO: probably we should create correct coordinates in the first place in prepare()... but anyway


function normalizePdfRect(rect, reverseForCircle) {
  var _rect = _slicedToArray(rect, 4),
      x1 = _rect[0],
      y1 = _rect[1],
      x2 = _rect[2],
      y2 = _rect[3];

  return reverseForCircle ? [Math.min(x1, x2), Math.min(y1, y2), Math.max(x1, x2), Math.max(y1, y2)] : [Math.min(x1, x2), Math.max(y1, y2), Math.max(x1, x2), Math.min(y1, y2)];
}

function rectFromArray(points, expand) {
  expand = expand === undefined ? 2 : expand;
  var xs = points.filter(function (c, i) {
    return i % 2 == 0;
  });
  var ys = points.filter(function (c, i) {
    return i % 2 == 1;
  });
  return [Math.min.apply(Math, _toConsumableArray(xs)) - expand, Math.min.apply(Math, _toConsumableArray(ys)) - expand, Math.max.apply(Math, _toConsumableArray(xs)) + expand, Math.max.apply(Math, _toConsumableArray(ys)) + expand];
}

function inflateRect(r, d) {
  r = normalizePdfRect(r);
  return [r[0] - d, r[1] + d, r[2] + d, r[3] - d];
}

function getRotationAngle(el) {
  var st = window.getComputedStyle(el, null);
  var tr = st.getPropertyValue("-webkit-transform") || st.getPropertyValue("-moz-transform") || st.getPropertyValue("-ms-transform") || st.getPropertyValue("-o-transform") || st.getPropertyValue("transform") || "";
  if (!tr || tr == 'none') return 0;
  var values = tr.split('(')[1].split(')')[0].split(',').map(function (s) {
    return parseFloat(s);
  });

  var _values = _slicedToArray(values, 4),
      a = _values[0],
      b = _values[1],
      c = _values[2],
      d = _values[3];

  var scale = Math.sqrt(a * a + b * b); // arc sin, convert from radians to degrees, round

  var sin = b / scale; // next line works for 30deg but not 130deg (returns 50);
  // var angle = Math.round(Math.asin(sin) * (180/Math.PI));

  var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));
  return angle;
}
/*function fixPopupPosition() {
        // even for annotations with NoZoom pdf.js rotates the popup - so we revert it 
        // and also ensure it is not partially off-view
        let popup = this.querySelector('.popupWrapper')
        if (!popup) 
                return
        let container = $(popup).closest('#viewerContainer')[0]
        let page = container.getBoundingClientRect()
        popup.style.transform = `rotate(${-pdfViewer.pagesRotation}deg)`
        popup.style.transformOrigin = '0 0'
        let oldHidden = popup.hidden
        popup.hidden = false
        let box = $(popup).find('.popup')[0].getBoundingClientRect()
        popup.hidden = oldHidden
        if (box.left < page.left)
                popup.style.transform += ` translateX(${page.left - box.left + 20}px)`
        if (box.right > page.right)
                popup.style.transform += ` translateX(${-(box.right - page.right + 20)}px)`
}*/


function ensureAnnotationClass(stampType) {
  // create special stamp classes
  var styleId = 'pdfAnnotateStampClasses';
  var style = document.getElementById(styleId);

  if (!style) {
    style = document.createElement('style');
    style.id = styleId;
    document.head.appendChild(style);
  }

  var type = fixType(stampType);
  var label = type;
  if (type.indexOf('SB') == 0 || type.indexOf('SH') == 0) label = type.substr(2);
  var text = "section.stampAnnotation > div.stamp.".concat(type, "::after { content: \"").concat(camelSplit(label), "\"; }");
  if (style.textContent.indexOf(text) < 0) style.textContent += '\n' + text;
}

function debounce(fn, timeout) {
  return function () {
    var scope = this;
    var args = Array.prototype.slice.call(arguments);
    clearTimeout(scope.dataset.debounceTimerId);
    scope.dataset.debounceTimerId = setTimeout(function () {
      fn.apply(scope, args);
    }, timeout || 150);
  };
}

function tooltip(el, cfg) {
  /*$(el).tooltip({
          items: 'section',
          create: function(ev, ui) {
                  $(this).data("ui-tooltip").liveRegion.remove();
          },
          content: function() {
                  return getTooltipHtml.call(this)
          }
  })*/
  el = $(el);
  el.on('mouseenter', debounce(function (e) {
    var html = getTooltipHtml.call(this);
    if (!html) return;
    var rel = $(this).closest(cfg.addel);
    var box = this.getBoundingClientRect();
    var relbox = rel[0].getBoundingClientRect();
    var tip = $('<div class="ui-tooltip"/>').html(html).css({
      left: box.left - relbox.left + rel[0].scrollLeft + 'px',
      top: box.bottom - relbox.top + rel[0].scrollTop + 'px',
      opacity: 0
    }).appendTo($(this).closest(cfg.addel));
    var tipbox = tip[0].getBoundingClientRect();
    var cropbox = $(this.closest(cfg.cropel))[0] ? $(this.closest(cfg.cropel))[0].getBoundingClientRect() : {};
    if (tipbox.right > cropbox.right - 20) tip[0].style.transform += " translateX(".concat(-(tipbox.right - tipbox.left - 20), "px)");
    if (tipbox.bottom > cropbox.bottom - 20) tip[0].style.transform += " translateY(".concat(-(tipbox.bottom - tipbox.top + (box.bottom - box.top) + 20), "px)");
    tip.animate({
      opacity: 1
    }, 100);
  })).on('mouseleave', debounce(function (e) {
    var add = $(this).closest(cfg.addel);
    add.find('> .ui-tooltip').each(function () {
      $(this).fadeOut(100, function () {
        this.remove();
      });
    });
  }));
}

function getTooltipHtml() {
  var popup = $(this).find('.popupWrapper > .popup').first().clone(true);
  if (!popup.length) return '';
  var html = "<div class=\"title\"><b>".concat(popup.find('> h1').text() || '(User)', "</b> ").concat(popup.find('> span:first-of-type').text() || '', "</div>");
  popup.find('h1, span:first-of-type').each(function () {
    this.remove();
  });
  html += '<div class="body">' + popup.html() + '</div>';
  popup.remove();
  return html;
}

function fixAnnotations(page) {
  page = !page || !page.length ? $('.page') : page; //tooltip(page, { addel: '.page', relel: '#viewerContainer', content: getTooltipHtml })

  page.find('> .annotationLayer > section').each(function () {
    var pageAnnot = this;
    var id = pageAnnot.dataset.annotationId.replace(/R.*$/, '');
    var a = annotations.filter(function (x) {
      return x.object_id.obj == id;
    })[0];
    if (!a || pageAnnot.dataset.fixed) return;
    pageAnnot.dataset.fixed = '1';
    tooltip(pageAnnot, {
      addel: '.page',
      cropel: '#viewerContainer',
      content: getTooltipHtml
    });
    $(pageAnnot).find('.popupWrapper > .popup').hide();

    var ael = $(pageAnnot).children('*:not(.popupWrapper)')[0] || $(pageAnnot)[0];
    // var ael = $(pageAnnot).children('*:not(.popupWrapper)')[0];

    var saveAnnot = function saveAnnot() {
      var box = (ael || pageAnnot).getBoundingClientRect();
      var cfg = {
        rect: rectToPdf(box, a.page)
      };

      if (a.inkList || a.vertices) {
        var dx = cfg.rect[0] - a.rect[0];
        var dy = cfg.rect[1] - a.rect[1];
        if (a.inkList) cfg.inkList = a.inkList.map(function (arr) {
          return arr.map(function (z, i) {
            return i % 2 ? z + dy : z + dx;
          });
        });else cfg.vertices = a.vertices.map(function (z, i) {
          return i % 2 ? z + dy : z + dx;
        });
      }

      changeAnnotation(a, cfg);
    };

    $(pageAnnot).find('.popup > span[data-l10n-args]').each(function () {
      var json = JSON.parse(this.dataset.l10nArgs);
      var text = this.textContent;

      for (var k in json) {
        text = text.replace('{{' + k + '}}', json[k]);
      }

      this.textContent = text;
    });
    pageAnnot.addEventListener('dblclick', function (e) {
      var id = $(e.target).closest('section').data('annotation-id').replace('R', '');
      var card = $('.sidebar').find(".side-card[data-annotation-id=".concat(id, "]"));

      if (card.length) {
        onSideCardActivate(card);
        card.find('iframe').ifeditor('focus');
      }
    });

    if (a.opacity || a.opacity === 0) ael.style.opacity = a.opacity;
    var pageRotate = a.type == '/Text' ? getPageRotate(a.page) : 0;
    var angle = pageRotate - (a.rotate || 0); // this is a hack for css rotate### styles... can fix them instead?
    // TODO avoid this and fix css classes rotation instead

    if (pageRotate && a.type != '/Text') angle = 180 + angle;
    angle = fixAngle(angle);

    if (a.type == '/Text') {
      ael.src = base_url + 'img/annotation' + ael.src.substr(ael.src.lastIndexOf('/'));
      $(ael).show(); //.addClass('rotate' + angle)

      ael.style.transform = 'rotate(' + -(angle + pdfViewer.pagesRotation) + 'deg)';
    } else if (a.type == '/Stamp' && !a.appearance_object && a.stampType) {
      //let box = ael.getBoundingClientRect()
      //let page = $(pageAnnot).closest('.page')[0]
      //let pagebox = page.getBoundingClientRect()
      // add special classes .stamp.STAMPTYPE.rotateANGLE that append ::after element with text
      ensureAnnotationClass(a.stampType);
      $(ael).addClass('stamp').addClass(fixType(a.stampType)).addClass('rotate' + angle);
      ael.style.boxSizing = 'border-box'; // this is kind of hack, but almost always stamp width > height, so we take the one that's bigger

      var sz = Math.max($(ael).width(), $(ael).height()); // approximate instead of measure

      ael.style.fontSize = 1.5 * sz / a.stampType.length + 'px'; // this approach is to add div with background image to the page itself so that it is not affected by section tranform matrix
      // I think we can also add it to the section but attach bg image to the ::after element as above

      /*$('<div/>')//.attr('src', 'img/annotation/stamps/approved.png')
      .css({
              'background-image': 'url(img/annotation/stamps/approved.png)',
              'background-size': 'contain',
              'background-repeat': 'no-repeat',
              'background-position': 'center',
              transform: a.rotate ? 'rotate(' + a.rotate + 'deg)' : 'none',
              position: 'absolute',
              left: (box.left - pagebox.left) + 'px',
              top: (box.top - pagebox.top) + 'px',
              width: box.width + 'px',
              height: box.height + 'px'
      }).appendTo($(pageAnnot).closest('.page'))*/
      // Old approach was to measure text but it fails with rotation
      //let text = fixType(a.stampType)
      //text = camelSplit(text)

      /*let span = $('<span class="stamp"/>').text(text).appendTo($(ael))
      let fsz = 0;
      let w = 0
      let lastw
      const sz = pdfViewer.pagesRotation % 180 == 0 ? $(ael).width() : $(ael).height()
      while (w < sz) {//} && w != lastw) {
              ++fsz
              ael.style.fontSize = fsz + 'px'
              lastw = w
              w = measureText(ael, text).width
      }
      ael.querySelector('.stamp').style.color = a.color ? rgbcss(a.color) : 'yellow'
      ael.querySelector('.stamp').style.borderColor = a.color ? rgbcss(a.color) : 'yellow'
      ael.querySelector('.stamp').style.textShadow = '1px 1px 2px ' +(a.color ? rgbcss(a.color) : 'yellow') + '88'*/
    } else if (a.type == '/FreeText') {
      var resizer = $('<div class="resizer fa fa-grip-vertical"/>').appendTo(pageAnnot);
      var resel = $(ael);
      draggable(resizer[0], {
        start: function start() {
          resel.css({
            border: '1px solid black '
          });
          resizer.prop('initialWidth', resel.width()).prop('initialHeight', resel.height());
        },
        apply: function apply(el, x, y, dx, dy, sx, sy) {
          resel.width(resel.width() + dx).height(resel.height() + dy);
        },
        stop: saveAnnot
      });

      if (!a.appearance_object) {
        ael.style.border = bordercss(a.border, 'black');
        ael.style.background = a.color ? rgbcss(a.color) : "yellow";
        ael.style.overflow = 'hidden';
        var fsz = getAnnotFontSize(a) || 9;
        var scale = ael.getBoundingClientRect().width / ael.offsetWidth;
        ael.style.fontSize = fsz / scale + 'pt';
        ael.style.whiteSpace = 'pre-wrap';
        ael.style.display = 'flex';
        ael.setAttribute('data-contents', a.contents || '');
        $(ael).addClass('rotate' + angle);
      }
    } else if (a.type == '/Circle' || a.type == '/Square' || a.type == '/Ink' || a.type == '/PolyLine' || a.type == '/Polygon') {
      var selectors = {
        '/Ink': 'polyline',
        '/Circle': 'ellipse',
        '/Square': 'rect',
        '/Polygon': 'polygon',
        '/PolyLine': 'polyline'
      };
      var selector = selectors[a.type];
      var svg = $(pageAnnot).find('> svg').css({
        overflow: 'visible'
      }); // make big line markers visible

      var el = svg.find('> ' + selector);
      var drawing = el.css({
        stroke: rgbcss(a.color || [0, 0, 0])
      }); // almost invisible but hovering will work... not sure if we need that hovering, though

      if (selector != 'polyline') drawing.css({
        fill: rgbcss(a.fill || '#00000001')
      });

      if (a.line_ending && selector == 'polyline') {
        var defs = document.getElementById('drawingLayer').querySelector('defs');
        var newdefs = defs.cloneNode();
        a.line_ending.forEach(function (le, index) {
          var tpl = defs.querySelector('#' + fixType(le).toLowerCase() + '_tpl');
          if (!tpl) return;
          var def = tpl.cloneNode(true);
          def.setAttribute('id', a.object_id.obj + '_' + index);
          if (tpl.firstElementChild.getAttribute('fill') != 'transparent') def.firstElementChild.setAttribute('fill', rgbcss(a.fill || 'transparent'));
          def.firstElementChild.setAttribute('stroke', drawing.css('stroke'));
          newdefs.appendChild(def); // we reverse points as pdf.js seems to reverse draw polylines...

          el.each(function () {
            this.setAttribute('marker-' + (!index ? 'start' : 'end'), 'url(#' + def.id + ')');
          });
        });
        svg.prepend(newdefs);
      }
    } // text is not movable


    if (!getType(a.type).text && isEditable(a)) {
      var allowed = function allowed(x, y) {
        var els = _toConsumableArray(document.elementsFromPoint(x, y));

        return els.indexOf(getPageDiv(a.page).firstChild) >= 0;
      };

      draggable(pageAnnot, {
        stop: saveAnnot,
        when: function when(e) {
          return !current && !$(e.target).is('.resizer');
        },
        allowed: allowed
      });
    } //$(pageAnnot).on('mouseenter', fixPopupPosition)

  });
}

function draggable(el, cfg) {
  var p;
  var start; //let scroller = $(el).parents().toArray().filter(x => $(x).css('scroll-y') == 'scroll')[0];

  $(el).on('mousedown', function (e) {
    e.preventDefault();
  }).on('pointerdown', function (e) {
    if (cfg.when && cfg.when(e) === false) return;
    $('.popupWrapper').hide();
    p = {
      x: e.pageX,
      y: e.pageY
    };
    start = p;
    el.setPointerCapture(e.pointerId);
    cfg.start && cfg.start();
  }).on('mousemove', function (e) {
    if (!p) return;
    $(el).addClass('dragging');
    $('body').addClass('noselection');
    var box = this.getBoundingClientRect();
    var left = box.left,
        top = box.top;
    var dx = e.pageX - p.x;
    var dy = e.pageY - p.y;
    var newLeft = box.left + dx;
    var newTop = box.top + dy;
    var allowed = !cfg.allowed || cfg.allowed(newLeft, newTop) && cfg.allowed(newLeft + box.width, newTop + box.height);

    if (allowed) {
      left = (this.style.left ? +this.style.left.replace('px', '') : left) + (e.pageX - p.x);
      top = (this.style.top ? +this.style.top.replace('px', '') : top) + (e.pageY - p.y);
      if (cfg.apply) cfg.apply(this, left, top, dx, dy, start.x, start.y);else {
        this.style.left = left + 'px';
        this.style.top = top + 'px';
      }
      p = {
        x: e.pageX,
        y: e.pageY
      };
    }
  }).on('pointerup', function (e) {
    if (e.originalEvent.handled) return;
    el.releasePointerCapture(e.pointerId);
    $(el).removeClass('dragging');
    $('body').removeClass('noselection');
    p = null;
    if (!start || start.x == e.pageX && start.y == e.pageY) return;
    e.originalEvent.handled = true;
    cfg.stop();
  });
}

function loadDocument(dataOrUrl, isnew) {
  onSideCardDeactivate();
  if (typeof dataOrUrl == 'string') setDocName(dataOrUrl);
  var loadingTask = pdfjsLib.getDocument(typeof dataOrUrl == 'string' ? {
    url: dataOrUrl
  } : {
    data: dataOrUrl
  });
  loadingTask.promise.then(function (pdfDocument) {
    pdfDocument._pdfInfo.fingerprint = "constant";
    pdfDocument.getData().then(function (data) {
      if (isnew) loadFactory(data);
    });

    var _document$getElementB = document.getElementById('viewerContainer'),
        scrollLeft = _document$getElementB.scrollLeft,
        scrollTop = _document$getElementB.scrollTop;

    var page = pdfViewer.currentPageNumber;
    var scale = pdfViewer.currentScale;
    var rotate = pdfViewer.pagesRotation;

    if (pdfViewer._location) {
      pdfViewer._location.pageNumber = pdfViewer.currentPageNumber;
      pdfViewer._location.scale = pdfViewer.currentScale;
    }

    pdfViewer.setDocument(pdfDocument);
    if (pdfViewer.pagesCount) pdfViewer.currentScale = scale;
    pdfViewer.eventBus.on('pagesinit', function () {
      pdfViewer.currentPageNumber = isnew ? 1 : page;
      pdfViewer.currentScale = isnew ? 1 : scale;
      pdfViewer.pagesRotation = isnew ? 0 : rotate;
      document.getElementById('viewerContainer').scrollLeft = scrollLeft;
      document.getElementById('viewerContainer').scrollTop = scrollTop;
    });
  });
}

function loadFactory(data) {
  pdfFactory = new pdfAnnotate.AnnotationFactory(data);
  reloadAnnotations();
}

function reloadAnnotations() {
  pdfFactory.getAnnotations().then(function (alist) {
    annotations = alist.flat().filter(function (a) {
      return a.type;
    });
    var toDelete = (pdfFactory.toDelete || []).map(function (a) {
      return a.object_id.obj;
    });
    annotations = annotations.filter(function (a) {
      return toDelete.indexOf(a.object_id.obj) < 0;
    }); // Filter out links without any annotation data

    annotations = annotations.filter(function (a) {
      return !(a.type == '/Link' && !a.contents && !a.author && !a.appearance_object);
    });
    annotations.forEach(function (a) {
      // id is for new, name is for loaded
      var id = a.id || a.name;
      var m = id ? id.match(/_userid_(\d+)/) : null;
      a.userId = m ? m[1] : null;
    });
    refreshSidebar();
    fixAnnotations();
  });
}

function setDocName(url) {
  var index = url.lastIndexOf('/');
  var name = index ? url.substr(index + 1) : url;
  $('.filename').text(name);
}

function openDocument(e) {
  detectAngle = true;
  var file = e.target.files[0];
  viewerParams.originFileName = file.name;

  viewerParams.saveToDataBase = function () {
    alert('This is not a database document');
  };

  setDocName(viewerParams.originFileName);
  var reader = new FileReader();
  reader.addEventListener('load', function (e) {
    loadDocument(new Uint8Array(e.target.result), true); //pdfViewer.currentScale = 1
    //pdfViewer.pagesRotation = 0
  });
  reader.readAsArrayBuffer(file);
}

function saveAndReload() {
  var saved = pdfFactory.write();
  loadDocument(saved); // we don't need the data from the loaded document - load factory right now
  //loadFactory(saved)

  reloadAnnotations();
}

function updatePageNumber() {
  $('#pagenum').val(pdfViewer.currentPageNumber);
  $("#page").text(" / ".concat(pdfViewer.pagesCount, " "));
}

function goToPage(page, offset) {
  page = page || pdfViewer.currentPageNumber + offset;

  if (pdfViewer.currentPageNumber != page) {
    pdfViewer.currentPageNumber = page;

    var scrollHandler = function scrollHandler() {
      var pageDiv = $('.page[data-page-number="' + pdfViewer.currentPageNumber + '"]')[0];
      if (!pageDiv) pdfViewer.eventBus.on('pagesinit', scrollHandler);else {
        $('#viewerContainer').animate({
          scrollTop: $('.page[data-page-number="' + pdfViewer.currentPageNumber + '"]')[0].offsetTop - 30
        }, 200);
        pdfViewer.eventBus.off('pagesinit', scrollHandler);
      }
    };

    scrollHandler();
  }

  updatePageNumber();
}

function fixAngle(angle) {
  while (angle >= 360) {
    angle -= 360;
  }

  while (angle < 0) {
    angle += 360;
  }

  return angle;
}

function rotate(angle) {
  angle = pdfViewer.pagesRotation + angle;
  angle = fixAngle(angle);
  pdfViewer.pagesRotation = angle;
  var rules = document.getElementById('pdfPopupRotationRules');

  if (!rules) {
    rules = document.createElement('style');
    rules.id = 'pdfPopupRotationRules';
    rules.type = 'text/css';
    document.head.appendChild(rules);
  } // avoid the popup being rotated
  //$(rules).text('.popupWrapper .popup { transform: rotate(' + (-pdfViewer.pagesRotation) + 'deg')

}

var scrollBeforeFind;

function find(e) {
  if (e.code == 'Enter') {
    if (e.target.value && scrollBeforeFind === undefined) scrollBeforeFind = $('#viewerContainer')[0].scrollTop; // find options: search, phraseSearch, caseSensitive, entireWord, highlightAll, findPrevious

    pdfViewer.findController.executeCommand('findagain', {
      query: e.target.value,
      phraseSearch: true,
      highlightAll: true,
      findPrevious: e.shiftKey
    });
    findscroll();
  } else if (e.code == 'Escape') {
    e.target.value = '';
    pdfViewer.findController.executeCommand('findagain', {
      query: ''
    });
    findscroll();
  }
}

var findScrollTimerId;

function findscroll() {
  clearTimeout(findScrollTimerId);
  findScrollTimerId = setTimeout(function () {
    var highlight = $('span.highlight.selected')[0];
    if (highlight) highlight.scrollIntoView({
      behavior: 'smooth'
    });else if (scrollBeforeFind !== undefined) {
      $('#viewerContainer').animate({
        scrollTop: scrollBeforeFind
      });
      scrollBeforeFind = undefined;
    }
  }, 100);
}

function setupViewer(url) {
  var pdfContainer = document.getElementById('viewerContainer');
  var eventBus = new pdfjsViewer.EventBus();
  var pdfLinkService = new pdfjsViewer.PDFLinkService({
    eventBus: eventBus
  });
  var pdfFindController = new pdfjsViewer.PDFFindController({
    eventBus: eventBus,
    linkService: pdfLinkService
  });
  pdfViewer = new pdfjsViewer.PDFViewer({
    container: pdfContainer,
    eventBus: eventBus,
    // issues with findcontroller - fix
    //linkService: pdfLinkService,
    findController: pdfFindController
  });

  pdfViewer._buffer.resize(10000, 10000);

  pdfLinkService.pdfViewer = pdfViewer;
  pdfViewer.eventBus.on('pagesinit', function (e) {
    pdfViewer.pagesRotation = 0;
    pdfLinkService.setDocument(pdfViewer.pdfDocument);
    pdfFindController.setDocument(pdfViewer.pdfDocument);
  });
  pdfViewer.eventBus.on('updatefindmatchescount', function (data) {
    $('.matches').text("".concat(data.matchesCount.current, "/").concat(data.matchesCount.total));
    findscroll();
  });
  pdfViewer.eventBus.on('updatefindcontrolstate', function (data) {
    $('.matches').text("".concat(data.matchesCount.current, "/").concat(data.matchesCount.total));
    findscroll();
  });
  pdfViewer.eventBus.on('textlayerrendered', function (e) {
    if (e.pageNumber && detectAngle) {
      var angles = e.source.textDivs.map(getElAngle);
      var counts = angles.reduce(function (p, x) {
        p[x] = (p[x] || 0) + 1;
        return p;
      }, {});

      for (var k in counts) {
        if (k != '0' && counts[k] > angles.length / 2) pdfViewer.pagesRotation = 360 - +k;
      }

      detectAngle = false;
    } // comment icons should not be rotated
    //getPageDiv(e.pageNumber - 1).querySelectorAll('section.textAnnotation > img').forEach(img => 
    //        img.style.transform = 'rotate(' + (-pdfViewer.pagesRotation) + 'deg)')


    updatePageNumber();
    fixAnnotations(getPageDiv(e.pageNumber - 1));
    refreshSelectedAnnot();
  });
  pdfViewer.eventBus.on('pagechanging', function (e) {
    updatePageNumber();

    for (var page = pdfViewer.currentPageNumber; page > 0; page--) {
      var sep = $(".sidebar-page-sep[data-page=".concat(pdfViewer.currentPageNumber, "]"))[0];

      if (sep) {
        sep.scrollIntoView();
        break;
      }
    }

    fixAnnotations(getPageDiv(e.pageNumber - 1));
  });
  loadDocument(url, true);
  document.addEventListener('wheel', function (e) {
    if (e.ctrlKey) {
      e.preventDefault();
      pdfViewer.currentScale += e.deltaY * -0.001;
    }
  }, {
    passive: false
  });
  draggable($('.sidebar-splitter')[0], {
    when: function when() {
      return current == null;
    },
    apply: function apply(el, left, top) {
      //el.style.left = left + 'px'
      document.documentElement.style.setProperty('--sidebar-width', left + 'px'); //$('.sidebar').width(left)
      //$('#main').width($('body').width() - left).css({ left: left + 'px' })
    },
    stop: function stop() {}
  });
} // keep the document with annotations when we hide them - used to restore


var dataWithAnnotations;

function toggleAnnotations(btn) {
  var icon = $('#toggleAnnotations').find('i');

  if (icon.hasClass('fa-eye-slash')) {
    $('.toolbar').children().show();
    icon.removeClass('fa-eye-slash').addClass('fa-eye');
    loadDocument(dataWithAnnotations);
    loadFactory(dataWithAnnotations);
    dataWithAnnotations = null;
  } else {
    $('.toolbar').children().hide();
    icon.removeClass('fa-eye').addClass('fa-eye-slash');
    if (current) current.stop();
    dataWithAnnotations = pdfFactory.write();
    pdfFactory.getAnnotations().then(function (alist) {
      Promise.allSettled(alist.flat().map(deleteAnnotation)).then(function () {
        saveAndReload();
      });
    });
  }
}
/* utility */


function getAnnotFontSize(annot) {
  if (!annot.defaultStyling) return null;
  var pt = annot.defaultStyling.split(';').map(function (x) {
    return x.trim();
  }).filter(function (x) {
    return x.indexOf('font:') == 0;
  }).map(function (x) {
    return x.match(/([-+]?[0-9]*\.?[0-9])+pt/);
  })[0];
  return pt ? pt[1] : null;
}

function elByClassAt(x, y, cls) {
  return document.elementsFromPoint(x, y).filter(function (e) {
    return e.classList.contains(cls);
  })[0];
}

function camelSplit(s) {
  return s.replace(/([a-z])([A-Z])/g, '$1 $2');
}

function parseColor(clr) {
  if (!clr) return clr;
  if (clr[0] == '#') return clr.match(/\w\w/g).map(function (x) {
    return parseInt(x, 16) / 255.0;
  });
  throw "Color type not supported";
}

function fixClrObj(arr) {
  if (!arr || arr.r !== undefined && arr.g !== undefined && arr.b !== undefined) return arr;
  if (!Array.isArray(arr)) arr = parseColor(arr);
  return {
    r: arr[0],
    g: arr[1],
    b: arr[2]
  };
}

function rgbcss(color, opacity) {
  if (!color) return '#000000';
  if (typeof color == 'string') return color;
  return '#' + color.map(function (c) {
    var h = (c * 255).toString(16);
    return h.length === 1 ? '0' + h : h;
  }).join('');
}

function bordercss(border, color) {
  if (!border) return 'none';
  var w = border.border_width || border[2];
  return w + 'px solid ' + rgbcss(color);
}

function getPdfDate(date) {
  if (!date) return '';
  date = date.replace('(D:', '').replace('D:', '');
  date = date.substr(0, 4) + '-' + date.substr(4, 2) + '-' + date.substr(6, 2) + 'T' + date.substr(8, 2) + ':' + date.substr(10, 2) + ':' + date.substr(12, 2) + 'Z';
  return new Date(date);
}

function fmtPdfDate(date) {
  if (!date) return '';
  date = date.replace('(D:', '').replace('D:', '');
  date = date.substr(0, 4) + '-' + date.substr(4, 2) + '-' + date.substr(6, 2);
  date = new Date(date).toLocaleDateString();
  return date;
}

function getElAngle(el) {
  var m = el.style.transform.match(/rotate\((-*\d+)deg\)/);
  var angle = m ? parseInt(m[1]) : 0;
  if (angle < 0) angle += 360;
  return angle;
}

function measureText(node, text) {
  // TODO: measure without inserting
  var cloned = node.cloneNode();
  cloned.style.width = "auto";
  cloned.style.height = "auto";
  cloned.style.display = "inline";
  cloned.style.transform = cloned.style.transform.replace(/rotate\(.*?\)/, '');
  cloned.textContent = text;
  node.parentElement.appendChild(cloned);

  var _cloned$getBoundingCl = cloned.getBoundingClientRect(),
      width = _cloned$getBoundingCl.width,
      height = _cloned$getBoundingCl.height;

  cloned.remove();
  return {
    width: width,
    height: height
  };
}

function shiftRectLeft(r, w, a) {
  switch (a) {
    //pdfViewer.pagesRotation) {
    case 0:
      r.left += w;
      break;

    case 90:
      r.top += w;
      break;

    case 180:
      r.right -= w;
      break;

    case 270:
      r.bottom -= w;
      break;
  }

  return r;
}

function shiftRectRight(r, w, a) {
  switch (a) {
    //pdfViewer.pagesRotation) {
    case 0:
      r.right -= w;
      break;

    case 90:
      r.bottom -= w;
      break;

    case 180:
      r.left += w;
      break;

    case 270:
      r.top += w;
      break;
  }

  return r;
}

function createTextSelectionAppearance(type, angle, rect, pagenum) {
  // for simplicity (to avoid text/page rotation combinations multiplication) 
  // we draw for text as it is on screen and then create line between two points converted to pdf
  var l = rect.left,
      t = rect.top,
      r = rect.right,
      b = rect.bottom;
  var line = []; // not sure how to organize it except for switches...

  switch (type) {
    case 'Underline':
      switch (angle) {
        case 0:
          line = [l, b, r, b];
          break;

        case 90:
          line = [l, t, l, b];
          break;

        case 180:
          line = [l, t, r, t];
          break;

        case 270:
          line = [r, t, r, b];
          break;
      }

      break;

    case 'StrikeOut':
      switch (angle) {
        case 0:
          line = [l, b + (t - b) * 2 / 3, r, b + (t - b) * 2 / 3];
          break;

        case 180:
          line = [l, b + (t - b) / 3, r, b + (t - b) / 3];
          break;

        case 90:
          line = [l + (r - l) * 0.6, t, l + (r - l) * 0.6, b];
          break;

        case 270:
          line = [l + (r - l) * 0.4, t, l + (r - l) * 0.4, b];
          break;
      }

      break;

    case 'Squiggly':
      switch (angle) {
        case 0:
        case 180:
          {
            var off = angle == 0 ? -2 : 2;
            var y = angle == 0 ? b + off : t + off;
            line = [];

            for (var n = 0; n < (r - l) / 8; n++) {
              var x = l + n * 8;
              line = line.concat([x, y + -off * 2, x + 4, y]);
            }
          }
          break;

        case 90:
        case 270:
          {
            var _off = angle == 270 ? -2 : 2;

            var _x = angle == 270 ? r + _off : l + _off;

            line = [];

            for (var _n2 = 0; _n2 < (b - t) / 8; _n2++) {
              var _y = t + _n2 * 8;

              line = line.concat([_x + 4, _y, _x, _y + 4]);
            }
          }
          break;
      }

      break;
  }

  var pfmt = function pfmt(p) {
    return p.map(function (x) {
      return x.toFixed(2);
    }).join(' ');
  };

  if (line.length == 4) {
    var p1 = pointToPdf(line[0], line[1], pagenum);
    var p2 = pointToPdf(line[2], line[3], pagenum);
    return "".concat(pfmt(p1), " m\n").concat(pfmt(p2), " l");
  } else {
    var cmds = [];

    for (var i = 0; i < line.length / 2; i++) {
      var p = pointToPdf(line[i * 2], line[i * 2 + 1], pagenum);
      if (i == 0) cmds.push("".concat(pfmt(p), " m"));
      cmds.push("".concat(pfmt(p), " l"));
    }

    return cmds.join('\n');
  }
}

function selectionQuads(type, range) {
  // convert selection into array of quads - rect coords for each line or run
  range = range || window.getSelection().getRangeAt(0);
  var node = range.startContainer;
  var endNode = range.endContainer;
  if (endNode.nodeType == Node.TEXT_NODE) endNode = endNode.parentElement;
  var lastNode = node;
  var quads = [];
  var stream = []; // appearance stream commands

  var pagenum = $(node).closest('.page').data('pageNumber') - 1;

  do {
    if (node.nodeType == Node.TEXT_NODE) node = node.parentElement;
    var rect = node.getBoundingClientRect();
    rect = {
      left: rect.left,
      top: rect.top,
      right: rect.right,
      bottom: rect.bottom
    };

    if (node == range.startContainer.parentElement) {
      var startDw = measureText(node, node.textContent.substr(0, range.startOffset)).width;
      rect = shiftRectLeft(rect, startDw, getElAngle(node));

      if (node == endNode) {
        var endDw = measureText(node, node.textContent.substr(range.endOffset)).width;
        rect = shiftRectRight(rect, endDw, getElAngle(node));
      }
    } else if (node == range.endContainer.parentElement) {
      var _endDw = measureText(node, node.textContent.substr(range.endOffset)).width;
      rect = shiftRectRight(rect, _endDw, getElAngle(node));
    }

    var _rectToPdf = rectToPdf(rect, pagenum),
        _rectToPdf2 = _slicedToArray(_rectToPdf, 4),
        x1 = _rectToPdf2[0],
        y1 = _rectToPdf2[1],
        x2 = _rectToPdf2[2],
        y2 = _rectToPdf2[3];

    quads = quads.concat([x1, y1, x2, y1, x1, y2, x2, y2]); // for rotated text we need to created appearance streams and calculate what side to use

    var angle = getElAngle(node);

    if (angle || pdfViewer.pagesRotation) {
      var cmd = createTextSelectionAppearance(type, angle, rect, pagenum);
      stream.push(cmd);
    }

    lastNode = node;
    node = node.nextSibling;
  } while (node && lastNode != endNode && range.startContainer != range.endContainer);

  return {
    quads: quads,
    appearance: stream.join('\n'),
    page: pagenum
  };
}
/* actions */


var current = null;

var Action = /*#__PURE__*/function () {
  function Action(type, annot) {
    var _this = this;

    _classCallCheck(this, Action);

    if (current) current.stop();
    current = this;
    this.type = type;
    this.annot = annot;
    this.params = {};
    this.points = [];
    $('body').addClass('noselection');
    $(window).on('keydown.action', function (e) {
      return e.code == 'Escape' ? _this.stop() : undefined;
    });
    $('.page').on('mousedown.action', function (e) {
      return _this.ondown(e);
    });
    $('.page').on('mousemove.action', function (e) {
      return _this.onmove(e);
    });
    $('.page').on('mouseup.action', function (e) {
      return _this.onup(e);
    });
    $('.page').addClass(this.constructor.name.replace('Action', '').toLowerCase() + '-action'); //this.color = getType(this.type) ? rgbcss(getType(this.type).defaultColor) : '#000000'
  }

  _createClass(Action, [{
    key: "setParams",
    value: function setParams(params) {
      this.params = params || {};
      this.params.border_width = this.params.border ? this.params.border.border_width : 1;
      this.updatePlaceholder();
    }
  }, {
    key: "updatePlaceholder",
    value: function updatePlaceholder() {}
  }, {
    key: "start",
    value: function start() {
      this.started = true;
      this.points = [];
    }
  }, {
    key: "stop",
    value: function stop() {
      $('body').removeClass('noselection');
      $(window).off('keydown.action');
      $('.page').off('mousedown.action');
      $('.page').off('mousemove.action');
      $('.page').off('mouseup.action');
      $('.page').removeClass(this.constructor.name.replace('Action', '').toLowerCase() + '-action');
      onSideCardDeactivate();
      current = null;
      this.points = [];
    }
  }, {
    key: "save",
    value: function save() {
      saveAndReload();
      this.stop();
    }
  }, {
    key: "prepare",
    value: function prepare(annot) {
      var def = getType(this.type) || {};
      var rect = rectToPdf(this.rect(), this.page);
      if (annot && annot.border) rect = inflateRect(rect, annot.border.border_width);
      return Object.assign(this.annot || {}, annot || {}, {
        page: this.page,
        type: this.type,
        rect: rect,
        author: viewerParams.authorAnnotation.name || 'Author',
        color: annot.color || def.defaultColor || [0, 0, 0]
      });
    }
  }, {
    key: "exec",
    value: function exec() {
      var _this2 = this;

      this.started = false;
      showPopBar(this.points[this.points.length - 1], {
        type: this.type,
        ok: function ok(info) {
          createAnnotation(_this2.prepare(info));

          _this2.save();
        },
        cancel: function cancel() {
          return _this2.stop();
        }
      });
    }
  }, {
    key: "rectFromPoints",
    value: function rectFromPoints(points, exact) {
      var xs = points.map(function (p) {
        return p.x;
      });
      var ys = points.map(function (p) {
        return p.y;
      });
      var off = typeof exact == 'number' ? exact : exact ? 0 : 2;
      return {
        left: Math.min.apply(Math, _toConsumableArray(xs)) - off,
        top: Math.min.apply(Math, _toConsumableArray(ys)) - off,
        right: Math.max.apply(Math, _toConsumableArray(xs)) + off,
        bottom: Math.max.apply(Math, _toConsumableArray(ys)) + off
      };
    }
  }, {
    key: "rect",
    value: function rect(exact) {
      var p1 = this.points[0];
      var p2 = this.points[this.points.length - 1];
      return this.rectFromPoints([p1, p2], exact);
    }
  }, {
    key: "isvalid",
    value: function isvalid(p) {
      var els = _toConsumableArray(document.elementsFromPoint(p.x, p.y));

      return this.page === undefined || els.indexOf(getPageDiv(this.page).firstChild) >= 0;
    }
  }, {
    key: "beforedown",
    value: function beforedown(e, p) {
      // most types except e.g. Ink don't support multiple rects
      return this.points.length === 0 && this.isvalid(p);
    }
  }, {
    key: "beforemove",
    value: function beforemove(e, p) {
      return this.isvalid(p);
    }
  }, {
    key: "down",
    value: function down(e, p) {}
  }, {
    key: "move",
    value: function move(e, p) {}
  }, {
    key: "up",
    value: function up(e, p) {
      this.exec();
    }
  }, {
    key: "ondown",
    value: function ondown(e) {
      var p = {
        x: e.pageX,
        y: e.pageY,
        action: 'start'
      };
      if (!this.beforedown(e, p)) return;

      if (!this.page) {
        this.pagediv = document.elementsFromPoint(p.x, p.y).filter(function (e) {
          return e.classList.contains('page');
        })[0];
        this.page = this.pagediv.dataset.pageNumber - 1;
      }

      this.start();
      this.points.push(p);
      this.down(e, p);
    }
  }, {
    key: "onmove",
    value: function onmove(e) {
      if (!this.started) return;
      var p = {
        x: e.pageX,
        y: e.pageY,
        action: 'move'
      };
      if (!this.beforemove(e, p)) return;
      this.points.push(p);
      this.move(e, p);
    }
  }, {
    key: "onup",
    value: function onup(e) {
      if (!this.started) return;
      var p = {
        x: e.pageX,
        y: e.pageY,
        action: 'up'
      };
      if (this.isvalid(p)) this.points.push(p);
      this.up(e, p);
    }
  }]);

  return Action;
}();

var RectAction = /*#__PURE__*/function (_Action) {
  _inherits(RectAction, _Action);

  var _super = _createSuper(RectAction);

  function RectAction(type, annot) {
    _classCallCheck(this, RectAction);

    return _super.call(this, type, annot);
  }

  _createClass(RectAction, [{
    key: "start",
    value: function start() {
      _get(_getPrototypeOf(RectAction.prototype), "start", this).call(this);

      $('<div id="placeholder"/>').appendTo('body');
    }
  }, {
    key: "stop",
    value: function stop() {
      _get(_getPrototypeOf(RectAction.prototype), "stop", this).call(this);

      $('#placeholder').remove();
    }
  }, {
    key: "move",
    value: function move(e, p) {
      this.updatePlaceholder();
    }
  }, {
    key: "prepare",
    value: function prepare(annot) {
      annot = _get(_getPrototypeOf(RectAction.prototype), "prepare", this).call(this, annot);
      annot.rotate = fixAngle(pdfViewer.pagesRotation + getPageRotate(this.page));
      return annot;
    }
  }, {
    key: "updatePlaceholder",
    value: function updatePlaceholder() {
      var r = this.rect(false);
      var c = rgbcss(this.params.color || 'black');
      var f = this.params.fill ? rgbcss(this.params.fill) : 'transparent';
      $('#placeholder').toggle(this.points.length > 1).css({
        width: r.right - r.left + 'px',
        height: r.bottom - r.top + 'px',
        left: r.left + 'px',
        top: r.top + 'px'
      }).css({
        border: (this.params.border_width || 1) + 'px solid ' + c,
        'border-radius': this.type == 'Circle' ? '50%' : 0,
        background: f,
        opacity: this.params.opacity || 1
      });
    }
  }]);

  return RectAction;
}(Action);

function canvasRoundRect(ctx, x, y, w, h, r) {
  if (w < 2 * r) r = w / 2;
  if (h < 2 * r) r = h / 2;
  ctx.beginPath();
  ctx.moveTo(x + r, y);
  ctx.arcTo(x + w, y, x + w, y + h, r);
  ctx.arcTo(x + w, y + h, x, y + h, r);
  ctx.arcTo(x, y + h, x, y, r);
  ctx.arcTo(x, y, x + w, y, r);
  ctx.closePath();
  return ctx;
}

function wrapText(context, text, x, y, maxWidth, lineHeight) {
  var words = text.split(' ');
  var line = '';

  for (var n = 0; n < words.length; n++) {
    var testLine = line + words[n] + ' ';
    var metrics = context.measureText(testLine);
    var testWidth = metrics.width;

    if (testWidth > maxWidth && n > 0) {
      context.fillText(line, x, y);
      line = words[n] + ' ';
      y += lineHeight;
    } else {
      line = testLine;
    }
  }

  context.fillText(line, x, y);
  return y + lineHeight;
}

function getTextHeight(css) {
  var text = $('<span>Hg</span>').css(css);
  var block = $('<div style="display: inline-block; width: 1px; height: 0px;"></div>');
  var div = $('<div></div>');
  div.append(text, block);
  var body = $('body');
  body.append(div);

  try {
    var result = {};
    block.css({
      verticalAlign: 'baseline'
    });
    result.ascent = block.offset().top - text.offset().top;
    block.css({
      verticalAlign: 'bottom'
    });
    result.height = block.offset().top - text.offset().top;
    result.descent = result.height - result.ascent;
  } finally {
    div.remove();
  }

  return result;
}

;

function createTextImage(annot, asimg) {
  var sz = pdfrectToViewportSize(annot.rect, annot.page);
  var angle = getPageRotate(annot.page) + pdfViewer.pagesRotation;
  angle = fixAngle(angle);
  if (angle == 90 || angle == 270) sz = {
    width: sz.height,
    height: sz.width
  };
  var scaleForQuality = 2;
  sz.width *= scaleForQuality;
  sz.height *= scaleForQuality;
  var canvas = document.createElement('canvas');
  canvas.width = sz.width;
  canvas.height = sz.height;
  var ctx = canvas.getContext('2d');
  ctx.globalAlpha = annot.opacity || 1;
  ctx.fillStyle = rgbcss(annot.color);
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.strokeRect(0, 0, canvas.width, canvas.height); //ctx.globalAlpha = 1

  ctx.save();

  if (angle) {
    ctx.translate(canvas.width / 2, canvas.height / 2);
    ctx.rotate(-angle * Math.PI / 180);

    switch (angle) {
      case 90:
        ctx.translate(-canvas.height / 2, -canvas.width / 2);
        break;

      case 180:
        ctx.translate(-canvas.width / 2, -canvas.height / 2);
        break;

      case 270:
        ctx.translate(-canvas.height / 2, -canvas.width / 2);
        break;
    }
  }

  ctx.textAlign = "left";
  ctx.textBaseline = "top"; // reduce size to make space for borders

  var fsz = getAnnotFontSize(annot) || 9;
  fsz *= scaleForQuality;
  ctx.font = 'normal normal ' + fsz + 'pt "Arial"';
  ctx.fillStyle = 'black';
  var y = 5;
  var lh = getTextHeight({
    'font-size': fsz + 'pt'
  }).height;
  (annot.contents || '').split('\n').forEach(function (line) {
    y = wrapText(ctx, line, 5, y, canvas.width - 10, lh);
  });
  ctx.restore();
  return asimg ? canvas.toDataURL("image/png") : ctx.getImageData(0, 0, canvas.width, canvas.height);
}

var FreeTextAction = /*#__PURE__*/function (_RectAction) {
  _inherits(FreeTextAction, _RectAction);

  var _super2 = _createSuper(FreeTextAction);

  function FreeTextAction(type, annot) {
    _classCallCheck(this, FreeTextAction);

    return _super2.call(this, type, annot);
  }

  _createClass(FreeTextAction, [{
    key: "updatePlaceholder",
    value: function updatePlaceholder() {
      _get(_getPrototypeOf(FreeTextAction.prototype), "updatePlaceholder", this).call(this); //let img = createTextImage(this.annot, true)


      var text = this.params.contents || '';
      $('#placeholder').css({
        'white-space': 'pre-wrap',
        'font-size': (getAnnotFontSize(this.params) || 9) + 'pt',
        overflow: 'hidden',
        border: '1px solid black',
        'background-color': 'yellow'
      }).text(text.split('\n'));
    }
  }]);

  return FreeTextAction;
}(RectAction);

var StampAction = /*#__PURE__*/function (_RectAction2) {
  _inherits(StampAction, _RectAction2);

  var _super3 = _createSuper(StampAction);

  function StampAction(type, annot) {
    var _this3;

    _classCallCheck(this, StampAction);

    _this3 = _super3.call(this, type, annot);

    if (!annot.stampType) {
      _this3.stop();

      var form = $("<div class=\"modal\">\n                        <form onsubmit=\"arguments[0].preventDefault()\">\n                                <div class=\"stamp-preview\" style=\"text-align: center; overflow: hidden; width: 100%; height: 50px; font-size: 40px; font-style: italic; font-weight: bold;\"></div>\n                                <div>Stamp type: <select name=\"stampType\"></select></div>\n                                <div>Stamp text: <input type=\"text\" name=\"stampText\"></div>\n                                <div>Font: <input type=\"text\" name=\"font\"></div>\n                                <div>Text color: <input type=\"color\" name=\"color\"></div>\n                                <div>Fill from: <input type=\"color\" name=\"stops0\"></div>\n                                <div>Fill to: <input type=\"color\" name=\"stops1\"></div>\n                                <div style=\"text-align: right\">\n                                        <button onclick=\"$(this).closest('.modal').remove()\">Cancel</button>\n                                        <button class=\"ok\">OK</button>\n                                </div>\n                        </form>\n                </div>");
      setupStampsOptions(form.find('[name=stampType]'));

      var get = function get() {
        return {
          stampType: form.find('select').val(),
          stampText: form.find('input').val(),
          color: form.find('[name=color]').val(),
          stops: [form.find('[name=stops0]').val(), form.find('[name=stops1]').val()],
          font: form.find('[name=font]').val()
        };
      };

      $(form).appendTo('body').find('.ok').click(function () {
        new StampAction(type, Object.assign(annot, get()));
        form.remove();
      });

      var onchange = function onchange() {
        var cfg = _this3.config(get());

        form.find('.stamp-preview').text(cfg.text).css(cfg.css);
      };

      form.find('[name=stampText]').val(form.find('select').val());
      form.find('[name=stampType').on('change', function (e) {
        var cfg = stamps[e.target.value];
        form.find('[name=stampText]').val(camelSplit(e.target.value));
        form.find('[name=color]').val(cfg.color);
        form.find('[name=stops0]').val(cfg.stops[0]);
        form.find('[name=stops1]').val(cfg.stops[1]);
        setTimeout(onchange, 1);
      }).trigger('change');
      onchange();
      form.find('input, select').on('input', onchange);
      $('#addStampAnnotation').blur().val('Stamp');
    }

    return _this3;
  }

  _createClass(StampAction, [{
    key: "config",
    value: function config(annot) {
      annot = annot || this.annot;
      var cfg = stamps[fixType(annot.stampType)];

      if (annot.stampText) {
        cfg = {
          color: annot.color,
          stops: annot.stops,
          font: annot.font,
          angle: annot.angle
        };
      }

      cfg.css = {
        background: "linear-gradient(45deg, ".concat(cfg.stops[0], ", ").concat(cfg.stops[1], ")"),
        color: cfg.color,
        'font-family': cfg.font
      };
      cfg.text = annot.stampText || camelSplit(fixType(annot.stampType));
      return cfg;
    }
  }, {
    key: "createStampImage",
    value: function createStampImage() {
      var r = this.rect();
      var angle = fixAngle(getPageRotate(this.page) + pdfViewer.pagesRotation);
      if (angle == 90 || angle == 270) r = {
        left: r.top,
        right: r.bottom,
        top: r.left,
        bottom: r.right
      }; // that's for better scaling

      for (var k in r) {
        r[k] *= 1.5;
      }

      var canvas = document.createElement('canvas');
      var sw = 2;
      canvas.width = r.right - r.left; // + sw * 4

      canvas.height = r.bottom - r.top; // + sw * 4

      var ctx = canvas.getContext('2d');
      ctx.globalAlpha = this.params.opacity || 1;
      ctx.fillStyle = 'rgb(0, 0, 0, 0)';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      var cfg = this.config();
      var grd = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
      grd.addColorStop(0, cfg.stops[0]);
      grd.addColorStop(1, cfg.stops[1]);
      ctx.fillStyle = grd;
      ctx.strokeStyle = cfg.color;
      ctx.lineWidth = 4;
      var ofs = sw * 2;
      canvasRoundRect(ctx, ofs, ofs, canvas.width - ofs * 2, canvas.height - ofs * 2, 6);
      ctx.shadowBlur = sw;
      var offsets = {
        0: [sw, sw],
        90: [sw, -sw],
        180: [-sw, -sw],
        270: [-sw, sw]
      };
      ctx.shadowOffsetX = offsets[angle][0];
      ctx.shadowOffsetY = offsets[angle][1];
      ctx.shadowColor = '#aaa';
      ctx.stroke();
      ctx.shadowBlur = 0;
      ctx.shadowColor = 'transparent';
      ctx.fill();
      ctx.save();
      ctx.translate(canvas.width / 2, canvas.height / 2);
      ctx.rotate(-angle * Math.PI / 180);
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle'; // reduce size to make space for borders

      var fsz = 1.5 * ($('#placeholder').css('font-size').replace('px', '') * 0.9) + 'px';
      ctx.font = 'bold italic ' + fsz + ' "' + (cfg.font || $('#placeholder').css('font-family')) + '"';
      ctx.fillStyle = cfg.color;
      ctx.fillText(cfg.text, 0, 0);
      ctx.restore(); //$('.sidebar-body').append(canvas) // dbg

      var data = ctx.getImageData(0, 0, canvas.width, canvas.height);
      return data;
    }
  }, {
    key: "prepare",
    value: function prepare(annot) {
      var image = this.createStampImage();
      annot = Object.assign({
        image: image
      }, _get(_getPrototypeOf(StampAction.prototype), "prepare", this).call(this, annot));
      delete annot.rotate;
      return annot;
    }
  }, {
    key: "up",
    value: function up(e, p) {
      this.updatePlaceholder();

      _get(_getPrototypeOf(StampAction.prototype), "up", this).call(this, e, p);
    }
  }, {
    key: "setParams",
    value: function setParams(params) {
      this.norecalc = true;

      _get(_getPrototypeOf(StampAction.prototype), "setParams", this).call(this, params);

      delete this.norecalc;
    }
  }, {
    key: "updatePlaceholder",
    value: function updatePlaceholder() {
      var r = this.rect(true);
      var w = r.right - r.left;
      var h = r.bottom - r.top;
      var cfg = this.config();
      var text = cfg.text; // this is an approximate instead of text measure

      $('#placeholder').css({
        opacity: this.params.opacity
      });
      if (cfg.text) $('#placeholder').css(cfg.css);

      if (!this.norecalc) {
        $('#placeholder').addClass("stamp").addClass(this.annot.stampType).css('font-size', 2 * w / text.length + 'px');
        var sz = measureText($('#placeholder')[0], text);
        w = sz.width;
        h = sz.height;
        $('#placeholder').text(text);
        var p1 = this.points[0];
        var p2 = {
          x: p1.x + w,
          y: p1.y + h
        };
        this.points = [p1, p2];
        r = this.rect(false);
        $('#placeholder').toggle(this.points.length > 1).css({
          width: r.right - r.left + 'px',
          height: r.bottom - r.top + 'px',
          left: r.left + 'px',
          top: r.top + 'px'
        });
      }
    }
  }]);

  return StampAction;
}(RectAction);

var PointAction = /*#__PURE__*/function (_Action2) {
  _inherits(PointAction, _Action2);

  var _super4 = _createSuper(PointAction);

  function PointAction(type, annot) {
    _classCallCheck(this, PointAction);

    return _super4.call(this, type, annot);
  }


  _createClass(PointAction, [{
    key: "rect",
    value: function rect() {
      // TODO: FIX FOR ROTATION? Set annot flag to disable rotation?
      var p = this.points[0];
      return Object.assign({
        left: p.x,
        top: p.y,
        right: p.x + 22,
        bottom: p.y + 22
      });
    }
  }, {
    key: "down",
    value: function down() {
      this.exec();
    }
  }]);

  return PointAction;
}(Action);

var DrawPolyBaseAction = /*#__PURE__*/function (_Action3) {
  _inherits(DrawPolyBaseAction, _Action3);

  var _super5 = _createSuper(DrawPolyBaseAction);

  function DrawPolyBaseAction(type, annot) {
    var _this4;

    _classCallCheck(this, DrawPolyBaseAction);

    _this4 = _super5.call(this, type, annot);
    _this4.parts = [];
    return _this4;
  }

  _createClass(DrawPolyBaseAction, [{
    key: "stop",
    value: function stop() {
      _get(_getPrototypeOf(DrawPolyBaseAction.prototype), "stop", this).call(this);

      this.parts = [];
      this.updatePlaceholder();
    }
  }, {
    key: "beforedown",
    value: function beforedown(e, p) {
      return this.isvalid(p);
    }
  }, {
    key: "move",
    value: function move() {
      this.updatePlaceholder();
    }
  }]);

  return DrawPolyBaseAction;
}(Action);

var DrawAction = /*#__PURE__*/function (_DrawPolyBaseAction) {
  _inherits(DrawAction, _DrawPolyBaseAction);

  var _super6 = _createSuper(DrawAction);

  function DrawAction() {
    _classCallCheck(this, DrawAction);

    return _super6.apply(this, arguments);
  }

  _createClass(DrawAction, [{
    key: "up",
    value: function up() {
      this.parts.push(this.points);
      if (this.parts.length == 1) this.exec();
      this.started = false; // for convenience re-focus text input after draw

      $('.side-card.active iframe').ifeditor('focus');
    }
  }, {
    key: "rect",
    value: function rect(exact) {
      return this.rectFromPoints(this.parts.flat(), exact);
    }
  }, {
    key: "prepare",
    value: function prepare(annot) {
      var _this5 = this;

      return Object.assign(_get(_getPrototypeOf(DrawAction.prototype), "prepare", this).call(this, annot), {
        inkList: this.parts.map(function (d) {
          return d.map(function (p) {
            return pointToPdf(p.x, p.y, _this5.page);
          }).flat();
        })
      });
    }
  }, {
    key: "down",
    value: function down(e, p) {
      this.updatePlaceholder();
    }
  }, {
    key: "beforemove",
    value: function beforemove(e, p) {
      return this.isvalid(p);
    }
  }, {
    key: "updatePlaceholder",
    value: function updatePlaceholder() {
      var _this6 = this;

      var box = document.getElementById('drawingLayer').getBoundingClientRect();
      $('#drawingLayer > path').remove();
      var svg = document.getElementById('drawingLayer');
      this.parts.concat([this.points]).forEach(function (points) {
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', points.map(function (p, i) {
          return "".concat(i ? 'L' : 'M', " ").concat(p.x - box.left, " ").concat(p.y);
        }).join(' '));
        path.setAttribute('stroke', rgbcss(_this6.params.color || 'black'));
        path.setAttribute('stroke-width', _this6.params.border_width);
        path.setAttribute('fill', 'none');
        svg.appendChild(path);
      });
      $(svg).css({
        opacity: this.params.opacity
      });
    }
  }]);

  return DrawAction;
}(DrawPolyBaseAction);

var PolyAction = /*#__PURE__*/function (_DrawPolyBaseAction2) {
  _inherits(PolyAction, _DrawPolyBaseAction2);

  var _super7 = _createSuper(PolyAction);

  function PolyAction() {
    _classCallCheck(this, PolyAction);

    return _super7.apply(this, arguments);
  }

  _createClass(PolyAction, [{
    key: "rect",
    value: function rect(exact) {
      return this.rectFromPoints(this.parts, exact);
    }
  }, {
    key: "prepare",
    value: function prepare(annot) {
      var _this7 = this;

      return Object.assign(_get(_getPrototypeOf(PolyAction.prototype), "prepare", this).call(this, annot), {
        // compensate for possible line endings - no need, using overflow: visible on svg instead
        //rect: rectToPdf(this.rect(6), this.page),
        vertices: this.parts.map(function (p) {
          return pointToPdf(p.x, p.y, _this7.page);
        }).flat()
      });
    }
  }, {
    key: "up",
    value: function up(e, p) {
      this.started = true;
      if (this.parts.length >= 2) // for convenience re-focus text input after draw
        $('.side-card.active iframe').ifeditor('focus');
    }
  }, {
    key: "down",
    value: function down(e, p) {
      this.parts.push(p);
      this.updatePlaceholder();

      if (this.parts.length == 2) {
        this.exec();
        this.started = true;
      }
    }
  }, {
    key: "beforemove",
    value: function beforemove(e, p) {
      return true;
    }
  }, {
    key: "updatePlaceholder",
    value: function updatePlaceholder() {
      var box = document.getElementById('drawingLayer').getBoundingClientRect();
      $('#drawingLayer > path').remove();
      var svg = document.getElementById('drawingLayer');
      var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      var last = this.points[this.points.length - 1];
      var points = this.parts.concat(last && this.isvalid(last) ? [last] : []);
      path.setAttribute('d', points.map(function (p, i) {
        return "".concat(i ? 'L' : 'M', " ").concat(p.x - box.left, " ").concat(p.y);
      }).join(' '));
      path.setAttribute('stroke', rgbcss(this.params.color || 'black'));
      path.setAttribute('stroke-width', this.params.border_width);
      path.setAttribute('fill', 'none');
      svg.appendChild(path);
      $(svg).css({
        opacity: this.params.opacity
      });
    }
  }]);

  return PolyAction;
}(DrawPolyBaseAction);

var FigureAction = /*#__PURE__*/function (_RectAction3) {
  _inherits(FigureAction, _RectAction3);

  var _super8 = _createSuper(FigureAction);

  function FigureAction() {
    _classCallCheck(this, FigureAction);

    return _super8.apply(this, arguments);
  }

  _createClass(FigureAction, [{
    key: "updatePlaceholder1",
    value: function updatePlaceholder1() {}
  }]);

  return FigureAction;
}(RectAction); // extend RectAction to support drawing over non-text regions


var TextAction = /*#__PURE__*/function (_RectAction4) {
  _inherits(TextAction, _RectAction4);

  var _super9 = _createSuper(TextAction);

  function TextAction(type, annot) {
    var _this8;

    _classCallCheck(this, TextAction);

    _this8 = _super9.call(this, type, annot);
    $('body').removeClass('noselection');

    if (!window.getSelection().isCollapsed) {
      _this8.selection = window.getSelection().getRangeAt(0);
      var el = window.getSelection().getRangeAt(0).endContainer.parentElement;
      var box = el.getBoundingClientRect();
      _this8.points = [{
        x: box.left,
        y: box.top
      }];
      _this8.page = $(el).closest('.page').data('pageNumber') - 1;

      _this8.exec();
    }

    return _this8;
  }

  _createClass(TextAction, [{
    key: "pdfrect",
    value: function pdfrect() {
      return rectFromArray(this.quads);
    }
  }, {
    key: "prepare",
    value: function prepare(annot) {
      var _this9 = this;

      var makestream = function makestream(cmds) {
        return !cmds ? null : Object.values(fixClrObj(_this9.params.color || getType(_this9.type).defaultColor)).join(' ') + ' RG\n1 w\n' + cmds + '\nS\n';
      };

      if (this.selection) {
        var _selectionQuads = selectionQuads(this.type, this.selection),
            quads = _selectionQuads.quads,
            appearance = _selectionQuads.appearance,
            page = _selectionQuads.page;

        this.page = this.page || page;
        this.quads = quads;
        this.appearance = makestream(appearance);
      } else {
        var rect = this.rect();

        var _rectToPdf3 = rectToPdf(rect, this.page),
            _rectToPdf4 = _slicedToArray(_rectToPdf3, 4),
            x1 = _rectToPdf4[0],
            y1 = _rectToPdf4[1],
            x2 = _rectToPdf4[2],
            y2 = _rectToPdf4[3];

        this.quads = [x1, y1, x2, y1, x1, y2, x2, y2];
        this.appearance = //!pdfViewer.pagesRotation ? null : 
        makestream(createTextSelectionAppearance(this.type, 0, rect, this.page));
      }

      return Object.assign(_get(_getPrototypeOf(TextAction.prototype), "prepare", this).call(this, annot), {
        quadPoints: this.quads,
        appearance: this.appearance,
        rect: [] //this.pdfrect() // lib will autocalculate

      });
    }
  }, {
    key: "up",
    value: function up(e, p) {
      if (this.textSelection) this.selection = window.getSelection().getRangeAt(0);
      return _get(_getPrototypeOf(TextAction.prototype), "up", this).call(this, e, p);
    }
  }, {
    key: "down",
    value: function down(e, p) {
      // TODO: handle double clicks... second click resets our points
      var els = document.elementsFromPoint(p.x, p.y);

      if (els[0].tagName == 'SPAN' && _toConsumableArray(els).filter(function (el) {
        return el.classList.contains('textLayer');
      })) {
        // selecting text - do nothing
        this.textSelection = true;
        $('#placeholder').remove();
      } else {
        // selecting non-text - draw rect
        $('body').addClass('noselection');
        this.textSelection = false;
      }
    }
  }]);

  return TextAction;
}(RectAction);
/* popbar */


function showPopBar(p, setup) {
  var card = createAnnotCard(Object.assign({}, setup, {
    page: pdfViewer.currentPageNumber - 1,
    author: viewerParams.authorAnnotation.name || 'Author',
    color: getType(setup.type).defaultColor
  }), setup);
  var body = $('.sidebar > .sidebar-body');
  body.prepend(card);
}
/* sidebar */


function refreshSelectedAnnot() {
  var card = $('.side-card.active');
  var svg = document.getElementById('pointers');
  var h = $('.toolbar').height();
  svg.style.top = h + 'px';
  svg.style.height = $('#viewerContainer').height() + 'px';
  var sz = svg.getBoundingClientRect();
  svg.setAttribute("viewBox", "0 ".concat(h, " ").concat(sz.width, " ").concat(sz.height - h));
  svg.querySelector('polyline').setAttribute('points', '');

  if (selectedAnnot) {
    var pageAnnot = $('.annotationLayer > section[data-annotation-id=' + selectedAnnot.object_id.obj + 'R]');
    pageAnnot.addClass('selected');

    if (pageAnnot[0]) {
      var ofs = 4;

      var _card$0$getBoundingCl = card[0].getBoundingClientRect(),
          r1 = _card$0$getBoundingCl.right,
          t1 = _card$0$getBoundingCl.top,
          b1 = _card$0$getBoundingCl.bottom,
          h1 = _card$0$getBoundingCl.height;

      var _pageAnnot$0$getBound = pageAnnot[0].getBoundingClientRect(),
          l2 = _pageAnnot$0$getBound.left,
          t2 = _pageAnnot$0$getBound.top,
          b2 = _pageAnnot$0$getBound.bottom,
          h2 = _pageAnnot$0$getBound.height;

      l2 -= 6;
      r1 += 6;
      t1 -= h;
      t2 -= h;
      var points = [[r1 - ofs, t1], [r1, t1], [r1, b1], [r1 - ofs, b1], [r1, b1], [r1, t1 + h1 / 2 + h / 2], [l2, t2 + h2 / 2 + h / 2], [l2, t2], [l2 + 2, t2], [l2, t2], [l2, b2], [l2 + 2, b2], [l2, b2]];
      svg.querySelector('polyline').setAttribute('points', points.map(function (p) {
        return p.join(',');
      }).join(' '));
    }
  } else {
    $('.annotationLayer > section').removeClass('selected'); //.find('.popupWrapper').attr('hidden', true)
  }
}

function getSideCardAnnotation(card) {
  var id = card.data('annotationId');
  return annotations.filter(function (x) {
    return x.object_id.obj == id;
  })[0];
}

function onSideCardActivate(card) {
  selectedAnnot = getSideCardAnnotation(card); //if (selectedAnnot && !isEditable(selectedAnnot))
  //        return

  var curcard = $('.side-card.active');
  if (curcard.length && curcard[0] != card[0]) onSideCardDeactivate();
  card.addClass('active');
  selectedAnnot = getSideCardAnnotation(card);

  if (selectedAnnot) {
    goToPage(selectedAnnot.page + 1);

    if (selectedAnnot.object_id) {
      $('.page').on('click.sidecard', function () {
        onSideCardDeactivate();
      });
      if (!isEditable(selectedAnnot)) card.addClass('readonly');
    }
  }

  refreshSelectedAnnot();
}

function onSideCardDeactivate() {
  $('body').removeClass('noselection');
  $('.page').off('click.sidecard');
  var card = $('.side-card.active');
  if (!card.length) return;
  card.removeClass('active');
  var a = card.prop('annot');
  if (!a.object_id) card.remove();else card.find('iframe').ifeditor('set', a.contents); // restore text

  card.find('iframe').ifeditor('blur', a.contents);
  $("#addStampAnnotation").val('Stamp');
  if (current) current.stop();
  selectedAnnot = null;
  refreshSelectedAnnot();
}

$.fn.ifeditor = function (method, arg, arg2) {
  var doc = this[0].contentDocument;

  var fmt = function fmt(s) {
    return s ? s.replace(/\\n/g, '\n') : '';
  };

  if (method == 'whenloaded') {
    if (doc && doc.readyState === 'complete') arg($(doc).find('textarea'), this);else this.on('load', function (e) {
      return arg($($(e.target)[0].contentDocument).find('textarea'), $(e.target));
    });
  } else if (method == 'focus') this.ifeditor('whenloaded', function (t) {
    return t.focus();
  });else if (method == 'blur') this.ifeditor('whenloaded', function (t) {
    return t.blur();
  });else if (method == 'bind') this.ifeditor('whenloaded', function (t) {
    return t.on(arg, arg2);
  });else if (method == 'set') this.ifeditor('whenloaded', function (t, iframe) {
    t.val(fmt(arg));
    iframe.ifeditor('recalc');
  });else if (method == 'get') return $(doc).find('textarea').val();else if (method == 'recalc') {
    var pre = $(doc).find('textarea')[0];
    pre.style.height = 'auto';
    pre.style.height = pre.scrollHeight + 'px';
    this.height(pre.scrollHeight);
  }
};

function setupEditor(iframe, value) {
  iframe.attr('srcdoc', "<body style=\"margin: 0\">\n                <textarea id=\"editor\" style=\"outline: none !important; border: 0; width: calc(100% - 8px); height: 5em; resize: none\"></textarea>\n        </body>");
  iframe.ifeditor('set', value);
  iframe.ifeditor('bind', 'input', function () {
    return iframe.ifeditor('recalc');
  });
  return iframe;
}

function createAnnotCard(a, setup) {
  var tpl = "<div class=\"side-card\" data-annotation-id=\"@id\">\n                <div class=\"card-title\" style=\"display: flex\">\n                        <div class=\"card-title-info\">\n                                <div class=\"card-author\"></div>\n                                <i class=\"fa fa-lock\" style=\"float: right; cursor: pointer\" title=\"Read-only\" onclick=\"alert('This annotation is not editable')\"></i>\n                                <i class=\"fa fa-trash\" style=\"float: right; cursor: pointer\" title=\"Delete\" \n                                        onclick=\"deleteAnnotation($(this).closest('.side-card').prop('annot')).then(saveAndReload)\"></i>\n                        </div>\n                        <div class=\"card-buttons\">\n                                <input type=\"color\" data-prop=\"color\" title=\"Color\" class=\"fg\" style=\"width: 3em; height: 1em\" list />\n                                <input type=\"color\" data-prop=\"fill\" title=\"Fill\" class=\"bg\" style=\"width: 3em; height: 1em\" list />\n                                <input type=\"number\" data-prop=\"border[0]\" min=\"0\" title=\"Border width\" class=\"border\" value=\"1\" style=\"height: 0.9em; width: 2.5em;\" />\n                                <input type=\"number\" data-prop=\"opacity\" min=\"0\" max=\"100\" title=\"Opacity\" class=\"opacity\" value=\"100\" style=\"height: 0.9em; width: 3em;\" />\n                                <input type=\"number\" data-prop=\"fontsize\" min=\"6\" max=\"48\" title=\"Font Size\" class=\"fontsize\" value=\"9\" style=\"height: 0.9em; width: 3em;\" />\n                                <button data-prop=\"iconName\" title=\"Comment icon type\" style=\"border: 1px solid #888; padding: 1px; margin: 0\"></button>\n                                <span>\n                                        <button data-prop=\"line_ending\" class=\"lestart\" title=\"Line beginning\" style=\"padding: 1px 0; margin-right: 0\"></button>\n                                        <button data-prop=\"line_ending\" class=\"leend\" title=\"Line ending\" style=\"padding: 1px 0; margin-left: 0;\"></button>\n                                </span>\n                        </div>\n                </div>\n                <iframe scrolling=\"no\" frameborder=\"0\"></iframe>\n                <div class=\"side-card-buttons\">\n                        <button class=\"cancel\">Cancel</button>\n                        <button class=\"ok\">OK</button>\n                </div>\n        </div>";
  var title = [a.author || '', fmtPdfDate(a.updateDate)].filter(function (x) {
    return x;
  }).join(' - ');
  var card = $(jQuery.parseHTML(tpl)[0]);
  card.attr('data-annotation-id', (a && a.object_id ? a.object_id.obj : '') || '').find('.card-title .card-author').text(title);
  card.prop('annot', a);
  var isnew = !a.object_id;
  card.toggleClass('newcard', isnew).toggleClass('readonly', !isEditable(a));
  card.addClass(fixType(a.type).toLowerCase());
  var editor = setupEditor(card.find('iframe'), a.contents);

  var commentMenu = {
    items: commentTypes.map(function (t) {
      return {
        value: t,
        text: "<img width=\"16\" src=\"".concat(base_url, "img/annotation/annotation-").concat(t, ".svg\" /> ") + t,
        parentText: "<img width=\"16\" src=\"".concat(base_url, "img/annotation/annotation-").concat(t, ".svg\" />"),
        // checked: t == (a.iconName || 'Note')
        checked: '/' + t == (a.iconName || '/Note') || t == (a.iconName || 'Note')
      };
    })
  };
  attachMenu(card.find('[data-prop=iconName]'), commentMenu, 'iconName');
  var lemenu = {};

  var getLeSvg = function getLeSvg(cls, type, w, vw) {
    return "<svg version=\"1.1\" width=\"".concat(w, "px\" height=\"1em\" viewBox=\"0 0 ").concat(vw, " 1\" style=\"overflow: visible\">\n                        <polyline stroke=\"black\" points=\"3,0 ").concat(vw - 3, ",0\" marker-").concat(cls, "=\"url(#").concat(type.toLowerCase(), "_tpl)\"></polyline>\n                </svg>");
  };

  ['start', 'end'].forEach(function (cls, index) {
    var avalue = (a.line_ending ? a.line_ending[index] : null) || 'None'; // TODO: add a value if not in our list to keep it

    var endings = lineEndings.indexOf(avalue) >= 0 ? lineEndings : [avalue].concat(lineEndings);
    var menu = {
      title: 'Line ' + cls,
      items: endings.map(function (t) {
        return {
          value: t,
          text: lineEndings.indexOf(t) >= 0 ? getLeSvg(cls, t, 72, 45) : t,
          parentText: lineEndings.indexOf(t) >= 0 ? getLeSvg(cls, t, 16, 14) : t,
          checked: t == avalue
        };
      })
    };
    attachMenu(card.find('.le' + cls), menu, 'le' + cls);
    lemenu[cls] = menu;
  });

  var getColor = function getColor(cls) {
    var input = card.find('input.' + cls)[0];
    return input.dataset.changed ? input.value : null;
  };

  var getPrepared = function getPrepared() {
    return {
      color: getColor('fg'),
      fill: getColor('bg'),
      border: Object.assign(a.border || {}, {
        border_width: +card.find('.border').val()
      }),
      opacity: +card.find('.opacity').val() / 100.0,
      iconName: commentMenu && commentMenu.checked && commentMenu.checked.value ? commentMenu.checked.value : 'Note',
      contents: editor.ifeditor('get'),
      line_ending: [lemenu.start.checked.value, lemenu.end.checked.value],
      defaultStyling: 'font: Helvetica,sans-serif ' + card.find('.fontsize').val() + 'pt'
    };
  };

  var okHandler = function okHandler(e) {
    e.stopPropagation();
    var copy = getPrepared();
    var annot = card.prop('annot');
    if (annot.object_id) deleteAnnotation(a).then(function () {
      createAnnotation(Object.assign({}, annot, copy));
      saveAndReload();
    });else setup.ok(copy);
  };

  var cancelHandler = function cancelHandler(e) {
    e.stopPropagation();
    onSideCardDeactivate();
  };

  card.find('.ok').click(okHandler);
  card.find('.cancel').click(cancelHandler);

  if (a.color || a.fill) {
    var c = rgbcss(a.fill || a.color);
    if (c != '#000000') card.find('.card-title').css({
      background: rgbcss(a.fill || a.color)
    });
  }

  if (a.color) card.find('input.fg').val(rgbcss(a.color))[0].dataset.changed = 1;
  if (a.fill) card.find('input.bg').val(rgbcss(a.fill))[0].dataset.changed = 1;
  card.find('input.fg').toggle(!getType(a.type).nocolor);
  card.find('input.bg').toggle(!!getType(a.type).fill);
  card.find('.fontsize').val(getAnnotFontSize(a) || 9).toggle(!!getType(a.type).font);
  card.find('.opacity').val((a.opacity || 1) * 100);
  card.find('.border').val(!a.border ? 1 : (Array.isArray(a.border) ? a.border[2] : a.border.border_width) || 1).toggle(!!getType(a.type).border);

  if (!a.object_id) {
    editor.ifeditor('focus');
    onSideCardActivate(card);
  } else {
    editor.ifeditor('bind', 'focus', function () {
      return onSideCardActivate(card);
    });
  }

  editor.ifeditor('bind', 'keydown', function (e) {
    if (e.code == 'Enter' && e.ctrlKey) {
      okHandler(e);
    } else if (e.code == 'Escape') {
      cancelHandler(e);
    }
  });
  card.click(function () {
    return onSideCardActivate(card);
  });
  $('.sidebar').click(function (e) {
    var els = _toConsumableArray(document.elementsFromPoint(e.pageX, e.pageY));

    if (els.filter(function (el) {
      return el.tagName == 'SELECT';
    })) return;
    var card = els.filter(function (el) {
      return $(el).hasClass('side-card');
    })[0];
    if (!card && $('.side-card.active').length) onSideCardDeactivate();
  });
  card.find('.fg, .bg, select[data-prop=iconName]').on('change', function (e) {
    var annot = $(e.target).closest('.side-card').prop('annot');
    this.dataset.changed = 1;

    if (annot.object_id) {//let cfg = {}
      //cfg[this.dataset.prop] = this.type == 'color' ? parseColor(this.value) : this.value
      //changeAnnotation(annot, cfg)
    }
  });
  card.find('input').on('input', function () {
    if (current) // postpone so that value is set
      setTimeout(function () {
        current.setParams(getPrepared());
      }, 0);
  });
  editor.ifeditor('bind', 'input', function (e) {
    if (current) // postpone so that value is set
      setTimeout(function () {
        current.setParams(getPrepared());
      }, 0);
  });
  return card;
}

function sidebarSort(order) {
  sidesort = order;
  refreshSidebar();
}

function refreshSidebar() {
  var sidebar = document.getElementsByClassName('sidebar')[0];
  sidebar.querySelectorAll('.side-card, .sidebar-page-sep').forEach(function (el) {
    return el.remove();
  });
  var lastpage;
  var body = $('.sidebar > .sidebar-body');
  var order = sidesorts.checked.fn;
  annotations.filter(function (a) {
    return (
      /*a.contents !== undefined && */
      a.type != '/Popup'
    );
  }).sort(order).forEach(function (a) {
    if (a.page != lastpage) {
      body.append($('<div class="sidebar-page-sep" />').attr('data-page', a.page + 1).append($('<span/>').text("Page ".concat(a.page + 1))));
      lastpage = a.page;
    }

    body.append(createAnnotCard(a));
  });
}

function printHandler() {
  /*let media = $('<div class="printing"/>').css({ width: '100vw' }).appendTo('body')
  let tasks = []
  for (let i = 1; i <= pdfViewer.pagesCount; i++) {
          tasks.push(pdfViewer.pdfDocument.getPage(i).then(function(page) {
                  let viewport = page.getViewport({ scale: pdfViewer.currentScaleValue, rotation: pdfViewer.pagesRotation })
                  let c = $('.canvasWrapper').first()
                  viewport = page.getViewport({ scale: c.width() / viewport.width, rotation: pdfViewer.pagesRotation })
                    let pageView = $('<div class="page"/>').appendTo(media)
                  let canvas = document.createElement("canvas")
                  canvas.className = 'canvasWrapper'
                  canvas.width = viewport.width
                  canvas.height = viewport.height
                  pageView.append(canvas)
                  return page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport }).promise
          }))
  }
  Promise.all(tasks)
  return*/
  // We need to preload all pages, so we make infinite buffer and make pdfViewer think that all pages are visible
  // Inspired by https://github.com/mozilla/pdf.js/issues/7391
  pdfViewer._buffer.resize(10000, 10000);

  var oldget = pdfViewer._getVisiblePages;

  pdfViewer._getVisiblePages = function () {
    var pages = pdfViewer._pages; //.filter(p => p.renderingState === 0)

    var last = pages[pages.length - 1];
    return {
      first: {
        id: pages[0].id,
        view: pages[0]
      },
      last: {
        id: last.id,
        view: last
      },
      views: pages.map(function (p) {
        return {
          id: p.id,
          view: p
        };
      })
    };
  };

  pdfViewer.currentScale = 1;
  pdfViewer.forceRendering();
  var overlay = $('<div class="print-overlay" />').text('Preparing...').appendTo('#main');

  var checker = function checker() {
    if ($('.page > .loadingIcon').length) {
      setTimeout(checker, 100);
      return;
    }

    overlay.remove();

    window.onafterprint = function () {
      pdfViewer._getVisiblePages = oldget;

      pdfViewer._buffer.resize(10, 10);

      pdfViewer.forceRendering();
    };

    window.print();
  };

  setTimeout(checker, 100);
  /*Promise.all([pdfViewer._pages[11]].map(pageView => 
          pdfViewer._ensurePdfPageLoaded(pageView).then(function () {
                  //debugger
                  if (pageView.renderingState === 0)
                          return pageView.draw().then(() => console.log('Finished ' + pageView.id))
                          //pdfViewer.renderingQueue.renderView(pageView);
          })
  ))*/
}
/* API */


function test() {
  window.pdferrors = [];
  $.get('./tests/pdfs_codiac.txt').then(function (list) {
    var datas = {};
    var loads = list.split('\n').map(function (x) {
      return x.trim();
    }).filter(function (x) {
      return x;
    }).map(function (file) {
      var url = './tests/codiac/' + file;
      return fetch(url).then(function (response) {
        return response.arrayBuffer();
      }).then(function (data) {
        return datas[file] = data;
      }); //return $.get(url).then(function(data) { datas[file] = data })
    });
    var failed = [];
    Promise.all(loads).then(function () {
      var _loop = function _loop() {
        var file = name;

        try {
          var pdf = new pdfAnnotate.AnnotationFactory(datas[file]);
          pdf.getAnnotations()["catch"](function (e) {
            window.pdferrors.push({
              file: file,
              size: datas[file].byteLength,
              error: e.toString()
            });
          });
        } catch (e) {
          window.pdferrors.push({
            file: file,
            size: datas[file].byteLength,
            error: e.toString()
          });
        }
      };

      for (var name in datas) {
        _loop();
      }
    }).then(function () {
      window.pdferrors.sort(function (a, b) {
        return b.size - a.size;
      }).forEach(function (x) {
        return console.log("".concat(x.file, " ").concat(x.size, ": ").concat(x.error));
      });
    });
  });
}

function createViewer(params) {
  var html = "<div id=\"main\">\n                    <div class=\"toolbar\">\n                            <button onclick=\"new TextAction('Highlight')\" title=\"Highlight text\" data-type=\"text\"><i class=\"fa fa-italic\"></i></button>\n                            <button onclick=\"new TextAction('Underline')\" title=\"Underline text\" data-type=\"text\"><i class=\"fa fa-underline\"></i></button>\n                            <button onclick=\"new TextAction('Squiggly')\" title=\"Squiggly underline text\" data-type=\"text\"><i class=\"fa fa-water\"></i></button>\n                            <button onclick=\"new TextAction('StrikeOut')\" title=\"Strike-out text\" data-type=\"text\"><i class=\"fa fa-strikethrough\"></i></button>\n                            <button onclick=\"new PointAction('Text')\" title=\"Add comment\" data-type=\"rect\"><i class=\"far fa-comment-alt\"></i></button>\n                            <button onclick=\"new FreeTextAction('FreeText')\" type=\"button\" title=\"Free Form Text\" data-type=\"rect\"><i class=\"far fa-object-ungroup\"></i></button>\n                            <button onclick=\"new RectAction('Circle')\" title=\"Add circle\" data-type=\"rect\"><i class=\"far fa-circle\"></i></button>\n                            <button onclick=\"new RectAction('Square')\" title=\"Add rectangle\" data-type=\"rect\"><i class=\"far fa-square\"></i></button>\n                            <button onclick=\"new PolyAction('PolyLine')\" title=\"Add polyline\" data-type=\"draw\"><i class=\"fa fa-route\"></i></button>\n                            <button onclick=\"new PolyAction('Polygon')\" title=\"Add polygon\" data-type=\"draw\"><i class=\"fa fa-draw-polygon\"></i></button>\n                            <button onclick=\"new DrawAction('Ink')\" title=\"Draw annotation\" data-type=\"draw\"><i class=\"fa fa-pencil-alt\"></i></button>\n                            <select id=\"addStampAnnotation\" onchange=\"if (this.selectedIndex) new StampAction('Stamp', { stampType: this.value })\" title=\"Add stamp\" data-type=\"rect\">\n                                    <option val=\"\" disabled=\"disabled\" selected=\"selected\">Stamp</option>\n                            <select>\n                            <!--select id=\"addFigureAnnotation\" onchange=\"if (this.selectedIndex) new FigureAction('Polygon', { figure: this.value })\" title=\"Add arrows and other figures\" data-type=\"rect\">\n                                    <option val=\"\" disabled=\"disabled\" selected=\"selected\">Figures</option>\n                                    <option val=\"Arrow\">Arrow</option>\n                            <select-->\n                            <div style=\"flex: 1; text-align: right; margin: auto\" class=\"filename\"></div>\n                    </div>\n                    <div id=\"viewerContainer\">\n                            <div id=\"viewer\" class=\"pdfViewer\"></div>\n                    </div>\n                    <div class=\"viewbar noselection\">\n                            <div class=\"pagecontrols\">\n                                    <button onclick=\"goToPage(null, -1)\"><i class=\"fa fa-chevron-left\"></i></button>\n                                    <input type=\"text\" id=\"pagenum\" style=\"width: 2em\" onchange=\"goToPage(+this.value)\" />\n                                    <span id=\"page\">1</span>\n                                    <button onclick=\"goToPage(null, +1)\"><i class=\"fa fa-chevron-right\"></i></button>\n                            </div>\n                            <div class=\"findcontrols\">\n                                    Find: <input oninput=\"find(arguments[0])\" onkeydown=\"find(arguments[0])\" />\n                                    <span class=\"matches\"></span>\n                            </div>\n                            <div class=\"zoomcontrols\">\n                                    <i class=\"fa fa-text-width\" title=\"Fit width\" onclick=\"pdfViewer.currentScaleValue = 'page-width'\"></i>\n                                    <i class=\"fa fa-search-minus\" title=\"Zoom out\" onclick=\"pdfViewer.currentScale -= 0.1\"></i>\n                                    <input type=\"range\" min=\"0\" max=\"500\" value=\"100\" oninput=\"pdfViewer.currentScale = this.value / 100\" />\n                                    <i class=\"fa fa-search-plus\" title=\"Zoom in\" onclick=\"pdfViewer.currentScale += 0.1\"></i>\n                                    <i class=\"fa fa-undo-alt\" title=\"Rotate counterclockwise\" onclick=\"rotate(-90)\"></i>\n                                    <i class=\"fa fa-redo-alt\" style=\"margin-right: 0.2em\" title=\"Rotate clockwise\" onclick=\"rotate(90)\"></i>\n                            </div>\n                    </div>\n                    <svg id=\"drawingLayer\">\n                        <defs>\n                                <marker id=\"circle_tpl\" viewBox=\"0 0 10 10\" refX=\"5\" refY=\"5\" markerWidth=\"5\" markerHeight=\"5\" markerUnits=\"strokeWidth\">\n                                        <circle cx=\"5\" cy=\"5\" r=\"5\" stroke=\"black\" />\n                                </marker>\n                                <marker id=\"openarrow_tpl\" viewBox=\"0 0 10 10\" refX=\"5\" refY=\"5\" markerUnits=\"strokeWidth\" markerWidth=\"10\" markerHeight=\"10\" orient=\"auto\">\n                                        <path d=\"M 0 3 L 5 5 L 0 7\" stroke=\"black\" fill=\"transparent\" />\n                                </marker>\n                                <marker id=\"closedarrow_tpl\" viewBox=\"0 0 10 10\" refX=\"5\" refY=\"5\" markerUnits=\"strokeWidth\" markerWidth=\"10\" markerHeight=\"10\" orient=\"auto\">\n                                        <path d=\"M 0 3 L 5 5 L 0 7 L 0 3 z\" stroke=\"black\" fill=\"red\" />\n                                </marker>\n                                <marker id=\"square_tpl\" markerWidth=\"7\" markerHeight=\"7\" refx=\"4\" refy=\"4\" markerUnits=\"strokeWidth\" orient=\"auto\">\n                                        <rect x=\"1\" y=\"1\" width=\"5\" height=\"5\" stroke=\"black\" fill=\"red\" />\n                                </marker>\n                                <marker id=\"diamond_tpl\" markerWidth=\"10\" markerHeight=\"10\" refX=\"5\" refY=\"5\" markerUnits=\"strokeWidth\" orient=\"auto\">\n                                        <path d=\"M 5,1 L 9,5 5,9 1,5 z\" stroke=\"black\" fill=\"red\" />\n                                </marker>\n                        </defs>\n                        <path d=\"\" stroke=\"black\" fill=\"transparent\" />\n                    </svg>\n            </div>\n            <div class=\"sidebar\">\n                    <div class=\"sidebar-splitter\"></div>\n                    <div class=\"sidebar-title\">\n                            <div class=\"sidebar-buttons\">\n                                <div style=\"position: relative; display: inline-block; top: 2px\">\n                                        <input type=\"file\" style=\"width: 2em;\" onchange=\"openDocument(arguments[0]); this.value = null\">\n                                        <i class=\"fa fa-folder-open\" style=\"font-size: 1.2em; position: absolute;height: calc(100% + 1px);left: 0;top: 0;width: 100%;background: white;color: black;pointer-events: none;\"></i>\n                                </div>\n                                <button type=\"button\" title=\"Save\" onclick=\"viewerParams.saveToDataBase(viewerParams.originFileName)\"><i class=\"fa fa-save\"></i></button>\n                                <button type=\"button\" title=\"Download\" onclick=\"pdfFactory.download(viewerParams.originFileName)\"><i class=\"fa fa-download\"></i></button>\n                                <button type=\"button\" id=\"toggleAnnotations\" title=\"Toggle annotations on/off\" onclick=\"toggleAnnotations()\"><i class=\"fa fa-eye\"></i></button>\n                                <button type=\"button\" title=\"Print\" onclick=\"printHandler()\"><i class=\"fa fa-print\"></i></button>\n                                <button type=\"button\" title=\"Test\" style=\"display: none\" onclick=\"test()\"><i class=\"fa fa-gear\"></i></button>\n                                <span style=\"flex: 1\"></span>\n                                <button data-menu=\"sidesorts\"><i class=\"fa fa-sort\"></i></button>\n                            </div>\n                    </div>\n                    <div class=\"sidebar-body\"></div>\n            </div>\n            <svg id=\"pointers\">\n                <polyline points=\"\" stroke-width=\"1\" stroke=\"black\" fill=\"transparent\"></polyline>\n            </svg>\n            ";
  $('body').append($.parseHTML(html));
  setupStampsOptions($("#addStampAnnotation"));
  $("#addStampAnnotation").append($('<option/>').val('').text('Custom...'));
  viewerParams = params;
  setupViewer(params.url);
  $('#viewerContainer').on('scroll', refreshSelectedAnnot); // menus

  $('*[data-menu]').each(function (i, el) {
    return attachMenu($(el), window[el.dataset.menu]);
  }); // tooltips

  var style = document.createElement('style');
  style.textContent = ".ui-tooltip {\n        position: absolute;\n        background: white;\n        padding: 0.2em;\n        border: 1px solid black;\n        box-shadow: 2px 2px 4px 0 #8888;\n        border-radius: 0.5em;\n        font-size: 10pt;\n        max-width: 20em;\n        background: #ffa;\n        z-index: 10;\n    }\n    .ui-tooltip > * {\n        padding: 0.5em;\n    }\n    .ui-tooltip p {\n            margin: 0.2em;\n    }\n    .ui-tooltip .title {\n            white-space: nowrap;\n            border-bottom: 1px solid #888;\n            font-size: 12pt;\n    }";
  document.head.appendChild(style);
}