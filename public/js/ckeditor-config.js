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
    .create(document.querySelector('#club_description'), {
        plugins: [ Essentials, Heading, Bold, Italic, Font, Paragraph ],
        toolbar: {
            items: [
                'heading' , '|', 'undo', 'redo', '|', 'bold', 'italic', '|',
                'fontSize', 'fontFamily', 'fontColor'
            ]
        }
    })
    .then( /* ... */ )
    .catch( /* ... */ );