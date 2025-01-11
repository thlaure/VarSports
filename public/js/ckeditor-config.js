import {
    ClassicEditor,
    Essentials,
    Bold,
    Italic,
    Font,
    Link,
    Paragraph,
    Heading,
    Image,
    ImageToolbar,
    ImageCaption,
    ImageStyle,
    ImageUpload,
    ImageResize
} from 'ckeditor5';

document.querySelectorAll('.ckeditor').forEach(element => {
    ClassicEditor
        .create(element, {
            plugins: [
                Essentials,
                Heading,
                Bold,
                Italic,
                Font,
                Link,
                Paragraph,
                Image,
                ImageToolbar,
                ImageCaption,
                ImageStyle,
                ImageUpload,
                ImageResize
            ],
            toolbar: {
                items: [
                    'heading', '|', 'undo', 'redo', '|', 'bold', 'italic', '|', 'link', '|',
                    'fontSize', 'fontFamily', 'fontColor', '|', 'insertImage'
                ]
            },
            image: {
                toolbar: [
                    'imageStyle:full',
                    'imageStyle:side',
                    '|',
                    'imageTextAlternative'
                ]
            }
        })
        .catch(error => {
            console.error(error);
        });
});