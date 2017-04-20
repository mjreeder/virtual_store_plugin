function displayToast() {
    var toast = document.getElementById("toast");
    toast.className = toast.className + " show";
    setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 3000);
}
displayToast();