jQuery(document).ready(function($) {

    var variationHiddenValuesAppended = false;
    var stockHiddenValuesAppended = false;
    var stockStatusHiddenValuesAppended = false;


    disableInputs();

    setInterval(function() {
        disableInputs();
    }, 1000);

    function disableInputs() {
        $("[name*='variable_sku']").attr("type", "hidden").siblings('label').css("display", "none");
        $("[name*='variable_stock']").attr("readonly", "readonly").siblings('label').addClass('disabled');
        $("[name*='variable_backorders']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_enabled']").attr("type", "hidden").parent('label').css("display", "none");
        $("[name*='variable_is_downloadable']").attr("type", "hidden").parent('label').css("display", "none");
        $("[name*='variable_is_virtual']").attr("type", "hidden").parent('label').css("display", "none");
        $("[name*='variable_manage_stock']").attr("type", "hidden").parent('label').css("display", "none");
        $("[name*='variable_weight']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_length']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_width']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='variable_height']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='product-type']").attr("type", "hidden").css("visibility","hidden").parent('label').addClass('disabled');
        $("[name*='variable_shipping_class']").attr("type", "hidden").siblings('label').addClass('disabled');
        $("[name*='_virtual']").attr("type", "hidden").parent('label').addClass('disabled');
        $("[name*='variable_download_limit']").attr("type", "hidden").siblings('label').css("display", "none");
        $("[name*='variable_download_expiry']").attr("type", "hidden").siblings('label').css("display", "none");
        $(".show_if_variation_downloadable").css("display", "none");
        $("[data-global*='true']").remove();
        $("option[value*='delete_all']").remove();
        $("optgroup[label*='Status']").remove();
        $("optgroup[label*='Inventory']").remove();
        $("optgroup[label*='Shipping']").remove();
        $("optgroup[label*='Downloadable products']").remove();
        $("option[value='variable_regular_price']").prop('selected', true);

        if (!variationHiddenValuesAppended || !stockHiddenValuesAppended) {

            if (!variationHiddenValuesAppended ) {
                $(".woocommerce_variation").each(function () {
                    var parent = $(this);
                    $(this).find("h3 > select").each(function() {
                        variationHiddenValuesAppended = true;
                        var input = $("<input>")
                            .attr("type", "hidden")
                            .attr("name", $(this).attr("name")).val($(this).val());
                        parent.append(input);
                    });
                });
            }

            if (!stockHiddenValuesAppended) {
                $(".show_if_variation_manage_stock").each(function () {
                    var parent = $(this);
                    $(this).find("p > select").each(function() {
                        stockHiddenValuesAppended = true;
                        var input = $("<input>")
                            .attr("type", "hidden")
                            .attr("name", $(this).attr("name")).val($(this).val());
                        parent.append(input);
                    });
                });
            }

            if (!stockStatusHiddenValuesAppended) {
                $("select[name*='variable_stock_status']").each(function () {
                    stockStatusHiddenValuesAppended = true;
                    var input = $("<input>")
                        .attr("type", "hidden")
                        .attr("name", $(this).attr("name")).val($(this).val());
                    $(this).parent().append(input);
                });
            }

        }
        $("select[name*='attribute_pa']").prop("disabled", true);
        $("select[name*='variable_backorders']").prop("disabled", true);
        $("select[name*='variable_stock_status']").prop("disabled", true);


    }

});