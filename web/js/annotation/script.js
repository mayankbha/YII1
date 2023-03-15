/**
 * TODO tracker:
 * - when deleting annotations, need to deleted related popup annotations?
 */

function lpdf(p, i) { return String.fromCharCode.apply(null, p.slice(i, i + 100)) }

let viewerParams

let scripts = document.getElementsByTagName('script')
let myurl = scripts[scripts.length - 1].src
pdfjsLib.GlobalWorkerOptions.workerSrc = myurl.substr(0, myurl.lastIndexOf('/')) + '/pdf.worker.js'
let base_url = (typeof baseUrl != 'undefined') ? baseUrl : ''

//let pdfUrl = 'output.pdf'
let coordinates = []
let annotations = undefined
let selectedAnnot = undefined
let detectAngle = true

var sidesorts = {
  title: 'Sort by:',
  items: [
    { text: 'Page position', fn: (a, b) => a.page == b.page && a.rect && b.rect && a.rect[3] && b.rect[3] ? b.rect[3] - a.rect[3] : a.page - b.page },
    { text: 'Date', fn: (a, b) => a.page == b.page ? (a.updateDate && b.updateDate ? getPdfDate(a.updateDate) - getPdfDate(b.updateDate) : 0) : a.page - b.page },
    { text: 'Date (desc)', checked: true, fn: (a, b) => a.page == b.page ? (a.updateDate && b.updateDate ? getPdfDate(b.updateDate) - getPdfDate(a.updateDate) : 0) : a.page - b.page },
  ],
  onchange: refreshSidebar
}

function attachMenu(el, def, name) {
  //$('.menu').filter((i, el) => el.dataset.name == name).each((i, el) => el.remove())
  if (Array.isArray(def))
    def = { items: def }
  let menu = $('<ul class="menu" />').appendTo(el)
  menu.attr('data-name', name)
  if (def.title)
    menu.append($('<li class="menu-title"/>').html(def.title))
  let checkable = def.items.filter(i => i.checked).length
  if (checkable)
    menu.addClass('checkable')
  def.items.forEach(i => {
    let check = () => {
      def.checked = i
      li.addClass('checked')
      if (i.parentText) {
        if (!el.find('.menu-info').length)
          el.append($('<span class="menu-info"/>'))
        el.find('.menu-info').html(i.parentText)
      }
    }
    let li = $('<li/>').html(i.text).appendTo(menu)
    if (checkable)
      li.prepend($('<i class="fa fa-check"/>'))
    if (i.checked)
      check()
    li.on('click', e => {
      e.stopPropagation()
      if (checkable) {
        menu.find('> li').removeClass('checked')
        check()
      }
      if (def.onchange)
        def.onchange()
      menu.hide(100)
    })
  })
  el./*css({ position: 'relative' }).*/click(e => {
    let box = el[0].getBoundingClientRect()
    menu.css({ left: box.left + 'px', top: box.top + 'px' })
    if (menu.is(':visible'))
      $('body').off('mousedown.menu')
    else
      $('body').on('mousedown.menu', function (e) {
        if (elByClassAt(e.pageX, e.pageY, 'menu'))
          return;
        $('.menu').hide(100)
      })
    menu.toggle(200)
  })
}

/* constants and defs */

const stamps = [
  { stamps: ['Approved', 'Final', 'Completed'], color: '#416A1C', stops: ['#eeffee', '#69ff69'] },
  {
    stamps: ['Draft', 'Confidential', 'ForPublicRelease', 'NotForPublicRelease', 'ForComment', 'PreliminaryResults', 'InformationOnly', 'AsIs', 'Departmental', 'Experimental'],
    color: '#244D7E', stops: ['#eeeeff', '#6969ff']
  },
  { stamps: ['NotApproved', 'Void', 'Expired', 'Sold', 'TopSecret'], color: '#A52A2A', stops: ['#ffeeee', '#ff6969'] },
].map(x => x.stamps.map(s => ({ name: s, color: x.color, stops: x.stops }))).flat()
  .reduce((dict, x) => { dict[x.name] = x; return dict }, {})

function setupStampsOptions(select) {
  Object.keys(stamps).sort().forEach(stamp => select.append($('<option/>').val(stamp).text(camelSplit(stamp))))
}

const commentTypes = ["Note", "Comment", "Key", "Help", "NewParagraph", "Paragraph", "Insert"]
const lineEndings = ["None", "Square", "Circle", "Diamond", "OpenArrow", "ClosedArrow"]//, "Butt", "ROpenArrow", "RClosedArrow", "Slash"]

let types = {
  Text: { defaultColor: [1, 1, 0] },
  FreeText: { defaultColor: [1, 1, 0], font: true },
  Squiggly: { defaultColor: [0, 0, 1], text: true },
  Underline: { defaultColor: [1, 0, 0], text: true },
  StrikeOut: { defaultColor: [0, 0, 0], text: true },
  Highlight: { defaultColor: [1, 1, 0], text: true },
  Square: { defaultColor: [0, 0, 0], fill: true, border: true },
  Circle: { defaultColor: [0, 0, 0], fill: true, border: true },
  Stamp: { defaultColor: [0, 0, 0], nocolor: true },
  Ink: { defaultColor: [0, 0, 0], border: true, },
  PolyLine: { defaultColor: [0, 0, 0], fill: true, border: true },
  Polygon: { defaultColor: [0, 0, 0], fill: true, border: true },
  Link: { defaultColor: [0, 0, 0], },
  Widget: { defaultColor: [0, 0, 0], },
  Caret: { defaultColor: [0, 0, 0], },
  Popup: { defaultColor: [0, 0, 0], }
}

function fixType(type) { return type[0] == '/' ? type.substr(1) : type }
function getType(type) { return types[fixType(type)] || {} }

/* document & annotations */

let pdfFactory = undefined

function isEditable(a) {
  return !a.object_id || viewerParams.authorAnnotation.isAdmin || a.userId == viewerParams.authorAnnotation.id;
}

function deleteAnnotation(a) {
  return pdfFactory.deleteAnnotation(a.id || a.object_id)
}

function createAnnotation(copy) {
  if (copy.object_id && !isEditable(copy))
    return
  let type = fixType(copy.type)
  let color = fixClrObj(copy.color || getType(type).defaultColor)
  let annot
  if (type == 'FreeText') {

    copy.image = createTextImage(copy)

    //delete annot.rotate
    annot = pdfFactory.createFreeTextAnnotation(copy.page, copy.rect, copy.contents, copy.author, color)
    annot.rect_diff = [2, 2, 2, 2] // make padding for text
    annot.defaultStyling = copy.defaultStyling
  }
  else if (type == 'Stamp')
    annot = pdfFactory.createStampAnnotation(copy.page, copy.rect, copy.contents, copy.author, fixType(copy.stampType), color)
  else if (type == 'Ink')
    annot = pdfFactory.createInkAnnotation(copy.page, copy.rect || [], copy.contents, copy.author, copy.inkList, color)
  else if (type == 'PolyLine' || type == 'Polygon')
    annot = pdfFactory[`create${type}Annotation`](copy.page, copy.rect || [], copy.contents, copy.author, copy.vertices, color)
  else if (type == 'Circle' || type == 'Square') {
    let rect = normalizePdfRect(copy.rect, type == 'Circle')
    annot = pdfFactory[`create${type}Annotation`](copy.page, rect, copy.contents, copy.author, color)
  } else if (type == 'Text') {
    annot = pdfFactory.createTextAnnotation(copy.page, copy.rect, copy.contents, copy.author, color)
    annot.iconName = copy.iconName || annot.iconName
  } else if (type == 'Highlight' || type == 'Underline' || type == 'Squiggly' || type == 'StrikeOut') {
    if (!copy.rect || copy.rect.length == 0)
      copy.rect = normalizePdfRect(rectFromArray(copy.quadPoints))
    annot = pdfFactory[`create${type}Annotation`](copy.page, copy.rect, copy.contents, copy.author, color, copy.quadPoints)
  } else
    throw "Unsupported annotation type"
  annot.opacity = copy.opacity
  if (annot.border && copy.border && (copy.border.border_width !== undefined || Array.isArray(copy.border))) {
    annot.border.border_width = copy.border.border_width !== undefined ? copy.border.border_width : copy.border[2]
  }
  if (copy.fill)
    annot.fill = fixClrObj(copy.fill)
  if (copy.rotate !== undefined)
    annot.rotate = copy.rotate
  if (copy.line_ending)
    annot.line_ending = copy.line_ending
  if (copy.appearance)
    annot.appearance = copy.appearance
  if (copy.appearance_object)
    annot.appearance_object = copy.appearance_object
  if (copy.image)
    annot.image = copy.image
  if (viewerParams.authorAnnotation.id && annot.id.slice(-1)[0] == ')')
    annot.id = annot.id.slice(0, -1) + '_userid_' + viewerParams.authorAnnotation.id + ')'
}

function changeAnnotation(a, mod) {
  if (!isEditable(a))
    return
  let copy = Object.assign({}, a, mod)
  // fix appearance stream color
  if (mod.color && copy.appearance_object) {
    if (!copy.appearance) {
      alert('Not supported (yet)')
      return
    }
    let idx = copy.appearance.indexOf(' RG')
    let start = copy.appearance.substr(0, idx)
    // check if it's our stream - starts with RG color def
    if (start.split(' ').filter(x => x).filter(s => isNaN(parseFloat(s))).length == 0)
      copy.appearance = mod.color.join(' ') + ' ' + copy.appearance.substr(idx)
  }
  deleteAnnotation(a).then(() => {
    createAnnotation(copy)
    saveAndReload()
  })
}

/* viewer */

let pdfViewer = undefined

function getPageDiv(page) {
  return pdfViewer._pages[page].div
}
function getPageRotate(page) {
  return pdfViewer.getPageView(page).pdfPage.rotate
}
function computePageOffset(page) {
  let pg = getPageDiv(page)
  const border = 9 // empiric

  let rect = pg.getBoundingClientRect()
  let bodyElt = document.body
  return {
    top: rect.top + bodyElt.scrollTop + border,
    left: rect.left + bodyElt.scrollLeft + border
  }
}
function pointToPdf(x, y, page) {
  let ost = computePageOffset(page)
  return pdfViewer._pages[page].viewport.convertToPdfPoint(x - ost.left, y - ost.top)
}
function rectToPdf(rec, page) {
  if (Array.isArray(rec) && rec.length == 0)
    return []
  let p1 = pointToPdf(rec.left, rec.top, page)
  let p2 = pointToPdf(rec.right, rec.bottom, page)
  return normalizePdfRect(p1.concat(p2))
}

function pdfrectToViewportSize(rect, page) {
  var viewport = pdfViewer._pages[page].viewport
  let [x1, y1] = viewport.convertToViewportPoint(rect[0], rect[1])
  let [x2, y2] = viewport.convertToViewportPoint(rect[2], rect[3])
  return { width: Math.abs(x2 - x1), height: Math.abs(y2 - y1) }
}
function pointToViewport(x, y, page) {
  let ost = computePageOffset(page)
  return pdfViewer._pages[page].viewport.convertToPdfPoint(x - ost.left, y - ost.top)
}

