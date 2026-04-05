jQuery(document).ready(function($) {
    
    // 1. Initialize Drag and Drop
    $("#ctib-days-wrapper").sortable({
        handle: ".drag-handle",
        placeholder: "ui-state-highlight",
        update: function(event, ui) {
            reindexDays();
        }
    });

    function reindexDays() {
        $(".ctib-day-box").each(function(index) {
            $(this).find(".ctib-day-header strong").text("Day " + (index + 1));
            // Update meal names to match new index
            $(this).find(".ctib-meals input").attr('name', 'ctib_meals[' + index + '][]');
        });
    }

    // 2. Cascading Filter: Fetch Hotels when Destination changes
    $(document).on('change', '.ctib-dest-filter', function() {
        const destId = $(this).val();
        const hotelSelect = $(this).closest('.ctib-day-box').find('.ctib-hotel-select');
        const preview = $(this).closest('.ctib-day-box').find('.ctib-hotel-preview');

        if (!destId) return;

        hotelSelect.html('<option>Loading...</option>');

        $.post(ajaxurl, {
            action: 'ctib_get_master_data',
            master_type: 'ctib_hotel',
            dest_id: destId,
            nonce: $('#ctib_admin_nonce').val()
        }, function(response) {
            if (response.success) {
                let options = '<option value="">-- Select Hotel --</option>';
                response.data.forEach(hotel => {
                    options += `<option value="${hotel.id}" data-img="${hotel.image}">${hotel.title}</option>`;
                });
                hotelSelect.html(options);
            }
        });
    });

    // 3. Dynamic Image Library Preview
    $(document).on('change', '.ctib-hotel-select', function() {
        const imgUrl = $(this).find(':selected').data('img');
        const preview = $(this).closest('.ctib-day-box').find('.ctib-hotel-preview');
        
        if (imgUrl) {
            preview.html(`<img src="${imgUrl}" style="height:60px; border-radius:4px; margin-top:5px;">`);
        } else {
            preview.empty();
        }
    });

    // 4. Add/Remove Day Logic
    $('#ctib-add-day').on('click', function() {
        // Clone the first day box and clear values
        let newBox = $('.ctib-day-box').first().clone();
        newBox.find('input, textarea').val('');
        newBox.find('.ctib-hotel-preview').empty();
        newBox.find('.ctib-hotel-select').html('<option value="">-- Choose Hotel --</option>');
        $('#ctib-days-wrapper').append(newBox);
        reindexDays();
    });

    $(document).on('click', '.ctib-remove-day', function() {
        if ($('.ctib-day-box').length > 1) {
            $(this).closest('.ctib-day-box').remove();
            reindexDays();
        }
    });
});