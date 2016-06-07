/* global FileReader */
/* global Routing */
(function(w, $){

    'use strict';

    /**
     * @param {jQuery} $el
     * @constructor
     */
    var Cropper = function($el) {
        this.$el = $el;
        this.options = {
            autoCropArea: 1
        };

        this
            .initElements()
            .initLocalEvents()
            .initRemoteEvents()
            .initCroppingEvents()
        ;
    };

    /**
     * @returns {Cropper}
     */
    Cropper.prototype.initElements = function() {
        this.$modal = this.$el.find('.modal');
        this.$aspectRatio = this.$modal.find('input[name="cropperAspectRatio"]');
        this.$input = this.$el.find('input.cropper-base64');

        this.$container = {
            $preview: this.$modal.find('.cropper-preview'),
            $canvas: this.$el.find('.cropper-canvas-container')
        };

        this.$local = {
            $btnUpload: this.$el.find('.cropper-local button'),
            $input: this.$el.find('.cropper-local input[type="file"]')
        };

        this.$remote = {
            $btnUpload: this.$el.find('.cropper-remote button'),
            $uploadLoader: this.$el.find('.cropper-remote .remote-loader'),
            $input: this.$el.find('.cropper-remote input[type="url"]')
        };

        return this;
    };

    /**
     * @returns {Cropper}
     */
    Cropper.prototype.initLocalEvents = function() {
        var self = this;

        // map virtual upload button to native input file element
        this.$local.$btnUpload.on('click', function () {
            self.$local.$input.trigger('click');
        });

        // start cropping process on input file "change"
        this.$local.$input.on('change', function () {
            var reader = new FileReader();

            // show a croppable preview image in a modal
            reader.onload = function (e) {
                self.prepareCropping(e.target.result);

                // clear input file so that user can select the same image twice and the "change" event keeps being triggered
                self.$local.$input.val('');
            };

            // trigger "reader.onload" with uploaded file
            reader.readAsDataURL(this.files[0]);
        });

        return this;
    };

    /**
     * @returns {Cropper}
     */
    Cropper.prototype.initRemoteEvents = function() {
        var self = this;

        var $btnUpload = this.$remote.$btnUpload;
        var $uploadLoader = this.$remote.$uploadLoader;

        // handle distant image upload button state
        this.$remote.$input.on('change, input', function () {
            var url = $(this).val();

            self.$remote.$btnUpload.prop('disabled', url.length <= 0 || url.indexOf('http') === -1);
        });

        // start cropping process get image's base64 representation from local server to avoid cross-domain issues
        this.$remote.$btnUpload.on('click', function () {
            $btnUpload.hide();
            $uploadLoader.removeClass('hidden');
            $.ajax({
                url: Routing.generate('presta_image_url_to_base64'),
                data: {
                    url: self.$remote.$input.val()
                },
                method: 'post'
            }).done(function (data) {
                self.prepareCropping(data.base64);
                $btnUpload.show();
                $uploadLoader.addClass('hidden')();
            });
        });

        return this;
    };

    /**
     * @returns {Cropper}
     */
    Cropper.prototype.initCroppingEvents = function() {
        var self = this;

        // handle image cropping
        this.$modal.find('[data-method="getCroppedCanvas"]').on('click', function() {
            self.crop();
        });

        // handle "aspectRatio" switch
        self.$aspectRatio.on('change', function() {
            self.$container.$preview.children('img').cropper('setAspectRatio', $(this).val());
        });

        return this;
    };

    /**
     * Open cropper "editor" in a modal with the base64 uploaded image.
     *
     * @param {string} base64
     */
    Cropper.prototype.prepareCropping = function(base64) {
        var self = this;

        // clean previous croppable image
        this.$container.$preview.children('img').cropper('destroy').remove();

        // reset "aspectRatio" buttons
        this.$aspectRatio.each(function() {
            var $this = $(this);

            if ($this.val().length <= 0) {
                $this.trigger('click');
            }
        });

        this.$modal
            .one('shown.bs.modal', function() {
                // (re)build croppable image once the modal is shown (required to get proper image width)
                $('<img>')
                    .attr('src', base64)
                    .on('load', function() {
                        $(this).cropper(self.options);
                    })
                    .appendTo(self.$container.$preview);
            })
            .modal('show');
    };

    /**
     * Create canvas from cropped image and fill in the hidden input with canvas base64 data.
     */
    Cropper.prototype.crop = function() {
        var canvas = this.$container.$preview.children('img').cropper('getCroppedCanvas', {
            width: this.$container.$canvas.data('max-width'),
            height: this.$container.$canvas.data('max-height')
        });

        // fill canvas container with cropped image
        this.$container.$canvas.html(canvas);

        // fill input with base64 cropped image
        this.$input.val(canvas.toDataURL());

        // hide the modal
        this.$modal.modal('hide');
    };

    window.Cropper = Cropper;

})(window, jQuery);