// for Circle especially we need lower y first
// TODO: probably we should create correct coordinates in the first place in prepare()... but anyway
function normalizePdfRect(rect, reverseForCircle) {
  let [x1, y1, x2, y2] = rect
  return reverseForCircle
    ? [Math.min(x1, x2), Math.min(y1, y2), Math.max(x1, x2), Math.max(y1, y2)]
    : [Math.min(x1, x2), Math.max(y1, y2), Math.max(x1, x2), Math.min(y1, y2)]
}

function rectFromArray(points, expand) {
  expand = expand === undefined ? 2 : expand
  let xs = points.filter((c, i) => i % 2 == 0)
  let ys = points.filter((c, i) => i % 2 == 1)
  return [Math.min(...xs) - expand, Math.min(...ys) - expand, Math.max(...xs) + expand, Math.max(...ys) + expand]
}
function inflateRect(r, d) {
  r = normalizePdfRect(r)
  return [r[0] - d, r[1] + d, r[2] + d, r[3] - d]
}

function getRotationAngle(el) {
  var st = window.getComputedStyle(el, null);
  var tr = st.getPropertyValue("-webkit-transform") || st.getPropertyValue("-moz-transform") ||
    st.getPropertyValue("-ms-transform") || st.getPropertyValue("-o-transform") ||
    st.getPropertyValue("transform") || "";
  if (!tr || tr == 'none') return 0

  var values = tr.split('(')[1].split(')')[0].split(',').map(s => parseFloat(s));
  var [a, b, c, d] = values;
  var scale = Math.sqrt(a * a + b * b);
  // arc sin, convert from radians to degrees, round
  var sin = b / scale;
  // next line works for 30deg but not 130deg (returns 50);
  // var angle = Math.round(Math.asin(sin) * (180/Math.PI));
  var angle = Math.round(Math.atan2(b, a) * (180 / Math.PI));

  return angle
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
  const styleId = 'pdfAnnotateStampClasses'
  let style = document.getElementById(styleId)
  if (!style) {
    style = document.createElement('style')
    style.id = styleId
    document.head.appendChild(style)
  }
  let type = fixType(stampType)
  let label = type
  if (type.indexOf('SB') == 0 || type.indexOf('SH') == 0)
    label = type.substr(2)
  let text = `section.stampAnnotation > div.stamp.${type}::after { content: "${camelSplit(label)}"; }`
  if (style.textContent.indexOf(text) < 0)
    style.textContent += '\n' + text
}

function debounce(fn, timeout) {
  return function () {
    let scope = this
    let args = [...arguments]
    clearTimeout(scope.dataset.debounceTimerId)
    scope.dataset.debounceTimerId = setTimeout(function () {
      fn.apply(scope, args)
    }, timeout || 150)
  }
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
  el = $(el)
  el.on('mouseenter', debounce(function (e) {
    let html = getTooltipHtml.call(this)
    if (!html)
      return
    let rel = $(this).closest(cfg.addel)
    let box = this.getBoundingClientRect()
    let relbox = rel[0].getBoundingClientRect()
    let tip = $('<div class="ui-tooltip"/>').html(html)
      .css({ left: (box.left - relbox.left + rel[0].scrollLeft) + 'px', top: (box.bottom - relbox.top + rel[0].scrollTop) + 'px', opacity: 0 })
      .appendTo($(this).closest(cfg.addel))
    let tipbox = tip[0].getBoundingClientRect()
    let cropbox = $(this.closest(cfg.cropel))[0] ? $(this.closest(cfg.cropel))[0].getBoundingClientRect() : {}
    if (tipbox.right > cropbox.right - 20)
      tip[0].style.transform += ` translateX(${-(tipbox.right - tipbox.left - 20)}px)`
    if (tipbox.bottom > cropbox.bottom - 20)
      tip[0].style.transform += ` translateY(${-((tipbox.bottom - tipbox.top) + (box.bottom - box.top) + 20)}px)`
    tip.animate({ opacity: 1 }, 100)
  })).on('mouseleave', debounce(function (e) {
    let add = $(this).closest(cfg.addel)
    add.find('> .ui-tooltip').each(function () { $(this).fadeOut(100, function () { this.remove() }) })
  }))
}

function getTooltipHtml() {
  let popup = $(this).find('.popupWrapper > .popup').first().clone(true)
  if (!popup.length)
    return ''
  let html = `<div class="title"><b>${popup.find('> h1').text() || '(User)'}</b> ${popup.find('> span:first-of-type').text() || ''}</div>`
  popup.find('h1, span:first-of-type').each(function () { this.remove() })
  html += '<div class="body">' + popup.html() + '</div>'
  popup.remove()
  return html
}

function fixAnnotations(page) {
  page = !page || !page.length ? $('.page') : page

  //tooltip(page, { addel: '.page', relel: '#viewerContainer', content: getTooltipHtml })
  page.find('> .annotationLayer > section').each(function () {
    let pageAnnot = this
    const id = pageAnnot.dataset.annotationId.replace(/R.*$/, '')
    let a = annotations.filter(x => x.object_id.obj == id)[0]
    if (!a || pageAnnot.dataset.fixed)
      return
    pageAnnot.dataset.fixed = '1'

    tooltip(pageAnnot, { addel: '.page', cropel: '#viewerContainer', content: getTooltipHtml })
    $(pageAnnot).find('.popupWrapper > .popup').hide()

    let ael = $(pageAnnot).children('*:not(.popupWrapper)')[0] || $(pageAnnot)[0]
    // let ael = $(pageAnnot).children('*:not(.popupWrapper)')[0]

    let saveAnnot = function () {
      let box = (ael || pageAnnot).getBoundingClientRect()
      let cfg = {
        rect: rectToPdf(box, a.page)
      }
      if (a.inkList || a.vertices) {
        let dx = cfg.rect[0] - a.rect[0]
        let dy = cfg.rect[1] - a.rect[1]
        if (a.inkList)
          cfg.inkList = a.inkList.map(arr => arr.map((z, i) => (i % 2) ? z + dy : z + dx))
        else
          cfg.vertices = a.vertices.map((z, i) => (i % 2) ? z + dy : z + dx)
      }
      changeAnnotation(a, cfg)
    }

    $(pageAnnot).find('.popup > span[data-l10n-args]').each(function () {
      let json = JSON.parse(this.dataset.l10nArgs)
      let text = this.textContent
      for (var k in json)
        text = text.replace('{{' + k + '}}', json[k])
      this.textContent = text
    })

    pageAnnot.addEventListener('dblclick', e => {
      let id = $(e.target).closest('section').data('annotation-id').replace('R', '')
      let card = $('.sidebar').find(`.side-card[data-annotation-id=${id}]`)
      if (card.length) {
        onSideCardActivate(card)
        card.find('iframe').ifeditor('focus')
      }
    });

    if (a.opacity || a.opacity === 0)
      ael.style.opacity = a.opacity

    let pageRotate = a.type == '/Text' ? getPageRotate(a.page) : 0
    let angle = pageRotate - (a.rotate || 0)
    // this is a hack for css rotate### styles... can fix them instead?
    // TODO avoid this and fix css classes rotation instead
    if (pageRotate && a.type != '/Text')
      angle = 180 + angle
    angle = fixAngle(angle)

    if (a.type == '/Text') {
      ael.src = base_url + 'img/annotation' + ael.src.substr(ael.src.lastIndexOf('/'))
      $(ael).show()//.addClass('rotate' + angle)
      ael.style.transform = 'rotate(' + -(angle + pdfViewer.pagesRotation) + 'deg)'
    }
    else if (a.type == '/Stamp' && !a.appearance_object && a.stampType) {
      //let box = ael.getBoundingClientRect()
      //let page = $(pageAnnot).closest('.page')[0]
      //let pagebox = page.getBoundingClientRect()

      // add special classes .stamp.STAMPTYPE.rotateANGLE that append ::after element with text
      ensureAnnotationClass(a.stampType)
      $(ael).addClass('stamp').addClass(fixType(a.stampType)).addClass('rotate' + angle)
      ael.style.boxSizing = 'border-box'

      // this is kind of hack, but almost always stamp width > height, so we take the one that's bigger
      const sz = Math.max($(ael).width(), $(ael).height())
      // approximate instead of measure
      ael.style.fontSize = (1.5 * sz / a.stampType.length) + 'px'

      // this approach is to add div with background image to the page itself so that it is not affected by section tranform matrix
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
    }
    else if (a.type == '/FreeText') {
      var resizer = $('<div class="resizer fa fa-grip-vertical"/>').appendTo(pageAnnot)
      var resel = $(ael)
      draggable(resizer[0], {
        start: () => {
          resel.css({ border: '1px solid black ' })
          resizer.prop('initialWidth', resel.width()).prop('initialHeight', resel.height())
        },
        apply: function (el, x, y, dx, dy, sx, sy) {
          resel
            .width(resel.width() + dx)
            .height(resel.height() + dy)
        },
        stop: saveAnnot
      })
      if (!a.appearance_object) {
        ael.style.border = bordercss(a.border, 'black')
        ael.style.background = a.color ? rgbcss(a.color) : "yellow"
        ael.style.overflow = 'hidden'
        let fsz = getAnnotFontSize(a) || 9
        let scale = ael.getBoundingClientRect().width / ael.offsetWidth
        ael.style.fontSize = fsz / scale + 'pt'
        ael.style.whiteSpace = 'pre-wrap'
        ael.style.display = 'flex'
        ael.setAttribute('data-contents', a.contents || '')
        $(ael).addClass('rotate' + angle)
      }
    } else if (a.type == '/Circle' || a.type == '/Square' || a.type == '/Ink' || a.type == '/PolyLine' || a.type == '/Polygon') {
      const selectors = {
        '/Ink': 'polyline', '/Circle': 'ellipse', '/Square': 'rect', '/Polygon': 'polygon', '/PolyLine': 'polyline'
      }
      const selector = selectors[a.type]
      let svg = $(pageAnnot).find('> svg').css({ overflow: 'visible' }) // make big line markers visible
      let el = svg.find('> ' + selector)
      let drawing = el.css({ stroke: rgbcss(a.color || [0, 0, 0]) })
      // almost invisible but hovering will work... not sure if we need that hovering, though
      if (selector != 'polyline')
        drawing.css({ fill: rgbcss(a.fill || '#00000001') })
      if (a.line_ending && selector == 'polyline') {
        let defs = document.getElementById('drawingLayer').querySelector('defs')
        let newdefs = defs.cloneNode()
        a.line_ending.forEach(function (le, index) {
          let tpl = defs.querySelector('#' + fixType(le).toLowerCase() + '_tpl')
          if (!tpl) return
          let def = tpl.cloneNode(true)
          def.setAttribute('id', a.object_id.obj + '_' + index)
          if (tpl.firstElementChild.getAttribute('fill') != 'transparent')
            def.firstElementChild.setAttribute('fill', rgbcss(a.fill || 'transparent'))
          def.firstElementChild.setAttribute('stroke', drawing.css('stroke'))
          newdefs.appendChild(def)
          // we reverse points as pdf.js seems to reverse draw polylines...
          el.each(function () { this.setAttribute('marker-' + (!index ? 'start' : 'end'), 'url(#' + def.id + ')') })
        })
        svg.prepend(newdefs)
      }
    }
    // text is not movable
    if (!getType(a.type).text && isEditable(a)) {
      const allowed = function (x, y) {
        let els = [...document.elementsFromPoint(x, y)]
        return els.indexOf(getPageDiv(a.page).firstChild) >= 0
      }
      draggable(pageAnnot, { stop: saveAnnot, when: e => !current && !$(e.target).is('.resizer'), allowed: allowed });
    }
    //$(pageAnnot).on('mouseenter', fixPopupPosition)
  })
}

