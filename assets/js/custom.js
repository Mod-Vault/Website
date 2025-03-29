$(document).ready(function() {
    //handle adding asterisk to required fields labels
    $(':input[required]').each(function () {
        var label = $(this).parent().find('label');
        if(label) {
            label.html(label.text() + ' <span class="text-danger fw-bold">*</span>');
        }
    });
});
