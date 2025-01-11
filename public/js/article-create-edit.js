document.addEventListener('DOMContentLoaded', () => {
    const homeCategoryField = document.getElementById('article_homeCategory');
    const externalLinkField = document.getElementById('external-link');

    const toggleExternalLinkField = () => {
        const selectedOption = homeCategoryField.options[homeCategoryField.selectedIndex]?.value;
        if ('social' === selectedOption) {
            externalLinkField.style.display = '';
        } else {
            externalLinkField.style.display = 'none';
            externalLinkField.value = '';
        }
    };

    toggleExternalLinkField();

    homeCategoryField.addEventListener('change', toggleExternalLinkField);
});