function draggable(el, cfg) {
  let p
  let start
  //let scroller = $(el).parents().toArray().filter(x => $(x).css('scroll-y') == 'scroll')[0];
  $(el).on('mousedown', function (e) {
    e.preventDefault()
  }).on('pointerdown', function (e) {
    if (cfg.when && cfg.when(e) === false)
      return
    $('.popupWrapper').hide()
    p = { x: e.pageX, y: e.pageY }
    start = p
    el.setPointerCapture(e.pointerId)
    cfg.start && cfg.start()
  }).on('mousemove', function (e) {
    if (!p) return
    $(el).addClass('dragging')
    $('body').addClass('noselection')
    let box = this.getBoundingClientRect()
    let { left, top } = box

    let dx = e.pageX - p.x
    let dy = e.pageY - p.y
    let newLeft = box.left + dx
    let newTop = box.top + dy
    const allowed = !cfg.allowed || (
      cfg.allowed(newLeft, newTop) && cfg.allowed(newLeft + box.width, newTop + box.height))

    if (allowed) {
      left = (this.style.left ? +this.style.left.replace('px', '') : left) + (e.pageX - p.x)
      top = (this.style.top ? +this.style.top.replace('px', '') : top) + (e.pageY - p.y)
      if (cfg.apply)
        cfg.apply(this, left, top, dx, dy, start.x, start.y)
      else {
        this.style.left = left + 'px'
        this.style.top = top + 'px'
      }
      p = { x: e.pageX, y: e.pageY }
    }

  }).on('pointerup', function (e) {
    if (e.originalEvent.handled)
      return
    el.releasePointerCapture(e.pointerId)
    $(el).removeClass('dragging')
    $('body').removeClass('noselection')
    p = null
    if (!start || (start.x == e.pageX && start.y == e.pageY))
      return
    e.originalEvent.handled = true
    cfg.stop()
  })
}

function loadDocument(dataOrUrl, isnew) {
  onSideCardDeactivate()
  if (typeof dataOrUrl == 'string')
    setDocName(dataOrUrl)
  let loadingTask = pdfjsLib.getDocument(typeof dataOrUrl == 'string' ? { url: dataOrUrl } : { data: dataOrUrl })
  loadingTask.promise.then((pdfDocument) => {
    pdfDocument._pdfInfo.fingerprint = "constant"
    pdfDocument.getData().then((data) => {
      if (isnew)
        loadFactory(data)
    })
    const { scrollLeft, scrollTop } = document.getElementById('viewerContainer')
    const page = pdfViewer.currentPageNumber
    const scale = pdfViewer.currentScale
    const rotate = pdfViewer.pagesRotation
    if (pdfViewer._location) {
      pdfViewer._location.pageNumber = pdfViewer.currentPageNumber
      pdfViewer._location.scale = pdfViewer.currentScale
    }
    pdfViewer.setDocument(pdfDocument)
    if (pdfViewer.pagesCount)
      pdfViewer.currentScale = scale
    pdfViewer.eventBus.on('pagesinit', () => {
      pdfViewer.currentPageNumber = isnew ? 1 : page
      pdfViewer.currentScale = isnew ? 1 : scale
      pdfViewer.pagesRotation = isnew ? 0 : rotate
      document.getElementById('viewerContainer').scrollLeft = scrollLeft
      document.getElementById('viewerContainer').scrollTop = scrollTop
    })
  })
}

function loadFactory(data) {
  pdfFactory = new pdfAnnotate.AnnotationFactory(data)
  reloadAnnotations()
}

function reloadAnnotations() {
  pdfFactory.getAnnotations().then(function (alist) {
    annotations = alist.flat().filter(a => a.type)
    let toDelete = (pdfFactory.toDelete || []).map(a => a.object_id.obj)
    annotations = annotations.filter(a => toDelete.indexOf(a.object_id.obj) < 0)
    // Filter out links without any annotation data
    annotations = annotations.filter(a => !(a.type == '/Link' && !a.contents && !a.author && !a.appearance_object))
    annotations.forEach(a => {
      // id is for new, name is for loaded
      let id = a.id || a.name
      let m = id ? id.match(/_userid_(\d+)/) : null
      a.userId = m ? m[1] : null
    })
    refreshSidebar()
    fixAnnotations()
  })
}

function setDocName(url) {
  let index = url.lastIndexOf('/')
  let name = index ? url.substr(index + 1) : url
  $('.filename').text(name)
}

function openDocument(e) {
  detectAngle = true
  let file = e.target.files[0]
  viewerParams.originFileName = file.name
  viewerParams.saveToDataBase = function () { alert('This is not a database document') }
  setDocName(viewerParams.originFileName)
  let reader = new FileReader()
  reader.addEventListener('load', e => {
    loadDocument(new Uint8Array(e.target.result), true)
    //pdfViewer.currentScale = 1
    //pdfViewer.pagesRotation = 0
  });
  reader.readAsArrayBuffer(file);
}

function saveAndReload() {
  let saved = pdfFactory.write()
  loadDocument(saved)
  // we don't need the data from the loaded document - load factory right now
  //loadFactory(saved)
  reloadAnnotations()
}

function updatePageNumber() {
  $('#pagenum').val(pdfViewer.currentPageNumber)
  $("#page").text(` / ${pdfViewer.pagesCount} `)
}
function goToPage(page, offset) {
  page = page || pdfViewer.currentPageNumber + offset
  if (pdfViewer.currentPageNumber != page) {
    pdfViewer.currentPageNumber = page

    let scrollHandler = function () {
      let pageDiv = $('.page[data-page-number="' + pdfViewer.currentPageNumber + '"]')[0]
      if (!pageDiv)
        pdfViewer.eventBus.on('pagesinit', scrollHandler)
      else {
        $('#viewerContainer').animate({
          scrollTop: $('.page[data-page-number="' + pdfViewer.currentPageNumber + '"]')[0].offsetTop - 30
        }, 200)
        pdfViewer.eventBus.off('pagesinit', scrollHandler)
      }
    }

    scrollHandler()
  }

  updatePageNumber()
}

function fixAngle(angle) {
  while (angle >= 360) angle -= 360
  while (angle < 0) angle += 360
  return angle
}

function rotate(angle) {
  angle = pdfViewer.pagesRotation + angle
  angle = fixAngle(angle)
  pdfViewer.pagesRotation = angle
  let rules = document.getElementById('pdfPopupRotationRules')
  if (!rules) {
    rules = document.createElement('style')
    rules.id = 'pdfPopupRotationRules'
    rules.type = 'text/css'
    document.head.appendChild(rules)
  }
  // avoid the popup being rotated
  //$(rules).text('.popupWrapper .popup { transform: rotate(' + (-pdfViewer.pagesRotation) + 'deg')
}

let scrollBeforeFind

function find(e) {
  if (e.code == 'Enter') {
    if (e.target.value && scrollBeforeFind === undefined)
      scrollBeforeFind = $('#viewerContainer')[0].scrollTop

    // find options: search, phraseSearch, caseSensitive, entireWord, highlightAll, findPrevious
    pdfViewer.findController.executeCommand('findagain', {
      query: e.target.value,
      phraseSearch: true,
      highlightAll: true,
      findPrevious: e.shiftKey
    })
    findscroll()
  } else if (e.code == 'Escape') {
    e.target.value = ''
    pdfViewer.findController.executeCommand('findagain', { query: '' })
    findscroll()
  }
}

let findScrollTimerId
function findscroll() {
  clearTimeout(findScrollTimerId)
  findScrollTimerId = setTimeout(() => {
    let highlight = $('span.highlight.selected')[0]
    if (highlight)
      highlight.scrollIntoView({ behavior: 'smooth' })
    else if (scrollBeforeFind !== undefined) {
      $('#viewerContainer').animate({ scrollTop: scrollBeforeFind })
      scrollBeforeFind = undefined
    }
  }, 100);
}

function setupViewer(url) {
  let pdfContainer = document.getElementById('viewerContainer')

  var eventBus = new pdfjsViewer.EventBus();
  var pdfLinkService = new pdfjsViewer.PDFLinkService({
    eventBus: eventBus,
  });
  var pdfFindController = new pdfjsViewer.PDFFindController({
    eventBus: eventBus,
    linkService: pdfLinkService,
  });
  pdfViewer = new pdfjsViewer.PDFViewer({
    container: pdfContainer,
    eventBus: eventBus,
    // issues with findcontroller - fix
    //linkService: pdfLinkService,
    findController: pdfFindController,
  })
  pdfViewer._buffer.resize(10000, 10000)
  pdfLinkService.pdfViewer = pdfViewer
  pdfViewer.eventBus.on('pagesinit', function (e) {
    pdfViewer.pagesRotation = 0
    pdfLinkService.setDocument(pdfViewer.pdfDocument)
    pdfFindController.setDocument(pdfViewer.pdfDocument)
  });
  pdfViewer.eventBus.on('updatefindmatchescount', data => {
    $('.matches').text(`${data.matchesCount.current}/${data.matchesCount.total}`)
    findscroll()
  });
  pdfViewer.eventBus.on('updatefindcontrolstate', data => {
    $('.matches').text(`${data.matchesCount.current}/${data.matchesCount.total}`)
    findscroll()
  });
  pdfViewer.eventBus.on('textlayerrendered', function (e) {
    if (e.pageNumber && detectAngle) {
      let angles = e.source.textDivs.map(getElAngle)
      let counts = angles.reduce((p, x) => { p[x] = (p[x] || 0) + 1; return p }, {})
      for (var k in counts)
        if (k != '0' && counts[k] > angles.length / 2)
          pdfViewer.pagesRotation = 360 - +k
      detectAngle = false
    }
    // comment icons should not be rotated
    //getPageDiv(e.pageNumber - 1).querySelectorAll('section.textAnnotation > img').forEach(img => 
    //        img.style.transform = 'rotate(' + (-pdfViewer.pagesRotation) + 'deg)')

    updatePageNumber()
    fixAnnotations(getPageDiv(e.pageNumber - 1))
    refreshSelectedAnnot()
  })
  pdfViewer.eventBus.on('pagechanging', function (e) {
    updatePageNumber()
    for (let page = pdfViewer.currentPageNumber; page > 0; page--) {
      let sep = $(`.sidebar-page-sep[data-page=${pdfViewer.currentPageNumber}]`)[0];
      if (sep) {
        sep.scrollIntoView()
        break
      }
    }
    fixAnnotations(getPageDiv(e.pageNumber - 1))
  })

  loadDocument(url, true)

  document.addEventListener('wheel', e => {
    if (e.ctrlKey) {
      e.preventDefault()
      pdfViewer.currentScale += e.deltaY * -0.001
    }
  }, { passive: false })

  draggable($('.sidebar-splitter')[0], {
    when: function () {
      return current == null
    },
    apply: function (el, left, top) {
      //el.style.left = left + 'px'
      document.documentElement.style.setProperty('--sidebar-width', left + 'px')
      //$('.sidebar').width(left)
      //$('#main').width($('body').width() - left).css({ left: left + 'px' })
    },
    stop: function () {
    }
  })
}

