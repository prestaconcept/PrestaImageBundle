const bootstrap = require('bootstrap');
const CropperJS = require('cropperjs');

(function(w, $) {

    'use strict';

    const Cropper = function($el, modalV5 = false) {
        this.$el = $el;
        this.options = $.extend({}, $el.data('cropper-options'));

        this
            .initElements(modalV5)
            .initLocalEvents()
            .initRemoteEvents()
            .initCroppingEvents()
        ;
    };

    Cropper.prototype.initElements = function(modalV5) {
        this.$modal = this.$el.find('.modal');
        this.$aspectRatio = this.$modal.find('input[name="cropperAspectRatio"]');
        this.$rotator = this.$modal.find('.rotate');
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

        this.options = $.extend(this.options, {
            aspectRatio: this.$aspectRatio.val()
        });

        this.cropper = null;
        this.modal = modalV5 ? new bootstrap.Modal(this.$modal) : undefined;

        return this;
    };

    Cropper.prototype.initLocalEvents = function() {
        const self = this;

        // map virtual upload button to native input file element
        this.$local.$btnUpload.on('click', function() {
            self.$local.$input.trigger('click');
        });

        // start cropping process on input file "change"
        this.$local.$input.on('change', function() {
            const reader = new FileReader();

            // show a croppable preview image in a modal
            reader.onload = function(e) {
                self.prepareCropping(e.target.result);

                // clear input file so that user can select the same image twice and the "change" event keeps being triggered
                self.$local.$input.val('');
            };

            // trigger "reader.onload" with uploaded file
            reader.readAsDataURL(this.files[0]);
        });

        return this;
    };

    Cropper.prototype.initRemoteEvents = function() {
        const self = this;

        const $btnUpload = this.$remote.$btnUpload;
        const $uploadLoader = this.$remote.$uploadLoader;

        // handle distant image upload button state
        this.$remote.$input.on('change, input', function() {
            const url = $(this).val();

            self.$remote.$btnUpload.prop('disabled', url.length <= 0 || url.indexOf('http') === -1);
        });

        // start cropping process get image's base64 representation from local server to avoid cross-domain issues
        this.$remote.$btnUpload.on('click', function() {
            $btnUpload.hide();
            $uploadLoader.removeClass('hidden d-none');
            $.ajax({
                url: $btnUpload.data('url'),
                data: {
                    url: self.$remote.$input.val()
                },
                method: 'post'
            }).done(function(data) {
                self.prepareCropping(data.base64);
                $btnUpload.show();
                $uploadLoader.addClass('hidden d-none');
            });
        });

        return this;
    };

    Cropper.prototype.initCroppingEvents = function() {
        const self = this;

        // handle image cropping
        this.$modal.find('[data-method="getCroppedCanvas"]').on('click', function() {
            self.crop();
        });

        // handle "aspectRatio" switch
        this.$aspectRatio.on('change', function() {
            self.cropper.setAspectRatio($(this).val());
        });

        this.$rotator.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            self.cropper.rotate($(this).data('rotate'));
        });

        return this;
    };

    /**
     * Open cropper "editor" in a modal with the base64 uploaded image.
     */
    Cropper.prototype.prepareCropping = function(base64) {
        const self = this;

        // clean previous croppable image
        if (this.cropper) {
            this.cropper.destroy();
            this.$container.$preview.children('img').remove();
        }

        // reset "aspectRatio" buttons
        this.$aspectRatio.each(function() {
            const $this = $(this);

            if ($this.val().length <= 0) {
                $this.trigger('click');
            }
        });

        this.$modal.each((index, element) => {
            const rebuildCroppableImage = () => {
                $('<img>')
                    .attr('src', base64)
                    .on('load', function() {
                        self.cropper = new CropperJS(this, self.options)
                    })
                    .appendTo(self.$container.$preview)
                ;
            }

            // support for bootstrap < 5
            $(element).one('shown.bs.modal', rebuildCroppableImage);

            // support for bootstrap >= 5
            element.addEventListener('shown.bs.modal', rebuildCroppableImage, {once: true})
        })

        if (this.modal) {
            this.modal.show();
        } else {
            this.$modal.modal('show');
        }
    };

    /**
     * Create canvas from cropped image and fill in the hidden input with canvas base64 data.
     */
    Cropper.prototype.crop = function() {
        const data = this.cropper.getData(),
            image_width = Math.min(this.$el.data('max-width'), data.width),
            image_height = Math.min(this.$el.data('max-height'), data.height),
            preview_width = Math.min(this.$container.$canvas.data('preview-width'), data.width),
            preview_height = Math.min(this.$container.$canvas.data('preview-height'), data.height),

            // TODO: getCroppedCanvas seams to only consider one dimension when calculating the maximum size
            // in respect to the aspect ratio and always considers width first, so height is basically ignored!
            // To set a maximum height, no width parameter should be set.
            // Example of current wrong behavior:
            // source of 200x300 with resize to 150x200 results in 150x225 => WRONG (should be: 133x200)
            // source of 200x300 with resize to 200x150 results in 200x300 => WRONG (should be: 100x150)
            // This is an issue with cropper, not this library
            preview_canvas = this.cropper.getCroppedCanvas({
                width: preview_width,
                height: preview_height
            }),
            image_canvas = this.cropper.getCroppedCanvas({
                width: image_width,
                height: image_height
            });

        // fill canvas preview container with cropped image
        this.$container.$canvas.html(preview_canvas);

        // fill input with base64 cropped image
        this.$input.val(image_canvas.toDataURL(this.$el.data('mimetype'), this.$el.data('quality')));

        // hide the modal
        if (this.modal) {
            this.modal.hide();
        } else {
            this.$modal.modal('hide');
        }
    };

    if (typeof module !== 'undefined' && 'exports' in module) {
        module.exports = Cropper;
    } else {
        window.Cropper = Cropper;
    }

})(window, jQuery);
