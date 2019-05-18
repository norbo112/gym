$(document).ready(function() {
    console.log("reg ready");

    regurlapEventAdder = function() {
        $("form#regUrlap").submit(function(e) {
            e.preventDefault();
            //lehetséges hogy itt kellene elhejeznem a captcha vizsgálatot
            visszajelzestEltavolit();
            var hibak = urlapotEllenoriz();
            if (hibak == "") {
                e.preventDefault();
                if (captchaObj == undefined || !captchaObj.success) {
                    $("#hibaModal").modal();
                    $("#fhonnan").val("regurlap");
                    $("#hibaModal").on("hide.bs.modal", function() {
                        $("#bevitelCap").focus();
                    });
                } else {
                    $("#loadingModal").modal();
                    regajax();
                    captchaObj = undefined;
                }
                return false;
            } else {
                visszajelzestAd(hibak);
                e.preventDefault();
                return false;
            }
            return false;
        });

        $("button[type='reset']").on("click", function() {
            visszajelzestEltavolit();
        });
    }




    function urlapotEllenoriz() {
        var hibasMezok = new Array();

        if ($("#vnev").val() == "") {
            hibasMezok.push("vnev");
        } else if ($("#vnev").val() != "" && $("#vnev").val().length < 50) {
            var regt = new RegExp(/([A-Za-z0-9 \_\-\^]+)/, "ig");
            if (!regt.test($("#vnev").val())) {
                hibasMezok.push("vnev");
            }
        }

        if ($("#knev").val() == "") {
            hibasMezok.push("knev");
        } else if ($("#knev").val() != "" && $("#knev").val().length < 50) {
            var regt = new RegExp(/([A-Za-z0-9 \_\-\^]+)/, "ig");
            if (!regt.test($("#knev").val())) {
                hibasMezok.push("knev");
            }
        }

        if ($("#email").val() == "") {
            hibasMezok.push("email");
        }

        //email cím ellenőrzése reg.exp. használatával
        var reg = new RegExp(/[\-\_A-Za-z0-9]+@[\-\_A-Za-z0-9]+\.(hu|com|org|net)/, "ig");
        if ($("#email").val() != "" || $("#email").val().length < 200) {
            if (!reg.test($("#email").val())) {
                hibasMezok.push("email");
            }
        }

        if ($("#jelszo1").val() == "") {
            hibasMezok.push("jelszo1");
        }

        if ($("#jelszo2").val() != $("#jelszo1").val()) {
            hibasMezok.push("jelszo2");
        }

        reg = new RegExp(/^(\d{1})(\d{1})(\d{1})(\d{1})$/, "i");
        var iranyszam = $("#iranyitoszam").val();
        if (iranyszam != "" && iranyszam.length != 4) {
            if (!reg.test(iranyszam)) {
                hibasMezok.push("iranyitoszam");
            }
        }

        reg = new RegExp(/([A-Za-z]+)/, "ig");
        if ($("#varos").val() != "" && $("#varos").val().length <= 200) {
            if (!reg.test($("#varos").val())) {
                hibasMezok.push("varos");
            }
        }

        reg = new RegExp(/[A-Za-z0-9]+/, "ig");
        if ($("#cim").val() != "" && $("#cim").val().length <= 200) {
            if (!reg.test($("#cim").val())) {
                hibasMezok.push("cim");
            }
        }

        reg = new RegExp(/[0-9]+/, "ig");
        if ($("#sajatsuly").val() != "" && $("#sajatsuly").val().length <= 3) {
            var sajatsulyInt = parseInt($("#sajatsuly").val());
            if (!reg.test($("#sajatsuly").val()) || sajatsulyInt == 0) {
                hibasMezok.push("sajatsuly");
            }
        }

        reg = new RegExp(/[0-9]+/, "ig");
        if ($("#mellcsucs").val() != "" && $("#mellcsucs").val().length <= 3) {
            var mellcsucsInt = parseInt($("#mellcsucs").val());
            if (!reg.test($("#mellcsucs").val()) || mellcsucsInt == 0) {
                hibasMezok.push("mellcsucs");
            }
        }

        reg = new RegExp(/[0-9]+/, "ig");
        if ($("#guggolocsucs").val() != "" && $("#guggolocsucs").val().length <= 3) {
            var guggolocsucsInt = parseInt($("#guggolocsucs").val());
            if (!reg.test($("#guggolocsucs").val()) || guggolocsucsInt == 0) {
                hibasMezok.push("guggolocsucs");
            }
        }

        reg = new RegExp(/[0-9]+/, "ig");
        if ($("#felhuzocsucs").val() != "" && $("#felhuzocsucs").val().length <= 3) {
            var felhuzocsucsInt = parseInt($("#felhuzocsucs").val());
            if (!reg.test($("#felhuzocsucs").val()) || felhuzocsucsInt == 0) {
                hibasMezok.push("felhuzocsucs");
            }
        }

        reg = new RegExp(/[0-9]+/, "ig");
        if ($("#magassag").val() != "" && $("#magassag").val().length <= 3) {
            var magassagInt = parseInt($("#magassag").val());
            if (!reg.test($("#magassag").val()) && (magassagInt == 0 || magassagInt > 300)) {
                hibasMezok.push("magassag");
            }
        }

        //mindenképp választania kell a rádió gombok közül
        if ($("input[name='tagneme']:checked").val() == undefined) {
            hibasMezok.push("tagneme");
        }

        return hibasMezok;
    }

    function visszajelzestAd(bejovoHibak) {
        for (var i = 0; i < bejovoHibak.length; i++) {
            $("#" + bejovoHibak[i]).addClass("hibaOsztaly");
            $("#" + bejovoHibak[i] + "Hiba").removeClass("hibaVisszajelzes");
        }
        $("#hibaDiv").html("Hibák történtek");
    }

    function visszajelzestEltavolit() {
        $("#hibaDiv").html("");
        $("#regUrlap input").each(function() {
            $(this).removeClass("hibaOsztaly");
        });
        $(".hibaSzakasz").each(function() {
            $(this).addClass("hibaVisszajelzes");
        });
    }

    function regajax() {
        var xhttp = new XMLHttpRequest();
        var adatok = "";
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                $("#regelo").html("Adatok elküldve <br>" + this.responseText);
                $("#loadingModal").modal("hide");
            }
        };

        xhttp.open("POST", "regphp.php", true);
        xhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        //átadom az űrlap értékeit a php scriptemnek
        adatok += "volte=volt&";
        adatok += "vnev=" + $("#vnev").val() + "&";
        adatok += "knev=" + $("#knev").val() + "&";
        adatok += "email=" + $("#email").val() + "&";
        adatok += "jelszo1=" + $("#jelszo1").val() + "&";
        adatok += "jelszo2=" + $("#jelszo2").val() + "&";
        adatok += "megye=" + $("#megye").val() + "&";
        adatok += "varos=" + $("#varos").val() + "&";
        adatok += "cim=" + $("#cim").val() + "&";
        adatok += "iranyitoszam=" + $("#iranyitoszam").val() + "&";
        adatok += "sajatsuly=" + $("#sajatsuly").val() + "&";
        adatok += "mellcsucs=" + $("#mellcsucs").val() + "&";
        adatok += "guggolocsucs=" + $("#guggolocsucs").val() + "&";
        adatok += "felhuzocsucs=" + $("#felhuzocsucs").val() + "&";
        adatok += "magassag=" + $("#magassag").val() + "&";
        adatok += "genre=" + $("input[name='tagneme']:checked").val() + "&";
        adatok += "megoszt=" + $("input[name='megoszt']:checked").val();
        xhttp.send(adatok);
    }
});

function checkRadio() {
    //müködik!!!!!
    alert($("input[name='tagneme']:checked").val());
}