// keep the document with annotations when we hide them - used to restore
let dataWithAnnotations

function toggleAnnotations(btn) {
  let icon = $('#toggleAnnotations').find('i')
  if (icon.hasClass('fa-eye-slash')) {
    $('.toolbar').children().show()
    icon.removeClass('fa-eye-slash').addClass('fa-eye')
    loadDocument(dataWithAnnotations)
    loadFactory(dataWithAnnotations)
    dataWithAnnotations = null
  }
  else {
    $('.toolbar').children().hide()
    icon.removeClass('fa-eye').addClass('fa-eye-slash')
    if (current)
      current.stop()
    dataWithAnnotations = pdfFactory.write()
    pdfFactory.getAnnotations().then(function (alist) {
      Promise.allSettled(alist.flat().map(deleteAnnotation)).then(() => {
        saveAndReload()
      })
    })
  }
}

/* utility */

function getAnnotFontSize(annot) {
  if (!annot.defaultStyling) return null
  let pt = annot.defaultStyling.split(';').map(x => x.trim()).filter(x => x.indexOf('font:') == 0)
    .map(x => x.match(/([-+]?[0-9]*\.?[0-9])+pt/))[0]
  return pt ? pt[1] : null
}

function elByClassAt(x, y, cls) {
  return document.elementsFromPoint(x, y).filter(e => e.classList.contains(cls))[0]
}

function camelSplit(s) {
  return s.replace(/([a-z])([A-Z])/g, '$1 $2')
}

function parseColor(clr) {
  if (!clr)
    return clr
  if (clr[0] == '#')
    return clr.match(/\w\w/g).map(x => parseInt(x, 16) / 255.0)
  throw "Color type not supported"
}

function fixClrObj(arr) {
  if (!arr || (arr.r !== undefined && arr.g !== undefined && arr.b !== undefined))
    return arr
  if (!Array.isArray(arr))
    arr = parseColor(arr)
  return { r: arr[0], g: arr[1], b: arr[2] }
}
function rgbcss(color, opacity) {
  if (!color)
    return '#000000'
  if (typeof color == 'string')
    return color
  return '#' + color.map(c => {
    const h = (c * 255).toString(16)
    return h.length === 1 ? '0' + h : h
  }).join('')
}
function bordercss(border, color) {
  if (!border) return 'none'
  let w = border.border_width || border[2]
  return w + 'px solid ' + rgbcss(color)
}

function getPdfDate(date) {
  if (!date) return ''
  date = date.replace('(D:', '').replace('D:', '')
  date = date.substr(0, 4) + '-' + date.substr(4, 2) + '-' + date.substr(6, 2) + 'T' +
    date.substr(8, 2) + ':' + date.substr(10, 2) + ':' + date.substr(12, 2) + 'Z'
  return new Date(date)
}
function fmtPdfDate(date) {
  if (!date) return ''
  date = date.replace('(D:', '').replace('D:', '')
  date = date.substr(0, 4) + '-' + date.substr(4, 2) + '-' + date.substr(6, 2)
  date = new Date(date).toLocaleDateString()
  return date
}

function getElAngle(el) {
  const m = el.style.transform.match(/rotate\((-*\d+)deg\)/)
  let angle = m ? parseInt(m[1]) : 0
  if (angle < 0)
    angle += 360
  return angle
}

function measureText(node, text) {
  // TODO: measure without inserting
  var cloned = node.cloneNode()
  cloned.style.width = "auto"
  cloned.style.height = "auto"
  cloned.style.display = "inline"
  cloned.style.transform = cloned.style.transform.replace(/rotate\(.*?\)/, '')
  cloned.textContent = text
  node.parentElement.appendChild(cloned)
  let { width, height } = cloned.getBoundingClientRect()
  cloned.remove()
  return { width: width, height: height }
}

function shiftRectLeft(r, w, a) {
  switch (a) {//pdfViewer.pagesRotation) {
    case 0: r.left += w; break;
    case 90: r.top += w; break;
    case 180: r.right -= w; break;
    case 270: r.bottom -= w; break;
  }
  return r;
}

function shiftRectRight(r, w, a) {
  switch (a) { //pdfViewer.pagesRotation) {
    case 0: r.right -= w; break;
    case 90: r.bottom -= w; break;
    case 180: r.left += w; break;
    case 270: r.top += w; break;
  }
  return r;
}

function createTextSelectionAppearance(type, angle, rect, pagenum) {
  // for simplicity (to avoid text/page rotation combinations multiplication) 
  // we draw for text as it is on screen and then create line between two points converted to pdf
  const { left: l, top: t, right: r, bottom: b } = rect
  let line = []
  // not sure how to organize it except for switches...
  switch (type) {
    case 'Underline':
      switch (angle) {
        case 0: line = [l, b, r, b]; break
        case 90: line = [l, t, l, b]; break
        case 180: line = [l, t, r, t]; break
        case 270: line = [r, t, r, b]; break
      }
      break
    case 'StrikeOut':
      switch (angle) {
        case 0: line = [l, b + (t - b) * 2 / 3, r, b + (t - b) * 2 / 3]; break
        case 180: line = [l, b + (t - b) / 3, r, b + (t - b) / 3]; break
        case 90: line = [l + (r - l) * 0.6, t, l + (r - l) * 0.6, b]; break
        case 270: line = [l + (r - l) * 0.4, t, l + (r - l) * 0.4, b]; break
      }
      break
    case 'Squiggly':
      switch (angle) {
        case 0: case 180: {
          let off = angle == 0 ? -2 : 2
          let y = angle == 0 ? b + off : t + off
          line = []
          for (let n = 0; n < (r - l) / 8; n++) {
            let x = l + n * 8
            line = line.concat([x, y + (-off * 2), x + 4, y])
          }
        } break
        case 90: case 270: {
          let off = angle == 270 ? -2 : 2
          let x = angle == 270 ? r + off : l + off
          line = []
          for (let n = 0; n < (b - t) / 8; n++) {
            let y = t + n * 8
            line = line.concat([x + 4, y, x, y + 4])
          }
        } break
      }
      break
  }
  var pfmt = p => p.map(x => x.toFixed(2)).join(' ')
  if (line.length == 4) {
    let p1 = pointToPdf(line[0], line[1], pagenum)
    let p2 = pointToPdf(line[2], line[3], pagenum)
    return `${pfmt(p1)} m\n${pfmt(p2)} l`
  } else {
    let cmds = []
    for (var i = 0; i < line.length / 2; i++) {
      let p = pointToPdf(line[i * 2], line[i * 2 + 1], pagenum)
      if (i == 0)
        cmds.push(`${pfmt(p)} m`)
      cmds.push(`${pfmt(p)} l`)
    }
    return cmds.join('\n')
  }
}

function selectionQuads(type, range) {
  // convert selection into array of quads - rect coords for each line or run
  range = range || window.getSelection().getRangeAt(0)
  let node = range.startContainer
  let endNode = range.endContainer
  if (endNode.nodeType == Node.TEXT_NODE)
    endNode = endNode.parentElement
  let lastNode = node
  let quads = []
  let stream = [] // appearance stream commands
  let pagenum = $(node).closest('.page').data('pageNumber') - 1
  do {
    if (node.nodeType == Node.TEXT_NODE)
      node = node.parentElement
    let rect = node.getBoundingClientRect()
    rect = { left: rect.left, top: rect.top, right: rect.right, bottom: rect.bottom }

    if (node == range.startContainer.parentElement) {
      let startDw = measureText(node, node.textContent.substr(0, range.startOffset)).width
      rect = shiftRectLeft(rect, startDw, getElAngle(node))
      if (node == endNode) {
        let endDw = measureText(node, node.textContent.substr(range.endOffset)).width
        rect = shiftRectRight(rect, endDw, getElAngle(node))
      }
    } else if (node == range.endContainer.parentElement) {
      let endDw = measureText(node, node.textContent.substr(range.endOffset)).width
      rect = shiftRectRight(rect, endDw, getElAngle(node))
    }

    let [x1, y1, x2, y2] = rectToPdf(rect, pagenum)
    quads = quads.concat([x1, y1, x2, y1, x1, y2, x2, y2])

    // for rotated text we need to created appearance streams and calculate what side to use
    let angle = getElAngle(node)
    if (angle || pdfViewer.pagesRotation) {
      let cmd = createTextSelectionAppearance(type, angle, rect, pagenum)
      stream.push(cmd)
    }
    lastNode = node
    node = node.nextSibling
  } while (node && lastNode != endNode && range.startContainer != range.endContainer)

  return { quads: quads, appearance: stream.join('\n'), page: pagenum }
}

/* actions */

let current = null

