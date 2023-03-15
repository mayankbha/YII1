/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/30		Mayank Bhatnagar		33				Added code to fix some errors. Also Create Room popup to create room and add user in one popup itself.
 **************************************************************************
 */
 
var TimeAgo = (function() {
    var self = {};

    self.locales = {
        prefix: '',
        sufix:  'ago',

        seconds: 'less than a minute',
        minute:  'about a minute',
        minutes: '%d minutes',
        hour:    'about an hour',
        hours:   'about %d hours',
        day:     'a day',
        days:    '%d days',
        month:   'about a month',
        months:  '%d months',
        year:    'about a year',
        years:   '%d years'
    };

    self.inWords = function(timeAgo) {
        var u = new Date(timeAgo*1000);

        var year = u.getUTCFullYear();
        var month = u.getUTCMonth() - 1;
        month = self.addZeroPrefix(month);
        var date = u.getUTCDate();
        date = self.addZeroPrefix(date);

        var hours = u.getHours();
        var minutes = u.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';

        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = self.addZeroPrefix(minutes);

        var strTime = month + '/' + date + '/' + year + ' ' + hours + ':' + minutes + ' ' + ampm;

        return strTime;
    };

    self.addZeroPrefix = function(number) {
        return number < 10 ? '0' + number : number;
    };

    return self;
}());

var ChatModel = function (config) {
    this.config = config;

    this.createMessage = function (room, message, file) {
        file = file || null;

        if (!this.config.message.create) {
            throw new Error('Configure url for "message.create" function');
        }

        return $.post(this.config.message.create, {room: room, message: message, file : file});
    };

    this.deleteMessage = function (id) {
        if (!this.config.message.delete) {
            throw new Error('Configure url for "message.delete" function');
        }

        return $.post(this.config.message.delete, {id: id});
    };

    this.getMessages = function (room) {
        if (!this.config.message.get) {
            throw new Error('Configure url for "message.get" function');
        }

        return $.get(this.config.message.get, {room: room});
    };

    this.createRoom = function (params) {
        if (!this.config.room.create) {
            throw new Error('Configure url for "message.create" function');
        }

        return $.post(this.config.room.create, params);
    };

    this.deleteRoom = function (id) {
        if (!this.config.room.delete) {
            throw new Error('Configure url for "message.delete" function');
        }

        return $.post(this.config.room.delete, {id: id});
    };

    this.getRooms = function () {
        if (!this.config.room.get) {
            throw new Error('Configure url for "message.get" function');
        }

        return $.get(this.config.room.get);
    };

    this.addUserToRoom = function (roomID, userID) {
        if (!this.config.room.addUser) {
            throw new Error('Configure url for "room.addUser" function');
        }

        return $.post(this.config.room.addUser, {room_id: roomID, user_id: userID});
    };

    this.inviteUser = function (userID) {
        if (!this.config.room.inviteUser) {
            throw new Error('Configure url for "room.inviteUser" function');
        }

        return $.post(this.config.room.inviteUser, {user_id: userID});
    };

    this.getUserList = function (room) {
        if (!this.config.room.userList) {
            throw new Error('Configure url for "room.userList" function');
        }

        return $.post(this.config.room.userList, {room: room});
    };

    this.updateUserRights = function (serialize) {
        if (!this.config.room.updateRights) {
            throw new Error('Configure url for "room.updateRights" function');
        }

        return $.post(this.config.room.updateRights, serialize);
    };

    this.removeUserFromRoom = function (room, user) {
        if (!this.config.room.removeUser) {
            throw new Error('Configure url for "room.removeUser" function');
        }

        return $.post(this.config.room.removeUser, {room_id: room, user_id: user});
    };
};

