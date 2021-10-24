const CropperJS = require('cropperjs')

export default class {
    constructor(element) {
        this.options = JSON.parse(element.dataset.cropperOptions)
        this.cropper = null

        this.initializeElements(element)
        this.initializeFileUploadEvents()
        this.initializeRemoteUrlEvents()
        this.initializeCroppingEvents()
    }

    initializeElements(element) {
        const form = element.querySelector('.presta-image-form')
        const modal = element.querySelector('.presta-image-modal')

        this.elements = {
            form: {
                canvas: form.querySelector('.presta-image-form-canvas'),
                input: form.querySelector('input[type="hidden"].cropper-base64'),
                widgets: {
                    fileUpload: {
                        button: form.querySelector('.widget-file-upload button'),
                        input: form.querySelector('.widget-file-upload input[type="file"]'),
                    },
                    remoteUrl: {
                        button: form.querySelector('.widget-remote-url button'),
                        loader: form.querySelector('.widget-remote-url .loader'),
                        input: form.querySelector('.widget-remote-url input[type="url"]'),
                    },
                },
            },
            modal: {
                aspectRatios: modal.querySelectorAll('input[name="cropper_aspect_ratio"]'),
                closeButtons: modal.querySelectorAll('.close'),
                preview: modal.querySelector('.preview'),
                rotate: modal.querySelector('.rotate'),
                root: modal,
            },
            root: element,
        }

        this.options = Object.assign(this.options, {
            aspectRatio: Array.from(this.elements.modal.aspectRatios).filter(element => element.checked)[0].value
        })
    }

    initializeFileUploadEvents() {
        // map virtual upload button to native input file element
        this.elements.form.widgets.fileUpload.button.addEventListener('click', () => {
            this.elements.form.widgets.fileUpload.input.click()
        })

        this.elements.form.widgets.fileUpload.input.addEventListener('change', () => {
            const reader = new FileReader();

            // show a croppable preview image in a modal
            reader.onload = (event) => {
                this.prepareCropping(event.target.result);

                // clear input file so that user can select the same image twice and the "change" event keeps being triggered
                this.elements.form.widgets.fileUpload.input.value = ''
            };

            // trigger "reader.onload" with uploaded file
            reader.readAsDataURL(event.target.files[0])
        })
    }

    initializeRemoteUrlEvents() {
        // handle distant image upload button state
        this.elements.form.widgets.remoteUrl.input.addEventListener('change', (event) => {
            const url = event.currentTarget.value

            this.elements.form.widgets.remoteUrl.button.disabled = 0 === url.length || -1 === url.indexOf('http')
        });

        // start cropping process get image's base64 representation from local server to avoid cross-domain issues
        this.elements.form.widgets.remoteUrl.button.addEventListener('click', () => {
            this.elements.form.widgets.remoteUrl.button.classList.add('hidden')
            this.elements.form.widgets.remoteUrl.loader.classList.remove('hidden')

            const request = new XMLHttpRequest()
            request.open('POST', this.elements.form.widgets.remoteUrl.button.dataset.url, true)
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
            request.onreadystatechange = (event) => {
                if (event.currentTarget.readyState === XMLHttpRequest.DONE && event.currentTarget.status === 200) {
                    const response = JSON.parse(event.currentTarget.response)
                    this.prepareCropping(response.base64)
                }

                this.elements.form.widgets.remoteUrl.button.classList.remove('hidden')
                this.elements.form.widgets.remoteUrl.loader.classList.add('hidden')
            }
            request.send(`url=${this.elements.form.widgets.remoteUrl.input.value}`)
        })
    }

    initializeCroppingEvents() {
        // handle image cropping
        this.elements.modal.root.querySelector('[data-method="getCroppedCanvas"]').addEventListener('click', () => {
            this.crop()
        })

        // handle "aspectRatio" switch
        this.elements.modal.aspectRatios.forEach(element => {
            element.addEventListener('change', (event) => {
                this.cropper.setAspectRatio(event.currentTarget.value)
            })
        })

        // handle "rotate" action
        if (this.elements.modal.rotate) {
            this.elements.modal.rotate.addEventListener('click', (event) => {
                event.preventDefault()
                event.stopPropagation()

                this.cropper.rotate(event.currentTarget.dataset.rotate)
            })
        }

        // handle "close" action
        this.elements.modal.closeButtons.forEach(element => {
            element.addEventListener('click', () => {
                this.elements.modal.root.classList.add('hidden')
            })
        })
    }

    /**
     * Open cropper "editor" in a modal with the base64 uploaded image.
     */
    prepareCropping(base64) {
        // clean previous croppable image
        if (this.cropper) {
            this.cropper.destroy()
            this.elements.modal.preview.innerHTML = ''
        }

        // reset "aspectRatio" buttons
        this.elements.modal.aspectRatios.forEach(element => {
            if (0 === element.value.length) {
                element.click()
            }
        })

        // handle image preview in the modal
        const preview = document.createElement('img')
        preview.src = base64
        preview.addEventListener('load', (event) => {
            this.cropper = new CropperJS(event.currentTarget, this.options)
        })

        this.elements.modal.preview.append(preview)
        this.elements.modal.root.classList.remove('hidden')
    }

    /**
     * Create canvas from cropped image and fill in the hidden input with canvas base64 data.
     */
    crop() {
        const data = this.cropper.getData()
        const imageWidth = Math.min(parseInt(this.elements.root.dataset.maxWidth), data.width)
        const imageHeight = Math.min(parseInt(this.elements.root.dataset.maxHeight), data.height)
        const previewWidth = Math.min(parseInt(this.elements.form.canvas.dataset.previewWidth), data.width)
        const previewHeight = Math.min(parseInt(this.elements.form.canvas.dataset.previewHeight), data.height)

        // "getCroppedCanvas()" seems to only consider one dimension when calculating the maximum size
        // in respect to the aspect ratio and always considers width first, so height is basically ignored!
        // To set a maximum height, no width parameter should be set.
        // Example of current wrong behavior:
        // source of 200x300 with resize to 150x200 results in 150x225 => WRONG (should be: 133x200)
        // source of 200x300 with resize to 200x150 results in 200x300 => WRONG (should be: 100x150)
        // This is an issue with cropper, not this library

        const previewCanvas = this.cropper.getCroppedCanvas({
            width: previewWidth,
            height: previewHeight,
        })

        const imageCanvas = this.cropper.getCroppedCanvas({
            width: imageWidth,
            height: imageHeight,
        })

        // fill preview canvas with cropped image
        previewCanvas.toBlob(blob => {
            const preview = document.createElement('img')
            preview.src = URL.createObjectURL(blob)

            this.elements.form.canvas.innerHTML = preview.outerHTML
        })

        // fill input with base64 cropped image
        this.elements.form.input.value = imageCanvas.toDataURL(this.elements.root.dataset.mimetype, this.elements.root.dataset.quality)

        // hide the modal
        this.elements.modal.root.classList.add('hidden')
    }
}
