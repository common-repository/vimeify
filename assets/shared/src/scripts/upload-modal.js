// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

(function ($) {

    window.WPVimeoVideos.Hook_Type_Backend  = 1;
    window.WPVimeoVideos.Hook_Type_Frontend = 2;

    /**
     * Uploader modal
     *
     * @param context
     * @param config
     * @constructor
     */
    window.WPVimeoVideos.UploaderModal = function (context, config) {
        this.context = context;
        this.config = config ? config : {};
    };

    /**
     * Uploader modal open
     * @returns {string}
     */
    window.WPVimeoVideos.UploaderModal.prototype.form = function () {

        var opts = [];
        if (Vimeify_Modal_Config.enable_local_search && (!this.config.hasOwnProperty('local') || this.config.local)) {
            opts.push('<label><input type="radio" class="vimeify-field-row vimeify-insert-type" name="insert_type" value="local">' + Vimeify_Modal_Config.methods.local + '</label>');
        }
        if (Vimeify_Modal_Config.enable_vimeo_search && (!this.config.hasOwnProperty('search') || this.config.search)) {
            opts.push('<label><input type="radio" class="vimeify-field-row vimeify-insert-type" name="insert_type" value="search">' + Vimeify_Modal_Config.methods.search + '</label>')
        }

        var opts_str;
        var method_upload_style;
        if (opts.length === 0) {
            opts_str = '<input type="hidden" name="insert_type" value="upload">';
            method_upload_style = 'display:block;';
        } else {
            opts = ['<label><input type="radio" class="vimeify-field-row vimeify-insert-type" name="insert_type" value="upload">' + Vimeify_Modal_Config.methods.upload + '</label>'].concat(opts);
            opts_str = '<div class="vimeify-vimeo-form-row">' + opts.join("\n") + '</div>';
            method_upload_style = 'display: none;';
        }

        var source = this.config.hasOwnProperty('source') ? this.config.source : 'Default';
        var hooks = this.config.hasOwnProperty('hook_type') ? this.config.hook_type :  window.WPVimeoVideos.Hook_Type_Backend;

        var privacy_option = '';

        if (Vimeify_Modal_Config.upload_form_options.enable_view_privacy && null !== Vimeify_Modal_Config.upload_form_options.privacy_view) {

            var privacy_view_options = '';

            for (var key in Vimeify_Modal_Config.upload_form_options.privacy_view) {
                var name = Vimeify_Modal_Config.upload_form_options.privacy_view[key].name;
                var is_available = Vimeify_Modal_Config.upload_form_options.privacy_view[key].available;
                var is_default = Vimeify_Modal_Config.upload_form_options.privacy_view[key].default;
                var disabled = is_available ? '' : 'disabled';
                var selected = is_default ? 'selected' : '';
                privacy_view_options += '<option ' + disabled + ' ' + selected + ' value="' + key + '">' + name + '</option>';
            }

            privacy_option = '<div class="vimeify-vimeo-form-row">\n' +
                '<label for="vimeo_view_privacy">' + Vimeify_Modal_Config.words.privacy_view + '</label>' +
                '<select name="vimeo_view_privacy" class="vimeify-field-row vimeify-w-100">' + privacy_view_options + '</select>\n' +
                '</div>\n';
        }

        var modal_title = (this.config.hasOwnProperty('title') && this.config.title) ? this.config.title : Vimeify_Modal_Config.phrases.title;

        var data_attr = '';
        if (this.config.hasOwnProperty('meta') && this.config.meta) {
            data_attr = "data-meta='" + JSON.stringify(this.config.meta) + "'";
        }

        return '<div id="' + this.context + '" class="vimeify-vimeo-upload-form vimeify-vimeo-plain-form vimeify-text-left">\n' +
            '\n' +
            '    <span class="vimeify-close-modal">&#215;</span>\n' +
            '    <h4 class="vimeify-mt-0">' + modal_title + '</h4>\n' +
            '\n' + opts_str + '\n' +
            '    <div class="vimeify-insert-wrapper vimeify-insert-type-upload" style="' + method_upload_style + '">\n' +
            '        <form id="vimeify-vimeo-upload-modal" ' + data_attr + '>\n' +
            '            <input type="hidden" name="vimeo_source" value="'+source+'">'+
            '            <input type="hidden" name="vimeo_hook_type" value="'+hooks+'">'+
            '            <div class="vimeify-vimeo-form-row">\n' +
            '                <label for="vimeo_title">' + Vimeify_Modal_Config.words.title + '</label>' +
            '                <input type="text" name="vimeo_title" class="vimeify-field-row">\n' +
            '            </div>\n' +
            '            <div class="vimeify-vimeo-form-row">\n' +
            '                <label for="vimeo_description">' + Vimeify_Modal_Config.words.desc + '</label>' +
            '                <textarea rows="5" name="vimeo_description" class="vimeify-field-row"></textarea>\n' +
            '            </div>\n' + privacy_option +
            '            <div class="vimeify-vimeo-form-row">\n' +
            '                <label for="vimeo_video">' + Vimeify_Modal_Config.words.file + '</label>' +
            '                <input type="file" name="vimeo_video" class="vimeify-field-row">\n' +
            '            </div>\n' +
            '            <div class="vimeify-vimeo-form-row">\n' +
            '                <div class="vimeify-progress-bar" style="display: none;">\n' +
            '                    <div class="vimeify-progress-bar-inner" style="width: 0;"></div>\n' +
            '                    <div class="vimeify-progress-bar-value">0%</div>\n' +
            '                </div>\n' +
            '            </div>\n' +
            '            <div class="vimeify-vimeo-form-row">\n' +
            '                <div class="vimeify-loader" style="display: none;"></div>\n' +
            '                <button type="submit" class="button submitUpload" data-waiting="' + Vimeify_Modal_Config.words.uploading3d + '" data-finished="Upload">\n' +
            '                    Upload\n' +
            '                </button>\n' +
            '            </div>\n' +
            '        </form>\n' +
            '    </div>\n' +
            '\n' +
            '    <div class="vimeify-insert-wrapper vimeify-insert-type-local" style="display:none;">\n' +
            '        <div class="vimeify-vimeo-form-row">\n' +
            '            <select class="vimeify-vimeo-existing" style="display: none;"></select>\n' +
            '            <button type="button" name="insert_video" class="button-primary insert_video">' + Vimeify_Modal_Config.words.insert + '</button>\n' +
            '        </div>\n' +
            '        <div class="vimeify-vimeo-form-row vimeify-videos-404" style="display: none;">\n' +
            '            <p>' + Vimeify_Modal_Config.phrases.videos_not_found + '</p>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '\n' +
            '    <div class="vimeify-insert-wrapper vimeify-insert-type-search" style="display: none;">\n' +
            '        <form id="vimeify-vimeo-search" class="vimeify-vimeo-form-row">\n' +
            '            <div class="vimeify-vimeo-form-row">\n' +
            '                <input type="text" name="video_title" placeholder="' + Vimeify_Modal_Config.words.title + '" class="vimeify-field-row">\n' +
            '                <button type="submit" class="button" data-waiting="' + Vimeify_Modal_Config.words.searching3d + '" data-finished="' + Vimeify_Modal_Config.words.upload + '">' + Vimeify_Modal_Config.words.search + '</button>\n' +
            '            </div>\n' +
            '        </form>\n' +
            '        <div class="vimeify-vimeo-form-row vimeify-videos-found" style="display: none;">\n' +
            '            <select class="vimeify-vimeo-existing"></select>\n' +
            '            <button type="button" name="insert_video" class="button-primary insert_video">' + Vimeify_Modal_Config.words.insert + '</button>\n' +
            '        </div>\n' +
            '        <div class="vimeify-vimeo-form-row vimeify-videos-404" style="display: none;">\n' +
            '            <p>' + Vimeify_Modal_Config.phrases.search_not_found + '</p>\n' +
            '        </div>\n' +
            '    </div>\n' +
            '\n' +
            '</div><style>.swal2-container {z-index: 999999 !important;}</style>'
    }


    window.WPVimeoVideos.UploaderModal.prototype.open = function () {
        var form = this.form()
        swal.fire({
            showCloseButton: false,
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
            html: form,
        });
    }

})(jQuery);


