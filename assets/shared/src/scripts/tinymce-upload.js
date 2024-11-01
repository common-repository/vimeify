// Copyright Darko Gjorgjijoski <info@codeverve.com>
// 2020. All Rights Reserved.
// This file is licensed under the GPLv2 License.
// License text available at https://opensource.org/licenses/gpl-2.0.php

(function ($) {

    /**
     * Create shortcode
     * @param data
     * @returns {string}
     */
    var create_shortcode = function (data) {

        var output = Vimeify_MCE_Config.markup;
        output = output.replace('{id}', data.uri);
        output = output.replace('{url}', 'https://vimeo.com/' + data.uri);

        return output;
    };

    /**
     * Insert in the current editor
     * @param data
     */
    var insert_in_editor = function (data) {
        if (!window.hasOwnProperty('currentEditor')) {
            return;
        }
        window.currentEditor.insertContent(create_shortcode(data));
    }

    tinymce.PluginManager.add('vimeify_vimeo_button', function (editor, url) {
        var tmpEditor = editor;
        var params = {
            text: Vimeify_MCE_Config.phrases.tmce_title,
            classes: 'ed_button button button-small',
            tooltip: Vimeify_MCE_Config.phrases.tmce_tooltip,
            onclick: function () {
                window.currentEditor = tmpEditor;
                var uploadModal = new WPVimeoVideos.UploaderModal('tinymce', {
                    source: $('body').hasClass('wp-admin') ? 'Backend.Editor.Classic' : 'Frontend.Editor.Classic'
                });
                uploadModal.open();
            }
        };
        if (Vimeify_MCE_Config.icon) {
            params.image = Vimeify_MCE_Config.icon_url;
        }
        editor.addButton('vimeify_vimeo_button', params);
    });

    $(window).on('wpvimeify.events.insert', function (e, data) {
        if (data.context === 'tinymce') {
            insert_in_editor(data);
        }
    })
    $(window).on('wpvimeify.events.upload', function (e, data) {
        if (data.context === 'tinymce') {
            insert_in_editor(data);
        }
    })

})(jQuery);