class Action {
  constructor(type, annot) {
    if (current)
      current.stop()
    current = this

    this.type = type
    this.annot = annot
    this.params = {}
    this.points = []

    $('body').addClass('noselection')
    $(window).on('keydown.action', e => e.code == 'Escape' ? this.stop() : undefined)
    $('.page').on('mousedown.action', e => this.ondown(e))
    $('.page').on('mousemove.action', e => this.onmove(e))
    $('.page').on('mouseup.action', e => this.onup(e))
    $('.page').addClass(this.constructor.name.replace('Action', '').toLowerCase() + '-action')

    //this.color = getType(this.type) ? rgbcss(getType(this.type).defaultColor) : '#000000'
  }
  setParams(params) {
    this.params = params || {}
    this.params.border_width = this.params.border ? this.params.border.border_width : 1
    this.updatePlaceholder()
  }
  updatePlaceholder() {
  }
  start() {
    this.started = true
    this.points = []
  }
  stop() {
    $('body').removeClass('noselection')
    $(window).off('keydown.action')
    $('.page').off('mousedown.action')
    $('.page').off('mousemove.action')
    $('.page').off('mouseup.action')
    $('.page').removeClass(this.constructor.name.replace('Action', '').toLowerCase() + '-action')
    onSideCardDeactivate()
    current = null
    this.points = []
  }
  save() {
    saveAndReload()
    this.stop()
  }
  prepare(annot) {
    let def = getType(this.type) || {}
    let rect = rectToPdf(this.rect(), this.page)
    if (annot && annot.border)
      rect = inflateRect(rect, annot.border.border_width)
    return Object.assign(this.annot || {}, annot || {}, {
      page: this.page,
      type: this.type,
      rect: rect,
      author: viewerParams.authorAnnotation.name || 'Author',
      color: annot.color || def.defaultColor || [0, 0, 0]
    })
  }
  exec() {
    this.started = false
    showPopBar(this.points[this.points.length - 1], {
      type: this.type,
      ok: (info) => {
        createAnnotation(this.prepare(info))
        this.save()
      },
      cancel: () => this.stop()
    })
  }
  rectFromPoints(points, exact) {
    let xs = points.map(p => p.x)
    let ys = points.map(p => p.y)
    let off = typeof exact == 'number' ? exact : (exact ? 0 : 2)
    return {
      left: Math.min(...xs) - off,
      top: Math.min(...ys) - off,
      right: Math.max(...xs) + off,
      bottom: Math.max(...ys) + off
    }
  }
  rect(exact) {
    let p1 = this.points[0]
    let p2 = this.points[this.points.length - 1]
    return this.rectFromPoints([p1, p2], exact)
  }

  isvalid(p) {
    let els = [...document.elementsFromPoint(p.x, p.y)]
    return this.page === undefined || els.indexOf(getPageDiv(this.page).firstChild) >= 0
  }
  beforedown(e, p) {
    // most types except e.g. Ink don't support multiple rects
    return this.points.length === 0 && this.isvalid(p)
  }
  beforemove(e, p) {
    return this.isvalid(p)
  }
  down(e, p) { }
  move(e, p) { }
  up(e, p) {
    this.exec()
  }
  ondown(e) {
    let p = { x: e.pageX, y: e.pageY, action: 'start' }
    if (!this.beforedown(e, p))
      return

    if (!this.page) {
      this.pagediv = document.elementsFromPoint(p.x, p.y).filter(e => e.classList.contains('page'))[0]
      this.page = this.pagediv.dataset.pageNumber - 1
    }
    this.start()

    this.points.push(p)
    this.down(e, p)
  }
  onmove(e) {
    if (!this.started)
      return

    let p = { x: e.pageX, y: e.pageY, action: 'move' }
    if (!this.beforemove(e, p))
      return
    this.points.push(p)
    this.move(e, p)
  }
  onup(e) {
    if (!this.started)
      return
    let p = { x: e.pageX, y: e.pageY, action: 'up' }
    if (this.isvalid(p))
      this.points.push(p)
    this.up(e, p)
  }
}

class RectAction extends Action {
  constructor(type, annot) {
    super(type, annot)
  }
  start() {
    super.start()
    $('<div id="placeholder"/>').appendTo('body')
  }
  stop() {
    super.stop()
    $('#placeholder').remove()
  }
  move(e, p) {
    this.updatePlaceholder()
  }
  prepare(annot) {
    annot = super.prepare(annot)
    annot.rotate = fixAngle(pdfViewer.pagesRotation + getPageRotate(this.page))
    return annot
  }
  updatePlaceholder() {
    let r = this.rect(false)
    let c = rgbcss(this.params.color || 'black')
    let f = this.params.fill ? rgbcss(this.params.fill) : 'transparent'

    $('#placeholder').toggle(this.points.length > 1).css({
      width: (r.right - r.left) + 'px',
      height: (r.bottom - r.top) + 'px',
      left: r.left + 'px',
      top: r.top + 'px'
    }).css({
      border: (this.params.border_width || 1) + 'px solid ' + c,
      'border-radius': this.type == 'Circle' ? '50%' : 0,
      background: f,
      opacity: this.params.opacity || 1
    })
  }
}

function canvasRoundRect(ctx, x, y, w, h, r) {
  if (w < 2 * r) r = w / 2
  if (h < 2 * r) r = h / 2
  ctx.beginPath()
  ctx.moveTo(x + r, y)
  ctx.arcTo(x + w, y, x + w, y + h, r)
  ctx.arcTo(x + w, y + h, x, y + h, r)
  ctx.arcTo(x, y + h, x, y, r)
  ctx.arcTo(x, y, x + w, y, r)
  ctx.closePath()
  return ctx
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
    }
    else {
      line = testLine;
    }
  }
  context.fillText(line, x, y);
  return y + lineHeight
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

    block.css({ verticalAlign: 'baseline' });
    result.ascent = block.offset().top - text.offset().top;

    block.css({ verticalAlign: 'bottom' });
    result.height = block.offset().top - text.offset().top;

    result.descent = result.height - result.ascent;

  } finally {
    div.remove();
  }

  return result;
};

function createTextImage(annot, asimg) {
  let sz = pdfrectToViewportSize(annot.rect, annot.page)
  let angle = getPageRotate(annot.page) + pdfViewer.pagesRotation
  angle = fixAngle(angle)
  if (angle == 90 || angle == 270)
    sz = { width: sz.height, height: sz.width }
  const scaleForQuality = 2
  sz.width *= scaleForQuality
  sz.height *= scaleForQuality

  let canvas = document.createElement('canvas')
  canvas.width = sz.width
  canvas.height = sz.height
  let ctx = canvas.getContext('2d')

  ctx.globalAlpha = annot.opacity || 1
  ctx.fillStyle = rgbcss(annot.color)
  ctx.fillRect(0, 0, canvas.width, canvas.height)
  ctx.strokeRect(0, 0, canvas.width, canvas.height)
  //ctx.globalAlpha = 1

  ctx.save()
  if (angle) {
    ctx.translate(canvas.width / 2, canvas.height / 2)
    ctx.rotate(-angle * Math.PI / 180)
    switch (angle) {
      case 90: ctx.translate(-canvas.height / 2, -canvas.width / 2); break
      case 180: ctx.translate(-canvas.width / 2, -canvas.height / 2); break
      case 270: ctx.translate(-canvas.height / 2, -canvas.width / 2); break
    }
  }

  ctx.textAlign = "left"
  ctx.textBaseline = "top"
  // reduce size to make space for borders
  let fsz = getAnnotFontSize(annot) || 9
  fsz *= scaleForQuality
  ctx.font = 'normal normal ' + fsz + 'pt "Arial"'
  ctx.fillStyle = 'black'
  let y = 5
  const lh = getTextHeight({ 'font-size': fsz + 'pt' }).height;
  (annot.contents || '').split('\n').forEach(line => {
    y = wrapText(ctx, line, 5, y, canvas.width - 10, lh)
  })
  ctx.restore()

  return asimg ? canvas.toDataURL("image/png") : ctx.getImageData(0, 0, canvas.width, canvas.height)
}

class FreeTextAction extends RectAction {
  constructor(type, annot) {
    super(type, annot)
  }
  updatePlaceholder() {
    super.updatePlaceholder()
    //let img = createTextImage(this.annot, true)
    const text = this.params.contents || ''
    $('#placeholder').css({
      'white-space': 'pre-wrap',
      'font-size': (getAnnotFontSize(this.params) || 9) + 'pt',
      overflow: 'hidden',
      border: '1px solid black',
      'background-color': 'yellow'
    }).text(text.split('\n'))
  }
}

class StampAction extends RectAction {
  constructor(type, annot) {
    super(type, annot)
    if (!annot.stampType) {
      this.stop()
      let form = $(`<div class="modal">
                        <form onsubmit="arguments[0].preventDefault()">
                                <div class="stamp-preview" style="text-align: center; overflow: hidden; width: 100%; height: 50px; font-size: 40px; font-style: italic; font-weight: bold;"></div>
                                <div>Stamp type: <select name="stampType"></select></div>
                                <div>Stamp text: <input type="text" name="stampText"></div>
                                <div>Font: <input type="text" name="font"></div>
                                <div>Text color: <input type="color" name="color"></div>
                                <div>Fill from: <input type="color" name="stops0"></div>
                                <div>Fill to: <input type="color" name="stops1"></div>
                                <div style="text-align: right">
                                        <button onclick="$(this).closest('.modal').remove()">Cancel</button>
                                        <button class="ok">OK</button>
                                </div>
                        </form>
                </div>`)
      setupStampsOptions(form.find('[name=stampType]'))
      let get = () => ({
        stampType: form.find('select').val(),
        stampText: form.find('input').val(),
        color: form.find('[name=color]').val(),
        stops: [form.find('[name=stops0]').val(), form.find('[name=stops1]').val()],
        font: form.find('[name=font]').val(),
      })
      $(form).appendTo('body').find('.ok').click(function () {
        new StampAction(type, Object.assign(annot, get()))
        form.remove()
      })
      let onchange = () => {
        let cfg = this.config(get())
        form.find('.stamp-preview').text(cfg.text).css(cfg.css)
      }
      form.find('[name=stampText]').val(form.find('select').val())
      form.find('[name=stampType').on('change', e => {
        let cfg = stamps[e.target.value]
        form.find('[name=stampText]').val(camelSplit(e.target.value))
        form.find('[name=color]').val(cfg.color)
        form.find('[name=stops0]').val(cfg.stops[0])
        form.find('[name=stops1]').val(cfg.stops[1])
        setTimeout(onchange, 1)
      }).trigger('change')
      onchange()
      form.find('input, select').on('input', onchange)
      $('#addStampAnnotation').blur().val('Stamp')
    }
  }
  config(annot) {
    annot = annot || this.annot
    let cfg = stamps[fixType(annot.stampType)]
    if (annot.stampText) {
      cfg = {
        color: annot.color,
        stops: annot.stops,
        font: annot.font,
        angle: annot.angle
      }
    }
    cfg.css = {
      background: `linear-gradient(45deg, ${cfg.stops[0]}, ${cfg.stops[1]})`,
      color: cfg.color,
      'font-family': cfg.font
    }
    cfg.text = annot.stampText || camelSplit(fixType(annot.stampType))
    return cfg
  }
  createStampImage() {
    let r = this.rect()
    let angle = fixAngle(getPageRotate(this.page) + pdfViewer.pagesRotation)
    if (angle == 90 || angle == 270)
      r = { left: r.top, right: r.bottom, top: r.left, bottom: r.right }
    // that's for better scaling
    for (var k in r)
      r[k] *= 1.5

    let canvas = document.createElement('canvas')
    const sw = 2
    canvas.width = (r.right - r.left)// + sw * 4
    canvas.height = (r.bottom - r.top)// + sw * 4
    let ctx = canvas.getContext('2d')

    ctx.globalAlpha = this.params.opacity || 1
    ctx.fillStyle = 'rgb(0, 0, 0, 0)'
    ctx.fillRect(0, 0, canvas.width, canvas.height)

    const cfg = this.config()

    var grd = ctx.createLinearGradient(0, 0, canvas.width, canvas.height)
    grd.addColorStop(0, cfg.stops[0])
    grd.addColorStop(1, cfg.stops[1])
    ctx.fillStyle = grd
    ctx.strokeStyle = cfg.color
    ctx.lineWidth = 4
    const ofs = sw * 2
    canvasRoundRect(ctx, ofs, ofs, canvas.width - ofs * 2, canvas.height - ofs * 2, 6)
    ctx.shadowBlur = sw
    let offsets = { 0: [sw, sw], 90: [sw, -sw], 180: [-sw, -sw], 270: [-sw, sw] }
    ctx.shadowOffsetX = offsets[angle][0]
    ctx.shadowOffsetY = offsets[angle][1]
    ctx.shadowColor = '#aaa'
    ctx.stroke()
    ctx.shadowBlur = 0
    ctx.shadowColor = 'transparent'
    ctx.fill()

    ctx.save()
    ctx.translate(canvas.width / 2, canvas.height / 2)
    ctx.rotate(-angle * Math.PI / 180)

    ctx.textAlign = 'center'
    ctx.textBaseline = 'middle'
    // reduce size to make space for borders
    let fsz = 1.5 * ($('#placeholder').css('font-size').replace('px', '') * 0.9) + 'px'
    ctx.font = 'bold italic ' + fsz + ' "' + (cfg.font || $('#placeholder').css('font-family')) + '"'
    ctx.fillStyle = cfg.color
    ctx.fillText(cfg.text, 0, 0)
    ctx.restore()

    //$('.sidebar-body').append(canvas) // dbg

    let data = ctx.getImageData(0, 0, canvas.width, canvas.height)
    return data
  }
  prepare(annot) {
    let image = this.createStampImage()
    annot = Object.assign({ image: image }, super.prepare(annot))
    delete annot.rotate
    return annot
  }
  up(e, p) {
    this.updatePlaceholder()
    super.up(e, p)
  }
  setParams(params) {
    this.norecalc = true
    super.setParams(params)
    delete this.norecalc
  }
  updatePlaceholder() {
    let r = this.rect(true)
    let w = r.right - r.left
    let h = r.bottom - r.top

    let cfg = this.config()
    let text = cfg.text
    // this is an approximate instead of text measure
    $('#placeholder').css({ opacity: this.params.opacity })
    if (cfg.text)
      $('#placeholder').css(cfg.css)
    if (!this.norecalc) {
      $('#placeholder').addClass("stamp").addClass(this.annot.stampType).css('font-size', (2 * w / text.length) + 'px')
      let sz = measureText($('#placeholder')[0], text)
      w = sz.width
      h = sz.height
      $('#placeholder').text(text)
      let p1 = this.points[0]
      let p2 = { x: p1.x + w, y: p1.y + h }
      this.points = [p1, p2]
      r = this.rect(false)

      $('#placeholder').toggle(this.points.length > 1).css({
        width: (r.right - r.left) + 'px',
        height: (r.bottom - r.top) + 'px',
        left: r.left + 'px',
        top: r.top + 'px'
      })
    }
  }
}

