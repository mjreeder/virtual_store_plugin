
var updateCartInput = document.querySelector("input[name=update_cart]");
updateCartInput.addEventListener("click", function () {
    jQuery(document).ajaxStop(function () {
        window.location.reload();
    });
});