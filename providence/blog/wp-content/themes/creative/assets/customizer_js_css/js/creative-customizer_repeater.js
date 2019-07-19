jQuery(document).ready(function() {
    jQuery('.customizer-repeater-general-control-droppable').sortable({
        update: function() {
            icare_customizer_repeater_refresh_general_control_values();
        }
    });

    function icare_customizer_repeater_refresh_general_control_values() {
        'use strict';
        var values = [];
        jQuery('.customizer-repeater-general-control-repeater-container').find('.section-id').each(function() {
            var value = jQuery(this).val();
            if (value !== '') {
                values.push(value);
            }
        });
        jQuery('.customizer-repeater-general-control-repeater').find('.customizer-repeater-colector').val(JSON.stringify(values));
        jQuery('.customizer-repeater-general-control-repeater').find('.customizer-repeater-colector').trigger('change');
    }
});