class PointAction extends Action {
  constructor(type, annot) {
    super(type, annot)
  }
  rect() {
    // TODO: FIX FOR ROTATION? Set annot flag to disable rotation?
    let p = this.points[0]
    return Object.assign({ left: p.x, top: p.y, right: p.x + 22, bottom: p.y + 22 })
  }
  down() {
    this.exec()
  }
}

class DrawPolyBaseAction extends Action {
  constructor(type, annot) {
    super(type, annot)
    this.parts = []
  }
  stop() {
    super.stop()
    this.parts = []
    this.updatePlaceholder()
  }
  beforedown(e, p) {
    return this.isvalid(p)
  }
  move() {
    this.updatePlaceholder()
  }
}

class DrawAction extends DrawPolyBaseAction {
  up() {
    this.parts.push(this.points)
    if (this.parts.length == 1)
      this.exec()
    this.started = false

    // for convenience re-focus text input after draw
    $('.side-card.active iframe').ifeditor('focus')
  }
  rect(exact) {
    return this.rectFromPoints(this.parts.flat(), exact)
  }
  prepare(annot) {
    return Object.assign(super.prepare(annot), {
      inkList: this.parts.map(d => d.map(p => pointToPdf(p.x, p.y, this.page)).flat())
    })
  }
  down(e, p) {
    this.updatePlaceholder()
  }
  beforemove(e, p) {
    return this.isvalid(p)
  }
  updatePlaceholder() {
    let box = document.getElementById('drawingLayer').getBoundingClientRect()
    $('#drawingLayer > path').remove()
    let svg = document.getElementById('drawingLayer')
    this.parts.concat([this.points]).forEach(points => {
      let path = document.createElementNS('http://www.w3.org/2000/svg', 'path')
      path.setAttribute('d', points.map((p, i) => `${(i ? 'L' : 'M')} ${p.x - box.left} ${p.y}`).join(' '))
      path.setAttribute('stroke', rgbcss(this.params.color || 'black'))
      path.setAttribute('stroke-width', this.params.border_width)
      path.setAttribute('fill', 'none')
      svg.appendChild(path)
    })
    $(svg).css({ opacity: this.params.opacity })
  }
}

class PolyAction extends DrawPolyBaseAction {
  rect(exact) {
    return this.rectFromPoints(this.parts, exact)
  }
  prepare(annot) {
    return Object.assign(super.prepare(annot), {
      // compensate for possible line endings - no need, using overflow: visible on svg instead
      //rect: rectToPdf(this.rect(6), this.page),
      vertices: this.parts.map(p => pointToPdf(p.x, p.y, this.page)).flat()
    })
  }
  up(e, p) {
    this.started = true
    if (this.parts.length >= 2)
      // for convenience re-focus text input after draw
      $('.side-card.active iframe').ifeditor('focus')
  }
  down(e, p) {
    this.parts.push(p)
    this.updatePlaceholder()

    if (this.parts.length == 2) {
      this.exec()
      this.started = true
    }
  }
  beforemove(e, p) {
    return true
  }
  updatePlaceholder() {
    let box = document.getElementById('drawingLayer').getBoundingClientRect()
    $('#drawingLayer > path').remove()
    let svg = document.getElementById('drawingLayer')
    let path = document.createElementNS('http://www.w3.org/2000/svg', 'path')
    let last = this.points[this.points.length - 1]
    let points = this.parts.concat(last && this.isvalid(last) ? [last] : [])
    path.setAttribute('d', points.map((p, i) => `${(i ? 'L' : 'M')} ${p.x - box.left} ${p.y}`).join(' '))
    path.setAttribute('stroke', rgbcss(this.params.color || 'black'))
    path.setAttribute('stroke-width', this.params.border_width)
    path.setAttribute('fill', 'none')
    svg.appendChild(path)
    $(svg).css({ opacity: this.params.opacity })
  }
}

class FigureAction extends RectAction {
  updatePlaceholder1() {
  }
}

// extend RectAction to support drawing over non-text regions
class TextAction extends RectAction {
  constructor(type, annot) {
    super(type, annot)
    $('body').removeClass('noselection')
    if (!window.getSelection().isCollapsed) {
      this.selection = window.getSelection().getRangeAt(0)
      let el = window.getSelection().getRangeAt(0).endContainer.parentElement
      let box = el.getBoundingClientRect()
      this.points = [{ x: box.left, y: box.top }]
      this.page = $(el).closest('.page').data('pageNumber') - 1
      this.exec()
    }
  }
  pdfrect() {
    return rectFromArray(this.quads)
  }
  prepare(annot) {
    let makestream = cmds => !cmds ? null :
      Object.values(fixClrObj(this.params.color || getType(this.type).defaultColor)).join(' ') + ' RG\n1 w\n' + cmds + '\nS\n'

    if (this.selection) {
      let { quads, appearance, page } = selectionQuads(this.type, this.selection)
      this.page = this.page || page
      this.quads = quads
      this.appearance = makestream(appearance)
    } else {
      let rect = this.rect()
      let [x1, y1, x2, y2] = rectToPdf(rect, this.page)
      this.quads = [x1, y1, x2, y1, x1, y2, x2, y2]
      this.appearance = //!pdfViewer.pagesRotation ? null : 
        makestream(createTextSelectionAppearance(this.type, 0, rect, this.page))
    }
    return Object.assign(super.prepare(annot), {
      quadPoints: this.quads,
      appearance: this.appearance,
      rect: [] //this.pdfrect() // lib will autocalculate
    })
  }
  up(e, p) {
    if (this.textSelection)
      this.selection = window.getSelection().getRangeAt(0)
    return super.up(e, p)
  }
  down(e, p) {
    // TODO: handle double clicks... second click resets our points
    let els = document.elementsFromPoint(p.x, p.y)
    if (els[0].tagName == 'SPAN' && [...els].filter(el => el.classList.contains('textLayer'))) {
      // selecting text - do nothing
      this.textSelection = true
      $('#placeholder').remove()
    } else {
      // selecting non-text - draw rect
      $('body').addClass('noselection')
      this.textSelection = false
    }
  }
}

/* popbar */

function showPopBar(p, setup) {
  let card = createAnnotCard(Object.assign({}, setup, {
    page: pdfViewer.currentPageNumber - 1,
    author: viewerParams.authorAnnotation.name || 'Author',
    color: getType(setup.type).defaultColor
  }), setup)
  let body = $('.sidebar > .sidebar-body')
  body.prepend(card)
}

/* sidebar */

function refreshSelectedAnnot() {
  let card = $('.side-card.active')
  let svg = document.getElementById('pointers')
  let h = $('.toolbar').height()
  svg.style.top = h + 'px'
  svg.style.height = $('#viewerContainer').height() + 'px'
  let sz = svg.getBoundingClientRect()
  svg.setAttribute("viewBox", `0 ${h} ${sz.width} ${sz.height - h}`)
  svg.querySelector('polyline').setAttribute('points', '')
  if (selectedAnnot) {
    let pageAnnot = $('.annotationLayer > section[data-annotation-id=' + selectedAnnot.object_id.obj + 'R]')
    pageAnnot.addClass('selected')

    if (pageAnnot[0]) {
      const ofs = 4
      let { right: r1, top: t1, bottom: b1, height: h1 } = card[0].getBoundingClientRect()
      let { left: l2, top: t2, bottom: b2, height: h2 } = pageAnnot[0].getBoundingClientRect()
      l2 -= 6
      r1 += 6
      t1 -= h
      t2 -= h
      let points = [[r1 - ofs, t1], [r1, t1], [r1, b1], [r1 - ofs, b1], [r1, b1], [r1, t1 + h1 / 2 + h / 2],
      [l2, t2 + h2 / 2 + h / 2], [l2, t2], [l2 + 2, t2], [l2, t2], [l2, b2], [l2 + 2, b2], [l2, b2]]
      svg.querySelector('polyline').setAttribute('points', points.map(p => p.join(',')).join(' '))
    }
  } else {
    $('.annotationLayer > section').removeClass('selected')//.find('.popupWrapper').attr('hidden', true)
  }
}

