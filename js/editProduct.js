jQuery(document).ready(function($) {

    var hiddenValuesAppended = false;

    disableInputs();

    setInterval(function() {
        disableInputs();
    }, 3000);

    function disableInputs() {
        $("[name*='variable_sku']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_stock']").attr("readonly", "readonly").siblings('label').addClass('disabled');
        $("[name*='variable_backorders']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_enabled']").attr("type", "hidden").parent('label').addClass('disabled');
        $("[name*='variable_is_downloadable']").attr("type", "hidden").parent('label').addClass('disabled');
        $("[name*='variable_is_virtual']").attr("type", "hidden").parent('label').addClass('disabled');
        $("[name*='variable_manage_stock']").attr("type", "hidden").parent('label').addClass('disabled');
        $("[name*='variable_weight']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_length']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_width']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_height']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='product-type']").attr("type", "hidden").css("visibility","hidden").parent('label').addClass('disabled');
        $("[name*='variable_shipping_class']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='_virtual']").attr("type", "hidden").parent('label').addClass('disabled');
        $("[data-global*='true']").remove();
        $("option[value*='delete_all']").remove();
        $("optgroup[label*='Status']").remove();
        $("optgroup[label*='Inventory']").remove();
        $("optgroup[label*='Shipping']").remove();
        $("optgroup[label*='Downloadable products']").remove();
        $("option[value='variable_regular_price']").prop('selected', true);

        if (!hiddenValuesAppended) {
            $(".woocommerce_variation").each(function () {
                var parent = $(this);
                $(this).find("h3 > select").each(function() {
                    hiddenValuesAppended = true;
                    var input = $("<input>")
                        .attr("type", "hidden")
                        .attr("name", $(this).attr("name")).val($(this).val());
                    parent.append(input);
                });
            });
        }
        $("select[name*='attribute_']").prop("disabled", true);

    }

});