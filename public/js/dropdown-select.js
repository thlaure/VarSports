document.addEventListener('DOMContentLoaded', function() {
    const labels = document.querySelectorAll('.dropdown-select .dropdown-select-label');
    for (const label of labels) {
        label.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }
});