var ChatObject = function (config) {
    var RIGHT_R = 'R';
    var RIGHT_U = 'U';
    var RIGHT_N = 'N';
    var RIGHT_Z = 'Z';

    this.model =  new ChatModel(config);
    this.activeRoom = null;
    this.activeRoomOwner = null;
    this.activeRoomRights = null;
    this.activeRoomType = null;

    this.messageBlock = $('.messages');
    this.messageHeader = $('.contact-profile');
    this.messageFooter = $('.message-input');
    this.messageFooterForm = $('#message-input-form');

    this.roomBlock = $('.chat-contacts');
    this.roomCreate = $('.create-room-button');
    this.inviteUser = $('.invite-user-button');
    this.roomAddUser = $('.add-user-button');

    this.getMessagesInterval = null;

    this.init = function () {
        this.bindLoadEvents();
        this.getRooms();

        this.getMessagesInterval = setInterval(this.getMessages, config.common.interval);
        setInterval(this.getRooms, config.common.interval);

		//var myDropzone = new Dropzone("#new-file-uploader");

		/*$(document).ready(function () {
			Dropzone.autoDiscover = false;

            $("div#new-file-uploader").dropzone({
				url: "/file/post",
			});
        });*/
    };

    this.bindLoadEvents = function () {
        var me = this,
            userList = new Bloodhound({
                datumTokenizer: Bloodhound.tokenizers.obj.whitespace('account_name'),
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                remote: {
                    url: me.model.config.userList + '?name=%QUERY',
                    wildcard: '%QUERY'
                }
            });

        var chatSearchInput = $('.js-search-user');

        chatSearchInput.typeahead(null, {
            display: 'account_name',
            source: userList,
            templates: {
                notFound: '<div class="text-danger" style="padding: 0 8px">Can\'t find user</div>'
            }
        });

        chatSearchInput.bind('typeahead:select', function(ev, suggestion) {
            var data = suggestion.id;
            if (data.id) {
                me.model.inviteUser(data.id).done(function (data) {
                    if(data.status == 'success') {
                        $('.js-search-user').val('');
                    }
                });
            }
        });

		$('body').on('hidden.bs.popover', function (e) {
			$(e.target).data("bs.popover").inState.click = false;
		});

        $(document)
            .on('click', '.upload-arrow-button:not(.is-completed, .is-active)', function () {
                var t = $(this),
                    family = t.attr('data-family'),
                    category = t.attr('data-category'),
                    fileName = t.attr('data-file-name'),
                    originalFileName = t.attr('data-original-file-name');

                $.ajax({
                    type: 'POST',
                    cache: false,
                    url: me.model.config.file.uploadInitUrl,
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
            .on('click', '.download-file-link:not(".is-cached, .is-active")', function (e) {
                e.preventDefault();
                //clearInterval(me.getMessagesInterval);

                var t = $(this),
                    pk = t.attr('data-pk');

                $.ajax({
                    type: 'GET',
                    cache: false,
                    url: me.model.config.file.downloadInitUrl,
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
                        setInterval(me.getMessages, me.model.config.common.interval);
                    },
                    error: function (data) {
                        $('.info-place span').addClass('danger').html(data.responseJSON.message);
                        t.removeClass('is-active');
                        setInterval(me.getMessages, me.model.config.common.interval);
                    }
                });
            })
            .on('click', '.chat-contacts .contact', function (event) {
                if (event.target.classList.contains('js-delete-room')) {
                    return false;
                }

                me.activeRoom = $(this).attr('data-room-id');
                me.activeRoomOwner = $(this).attr('data-room-owner');
                me.activeRoomType = $(this).attr('data-room-type');

                me.messageHeader.removeClass('hidden');
                me.messageFooter.removeClass('hidden');


                $('.chat-contacts .contact').removeClass('active');

                var roomName = me.getRoomTypeIcon(me.activeRoomType) + $(this).attr('data-room-name');
                $('.room-name').html(roomName);
                $(this).addClass('active');

                if (
                 me.activeRoomOwner == config.user &&
                 $(this).attr('data-room-type') == 'RoomType.Group'
                ) {
                    me.roomAddUser.removeClass('hidden');
                } else {
                    me.roomAddUser.addClass('hidden');
                }

                me.getUserList();
                me.getMessages(true);
            })
            .on('submit', '#create-room-form', function (event) {
                event.preventDefault();

                var roomObject = $(this),
                    serialize = roomObject.serialize();
                me.model.createRoom(serialize).done(function (data) {
                    if (data.status == 'success') {
                        me.roomCreate.popover('hide');
                        return data.pk;
                    }
                });
            })
            .on('click', ' .js-delete-room', function () {
                var id = $(this).parent('li').attr('data-room-id');
                if (id && confirm('Do you want to delete this room?')) {
                    me.model.deleteRoom(id).done(function (data) {
                        if (data.status == 'success' && me.activeRoom == id) {
                            me.messageHeader.addClass('hidden');
                            me.messageFooter.addClass('hidden');

                            me.messageBlock.html('');
                            me.activeRoom = null;
                        }
                    });
                }
            })
            .on('submit', '.settings-user-room-form', function (event) {
                event.preventDefault();

                var roomSettingsObject = $(this),
                    serialize = roomSettingsObject.serialize();

                if (serialize) {
                    serialize += '&room=' + me.activeRoom;
                    me.model.updateUserRights(serialize).done(function (data) {
                        if (data.status == 'success') {
                            roomSettingsObject.parents('.popover').prev('.list-group-item').popover('hide');
                            me.getUserList();
                        }
                    });
                }
            })
            .on('click', '.remove-user-button', function (event) {
                event.preventDefault();

                var removeUserButton = $(this),
                    userID = removeUserButton.attr('data-user-id');

                if (confirm('Do you want remove user from this room')) {
                    me.model.removeUserFromRoom(me.activeRoom, userID).done(function (data) {
                        if (data.status == 'success') {
                            removeUserButton.parents('.popover').prev('.list-group-item').popover('hide');
                            me.getUserList();
                        }
                    });
                }
            })
            .on('click', function (e) {
                var target = $(e.target);
                var popover = target.parents('.popover');
                if (target.attr('role') == 'option' || popover.length != 0) {
                    return;
                }

                var parent;
                $('.popover.in').each(function () {
                    parent = target.parent('[aria-describedby*="popover"]');
                    if (parent.attr('aria-describedby') == $(this).attr('id')) {
                        return;
                    }

                    if (target.attr('aria-describedby') == $(this).attr('id')) {
                        return;
                    }

                    $(this).popover('hide');
                 });
            });

        me.messageFooterForm.on('keydown keypress', function (event) {
            var keyCode = event.keyCode || event.which;
            if(keyCode == 13 && !event.shiftKey){
                event.preventDefault();
                me.sendMessage();
            }
        });

        me.messageFooter.on('submit', function (event) {
            event.preventDefault();
            me.sendMessage();
        });

        me.roomAddUser.popover({
            html: true,
            title: 'Add user',
            content: '<input class="typeahead form-control search-user-input" type="text" placeholder="User name">',
            placement: 'bottom'
        });

        me.roomAddUser.on('shown.bs.popover', function () {
            var input = $('.search-user-input');

            input.typeahead(null, {
                display: 'account_name',
                source: userList,
                templates: {
                    notFound: '<div class="text-danger" style="padding: 0 8px">Can\'t find user</div>'
                }
            });

            input.bind('typeahead:select', function(ev, suggestion) {
				var data = suggestion.id;
                if (data.id) {
                    me.model.addUserToRoom(me.activeRoom, data.id).done(function (data) {
                        if(data.status == 'success') {
                            me.roomAddUser.popover('hide');
                            me.getUserList();
                        }
                    });
                }
            });
        });

        me.roomCreate.popover({
            html: true,
            title: 'Create room',
            content: '' +
                '<form id="create-room-form">' +
                    '<input class="form-control" name="room_name" placeholder="Name">' +
                    '<br>' +
                    '<span class="list-group-item" style="border: 0px; padding: 0px; margin-bottom: 0px;">Add User</span>' +
                    '<input class="form-control typehead search-multiple-user-input" type="text" placeholder="User name">' +
                        '<div class="room-multiple-members list-group list-group-horizontal" ' +
                            'style="padding: 0px; padding-left: 0px; margin-bottom: 0px;">' +
                            '<span class="room-multiple-members-wrap"></span>' +
                        '</div>' +
                    '<button class="btn btn-primary" type="submit" style="margin-top: 20px;">Create</button>' +
                '</form>',
            placement: 'bottom'
        });

		me.roomCreate.on('shown.bs.popover', function () {
			var users = [];
			var count = 0;
            var input = $('.search-multiple-user-input');
			var wrapper = $('.room-multiple-members .room-multiple-members-wrap');
			wrapper.html('');

            input.typeahead(null, {
                display: 'account_name',
                source: userList,
                templates: {
                    notFound: '<div class="text-danger" style="padding: 0 8px">Can\'t find user</div>'
                }
            });

            input.bind('typeahead:select', function(ev, suggestion) {
				var wrapper = $('.room-multiple-members .room-multiple-members-wrap');
				var me = this;

				var data = suggestion.id;
				var user_name = suggestion.account_name;

                if (data.id) {
					if(users.indexOf(data.id) === -1) {
						users.push(data.id);
						count++;
					}

					if ((config.user != data.id) && (count == 1)) {
						var span = $('<span />', {
							'class': 'list-group-item',
							'id': 'delete_user_'+data.id,
							'html': '<input type="hidden" name="user_ids[]" value="'+data.id+'" /><a href="javascript: void(0);" onclick="deleteRoomUser('+data.id+');">'+user_name+'</a>',
						});

						count = 0;

						input.typeahead('val', '');

						return span.appendTo(wrapper);
                    }

					input.typeahead('val', '');
                }
            });
        });
    };

    this.addMessageToContainer = function (object) {
        var temp_message = object.message.replace(/\n/gi, '<br>');

        var message =  '<p>' + temp_message + '</p>';
        if (object.file_id) {
            message =  '<a href="#" class="message-with-file download-file-link" ' +
                'data-pk="'+object.file_id+'">' +
                '<i class="glyphicon glyphicon-file"></i> ' + temp_message + '</a>';
        }

        var delete_msg = '';
        if(config.user == object.sender) {
            delete_msg = '<a href="javascript: void(0);" ' +
                'alt="Delete" title="delete" data-toggle="tooltip" style="background: none;" ' +
                'data-pk="' + object.id + '" onclick="deleteMessage(' + object.id + ');">' +
                '<i class="glyphicon glyphicon-trash"></i>' +
                '</a>';
        }

        var user = object.account_name ? object.account_name : object.user_name;
        var ul = this.messageBlock.find('ul'),
            timeBlock = ' <time class="message-time">' + TimeAgo.inWords(object.message_time) + '</time>',
            li = $('<li />', {
                class: (config.user == object.sender) ? 'replies' : 'sent',
                html: '<div class="user-name">' + user + timeBlock + delete_msg + '</div>' +
                message
            });

        if (ul.length == 0) {
            return $('<ul />', {html: li}).appendTo(this.messageBlock);
        }

        return li.appendTo(ul);
    };

    this.addParticipantListToContainer = function (object) {
        var wrapper = this.messageHeader.find('.room-members-wrap');

        var user = object.account_name ? object.account_name : object.user_name;
        var span = '';
        if (user) {
            span = this.addUserListToContainer(object);
        } else {
            span = this.addGroupListToContainer(object);
        }

        return span.appendTo(wrapper);
    };

    this.uploadFileFragment = function (object, fileName, initResponse, offset, chunk, _this, file) {
        var me = this,
            progressObject = object.prev('.dropzone').find('.dz-progress'),
            present;

        $.ajax({
            type: 'POST',
            cache: false,
            url: me.model.config.file.uploadFragmentUrl,
            data: {pk: initResponse['file_container_pk'], file_name: fileName, offset: offset, chunk: chunk},
            success: function (data) {
                if (data.status == 'completed') {
                    present = Math.round(data.response.offset * 100 / parseInt(data.response.size));
                    present = (present > 100) ? 100 : present;
                    progressObject.css('opacity', 1).find('.dz-upload').css('width', present + '%');

                    me.uploadFileFragment(object, fileName, initResponse, data.response.offset, data.response.chunk, _this, file);
                } else if (data.status == 'success') {
                    me.uploadFileFinish(object, initResponse, _this, file);
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

    this.uploadFileFinish = function (object, initResponse, _this, file) {
        var me = this;

        $.ajax({
            type: 'POST',
            cache: false,
            url: me.model.config.file.uploadFinishUrl,
            data: {pk: initResponse['file_container_pk']},
            success: function (data) {
                if (data.status == 'success') {
                    me.uploadSuccessHelper(object, initResponse['file_container_pk'], _this, file);
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

    this.uploadSuccessHelper = function (object, pk, _this, file) {
        var me = this;

        var fileName = $('#upload-modal').find('span[data-dz-name]').text();
        me.model.createMessage(me.activeRoom, fileName, pk).done(function (data) {
			/*var uploadArrowButton = $('.upload-setting-block').find('.upload-arrow-button');
			uploadArrowButton.attr('data-file-name', '');
			uploadArrowButton.attr('data-original-file-name', '');
            uploadArrowButton.parent().find('input[type=\"hidden\"]').val('');

			$('.upload-setting-block').find('.dz-preview').remove();
			$('.upload-setting-block').find('#file-uploader').removeClass('dz-max-files-reached');*/

			$('.upload-setting-block').find('.dz-message').show();

            $('#upload-modal').modal('hide');

			_this.removeFile(file);

            if (data.status == 'success') {
                me.getMessages();
            }
        });
    };

    this.downloadFileFragment = function (object, pk, initResponse, offset) {
        var me = this,
            present;

        $.ajax({
            type: 'GET',
            cache: false,
            url: me.model.config.file.downloadFragmentUrl,
            data: {pk: pk, file_name: initResponse.name, offset: offset},
            success: function (data) {
                if (data.status == 'completed') {
                    present = Math.round(data.response.offset * 100 / parseInt(initResponse.size));
                    present = (present > 100) ? 100 : present;
                    object.find('.progress-inner').css('width', present + '%');

                    me.downloadFileFragment(object, pk, initResponse, data.response.offset);
                } else if (data.status == 'success') {
                    me.downloadFileFinish(object, initResponse);
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

    this.downloadFileFinish = function (object, initResponse) {
        var me = this;

        $.ajax({
            type: 'GET',
            cache: false,
            url: me.model.config.file.downloadFinishUrl,
            data: {file_name: initResponse.name, file_size: initResponse.size, file_hash: initResponse.hash_hex},
            success: function (data) {
                if (data.status == 'success') {
                    me.downloadSuccessHelper(object, initResponse.original_name, data.response.url);
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

    this.downloadSuccessHelper = function (object, originalName, url) {
        object.removeClass('is-active').addClass('is-cached');
        object.find('.container-text-inner').text('Download file');
        object.attr('href', url);
        object.attr('download', originalName)

        object[0].click();
    };

    this.addGroupListToContainer = function (object) {
        var span = $('<span />', {
            'class': 'list-group-item',
            'html': object.access_group,
        });

        return span;
    };

    this.addUserListToContainer = function (object) {
        if (object.user == config.user) {
            this.activeRoomRights = object.rights;
            this.changeRoomRights();
        }
        var user = object.account_name ? object.account_name : object.user_name;
        var span = $('<span />', {
            'class': 'list-group-item',
            'html': user,
        });

        if (this.activeRoomOwner == config.user) {
            switch (object.rights) {
                case RIGHT_Z:
                    span.css('color', 'blue');
                    break;
                case RIGHT_U:
                    span.css('color', 'green');
                    break;
                case RIGHT_R:
                    span.css('color', 'orange');
                    break;
                default:
                    span.css('color', 'red');
                    break;
            }

            span.popover({
                html: true,
                title: user + ' rights in this room',
                content: '' +
                '<form class="settings-user-room-form">' +
                '<input type="hidden" name="user" value="' + object.user + '">' +
                '<label><input type="radio" name="rights" value="' + RIGHT_U + '" ' + ((object.rights == RIGHT_U) ? 'checked' : '') + '> Full rights</label><br />' +
                '<label><input type="radio" name="rights" value="' + RIGHT_R + '" ' + ((object.rights == RIGHT_R) ? 'checked' : '') + '> Only read</label><br />' +
                '<label><input type="radio" name="rights" value="' + RIGHT_N + '" ' + ((object.rights == RIGHT_N) ? 'checked' : '') + '> Blacklist</label><br />' +
                '<button class="btn btn-sm btn-primary" type="submit">Change</button> ' +
                '<button class="btn btn-sm btn-danger remove-user-button" data-user-id="' + object.user + '">Remove</button>' +
                '</form>',
                placement: 'bottom'
            });
        }

        return span;
    };

    this.addRoomToContainer = function (object) {
        var searchClass = 'user-rooms-container';
        if (this.isSystemRoomType(object.room_type)) {
            searchClass = 'system-rooms-container';
        }
        var ul = this.roomBlock.find('ul.' + searchClass);
        var roomTypeIcon = this.getRoomTypeIcon(object.room_type);
        var li = $('<li />', {
            'class': 'contact ' + ((this.activeRoom == object.room) ? 'active' : ''),
            'html': '<p>' + roomTypeIcon + object.room_name + ((object.count > 0) ? ' <span class="label label-success">' + object.count + '</span>' : '') + '</p>',
            'data-room-id': object.room,
            'data-room-owner': object.owner,
            'data-room-name': object.room_name,
            'data-room-type': object.room_type,
        });

        if (!this.isSystemRoomType(object.room_type)) {
            var icon = $('<i />', {
                'class': 'glyphicon glyphicon-trash js-delete-room',
                'title': 'Delete room'
            });
            icon.appendTo(li);
        }

        if (ul.length == 0) {
            var chatRoomLabel = $('<li />', {
                'class': 'text-muted ml-10',
                'html': '<p>' + (this.isSystemRoomType(object.room_type) ? 'System Rooms' : 'User Rooms') + '</p>'
            });

            ul = $('<ul />', {html: chatRoomLabel, class: searchClass});
            ul.appendTo(this.roomBlock);
        }

        return li.appendTo(ul);
    };

    this.getRoomTypeIcon = function (roomType) {
        var roomTypeIcon = '';
        if (roomType == 'RoomType.Private') {
            roomTypeIcon = '<i class="glyphicon glyphicon-user chat-icon"></i>';
        } else if (roomType == 'RoomType.Group') {
            roomTypeIcon = '<i class="fa fa-users chat-icon"></i>';
        } else if (roomType == 'RoomType.SystemUser') {
            roomTypeIcon = '<i class="fa fa-address-card chat-icon" aria-hidden="true"></i>';
        } else if (roomType == 'RoomType.SystemGroup') {
            roomTypeIcon = '<i class="fa fa-object-group chat-icon" aria-hidden="true"></i>';
        }
        return roomTypeIcon;
    };

    this.isSystemRoomType = function (roomType) {
        if (roomType == 'RoomType.SystemGroup' || roomType == 'RoomType.SystemUser') {
            return true;
        }
        return false;
    };

    this.getMessages = function (scrolling) {
        var me = this;

        if (!this.activeRoom) {
            return false;
        }

        this.model.getMessages(this.activeRoom).done(function (data) {
            me.messageBlock.html('');

            if (data.status == 'success') {
                //todo: hide form
                me.activeRoomRights = data.rights;
                me.changeRoomRights();
                $.each(data.list, function(i, item) {
                    me.addMessageToContainer(item);
                });

                var liHeight = 0;
                $.each(me.messageBlock.find('li'), function(i, item) {
                    liHeight += $(item).outerHeight(true);
                    if (liHeight > me.messageBlock[0].scrollHeight) {
                        return false;
                    }
                });

                if (liHeight < me.messageBlock[0].scrollHeight) {
                    me.messageBlock.find('ul').css('margin-top', me.messageBlock[0].scrollHeight - liHeight);
                } else if (scrolling) {
                    me.messageBlock.animate({ scrollTop: me.messageBlock[0].scrollHeight}, "fast");
                }
            } else {
                me.messageBlock.html('<h3 class="text-center"><span class="text-capitalize text-danger">' + data.status + ':</span> <small>' + data.message + '</small></h3>');
            }
        });
    };

    this.sendMessage = function () {
        var me = this;
        var input = $('textarea[name="message"]');
        var message = input.val();

        if (message && me.activeRoom) {
            input.val('');
            this.model.createMessage(me.activeRoom, message).done(function (data) {
                if (data.status == 'success') {
                    me.getMessages();
                }
            });
        }
    };

    this.getUserList = function () {
        var me = this;

        if (!this.activeRoom) {
            return false;
        }

        if (me.activeRoomType == 'RoomType.Private') {
            var wraper = $('.room-members-wrap');
            if (wraper.length) {
                wraper.empty();
            }
            return false;
        }

        this.model.getUserList(this.activeRoom).done(function (data) {
            if (data.status == 'success') {
                me.messageHeader.find('.room-members-wrap').html('');
                $.each(data.list, function(i, item) {
                    if (item.user != me.model.config.user) {
                        me.addParticipantListToContainer(item);
                    }
                });
            }
        });
    };

    this.getRooms = function () {
        var me = this;

        this.model.getRooms().done(function (data) {
            if (data.status == 'success') {
                me.roomBlock.html('');
                $.each(data.list, function(i, item) {
                    me.addRoomToContainer(item);
                });
            }
        });
    };

    this.changeRoomRights = function () {
        var input = this.messageFooter.find('textarea[name="message"]'),
            button = this.messageFooter.find('button[type="submit"]');

        switch (this.activeRoomRights) {
            case RIGHT_U:
            case RIGHT_Z:
                input.prop('disabled', false).attr('placeholder', 'Write your message...');
                button.prop('disabled', false);
                break;
            case RIGHT_R:
                input.prop('disabled', true).attr('placeholder', 'You can\'t write messages in this room!');
                button.prop('disabled', true);
                break;
            default:
                input.prop('disabled', true).attr('placeholder', 'You are blocked in this room!');
                button.prop('disabled', true);
                break;
        }
    };

	this.deleteRoomUser = function (user_id) {
		var con = confirm('Are you sure you want to remove?');

		if(con)
			$('#delete_user_'+user_id).remove();
	};

    this.getCaret = function (el) {
        if (el.selectionStart) {
            return el.selectionStart;
        } else if (document.selection) {
            el.focus();

            var r = document.selection.createRange();

            if (r == null) {
                return 0;
            }

            var re = el.createTextRange(),
            rc = re.duplicate();
            re.moveToBookmark(r.getBookmark());
            rc.setEndPoint('EndToStart', re); 

            return rc.text.length;
        }

        return 0;
    };

    this.deleteMessage = function (id) {
        var me = this;

        if (!this.activeRoom) {
            return false;
        }

        var con = confirm('Are you sure you want to remove?');

        if(con) {
            this.model.deleteMessage(id).done(function (data) {
                if (data.status == 'success') {
                    me.getMessages(true);
                }
            });
        }
    };

    return this.init();
};