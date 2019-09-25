$(function() {
    $("#cpimg").click(function(e) {
        e.preventDefault();
        $("#cpimg").attr("src", "nwcap/nwctest.php?R=220&G=250&B=255" +
            "&dummy=" + Math.floor(Math.random() * 1000));
        return false;
    });
});