function getSideCardAnnotation(card) {
  let id = card.data('annotationId')
  return annotations.filter(x => x.object_id.obj == id)[0]
}

function onSideCardActivate(card) {
  selectedAnnot = getSideCardAnnotation(card)
  //if (selectedAnnot && !isEditable(selectedAnnot))
  //        return

  let curcard = $('.side-card.active')
  if (curcard.length && curcard[0] != card[0])
    onSideCardDeactivate()

  card.addClass('active')
  selectedAnnot = getSideCardAnnotation(card)
  if (selectedAnnot) {
    goToPage(selectedAnnot.page + 1)
    if (selectedAnnot.object_id) {
      $('.page').on('click.sidecard', function () {
        onSideCardDeactivate()
      })
      if (!isEditable(selectedAnnot))
        card.addClass('readonly')
    }
  }
  refreshSelectedAnnot()
}
function onSideCardDeactivate() {
  $('body').removeClass('noselection')
  $('.page').off('click.sidecard')

  let card = $('.side-card.active')
  if (!card.length)
    return

  card.removeClass('active')
  let a = card.prop('annot')
  if (!a.object_id)
    card.remove()
  else
    card.find('iframe').ifeditor('set', a.contents) // restore text
  card.find('iframe').ifeditor('blur', a.contents)

  $("#addStampAnnotation").val('Stamp')
  if (current)
    current.stop()

  selectedAnnot = null
  refreshSelectedAnnot()
}

$.fn.ifeditor = function (method, arg, arg2) {
  let doc = this[0].contentDocument
  let fmt = s => s ? s.replace(/\\n/g, '\n') : ''
  if (method == 'whenloaded') {
    if (doc && doc.readyState === 'complete')
      arg($(doc).find('textarea'), this)
    else
      this.on('load', e => arg($($(e.target)[0].contentDocument).find('textarea'), $(e.target)))
  } else if (method == 'focus')
    this.ifeditor('whenloaded', t => t.focus())
  else if (method == 'blur')
    this.ifeditor('whenloaded', t => t.blur())
  else if (method == 'bind')
    this.ifeditor('whenloaded', t => t.on(arg, arg2))
  else if (method == 'set')
    this.ifeditor('whenloaded', (t, iframe) => { t.val(fmt(arg)); iframe.ifeditor('recalc') })
  else if (method == 'get')
    return $(doc).find('textarea').val()
  else if (method == 'recalc') {
    let pre = $(doc).find('textarea')[0]
    pre.style.height = 'auto'
    pre.style.height = pre.scrollHeight + 'px'
    this.height(pre.scrollHeight)
  }
}

function setupEditor(iframe, value) {
  iframe.attr('srcdoc', `<body style="margin: 0">
                <textarea id="editor" style="outline: none !important; border: 0; width: calc(100% - 8px); height: 5em; resize: none"></textarea>
        </body>`)
  iframe.ifeditor('set', value)
  iframe.ifeditor('bind', 'input', () => iframe.ifeditor('recalc'))
  return iframe
}

function createAnnotCard(a, setup) {
  let tpl = `<div class="side-card" data-annotation-id="@id">
                <div class="card-title" style="display: flex">
                        <div class="card-title-info">
                                <div class="card-author"></div>
                                <i class="fa fa-lock" style="float: right; cursor: pointer" title="Read-only" onclick="alert('This annotation is not editable')"></i>
                                <i class="fa fa-trash" style="float: right; cursor: pointer" title="Delete" 
                                        onclick="deleteAnnotation($(this).closest('.side-card').prop('annot')).then(saveAndReload)"></i>
                        </div>
                        <div class="card-buttons">
                                <input type="color" data-prop="color" title="Color" class="fg" style="width: 3em; height: 1em" list />
                                <input type="color" data-prop="fill" title="Fill" class="bg" style="width: 3em; height: 1em" list />
                                <input type="number" data-prop="border[0]" min="0" title="Border width" class="border" value="1" style="height: 0.9em; width: 2.5em;" />
                                <input type="number" data-prop="opacity" min="0" max="100" title="Opacity" class="opacity" value="100" style="height: 0.9em; width: 3em;" />
                                <input type="number" data-prop="fontsize" min="6" max="48" title="Font Size" class="fontsize" value="9" style="height: 0.9em; width: 3em;" />
                                <button data-prop="iconName" title="Comment icon type" style="border: 1px solid #888; padding: 1px; margin: 0"></button>
                                <span>
                                        <button data-prop="line_ending" class="lestart" title="Line beginning" style="padding: 1px 0; margin-right: 0"></button>
                                        <button data-prop="line_ending" class="leend" title="Line ending" style="padding: 1px 0; margin-left: 0;"></button>
                                </span>
                        </div>
                </div>
                <iframe scrolling="no" frameborder="0"></iframe>
                <div class="side-card-buttons">
                        <button class="cancel">Cancel</button>
                        <button class="ok">OK</button>
                </div>
        </div>`
  let title = [a.author || '', fmtPdfDate(a.updateDate)].filter(x => x).join(' - ')
  var card = $(jQuery.parseHTML(tpl)[0])
  card.attr('data-annotation-id', (a && a.object_id ? a.object_id.obj : '') || '')
    .find('.card-title .card-author').text(title)
  card.prop('annot', a)
  const isnew = !a.object_id
  card.toggleClass('newcard', isnew).toggleClass('readonly', !isEditable(a))
  card.addClass(fixType(a.type).toLowerCase())

  let editor = setupEditor(card.find('iframe'), a.contents)

  let commentMenu = {
    items: commentTypes.map(t => ({
      value: t,
      text: `<img width="16" src="${base_url}img/annotation/annotation-${t}.svg" /> ` + t,
      parentText: `<img width="16" src="${base_url}img/annotation/annotation-${t}.svg" />`,
      // checked: t == (a.iconName || 'Note')
      checked: '/' + t == (a.iconName || '/Note') || t == (a.iconName || 'Note')
    }))
  }
  attachMenu(card.find('[data-prop=iconName]'), commentMenu, 'iconName')

  let lemenu = {};
  let getLeSvg = function (cls, type, w, vw) {
    return `<svg version="1.1" width="${w}px" height="1em" viewBox="0 0 ${vw} 1" style="overflow: visible">
                        <polyline stroke="black" points="3,0 ${vw - 3},0" marker-${cls}="url(#${type.toLowerCase()}_tpl)"></polyline>
                </svg>`
  };
  ['start', 'end'].forEach((cls, index) => {
    let avalue = (a.line_ending ? a.line_ending[index] : null) || 'None'
    // TODO: add a value if not in our list to keep it
    let endings = lineEndings.indexOf(avalue) >= 0 ? lineEndings : [avalue].concat(lineEndings)
    let menu = {
      title: 'Line ' + cls,
      items: endings.map(t => ({
        value: t,
        text: lineEndings.indexOf(t) >= 0 ? getLeSvg(cls, t, 72, 45) : t,
        parentText: lineEndings.indexOf(t) >= 0 ? getLeSvg(cls, t, 16, 14) : t,
        checked: t == avalue
      }))
    }
    attachMenu(card.find('.le' + cls), menu, 'le' + cls)
    lemenu[cls] = menu
  })

  let getColor = function (cls) {
    let input = card.find('input.' + cls)[0];
    return input.dataset.changed ? input.value : null
  }
  let getPrepared = () => ({
    color: getColor('fg'),
    fill: getColor('bg'),
    border: Object.assign(a.border || {}, { border_width: +card.find('.border').val() }),
    opacity: +card.find('.opacity').val() / 100.0,
    iconName: (commentMenu && commentMenu.checked && commentMenu.checked.value ? commentMenu.checked.value : 'Note'),
    contents: editor.ifeditor('get'),
    line_ending: [lemenu.start.checked.value, lemenu.end.checked.value],
    defaultStyling: 'font: Helvetica,sans-serif ' + card.find('.fontsize').val() + 'pt'
  })
  let okHandler = function (e) {
    e.stopPropagation()
    let copy = getPrepared()
    let annot = card.prop('annot')
    if (annot.object_id)
      deleteAnnotation(a).then(() => {
        createAnnotation(Object.assign({}, annot, copy))
        saveAndReload()
      })
    else
      setup.ok(copy)
  }
  let cancelHandler = function (e) {
    e.stopPropagation()
    onSideCardDeactivate()
  }

  card.find('.ok').click(okHandler)
  card.find('.cancel').click(cancelHandler)

  if (a.color || a.fill) {
    let c = rgbcss(a.fill || a.color)
    if (c != '#000000')
      card.find('.card-title').css({ background: rgbcss(a.fill || a.color) })
  }
  if (a.color)
    card.find('input.fg').val(rgbcss(a.color))[0].dataset.changed = 1
  if (a.fill)
    card.find('input.bg').val(rgbcss(a.fill))[0].dataset.changed = 1
  card.find('input.fg').toggle(!getType(a.type).nocolor)
  card.find('input.bg').toggle(!!getType(a.type).fill)
  card.find('.fontsize').val(getAnnotFontSize(a) || 9).toggle(!!getType(a.type).font)
  card.find('.opacity').val((a.opacity || 1) * 100)
  card.find('.border').val(!a.border ? 1 : ((Array.isArray(a.border) ? a.border[2] : a.border.border_width) || 1)).toggle(!!getType(a.type).border)

  if (!a.object_id) {
    editor.ifeditor('focus')
    onSideCardActivate(card)
  }
  else {
    editor.ifeditor('bind', 'focus', () => onSideCardActivate(card))
  }
  editor.ifeditor('bind', 'keydown', function (e) {
    if (e.code == 'Enter' && e.ctrlKey) {
      okHandler(e)
    } else if (e.code == 'Escape') {
      cancelHandler(e)
    }
  })
  card.click(() => onSideCardActivate(card))
  $('.sidebar').click(e => {
    let els = [...document.elementsFromPoint(e.pageX, e.pageY)]
    if (els.filter(el => el.tagName == 'SELECT'))
      return
    let card = els.filter(el => $(el).hasClass('side-card'))[0]
    if (!card && $('.side-card.active').length)
      onSideCardDeactivate()
  })

  card.find('.fg, .bg, select[data-prop=iconName]').on('change', function (e) {
    let annot = $(e.target).closest('.side-card').prop('annot')
    this.dataset.changed = 1
    if (annot.object_id) {
      //let cfg = {}
      //cfg[this.dataset.prop] = this.type == 'color' ? parseColor(this.value) : this.value
      //changeAnnotation(annot, cfg)
    }
  })
  card.find('input').on('input', function () {
    if (current)
      // postpone so that value is set
      setTimeout(function () { current.setParams(getPrepared()) }, 0)
  })
  editor.ifeditor('bind', 'input', function (e) {
    if (current)
      // postpone so that value is set
      setTimeout(function () { current.setParams(getPrepared()) }, 0)
  });

  return card
}

