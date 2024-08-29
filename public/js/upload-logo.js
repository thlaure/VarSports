document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.querySelector('#club_logo');
    const cropperImage = document.querySelector('#cropper-image');

    if (fileInput && cropperImage) {
        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    cropperImage.src = e.target.result;
                    const cropper = Cropper.getInstance(cropperImage);
                    if (cropper) {
                        cropper.replace(e.target.result);
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }
});