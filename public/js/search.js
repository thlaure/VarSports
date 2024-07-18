document.addEventListener('DOMContentLoaded', function() {
    const labels = document.querySelectorAll('#search-clubs li label');
    for (const label of labels) {
        label.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});