function sidebarSort(order) {
  sidesort = order
  refreshSidebar()
}

function refreshSidebar() {
  let sidebar = document.getElementsByClassName('sidebar')[0];
  sidebar.querySelectorAll('.side-card, .sidebar-page-sep').forEach(el => el.remove())
  let lastpage
  let body = $('.sidebar > .sidebar-body')

  let order = sidesorts.checked.fn

  annotations.filter(a => /*a.contents !== undefined && */ a.type != '/Popup').sort(order).forEach(a => {
    if (a.page != lastpage) {
      body.append($('<div class="sidebar-page-sep" />').attr('data-page', a.page + 1).append($('<span/>').text(`Page ${a.page + 1}`)))
      lastpage = a.page
    }
    body.append(createAnnotCard(a))
  })
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
  pdfViewer._buffer.resize(10000, 10000)
  let oldget = pdfViewer._getVisiblePages
  pdfViewer._getVisiblePages = function () {
    let pages = pdfViewer._pages//.filter(p => p.renderingState === 0)
    let last = pages[pages.length - 1]
    return {
      first: { id: pages[0].id, view: pages[0] },
      last: { id: last.id, view: last },
      views: pages.map(p => ({ id: p.id, view: p }))
    }
  }
  pdfViewer.currentScale = 1
  pdfViewer.forceRendering()
  let overlay = $('<div class="print-overlay" />').text('Preparing...').appendTo('#main')
  let checker = function () {
    if ($('.page > .loadingIcon').length) {
      setTimeout(checker, 100)
      return
    }
    overlay.remove()
    window.onafterprint = function () {
      pdfViewer._getVisiblePages = oldget
      pdfViewer._buffer.resize(10, 10)
      pdfViewer.forceRendering()
    }
    window.print()
  }
  setTimeout(checker, 100)

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
  window.pdferrors = []
  $.get('./tests/pdfs_codiac.txt').then(list => {
    let datas = {}
    let loads = list.split('\n').map(x => x.trim()).filter(x => x).map(file => {
      let url = './tests/codiac/' + file
      return fetch(url).then(response => response.arrayBuffer()).then(data => datas[file] = data)
      //return $.get(url).then(function(data) { datas[file] = data })
    })
    let failed = []
    Promise.all(loads).then(() => {
      for (var name in datas) {
        let file = name
        try {
          let pdf = new pdfAnnotate.AnnotationFactory(datas[file])
          pdf.getAnnotations()
            .catch(e => {
              window.pdferrors.push({
                file: file,
                size: datas[file].byteLength,
                error: e.toString()
              })
            })
        }
        catch (e) {
          window.pdferrors.push({
            file: file,
            size: datas[file].byteLength,
            error: e.toString()
          })
        }
      }
    }).then(() => {
      window.pdferrors.sort((a, b) => b.size - a.size).forEach(x => console.log(`${x.file} ${x.size}: ${x.error}`))
    })
  })
}

function createViewer(params) {
  let html = `<div id="main">
                    <div class="toolbar">
                            <button onclick="new TextAction('Highlight')" title="Highlight text" data-type="text"><i class="fa fa-italic"></i></button>
                            <button onclick="new TextAction('Underline')" title="Underline text" data-type="text"><i class="fa fa-underline"></i></button>
                            <button onclick="new TextAction('Squiggly')" title="Squiggly underline text" data-type="text"><i class="fa fa-water"></i></button>
                            <button onclick="new TextAction('StrikeOut')" title="Strike-out text" data-type="text"><i class="fa fa-strikethrough"></i></button>
                            <button onclick="new PointAction('Text')" title="Add comment" data-type="rect"><i class="far fa-comment-alt"></i></button>
                            <button onclick="new FreeTextAction('FreeText')" type="button" title="Free Form Text" data-type="rect"><i class="far fa-object-ungroup"></i></button>
                            <button onclick="new RectAction('Circle')" title="Add circle" data-type="rect"><i class="far fa-circle"></i></button>
                            <button onclick="new RectAction('Square')" title="Add rectangle" data-type="rect"><i class="far fa-square"></i></button>
                            <button onclick="new PolyAction('PolyLine')" title="Add polyline" data-type="draw"><i class="fa fa-route"></i></button>
                            <button onclick="new PolyAction('Polygon')" title="Add polygon" data-type="draw"><i class="fa fa-draw-polygon"></i></button>
                            <button onclick="new DrawAction('Ink')" title="Draw annotation" data-type="draw"><i class="fa fa-pencil-alt"></i></button>
                            <select id="addStampAnnotation" onchange="if (this.selectedIndex) new StampAction('Stamp', { stampType: this.value })" title="Add stamp" data-type="rect">
                                    <option val="" disabled="disabled" selected="selected">Stamp</option>
                            <select>
                            <!--select id="addFigureAnnotation" onchange="if (this.selectedIndex) new FigureAction('Polygon', { figure: this.value })" title="Add arrows and other figures" data-type="rect">
                                    <option val="" disabled="disabled" selected="selected">Figures</option>
                                    <option val="Arrow">Arrow</option>
                            <select-->
                            <div style="flex: 1; text-align: right; margin: auto" class="filename"></div>
                    </div>
                    <div id="viewerContainer">
                            <div id="viewer" class="pdfViewer"></div>
                    </div>
                    <div class="viewbar noselection">
                            <div class="pagecontrols">
                                    <button onclick="goToPage(null, -1)"><i class="fa fa-chevron-left"></i></button>
                                    <input type="text" id="pagenum" style="width: 2em" onchange="goToPage(+this.value)" />
                                    <span id="page">1</span>
                                    <button onclick="goToPage(null, +1)"><i class="fa fa-chevron-right"></i></button>
                            </div>
                            <div class="findcontrols">
                                    Find: <input oninput="find(arguments[0])" onkeydown="find(arguments[0])" />
                                    <span class="matches"></span>
                            </div>
                            <div class="zoomcontrols">
                                    <i class="fa fa-text-width" title="Fit width" onclick="pdfViewer.currentScaleValue = 'page-width'"></i>
                                    <i class="fa fa-search-minus" title="Zoom out" onclick="pdfViewer.currentScale -= 0.1"></i>
                                    <input type="range" min="0" max="500" value="100" oninput="pdfViewer.currentScale = this.value / 100" />
                                    <i class="fa fa-search-plus" title="Zoom in" onclick="pdfViewer.currentScale += 0.1"></i>
                                    <i class="fa fa-undo-alt" title="Rotate counterclockwise" onclick="rotate(-90)"></i>
                                    <i class="fa fa-redo-alt" style="margin-right: 0.2em" title="Rotate clockwise" onclick="rotate(90)"></i>
                            </div>
                    </div>
                    <svg id="drawingLayer">
                        <defs>
                                <marker id="circle_tpl" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="5" markerHeight="5" markerUnits="strokeWidth">
                                        <circle cx="5" cy="5" r="5" stroke="black" />
                                </marker>
                                <marker id="openarrow_tpl" viewBox="0 0 10 10" refX="5" refY="5" markerUnits="strokeWidth" markerWidth="10" markerHeight="10" orient="auto">
                                        <path d="M 0 3 L 5 5 L 0 7" stroke="black" fill="transparent" />
                                </marker>
                                <marker id="closedarrow_tpl" viewBox="0 0 10 10" refX="5" refY="5" markerUnits="strokeWidth" markerWidth="10" markerHeight="10" orient="auto">
                                        <path d="M 0 3 L 5 5 L 0 7 L 0 3 z" stroke="black" fill="red" />
                                </marker>
                                <marker id="square_tpl" markerWidth="7" markerHeight="7" refx="4" refy="4" markerUnits="strokeWidth" orient="auto">
                                        <rect x="1" y="1" width="5" height="5" stroke="black" fill="red" />
                                </marker>
                                <marker id="diamond_tpl" markerWidth="10" markerHeight="10" refX="5" refY="5" markerUnits="strokeWidth" orient="auto">
                                        <path d="M 5,1 L 9,5 5,9 1,5 z" stroke="black" fill="red" />
                                </marker>
                        </defs>
                        <path d="" stroke="black" fill="transparent" />
                    </svg>
            </div>
            <div class="sidebar">
                    <div class="sidebar-splitter"></div>
                    <div class="sidebar-title">
                            <div class="sidebar-buttons">
                                <div style="position: relative; display: inline-block; top: 2px">
                                        <input type="file" style="width: 2em;" onchange="openDocument(arguments[0]); this.value = null">
                                        <i class="fa fa-folder-open" style="font-size: 1.2em; position: absolute;height: calc(100% + 1px);left: 0;top: 0;width: 100%;background: white;color: black;pointer-events: none;"></i>
                                </div>
                                <button type="button" title="Save" onclick="viewerParams.saveToDataBase(viewerParams.originFileName)"><i class="fa fa-save"></i></button>
                                <button type="button" title="Download" onclick="pdfFactory.download(viewerParams.originFileName)"><i class="fa fa-download"></i></button>
                                <button type="button" id="toggleAnnotations" title="Toggle annotations on/off" onclick="toggleAnnotations()"><i class="fa fa-eye"></i></button>
                                <button type="button" title="Print" onclick="printHandler()"><i class="fa fa-print"></i></button>
                                <button type="button" title="Test" style="display: none" onclick="test()"><i class="fa fa-gear"></i></button>
                                <span style="flex: 1"></span>
                                <button data-menu="sidesorts"><i class="fa fa-sort"></i></button>
                            </div>
                    </div>
                    <div class="sidebar-body"></div>
            </div>
            <svg id="pointers">
                <polyline points="" stroke-width="1" stroke="black" fill="transparent"></polyline>
            </svg>
            `
  $('body').append($.parseHTML(html))
  setupStampsOptions($("#addStampAnnotation"))
  $("#addStampAnnotation").append($('<option/>').val('').text('Custom...'))
  viewerParams = params
  setupViewer(params.url)
  $('#viewerContainer').on('scroll', refreshSelectedAnnot)

  // menus
  $('*[data-menu]').each((i, el) => attachMenu($(el), window[el.dataset.menu]))

  // tooltips
  let style = document.createElement('style')
  style.textContent = `.ui-tooltip {
        position: absolute;
        background: white;
        padding: 0.2em;
        border: 1px solid black;
        box-shadow: 2px 2px 4px 0 #8888;
        border-radius: 0.5em;
        font-size: 10pt;
        max-width: 20em;
        background: #ffa;
        z-index: 10;
    }
    .ui-tooltip > * {
        padding: 0.5em;
    }
    .ui-tooltip p {
            margin: 0.2em;
    }
    .ui-tooltip .title {
            white-space: nowrap;
            border-bottom: 1px solid #888;
            font-size: 12pt;
    }`
  document.head.appendChild(style)
}