(function ($) {

    window.VimeoUploaderModal = {working: false, uploader: null}

    var units = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    function niceBytes(x){
        var l = 0, n = parseInt(x, 10) || 0;
        while(n >= 1024 && ++l){
            n = n/1024;
        }
        return(n.toFixed(n < 10 && l > 0 ? 1 : 0) + ' ' + units[l]);
    }

    // Handle insert dropdown
    $(document).on('change', '.vimeify-insert-type', function (e) {

        var $form = $(this).closest('.vimeify-vimeo-upload-form')
        var context = $form.attr('id');

        $('.vimeify-insert-wrapper').hide();
        var $self = $(this);
        var value = $self.val();
        var $wrapper = $('.vimeify-insert-type-' + value);
        if (value === 'local') {
            $.ajax({
                url: Vimeify_Modal_Config.ajax_url + '?action=vimeify_get_uploads',
                data: {_wpnonce: Vimeify_Modal_Config.nonce},
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        var uploads = response.data.uploads;
                        var $elSelect = $('.vimeify-insert-type-local .vimeify-vimeo-existing');
                        var $el404 = $('.vimeify-insert-type-local .vimeify-videos-404');
                        if (uploads.length > 0) {
                            var options = '';
                            for (var i in uploads) {
                                options += '<option value="' + uploads[i].vimeo_id + '">' + uploads[i].title + '</option>';
                            }
                            $elSelect.append(options);
                            $elSelect.show();
                        } else {
                            var parent = $self.parent();
                            parent.hide();
                            $el404.show();
                        }
                    } else {
                        swal.fire(Vimeify_Modal_Config.words.sorry, response.data.message, 'error');
                    }

                },
                error: function () {
                    swal.fire(Vimeify_Modal_Config.words.sorry, Vimeify_Modal_Config.phrases.http_error, 'error');
                }
            });
        }
        $wrapper.show();
    });


    // Handle vimeo search
    $(document).on('submit', '#vimeify-vimeo-search', function () {

        var $form = $(this).closest('.vimeify-vimeo-upload-form')
        var context = $form.attr('id');

        var search_phrase = $(this).find('input[name=video_title]').val();
        var opts = '';
        if (search_phrase !== '') {
            var wrapperFound = $(this).closest('.vimeify-insert-type-search').find('.vimeify-videos-found');
            var wrapper404 = $(this).closest('.vimeify-insert-type-search').find('.vimeify-videos-404');
            var vimeoProfile = new WPVimeoVideos.Profile(Vimeify_Modal_Config.access_token);
            vimeoProfile.search({
                'page': 1,
                'per_page': 100,
                'query': search_phrase,
                'sort': 'date',
                'direction': 'desc',
                'onSuccess': function (response) {
                    if (response.data.length > 0) {
                        for (var i in response.data) {
                            opts += '<option value="' + response.data[i].uri + '">' + response.data[i].name + '</option>';
                        }
                        wrapper404.hide();
                        wrapperFound.find('.vimeify-vimeo-existing').html(opts);
                        wrapperFound.show();
                    } else {
                        wrapperFound.hide();
                        wrapper404.show();
                    }
                },
                'onError': function (response) {
                    swal.fire(Vimeify_Modal_Config.words.sorry, Vimeify_Modal_Config.phrases.invalid_search_phrase, 'error');
                }
            });
        } else {
            swal.fire(Vimeify_Modal_Config.words.sorry, Vimeify_Modal_Config.phrases.invalid_search_phrase, 'error');
        }
        return false;
    });

    // Handle vimeo insert
    $(document).on('click', '.insert_video', function (e) {
        e.preventDefault();

        var $form = $(this).closest('.vimeify-vimeo-upload-form')
        var context = $form.attr('id');

        var $selected = $(this).closest('.vimeify-vimeo-form-row').find('.vimeify-vimeo-existing');
        var uri = $selected.val();
        if (uri) {
            uri = uri.replace('/videos/', '');
            $(window).trigger('wpvimeify.events.insert', [{uri: uri, context: context}]);
            swal.close();
        }
    });

    // Handle vimeo upload
    $(document).on('submit', '#vimeify-vimeo-upload-modal', function (e) {

        var $form = $(this).closest('.vimeify-vimeo-upload-form')
        var context = $form.attr('id');

        var $self = $(this);
        var $loader = $self.find('.vimeify-loader');
        var $submit = $self.find('button[type=submit]');
        var $progressBar = $self.find('.vimeify-progress-bar');

        var formData = new FormData(this);
        var videoFile = formData.get('vimeo_video');
        var notify_meta = $(this).data('meta');
        var notify_endpoint_enabled = true;

        if (!WPVimeoVideos.Uploader.validateVideo(videoFile)) {
            swal.fire(Vimeify_Modal_Config.words.sorry, Vimeify_Modal_Config.phrases.upload_invalid_file, 'error');
            return false;
        }

        var title = formData.get('vimeo_title');
        var description = formData.get('vimeo_description');
        var privacy = formData.get('vimeo_view_privacy');
        var source = formData.get('vimeo_source') ? formData.get('vimeo_source') : 'UploadModal';
        var hook_type = formData.get('vimeo_hook_type') ? formData.get('vimeo_hook_type') : 1;
        if (!privacy) {
            privacy = Vimeify_Modal_Config.default_privacy;
        }
        var errorHandler = function ($eself, error) {
            var message = error;
            var type = 'error';
            swal.fire(Vimeify_Modal_Config.words.sorry, message, type);

        };
        var updateProgressBar = function ($pbar, value) {
            if ($pbar.is(':hidden')) {
                $pbar.show();
            }
            $pbar.find('.vimeify-progress-bar-inner').css({width: value + '%'})
            $pbar.find('.vimeify-progress-bar-value').text(value + '%');
        };

        const params = {
            action: 'vimeify_store_upload',
            source: source,
            hook_type: hook_type,
            _wpnonce: Vimeify_Modal_Config.nonce
        }
        const esc = encodeURIComponent;
        const query = Object.keys(params)
            .map(k => esc(k) + '=' + esc(params[k]))
            .join('&');

        window.VimeoUploaderModal.uploader = new WPVimeoVideos.Uploader(Vimeify_Modal_Config.access_token, videoFile, {
            'title': title,
            'description': description,
            'privacy': privacy,
            'wp': {
                'notify_endpoint': notify_endpoint_enabled ? (Vimeify_Modal_Config.ajax_url + '?' + query) : false,
                'notify_meta': notify_meta ? notify_meta : null,
            },
            'beforeStart': function () {
                $loader.css({'display': 'inline-block'});
                $submit.prop('disabled', true);
                window.VimeoUploaderModal.working = true;
            },
            'onProgress': function (bytesUploaded, bytesTotal) {
                var percentage = (bytesUploaded / bytesTotal * 100).toFixed(2);
                updateProgressBar($progressBar, percentage);
            },
            'onSuccess': function (response, currentUpload) {
                var type = response.success ? 'success' : 'error';
                var message = response.data.message;

                window.setTimeout(function () {
                    $self.get(0).reset();
                    $loader.css({'display': 'none'});
                    $submit.prop('disabled', false);
                    updateProgressBar($progressBar, 0);
                    $progressBar.hide();
                }, 1000);

                swal.fire(Vimeify_Modal_Config.words.success, message, type);
                var uri = currentUpload.uri;
                uri = uri.replace('/videos/', '');
                var data = {
                    name: currentUpload.hasOwnProperty('name') ? currentUpload.name : '',
                    description: currentUpload.hasOwnProperty('description') ? currentUpload.description : '',
                    context: context,
                    uri: uri,
                    full_uri: currentUpload.uri,
                    size_formatted: niceBytes(currentUpload.upload.size),
                    root: currentUpload
                }
                window.VimeoUploaderModal.working = false;
                $(window).trigger('wpvimeify.events.upload', [data])
            },
            'onError': function (error) {
                window.VimeoUploaderModal.working = false;
                errorHandler($self, error);
            },
            'onVideoCreateError': function (error) {
                window.VimeoUploaderModal.working = false;
                errorHandler($self, error);
            },
            'onWPNotifyError': function (error) {
                window.VimeoUploaderModal.working = false;
                errorHandler($self, error);
            }
        });
        window.VimeoUploaderModal.uploader.start();
        return false;
    });

    // Close the modal
    $(document).on('click', '.vimeify-close-modal', function (e) {
        e.preventDefault();
        if (window.VimeoUploaderModal.working) {
            if (confirm(Vimeify_Modal_Config.phrases.cancel_upload_confirm)) {
                window.VimeoUploaderModal.uploader.abort();
                window.VimeoUploaderModal.working = false;
                swal.close();
            }
        } else {
            swal.close();
        }
    });

})(jQuery);
