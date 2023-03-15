/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/30		Mayank Bhatnagar		38				Need to uncomment two lines currentTr.find('[data-sub-id="-1"]').val(null); and 
 element.parents("table").find('[data-sub-id="-1"]').val(null); in this.addGridLine = function (element) { function
 **************************************************************************
 */

(function($){
    var rCRLF = /\r?\n/g,
        rsubmitterTypes = /^(?:submit|button|image|reset|file)$/i,
        rsubmittable = /^(?:input|select|textarea|keygen)/i;

    $.fn.serializeArrayWithData = function () {
        return this.map(function () {
            var elements = $.prop(this, "elements");
            return elements ? $.makeArray(elements) : this;
        })
        .filter(function () {
            var type = this.type;
            return this.name && !$(this).is(":disabled") && rsubmittable.test(this.nodeName) && !rsubmitterTypes.test(type);
        })
        .map(function (i, elem) {
            var val = $(this).val(),
                data = $(this).data();

            if (val == null) {
                return null;
            }

            $.each(data, function (i, item) {
                if (typeof item == 'object' && $.inArray(i, ['afPkPart', 'notifications', 'notificationParams', 'notificationRecipient']) < 0) {
                    delete data[i];
                }
            });

            if (Array.isArray(val)) {
                return $.map(val, function (val) {
                    var result = {name: elem.name, value: val.replace(rCRLF, "\r\n")};
                    if (data) {
                        return $.extend(result, data);
                    }

                    return result;
                });
            }

            var result = {name: elem.name, value: val.replace(rCRLF, "\r\n")};
            if (data) {
                return $.extend(result, data);
            }

            return result;
        }).get();
    };

    $.fn.dependentField = function (action) {
		console.log('in $.fn.dependentField');

        var me = this,
            data = me.data('dependentField'),
            dependentTimestamp,
            dependentID,
            setTimestamp = 0;

        if (!data) {
            return false;
        }

        if (data['id'] && data['type'] == 'use_field_date') {
            dependentID = $('#' + data['id']);
            if (action != 'reset') {
                dependentID.change(function () {
                    me.dependentField('reset');
                });
            }

            if (dependentID.attr('data-krajee-kvdatepicker') && dependentID.data('datepickerSource')) {
                dependentTimestamp = $('#' + dependentID.data('datepickerSource')).kvDatepicker('getDate');
            } else if (dependentID.attr('data-krajee-datetimepicker')) {
                dependentTimestamp = $('#' + dependentID.attr('id') + '-datetime').datetimepicker('getDate')
            }
        } else if (data['type'] == 'use_current_date') {
            dependentTimestamp = new Date();
        }

        if (dependentTimestamp) {
            dependentTimestamp = dependentTimestamp.getTime();
            dependentTimestamp = (dependentTimestamp < 0) ? 0 : dependentTimestamp;
        } else {
            return false;
        }

        if (data['pivot-id']) {
            $.each(data['pivot-id'], function (i, id) {
                if (!id) {
                    return true;
                }

                var field = $('#' + id),
                    value = (field.length > 0) ? field.val() : 0;

                if (action != 'reset') {
                    field.change(function () {
                        me.dependentField('reset');
                    });
                }

                if (data['pivot-timestamp'][i] && data['pivot-value'][i] && data['pivot-value'][i] == value) {
                    setTimestamp = dependentTimestamp + (parseInt(data['pivot-timestamp'][i]) * 1000);
                }
            })
        }

        if (!setTimestamp && data['timestamp']) {
            setTimestamp = dependentTimestamp + (parseInt(data['timestamp']) * 1000);
        }

        if (me.attr('data-krajee-kvdatepicker') && me.data('datepickerSource')) {
            $('#' + me.data('datepickerSource')).kvDatepicker("setDate", new Date(setTimestamp));
        } else if (me.attr('data-krajee-datetimepicker')) {
            $('#' + me.attr('id') + '-datetime').datetimepicker("setDate", new Date(setTimestamp));
        }
    }
})(jQuery);

/**
 * Main class. CRUD and search functionality
 * @class commonApp
 */
var commonApp = function () {
    this.selectedLib = null;
    this.baseUrl = null;
    this.LockRecordUrl = null;
    this.UnlockRecordUrl = null;
    this.activeMode = null;
    this.lastSearchResults = null;
    this.getSubDataUrl = null;
    this.LoadUrl = null;
    this.searchUrl = null;
    this.downloadInitUrl = null;
    this.downloadFragmentUrl = null;
    this.generateReportUrl = null;
    this.searchReportUrl = null;
    this.downloadFinishUrl = null;
    this.uploadInitUrl = null;
    this.uploadFragmentUrl = null;
    this.uploadFinishUrl = null;
    this.inlineSearchUrl = null;
    this.inlineSearchTempUrl = null;
    this.customExecuteUrl = null;
    this.getScreenLinkUrl = null;
    this.workflowReleaseUrl = null;
    this.workflowLockUrl = null;
    this.workflowUnlockUrl = null;
    this.saveWorkflowTaskUrl = null;
    this.createWorkflowTaskUrl = null;
    this.getWorkflowTaskUrl = null;
    this.getWorkflowStepUrl = null;
    this.getUserListUrl = null;
    this.getTaskHistoryUrl = null;
    this.getDocumentListUrl = null;
    this.getDocumentUploadUrl = null;
    this.getDocumentInitUploadUrl = null;
    this.getDocumentUploadFragmentUrl = null;
    this.getDocumentFinishUploadUrl = null;
    this.getDeleteDocumentUrl = null;
    this.getGetDeletedDocumentListUrl = null;
    this.getUndeletedDocumentUrl = null;
    this.getUpdateDocumentUrl = null;
	this.getDocumentDownloadUrl = null;
    this.getDocumentDownloadFragmentUrl = null;
    this.getFieldUploadImageUrl = null;
    this.getWorkflowJsonUrl = null;
    this.getReportUrl = null;
    this.searchLinkedListCustomQueryUrl = null;
    this.headerSearchUrl = null;
    this.exportTableDataUrl = null;
    this.checkLoginUrl = null;
    this.checkEmailLoginUrl = null;
    this.checkSMSLoginUrl = null;
    this.checkSQLoginUrl = null;

	this.current_login_screen_action = null;

    this.currencyProperty = {
        thousands: '.',
        decimal: ','
    };
    this.decimalProperty = {
        thousands: ' ',
        decimal: ','
    };
    this.loadedTabsNum = 0;

    this.lastGetDataPK = {};
    this.aliasFrameworkInfo = {
        enable: false,
        request_primary_table: null
    };

    this.subData = {
        subIdStart: 0,
        insert: {},
        update: {},
        delete: {}
    };

    this.workflowInfo = {};

	this.setDownloadInitUrl = function (url) {
        this.downloadInitUrl = url;
    };

    this.setDownloadFragmentUrl = function (url) {
        this.downloadFragmentUrl = url;
    };

    this.setGenerateReportUrl = function (url) {
        this.generateReportUrl = url;
    };

    this.setSearchReportUrl = function (url) {
        this.searchReportUrl = url;
    };

    this.setDownloadFinishUrl = function (url) {
        this.downloadFinishUrl = url;
    };

    this.setUploadInitUrl = function (url) {
        this.uploadInitUrl = url;
    };

    this.setUploadFragmentUrl = function (url) {
        this.uploadFragmentUrl = url;
    };

    this.setUploadFinishUrl = function (url) {
        this.uploadFinishUrl = url;
    };

    this.setWorkflowReleaseUrl = function (url) {
        this.workflowReleaseUrl = url;
    };

    this.setWorkflowLockUrl = function (url) {
        this.workflowLockUrl = url;
    };

    this.setWorkflowUnlockUrl = function (url) {
        this.workflowUnlockUrl = url;
    };

    this.setSaveWorkflowTaskUrl = function (url) {
        this.saveWorkflowTaskUrl = url;
    };

    this.setCreateWorkflowTaskUrl = function (url) {
        this.createWorkflowTaskUrl = url;
    };

    this.setGetWorkflowTaskUrl = function (url) {
        this.getWorkflowTaskUrl = url;
    };

    this.setGetWorkflowStepUrl = function (url) {
        this.getWorkflowStepUrl = url;
    };

    this.setGetUserListUrl = function (url) {
        this.getUserListUrl = url;
    };

	this.setWorkflowJsonUrl = function (url) {
        this.getWorkflowJsonUrl = url;
    };

    this.setInlineSearchUrl = function (data) {
        this.inlineSearchUrl = data;
    };

	this.setinlineSearchTempUrl = function (data) {
        this.inlineSearchTempUrl = data;
    };

	this.setsearchLinkedListCustomQueryUrl = function (data) {
        this.searchLinkedListCustomQueryUrl = data;
    };

	this.setExportTableDataUrl = function (data) {
        this.exportTableDataUrl = data;
    };

	this.setHeaderSearchUrl = function (headerSearchUrl) {
        this.headerSearchUrl = headerSearchUrl;
    };

    this.setCustomExecuteUrl = function (url) {
        this.customExecuteUrl = url;
    };

    this.setGetScreenLinkUrl = function (url) {
        this.getScreenLinkUrl = url;
    };

	this.setGetTaskHistoryUrl = function (url) {
        this.getTaskHistoryUrl = url;
    };

	this.setGetDocumentListUrl = function (url) {
        this.getDocumentListUrl = url;
    };

	this.setGetReportUrl = function (url) {
        this.getReportUrl = url;
    };

	this.setDocumentUploadUrl = function (url) {
        this.getDocumentUploadUrl = url;
    };

	this.setDocumentInitUploadUrl = function (url) {
        this.getDocumentInitUploadUrl = url;
    };

	this.setDocumentUploadFragmentUrl = function (url) {
        this.getDocumentUploadFragmentUrl = url;
    };

	this.setDocumentFinishUploadUrl = function (url) {
        this.getDocumentFinishUploadUrl = url;
    };

	this.setDocumentDownloadUrl = function (url) {
        this.getDocumentDownloadUrl = url;
    };

	this.setDeleteDocumentUrl = function (url) {
        this.getDeleteDocumentUrl = url;
    };

	this.setGetDeletedDocumentListUrl = function (url) {
        this.getGetDeletedDocumentListUrl = url;
    };

	this.setUndeletedDocumentUrl = function (url) {
        this.getUndeletedDocumentUrl = url;
    };

	this.setUpdateDocumentUrl = function (url) {
        this.getUpdateDocumentUrl = url;
    };

	this.setDocumentDownloadFragmentUrl = function (url) {
        this.getDocumentDownloadFragmentUrl = url;
    };

	this.setGetFieldUploadImageUrl = function (url) {
        this.getFieldUploadImageUrl = url;
    };

    this.setBaseUrl = function (baseUrl) {
        this.baseUrl = baseUrl;
    };

    this.setLockRecordUrl = function (url) {
        this.LockRecordUrl = url;
    };

    this.setUnlockRecordUrl = function (url) {
        this.UnlockRecordUrl = url;
    };

    this.setLastSearchResults = function (data) {
        this.lastSearchResults = data;
    };

    this.setSubDataUrl = function (data) {
        this.getSubDataUrl = data;
    };

    this.setLoadUrl = function (url) {
        this.LoadUrl = url;
    };

    this.setSearchUrl = function (url) {
        this.searchUrl = url;
    };

	this.setCheckLoginUrl = function (data) {
        this.checkLoginUrl = data;
    };

	this.setCheckEmailLoginUrl = function (data) {
        this.checkEmailLoginUrl = data;
    };

	this.setCheckSMSLoginUrl = function (data) {
        this.checkSMSLoginUrl = data;
    }

	this.setCheckSQLoginUrl = function (data) {
        this.checkSQLoginUrl = data;
    };

    //Init after loading page
    this.load = function () {
        var me = this,
			active_tab_id,
			section_depth_value,
            searchArray = [];

		if(me.activeMode == 'insert' || me.activeMode == 'edit' || me.activeMode == 'copy')
			$('.screen-execute-btn').hide();

		me.bindLoadEvents();

        $(document).ready(function () {
			console.log('In document ready');

			me.current_login_screen_action = 'standard_login';

            me.init();
            me.selectedLib = $('.screen-tab.btn.active').data('lib');

            var anchors = me.getAnchors(),
                SearchResult = (sessionStorage['search-res-' + me.selectedLib]) ? JSON.parse(sessionStorage['search-res-' + me.selectedLib]) : false;

            if (anchors) {
				console.log('in anchors if');
				console.log(anchors);

                if (anchors['id']) {
                    me.setActiveId(anchors['id']);
                }

                if (anchors['search']) {
                    $.each(anchors['search'], function (name, value) {
                        searchArray.push({name: name, value: value});
                    });
                    me.getDataByKeyFields(searchArray);
                }

                if (anchors['tab']) {
                    $('.screen-group-tab [data-tab-id="' + anchors['tab'] + '"]').click();
                } else {
                    $('.screen-tab.btn.active').click();
                }
            } else {
				console.log('in anchors else');

                $('.screen-tab.btn.active').click();
            }

			console.log('searchArray.length');
			console.log(searchArray.length);

			console.log("$('.search-input-inner-wrapper .search-field').attr('data-search')");
			console.log($('.search-input-inner-wrapper .search-field').attr('data-search'));

			if($('.search-input-inner-wrapper .search-field').attr('data-search') != '' && $('.search-input-inner-wrapper .search-field').attr('data-search') != '""' && $('.search-input-inner-wrapper .search-field').attr('data-search') != null && $('.search-input-inner-wrapper .search-field').attr('data-search') != undefined) {
				console.log('in .search-input-inner-wrapper .search-field if');

				var current_logged_in_user_data = JSON.parse($('.search-input-inner-wrapper .search-field').attr('data-search'));

				if(current_logged_in_user_data.pass_logged_user) {
					me.lastSearchResults = current_logged_in_user_data;
					me.setActiveId(current_logged_in_user_data.id, current_logged_in_user_data);
				}
			} else if (searchArray.length == 0) {
				console.log('in .search-input-inner-wrapper .search-field else if');

                if (sessionStorage['active-id-' + me.selectedLib] && SearchResult) {
					console.log('in else if');
					//console.log('SearchResult');
					//console.log(SearchResult);

                    $.each(SearchResult, function (i, item) {
                        if (JSON.stringify(item['id']) == sessionStorage['active-id-' + me.selectedLib]) {
                            me.setActiveId(item['id'], item, true);
                            return false;
                        }
                    });
                } else if (sessionStorage['lastFoundData']) {
					console.log('lastFoundData');

					console.log(sessionStorage['lastFoundData']);
					console.log(me.selectedLib);

					console.log('me.searchResults');
					console.log(me.lastSearchResults);

					//console.log(me.searchResults);

					if(sessionStorage['lastFoundData'] != '""' && sessionStorage['lastFoundData'] != null) {
						console.log('in if');

						var temp_lastFoundData = typeof sessionStorage['lastFoundData'] != 'object' ? JSON.parse(sessionStorage['lastFoundData']) : sessionStorage['lastFoundData'];
						me.setActiveId(temp_lastFoundData.id, temp_lastFoundData);
					} else {
						console.log('in else');

						me.setActiveId('', '', false, '', '', '', 'search_submit');
					}

                    /*me.searchResults(me.selectedLib, sessionStorage['lastFoundData'], function (data) {
						console.log('in me.searchResults data');
						//console.log(data);

                        if (data) {
                            me.lastSearchResults = data;
                            if (data[0].id && data[0].value) {
                                me.setActiveId(data[0].id, data);
                            }
                        } else {
                            me.addErrorMessageToArea('Nothing found');
                        }
                    });*/
                }
            }
        });
    };

    this.getAnchors = function () {
        var loc, locQuery, paramName, params = {}, matches;
        if (loc = window.location.hash.replace("#","")) {
            loc = decodeURIComponent(loc);
            if (!(locQuery = loc.split('&'))) {
                locQuery = [loc];
            }

            for (var i = 0; i < locQuery.length; i++) {
                paramName = locQuery[i].split('=');
                matches = paramName[0].match(/(\w|:)+/gi);
                if (matches[1]) {
                    if (!params[matches[0]] || typeof params[matches[0]] != 'object') {
                        params[matches[0]] = {};
                    }
                    params[matches[0]][matches[1]] = paramName[1];
                } else {
                    params[paramName[0]] = paramName[1];
                }
            }

            return params;
        }

        return null;
    };

    this.setSearchAnchor = function (search) {
        var issetAnchors = this.getAnchors(),
            anchor = (issetAnchors && issetAnchors['tab']) ? '&tab=' + issetAnchors['tab'] : '';

        search = $.param({search: search});
        window.location.hash = '#' + search + anchor;
    };

    this.triggerAction = function (action, target) {
		console.log('In this.triggerAction');
		console.log('action :: ' + action);
		console.log('target :: ' + target);

		if(action == 'execute_list_with_extension_function') {
			var field_val = $('.'+target).val();
			var field_list_json = $("."+target).attr('data-select-field-list');
		}

        var activeStep = $('.screen-stepper-step a.active');

		var report_pass_through_input_no = $(target).data('report-pass-through-input-no');

        switch(action) {
            case 'insert':
                $('.screen-insert-btn').click();
                break;
            case 'edit':
                $('.screen-edit-btn').click();
                break;
            case 'key':
                $('.btn-link[data-mode="key"]').click();
                break;
            case 'copy':
                $('.screen-copy-btn').click();
                break;
            case 'prev-search':
                $('.navigation-btn.prev').click();
                break;
            case 'next-search':
                $('.navigation-btn.next').click();
                break;
            case 'delete':
                $('.screen-remove-btn').click();
                break;
            case 'prev-step':
				this.activePrevNextTab('button', $('.screen-stepper-step a.active').attr('data-prev'));
                //activeStep.parent('.screen-stepper-step').prev('.screen-stepper-step').find('a').click();
                break;
            case 'next-step':
				this.activePrevNextTab('button', $('.screen-stepper-step a.active').attr('data-next'));
                //activeStep.parent('.screen-stepper-step').next('.screen-stepper-step').find('a').click();
                break;
            case 'report':
				if(report_pass_through_input_no == 'Y') {
					this.generateReport($(target).data('reportTemplate'), $(target).data('id'));
				} else {
					$('#generate-report-modal').val({id: $(target).data('reportTemplate'), name: $(target).data('reportName'), simpleSearch: $(target).data('simpleSearch'), multiSearch: $(target).data('multiSearch'), batchQuery: $(target).data('batchQuery')}).modal('show');
				}
				break;
			case 'document':
				$('#document-modal')
					.val({
                        id: $(target).data('id'),
                    })
					.modal('show');
				break;
			case 'execute_list_with_extension_function':
				this.changeMode('execute', field_val, field_list_json);
				break;
			case 'search_submit':
				this.setActiveId('', '', false, '', '', '', 'search_submit');

				/*if(sessionStorage['lastFoundData']) {
					var lastFoundData = JSON.parse(sessionStorage['lastFoundData']);

					this.setActiveId(lastFoundData.id, lastFoundData, false, '', '', '', 'search_submit');
				} else {
					this.setActiveId('', '', false, '', '', '', 'search_submit');
				}*/
				break;
			case 'login':
				this.checkLogin(action, $(target).data('id'));
				break;
        }
    };

	this.validateLoginInput = function() {
		var check = true;

		var screen_tab = $('.screen-tab.btn[data-lib="' + this.selectedLib + '"]').attr('data-target');
		var active_tab_id = $('.screen-tab.active').attr('data-tab-id');
		var tabPlace = screen_tab;

		if(this.current_login_screen_action == 'standard_login')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-1').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');
		else if(this.current_login_screen_action == 'E')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-3').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');
		else if(this.current_login_screen_action == 'S')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-2').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');
		else if(this.current_login_screen_action == 'SQ')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-4').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

		var serializeArray = inputsSelector.serializeArrayWithData();

		console.log('inputsSelector');
		console.log(inputsSelector);

		if(serializeArray.length > 0) {
			console.log('in serializeArray.length');

			$.each(inputsSelector, function (i, item) {
				console.log('in $.each');
				console.log(item.value);
				console.log('$ this');
				console.log(this);

				if(item.value == '') {
					//$(this).css('border-color', 'red !important');
					$(this).attr('style', 'border-color: red !important;');

					$('.info-place').show();
					$('.info-place span').addClass('danger').html('field(s) can not be left empty');
					$('.message-pool-link').hide();

					//var error_msg_div = $('<div />', {style: 'display: block; color: red'}).html('Please fill out this field.');
					//$(this).after(error_msg_div);
					//$(this).parent().append(error_msg_div);

					check = false;
				} else {
					$(this).removeAttr('style');
					$(this).next('div').remove();
					//$(this).parent().find("div:last").remove();
				}

				$(this).attr('autocomplete', 'off');
			});
		}

		return check;
	}

	this.checkLogin = function (action, id) {
		console.log('in this.checkLogin');

		var _this = this;

		var screen_tab = $('.screen-tab.btn[data-lib="' + this.selectedLib + '"]').attr('data-target');
		var active_tab_id = $('.screen-tab.active').attr('data-tab-id');
		var tabPlace = screen_tab;

		if(this.current_login_screen_action == 'standard_login')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-1').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');
		else if(this.current_login_screen_action == 'E')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-3').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');
		else if(this.current_login_screen_action == 'S')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-2').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');
		else if(this.current_login_screen_action == 'SQ')
			var inputsSelector = $('.tab-content [data-section-lib="' + this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-4').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

		var serializeArray = inputsSelector.serializeArrayWithData();

		if(_this.validateLoginInput()) {
			$('.info-place').hide();
			$('.message-pool-link').hide();

			console.log('_this.current_login_screen_action :: ' + _this.current_login_screen_action);

			if(_this.current_login_screen_action == 'standard_login') {
				$.ajax({
					type: "POST",
					url: this.checkLoginUrl,
					data: {username: serializeArray[0]['value'], password: serializeArray[1]['value']}
				}).done(function (data) {
					console.log('data');
					console.log(data);

					if(data == 'AuthType.E') {
						console.log('in success AuthType.E if');

						if(_this.current_login_screen_action != 'E') {
							$.ajax({
								type: "GET",
								url: _this.checkEmailLoginUrl,
							}).done(function (data1) {
								console.log('data1');
								console.log(data1);

								if(data1 && data1 == 1) {
									_this.current_login_screen_action = 'E';

									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).hide();

									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('disabled', false);
									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('readonly', false);
									$(tabPlace).find('#section_depth_'+active_tab_id+'_1-3').show();
								}
							});
						}
					} else if(data == 'AuthType.S') {
						console.log('in success AuthType.S if');

						if(_this.current_login_screen_action != 'S') {
							$.ajax({
								type: "GET",
								url: _this.checkSMSLoginUrl,
							}).done(function (data1) {
								console.log('data1');
								console.log(data1);

								if(data1 && data1 == 1) {
									_this.current_login_screen_action = 'S';

									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).hide();

									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('disabled', false);
									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('readonly', false);
									$(tabPlace).find('#section_depth_'+active_tab_id+'_1-2').show();

									var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-2').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

									$.each(inputsSelector1, function (i, item) {
										console.log('in $.each');
										console.log(item.value);

										$(this).val('');
									});
								}
							});
						}
					} else if(data == 'AuthType.SQ') {
						if(_this.current_login_screen_action != 'SQ') {
							$.ajax({
								type: "GET",
								url: _this.checkSQLoginUrl,
							}).done(function (data1) {
								console.log('data1');
								console.log(data1);

								if(data1 && data1 != 0) {
									_this.current_login_screen_action = 'SQ';

									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).hide();

									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('disabled', false);
									$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('readonly', false);
									$(tabPlace).find('#section_depth_'+active_tab_id+'_1-4').show();

									var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-4').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

									$.each(inputsSelector1, function (i, item) {
										console.log('in $.each');
										console.log(item.value);

										if(i == 1)
											$(this).val(data1).attr('disabled', true);
									});
								} else {
									$('.info-place').show();
									$('.info-place span').addClass('danger').html('Error getting secret question.');
									$('.message-pool-link').hide();
									//alert('Error getting secret question');
								}
							});
						}
					} else if(data != '' && (data == 0 || data == 'incorrect_username_and_password')) {
						var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-1').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

							$.each(inputsSelector1, function (i, item) {
								console.log('in $.each');
								console.log(item.value);

								if(i == 1) {
									//$(this).css('border-color', 'red !important');
									$(this).attr('style', 'border-color: red !important;');

									//var error_msg_div = $('<div />', {style: 'display: block; color: red'}).html('Incorrect username or password.');
									//$(this).after(error_msg_div);

									$('.info-place').show();
									$('.info-place span').addClass('danger').html('Incorrect username or password.');
									$('.message-pool-link').hide();
								}
							});
					} else {
						//window.location.href = data;
					}
				});
			} else if(_this.current_login_screen_action == 'E') {
				$.ajax({
					type: 'GET',
					cache: false,
					url: _this.checkEmailLoginUrl,
					data: {action: 'check_code', confirmation_code: serializeArray[0]['value']},
					success: function (data) {
						console.log('data');
						console.log(data);

						if(data && data == 'Incorrect code') {
							var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-3').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

							$.each(inputsSelector1, function (i, item) {
								console.log('in $.each');
								console.log(item.value);

								//$(this).css('border-color', 'red !important');
								$(this).attr('style', 'border-color: red !important;');

								//var error_msg_div = $('<div />', {style: 'display: block; color: red'}).html('Incorrect Code.');
								//$(this).after(error_msg_div);

								$('.info-place').show();
								$('.info-place span').addClass('danger').html('Incorrect Code.');
								$('.message-pool-link').hide();
							});
						} else if(data != '') {
							if(data == 'AuthType.S') {
								console.log('in success AuthType.S if');

								if(_this.current_login_screen_action != 'S') {
									$.ajax({
										type: "GET",
										url: _this.checkSMSLoginUrl,
									}).done(function (data1) {
										console.log('data1');
										console.log(data1);

										if(data1 && data1 == 1) {
											_this.current_login_screen_action = 'S';

											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).hide();

											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('disabled', false);
											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('readonly', false);
											$(tabPlace).find('#section_depth_'+active_tab_id+'_1-2').show();

											var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-2').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

											$.each(inputsSelector1, function (i, item) {
												console.log('in $.each');
												console.log(item.value);

												$(this).val('');
											});
										}
									});
								}
							} else if(data == 'AuthType.SQ') {
								if(_this.current_login_screen_action != 'SQ') {
									$.ajax({
										type: "GET",
										url: _this.checkSQLoginUrl,
									}).done(function (data1) {
										console.log('data1');
										console.log(data1);

										if(data1 && data1 != 0) {
											_this.current_login_screen_action = 'SQ';

											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).hide();

											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('disabled', false);
											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('readonly', false);
											$(tabPlace).find('#section_depth_'+active_tab_id+'_1-4').show();

											var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-4').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

											$.each(inputsSelector1, function (i, item) {
												console.log('in $.each');
												console.log(item.value);

												if(i == 1)
													$(this).val(data1).attr('disabled', true);
											});
										} else {
											$('.info-place').show();
											$('.info-place span').addClass('danger').html('Error getting secret question.');
											$('.message-pool-link').hide();
											//alert('Error getting secret question');
										}
									});
								}
							} else {
								//alert(data);

								//window.location.href = data1;
							}
						}
					}
				});
			} else if(_this.current_login_screen_action == 'S') {
				$.ajax({
					type: 'GET',
					cache: false,
					url: _this.checkSMSLoginUrl,
					data: {action: 'check_code', confirmation_code: serializeArray[0]['value']},
					success: function (data) {
						console.log('data');
						console.log(data);

						if(data && data == 'Incorrect code') {
							var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-2').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

							$.each(inputsSelector1, function (i, item) {
								console.log('in $.each');
								console.log(item.value);

								//$(this).css('border-color', 'red !important');
								$(this).attr('style', 'border-color: red !important;');

								//var error_msg_div = $('<div />', {style: 'display: block; color: red'}).html('Incorrect Code.');
								//$(this).after(error_msg_div);

								$('.info-place').show();
								$('.info-place span').addClass('danger').html('Incorrect Code.');
								$('.message-pool-link').hide();
							});
						} else if(data != '') {
							if(data == 'AuthType.SQ') {
								if(_this.current_login_screen_action != 'SQ') {
									$.ajax({
										type: "GET",
										url: _this.checkSQLoginUrl,
									}).done(function (data1) {
										console.log('data1');
										console.log(data1);

										if(data1 && data1 != 0) {
											_this.current_login_screen_action = 'SQ';

											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).hide();

											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('disabled', false);
											$(tabPlace).find('.common_section_depth_class_'+active_tab_id).find('input, select, textarea, keygen').attr('readonly', false);
											$(tabPlace).find('#section_depth_'+active_tab_id+'_1-4').show();

											var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-4').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

											$.each(inputsSelector1, function (i, item) {
												console.log('in $.each');
												console.log(item.value);

												if(i == 1)
													$(this).val(data1).attr('disabled', true);
											});
										} else {
											$('.info-place').show();
											$('.info-place span').addClass('danger').html('Error getting secret question.');
											$('.message-pool-link').hide();
											//alert('Error getting secret question');
										}
									});
								}
							} else {
								//window.location.href = data1;
							}
						}
					}
				});
			} else if(_this.current_login_screen_action == 'SQ') {
				$.ajax({
					type: "GET",
					cache: false,
					url: _this.checkSQLoginUrl,
					data: {action: 'check_code', confirmation_code: serializeArray[0]['value']}
				}).done(function (data) {
					console.log('data');
					console.log(data);

					if(data && data == 'Incorrect answer') {
						var inputsSelector1 = $('.tab-content [data-section-lib="' + _this.selectedLib + '"].active .common_section_depth_'+active_tab_id+' #section_depth_'+active_tab_id+'_1-4').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled)').not('.form-control-grid');

						$.each(inputsSelector1, function (i, item) {
							console.log('in $.each');
							console.log(item.value);

							//$("#"+id).css('border-color', 'red !important');
							$(this).attr('style', 'border-color: red !important;');

							//var error_msg_div = $('<div />', {style: 'display: block; color: red'}).html('Incorrect Answer');
							//$(this).after(error_msg_div);

							$('.info-place').show();
							$('.info-place span').addClass('danger').html('Incorrect Answer.');
							$('.message-pool-link').hide();
						});
					} else if(data != '') {
						//window.location.href = data1;
					}
				});
			}
		}
	}

	this.generateReport = function (reportId, target) {
		console.log('in this.generateReport');
		console.log(sessionStorage['lastFoundData']);

		let data = {};

		data.reportId = reportId;
		data.searchResult = JSON.parse(sessionStorage['lastFoundData']);

		console.log('data');
		console.log(data);

		let batchFields = $('.batch-field').length ? $('.batch-field').serialize() : '';

		$('#'+target).html('Loading...').prop('disabled', true);

		var downloadInitUrl = this.downloadInitUrl;

		var _this = this;

		setTimeout(function() {
			$.ajax({
				type: 'POST',
				url: _this.generateReportUrl,
				data: ($('.report-mode-select').val() == 'batch') ? ('isBatch=true&reportId=' + data.reportId + '&' + batchFields) : data,
				success: function (data1) {
					if (data1.response && data1.response.PKList) {
						console.log('in data1.response');
						console.log(data1.response.PKList);

						//setTimeout(function() {
							$.each(data1.response.PKList, function (index, item) {
								console.log('in data1.response.PKList.map');
								console.log('item :: ' + item);
								console.log('downloadInitUrl :: ' + downloadInitUrl);

								$.ajax({
									type: 'GET',
									cache: false,
									url: _this.downloadInitUrl,
									data: {pk: item},
									success: function (data2) {
										console.log('in this.downloadInitUrl success');
										console.log(data2);

										/*if (data2.status == 'success') {
											if (data2.response['file']) {
												_this.downloadSuccessHelper('', data2.response.original_name, data2.response.file);
											} else {
												_this.addErrorMessageToArea('Downloading file from server');
												//$('.info-place span').removeClass('danger').html('Downloading file from server');
												//t.addClass('is-active');
												_this.downloadFileFragment('', item, data2.response, 0);
											}
										} else if (data2.status == 'error') {
											//t.removeClass('is-active');
											_this.addErrorMessageToArea(data2.message);
											//$('.info-place span').addClass('danger').html(data1.message);
										}*/
									},
									error: function (data2) {
										_this.addErrorMessageToArea(data2.responseJSON.message);
										//$('.info-place span').addClass('danger').html(data1.responseJSON.message);
										//t.removeClass('is-active');
									}
								});
							});
						//}, 2000);
					}
				},
				error: function (data1) {
					//$('#'+target).html('Error').addClass('btn-danger');
					//downloadLink.hide();
					_this.addErrorMessageToArea(data1.responseJSON.message);
				}
			});
		}, 1000);
	}

    this.triggerExecute = function (getFunction, customData, pre, post, gridSubId) {
        var me = this,
            id = (gridSubId) ? gridSubId : JSON.parse(sessionStorage['active-id-' + me.selectedLib]),
            activeTabId = $('.screen-tab.btn.active').data('tab-id');

        pre = (pre) ? pre : {};
        post = (post) ? post : {};

        this.addMessageToArea('Executing data...');
        $.ajax({
            type: 'POST',
            cache: false,
            url: me.customExecuteUrl,
            data: {id: id, activeTab: activeTabId, getFunction: getFunction, customData: customData, pre: pre, post: post},
            success: function (response) {
                me.showMessagePool(response);
                if (response.data) {
                    me.addMessageToArea('Extension has been executed');
                    me.activeMode = null;
                    me.reloadActiveLibTabs();
                } else {
                    me.addErrorMessageToArea('Error execute extension');
                }
            },
            error: function () {
                me.addErrorMessageToArea('Error execute extension');
            }
        });
    };

    this.triggerSpecialAction = function (action, button) {
        switch(action) {
            case 'edit':
                if (button == 'save') $('.special-sub-btns-edit .left-navigation-button-save').click();
                else if (button == 'cancel') $('.special-sub-btns-edit .left-navigation-button-cancel').click();
                break;
            case 'insert':
                if (button == 'save') $('.special-sub-btns-insert .left-navigation-button-save').click();
                else if (button == 'cancel') $('.special-sub-btns-insert .left-navigation-button-cancel').click();
                break;
            case 'copy':
                if (button == 'save') $('.special-sub-btns-copy .left-navigation-button-save').click();
                else if (button == 'cancel') $('.special-sub-btns-copy .left-navigation-button-cancel').click();
                break;
        }
    };

    /**
     * Getting search result
     * @param {string} library
     * @param {array} queries
     * @param {function} callback
     */
    this.searchResults = function (library, queries, callback) {
		console.log('in this.searchResults');

        var me = this;

		console.log('library');
		console.log(library);

		console.log('queries');
		console.log(queries);

		var temp_queries = typeof queries != 'object' ? JSON.parse(queries) : queries;

		console.log('temp_queries');
		console.log(temp_queries);

        $.post(me.searchUrl, {
            library: library,
            queries: temp_queries,
            aliasFrameworkInfo: me.aliasFrameworkInfo
        }, function (data) {
            if (data) {
                me.setLastSearchResults(data);
            }
            if (typeof callback == 'function') {
                callback(data);
            }
        });
    };

    this.inlineSearchResults = function (pk, queries, callback) {
        var me = this;

        $.post(me.inlineSearchUrl, {
            pk: pk,
            queries: queries
        }, function (data) {
            if (typeof callback == 'function') {
                callback(data);
            }
        });
    };

	this.inlineSearchTemp = function () {
		var me = this;

		$.post(me.inlineSearchTempUrl, function (data) {
            return data;
        });
	};

    this.bindLoadEvents = function () {
        var me = this;

        $(document)
            //Click on page bum button
            .on('click', '.navbar-collapse.collapse.in ul li a:not(.dropdown-toggle)', function () {
                $(this).parents('.collapse').collapse('hide');
            })
            .on('click', '.sub-pagination a:not(.active)', function (e) {
				console.log('in .sub-pagination a:not(.active)');

                e.preventDefault();

                var t = $(this),
                    statSection = t.parents('.stats-section'),
                    page = t.text(),
                    row = statSection.attr('data-row'),
                    col = statSection.attr('data-col'),
                    tid = $('.screen-tab.active').attr('data-tab-id'),
                    table = statSection.find('table'),
                    activePage = t.parents('ul').find('li.active a').text();

                if (t.attr('aria-label') == 'Previous') {
                    if (activePage - 1 >= 1) page = parseInt(activePage) - 1;
                    else return false;
                } else if (t.attr('aria-label') == 'Next') {
                    if (activePage + 1 <= t.parents('ul').find('li:nth-last-child(2) a').text()) page = parseInt(activePage) + 1;
                    else return false;
                }

                me.loadSubData(page, row, col, tid, me.activeMode, function (isSuccess, data) {
                    me.addDataToTable(data, table, page);
                    t.parents('ul').find('li').removeClass('active');
                    t.parents('ul').find('li').each(function () {
                        if ($(this).find('a').text() == page) {
                            $(this).addClass('active');
                            return false;
                        }
                    });
                });
            })
            //Click on add new row to table button
            .on('click', 'table .add-sub-item', function () {
                $(this).trigger('insert-top-table-custom-js');
                $(this).trigger('insert-left-table-custom-js');

                me.addGridLine($(this));
            })
            //Click on remove row to table button
            .on('click', 'table .remove-sub-item', function () {
                me.removeGridLine($(this));
            })
            //Change value of field in table
            .on('change', 'table .form-control-grid', function () {
				//alert('in change table .form-control-grid');

                $(this).trigger('edit-top-table-custom-js');
                $(this).trigger('edit-left-table-custom-js');

				me.changeGridLineElement($(this));
            })
            .on('click', 'table .return-sub-item', function () {
                me.unRemoveGridLine($(this));
            })
            //Click on detach button
            .on('click', '.detach-icon', function () {
                me.editOnly = false;
                var panelObject = $(this).parents('.panel');
                me.detachPanel(panelObject);
            })
            //Click on attach button
            .on('click', '.attach-icon', function () {
                var panelObject = $(this).parents('.panel');
                me.attachPanel(panelObject);
            })
            .on('click', '.tab-content input[type="radio"].form-control-grid', function () {
                var name = $(this).attr('name');

                $(this).parents('table').find('input[type="radio"][name="' + name + '"]').val(0).prop('checked', false).trigger('change');
                $(this).prop('checked', true).val(1).trigger('change');
            })
            .on('click', '.tab-content input[type="checkbox"]:not([data-field-group])', function () {
                $(this).val(+$(this).prop('checked')).trigger('change');
            })
            .on('click', 'input[data-field-group]', function () {
                var value = $(this).attr('data-field-group');

                $(this).parents('.stats-section, .header-section').find('[data-field-group="' + value + '"]').val(0).prop('checked', false).trigger('change');
                $(this).prop('checked', true).val(1).trigger('change');
            })
            .on('change', '.tab-content input:not(.form-control-grid), .tab-content select:not(.form-control-grid), .tab-content textarea:not(.form-control-grid)', function () {
				//console.log('in .tab-content input:not(.form-control-grid), .tab-content select:not(.form-control-grid), .tab-content textarea:not(.form-control-grid)');

                var me = $(this);
                $('[name="' + me.attr('name') + '"]:not(.search-field)').val(me.val());
            })
            .on('click', '.message-pool-link', function () {
				$('.message-pool').show();
				$('#message-modal').modal('show');

                /*var t = $(this);
                if (t.hasClass('is-hide')) {
                    t.removeClass('is-hide').show().html(t.attr('data-hide-text'));
                    $('.message-pool').show();
                } else {
                    t.addClass('is-hide').html(t.attr('data-show-text'));
                    $('.message-pool').hide();
                }*/
            })
            .on('click', '.download-file-link:not(".is-cached, .is-active")', function () {
                var t = $(this),
                    pk = t.data('pk');

                $('.info-place span').removeClass('danger').html('Start downloading file...');

                $.ajax({
                    //type: 'POST',
                    type: 'GET',
                    cache: false,
                    //url: me.getReportUrl ,
                    url: me.downloadInitUrl,
                    data: {pk: pk},
                    success: function (data) {
                        if (data.status == 'success') {
                            if (data.response['file']) {
                                me.downloadSuccessHelper(t, data.response.original_name, data.response.file);
                            } else {
                                $('.info-place span').removeClass('danger').html('Downloading file from server');
                                t.addClass('is-active');
                                me.downloadFileFragment(t, pk, data.response, 0);
                            }
                        } else if (data.status == 'error') {
                            t.removeClass('is-active');
                            $('.info-place span').addClass('danger').html(data.message);
                        }
                    },
                    error: function (data) {
                        $('.info-place span').addClass('danger').html(data.responseJSON.message);
                        t.removeClass('is-active');
                    }
                });
            })
            .on('click', '.upload-arrow-button:not(.is-completed, .is-active)', function () {
                var t = $(this),
                    family = t.attr('data-family'),
                    category = t.attr('data-category'),
                    fileName = t.attr('data-file-name'),
                    originalFileName = t.attr('data-original-file-name');

                $.ajax({
                    type: 'POST',
                    cache: false,
                    url: me.uploadInitUrl,
                    data: {
                        family: family,
                        category: category,
                        file_name: fileName,
                        original_file_name: originalFileName
                    },
                    success: function (data) {
                        if (data.status == 'success') {
                            $('.info-place span').removeClass('danger').html('Upload file to server');
                            t.addClass('is-active');
                            me.uploadFileFragment(t, fileName, data.response, 0, 1);
                        } else if (data.status == 'error') {
                            t.removeClass('is-active');
                            $('.info-place span').addClass('danger').html(data.message);
                        }
                    },
                    error: function (data) {
                        $('.info-place span').addClass('danger').html(data.responseJSON.message);
                        t.removeClass('is-active');
                    }
                });
            })
            .on('click', '.alert-field-btn', function (e) {
                me.setInfo('#alert-message-edit-modal .modal-body', false)
                var idAlertMessage = $(e.target).data('sub-id-btn');
                 $('input#alert-sub-id').val($(e.target).data('sub-id-btn'));
                 var jsonAlert = $('.sub-id[data-sub-id=' + idAlertMessage + ']').val();
                 if (jsonAlert) {
                     $('#alert_message').val(JSON.parse(jsonAlert).message);
                     $('#alert_type').val(JSON.parse(jsonAlert).type);
                 } else {
                     $('#alert_message').val('');
                     $('#alert_type').val('');
                 }

            })
            .on('click', '.alert-save', function (e) {
                var alertType = $('#alert_type').val();
                var alertMessage = $('#alert_message').val();
                var result = '';
                if (alertType && alertMessage) {
                    result = {};
                    result['type'] = alertType;
                    result['message'] = alertMessage;
                    result = JSON.stringify(result);
                }
                var inputResult = $('.sub-id[data-sub-id=' + $("input#alert-sub-id").val() + ']');
                $(inputResult).val(result);
                me.changeGridLineElement($(inputResult));

                me.setInfo('#alert-message-edit-modal .modal-body', 'success', 'Alert has been added');
            })
            .on('click', '.screen-stepper a', function () {
				var id = $(this).attr('data-id');
				var prev = $(this).attr('data-prev');
				var next = $(this).attr('data-next');

				me.activePrevNextTab('tabs', id);

                //$('.screen-group-tab [data-tab-id="' + id + '"]').click();
            })
            .on('click', '.screen-group-tab [data-tab-id]', function () {
				console.log('in .screen-group-tab [data-tab-id] click');

                var id = $(this).attr('data-tab-id');
                $('.screen-stepper a').removeClass('active');
                $('.screen-stepper a[data-id="' + id + '"]').addClass('active');

				//me.setActiveId(id);
            })
            .on('focus custom-focus', '[data-relation-field]', function () {
				//console.log('custom focus');

                var t = $(this),
                    data = t.data(),
                    relationValues = {},
                    queries = [];

                if (!data['customQuery']) {
                    t.html('').append('<option value="' + data['initValue'] + '" selected>' + data['initValue'] + '</option>');
                    return true;
                }

                if (data['relationId']) {
                    $.each(data['relationId'], function (name, id) {
                        var value;
                        if (value = $('#' + id).val()) {
                            relationValues[name] = value;
                        }
                    })
                }

                if (data['relationDefault']) {
                    $.each(data['relationDefault'], function (name, value) {
                        value = (relationValues[name]) ? relationValues[name] : value;
                        queries.push({name: name, value: value});
                    })
                }

                if (data['lastQueries'] && JSON.stringify(data['lastQueries']) === JSON.stringify(queries)) {
                    return true;
                } else {
                    t.data('last-queries', queries);
                }

                t.html('').append('<option selected>Loading ...</option>');
                me.inlineSearchResults(data['customQuery'], queries, function (result) {
					//console.log('result');
					//console.log(result);

                    if ($.isArray(result) && result.length > 0) {
						if(data['initValue'] == '')
							t.html('<option value="">Please select</option>');
						else
							t.html('');

                        $.each(result, function (i, item) {
							//console.log('item');
							//console.log(item);

                            var firstObject = item[Object.keys(item)[0]],
                                value = (item['value']) ? item['value'] : firstObject,
                                description = (item['description']) ? item['description'] : firstObject,
                                option = $('<option />', {text: description, value: value});

                            if (value == data['initValue']) {
								t.val(description);
								t.attr('title', description);
                                option.prop('selected', true);
                            }

                            t.append(option).blur();

							if(me.activeMode != null && me.activeMode != '') {
								if(t.hasClass('select-picker')) {
									setTimeout(function() {
										$('#'+t.attr('id')).selectpicker('refresh');
									}, 1000);
								}
							}
						});
                        $('[data-dependent-field]').dependentField('reset');
                    } else {
                        t.html('').append('<option value="' + data['initValue'] + '" selected>' + data['initValue'] + '</option>');
                        $('.info-place span').addClass('danger').html('Relation data for "' + t.attr('name') + '" not found');
                    }
                });
            })
            .on('click', '.field-custom-link', function (e) {
                var target = $(this).attr('target'),
                    href = $(this).attr('href'),
                    modal = $('#screen-modal');

                if (target == "_modal") {
                    e.preventDefault();
                    modal.find('iframe').attr('src', href);
                    modal.modal('show');
                } else if(target == "_self") {
					location.href = href;
					location.reload(true);
				}
            })
            .on('click', '.stepper--release[data-is-can-release]', function () {
                var icon = $(this),
                    tid = icon.data('tid'),
                    releaseTabList = icon.data('releaseTabList');

                if (tid && releaseTabList && releaseTabList.length > 0) {
                    me.addMessageToArea('Sending record to release...');
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        url: me.workflowReleaseUrl,
                        data: {
                            tabList: releaseTabList,
                            pk: (sessionStorage['active-id-' + me.selectedLib]) ? JSON.parse(sessionStorage['active-id-' + me.selectedLib]) : null
                        },
                        success: function (data) {
                            me.showMessagePool(data);
                            if (data.status == 'success') {
                                me.addMessageToArea(data.message);
                                me.changeMode();
                                icon.attr('data-is-can-release', false)
                                    .data('releaseTabList', null)
                                    .css({'color': 'black', 'cursor': 'not-allowed'});
                            } else if (data.status == 'error') {
                                me.addErrorMessageToArea(data.message);
                            }
                        },
                        error: function (data) {
                            me.addErrorMessageToArea(data.responseJSON.message);
                        }
                    });
                }
            })
            .on('click', '.stepper--lock', function () {
                var tid = $(this).data('tid'),
                    tabList = $(this).data('lockScreenList');

                me.clearResultApi();
                if (me.workflowInfo[tid] && !me.workflowInfo[tid]['locked']) {
                    var isWorkflow = me.workflowInfo[tid]['workflow'],
                        url = (isWorkflow) ? me.workflowUnlockUrl : me.workflowLockUrl;

                    me.addMessageToArea((isWorkflow ? 'Unlocking' : 'Locking') + ' screen at workflow...');
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        url: url,
                        data: {
                            tabList: tabList,
                            pk: JSON.parse(sessionStorage['active-id-' + me.selectedLib])
                        },
                        success: function (data) {
                            if (data.status == 'success') {
                                me.addMessageToArea(data.message);
                                me.changeMode();
                            } else if (data.status == 'error') {
                                me.addErrorMessageToArea(data.message);
                            }
                        },
                        error: function (data) {
                            me.addErrorMessageToArea(data.responseJSON.message);
                        }
                    });
                }
            })
            .on('change', 'input, select, textarea, keygen', function () {
                var newValue = $(this).val(),
                    notifyParams = $(this).data('notification-params') || {};

                $(this).data('value-changed', true);

                Object.keys(notifyParams).map(function (notifyName) {
                    if (typeof notifyParams[notifyName] == "object") {
                        Object.keys(notifyParams[notifyName]).map(function (paramName) {
                            if (notifyParams[notifyName][paramName] == 'new_value') {
                                notifyParams[notifyName][paramName] = newValue;
                            }
                        })
                    }
                });

                $(this).data('notification-params', notifyParams);
            })
            .on('hidden.bs.modal', '#screen-modal' , function () {
                modal = $('#screen-modal');
				modal.find('iframe').attr('src', '');

                location.reload();
            })
            .on('shown.bs.modal', '#generate-report-modal' , function (event) {
                let val = $(this).val();

                me.renderReportModal(val);
            })
            .on('click', '[data-report-id]', function () {
                let t = $(this),
                    data = t.data(),
                    downloadLink = t.nextAll('.download-file-link'),
                    batchFields = $('.batch-field').length ? $('.batch-field').serialize() : '';

                t.html('Loading...').prop('disabled', true);

				console.log('data');
				console.log(data);

                $.ajax({
                    type: 'POST',
                    url: me.generateReportUrl,
                    data: ($('.report-mode-select').val() == 'batch') ? ('isBatch=true&reportId=' + data.reportId + '&' + batchFields) : data,
                    success: function (data) {
                        if (data.response && data.response.PKList) {
                            t.hide();
                            data.response.PKList.map(function (item) {
                                $('#generate-report-modal').find('.modal-body').append(
                                    "<a class='btn btn-default download-file-link' data-pk='" + item + "'>" +
                                        "<div class='container-icon-inner'>" +
                                            "<span class='glyphicon glyphicon-arrow-down'></span> " +
                                            "<span class='container-text-inner'>Download from API server</span>" +
                                        "</div>" +
                                        "<div class='progress-inner'></div>" +
                                    "</a>" +
                                    "<br /><br />"
                                );
                            });
                        }
                    },
                    error: function (data) {
                        t.html('Error').addClass('btn-danger');
                        downloadLink.hide();
                        me.addErrorMessageToArea(data.responseJSON.message);
                    }
                });
            })
            .on('change', '.workflow-route-input', function () {
                let option = $(this).find('option:selected'),
                    groups = option.data('userGroups'),
                    flowId = option.data('flowId'),
                    UuId = option.data('uuid'),
                    AssignedTo = option.data('assigned-to'),
                    AllowUnassigned = 'f',
                    //AllowUnassigned = option.data('allow-unassigned'),
                    flowIdInput = $('.workflow-flow-id-input'),
                    UuIdInput = $('.workflow-flow-uuid-input'),
                    groupInput = $('.workflow-assigned-group-input'),
                    assignToUserInput = $('.workflow-assigned-user-input');

                flowIdInput.val(flowId);
                UuIdInput.val(UuId);

				if(AssignedTo == 't')
					assignToUserInput.prop('required', true);
				else
					assignToUserInput.prop('required', false);

				if(AllowUnassigned == 'f') {
					groupInput.parent().hide();
				} else {
					if (me.activeMode != 'insert') {
						groupInput.html('<option value="" selected>-- Please select route first --</option>');
					}

					if (!$(this).val()) {
						groupInput.prop('disabled', true);
						groupInput.change();

						return false;
					}

					groupInput.prop('disabled', false);
				}

				if(groups) {
					groups.map(function (groupName) {
						groupInput.append('<option value="' + groupName + '">' + groupName + '</option>');
					});
				}

				groupInput.find('option').first().prop('selected', true);
				groupInput.change();
            })
            .on('change', '.workflow-assigned-group-input', function () {
                let group = $(this).val(),
                    usersInput = $('.workflow-assigned-user-input');

                usersInput.prop('disabled', false).html('<option value="" selected>-- Please select group first --</option>');
                if (!group) {
                    usersInput.prop('disabled', true);
                    return false;
                }

				var itemsBlock = $('.workflow-task-item');
				var meta = itemsBlock.find('[name="TaskId"]:checked').data('meta');

				var AssignedToUser = '';

                usersInput.html('<option value="" selected>Loading...</option>');

                $.ajax({
                    type: 'POST',
                    url: me.getUserListUrl,
                    data: {'group': [group]},
                    success: function (data) {
						var selected = '';
	
                        usersInput.html('<option value="" selected>-- Select user --</option>');

                        if (data && data.list) {
							//console.log(data.list);

                            data.list.map(function (item) {
								if(meta)
									AssignedToUser = meta.AssignedToUser;

								if(AssignedToUser != '' && AssignedToUser == item.id.id)
									selected = 'selected';
								else
									selected = '';

                                //usersInput.append($('<option />', {text: item.user_name, value: item.id.id}));
								usersInput.append('<option value="'+item.id.id+'" '+selected+'>'+item.user_name+'</option>');
                            });
                        }
                    },
                    error: function (data) {
                        me.addErrorMessageToArea(data.responseJSON.message);
                    }
                });

				if(me.activeMode == null || me.activeMode == '')
					usersInput.prop('disabled', true);
				else
					usersInput.prop('disabled', false);
            })
            .on('click', '.save-route-btn', function () {
                me.triggerSpecialAction(me.activeMode, 'save');
            })
			.on('click', '.workflow-task-item__radio', function () {
				console.log('in workflow-task-item__radio radio checked');

				me.updateModeWorkflowContainer(me.activeMode);

				//if(me.activeMode == null || me.activeMode == '')
					//me.updateModeWorkflowContainer(me.activeMode);
            })
			.on('click', '.show-task-history-btn', function () {
				var descriptionBlock = $('.workflow-task-description');
				var itemsBlock = $('.workflow-task-item');
				var meta = itemsBlock.find('[name="TaskId"]:checked').data('meta');
				var TaskId = meta.TaskId;
				//var TaskId = meta.TaskKey.BugID;

				$('#task-history-tbl thead').html('');
				$('#task-history-tbl tbody').html('');

				$.ajax({
					type: 'POST',
					cache: false,
					url: me.getTaskHistoryUrl,
					data: {TaskId: TaskId},
					success: function (response) {
						//loading.hide();

						if (response.length) {
							var taskKeyColName = Object.keys(response[0].TaskKey);

							$('#task-history-tbl thead').append(
								'<tr>' +
									'<th>Action</th>' +
									'<th>CreatedBy</th>' +
									'<th>CustomerDefinedID</th>' +
									'<th>Flow</th>' +
									'<th>ParentId</th>' +
									'<th>From Step</th>' +
									'<th>To Step</th>' +
									'<th>Task</th>' +
									'<th>' + taskKeyColName + '</th>' +
									'<th>User</th>' +
								'</tr>'
							);

							$(response).each(function (index, item) {
								var colName = Object.keys(item.TaskKey)[0];

								$('#task-history-tbl tbody').append(
									'<tr>' +
										'<td>' + item.ActionPerformed + '</td>' +
										'<td>' + item.CreatedBy + '</td>' + 
										'<td>' + item.CustomerDefinedID + '</td>' + 
										'<td>' + item.FlowName + '</td>' +
										'<td>' + item.ParentId + '</td>' +
										'<td>' + item.StepName + '</td>' +
										'<td>' + item.ToStepName + '</td>' +
										'<td>' + item.TaskId + '</td>' +
										'<td>' + item.TaskKey[colName] + '</td>' +
										'<td>' + item.user_name + '</td>' +
									'</tr>'
								)
							});

							$('#task-history-modal').modal('show');
						} else {
							alert('No record found');
						}
					}
				});
            })
			.on('shown.bs.modal', '#document-modal' , function (event) {
                let val = $(this).val();

                me.renderDocumentModal(me.activeMode, val);
            })
			.on('click', '.add-category-family', function () {
				var wrapper = $('.document-family-wrapper'),
					wrapper2 = $('.document-post-form-div'),
					iteration = parseInt($('#document-cnt').val()) + 1,
					newWrapper = $('<div />', {class: 'row'}).html(wrapper.html());

				newWrapper.find('input, select').each(function () {
					var name = $(this).attr('name');

					if(name == 'document_id[]')
						$(this).attr('id', 'document_id_'+iteration);

					if(name == 'document_category[]') {
						$(this).val('');
						$(this).attr('id', 'document_category_'+iteration);
						$(this).attr('data-id', iteration);
					}

					if(name == 'document[]')
						$(this).attr('id', iteration);

					if(name == 'description[]')
						$(this).attr('id', 'document_description_'+iteration);
				});
            
				wrapper2.append(newWrapper);
				$('#document-cnt').val(iteration);
			})
			.on('click', '.remove-family-icon', function () {
				if (confirm('A you\'re a sure want to delete this document?')) {
					$(this).parents('.row')[0].remove();
				}
			})
			.on('click', '.remove-document-icon', function () {
				if (confirm('A you\'re a sure want to delete this document?')) {
					var pk = $(this).data('id');
					var field_id = $(this).attr('id');

					$.ajax({
						type: 'POST',
						url: me.getDeleteDocumentUrl,
						data: {'pk': pk},
						//dataType: 'json',
						success: function (response) {
							//console.log(response.length);
							//loading.hide();

							if(response) {
								//alert('Remove Row');

								$('#'+field_id).parents('.row')[0].remove();
							}
						}
					});
				}
			})
			.on('click', '.document-model-undelete-btn', function () {
				if (confirm('A you\'re a sure want to undelete this document?')) {
					var pk = $(this).attr('id');

					$.ajax({
						type: 'POST',
						url: me.getUndeletedDocumentUrl,
						data: {'pk': pk},
						//dataType: 'json',
						success: function (response) {
							//console.log(response.length);
							//loading.hide();

							if(response) {
								$('#row_'+pk).remove();
							}
						}
					});
				}
			})
			.on('submit', '#edit-document-form-id', function(e) {
				e.preventDefault();

				var data = new FormData();

				var alert = $('.alert-danger');
				var alertMessageArea = alert.find('.error-message');

				var check = true;

				$('.common-document-category-class').each(function(index, item) {
					var id = $(this).data('id');

					if(id != undefined && id != 'undefined') {
						var document_id = $('#document_id_'+id).val();

						var document_category_id = $('#document_category_'+id);
						var document_description_id = $('#document_description_'+id);
						var document_file_id = $('#'+id);

						if(document_category_id.val() == '') {
							document_category_id.parent().addClass('has-error');
							var error_msg_div = $('<div />', {class: 'help-block'}).html('Please fill out this field.');
							document_category_id.after(error_msg_div);

							check = false;
						}

						if(document_id == 0) {
							if(document_file_id.val() == '') {
								document_file_id.parent().addClass('has-error');
								var error_msg_div = $('<div />', {class: 'help-block'}).html('Please select document to upload.');
								document_file_id.after(error_msg_div);

								check = false;
							}

							if(check) {
								document_category_id.parent().removeClass('has-error');
								document_category_id.next('div').remove();

								document_file_id.parent().removeClass('has-error');
								document_file_id.next('div').remove();

								var fileUpload = $('#'+id).get(0);
								var files = fileUpload.files;

								if (files.length != 0) {
									console.log(files);

									data.append('file', files[0]);

									$.ajax({
										type: 'POST',
										url: me.getDocumentUploadUrl,
										data: data,
										//dataType: 'json',
										contentType: false,
										cache: false,
										processData: false,
										success: function (response) {
											//console.log(response.length);
											//loading.hide();

											if(response) {
												console.log(response);

												var family = $('#document-modal').find('#document_family').val();
												var category = $('#document-modal').find('#document_category_'+id).val();
												var description = $('#document-modal').find('#document_description_'+id).val();
												var kps = $('#document-modal').find('#document_kps').val();
												var fileName = response.name;

												$.ajax({
													type: 'POST',
													cache: false,
													url: me.getDocumentInitUploadUrl,
													data: {
														family: family,
														category: category,
														description: description,
														file_name: fileName,
														kps: kps
													},
													success: function (data) {
														console.log(data);

														//alert.hide();

														if (data.status == 'success') {
															//t.addClass('is-active').prop('disabled', true);
															me.uploadDocumentFileFragment(me.this, fileName, data.response, 0, 1);
														} else if (data.status == 'error') {
															//alert.show();
															//alertMessageArea.html(data.message);
															//t.removeClass('is-active').prop('disabled', false);
														}
													},
													error: function (data) {
														alert.show();
														alertMessageArea.html(data.message);
														t.removeClass('is-active').prop('disabled', false);
													}
												});
											}
										}
									});
								}
							}
						} else {
							if(check) {
								document_category_id.parent().removeClass('has-error');
								document_category_id.next('div').remove();

								document_file_id.parent().removeClass('has-error');
								document_file_id.next('div').remove();

								$.ajax({
									type: 'POST',
									cache: false,
									url: me.getUpdateDocumentUrl,
									data: {
										pk: document_id,
										category: document_category_id.val(),
										description: document_description_id.val()
									},
									success: function (data) {
										console.log(data);

										//alert.hide();
									},
									error: function (data) {
										
									}
								});
							}
						}
					}
				});

				/*$.each($("input[type='file']"), function(i, file) {
					var id = $(this).attr('id');

					
				});*/
			})
			.on('click', '.common_document_download_link_class', function () {
				var id = $(this).attr('id');
				var filePK = $('.download_document_id_'+id).data('id');
				var fileName = $('.download_document_id_'+id).data('file-name');
				var fileHash = $('.download_document_id_'+id).data('file-hash');
				var fileSize = $('.download_document_id_'+id).data('file-size');
				var chunkSize = $('.download_document_id_'+id).data('chunk-size');

				me.downloadDocumentFragment(filePK, fileName, fileSize, 0, chunkSize, 0);
			})
            .on('click', '.common_document_annotate', function () {
                var id = $(this).attr('id');
                var data = {
                    filePK: $('.download_document_id_' + id).data('id'),
                };

                var url = 'file/annotate-pdf?' + $.param(data);
                var win = window.open(url, '_blank');
                win.focus();
            })
			.on('click', '.show-deleted-document-icon', function () {
				$('.show-deleted-document-icon').hide();
				$('.document-model-list-return-btn').show();

				var id = $(this).attr('id');
				var id_split = id.split('_');
				var final_id = id_split[1];

				var kps = $('#'+final_id).data('document-kp');

				$.ajax({
					type: 'POST',
					cache: false,
					url: me.getGetDeletedDocumentListUrl,
					data: {'kp' : kps},
					success: function (response) {
						//console.log(response);

						modalHeaderTitle = $('#document-modal').find('.modal-title');
						modalBody = $('#document-modal').find('.modal-body');

						modalHeaderTitle.html("View Deleted Document");

						$('#document-modal').find('.view-deleted-document-list-tbl thead').html('');
						$('#document-modal').find('.view-deleted-document-list-tbl tbody').html('');

						$('#document-modal').find('.view-deleted-document-list-tbl thead').append(
							'<tr>' +
								'<th>File Name</th>' +
								'<th>Description</th>' +
								'<th>File Size</th>' +
								'<th>Created By</th>' +
								'<th>Created Date</th>' +
								'<th>Action</th>' +
							'</tr>'
						);

						$('#document-modal').find('.view-deleted-document-list-tbl').show();
						$('#document-modal').find('.edit-document-modal-div').hide();

						if (response.length) {
							$(response).each(function (index, item) {
								$('.view-deleted-document-list-tbl tbody').append(
									'<tr id="row_'+item.id+'">' +
										'<td><a href="javascript:void(0);" class="common_document_download_link_class download_document_id_'+index+'" id="'+index+'" data-file-name="'+item.original_file_name+'" data-file-hash="'+item.original_file_hash+'" data-file-size="'+item.original_file_size+'" data-chunk-size="'+item.chunk_size+'" data-id="'+item.id+'">' + item.original_file_name + '</a></td>' +
										'<td>' + item.Description + '</td>' + 
										'<td>' + item.original_file_size + '</td>' + 
										'<td>' + item.CreatedBy + '</td>' +
										'<td>' + item.CreatedDate + '</td>' +
										'<td><button type="button" class="btn btn-info document-model-undelete-btn" id="'+item.id+'">Undelete</button></td>' +
									'</tr>'
								)
							});
						} else {
							$('.view-deleted-document-list-tbl tbody').append(
								'<tr>' +
									'<td colspan="6">No record found</td>' +
								'</tr>'
							);
						}
					}
				});
			})
			.on('click', '.document-model-list-return-btn', function () {
				$('.show-deleted-document-icon').show();
				$('.document-model-list-return-btn').hide();

				var id = $(this).attr('id');
				var id_split = id.split('_');
				var final_id = id_split[1];

				var kps = $('#'+final_id).data('document-kp');

				//console.log(id);

				$.ajax({
					type: 'POST',
					cache: false,
					url: me.getDocumentListUrl,
					data: {'kp' : kps},
					success: function (response) {
						//console.log(response);

						modalHeaderTitle = $('#document-modal').find('.modal-title');
						modalBody = $('#document-modal').find('.modal-body');

						modalHeaderTitle.html("Edit Document");

						$('#document-modal').find('.view-deleted-document-list-tbl').hide();
						$('#document-modal').find('.edit-document-modal-div').show();

						if (response.length) {
							$('#document-modal').find('.document-post-form-div').html('');

							$('#document-modal').find('#document-cnt').val(response.length - 1);

							$(response).each(function (index, item) {
								console.log(item);

								var document_category = $('#'+final_id).data('document-category');

								var options = '<option value="">-- Select category --</option>';

								if(document_category.length) {
									$(document_category).each(function (index1, item1) {
										if(item1 == item.document_category)
											options += '<option value="'+item1+'" selected>'+item1+'</option>';
										else
											options += '<option value="'+item1+'">'+item1+'</option>';
									});
								}

								var id = index,
								wrapper = $('<div />', {
									'class': 'row'
								}).append($('<input />', {
									'type': 'hidden',
									'name': 'document_id[]',
									'id': 'document_id_'+index,
									'value': item.id
								})),
								document_category = $('<div />', {
									'class': 'col-sm-2'
								}).append($('<select />', {
									'class': 'form-control common-document-category-class',
									'name': 'document_category[]',
									'id': 'document_category_'+id,
									'data-id': id
									//'readonly': true
								}).append(options)
								),
								description = $('<div />', {
									'class': 'col-sm-3 form-group'
								}).append($('<input />', {
									'type': 'text',
									'name': 'description[]',
									'id': 'document_description_'+id,
									'class': 'form-control',
									'value': item.Description
								})),
								file_name = $('<div />', {
									'class': 'col-sm-6 form-group'
								}).append($('<span />', {
									'id': 'file_name_'+id,
									'text': item.original_file_name
								})),
								/*file_size = $('<div />', {
									'class': 'col-sm-1 form-group'
								}).append($('<span />', {
									'id': 'file_size_'+id,
									'text': item.original_file_size
								})),
								created_by = $('<div />', {
									'class': 'col-sm-2 form-group'
								}).append($('<span />', {
									'id': 'created_by_'+id,
									'text': item.CreatedBy
								})),
								created_date = $('<div />', {
									'class': 'col-sm-2 form-group'
								}).append($('<span />', {
									'id': 'created_date_'+id,
									'text': item.CreatedDate
								})),*/
								remove_icon = $('<div />', {
									'class': 'col-sm-1 form-group'
								}).append($('<span />', {
									'id': item.id,
									'class': 'glyphicon glyphicon-remove remove-document-icon',
									'data-id': item.id
								}));

								wrapper.append(document_category).append(description).append(file_name).append(remove_icon);//wrapper.append(document_category).append(file_name).append(description).append(file_size).append(created_by).append(created_date).append(remove_icon);

								$('#document-modal').find('.document-post-form-div').append(wrapper);
							});
						}
					}
				});
			})
			.on('change', '.common_field_upload_image_class', function () {
				//alert('Upload File');

				var input_id = $(this).data('input-id');
				var file_id = $(this).attr('id');

				//alert(input_id);
				//alert(file_id);

				var data = new FormData();

				var fileUpload = $('#'+file_id).get(0);
				var files = fileUpload.files;

				//console.log(files);

				data.append('file', files[0]);

				$.ajax({
					type: 'POST',
					url: me.getFieldUploadImageUrl,
					data: data,
					contentType: false,
					cache: false,
					processData: false,
					success: function (res) {
						console.log(res);

						if(res.data != '') {
							$('#'+input_id).val(res.data);

							$('#image_'+input_id).prop('src', 'data:image/jpeg;base64,'+res.data);
							$('#image_'+input_id).css('display', 'inline-block');
						}
					},
					error: function (res) {
						
					}
				});
			})
			.on('change', '.common_tbl_field_upload_image_class', function () {
				//alert('Upload File');

				var input_id = $(this).data('input-id');
				var file_id = $(this).attr('id');

				//alert(input_id);
				//alert(file_id);

				var data = new FormData();

				var fileUpload = $('#'+file_id).get(0);
				var files = fileUpload.files;

				//console.log(files);

				data.append('file', files[0]);

				$.ajax({
					type: 'POST',
					url: me.getFieldUploadImageUrl,
					data: data,
					contentType: false,
					cache: false,
					processData: false,
					success: function (res) {
						console.log(res);

						if(res.data != '') {
							$('#dimage'+input_id).val(res.data);

							$('#dimage_show_'+input_id).prop('src', 'data:image/jpeg;base64,'+res.data);
							$('#dimage_show_'+input_id).css('display', 'inline-block');
						}
					},
					error: function (res) {
						
					}
				});
			})
			.on('click', '.common_image_button_class', function () {
				//var field_id = $(this).attr('id');
				//var image_val = $(this).data('image-val');

				//var image_val = $(this).find('img').attr('src');

				//var realWidth = $(this).find('img').naturalWidth;
				//var realHeight = $(this).find('img').naturalHeight;

				//alert(realWidth + ' :: ' + realHeight);

				$('#image-modal').find('.show_full_width_image').attr('src', $(this).find('img').attr('src'));
				$('#image-modal').modal('show');
			})
			.on('change', '.common-linked-list-field', function () {
				var field_id = $(this).attr('data-id');
				console.log(field_id);

				$('.common-linked-list-field').each(function() {
					//console.log($(this).val());

					var source_field_identifier = $(this).data('source-field-identifier');
					var target_field_identifier = $(this).data('target-field-identifier');

					if(source_field_identifier && source_field_identifier != '' && source_field_identifier == field_id) {
						console.log('in if');

						var selected_target_field_val = $('#'+target_field_identifier).val();

						var query_to_execute = $(this).data('query-to-execute');
						var query_param = $(this).val();
						//var query_param = $('#'+source_field_identifier).val();

						//alert(query_to_execute);

						//console.log('field_id :: source_field_identifier :: target_field_identifier');
						//console.log(field_id +' ::  '+ source_field_identifier +' :: '+ target_field_identifier);

						$.ajax({
							type: 'POST',
							url: me.searchLinkedListCustomQueryUrl,
							data: {custom_query: query_to_execute, custom_query_param: query_param},
						}).done(function (res) {
							//console.log('res');
							//console.log(res);
							//console.log(res.length);

							//$('.linked-list-field-class-'+field_id).html('<option value="">-- Please select --</option>');
							$('#'+target_field_identifier).html('<option value="">-- Please select --</option>');

							if(res.length > 0) {
								$(res).each(function(index, value) {
									//console.log(value);

									//$('.linked-list-field-class-'+field_id).append('<option value="'+value+'">'+value+'</option>');

									if(selected_target_field_val != '' && selected_target_field_val == value)
										var selected = 'selected';
									else
										var selected = '';

									$('#'+target_field_identifier).append('<option value="'+value+'" '+selected+'>'+value+'</option>');
								});
							}
						});
					}
				});
			})
			.on('change', '.common-refresh-section-class', function () {
				console.log('in common-refresh-section-class change');

				$('.common-refresh-section-class').each(function(i, obj) {
					var field_identifier = $(this).data('field-identifier');
					var section_to_refresh = $(this).data('section-to-refresh');

					if(section_to_refresh != '' && section_to_refresh != null && section_to_refresh != undefined)
						var field_id = $(this).attr('id');
					else var field_id = null;

					var tabPlace = $('.screen-tab.active').attr('data-target');
					var tabId = $('.screen-tab.active').attr('data-tab-id');

					var refresh_section_val = $('.refresh-section-class-'+field_identifier).val();

					var template_layout_section_depth_active_ids = [];

					var section_depth_refresh_field_linked_value = $(this).val();

					console.log('section_depth_refresh_field_linked_value :: ' + section_depth_refresh_field_linked_value);

					var template_layout_section_row_cnt = $(tabPlace).data('template-layout-section-row-cnt');
					var template_layout_section_col_cnt = $(tabPlace).data('template-layout-section-col-cnt');

					console.log('template_layout_section_row_cnt :: ' + template_layout_section_row_cnt);
					console.log('template_layout_section_col_cnt :: ' + template_layout_section_col_cnt);

					var template_section_depth_cnt = 1;

					$(tabPlace).find('.common_section_depth_class_'+tabId).hide();

					$(tabPlace).find('.common_section_depth_class_'+tabId).find('input, select, textarea, keygen').attr('disabled', true);

					$(tabPlace).find('.common_section_depth_'+tabId).each(function () {
						console.log('in common_section_depth');

						template_layout_section_depth_row_num = $(this).data('template-layout-section-depth-row-num');

						console.log('template_layout_section_depth_row_num :: ' + template_layout_section_depth_row_num);

						for(var row = 1; row <= template_layout_section_row_cnt; row++) {
							if(template_layout_section_depth_row_num == row) {
								template_section_depth_cnt = $(this).data('template-layout-section-depth-cnt');

								console.log('template_section_depth_cnt :: ' + template_section_depth_cnt);

								for(var col = 1; col <= template_layout_section_col_cnt; col++) {
									if(col == 1)
										var temp_col = 1;
									else if(col == 2 && template_section_depth_cnt > 1)
										var temp_col = 8;
									else if(col ==2 && template_section_depth_cnt == 1)
										var temp_col = 1;

									var template_layout_section_depth_active_id = '#section_depth_'+tabId+'_'+row+'-'+temp_col;

									console.log('default template_layout_section_depth_active_id :: ' + template_layout_section_depth_active_id);

									var section_depth_linked_field_value = '';
									for(var i = temp_col; i <= template_section_depth_cnt; i++) {
										section_depth_linked_field_value = $('#section_depth_'+tabId+'_'+row+'-'+i).data('template-layout-section-depth-linked-field-value');

										//section_depth_linked_field_value = $('#section_depth_linked_field_value_'+tabId+'_'+row+'_'+i).val();

										console.log('section_depth_linked_field_value :: ' + section_depth_linked_field_value);

										if(section_depth_linked_field_value != '' && section_depth_linked_field_value != undefined && section_depth_linked_field_value == section_depth_refresh_field_linked_value)
											template_layout_section_depth_active_id = '#section_depth_'+tabId+'_'+row+'-'+i;
									}

									console.log('active template_layout_section_depth_active_id :: ' + template_layout_section_depth_active_id);

									if($.inArray(template_layout_section_depth_active_id, template_layout_section_depth_active_ids) == -1)
										template_layout_section_depth_active_ids.push(template_layout_section_depth_active_id);
								}
							}
						}
					});

					console.log('template_layout_section_depth_active_ids');
					console.log(template_layout_section_depth_active_ids);

					console.log('template_layout_section_depth_active_ids.length :: ' + template_layout_section_depth_active_ids.length);

					if(template_layout_section_depth_active_ids.length > 0) {
						$.each(template_layout_section_depth_active_ids, function (index, val) {
							var active_section_id = val;

							$(tabPlace).find(active_section_id).show();

							$(tabPlace).find(active_section_id).find('input, select, textarea, keygen').removeAttr('disabled');

							$(tabPlace).find(active_section_id+' [data-dependent-field]').dependentField();
							$(tabPlace).find(active_section_id+' [readonly][data-krajee-datetimepicker]').each(function () {
								$('#' + $(this).attr('id') + '-datetime').on('show', function(){
									$(this).datetimepicker('hide');
								});
							});

							$(tabPlace).find(active_section_id+' [data-relation-field]').each(function () {
								$(this).trigger('custom-focus');
							});

							if(me.activeMode == 'insert' || me.activeMode == 'edit')
								$(tabPlace).find(active_section_id+' input.form-control.tt-input').css('background-color', '#fff');

							$(tabPlace).find(active_section_id+' #search_extra_param').each(function () {
								var layout_type = $(this).find('#layout_type').val();
								var mode = $(this).find('#mode').val();
								var readonly = $(this).find('#readonly').val();

								if(layout_type == 'TABLE' && mode == 'edit' && readonly == 1) {
									$(tabPlace).find(active_section_id+' .add-sub-item').parent().parent().hide();
									$(tabPlace).find(active_section_id+' .remove-sub-item').parent().hide();
								} else if(mode == 'edit' && me.activeMode != mode) {
									me.activeMode = mode;
									me.setLockedActiveId(true);
									//me.changeMode(mode);
								}
							});

							$(tabPlace).find(active_section_id+' .grid-view').each(function () {
								var widget_id = $(this).attr('id');
								var widget_table_pagination_count = $('#'+widget_id+'_table_pagination_count').val();
								var widget_table_row_count = $('#'+widget_id+'_table_row_count').val();

								if(me.activeMode == '' || me.activeMode == null) {
									if(button_action == 'search_submit') {
										if(widget_table_row_count > widget_table_pagination_count) {
											//console.log('in if');

											$(this).find('.'+widget_id+'_tbl').DataTable({
												//"scrollY": 400,
												"pageLength": widget_table_row_count,
												"order": [],
												//"scrollCollapse": true,
												//"scrollX": true,
												//"sScrollX": "100%",
												//"sScrollXInner": "110%",
												"bAutoWidth": false,
												//"iDisplayLength":-1,
												"scrollY": '50vh',
												//scrollCollapse: true,
												"autoWidth": false,
											});
										} else {
											//console.log('in else');

											$(this).find('.'+widget_id+'_tbl').DataTable({
												//"scrollY": 400,
												"pageLength": widget_table_row_count,
												"order": [],
												//"scrollCollapse": true,
												//"scrollY": false,
												//"scrollX": true,
												//"sScrollX": "100%",
												//"sScrollXInner": "110%",
												"bAutoWidth": false,
												//"iDisplayLength":-1,
												//"scrollY": '50vh',
												//scrollCollapse: true,
												"autoWidth": false,
											});
										}

										//$(tabPlace).find('.stats-section .grid-view').after('<div class="row"><div class="col-sm-6"><br>&nbsp;</div><div class="col-sm-6"><br><button type="button" style="max-width: 100%; display: inline-block; vertical-align: top; width: 100%;" class="btn btn-default" id="common_export_to_excel_btn" data-button-action="'+button_action+'" data-table-id="'+widget_id+'">Export to Excel</button></div></div>');
									} else {
										if(widget_table_row_count > widget_table_pagination_count) {
											//console.log('in if');

											$(this).find('.'+widget_id+'_tbl').DataTable({
												//"scrollY": 400,
												"pageLength": widget_table_row_count,
												"order": [],
												//"scrollCollapse": true,
												//"scrollX": true,
												//"sScrollX": "100%",
												//"sScrollXInner": "110%",
												"bAutoWidth": false,
												//"iDisplayLength":-1,
												"scrollY": '50vh',
												//scrollCollapse: true,
												"autoWidth": false,
											});
										} else {
											//console.log('in else');

											$(this).find('.'+widget_id+'_tbl').DataTable({
												//"scrollY": 400,
												"pageLength": widget_table_row_count,
												"order": [],
												//"scrollCollapse": true,
												//"scrollY": false,
												//"scrollX": true,
												//"sScrollX": "100%",
												//"sScrollXInner": "110%",
												"bAutoWidth": false,
												//"iDisplayLength":-1,
												//"scrollY": '50vh',
												//scrollCollapse: true,
												"autoWidth": false,
											});
										}
									}

									setTimeout(function() {
										$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
									}, 4000);
								} else if(me.activeMode == 'edit') {
									if(widget_table_row_count > widget_table_pagination_count)
										$(this).find(".table-responsive").addClass("is-top-scroll");
								}
							});

							if(me.activeMode == 'edit') {
								$(tabPlace).find(".table tbody tr[data-key=-1]").find("input, select, textarea").val('');
								$(tabPlace).find(".table tbody tr[data-key=-1]").find("div, select, input, textarea").hide();
							}

							me.addMessageToArea('<span style="text-transform: capitalize">' + ((me.activeMode) ? me.activeMode : 'Tab') + '</span> data has been loaded');

							if (me.activeMode == 'insert' || me.activeMode == 'edit'  || me.activeMode == 'copy') {
								var gridItem = $(tabPlace).find(active_section_id+' .grid-view');
							} else if (!me.activeMode) {
								me.setRelationDataBlockInfo();
							}

							me.checkAvailableBtnLoad();

							var tabindex = 1;
							$('.tab-content').find(active_section_id+' .stats-section, .header-section').each(function () {
								var inputs = $(this).find('input,select,textarea,keygen,button');
								inputs.sort(function(a, b) {
									var gridStackBlockForA = $(a).parents('.grid-stack-item'),
										gridStackBlockForB = $(b).parents('.grid-stack-item'),
										xAxisForA = gridStackBlockForA.attr('data-gs-x'),
										xAxisForB = gridStackBlockForB.attr('data-gs-x'),
										yAxisForA = gridStackBlockForA.attr('data-gs-y'),
										yAxisForB = gridStackBlockForB.attr('data-gs-y');

									return (yAxisForA*10+xAxisForA) - (yAxisForB*10+xAxisForB);
								});

								inputs.each(function () {
									if ($(this).attr('tabindex') != -1) {
										$(this).attr('tabindex', tabindex++);
									}
								});
							});

							$(tabPlace).find(active_section_id+' [tabindex="1"]').trigger('focus');

							if($('.workflow-task-notification').text() != 0 && $('.workflow-task-notification').text != '')
								$('.workflow-task-notification').show();
						});
					}

					/*if(sessionStorage['lastFoundData']) {
						var lastFoundData = JSON.parse(sessionStorage['lastFoundData']);

						console.log('lastFoundData');
						console.log(lastFoundData);

						console.log('section_to_refresh :: ' + section_to_refresh);

						me.setActiveId(lastFoundData.id, lastFoundData, false, section_to_refresh, $(this).val(), field_id, '');
					} else {
						me.setActiveId('', '', false, section_to_refresh, $(this).val(), field_id, '');
					}*/
				});
			})
			.on('keydown', '.numeric-input', function () {
				/*console.log($(this).val());

				if($(this).val() == 0)
					return false;
				else
					return true;*/
			})
			//$('.numeric-input').bind('copy paste cut',function(e) {
			.on('paste', '.numeric-input', function (e) {
				e.preventDefault();

				//alert('cut,copy & paste options are disabled !!');
			})
			.on('keyup', '.numeric-input', function (e) {
				if($(this).val() != '' && $(this).val().length == 1 && $(this).val() == 0)
					$(this).val('');

				//alert('cut,copy & paste options are disabled !!');
			})
			.on('paste', '.phone-input', function (e) {
				e.preventDefault();

				//alert('cut,copy & paste options are disabled !!');
			})
			.on('keyup', '.phone-input', function (e) {
				if($(this).val() != '' && !isNaN($(this).val()) && $(this).val().length == 1 && $(this).val() == 0) {
					$(this).val('');
				} else {
					var number = $(this).val().replace(/[^\d]/g, '');

					if (number.length == 7) {
						number = number.replace(/(\d{3})(\d{4})/, "$1-$2");
					} else if (number.length == 10) {
						number = number.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
					} else {
						number = number.replace(/(\d{3})(\d{3})(\d{4})/g, "($1) $2-$3-");
					}

					$(this).val(number);
				}

				//alert('cut,copy & paste options are disabled !!');
			})
			.on('click', '#common_export_to_excel_btn', function (e) {
				console.log('in common_export_to_excel_btn');

				var button_action = $(this).data('button-action');
				var table_id = $(this).data('table-id');

				me.exportToExcel(table_id);
			})
			/*.on('change', '.common-list-with-extensin-function-field', function () {
				var field_identifier = $(this).attr('field_identifier');

				//var field_selected_val = $('').val();

				//alert(field_selected_val);

				//field_selected_val

				me.changeMode('execute');
			})*/
			.on('shown.bs.tab', function(e) {
				$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
			})
            .ajaxComplete(function () {
                me.maskedEvent();
                me.maskDecimal();
            });
    };

	this.exportToExcel = function (table_id) {
		console.log('in this.exportToExcel');

		var table_titles_obj = {};

		var table_data_arr = [];

		//var table_data = $('.'+table_id+'_tbl').DataTable().rows().data().toArray();

		//console.log(table_data);
		//console.log($('.'+table_id+'_tbl').DataTable().rows().data().toArray());

		/*console.log($('.'+table_id+'_tbl').DataTable().settings().init().columns);

		var columns = $('.'+table_id+'_tbl').DataTable().settings().init().columns;

		$('.'+table_id+'_tbl').DataTable().columns().every(function(index) { 
			console.log($('.'+table_id+'_tbl').DataTable().column(index).title());
		});*/

		/*$('.'+table_id+'_tbl thead tr th').each(function() {
			//alert(this.innerHTML);
		});*/

		$('.'+table_id+'_tbl').DataTable().columns().every(function (index) {
			//console.log('index :: ' + index);

			var visible = this.visible();

			if(visible)
				table_titles_obj[index] = this.header().innerHTML;

			/*var temp_json = {};

			var data = this.data();

			console.log(data[0]);

            var visible = this.visible();

			if(visible) {
				temp_json[this.header().innerHTML] = data[0];
			}*/

			//table_data_arr.push(temp_json);
		});

		//console.log('table_titles_obj');
		//console.log(table_titles_obj);

		//console.log('table_data_arr');
		//console.log(table_data_arr);

		/*$('.'+table_id+'_tbl').DataTable().columns().every(function (index) {
			console.log(index);

			//console.log(tableColumns[index].name);
		});*/

		var table = $('.'+table_id+'_tbl').DataTable();

		table.rows().every(function(rowIdx, tableLoop, rowLoop) {
			var temp_json = {};

			///temp_json = table_titles_obj;

			//console.log('temp_json');
			//console.log(temp_json);

			//console.log(rowIdx);

			//console.log(table.columns(rowIdx).data());

			var data = this.data();

			//console.log(data);

			$.each(data, function (index, val) {
				//console.log('index :: ' + index);
				//console.log('val :: ' + val);

				var index_val = table_titles_obj[index];

				temp_json[table_titles_obj[index]] = val;
			});

			table_data_arr.push(temp_json);
		});

		//console.log('table_data_arr');
		//console.log(table_data_arr);

		this.JSONToCSVConvertor(table_data_arr, 'render', true);
	}

	this.JSONToCSVConvertor = function (JSONData, ReportTitle, ShowLabel) {
		//If JSONData is not an object then JSON.parse will parse the JSON string in an Object
		//var arrData = JSONData;

		var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;

		var CSV = '';
		//var CSV = 'sep=,' + '\r\n\n';

		//This condition will generate the Label/Header
		if (ShowLabel) {
			var row = "";

			//This loop will extract the label from 1st index of on array
			for (var index in arrData[0]) {
				//remove underscore from the string and capitalize first letter
				var label = index.replace(/(?:_| |\b)(\w)/g, function($1){return $1.toUpperCase().replace('_',' ');});

				//Now convert each value to string and comma-seprated
				row += label + ',';

				//row += index + ',';
			}

			row = row.slice(0, -1);

			//append Label row with line break
			CSV += row + '\r\n';
		}

		//1st loop is to extract each row
		for (var i = 0; i < arrData.length; i++) {
			var row = "";

			//2nd loop will extract each column and convert it in string comma-seprated
			for (var index in arrData[i]) {
				row += '"' + arrData[i][index] + '",';
			}

			row.slice(0, row.length - 1);

			//add a line break after each row
			CSV += row + '\r\n';
		}

		if (CSV == '') {
			alert("Invalid data");
			return;
		}

		//Generate a file name, this will remove the blank-spaces from the title and replace it with an underscore
		var fileName = ReportTitle.replace(/ /g, "_");

		//Initialize file format you want csv or xls
		var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);

		// Now the little tricky part.
		// you can use either>> window.open(uri);
		// but this will not work in some browsers
		// or you will not get the correct file extension

		//this trick will generate a temp <a /> tag
		var link = document.createElement("a");
		link.href = uri;

		//set the visibility hidden so it will not effect on your web-layout
		link.style = "visibility:hidden";
		link.download = fileName + ".csv";

		//this part will append the anchor tag and remove it after automatic click
		document.body.appendChild(link);
		link.click();
		document.body.removeChild(link);
	}

	this.isNumber = function (evt) {
		evt = (evt) ? evt : window.event;

		let charCode = (evt.which) ? evt.which : evt.keyCode;

		if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
		  evt.preventDefault();
		} else {
		  return true;
		}
	}

	this.activePrevNextTab = function (action = '', id) {
		if(action == 'button') {
			$('.screen-stepper-step a').removeClass('active');
			$('.screen-group-tab .tab-content .tab-pane').removeClass('active in');

			$('.screen-stepper-step').find('[data-id="' + id + '"]').addClass('active');
			$('#New_screen_'+id).addClass('active in');
		}
	}

	this.uploadDocumentFileFragment  = function (object, fileName, initResponse, offset, chunk) {
		var me = this;

		$.ajax({
            type: 'POST',
            cache: false,
            url: this.getDocumentUploadFragmentUrl,
            data: {pk: initResponse['file_container_pk'], file_name: fileName, offset: offset, chunk: chunk},
            success: function (data) {
                //alert.hide();

                if (data.status == 'completed') {
                    me.uploadDocumentFileFragment(object, fileName, initResponse, data.response.offset, data.response.chunk);
                } else if (data.status == 'success') {
                    me.uploadDocumentFileFinish(object, initResponse, fileName);
                } else if (data.status == 'error') {
                   // alert.show();
                    //alertMessageArea.html(data.message);
                    //t.removeClass('is-active').prop('disabled', false);
                }
            },
            error: function (data) {
                //alert.show();
                //alertMessageArea.html(data.responseJSON.message);
                //uploadToServerButton.removeClass('is-active').prop('disabled', false);
            }
        });
	};

	this.uploadDocumentFileFinish = function (object, initResponse) {
        var me = this;

        $.ajax({
            type: 'POST',
            cache: false,
            url: this.getDocumentFinishUploadUrl,
            data: {pk: initResponse['file_container_pk']},
            success: function (data) {
                if (data.status == 'success') {
					$('#document-modal').find('#edit-document-success-message-div').show();
					$('#document-modal').find('#edit-document-failed-message-div').hide();

					var id = $('#document-modal').find('.show-deleted-document-icon').attr('id');
					var id_split = id.split('_');
					var final_id = id_split[1];

					var kps = $('#'+final_id).data('document-kp');

					//console.log(id);

					$.ajax({
						type: 'POST',
						cache: false,
						url: me.getDocumentListUrl,
						data: {'kp' : kps},
						success: function (response) {
							//console.log(response);

							if (response.length) {
								$('#document-modal').find('.document-post-form-div').html('');

								$('#document-modal').find('#document-cnt').val(response.length - 1);

								$(response).each(function (index, item) {
									console.log(item);

									var document_category = $('#'+final_id).data('document-category');

									var options = '<option value="">-- Select category --</option>';

									if(document_category.length) {
										$(document_category).each(function (index1, item1) {
											if(item1 == item.document_category)
												options += '<option value="'+item1+'" selected>'+item1+'</option>';
											else
												options += '<option value="'+item1+'">'+item1+'</option>';
										});
									}

									var id = index,
									wrapper = $('<div />', {
										'class': 'row'
									}).append($('<input />', {
										'type': 'hidden',
										'name': 'document_id[]',
										'id': 'document_id_'+index,
										'value': item.id
									})),
									document_category = $('<div />', {
										'class': 'col-sm-2'
									}).append($('<select />', {
										'class': 'form-control common-document-category-class',
										'name': 'document_category[]',
										'id': 'document_category_'+id,
										'data-id': id
										//'readonly': true
									}).append(options)
									),
									description = $('<div />', {
										'class': 'col-sm-3 form-group'
									}).append($('<input />', {
										'type': 'text',
										'name': 'description[]',
										'id': 'document_description_'+id,
										'class': 'form-control',
										'value': item.Description
									})),
									file_name = $('<div />', {
										'class': 'col-sm-6 form-group'
									}).append($('<span />', {
										'id': 'file_name_'+id,
										'text': item.original_file_name,
										'data-toggle': 'tooltip',
										'title': 'FileSize: '+item.original_file_size+', CreatedBy: '+item.CreatedBy+', CreatedDate: '+item.CreatedDate
									})),
									/*file_size = $('<div />', {
										'class': 'col-sm-1 form-group'
									}).append($('<span />', {
										'id': 'file_size_'+id,
										'text': item.original_file_size
									})),
									created_by = $('<div />', {
										'class': 'col-sm-2 form-group'
									}).append($('<span />', {
										'id': 'created_by_'+id,
										'text': item.CreatedBy
									})),
									created_date = $('<div />', {
										'class': 'col-sm-2 form-group'
									}).append($('<span />', {
										'id': 'created_date_'+id,
										'text': item.CreatedDate
									})),*/
									remove_icon = $('<div />', {
										'class': 'col-sm-1 form-group'
									}).append($('<span />', {
										'id': item.id,
										'class': 'glyphicon glyphicon-remove remove-document-icon',
										'data-id': item.id
									}));

									wrapper.append(document_category).append(description).append(file_name).append(remove_icon);//wrapper.append(document_category).append(file_name).append(description).append(file_size).append(created_by).append(created_date).append(remove_icon);

									$('#document-modal').find('.document-post-form-div').append(wrapper);
								}); 
							}
						}
					});
                } else if (data.status == 'error') {
                    $('#document-modal').find('#edit-document-success-message-div').hide();
					$('#document-modal').find('#edit-document-failed-message-div').html(data.message).show();
                }
            },
            error: function (data) {
                $('.info-place span').addClass('danger').html(data.responseJSON.message);
                object.removeClass('is-active');
            }
        });
    };

    this.uploadDocumentSuccessHelper = function (object, pk) {
        var progress = object.prev('.dropzone').find('.dz-progress');

        progress.css('opacity', 0);
        object.removeClass('is-active').addClass('is-completed').prop('disabled', true);
        object.parent().find('input[type="hidden"]').val(pk);

        $('.info-place span').removeClass('danger').html('File has been uploaded to API server');
    };

    this.triggerChangeTask = function (taskKey) {
        let url,
            me = this,
            taskBlock = $('.workflow-task-block');

        if (this.activeMode == 'edit' && ($('.workflow-task-item [name="TaskId"]:checked').length > 0) && taskBlock.hasClass('active')) {
            url = this.saveWorkflowTaskUrl;
        } else if (this.activeMode == 'insert') {
            url = this.createWorkflowTaskUrl;
            if (!taskKey) {
                this.addErrorMessageToArea("Task can\'t be created");
            }
        }

        if (url) {
            let activeStep = $('.screen-tab.btn.active').data('flowStepId');

            this.addMessageToArea('Updating task');
            $.ajax({
                type: 'POST',
                cache: false,
                url: url,
                data: taskBlock.serialize() +
                      (taskKey ? '&TaskKey=' + encodeURIComponent(JSON.stringify(taskKey)) : ''),
                success: function (response) {
                    if (response.status === 'success') {
                        me.updateWorkflowIconContainer($('.screen-tab.btn.active').data('flowStepId'), taskKey ? JSON.stringify(taskKey) : sessionStorage['active-id-' + me.selectedLib]);
                    } else {
                        me.addErrorMessageToArea(response.message);
                    }
                }
            });
        }
    };

    this.renderReportModal = function (config, useBatch) {
        let me = this,
            inputWrapper = $('<div />', {class: 'search-input-wrapper form-control', style: 'width: 100%'}),
            modalBody = $('#generate-report-modal').find('.modal-body'),
            inputs = [],
            button = $('<button />', {
                'text': 'Generate report',
                'class': 'btn btn-primary',
                'data-report-id': config.id
            }),
            renderParamSelect = config.batchQuery && $("" +
                "<select class='pull-right report-mode-select' style='margin: 30px 0 0 0;'>" +
                    "<option value='search'>Search</option>" +
                    "<option value='batch' " + (useBatch && 'selected') + ">Batch</option>" +
                "</select>"
            ).change(function (event) {
                me.renderReportModal(config, event.target.value === 'batch');
            });

        if (!useBatch) {
            if (config.multiSearch) {
                inputs = config.multiSearch.query_params.map(function (item) {
                    return item.name
                });
            } else if (config.simpleSearch) {
                inputs = config.simpleSearch.func_inparam_configuration;
            } else {
                return modalBody.html('<div class="alert alert-danger">Report template configured incorrectly</div>');
            }
        }

        modalBody.html("<h2 class='pull-left'>Report template: " + config.name + "</h2>");
        modalBody.append(renderParamSelect);
        modalBody.append("<div class='clearfix'></div>");

        if (useBatch) {
            modalBody.append("<h3>Batch</h3>");
            if (config.batchQuery.query_params) {
                config.batchQuery.query_params.split(';').map(function (param) {
                    let input = $('<input />', {
                        name: 'batch[' + param + ']',
                        class: 'batch-field form-control',
                        placeholder: param
                    });
                    modalBody.append(input)
                });
            }
        } else {
            modalBody.append("<h3>Select record</h3>");
            modalBody.append(inputWrapper);
        }

        modalBody.append("<br /><br />");
        modalBody.append(button);

        if (!useBatch) {
            inputs.forEach(function (item) {
                let classList = 'report-template-search',
                    input = $('<input />', {name: item, class: classList + ' search-field', placeholder: item});

                inputWrapper.append(
                    $('<div />', {class: 'search-input-inner-wrapper'}).append(
                        input
                    )
                );

                input.typeahead(null, {
                    datumTokenizer: Bloodhound.tokenizers.obj.whitespace(item),
                    display: item,
                    limit: 15,
                    source: function (query, syncResults, asyncResults) {
                        setTimeout(function () {
                            if (query == input.val()) {
                                let queries = $('.' + classList).serializeArray();
                                $.ajax({
                                    type: 'POST',
                                    url: me.searchReportUrl,
                                    data: {
                                        queries: queries,
                                        simpleSearch: config.simpleSearch,
                                        multiSearch: config.multiSearch
                                    },
                                    success: function (data) {
                                        asyncResults(data);
                                    }
                                });
                            }
                        }, 1000);
                    },
                    templates: {
                        notFound: '<div class="text-danger">No search result</div>',
                        header: Handlebars.compile('<div class="tt-dataset-header">' + inputs.map(function (inputItem) {return '<div class="row-item">' + inputItem + '</div>'}).join('') + '</div>'),
                        suggestion: Handlebars.compile('<div>' + inputs.map(function (inputItem) {return '<div class="row-item">{{' + inputItem + '}}</div>'}).join('') + '</div>')
                    }
                });

                input.bind('typeahead:select', function(ev, suggestion) {
                    if (suggestion) {
                        button.data('searchResult', suggestion);
                        Object.keys(suggestion).forEach(function (key) {
                            $('.' + classList).filter('[name="' + key + '"]').val(suggestion[key]);
                        });
                    }
                }).bind('typeahead:asyncrequest', function () {
                    $(this).removeClass('loading').addClass('loading');
                }).bind('typeahead:asynccancel typeahead:asyncreceive', function () {
                    $(this).removeClass('loading');
                });
            });
        }
    };

    this.setRelationDataBlockInfo = function () {
		console.log('in setRelationDataBlockInfo');

        var me = this;
        $('[data-relation-field--block]').each(function () {
            var t = $(this),
                data = t.data(),
                queries = [{name: 'value', value: t.text()}];

            if (!data['customQuery']) {
                return true;
            }

            t.text('Loading ...');
            me.inlineSearchResults(data['customQuery'], queries, function (result) {
                if ($.isArray(result) && result.length > 0 && result[0]['description']) {
                    var textValue = result[0]['description'];
                    if (data && data['initValue']) {
                        var selectedData = result.find(function (item) {
                            return String(item.value) === String(data['initValue'])
                        });
                        if (selectedData) {
                            textValue = selectedData['description'];
                        }
                    }
                    t.text(textValue);
                } else {
                    t.text(data['initValue']);
                    $('.info-place span').addClass('danger').html('Relation data for "' + t.attr('name') + '" not found');
                }
            });
        });
    };

    /**
     * Append info alert in modal.
     * @param {string} modalBody - Selector
     * @param {string|boolean} [type] - Type of bootstrap alert class
     * @param {string} [text]
     * @param {boolean} [isHideBody] - Set TRUE if you want remove body of modal
     */
    this.setInfo = function (modalBody, type, text, isHideBody) {
        $(modalBody).children().show();
        $(modalBody).find('.alert').remove();

        if (type && text) {
            var alertObject = $('<div/>', {"class": 'alert alert-' + type, text: text});

            if (isHideBody) {
                $(modalBody).children().hide();
            }
            alertObject.appendTo($(modalBody));
        }
    };

    this.uploadFileFragment = function (object, fileName, initResponse, offset, chunk) {
        var me = this,
            progressObject = object.prev('.dropzone').find('.dz-progress'),
            present;

        $.ajax({
            type: 'POST',
            cache: false,
            url: this.uploadFragmentUrl,
            data: {pk: initResponse['file_container_pk'], file_name: fileName, offset: offset, chunk: chunk},
            success: function (data) {
                if (data.status == 'completed') {
                    present = Math.round(data.response.offset * 100 / parseInt(data.response.size));
                    present = (present > 100) ? 100 : present;
                    progressObject.css('opacity', 1).find('.dz-upload').css('width', present + '%');

                    me.uploadFileFragment(object, fileName, initResponse, data.response.offset, data.response.chunk);
                } else if (data.status == 'success') {
                    me.uploadFileFinish(object, initResponse);
                } else if (data.status == 'error') {
                    $('.info-place span').addClass('danger').html(data.message);
                    object.removeClass('is-active');
                }
            },
            error: function (data) {
                $('.info-place span').addClass('danger').html(data.responseJSON.message);
                object.removeClass('is-active');
            }
        });
    };

    this.downloadFileFragment = function (object='', pk, initResponse, offset) {
		console.log('in this.downloadFileFragment');

        var me = this,
            present;

        $.ajax({
            type: 'GET',
            cache: false,
            url: this.downloadFragmentUrl,
            data: {pk: pk, file_name: initResponse.name, offset: offset},
            success: function (data) {
                if (data.status == 'completed') {
                    present = Math.round(data.response.offset * 100 / parseInt(initResponse.size));
                    present = (present > 100) ? 100 : present;

					if(object != '')
						object.find('.progress-inner').css('width', present + '%');

                    me.downloadFileFragment(object, pk, initResponse, data.response.offset);
                } else if (data.status == 'success') {
                    me.downloadFileFinish(object, initResponse);
                } else if (data.status == 'error') {
                    $('.info-place span').addClass('danger').html(data.message);

					if(object != '')
						object.removeClass('is-active');
                }
            },
            error: function (data) {
                $('.info-place span').addClass('danger').html(data.responseJSON.message);

				if(object != '')
					object.removeClass('is-active');
            }
        });
    };

    this.uploadFileFinish = function (object, initResponse) {
        var me = this;

        $.ajax({
            type: 'POST',
            cache: false,
            url: this.uploadFinishUrl,
            data: {pk: initResponse['file_container_pk']},
            success: function (data) {
                if (data.status == 'success') {
                    me.uploadSuccessHelper(object, initResponse['file_container_pk']);
                } else if (data.status == 'error') {
                    $('.info-place span').addClass('danger').html(data.message);
                    object.removeClass('is-active');
                }
            },
            error: function (data) {
                $('.info-place span').addClass('danger').html(data.responseJSON.message);
                object.removeClass('is-active');
            }
        });
    };

    this.downloadFileFinish = function (object='', initResponse) {
        var me = this;

        $.ajax({
            type: 'GET',
            cache: false,
            url: this.downloadFinishUrl,
            data: {file_name: initResponse.name, file_size: initResponse.size, file_hash: initResponse.hash_hex},
            success: function (data) {
                if (data.status == 'success') {
                    me.downloadSuccessHelper(object, initResponse.original_name, data.response.url);
                } else if (data.status == 'error') {
                    $('.info-place span').addClass('danger').html(data.message);

					if(object != '')
						object.removeClass('is-active');
                }
            },
            error: function (data) {
                $('.info-place span').addClass('danger').html(data.responseJSON.message);

				if(object != '')
					object.removeClass('is-active');
            }
        });
    };

    this.downloadSuccessHelper = function (object='', originalName, url) {
		var win = window.open(url, '_blank');
		win.focus();

		if(object != '') {
			var relatedFrame = object.attr('data-related-frame-class'),
				frameObject;

			//object.removeClass('is-active').addClass('is-cached');
			//object.find('.container-text-inner').text('Download file');
			object.attr('href', url);
			object.attr('download', originalName);
			//object.trigger('click');

			if (relatedFrame) {
				frameObject = $('.' + relatedFrame);
				frameObject.attr('src', frameObject.attr('data-src'));
				frameObject.show();

				object.hide();
			}
		}

        $('.info-place span').removeClass('danger').html('File has been downloaded from API server');
    };

    this.uploadSuccessHelper = function (object, pk) {
        var progress = object.prev('.dropzone').find('.dz-progress');

        progress.css('opacity', 0);
        object.removeClass('is-active').addClass('is-completed').prop('disabled', true);
        object.parent().find('input[type="hidden"]').val(pk);

        $('.info-place span').removeClass('danger').html('File has been uploaded to API server');
    };

    /**
     * @param {jQuery} panelObject
     */
    this.detachPanel = function (panelObject) {
        if (this.editOnly) {
            panelObject.find('.btn-wrap').show();
        } else {
            var height = panelObject.height(),
                width = panelObject.width();
            panelObject.removeClass('active-panel').addClass('detach')
                .draggable({
                    handle: '.panel-heading',
                    stack: '.stats-section .panel'
                })
                .resizable({
                    handles: 'all',
                    minWidth: width,
                    minHeight: height
                })
                .css({
                    width: width,
                    height: height
                });

            panelObject.find('.panel-body').css({
                height: 'inherit'
            });
        }
        this.activatePanel(panelObject);
    };

    /**
     * Focus last detach panel
     * @param {jQuery} panelObject
     */
    this.activatePanel = function (panelObject) {
        this.focusOnPanel(panelObject);
    };

    this.focusOnPanel = function (panelObject) {
        $('.panel-window.detach.ui-draggable').removeClass('active-panel');
        panelObject.addClass('active-panel');
    };

    /**
     * @param {jQuery} panelObject
     */
    this.attachPanel = function (panelObject) {
        if (this.editOnly) {
            panelObject.find('.btn-wrap').hide();
        } else {
            panelObject.removeClass('detach')
                .draggable('destroy')
                .resizable('destroy')
                .css({
                    width: '100%',
                    height: 'auto'
                });
        }
    };

    /**
     * Add data to table after change page.
     * @param {Object} data - Returned data after getting from API server
     * @param {jQuery} table - Object of table
     * @param {number} page - Number of page
     */
	this.addDataToTable = function (data, table, page) {
		console.log('in this.addDataToTable');

        var me = this,
            isLeftOrientation = table.attr('data-top-orientation') == '0',
            newData = data;

        table.find('tbody').html('');

        if ((data['-1'])) {
            newData = [data[-1]];

            $.each(data, function (i, item) {
                if (i != '-1') {
                    newData.push(item);
                }
            });
        }

        if (page > 1) {
            if (isLeftOrientation) {
                $.each(newData, function (i, item) {
                    delete newData[i]['new'];
                });
            } else {
                delete data['-1'];
                newData = data;
            }
        }

        $.each(newData, function (i, item) {
            var tr = $('<tr />'),
                j = 1;
            table.find('th').each(function () {
                var th = $(this).text();
                $.each(item, function (thName, tdValue) {
                    if (th.toLowerCase() == thName.toLowerCase()) {
                        var td = $('<td />', {html: tdValue});

                        if (isLeftOrientation && j == 1) {
                            td.css({
                                'border-right': '2px solid #ddd',
                                'white-space': 'nowrap'
                            });
                        }

                        tr.append(td);
                    }
                });
                j++;
            });
            table.find('tbody').append(tr);
        });

        if (page == 1) {
            $.each(me.subData.insert, function (key, value) {
                $.each(value, function (i, item) {
                    table.find('[data-sub-id="-1"]').each(function () {
                        if ($(this).attr('name') == item.name) {
                            $(this).val(item.value);
                        }
                    });
                });
                delete me.subData.insert[key];
                table.find('.add-sub-item ').trigger('click');
            });
        }

        table.find('.form-control-grid').each(function () {
            var t = $(this),
                subId = t.attr('data-sub-id');
            if (me.subData.update[subId]) {
                $.each(me.subData.update[subId], function (i, item) {
                    if (item.name == t.attr('name')) {
                        t.val(item.value);
                    }
                });
            }

            if (me.subData.delete[subId]) {
                t.parents('td').css('background-color', 'rgb(255, 222, 222)');
                t.prop('disabled', true);
            }
        });

        table.find('.remove-sub-item').each(function () {
            var t = $(this);
            if (me.subData.delete[t.attr('data-id')]) {
                t.parents('td').css('background-color', 'rgb(255, 222, 222)');
                t.removeClass('glyphicon-trash');
                t.addClass('glyphicon-pushpin');
                t.removeClass('remove-sub-item');
                t.addClass('return-sub-item');
            }
        });

        table.find('input').each(function () {
            var dateTimePickerHash = $(this).attr('data-krajee-datetimepicker');
            var datePickerHash = $(this).attr('data-krajee-kvdatepicker');
            if (dateTimePickerHash) {
                $(this).datetimepicker(window[dateTimePickerHash]);
            } else if (datePickerHash) {
                $(this).kvDatepicker('destroy');
                $(this).parent().kvDatepicker(window[datePickerHash]);
                initDPAddon($(this).attr('id'));
            }
        });
    };

    /**
     * Getting data for table, after change page. After getting successfully result run callback function
     * @param {number} page - Number of page
     * @param {number} row
     * @param {number} col
     * @param {number} tid - Parent ID of sub data
     * @param {string} mode - Edit or insert
     * @param {function} callback
     */
    this.loadSubData = function (page, row, col, tid, mode, callback) {
        var me = this;
        $.ajax({
            type: 'POST',
            cache: false,
            url: me.getSubDataUrl,
            data: {
                lib: me.selectedLib,
                id: JSON.parse(sessionStorage['active-id-' + me.selectedLib]),
                page: page,
                row: row,
                col: col,
                activeTab: tid,
                mode: mode
            },
            success: function (response) {
                callback(true, response);
            },
            error: function () {
                callback(false);
            }
        });
    };

    this.addErrorMessageToArea = function (message) {
        $('.info-place span').addClass('danger').html(message);
    };

    this.addMessageToArea = function (message) {
        $('.info-place span').removeClass('danger').html(message);
    };

    this.getErrorMessageI18N = function (message) {
        try {
            var errorMessageObj = JSON.parse(message);
        } catch(e) {
            console.log('Error message without I18N');
        }

        if (errorMessageObj instanceof Object) {
            var userLanguage = document.documentElement.lang;
            if (errorMessageObj[userLanguage]) {
                message = errorMessageObj[userLanguage];
            } else if (errorMessageObj['en-us']) {
                message = errorMessageObj['en-us'];
            } else {
                message = errorMessageObj[Object.keys(errorMessageObj)[1]];
            }
        }

        return message;
    };

    this.getErrorModalType = function (errorSettings) {
        try {
            errorSettings = JSON.parse(errorSettings);
        } catch(e) {
            // console.log('Error modal type is empty');
        }

        if (errorSettings instanceof Object && errorSettings['modal-type']) {
            return errorSettings['modal-type'] == 'confirm' ? common.showConfirm : common.showAlert;
        }

        return null;
    };

    this.showAlert = function (errorMessage, elObj) {
		if(errorMessage != '' && errorMessage != null)
			alert(errorMessage);
    };

    this.showConfirm = function (errorMessage, elObj) {
        if(skipErrors[errorMessage] === undefined) {
            bootbox.confirm({
                message: errorMessage,
                buttons: {
                    confirm: {
                        label: "Continue",
                        className: "btn-success"
                    },
                    cancel: {
                        label: "Cancel",
                        className: "btn-danger"
                    }
                },
                callback: function (result) {
                    if (result) {
                        elObj.removeClass("not-valid-data");
                        elObj[0].setCustomValidity('');

                        skipErrors[errorMessage] = true;
                    }
                }
            });
        } else {
            elObj.removeClass("not-valid-data");
            elObj[0].setCustomValidity('');
        }
    };

    this.customJsException = function (message) {
        message = common.getErrorMessageI18N(message);
        $('.info-place span').addClass('danger').html(message);
        throw new Error(message);
    };

    this.showMessagePool = function (response) {
        var messages = response.messagePool,
            messagePool = $('<ul />');

        if (typeof messages === 'object') {
            $.each(messages, function(i, item) {
                messagePool.append($('<li />', {text: item}));
            });

            $('.message-pool').html(messagePool);
            $('.message-pool-link').show();
        }
    };

    this.addToMessagePool = function (messages) {
        messages.map(function (item) {
            $('.message-pool').append($('<li />', {text: item}));
        });
        $('.message-pool-link').show();
    };

    this.clearResultApi = function () {
        $('.message-pool-link').hide();
        $('.message-pool').html('').hide();
        $('.message-pool-link').addClass('is-hide').html($('.message-pool-link').attr('data-show-text'));
    };

	this.getDataByKeyFields = function (queries) {
		console.log('in this.getDataByKeyFields');

        var me = this,
            tabId = $('.screen-tab.btn[data-lib="' + me.selectedLib + '"]').data('tab-id');

        me.addMessageToArea('Searching data by key fields...');
        $.ajax({
            type: 'POST',
            cache: false,
            url: me.LoadUrl,
            data: {
                library: me.selectedLib,
                queries: queries,
                activeTab: tabId,
                aliasFrameworkInfo: me.aliasFrameworkInfo
            },
            success: function (response) {
				console.log('this.getDataByKeyFields success response');
				console.log(response);

                me.showMessagePool(response);
                if (response.errorMessage) {
                    me.addErrorMessageToArea(response.errorMessage);
                } else if (response.list && (response.list.length > 0)) {
					console.log(response.list[0].id);

                    me.addMessageToArea('Search executed');
                    me.setActiveId(response.list[0].id, response.list[0]);
                } else {
                    me.addErrorMessageToArea('Nothing found');
                }
            },
            error: function () {
                me.addErrorMessageToArea('Error during search');
            }
        });
    };

    /**
     * Main method with events and CRUD functionality
     */
    this.init = function () {
        var me = this,
            PrimaryTableData = $('.screen-tab.btn.active').data('alias-framework');

        PrimaryTableData = (PrimaryTableData) ? PrimaryTableData : null;
        me.aliasFrameworkInfo = {
            enable: PrimaryTableData != null,
            request_primary_table: PrimaryTableData
        };

        $('.screen-tab.btn').click(function (e) {
            var t = $(this);
			var id = $(this).attr('data-tab-id'); 
            var selectedLib = t.data('lib'),
                PrimaryTableData = t.data('alias-framework');

            if (selectedLib !== me.selectedLib && (me.activeMode === 'insert' || me.activeMode === 'edit' || me.activeMode === 'copy')) {
                if (!confirm('Are you sure want to exit from ' + me.activeMode + ' mode?')) return false;
                else {
                    var mode = (me.activeMode === 'insert') ? 'empty' : 'unlock';
                    $('.nav-left-group a[data-mode="' + mode + '"]').trigger('click');
                }
            }

            $('.screen-tab.btn.active').removeClass('active');
            t.addClass('active');
            me.selectedLib = selectedLib;
            $('.search-group.active').removeClass('active');
            $('.search-group[data-srch-lib="' + selectedLib + '"]').addClass('active');
            if (!sessionStorage['active-id-' + me.selectedLib]) {
                $('.special-btns.active').removeClass('active');
            }
            else {
                if (!$('.special-btns').hasClass('active')) {
                    $('.special-btns').addClass('active');
                }
                me.checkAvailableBtnLoad();
            }

            PrimaryTableData = (PrimaryTableData) ? PrimaryTableData : null;
            me.aliasFrameworkInfo = {
                enable: PrimaryTableData != null,
                request_primary_table: PrimaryTableData
            };

			//console.log("sessionStorage['active-id-' + me.selectedLib]");
			//console.log(sessionStorage['active-id-' + me.selectedLib]);

            common.updateWorkflowIconContainer(t.data('flowStepId'), sessionStorage['active-id-' + me.selectedLib]);

			//alert($('#screen_step_'+id).val());

			if($('#screen_step_'+id).val() != '' && $('#screen_step_'+id).val() == 'insert' && $('#screen_step_'+id).val() != me.activeMode) {
				$('.nav-left-group').find('.screen-insert-btn').click();
			}

			if($('#login_screen_'+id).val() == 1) {
				$('.nav-left-group').find('.screen-insert-btn').click();
			}
        });

        $('.screen-edit-btn, .screen-copy-btn, .screen-insert-btn').click(function (e) {
            $('.nav-left-group a').not('.left-navigation-button-save, .left-navigation-button-cancel').addClass('disabled');
			$('.search-field').prop('disabled', true);
			$('#document-modal').find('#view-document-list-tbl').hide();
			$('#document-modal').find('.edit-document-modal-div').hide();
        });

        $('.left-navigation-button-save, .left-navigation-button-cancel').click(function () {
            $('.nav-left-group a').removeClass('disabled');
			$('.search-field').prop('disabled', false);
			$('#document-modal').find('#view-document-list-tbl').hide();
			$('#document-modal').find('.edit-document-modal-div').hide();
			$('.loading-circle').hide();
        });

		//Click on left navigation button
		$('.nav-left-group button.left-navbar-icon').click(function (e) {
			//alert('hi');

			setTimeout(function() {
				$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
			}, 2000);
		});

        //Click on left navigation links
		$('.nav-left-group a').click(function (e) {
			console.log('.nav-left-group a click');

            me.clearResultApi();
            var newMode = $(this).data('mode');
            e.preventDefault();

            if (me.activeMode === 'edit' || me.activeMode === 'insert' || me.activeMode === 'copy') {
                if ($(this).parents('.special-sub-btns-' + me.activeMode).length == 0 && !$(this).hasClass('navigation-btn')) {
                    if (!confirm('Are you sure want to exit from ' + me.activeMode + ' mode?')) return false;
                }
            }

            if (newMode) {
                if (newMode === 'edit') {
                    me.setLockedActiveId(true);
                } else if (newMode === 'key') {
                    if (!(me.activeMode === 'edit' || me.activeMode === 'insert')) {
                        var formControlKeyObject = $('.tab-content [data-section-lib="' + me.selectedLib + '"] .form-control.form-control-key'),
                            serializeKeyArray = formControlKeyObject.serializeArrayWithData();
                        formControlKeyObject.each(function () {
                            var t = $(this);
                            if (t.hasClass('currency-input') || t.hasClass('decimal-input')) {
                                $.each(serializeKeyArray, function (i, item) {
                                    if (item.name == t.attr('name')) {
                                        serializeKeyArray[i]['value'] = t.maskMoney('unmasked')[0];
                                    }
                                });
                            }
                        });
                        me.getDataByKeyFields(serializeKeyArray);
                    }
                } else {
                    $('.special-sub-btns.active').removeClass('active');
                    if ($('.special-sub-btns.special-sub-btns-' + newMode).length) {
                        $('.special-sub-btns.special-sub-btns-' + newMode).addClass('active');
                    }

                    if (newMode === 'insert') {
                        sessionStorage.removeItem('active-id-' + me.selectedLib);
                        $('.search-group.active input.form-control.tt-hint, .search-group.active input.form-control.tt-input').val('');
                        $('.special-btns.active').removeClass('active');

                        me.addMessageToArea('Insert mode was selected');
                        $('.info-place span').removeClass('danger').html('Insert mode was selected');
                    }
                    if (newMode === 'copy') {
                        $('.special-sub-btns.active').removeClass('active');
                        if ($('.special-sub-btns.special-sub-btns-' + newMode).length) {
                            $('.special-sub-btns.special-sub-btns-' + newMode).addClass('active');
                        }
                        $('.info-place span').removeClass('danger').html('Copy mode was selected');
                    } else if (newMode === 'empty') {
                        newMode = null;
                        $('.workflow-task-block').removeClass('active').hide();
                        $('.workflow-task-btn').hide();
                        me.addErrorMessageToArea('Insert mode was canceled');
                    } else if (newMode === 'unlock') {
                        newMode = null;
                        me.setLockedActiveId(false);
                        me.subData = {
                            subIdStart: 0,
                            insert: {},
                            update: {},
                            delete: {}
                        };
                    }

					//if(newMode != 'execute')
						me.changeMode(newMode);
                }
            }

            //clicked left navigation button
            var createTabId = $('.screen-tab.btn[data-lib="' + me.selectedLib + '"]').map(function () {return $(this).data('tab-id')}).toArray();
            var ActiveTabId = $('.screen-tab.btn.active').data('tab-id');
            if ($(this).attr('data-action')) {
                if (me.activeMode === 'insert' || me.activeMode === 'edit' ||  me.activeMode === 'copy') {
                    var inputsSelector = $('.tab-content [data-section-lib="' + me.selectedLib + '"]').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled),keygen').not('.form-control-grid'),
                        customButtonSelector = $('.screen-btn-custom-action');
                    try {
						//alert('js_event_'+me.activeMode);

                        inputsSelector.trigger('js_event_'+me.activeMode);
                        customButtonSelector.trigger('js_event_'+me.activeMode);
                    } catch (e) {
                        me.customJsException(e.message);
                    }

                    if (me.activeMode === 'copy') {
                        $('.tab-content [data-section-lib="' + me.selectedLib + '"]').find(':input:disabled').removeAttr('disabled');
                    }

                    $('.currency-input, .decimal-input').each(function () {
                        $(this).val($(this).maskMoney('unmasked')[0]);
                    });

                    var validationErrors = false;
                    inputsSelector.filter('input, select, textarea').each(function (k, v) {
                        if (this.checkValidity()) {
                            $(this).removeClass("not-valid-data");
                            if ($(this).hasClass('select-picker')) {
                                $(this).parent().find('div.select-picker button').removeClass('not-valid-data');
                            }
                        } else {
                            $(this).addClass("not-valid-data");
                            if ($(this).hasClass('select-picker')) {
                                $(this).parent().find('div.select-picker button').addClass('not-valid-data');
                            }

                            this.reportValidity();
                            me.addErrorMessageToArea('Validation error');
                            validationErrors = true;
                        }
                    });

                    if (validationErrors) return false;

                    var serializeArray = inputsSelector.serializeArrayWithData();

                    if (me.aliasFrameworkInfo && me.aliasFrameworkInfo['enable']) {
                        $('.tab-content [data-section-lib="' + me.selectedLib + '"] input.form-control-grid').trigger('change');
                    }

                    //send edit or insert data to server API
                    me.addMessageToArea('Sending ' + me.activeMode + 'ed data...');
                    $.ajax({
                        type: 'POST',
                        cache: false,
                        url: $(this).data('action'),
                        data: {
                            lib: me.selectedLib,
                            data: serializeArray,
                            subData: me.subData,
                            id: ((me.activeMode === 'insert' || me.activeMode === 'copy') ? null : JSON.parse(sessionStorage['active-id-' + me.selectedLib])),
                            activeTab: ActiveTabId,
                            tabList: createTabId,
                            pkForAliasFramework: ((me.activeMode === 'insert' || me.activeMode === 'copy') ? null : me.lastGetDataPK),
                            aliasFrameworkInfo: me.aliasFrameworkInfo
                        },
                        success: function (response) {
							console.log('create API response');
							console.log(response);

                            me.showMessagePool(response);

                            if (response.status === 'success') {
                                me.addMessageToArea(response.message);
                                $('.nav-left-group a[data-mode="' + me.activeMode + '"]').removeClass('active');
                                me.subData = {
                                    subIdStart: 0,
                                    insert: {},
                                    update: {},
                                    delete: {}
                                };

                                me.triggerChangeTask(response.id);

                                me.activeMode = null;
                                me.setActiveId(response.id);

                                $('.special-sub-btns.active').removeClass('active');
                            }
                            else {
                                me.addErrorMessageToArea(response.message);
                            }
                        }
                    });
                } else { //Delete tab data
                    if (!confirm('Are you sure want to delete this record?')) return false;

                    var dataSourceGet = null,
                        dataSourceDelete = null;
                    $('.stats-section').find('.panel.panel-default.panel-window').each(function () {
                        dataSourceGet = $(this).attr('data-source-get');
                        if (!dataSourceDelete) {
                            dataSourceDelete = $(this).attr('data-source-delete');
                        }
                        return false;
                    });

                    if (sessionStorage['active-id-' + me.selectedLib]) {
                        me.addMessageToArea('Removing data...');
                        $.ajax({
                            type: 'POST',
                            cache: false,
                            url: $(this).data('action'),
                            data: {
                                lib: me.selectedLib,
                                id: JSON.parse(sessionStorage['active-id-' + me.selectedLib]),
                                function: {
                                    delete: dataSourceDelete,
                                    get: dataSourceGet
                                },
                                activeTab: ActiveTabId,
                                tabList: createTabId,
                                pkForAliasFramework: me.lastGetDataPK,
                                aliasFrameworkInfo: me.aliasFrameworkInfo
                            },
                            success: function (response) {
                                me.showMessagePool(response);
                                if (response.status === 'success') {
                                    me.addMessageToArea(response.message);
                                    $('.search-group.active input.form-control.tt-hint, .search-group.active input.form-control.tt-input').val('');
                                    me.activeMode = null;
                                    me.setActiveId(null);
                                    delete sessionStorage['lastFoundData'];
                                    $('.special-btns.active').removeClass('active');
                                } else {
                                    me.addErrorMessageToArea(response.message);
                                }
                            }
                        });
                    }

                }
            } else {
				/*var Id = !sessionStorage['active-id-' + me.selectedLib] ? null : JSON.parse(sessionStorage['active-id-' + me.selectedLib]);
				var screenTabs = $('.screen-tab.btn[data-lib="' + me.selectedLib + '"]');

				if (me.aliasFrameworkInfo.enable) {
					screenTabs = screenTabs.filter('[data-alias-framework="' + me.aliasFrameworkInfo.request_primary_table + '"]');
				} else {
					screenTabs = screenTabs.not('[data-alias-framework]');
				}

				screenTabs = screenTabs.map(function () {
					var tabId = $(this).data('tab-id');

					if (!(me.workflowInfo[tabId] && me.workflowInfo[tabId]['locked'])) {
						return $(this);
					}
				});

				screenTabs.each(function (index) {
					var t = $(this);
					var tabPlace = t.data('target');
					var tabId = t.data('tab-id');

					$.ajax({
						type: 'POST',
						cache: false,
						url: 'http://192.168.100.229/codiac/web/site/execute-data',
						data: {
							id: Id,
							activeTab: tabId
						},
						success: function (response) {
							
						},
						error: function () {
							me.addErrorMessageToArea('Loading error');
						}
					});
				});*/

				var inputsSelector = $('.tab-content [data-section-lib="' + me.selectedLib + '"]').find('input:not(:disabled),select:not(:disabled),textarea:not(:disabled),keygen').not('.form-control-grid');

				$('.currency-input, .decimal-input').each(function () {
					$(this).val($(this).maskMoney('unmasked')[0]);
				});

				var serializeArray = inputsSelector.serializeArrayWithData();

				if (me.aliasFrameworkInfo && me.aliasFrameworkInfo['enable']) {
					$('.tab-content [data-section-lib="' + me.selectedLib + '"] input.form-control-grid').trigger('change');
				}

				//send edit or insert data to server API
				me.addMessageToArea('Executing Data...');

				//alert(me.activeMode);

				$.ajax({
					type: 'POST',
					cache: false,
					url: $(this).data('url'),
					data: {
						lib: me.selectedLib,
						data: serializeArray,
						subData: me.subData,
						id: ((me.activeMode === 'insert' || me.activeMode === 'copy') ? null : JSON.parse(sessionStorage['active-id-' + me.selectedLib])),
						activeTab: ActiveTabId,
						tabList: createTabId,
						pkForAliasFramework: ((me.activeMode === 'insert' || me.activeMode === 'copy') ? null : me.lastGetDataPK),
						aliasFrameworkInfo: me.aliasFrameworkInfo
					},
					success: function (response) {
						me.showMessagePool(response);

						if (response.status === 'success') {
							me.addMessageToArea(response.message);
							me.activeMode = null;
							$('.nav-left-group a[data-mode="' + me.activeMode + '"]').removeClass('active');
							$('.special-sub-btns.active').removeClass('active');
						}
						else {
							me.addErrorMessageToArea(response.message);
						}
					}
				});
			}
            return false;
        });

        //Clicked on 2 level menu
        $('.second-menu-level li a').click(function (e) {
            if (me.activeMode === 'edit' || me.activeMode === 'insert') {
                if ($(this).parent('li').hasClass('active')) {
                    e.preventDefault();
                    return false;
                }

                var isGoToLink = confirm('Are you sure want to exit ' + me.activeMode + ' mode?');
                if (!isGoToLink) e.preventDefault();
            }
        });

        //Next or prev button clicked
        $('.navigation-btn').on('click', function (e) {
			console.log('in .navigation-btn');

            var id = !sessionStorage['active-id-' + me.selectedLib] ? null : sessionStorage['active-id-' + me.selectedLib],
                isFind = false,
                prevID = false,
                nextID = false,
                prevData = '',
                nextData = '',
                searchResult = JSON.parse(sessionStorage['search-res-' + me.selectedLib]);

			console.log('id');
			console.log(id);

			console.log('searchResult');
			console.log(searchResult);

            if (id) {
                $.each(searchResult, function (i, item) {
                    if (isFind) {
                        nextID = item['id'];
                        nextData = item;
                        return false;
                    } else if (JSON.stringify(item['id']) == id) {
                        isFind = true;
                    } else {
                        prevID = item['id'];
                        prevData = item;
                    }
                });
            }

            var isPrev = $(this).hasClass('prev'),
                isNext = !isPrev;

            if (searchResult) {
                if (isPrev === true && prevID === false && searchResult.length) {
                    var prevItem = searchResult[searchResult.length - 1];
                    prevID = prevItem['id'];
                    prevData = prevItem;
                }

                if (isNext === true && nextID === false && searchResult.length) {
                    var nextItem = searchResult[0];
                    nextID = nextItem['id'];
                    nextData = nextItem;
                }
            }

            if (isPrev && prevID) {
                me.setActiveId(prevID, prevData, true);
            } else if (isNext && nextID) {
                me.setActiveId(nextID, nextData, true);
            }
        });

		$('.common-datatable-class').DataTable();
    };

    this.updateWorkflowIconContainer = function (flowStepId, activePK) {
		console.log('in updateWorkflowIconContainer');

        let icon = $('.workflow-task-btn'),
            block = $('.workflow-task-block'),
            loading = block.find('.loading-circle'),
            itemsBlock = $('.workflow-task-item'),
            notificationBlock = $('.workflow-task-notification'),
            descriptionBlock = $('.workflow-task-description');

        if (this.activeMode == 'edit') {
            return false;
        }

        loading.hide();
        if (this.activeMode != 'insert') {
            itemsBlock.hide().html('');
            descriptionBlock.hide();
            icon.hide();
        }

        if (flowStepId && activePK) {
            icon.show();
            loading.show();

            activePK = JSON.parse(activePK);
            notificationBlock.html('').hide();

			var workflow_url = this.getWorkflowJsonUrl;

            $.ajax({
                type: 'GET',
                cache: false,
                url: this.getWorkflowTaskUrl,
                data: {taskKey: activePK, flowStepId: flowStepId},
                success: function (response) {
					console.log('response');
					console.log(response);

                    loading.hide();

                    if (response.length) {
                        itemsBlock.show().html('');

                        //notificationBlock.show().html(response.length);

						notificationBlock.html(response.length);

						var CurrentStepUuid;
						var FlowId;

                        response.forEach(function (item) {
							CurrentStepUuid = item.CurrentStepUuid;
							FlowId = item.FlowId;

							if(item.StepName != 'START') {
								itemsBlock.append(
									'<div class="input-group">' +
										'<div class="input-group-addon">' +
											'<input name="TaskId" type="radio" data-meta=\'' + JSON.stringify(item) + '\' class="workflow-task-item__radio" value="' + item.TaskId + '" />' +
										'</div>' +
										'<input type="text" class="form-control task-description-label-field" disabled value="' + item.TaskDescription + '">' +
									'</div>'
								);
							}
                        });

						//console.log('lastFoundData');
						//console.log(sessionStorage['lastFoundData']);

						$.ajax({
							type: 'POST',
							cache: false,
							url: workflow_url,
							data: {workflow_id: FlowId},
							success: function (response) {
								//console.log('response');
								//console.log(response[0]);

								if (response[0].Steps.length) {
									var field_id = $('#workflow_tracker_diagram_id').val();

									$('.workflow-tracker-diagram-'+field_id).show();
									$('.sub-content-wrapper').css('margin', '22px 40px 20px 40px');

									//console.log('response[0].Steps');
									//console.log(response[0].Steps);

									var steps = response[0].Steps[0];

									steps.forEach(function (item) {
										console.log(item);

										if(item.StepUuid == CurrentStepUuid) {
											console.log(CurrentStepUuid);
											console.log(item.StepType);

											if(item.StepType == 'basic_workflow.BeginStep') {
												$('#status_start_'+field_id).css('border', '0.2em solid green');
												$('#status_start_'+field_id).css('color', 'green');
											} else if(item.StepType == 'basic_workflow.ProcessStep') {
												$('#status_start_'+field_id).css('border', '0.2em solid green');
												$('#status_start_'+field_id).css('color', 'green');
												//$('#status_start_line_'+field_id).css('background-color', 'green');

												$('#status_process_'+field_id).css('border', '0.2em dotted green');
												$('#status_process_'+field_id).css('color', 'green');
											} else if (item.StepType == 'basic_workflow.HoldStep') {
												$('#status_start_'+field_id).css('border', '0.2em solid green');
												$('#status_start_'+field_id).css('color', 'green');
												//$('#status_start_line_'+field_id).css('background-color', 'green');

												$('#status_process_'+field_id).css('border', '0.2em solid green');
												$('#status_process_'+field_id).css('color', 'green');
												//$('#status_process_line_'+field_id).css('background-color', 'green');

												$('#status_hold_'+field_id).css('border', '0.2em solid green');
												$('#status_hold_'+field_id).css('color', 'green');
											} else if(item.StepType == 'basic_workflow.EndStep') {
												
												$('#status_start_'+field_id).css('border', '0.2em solid green');
												$('#status_start_'+field_id).css('color', 'green');
												$('#status_start_line_'+field_id).css('background-color', 'green');

												$('#status_process_'+field_id).css('border', '0.2em solid green');
												$('#status_process_'+field_id).css('color', 'green');
												$('#status_process_line_'+field_id).css('background-color', 'green');

												$('#status_hold_'+field_id).css('border', '0.2em solid green');
												$('#status_hold_'+field_id).css('color', 'green');
												$('#status_hold_line_'+field_id).css('background-color', 'green');

												$('#status_end_'+field_id).css('border', '0.2em solid green');
												$('#status_end_'+field_id).css('color', 'green');
											}
										}
									});
								}
							}
						});
                    } else {
                        itemsBlock.show().html('Has no tasks');
                        block.removeClass('active').hide();
                    }
                }
            });
        }
    };

    this.updateModeWorkflowContainer = function (mode) {
		console.log('in updateModeWorkflowContainer');

        let icon = $('.workflow-task-btn'),
            notificationBlock = $('.workflow-task-notification'),
            itemsBlock = $('.workflow-task-item'),
            descriptionBlock = $('.workflow-task-description'),
            meta = itemsBlock.find('[name="TaskId"]:checked').data('meta'),
            routeInput = $('.workflow-route-input'),
            saveBtn = $('.save-route-btn'),
            stepId = [],
            taskInput = descriptionBlock.find('.workflow-task-input');

        taskInput.filter(':not(.workflow-task-input-hidden)').val('').trigger('change, keyup').filter('select').html('');
        taskInput.filter('[name="CurrentStepId"]').attr('readonly', false);

		console.log('hide description block');

        descriptionBlock.hide();
        saveBtn.hide();

        if (mode == 'edit' && meta) {
            stepId = meta.CurrentStepId;

            $.each(meta, function (key, item) {
                descriptionBlock.find('.workflow-task-input[name="' + key + '"]').val(item).trigger('keyup').prop('disabled', false);
            });
        } else if (mode == 'insert') {
            stepId = $('.screen-tab.btn.active').data('flowStepId');
            if (stepId) {
                icon.show();
            }

            notificationBlock.hide().html('');

            itemsBlock.show().html('<input type="text" class="form-control task-description-label-field" disabled value="Please wait..." placeholder="Please enter task description">');
            taskInput.filter('[name="CurrentStepId"]').attr('readonly', 'readonly');
        } else {
			if(meta) {
				stepId = meta.CurrentStepId;

				$.each(meta, function (key, item) {
					descriptionBlock.find('.workflow-task-input[name="' + key + '"]').val(item).trigger('keyup').prop('disabled', true);
				});
			}
		}

        if ((mode == 'edit' || mode == 'insert') && stepId.length) {
			console.log("(mode == 'edit' || mode == 'insert') && stepId.length");

            routeInput.html('<option value="" selected>Loading...</option>');

            descriptionBlock.show();
            saveBtn.show();

            $.ajax({
                type: 'GET',
                cache: false,
                url: this.getWorkflowStepUrl,
                data: {stepId: stepId},
                success: function (response) {
					console.log('response');
					console.log(response);

                    let routes = [];

					var currentStepID = response[0].StepId;
					var currentStepScreen = response[0].Screen;
					var currentStepUuid = response[0].StepUuid;
					var currentStepFlowId = response[0].FlowId;
					var currentStepGroup = response[0].Group;
					var currentStepName = response[0].StepName;
					var currentStepType = response[0].StepType;
					var currentStepAssignedTo = response[0].StepConnectAlloUnassigned;
					var currentStepAllowUnassigned = response[0].StepConnectAssignedTo;
					var currentStepUsers = [response[0].Group];

                    response.map(function (item) {
                        if (item.ToSteps) {
                            routes[item.FlowId] = [];

							if(currentStepName != 'START')
								routes[item.FlowId][currentStepID] = {FlowId: currentStepFlowId, Group: currentStepGroup, Screen: currentStepScreen, StepId: currentStepID, StepName: currentStepName, StepType: currentStepType, StepUuid: currentStepUuid, Users: currentStepUsers,StepConnect: {AssignedTo: currentStepAssignedTo, AllowUnassigned: currentStepAllowUnassigned}};

                            item.ToSteps.map(function (step) {
                                routes[item.FlowId][step.StepId] = step;
                            });
                        }
                    });

                    if (routes.length > 0) {
						console.log(routes);

                        routeInput.html('');

                        if (mode != 'insert' && (currentStepName != '' && currentStepName != 'START')) {
                            routeInput.append('<option value="">-- Please select --</option>');
                        }

                        routes.forEach(function (steps, flowId) {
                            steps.forEach(function (item) {
                                routeInput.append('<option value="' + item.StepId + '" data-screen-id="' + item.Screen + '" data-uuid="' + item.StepUuid + '" data-flow-id="' + flowId + '" data-user-groups=\'' + JSON.stringify(item.Users) + '\'>' + item.StepName + '</option>');
                            });
                        });
                    } else {
                        routeInput.html('<option selected>-- You are on the last step --</option>');
                    }

                    if (mode == 'insert') {
						console.log('Insert Mode');

						$('.workflow-task-description').find('[name="TaskDescription"]').val(response[0]["TaskDescription"]);
						$('.save-route-btn').html('Save and route');

						if(currentStepName == 'START') {
							itemsBlock.show().html('');
							//itemsBlock.show().html('Has no tasks');
							descriptionBlock.hide();
							saveBtn.hide();
						} else {
							itemsBlock.find('.task-description-label-field').prop('disabled', true).val(response[0]["TaskDescription"]);
						}
					}

					if (mode == 'edit') {
						console.log('Edit Mode');

                        $('.save-route-btn').html('Save');
                    }

                    if(currentStepName == 'START') {
						routeInput.find('option').first().prop('selected', true);
						//routeInput.find('option[value="' + FIELD_TYPE_ALERT + '"]').prop('disabled', false);
						routeInput.find('option').first().prop('readonly', true);
					} else {
						routeInput.val(currentStepID);
					}

                    routeInput.change();
                }
            });
        } else {
			routeInput.html('<option value="" selected>Loading...</option>');

            descriptionBlock.show();
            saveBtn.hide();

            $.ajax({
                type: 'GET',
                cache: false,
                url: this.getWorkflowStepUrl,
                data: {stepId: stepId},
                success: function (response) {
					console.log(response);

                    let routes = [];

					if (response.length > 0) {
						var currentStepID = response[0].StepId;
						var currentStepScreen = response[0].Screen;
						var currentStepUuid = response[0].StepUuid;
						var currentStepFlowId = response[0].FlowId;
						var currentStepGroup = response[0].Group;
						var currentStepName = response[0].StepName;
						var currentStepType = response[0].StepType;
						var currentStepAssignedTo = response[0].StepConnectAlloUnassigned;
						var currentStepAllowUnassigned = response[0].StepConnectAssignedTo;
						var currentStepUsers = [response[0].Group];
					}

                    response.map(function (item) {
                        if (item.ToSteps) {
                            routes[item.FlowId] = [];

							if(currentStepName != 'START')
								routes[item.FlowId][currentStepID] = {FlowId: currentStepFlowId, Group: currentStepGroup, Screen: currentStepScreen, StepId: currentStepID, StepName: currentStepName, StepType: currentStepType, StepUuid: currentStepUuid, Users: currentStepUsers,StepConnect: {AssignedTo: currentStepAssignedTo, AllowUnassigned: currentStepAllowUnassigned}};

                            item.ToSteps.map(function (step) {
                                routes[item.FlowId][step.StepId] = step;
                            });
                        }
                    });

                    if (routes.length > 0) {
                        routeInput.html('');

						//routeInput.append('<option value="">-- Please select --</option>');

                        routes.forEach(function (steps, flowId) {
							//console.log(steps);

                            steps.forEach(function (item) {
								routeInput.append('<option value="' + item.StepId + '" data-screen-id="' + item.Screen + '" data-uuid="' + item.StepUuid + '" data-flow-id="' + flowId + '" data-user-groups=\'' + JSON.stringify(item.Users) + '\' data-assigned-to="' + item.StepConnect.AssignedTo + '" data-assigned-to="' + item.StepConnect.AssignedTo + '" data-user-group="' + item.Group + '">' + item.StepName + '</option>');
                            });
                        });
                    } else {
                        routeInput.html('<option selected>-- You are on the last step --</option>');
                    }

					if(currentStepName == 'START')
						routeInput.find('option').first().prop('selected', true);
					else
						routeInput.val(currentStepID);

                    routeInput.change();
                }
            })
		}
    };

    /**
     * Getting a search result items
     * @param {?array|?boolean} id
     * @param {object} [foundData]
     * @param {boolean} [isReload] - set TRUE if you want update sessionStorage
     */
    this.setActiveId = function (id, foundData, isReload, section_to_refresh='', section_depth_value='', field_id='', button_action='') {
		console.log('in this.setActiveId');
		console.log('button_action :: ' + button_action);

		console.log('id');
		console.log(id);
		console.log('foundData');
		console.log(foundData);

        var me = this,
            specialButton = $('.special-btns'),
            fields = $('.search-field[data-library="' + me.selectedLib + '"]'),
            customWrapper = fields.parents('.search-input-wrapper');

        if (id) {
            sessionStorage['active-id-' + me.selectedLib] = JSON.stringify(id);
            this.updateWorkflowIconContainer($('.screen-tab.btn.active').data('flowStepId'), sessionStorage['active-id-' + me.selectedLib]);
        } else {
            delete sessionStorage['active-id-' + me.selectedLib];
        }

        if (!isReload) {
			sessionStorage['search-res-' + me.selectedLib] = JSON.stringify(me.lastSearchResults);
        }

        if (foundData !== undefined) {
            var placeholder = [];

            sessionStorage['lastFoundData'] = JSON.stringify(foundData);
            $.each(foundData, function (name, value) {
                var input = fields.filter('[name="' + name + '"]');
                if (input.length > 0) {
                    input.val(value);
                    placeholder.push(value);
                }
            });
            delete foundData.id;
            this.setSearchAnchor(foundData);

            if (customWrapper.length > 0) {
                customWrapper.find('.search-group-placeholder').text(placeholder.join(', '));
            }
        }

        this.clearResultApi();

        if(section_to_refresh != '' && section_depth_value != '' && field_id != '') {
			
		} else if(button_action != '' && button_action == 'search_submit') {
			this.reloadActiveLibTabs('', '', button_action);
		} else {
			this.reloadActiveLibTabs();
		}

        if (!specialButton.hasClass('active')) {
            specialButton.addClass('active');
        }
    };

    /**
     * Lock/Unlock fields of current library for update
     * @param {boolean} [isLock] - Set TRUE if you want locked
     */
    this.setLockedActiveId = function (isLock) {
        var me = this,
            url = (isLock) ? me.LockRecordUrl : me.UnlockRecordUrl,
            dataSourceGet = null,
            textInfo = (isLock) ? 'Locking' : 'Unlocking',
            targetOfActiveTab = $('.screen-tab.btn.active').attr('data-target');

        $(targetOfActiveTab).find('.panel.panel-default.panel-window, .header-section').each(function () {
            dataSourceGet = $(this).attr('data-source-get');
            return false;
        });

        me.addMessageToArea(textInfo + ' this record...');
        $.ajax({
            type: 'POST',
            cache: false,
            url: url,
            data: {
                lib: me.selectedLib,
                id: JSON.parse(sessionStorage['active-id-' + me.selectedLib]),
                function: dataSourceGet,
                pkForAliasFramework: (me.aliasFrameworkInfo && me.aliasFrameworkInfo['enable']) ? me.lastGetDataPK : null,
                aliasFrameworkInfo: me.aliasFrameworkInfo,
                activeTab: $('.screen-tab.active').attr('data-tab-id')
            }
        }).done(function (response) {
            if (response['status'] === 'error') {
                me.addErrorMessageToArea(response.message);
                $('.search-group.active input.form-control.tt-hint, .search-group.active input.form-control.tt-input').val('');
				$('input.form-control.tt-input').css('background-color', 'none');
                $('.nav-left-group a').removeClass('disabled');
            } else if (isLock) {
                me.addMessageToArea(response.message);
                 $('.special-sub-btns.active').removeClass('active');
                 $('.special-sub-btns.special-sub-btns-edit').addClass('active');
                me.changeMode('edit');
            }
        });
    };

    //Getting html code of tab
    this.reloadActiveLibTabs = function (field_val='', field_list_json='', button_action='') {
		console.log('this.reloadActiveLibTabs');
		console.log(button_action);

        var me = this,
            Id = !sessionStorage['active-id-' + me.selectedLib] ? null : JSON.parse(sessionStorage['active-id-' + me.selectedLib]),
            cacheFlag = true,
            screenTabs = $('.screen-tab.btn[data-lib="' + this.selectedLib + '"]'),
            screenTabsLength = screenTabs.length;

        me.subData = {
            subIdStart: 0,
            insert: {},
            update: {},
            delete: {}
        };

        if (me.activeMode) {
            if (this.aliasFrameworkInfo.enable) {
                screenTabs = screenTabs.filter('[data-alias-framework="' + this.aliasFrameworkInfo.request_primary_table + '"]');
            } else {
                screenTabs = screenTabs.not('[data-alias-framework]');
            }
        }

		if(me.activeMode == 'insert' || me.activeMode == 'edit' || me.activeMode == 'copy')
			$('.screen-execute-btn').hide();

        //Remove tabs that use workflow
        if ((me.activeMode == 'edit' || me.activeMode == 'copy')) {
            screenTabs = screenTabs.map(function () {
                var tabId = $(this).data('tab-id');
                if (!(me.workflowInfo[tabId] && me.workflowInfo[tabId]['locked'])) {
                    return $(this);
                }
            });

            if (screenTabs.length == 0) {
                me.addErrorMessageToArea('Access denied: All of screens is locked for update');
                $('.nav-left-group a').removeClass('disabled');
                $('.special-sub-btns.special-sub-btns-edit').removeClass('active');
                me.activeMode = null;
            }
        }

        this.lastGetDataPK = {};

		console.log('reloadActiveLibTabs screenTabs');
		console.log(screenTabs);
		console.log(me.baseUrl);

        screenTabs.each(function (index) {
            var t = $(this);
            var tabPlace = t.data('target');
            var tabId = t.data('tab-id');

			console.log('id :: tabId');
			console.log(Id + ' :: ' + tabId);

            if (Id == null && me.activeMode !== 'insert' && button_action == '') {
                $(tabPlace).html('');
            } else {
				//console.log('tabPlace');
				//console.log($(tabPlace).html());

                if (me.activeMode == 'execute') {
                    me.addMessageToArea('Executing data...');
                } else {
                    me.addMessageToArea('Loading ' + ((me.activeMode) ? me.activeMode : 'tab') + ' data...');
                }

				if(button_action != '' && button_action == 'search_submit') {
					var headerInputsSelector = $('.tab-content [data-section-lib="' + me.selectedLib + '"].active .header-section').find('input,select,textarea').not('.form-control-grid');
					var serializeHeaderInputArray = headerInputsSelector.serializeArrayWithData();
				} else {
					var serializeHeaderInputArray = [];
				}

				console.log('serializeHeaderInputArray');
				console.log(serializeHeaderInputArray);

				console.log('serializeHeaderInputArray.length');
				console.log(serializeHeaderInputArray.length);

                $.ajax({
                    type: 'POST',
                    cache: false,
                    url: me.baseUrl,
                    data: {
                        id: Id,
                        activeTab: tabId,
                        mode: me.activeMode,
                        cache: cacheFlag,
                        lastFoundData: (sessionStorage['lastFoundData']) ? JSON.parse(sessionStorage['lastFoundData']) : null,
						field_val: field_val,
						field_list_json: field_list_json,
						section_to_refresh: '',
						section_depth_value: '',
						button_action: button_action,
						header_fields: serializeHeaderInputArray
                    },
                    success: function (response) {
                        if (response.messagePool) {
                            me.showMessagePool(response);
                        }

                        if (me.activeMode == 'execute') {
                            me.loadedTabsNum++;
                            if (screenTabsLength == me.loadedTabsNum) {
                                me.activeMode = null;
                                me.loadedTabsNum = 0;
                                me.reloadActiveLibTabs();
                            }
                        } else {
							if(response != null && response != '')
								$('.screen-execute-btn').show();
							else
								$('.screen-execute-btn').hide();

                            $(tabPlace).html(response);

							var button_data = $(tabPlace).find('.stats-section .common-login-btn-class');
							console.log('button_data');
							console.log(button_data);
							console.log($(button_data).attr('id'));

							if($(button_data).attr('id') != undefined && $(button_data).attr('id') != '') {
								$('.info-place').hide();
								$('.message-pool-link').hide();
							}

							var template_layout_section_depth_active_ids = [];

							var section_depth_refresh_field_linked_value = '';

							$(tabPlace).find('.stats-section, .header-section').each(function () {
								section_depth_refresh_field_linked_value = $('.common-refresh-section-class').val();
							});

							console.log('tabPlace section_depth_refresh_field_linked_value :: ' + section_depth_refresh_field_linked_value);

							var template_layout_section_row_cnt = $(tabPlace).data('template-layout-section-row-cnt');
							var template_layout_section_col_cnt = $(tabPlace).data('template-layout-section-col-cnt');

							console.log('template_layout_section_row_cnt :: ' + template_layout_section_row_cnt);
							console.log('template_layout_section_col_cnt :: ' + template_layout_section_col_cnt);

							var template_section_depth_cnt = 1;

							/*$(tabPlace).find('.common_section_depth_class_'+tabId).each(function () {
								console.log('in common_section_depth_class');

								var row_num = $(this).data('row');
								var col_num = $(this).data('col');

								console.log('row_num :: ' + row_num);
								console.log('col_num :: ' + col_num);

								template_section_depth_cnt = $(this).data('template-layout-section-depth-cnt');

								console.log('template_section_depth_cnt :: ' + template_section_depth_cnt);

								for(var row = 1; row <= template_layout_section_row_cnt; row++) {
									for(var col = 1; col <= template_layout_section_col_cnt; col++) {
										if(col == 1)
											var temp_col = 1;
										else if(col == 2 && template_section_depth_cnt > 1)
											var temp_col = 8;
										else
											var temp_col = 1;

										var template_layout_section_depth_active_id = '#section_depth_'+tabId+'_'+row+'-'+temp_col;

										console.log('default template_layout_section_depth_active_id :: ' + template_layout_section_depth_active_id);

										var section_depth_linked_field_value = '';

										for(var i = temp_col; i <= template_section_depth_cnt; i++) {
											section_depth_linked_field_value = $('#section_depth_linked_field_value_'+tabId+'_'+row+'_'+i).val();

											console.log('section_depth_linked_field_value :: ' + section_depth_linked_field_value);

											if(section_depth_linked_field_value != '' && section_depth_linked_field_value != undefined && section_depth_linked_field_value == section_depth_refresh_field_linked_value)
												template_layout_section_depth_active_id = '#section_depth_'+tabId+'_'+row+'-'+i;
										}

										console.log('active template_layout_section_depth_active_id :: ' + template_layout_section_depth_active_id);
									}
								}
							});*/

							$(tabPlace).find('.common_section_depth_class_'+tabId).hide();

							if(me.activeMode == 'insert' || me.activeMode == 'edit')
								$(tabPlace).find('.common_section_depth_class_'+tabId).find('input, select, textarea, keygen').attr('disabled', true);
								//$(tabPlace).find('input, select, textarea, keygen').attr('disabled', true);

							$(tabPlace).find('.common_section_depth_'+tabId).each(function () {
								console.log('in common_section_depth');

								template_layout_section_depth_row_num = $(this).data('template-layout-section-depth-row-num');

								console.log('template_layout_section_depth_row_num :: ' + template_layout_section_depth_row_num);

								for(var row = 1; row <= template_layout_section_row_cnt; row++) {
									if(template_layout_section_depth_row_num == row) {
										template_section_depth_cnt = $(this).data('template-layout-section-depth-cnt');

										console.log('template_section_depth_cnt :: ' + template_section_depth_cnt);

										for(var col = 1; col <= template_layout_section_col_cnt; col++) {
											if(col == 1)
												var temp_col = 1;
											else if(col == 2 && template_section_depth_cnt > 1)
												var temp_col = 8;
											else if(col == 2 && template_section_depth_cnt == 1)
												var temp_col = 1;
											else
												var temp_col = 8;

											var template_layout_section_depth_active_id = '#section_depth_'+tabId+'_'+row+'-'+temp_col;

											console.log('default template_layout_section_depth_active_id :: ' + template_layout_section_depth_active_id);

											var section_depth_linked_field_value = '';

											for(var i = temp_col; i <= template_section_depth_cnt; i++) {
												section_depth_linked_field_value = $('#section_depth_'+tabId+'_'+row+'-'+i).data('template-layout-section-depth-linked-field-value');

												//section_depth_linked_field_value = $('#section_depth_linked_field_value_'+tabId+'_'+row+'_'+i).val();

												console.log('section_depth_linked_field_value :: ' + section_depth_linked_field_value);

												if(section_depth_linked_field_value != '' && section_depth_linked_field_value != undefined && section_depth_linked_field_value == section_depth_refresh_field_linked_value)
													template_layout_section_depth_active_id = '#section_depth_'+tabId+'_'+row+'-'+i;
											}

											console.log('active template_layout_section_depth_active_id :: ' + template_layout_section_depth_active_id);

											if($.inArray(template_layout_section_depth_active_id, template_layout_section_depth_active_ids) == -1)
												template_layout_section_depth_active_ids.push(template_layout_section_depth_active_id);
										}
									}
								}
							});

							/*var template_layout_section_depth_cnt = '';
							var template_section_depth_default_col_num = '';
							var template_active_section_depth_id = '';
							var template_inactive_section_depth_id = '';

							$(tabPlace).find('.stats-section').each(function () {
								//console.log('Section Parent ID :: ' + $(this).parent().parent().attr('id'));

								$(this).hide();

								var row_num = $(this).data('row');
								var col_num = $(this).data('col');

								var template_id = $(this).data('template-id');

								var section_depth_linked_field_value = $('#section_depth_linked_field_value_'+template_id+'_'+row_num+'_'+col_num).val();

								//console.log('row_num :: ' + row_num);
								//console.log('col_num :: ' + col_num);

								//console.log('template_id :: ' + template_id);

								//console.log('section_depth_linked_field_value :: ' + section_depth_linked_field_value);

								if(col_num == 1 || (col_num == 2 || col_num == 8)) {
									template_layout_section_depth_cnt = 1;

									if(template_section_depth_default_col_num == '' && template_section_depth_default_col_num != 8)
										template_section_depth_default_col_num = 1;
									else if(template_section_depth_default_col_num != '' && template_section_depth_default_col_num == 8)
										template_section_depth_default_col_num = 8;
									else if(template_section_depth_default_col_num != '' && template_section_depth_default_col_num == 2)
										template_section_depth_default_col_num = 2;
								}

								//console.log('template_layout_section_depth_cnt :: ' + template_layout_section_depth_cnt);
								//console.log('template_section_depth_default_col_num :: ' + template_section_depth_default_col_num);

								if(section_depth_linked_field_value == '' && template_layout_section_depth_cnt == 1) {
									$(this).show();

									template_active_section_depth_id = $(this).attr('id');
								} else if(section_depth_linked_field_value != '' && section_depth_refresh_field_linked_value == section_depth_linked_field_value) {
									$(this).show();

									template_active_section_depth_id = $(this).attr('id');
								} else if(section_depth_linked_field_value != '' && section_depth_refresh_field_linked_value != section_depth_linked_field_value && col_num == template_section_depth_default_col_num) {
									$(this).show();

									template_active_section_depth_id = $(this).attr('id');
								} else {
									template_inactive_section_depth_id = $(this).attr('id');

									if(me.activeMode == 'insert' || me.activeMode == 'edit')
										$(tabPlace).find('#'+template_inactive_section_depth_id+' input,select,textarea,keygen').attr('disabled', true);
								}
							});*/

							console.log('template_layout_section_depth_active_ids');
							console.log(template_layout_section_depth_active_ids);

							console.log('template_layout_section_depth_active_ids.length :: ' + template_layout_section_depth_active_ids.length);

							if(template_layout_section_depth_active_ids.length > 0) {
								console.log('in template_layout_section_depth_active_ids.length if');

								$.each(template_layout_section_depth_active_ids, function (index, val) {
									console.log('in template_layout_section_depth_active_ids each');
									console.log('val :: ' + val);

									var active_section_id = val;

									$(tabPlace).find(active_section_id).show();

									if(me.activeMode == 'insert' || me.activeMode == 'edit') {
										$(tabPlace).find(active_section_id).find('input, select, textarea, keygen').removeAttr('disabled');
										$(tabPlace).find(active_section_id).find('input, textarea').attr('autocomplete', 'off');
									}

									$(tabPlace).find(active_section_id+' [data-dependent-field]').dependentField();
									$(tabPlace).find(active_section_id+' [readonly][data-krajee-datetimepicker]').each(function () {
										$('#' + $(this).attr('id') + '-datetime').on('show', function(){
											$(this).datetimepicker('hide');
										});
									});

									$(tabPlace).find(active_section_id+' [data-relation-field]').each(function () {
										$(this).trigger('custom-focus');
									});

									if(me.activeMode == 'insert' || me.activeMode == 'edit')
										$(tabPlace).find(active_section_id+' input.form-control.tt-input').css('background-color', '#fff');

									$(tabPlace).find(active_section_id+' #search_extra_param').each(function () {
										var layout_type = $(this).find('#layout_type').val();
										var mode = $(this).find('#mode').val();
										var readonly = $(this).find('#readonly').val();

										if(layout_type == 'TABLE' && mode == 'edit' && readonly == 1) {
											$(tabPlace).find(active_section_id+' .add-sub-item').parent().parent().hide();
											$(tabPlace).find(active_section_id+' .remove-sub-item').parent().hide();
										} else if(mode == 'edit' && me.activeMode != mode) {
											me.activeMode = mode;
											me.setLockedActiveId(true);
											//me.changeMode(mode);
										}
									});

									$(tabPlace).find(active_section_id+' .grid-view').each(function () {
										console.log('in active_section_id each');
										console.log('widget_id :: ' + $(this).attr('id'));

										var widget_id = $(this).attr('id');
										var widget_table_pagination_count = parseInt($('#'+widget_id+'_table_pagination_count').val());
										var widget_table_row_count = parseInt($('#'+widget_id+'_table_row_count').val());

										console.log('button_action :: ' + button_action);
										console.log('widget_table_pagination_count :: ' + widget_table_pagination_count);
										console.log('widget_table_row_count :: ' + widget_table_row_count);
										console.log('me.activeMode :: ' + me.activeMode);

										if(me.activeMode == '' || me.activeMode == null) {
											console.log('in me.activeMode if null or blank');

											if(button_action == 'search_submit' && widget_table_row_count > 0) {
												if(widget_table_pagination_count < widget_table_row_count) {
													//console.log('in if');

													$(this).find('.'+widget_id+'_tbl').DataTable({
														//"scrollY": 400,
														"pageLength": widget_table_pagination_count,
														"order": [],
														//"scrollCollapse": true,
														//"scrollX": true,
														//"sScrollX": "100%",
														//"sScrollXInner": "110%",
														"bAutoWidth": false,
														//"iDisplayLength":-1,
														//"scrollY": '50vh',
														//scrollCollapse: true,
														"autoWidth": false,
													});
												} else {
													//console.log('in else');

													$(this).find('.'+widget_id+'_tbl').DataTable({
														//"scrollY": 400,
														"pageLength": widget_table_pagination_count,
														"order": [],
														//"scrollCollapse": true,
														//"scrollY": false,
														//"scrollX": true,
														//"sScrollX": "100%",
														//"sScrollXInner": "110%",
														"bAutoWidth": false,
														//"iDisplayLength":-1,
														//"scrollY": '50vh',
														//scrollCollapse: true,
														"autoWidth": false,
													});
												}

												//$(tabPlace).find('.stats-section .grid-view').after('<div class="row"><div class="col-sm-6"><br>&nbsp;</div><div class="col-sm-6"><br><button type="button" style="max-width: 100%; display: inline-block; vertical-align: top; width: 100%;" class="btn btn-default" id="common_export_to_excel_btn" data-button-action="'+button_action+'" data-table-id="'+widget_id+'">Export to Excel</button></div></div>');
											} else if(widget_table_row_count > 0) {
												console.log('in me.activeMode == blank if else');

												if(widget_table_pagination_count < widget_table_row_count) {
													console.log('in widget_table_pagination_count < widget_table_row_count if');

													$(this).find('.'+widget_id+'_tbl').DataTable({
														//"scrollY": 400,
														"pageLength": widget_table_pagination_count,
														"order": [],
														//"scrollCollapse": true,
														//"scrollX": true,
														//"sScrollX": "100%",
														//"sScrollXInner": "110%",
														"bAutoWidth": false,
														//"iDisplayLength":-1,
														//"scrollY": '50vh',
														//scrollCollapse: true,
														"autoWidth": false,
													});
												} else {
													console.log('in widget_table_pagination_count < widget_table_row_count else');

													$(this).find('.'+widget_id+'_tbl').DataTable({
														//"scrollY": 400,
														"pageLength": widget_table_pagination_count,
														"order": [],
														//"scrollCollapse": true,
														//"scrollY": false,
														//"scrollX": true,
														//"sScrollX": "100%",
														//"sScrollXInner": "110%",
														"bAutoWidth": false,
														//"iDisplayLength":-1,
														//"scrollY": '50vh',
														//scrollCollapse: true,
														"autoWidth": false,
													});
												}
											}

											setTimeout(function() {
												$($.fn.dataTable.tables(true)).DataTable().columns.adjust();
											}, 4000);
										} else if(me.activeMode == 'edit') {
											if(widget_table_pagination_count < widget_table_row_count)
												$(this).find(".table-responsive").addClass("is-top-scroll");
										}
									});

									if(me.activeMode == 'edit') {
										$(tabPlace).find(".table tbody tr[data-key=-1]").find("input, select, textarea").val('');
										$(tabPlace).find(".table tbody tr[data-key=-1]").find("div, select, input, textarea").hide();
									}

									me.addMessageToArea('<span style="text-transform: capitalize">' + ((me.activeMode) ? me.activeMode : 'Tab') + '</span> data has been loaded');

									if (me.activeMode == 'insert' || me.activeMode == 'edit'  || me.activeMode == 'copy') {
										var gridItem = $(tabPlace).find(active_section_id+' .grid-view');
									} else if (!me.activeMode) {
										me.setRelationDataBlockInfo();
									}

									me.checkAvailableBtnLoad();

									var tabindex = 1;
									$('.tab-content').find(active_section_id+' .stats-section, .header-section').each(function () {
										var inputs = $(this).find('input,select,textarea,keygen,button');
										inputs.sort(function(a, b) {
											var gridStackBlockForA = $(a).parents('.grid-stack-item'),
												gridStackBlockForB = $(b).parents('.grid-stack-item'),
												xAxisForA = gridStackBlockForA.attr('data-gs-x'),
												xAxisForB = gridStackBlockForB.attr('data-gs-x'),
												yAxisForA = gridStackBlockForA.attr('data-gs-y'),
												yAxisForB = gridStackBlockForB.attr('data-gs-y');

											return (yAxisForA*10+xAxisForA) - (yAxisForB*10+xAxisForB);
										});

										inputs.each(function () {
											if ($(this).attr('tabindex') != -1) {
												$(this).attr('tabindex', tabindex++);
											}
										});
									});

									$(tabPlace).find(active_section_id+' [tabindex="1"]').trigger('focus');

									if($('.workflow-task-notification').text() != 0 && $('.workflow-task-notification').text != '')
										$('.workflow-task-notification').show();

									if(serializeHeaderInputArray.length > 0) {
										console.log('in success serializeHeaderInputArray');

										$.each(serializeHeaderInputArray, function (index, val) {
											if(val.value != '')
												$(tabPlace).find("input[name='"+val.name+"']").val(val.value);
										});
									}
								});
							}
                        }
                    },
                    error: function () {
                        $(tabPlace).html('');
                        me.addErrorMessageToArea('Loading error');
                    }
                });
                cacheFlag = null;
            }
        });
    };

    this.checkAvailableBtnLoad = function () {
        var availableLoad = $('.tab-content [data-section-lib="' + this.selectedLib + '"] .form-control.form-control-key').length > 0;
        var loadBtn = $('[data-mode="key"]');
        if (availableLoad) {
            loadBtn.show();
        }
        else {
            loadBtn.hide();
        }
    };

    /**
     * Add row to table in insert/update mode
     * @param {jQuery} element
     */
    this.addGridLine = function (element) {
		console.log('in addGridLine');

        var me = this;
        var subId = me.subData.subIdStart;
        me.subData.subIdStart = subId + 1;

        var newSubIdText = 'n' + subId,
            isTopOrientation = element.parents('table').attr('data-top-orientation'),
            serializeElements = element.parents('table').find('[data-sub-id="-1"]');

        this.subData.insert[newSubIdText] = serializeElements.serializeArrayWithData();
        serializeElements.each(function () {
            var t = $(this);
            if (t.hasClass('currency-input') || t.hasClass('decimal-input')) {
                $.each(me.subData.insert[newSubIdText], function (i, item) {
                    if (item.name == t.attr('name')) {
                        me.subData.insert[newSubIdText][i]['value'] = t.maskMoney('unmasked')[0];
                    }
                });
            }
        });

        if (isTopOrientation == "1") {
            var currentTr = element.parent().parent(),
                newTr = currentTr.clone();

            currentTr.find('select').each(function () {
                newTr.find('select[name="' + $(this).attr('name') + '"]').val($(this).val());
            });

            currentTr.after(newTr);

            newTr.attr('data-key', newSubIdText);
            newTr.find('[data-sub-id="-1"]').each(function () {
                $(this).attr('data-sub-id', newSubIdText);
            });
            newTr.find('td:last-child').html('<span class="glyphicon glyphicon-trash sub-item-control remove-sub-item" data-id="' + newSubIdText + '"></span>');
			newTr.find('td').find('div, select, input, textarea').show();

            me.pickerEvent(newTr.find('.krajee-datepicker'), newSubIdText, 'datepicker');
            newTr.find('input').each(function () {
				console.log('in isTopOrientation');
				console.log($(this).attr('name'));

                if ($(this).attr('data-krajee-datetimepicker')) {
                    me.pickerEvent($(this), newSubIdText, 'datetimepicker');
                }
            });

			//console.log(newTr.find('.tbl-dynamic-common-div-class').html());

			var input_id = currentTr.find('.tbl-dynamic-common-div-class').find('input:file').attr('data-input-id');

			var dynamic_image_upload_html = '<input type="file" class="common_tbl_field_upload_image_class" id="file'+subId+'_'+input_id+'" data-input-id="'+subId+'_'+input_id+'" style="display: none;" /><a href="javascript: void(0);" id="upload_link'+input_id+'" onclick="$(\'#file'+subId+'_'+input_id+'\').trigger(\'click\'); return false;"><img src="" class="glyphicon glyphicon-picture"></a>&nbsp;&nbsp;&nbsp;&nbsp;<img src="" id="dimage_show_'+subId+'_'+input_id+'" style="width: auto; height: 60px; display: none;">';

            me.maskedEvent(newTr.find(".currency-input"));
            me.maskDecimal(newTr.find(".decimal-input"));

			newTr.find('.tbl-dynamic-common-div-class').html(dynamic_image_upload_html);

			newTr.find('.tbl-dynamic-image-field-common-div').find('input[type=hidden]:first').attr('id', 'dimage'+subId+'_'+input_id);

            currentTr.after(newTr);

            currentTr.find('[data-sub-id="-1"]').val(null);

			//currentTr.find('.tbl-dynamic-common-div-class').html('test');
        } else {
            var myIndex = element.closest("td").prevAll("td").length;
            element.parents("table").find("tr").each(function () {
                $(this).find("td:eq(" + myIndex + ")").each(function () {
                    var newTD = $(this).clone();
                    if (newTD.find('.add-sub-item').length > 0) {
                        newTD.html('<span class="glyphicon glyphicon-trash sub-item-control remove-sub-item" data-id="' + newSubIdText + '"></span>');
                    } else {
                        newTD.find('[data-sub-id="-1"]').attr('data-sub-id', newSubIdText);

                        me.pickerEvent(newTD.find('.krajee-datepicker'), newSubIdText, 'datepicker');
                        newTD.find('input').each(function () {
                            if ($(this).attr('data-krajee-datetimepicker')) {
                                me.pickerEvent($(this), newSubIdText, 'datetimepicker');
                            }
                        });
                        me.maskedEvent(newTD.find(".currency-input"));
                        me.maskDecimal(newTD.find(".decimal-input"));
                    }
                    $(this).after(newTD);
                });
            });
            element.parents("table").find('[data-sub-id="-1"]').val(null);
        }
    };

    this.pickerEvent = function (picker, id, pickername) {
		console.log('in this.pickerEvent');

        if (picker.length > 0) {
            var pickerHash;
            if ('datetimepicker' == pickername) {
                pickerHash = picker.attr('data-krajee-datetimepicker');

                picker.attr('id', id + '-date');
                picker.parent().attr('id', id + 'date-datetime');

                picker.parent().datetimepicker(window[pickerHash]);
            } else {
                picker.attr('id', id + '-date');
                picker.attr('data-' + pickername + '-source', id + '-kvdate');
                picker.parent().attr('id', id + '-kvdate');

                pickerHash = picker.attr('data-krajee-kv' + pickername);

                $('#' + id + '-date').kvDatepicker('destroy');
                picker.parent().kvDatepicker(window[pickerHash]);
                initDPAddon(id + '-date');
            }
        }
    };

    this.maskedEvent = function (currencyInput) {
        if (!currencyInput) currencyInput = $(".currency-input:not(.is-masked)");

        currencyInput.maskMoney({
            thousands: this.currencyProperty.thousands,
            decimal: this.currencyProperty.decimal,
            allowZero: true,
            suffix: ""
        });
        currencyInput.maskMoney("mask");
        currencyInput.addClass("is-masked");
    };

    this.maskDecimal = function (decimalInput) {
        var me = this;

        if (!decimalInput) {
            decimalInput = $(".decimal-input:not(.is-masked)");
        }

        decimalInput.each(function () {
            var precision = $(this).attr('data-precision') || 0;
            $(this).maskMoney({
                thousands: me.decimalProperty.thousands,
                decimal: me.decimalProperty.decimal,
                allowZero: true,
                precision: precision,
                suffix: ""
            });
        });
        decimalInput.maskMoney("mask");
        decimalInput.addClass("is-masked");
    };

    /**
     * Remove row from table in insert/update mode
     * @param {jQuery} element
     */
    this.removeGridLine = function (element) {
        var me = this,
            subId = element.data('id'),
            isTopOrientation = element.parents('table').attr('data-top-orientation'),
            isInsertDelete = subId in this.subData.insert,
            isUpdateDelete = subId in this.subData.update,
            serializeElements = element.parents('table').find('[data-sub-id="' + subId + '"]'),
            serializeArray = serializeElements.serializeArrayWithData();

        if (isInsertDelete) {
            delete this.subData.insert[subId];
        } else if (isUpdateDelete) {
            delete this.subData.update[subId];
        }

        if (isTopOrientation == "1") {
            var childTR = element.parents('tr');

            if (isInsertDelete) {
                childTR.remove();
            } else {
                childTR.find('td').css('background-color', 'rgb(255, 222, 222)');
                childTR.find('td .form-control-grid').prop('disabled', true);

                this.subData.delete[subId] = serializeArray;
            }

            me.addMessageToArea('Row was removed from the table');
        } else {
            var myIndex = element.closest("td").prevAll("td").length;

            element.parents("table").find("tr").each(function () {
                var childTD = $(this).find("td:eq(" + myIndex + ")");

                if (isInsertDelete) {
                    childTD.remove();
                } else {
                    childTD.css('background-color', 'rgb(255, 222, 222)');
                    childTD.find('.form-control-grid').prop('disabled', true);

                    me.subData.delete[subId] = serializeArray;
                }
            });

            me.addMessageToArea('Column was removed from the table');
        }

        element.removeClass('glyphicon-trash');
        element.addClass('glyphicon-pushpin');
        element.removeClass('remove-sub-item');
        element.addClass('return-sub-item');
    };

    /**
     * Return deleted row to table in insert/update mode
     * @param {jQuery} element
     */
    this.unRemoveGridLine = function (element) {
        var subId = element.data('id'),
            isTopOrientation = element.parents('table').attr('data-top-orientation');

        delete this.subData.delete[subId];

        if (isTopOrientation == "1") {
            element.parent().parent().find('td').css('background-color', 'transparent');
            element.parent().parent().find('td .form-control-grid').prop('disabled', false);

            this.addMessageToArea('Row was returned to the table');
        } else {
            var myIndex = element.closest("td").prevAll("td").length;
            element.parents("table").find("tr").each(function () {
                $(this).find("td:eq(" + myIndex + ")").css('background-color', 'white');
                $(this).find("td:eq(" + myIndex + ")").find('.form-control-grid').prop('disabled', false);
            });

            this.addMessageToArea('Column was returned to the table');
        }

        element.addClass('glyphicon-trash');
        element.removeClass('glyphicon-pushpin');
        element.addClass('remove-sub-item');
        element.removeClass('return-sub-item');
    };

    /**
     * Save changed data of table in insert/update mode
     * @param {jQuery} element
     */
    this.changeGridLineElement = function (element) {
		console.log('in changeGridLineElement');

        var subId = element.data('sub-id');
        if (subId !== -1) {
            var serializeElements = element.parents('table').find('[data-sub-id="' + subId + '"]'),
                newSerialize = serializeElements.serializeArrayWithData();

            serializeElements.each(function () {
                var t = $(this);
                if (t.hasClass('currency-input') || t.hasClass('decimal-input')) {
                    $.each(newSerialize, function (i, item) {
                        if (item.name == t.attr('name')) {
                            newSerialize[i]['value'] = t.maskMoney('unmasked')[0];
                        }
                    });
                }
            });

            if (/^n\d+/.test(subId)) {
                this.subData.insert[subId] = newSerialize;
            } else {
                this.subData.update[subId] = newSerialize;
            }
        }
    };

    /**
     * @param {string} [mode] - Insert or edit or unlock or execute
     */
    this.changeMode = function (mode, field_val='', field_list_json='') {
        this.updateModeWorkflowContainer(mode);

        $('.nav-left-group a.active').removeClass('active');
        if (mode === 'edit' || mode === 'insert' || mode ==='copy') {
            $('.nav-left-group a[data-mode="' + mode + '"]').addClass('active');
        }

        this.activeMode = mode;
        this.reloadActiveLibTabs(field_val, field_list_json);
    };

    this.setCurrencyProperty = function (thousands, decimal) {
        this.currencyProperty = {
            thousands: thousands,
            decimal: decimal
        }
    };

    this.setDecimalProperty = function (thousands, decimal) {
        this.decimalProperty = {
            thousands: thousands,
            decimal: decimal
        }
    };

    this.resizeChart = function (chart) {
        var parentWindow = $($($(chart)[0].canvas).parent()[0]);
        if (parentWindow.height() < parentWindow.width() + 45) {
            chart.height = parentWindow.height() - 75;
        } else {
            chart.width = parentWindow.width() - 15;
        }
    };

    this.generateLegendPieChart = function (chart, data) {
        var value = data.datasets[0].data[chart.index] ? data.datasets[0].formatData[chart.index] : 0;
        chart.text += " ( " + value + " ) ";
        return chart;
    };

    this.generateTooltipsPieChart = function (chart, data) {
        var value = data.datasets[0].data[chart.index] ? data.datasets[0].formatData[chart.index] : 0;
        chart.text = data.labels[chart.index] + " ( " + value + " ) ";
        return chart.text;
    };

    this.generateTooltipsLineChart = function (chart, data) {
        var value = data.datasets[0].data[chart.index] ? data.datasets[0].formatData[chart.index] : 0;
        chart.text = data.datasets[0].label + " ( " + value + " ) ";
        return chart.text;
    };

    this.setLastGetDataPK = function (object, functionName, isParentSection) {
		//console.log('this.setLastGetDataPK');

		var me = this;

		/*console.log('Before me.lastGetDataPK');
		console.log(me.lastGetDataPK);
		console.log(object);
		console.log('functionName');
		console.log(functionName);
		console.log('isParentSection');
		console.log(isParentSection);*/

        if (functionName in me.lastGetDataPK) {
            if (!isParentSection) {
                if (object && object[0]) {
                    $.each(object[0], function (i, newItem) {
                        $.each(me.lastGetDataPK[functionName][0], function (j, issetItem) {
                            if (i == j && newItem == issetItem) {
                                delete object[0][i];
                                return false;
                            }
                        });
                    });
                    $.each(object[0], function (i, newItem) {
                        me.lastGetDataPK[functionName][0][i] = newItem;
                    });
                }
            } else {
				Array.prototype.push.apply(me.lastGetDataPK[functionName], object);

                //me.lastGetDataPK[functionName].concat(object);
            }
        } else {
            me.lastGetDataPK[functionName] = object;
        }

		//console.log('After me.lastGetDataPK');
		//console.log(me.lastGetDataPK);
    };

    this.setEditModeInfo = function (tid, value) {
        this.workflowInfo[tid] = value;

        if (value['workflow'] && value['locked']) {
            $('.stepper--lock[data-tid="' + tid + '"]').css({'color': 'red'});

            if (value['afterReleaseRequired'] && !value['released']) {
                $('.screen-stepper-step a[data-id="' + tid + '"]').parents('.screen-stepper-step').prevAll('.screen-stepper-step').each(function () {
                    var releaseIcon = $(this).find('.stepper--release');
                    if (releaseIcon.length > 0) {
                        var releaseTabList = releaseIcon.data('releaseTabList') || [];
                        releaseTabList.push(tid);

                        releaseIcon.attr('data-is-can-release', true).data('releaseTabList', releaseTabList).css({'color': 'green', 'cursor': 'pointer'}).show();
                        return false;
                    }
                });
            }
        } else if (value['workflow']) {
            $('.stepper--lock[data-tid="' + tid + '"]').css('color', 'green');
        } else {
            $('.stepper--lock[data-tid="' + tid + '"]').css('color', 'black');
            $('.stepper--release[data-tid="' + tid + '"]').hide();
        }
    }

	this.downloadDocumentFragment = function (filePK, fileName, fileSize, offset, chunk, remainingChunk=0, fileData=null) {
		me = this;
		var final_file_data = '';

		$.ajax({
			type: 'POST',
			cache: false,
			url: this.getDocumentDownloadFragmentUrl,
			data: {'filePK' : filePK, 'fileName': fileName, 'fileSize': fileSize, 'offset': offset, 'chunk': chunk, 'remainingChunk': remainingChunk, 'fileData': fileData},
			success: function (data) {
				console.log(data);

				console.log('fileSize :: ' + data.fileSize);
				console.log('offset :: ' + data.offset);
				console.log('chunk :: ' + data.chunk);
				//console.log('remainingChunk :: ' + data.remainingChunk);
				//console.log('fileData :: ' + data.fileData);

				if(data.remainingChunk != 0) {
					me.downloadDocumentFragment(filePK, fileName, data.fileSize, data.offset, data.chunk, data.remainingChunk, data.fileData);
				} else if(data.status == 'success') {
					if (data.response.file_data) {
						//console.log(data.response.file_data);
						//console.log(atob(data.response.file_data));

						//final_file_data = final_file_data + atob(data.response.file_data);
						//console.log(final_file_data);

						//var win = window.open(data.url, '_blank');
						//win.focus();

						const body = {profilepic:"data:image/png;base64,"+data.response.file_data};
						let mimeType = body.profilepic.match(/[^:]\w+\/[\w-+\d.]+(?=;|,)/)[0];

						if(data.fileData == null || data.fileData == '')
							me.documentDownload(fileName, mimeType, data.response.file_data, data.fileData);
						else
							me.documentDownload(fileName, mimeType, data.response.file_data, data.fileData);

						//alert(mimeType);

						/*var image = new Image();
						image.src = "data:image/jpg;base64," + data.file_data;

						var w = window.open("");
						w.document.write(image.outerHTML);*/
					}
				}
			}
		});
	}

	this.documentDownload = function (fileName, mimeType, fileData, fileData2) {
		$.ajax({
			type: 'POST',
			cache: false,
			url: this.getDocumentDownloadUrl,
			data: {'fileName': fileName, 'mimeType': mimeType, 'fileData': fileData, 'fileData2': fileData2},
			success: function (data) {
				console.log(data);

				if(data.status == 'success') {
					var win = window.open(data.url, '_blank');
					win.focus();
				}

				/*var blob=new Blob([data]);
				var link=document.createElement('a');
				link.href=window.URL.createObjectURL(blob);
				link.download=fileName;
				link.click();*/
			}
		});
	};

	this.renderDocumentModal = function (mode, value) {
		//console.log(value.id);
		//console.log($('#'+value.id).data('document-kp'));

		modalHeaderTitle = $('#document-modal').find('.modal-title');
		modalBody = $('#document-modal').find('.modal-body');

		$('#document-modal').find('#edit-document-success-message-div').hide();
		$('#document-modal').find('#edit-document-failed-message-div').hide();

		var document_family = $('#'+value.id).data('document-family');
		var kps = $('#'+value.id).data('document-kp');

		$('#document-modal').find('#document_kps').val(JSON.stringify(kps));

		$('#document-modal').find('.show-deleted-document-icon').hide();

		$.ajax({
			type: 'POST',
			cache: false,
			url: this.getDocumentListUrl,
			data: {'kp' : $('#'+value.id).data('document-kp')},
			success: function (response) {
				//console.log(response);

				//loading.hide();

				$('#document-modal').find('#document_family').val(document_family);

				if(mode != null && mode == 'edit') {
					$('#document-modal').find('.show-deleted-document-icon').show();
					$('#document-modal').find('.show-deleted-document-icon').attr('id', 'removeIconId_'+value.id);
					$('#document-modal').find('.document-model-list-return-btn').attr('id', 'returnBtnId_'+value.id);

					modalHeaderTitle.html("Edit Documents");

					$('#document-modal').find('#view-document-list-tbl').hide();
					$('#document-modal').find('.view-deleted-document-list-tbl').hide();
					$('#document-modal').find('.edit-document-modal-div').show();

					if (response.length) {
						$('#document-modal').find('.document-post-form-div').html('');

						$('#document-modal').find('#document-cnt').val(response.length - 1);

						$(response).each(function (index, item) {
							console.log(item);

							var document_category = $('#'+value.id).data('document-category');

							var options = '<option value="">-- Select category --</option>';

							if(document_category.length) {
								$(document_category).each(function (index1, item1) {
									if(item1 == item.document_category)
										options += '<option value="'+item1+'" selected>'+item1+'</option>';
									else
										options += '<option value="'+item1+'">'+item1+'</option>';
								});
							}

							var id = index,
							wrapper = $('<div />', {
								'class': 'row'
							}).append($('<input />', {
								'type': 'hidden',
								'name': 'document_id[]',
								'id': 'document_id_'+index,
								'value': item.id
							})),
							document_category = $('<div />', {
								'class': 'col-sm-2'
							}).append($('<select />', {
								'class': 'form-control common-document-category-class',
								'name': 'document_category[]',
								'id': 'document_category_'+id,
								'data-id': id
								//'readonly': true
							}).append(options)
							),
							description = $('<div />', {
								'class': 'col-sm-3 form-group'
							}).append($('<input />', {
								'type': 'text',
								'name': 'description[]',
								'id': 'document_description_'+id,
								'class': 'form-control',
								'value': item.Description
							})),
							file_name = $('<div />', {
								'class': 'col-sm-6 form-group'
							}).append($('<span />', {
								'id': 'file_name_'+id,
								'text': item.original_file_name,
								'data-toggle': 'tooltip',
								'title': 'FileSize: '+item.original_file_size+', CreatedBy: '+item.CreatedBy+', CreatedDate: '+item.CreatedDate
							})),
							/*file_size = $('<div />', {
								'class': 'col-sm-1 form-group'
							}).append($('<span />', {
								'id': 'file_size_'+id,
								'text': item.original_file_size
							})),
							created_by = $('<div />', {
								'class': 'col-sm-2 form-group'
							}).append($('<span />', {
								'id': 'created_by_'+id,
								'text': item.CreatedBy
							})),
							created_date = $('<div />', {
								'class': 'col-sm-2 form-group'
							}).append($('<span />', {
								'id': 'created_date_'+id,
								'text': item.CreatedDate
							})),*/
							remove_icon = $('<div />', {
								'class': 'col-sm-1 form-group'
							}).append($('<span />', {
								'id': item.id,
								'class': 'glyphicon glyphicon-remove remove-document-icon',
								'data-id': item.id
							})),
							document_category2 = $('<div />', {
								'class': 'col-sm-2 wrapper-category-class'
							}).append($('<select />', {
								'class': 'form-control common-document-category-class',
								'name': 'document_category[]',
								'id': 'document_category_'+id
							}).append(options));

							wrapper.append(document_category).append(description).append(file_name).append(remove_icon);
							//wrapper.append(document_category).append(file_name).append(description).append(file_size).append(created_by).append(created_date).append(remove_icon);

							$('#document-modal').find('.document-post-form-div').append(wrapper);

							if(index == 0) {
								if(!$('div').hasClass('wrapper-category-class'))
									$('#document-modal').find('.document-family-wrapper').prepend(document_category2).prepend('<input type="hidden" name="document_id[]" value="0" />').prepend('</br>');
							}
						});
					} else {
						var document_category = $('#'+value.id).data('document-category');

						var options = '<option value="">-- Select category --</option>';

						if(document_category.length) {
							$(document_category).each(function (index, item) {
								options += '<option value="'+item+'">'+item+'</option>';
							});
						}

						$('#document-modal').find('#document-cnt').val(0);

						var id = 0,
							wrapper = $('<div />', {
								'class': 'row'
							}).append($('<input />', {
								'type': 'hidden',
								'name': 'document_id[]',
								'id': 'document_id_0',
								'value': 0
							})),
							document_category = $('<div />', {
								'class': 'col-sm-2'
							}).append($('<select />', {
								'class': 'form-control common-document-category-class',
								'name': 'document_category[]',
								'id': 'document_category_'+id,
								'data-id': id
							}).append(options)
							),
							description = $('<div />', {
								'class': 'col-sm-3 form-group'
							}).append($('<input />', {
								'type': 'text',
								'name': 'description[]',
								'id': 'document_description_'+id,
								'class': 'form-control'
							})),
							file_name = $('<div />', {
								'class': 'col-sm-6 form-group'
							/*}).append($('<span />', {
								'id': 'file_name_'+id*/
							}).append($('<input />', {
								'type': 'file',
								'name': 'document[]',
								'class': 'form-control',
								'id': id
							})),
							/*file_size = $('<div />', {
								'class': 'col-sm-1 form-group'
							}).append($('<span />', {
								'id': 'file_size_'+id
							})),
							created_by = $('<div />', {
								'class': 'col-sm-2 form-group'
							}).append($('<span />', {
								'id': 'created_by_'+id
							})),
							created_date = $('<div />', {
								'class': 'col-sm-2 form-group'
							}).append($('<span />', {
								'id': 'created_date_'+id
							})),*/
							document_category2 = $('<div />', {
								'class': 'col-sm-2'
							}).append($('<select />', {
								'class': 'form-control common-document-category-class',
								'name': 'document_category[]',
								'id': 'document_category_'+id
							}).append(options));

						wrapper.append(document_category).append(description).append(file_name);	//wrapper.append(document_category).append(file_name).append(description).append(file_size).append(created_by).append(created_date);

						$('#document-modal').find('.document-post-form-div').html(wrapper);

						$('#document-modal').find('.document-family-wrapper').prepend(document_category2).prepend('<input type="hidden" name="document_id[]" value="0" />').prepend('</br>');
					}
				} else {
					modalHeaderTitle.html("View Document");

					$('#document-modal').find('#view-document-list-tbl thead').html('');
					$('#document-modal').find('#view-document-list-tbl tbody').html('');

					var editPdfButton = false;
                    if (response.length) {
                        $(response).each(function (index, item) {
                            if (me.isEditAnnotation(item.access_right, item.original_file_type )) {
                                editPdfButton = true;
                            }
                        });
                    }

                    var thead =
                        '<tr>' +
                        '<th>File Name</th>' +
                        '<th>Description</th>' +
                        '<th>File Size</th>' +
                        '<th>Created By</th>' +
                        '<th>Created Date</th>';
                    if (editPdfButton) {
                        thead += '<th>Annotate</th>';
                    }
                    thead += '</tr>';
                    $('#document-modal').find('#view-document-list-tbl thead').append(thead);

                    $('#document-modal').find('#view-document-list-tbl').show();
                    $('#document-modal').find('.edit-document-modal-div').hide();

					var tbody = '';
					if (response.length) {
						$(response).each(function (index, item) {
                            tbody =
                                '<tr>' +
                                '<td><a href="javascript:void(0);" class="common_document_download_link_class download_document_id_'+index+'" id="'+index+'" data-file-name="'+item.original_file_name+'" data-file-hash="'+item.original_file_hash+'" data-file-size="'+item.original_file_size+'" data-chunk-size="'+item.chunk_size+'" data-id="'+item.id+'">' + item.original_file_name + '</a></td>' +
                                '<td>' + item.Description + '</td>' +
                                '<td>' + item.original_file_size + '</td>' +
                                '<td>' + item.CreatedBy + '</td>' +
                                '<td>' + item.CreatedDate + '</td>';
                            if (editPdfButton) {
                                if (me.isEditAnnotation(item.access_right, item.original_file_type)) {
                                    tbody += '<td><a href="javascript:void(0);" class="btn btn-default common_document_annotate" id="' + index + '" data-id="' + item.id + '">Edit</a></td>';
                                } else {
                                    tbody += '<td></td>';
                                }
                            }
                            tbody += '</tr>';

							$('#view-document-list-tbl tbody').append(tbody)
						});
					} else {
						$('#view-document-list-tbl tbody').append(
							'<tr>' +
								'<td colspan="5">No record found</td>' +
							'</tr>'
						);
					}
				}
			}
		});
	}

	this.isEditAnnotation = function (access_right, original_file_type) {
	    if (access_right == 'U' && original_file_type == 'application/pdf') {
	        return true;
        }
	    return false;
    }

};

var common = new commonApp();
common.load();