jQuery(document).ready(function($) {

    disableInputs();

    setInterval(function() {
        disableInputs();
    }, 3000);

    function disableInputs() {
        $("[name*='variable_sku']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='variable_stock']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='variable_backorders']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='variable_enabled']").prop("disabled", true).parent('label').addClass('disabled');
        $("[name*='variable_is_downloadable']").prop("disabled", true).parent('label').addClass('disabled');
        $("[name*='variable_is_virtual']").prop("disabled", true).parent('label').addClass('disabled');
        $("[name*='variable_manage_stock']").prop("disabled", true).parent('label').addClass('disabled');
        $("[name*='variable_weight']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='variable_length']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='variable_width']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='variable_height']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='product-type']").prop("disabled", true).parent('label').addClass('disabled');
        $("[name*='variable_shipping_class']").prop("disabled", true).siblings('label').addClass('disabled');
        $("[name*='_virtual']").prop("disabled", true).parent('label').addClass('disabled');
        $("[data-global*='true']").remove();
        $("option[value*='delete_all']").remove();
        $("optgroup[label*='Status']").remove();
        $("optgroup[label*='Inventory']").remove();
        $("optgroup[label*='Shipping']").remove();
        $("optgroup[label*='Downloadable products']").remove();
        $("option[value='variable_regular_price']").prop('selected', true);
    }

});