(function ($) {
    'use strict';

    if (typeof wp === 'undefined' || !wp.media) return;

    var frames = {};

    function openFrame(targetId) {
        if (frames[targetId]) {
            frames[targetId].open();
            return;
        }
        var frame = wp.media({
            title: 'Select Screenshot',
            button: { text: 'Use this image' },
            library: { type: 'image' },
            multiple: false
        });
        frame.on('select', function () {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#' + targetId).val(attachment.id);
            var previewSrc = attachment.sizes && attachment.sizes.medium
                ? attachment.sizes.medium.url
                : attachment.url;
            $('#' + targetId + '_preview').html(
                $('<img/>').attr('src', previewSrc).css({
                    maxWidth: '100%',
                    height: 'auto',
                    maxHeight: '200px'
                })
            );
            $('.mph-cs-remove[data-target="' + targetId + '"]').show();
        });
        frames[targetId] = frame;
        frame.open();
    }

    $(document).on('click', '.mph-cs-select', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        if (targetId) openFrame(targetId);
    });

    $(document).on('click', '.mph-cs-remove', function (e) {
        e.preventDefault();
        var targetId = $(this).data('target');
        if (!targetId) return;
        $('#' + targetId).val('');
        $('#' + targetId + '_preview').html(
            $('<span/>').css({ color: '#8c8f94' }).text('No image selected')
        );
        $(this).hide();
    });
})(jQuery);
