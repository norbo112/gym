$(document).ready(function() {
    var hirid = 0;
    console.log("hirkuldo ready");

    //megjelenito(logintEllenoriz());
    hirkuldo = function() {
        //alert("Hírküldés itten van");
        visszajelzestEltavolit();
        var hibak = urlapotEllenoriz();
        if (hibak == "") {
            hirbekuldo();
            //hir beküldése ajaxal
            return true;
        } else {
            visszajelzestAd(hibak);
            return false;
        }
    }

    uzikuldo = function() {
        //alert("Hírküldés itten van");
        uziVisszajelzestEltavolit();
        var hibak = uziUrlapotEllenoriz();
        if (hibak == "") {
            uzibekuldo();
            //üzi beküldése ajaxal
            //alert("itten vagyok üziküldő okés lett");
            return true;
        } else {
            visszajelzestAd(hibak);
            return false;
        }
    }

    $("button[type='reset']").on("click", function() {
        visszajelzestEltavolit();
    });

    function urlapotEllenoriz() {
        var hibasMezok = new Array();

        if ($("#hir_cim").val() == "") {
            hibasMezok.push("hir_cim");
        }

        if ($("#hir_note").val() == "") {
            hibasMezok.push("hir_note");
        }

        reg = new RegExp(/[A-Za-z0-9]+/, "ig");
        if ($("#hir_cim").val() != "" && $("#hir_cim").val().length < 150) {
            if (!reg.test($("#hir_cim").val())) {
                hibasMezok.push("hir_cim");
            }
        }

        reg = new RegExp(/[A-Za-z0-9 \-\!\?\.,]+/, "ig");
        if ($("#hir_note").val() != "" && $("#hir_note").val().length < 300) {
            if (!reg.test($("#hir_note").val())) {
                hibasMezok.push("hir_cim");
            }
        }
        return hibasMezok;
    }

    uziUrlapotEllenoriz = function() {
        var hibasMezok = new Array();

        if ($("#uzi_cim").val() == "") {
            hibasMezok.push("uzi_cim");
        }

        if ($("#uzi_note").val() == "") {
            hibasMezok.push("uzi_note");
        }

        reg = new RegExp(/[A-Za-z0-9]+/, "ig");
        if ($("#uzi_cim").val() != "" && $("#uzi_cim").val().length < 100) {
            if (!reg.test($("#uzi_cim").val())) {
                hibasMezok.push("uzi_cim");
            }
        }

        reg = new RegExp(/[A-Za-z0-9 \-\!\?\.,]+/, "ig");
        if ($("#uzi_note").val() != "" && $("#uzi_note").val().length < 300) {
            if (!reg.test($("#uzi_note").val())) {
                hibasMezok.push("uzi_note");
            }
        }
        return hibasMezok;
    }

    visszajelzestAd = function(bejovoHibak) {
        for (var i = 0; i < bejovoHibak.length; i++) {
            $("#" + bejovoHibak[i]).addClass("hibaOsztaly");
            $("#" + bejovoHibak[i] + "Hiba").removeClass("hibaVisszajelzes");
        }
        $("#hibaDiv2").html("Hibák történtek");
    }

    function visszajelzestEltavolit() {
        $("#hibaDiv2").html("");
        $("#hirkuldoform input, #hirkuldoform textarea").each(function() {
            $(this).removeClass("hibaOsztaly");
        });
        $("#hirkuldoform .hibaSzakasz").each(function() {
            $(this).addClass("hibaVisszajelzes");
        });
    }

    uziVisszajelzestEltavolit = function() {
        $("#hibaDiv2").html("");
        $("#uzikuldoform input, #uzikuldoform textarea").each(function() {
            $(this).removeClass("hibaOsztaly");
        });
        $("#uzikuldoform .hibaSzakasz").each(function() {
            $(this).addClass("hibaVisszajelzes");
        });
    }

    function hirbekuldo() {
        var xhttp = new HttpClient();
        xhttp.isAsync = true;
        xhttp.requestType = "POST";
        var adatok = "";
        xhttp.callback = function(res) {
            $("#hirekAdd").html("" + res);
            hirekMegjelenito("#hirek"); //ha sikerült, ha nem akkoris frissitem a hireket
        };

        //átadom az űrlap értékeit a php scriptemnek
        adatok += "volte=volt&";
        adatok += "hir_cim=" + $("#hir_cim").val() + "&";
        adatok += "hir_tipus=" + $("#hir_tipus").val() + "&";
        adatok += "hir_note=" + $("#hir_note").val();

        xhttp.makeRequest("hirkuldoin.php", adatok);
    }

    function uzibekuldo() {
        var xhttp = new HttpClient();
        xhttp.isAsync = true;
        xhttp.requestType = "POST";
        var adatok = "";
        xhttp.callback = function(res) {
            $("#hirekAdd").html("" + res);
        };

        //átadom az űrlap értékeit a php scriptemnek
        adatok += "volte=volt&";
        adatok += "uzi_cim=" + $("#uzi_cim").val() + "&";
        adatok += "uzi_tipus=" + $("#uzi_tipus").val() + "&";
        adatok += "uzi_note=" + $("#uzi_note").val();

        xhttp.makeRequest("uzikuldo.php", adatok);
    }

    whmod = function(id) {
        hirid = id;
        //console.log("HirID:" + hirid);
        $("hibaDiv4").html("");
        $("#webhirhszmodal").modal();
    }

    whmodOn = function() {
        if (hirid != 0) {
            var h = hszEllenor();
            visszajelzestEltavolit();
            if (h == "") {
                whkuldo();
                return true;
            } else {
                visszajelzestAd(h);
                return false;
            }
        }
    }

    whreset = function() {
        $("#wbtextHiba").addClass("hibaVisszajelzes");
        $("#wbtext").removeClass("hibaOsztaly");
        $("#wbtext").val("");
        $("#hibaDiv4").html("");
    }

    function hszEllenor() {
        var hibak = new Array();
        if ($("#wbtext").val() == "") {
            hibak.push("wbtext");
        }
        //link egyébb strong tagmodositása itt lesz
        var reg = new RegExp(/[A-Za-z0-9]+/, "ig");
        if ($("#wbtext").val() != "") {
            if (!reg.test($("#wbtext").val())) {
                hibak.push("wbtext");
            }
        }
        return hibak;
    }

    function whkuldo() {
        var xhttp = new HttpClient();
        xhttp.isAsync = true;
        xhttp.requestType = "POST";
        var adatok = "";
        xhttp.callback = function(res) {
            var res = JSON.parse(res);
            if (res.hiba) {
                $("#hibaDiv4").html(res.hiba);

            } else {
                $("#webhirhszmodal").modal("hide");
                hirekMegjelenito("#hirek");
            }
        }

        //átadom az űrlap értékeit a php scriptemnek
        adatok += "volte=volt&";
        adatok += "uid=" + hirid + "&";
        adatok += "wbtext=" + $("#wbtext").val();

        xhttp.makeRequest("webhszk.php", adatok);
    }

    whhsz = function(id) {
        var xhttp = new HttpClient();
        xhttp.isAsync = true;
        xhttp.requestType = "POST";
        var adatok = "";
        xhttp.callback = function(res) {
            var hszk = JSON.parse(res);
            hozzaszolasMegjelenit(hszk, id);
        }

        //átadom az űrlap értékeit a php scriptemnek
        adatok += "volte=volt&";
        adatok += "uid=" + id + "&";
        adatok += "kapcsolo=" + 1;

        xhttp.makeRequest("webhszk.php", adatok);
    }

    delhir = function(id) {
        if (confirm("Biztosan törölni szeretnéd?")) {
            var xhttp = new HttpClient();
            xhttp.isAsync = true;
            xhttp.requestType = "POST";
            var adatok = "";
            xhttp.callback = function(res) {
                console.log(JSON.parse(res));
                hirekMegjelenito("#hirek");
            }

            //átadom az űrlap értékeit a php scriptemnek
            adatok += "volte=volt&";
            adatok += "uid=" + id + "&";
            adatok += "kapcsolo=" + 2;

            xhttp.makeRequest("webhszk.php", adatok);
        } else {
            alert("Az adatok megmaradtak");
        }
    }

    //var volteanim = false;

    function hozzaszolasMegjelenit(obj, id) {
        var str = new String();
        if (typeof(obj) == "object" && !obj.nulla) {
            for (var i = 0; i < obj.length; i++) {
                str += '<blockquote>';
                str += '<p>' + obj[i].szoveg + '</p>';
                str += '<footer>' + obj[i].kitol + ' <i>' + obj[i].letrehozva + '</i></footer>';
                str += '</blockquote>';
                $("#hszmegjelenito_" + obj[i].id).html(str);

            }
        } else if (typeof(obj) == "object" && obj.nulla) {
            $("#hszmegjelenito_" + id).html(obj.nulla);
        } else {
            console.log("Object type hiba: " + obj);
        }

        var attr = document.getElementById("hszmegjelenito_" + id);

        if (attr.getAttribute("data-volttog" + id) == 0) {
            $("#hszmegjelenito_" + id).slideDown(1500, function() {
                $(" .spalpha_" + id).
                removeClass("glyphicon glyphicon-chevron-down").
                addClass("	glyphicon glyphicon-chevron-up")
            });
            attr.setAttribute("data-volttog" + id, 1);
        } else {
            $("#hszmegjelenito_" + id).slideUp(1500, function() {
                $(".spalpha_" + id).
                removeClass("glyphicon glyphicon-chevron-up").
                addClass("	glyphicon glyphicon-chevron-down")
            });
            attr.setAttribute("data-volttog" + id, 0);
        }
    }
});