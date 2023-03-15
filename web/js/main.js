$(document).ready(function () {
    var leftNavigationBar = $('.left-navbar-icon');

    if (typeof chatConfig != 'undefined') {
        //setInterval(function(){ getNewNotifications(); }, chatConfig.interval);
    }

    $(document).on('submit', '.registration-main-form', function () {
        var t = $(this),
            selectors = t.find('input, select, textarea, button');

        try {
            selectors.trigger('js_event_insert');
        } catch (e) {
            t.find('.alert-wrap').show().find('.alert-message').html(e.message);
            return false;
        }
    });

    $(document).on('click', '.js-style-switcher', function () {
        var panelBody = $(this).parents('.panel-body');
        var useImages = $(this).find('input[type="radio"]').val();
        switchBodySettings(panelBody, useImages);
    });


    $(document).on('click', '.js-open-chat', function (e) {
        e.preventDefault();
        if ($(this).hasClass('opened')) {
            $(this).removeClass('opened');
            $(this).find('.glyphicon').removeClass('glyphicon-list').addClass('glyphicon-comment');
            $('#sidepanel').removeClass('hidden');
            $('.chat-left-menu').addClass('hidden');
            $('.screen-list-menu').removeClass('hidden');
        } else {
            $(this).addClass('opened');
            $(this).find('.glyphicon').removeClass('glyphicon-comment').addClass('glyphicon-list');
            $('#sidepanel').addClass('hidden');
            $('.chat-left-menu').removeClass('hidden');
            $('.screen-list-menu').addClass('hidden');
        }

        return false;
    });

    $(document).on('hidden.bs.dropdown', '.left-position-navbar-menu', function () {
        $(this).find('.dropdown').each(function () {
            if ($(this).hasClass('active')) {
                $(this).addClass('open');
            }
        });
    });

    tooltipInit();
    $(document).ajaxComplete(function () {
        tooltipInit();
    });

    $(document).on('click', '.js-delete-image', function () {
        var url = $('#settings-form').data('delImageUrl');
        var requestData = {
            'attribute': $(this).data('modelAttr'),
            'image': $(this).siblings('input[type="radio"]').val()
        };

        deleteImage(url, requestData);
        $(this).parent('.img-thumbnail-wrapper').html('');
    });

    if ($(window).width() <= 900) {
        leftNavigationBar.attr('data-toggle', 'true');
    }
    $(window).resize(function () {
        if ($(window).width() <= 900) {
            if (leftNavigationBar.attr('data-toggle') != "true") {
                leftNavigationBar.click();
            }
        }
    });

    leftNavigationBar.on('click', function () {
        var toggle = leftNavigationBar.attr('data-toggle');
        leftNavigationBar.attr('data-toggle', !(toggle == "true"));

        if (toggle == "true") {
            $('.left-position-navbar').animate({'left': 0});
            $('body div:not(.sub-content-wrapper) > div > .nav-left-group').animate({'left': 250});
            if ($(window).width() > 900) {
                $('.wrap').animate({'margin-left': 250});
                $('.wrap > .navbar.info-place').css({'left': 'auto'});
            } else {
                $('.wrap > .navbar.info-place').animate({'left': 250});
                $('.wrap').css({'margin-left': 0});
            }
        } else {
            $('.left-position-navbar').animate({'left': '-250'});
            $('body div:not(.sub-content-wrapper) > div > .nav-left-group').animate({'left': 0});
            if ($(window).width() > 900) {
                $('.wrap').animate({'margin-left': 0});
                $('.wrap > .navbar.info-place').css({'left': 'auto'});
            } else {
                $('.wrap > .navbar.info-place').animate({'left': 0});
                $('.wrap').css({'margin-left': 0});
            }
        }
    });

    function deleteImage(url, requestData) {
        $.ajax({
            type: 'POST',
            url: url,
            data: requestData
        }).done(function (data) {
            console.log(data);
        });
    }

    function switchBodySettings(panelBody, useImages) {
        var styleWrapper = panelBody.find('.style-case-wrapper');
        var imageWrapper = panelBody.find('.image-case-wrapper');
        if (useImages) {
            imageWrapper.fadeIn(400);
            styleWrapper.hide();
        } else {
            styleWrapper.fadeIn(400);
            imageWrapper.hide();
        }
    }

    function getNewNotifications() {
        if (typeof chatConfig.url != 'undefined') {
            $.ajax({
                type: "GET",
                url: chatConfig.url,
            }).done(function (data) {
                console.log('refresh notifications');
                $('.all-chat-notifications').text(data);
            });
        }
    }

    function tooltipInit() {
        $('[data-toggle="tooltip"]').tooltip({
            position: {
                my: "center bottom-10",
                at: "center top",
                using: function(position, feedback) {
                    $(this).css(position);
                    $("<div>")
                        .addClass("arrow")
                        .addClass(feedback.vertical)
                        .addClass(feedback.horizontal)
                        .appendTo(this);
                }
            },
            html: true,
            content: function (callback) {
                callback($(this).prop('title').replace(new RegExp("\\,", "g"), '<br />'));
            }
        });
    }

});