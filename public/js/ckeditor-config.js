import {
    ClassicEditor,
    Essentials,
    Bold,
    Italic,
    Font,
    Paragraph,
    Heading
} from 'ckeditor5';

ClassicEditor
    .create(document.querySelector('.ckeditor'), {
        plugins: [ Essentials, Heading, Bold, Italic, Font, Paragraph ],
        toolbar: {
            items: [
                'heading' , '|', 'undo', 'redo', '|', 'bold', 'italic', '|',
                'fontSize', 'fontFamily', 'fontColor'
            ]
        }
    })
    .catch(error => {
        console.error(error);
    });