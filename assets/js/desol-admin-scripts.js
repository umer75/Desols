jQuery(document).ready(function ($) {
    $('#get-average-salary').on('click', function (e) {
        e.preventDefault();

        $('#average-salary').text('...');

        $.ajax({
            url: desol_nonce_data.ajaxurl,
            type: 'POST',
            data: {
                action: 'fetch_average_salary',
                desol_nonce: desol_nonce_data.desol_nonce
            },
            success: function (response) {
                if (response.success) {
                    $('#average-salary').text(response.data.average_salary);
                }
            },
        });
    });
});
