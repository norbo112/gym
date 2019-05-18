$(document).ready(function() {
    console.log("Add Gyakorlat Js loaded");
    var inf = new Info();
    adatInit("#dinamikusSelect", null, true);
    adatInit("#ed_gyaklista", "kapcs=1", false);
    adatInit("#ed_gyakizomlist", "kapcs=2&izomcsoport=" + $("#izomcsoportok").val(), false);

    $("#ed_gyaklista").on("change", function() {
        //console.log("ed_gyaklista select itten van");
        adatInit("#ed_gyakizomlist", "kapcs=2&izomcsoport=" + $("#izomcsoportok").val());
    });

    hozzadas = function(e) {
        //alert("gyakorlat_add submit");
        visszajelzestEltavolit();
        var hibak = urlapotEllenoriz();
        if (hibak == "") {
            adatBekuld();
            e.preventDefault();
            //alert("gyakorlat_add itt lennék a nincs hibákba");
            return false;
        } else {
            visszajelzestAd(hibak);
            //e.preventDefault();
            //return false;
        }
    }

    resetvan = function() {
        //alert("click volt a reset gombon");
        visszajelzestEltavolit2();
    }

    function urlapotEllenoriz() {
        var hibasMezok = new Array();

        if ($("#ujgyak_nev").val() == "") {
            hibasMezok.push("ujgyak_nev");
        }

        if ($("#ujgyak_tipus").val() == "") {
            hibasMezok.push("ujgyak_tipus");
        }

        var reg = new RegExp(/([A-Za-z0-9]+)/, "ig");
        if ($("#ujgyak_nev").val() != "") {
            if (!reg.test($("#ujgyak_nev").val())) {
                hibasMezok.push("ujgyak_nev");
            }
        }

        reg = new RegExp(/[A-Za-z0-9]+/, "ig");
        if ($("#ujgyak_tipus").val() != "") {
            if (!reg.test($("#ujgyak_tipus").val())) {
                hibasMezok.push("ujgyak_tipus");
            }
        }

        reg = new RegExp(/[A-Za-z0-9]+/, "ig");
        if ($("#ujgyak_leiras").val() != "") {
            if (!reg.test($("#ujgyak_leiras").val())) {
                hibasMezok.push("ujgyak_leiras");
            }
        }

        if ($("#ujgyak_video").val() != "") {
            //itt kellene leelenöriznem a video linket vagy az id-t
            console.log("Video Link ellenőrzése");
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
        $("input").each(function() {
            $(this).removeClass("hibaOsztaly");
        });
        $(".hibaSzakasz").each(function() {
            $(this).addClass("hibaVisszajelzes");
        });
    }

    function visszajelzestEltavolit2() {
        $("#hibaDiv").html("");
        $("#ujgyak_nev").removeClass("hibaOsztaly");
        $("#ujgyak_tipus").removeClass("hibaOsztaly");
        $("#ujgyak_leiras").removeClass("hibaOsztaly");
        $(".hibaSzakasz").each(function() {
            $(this).addClass("hibaVisszajelzes");
        });
    }

    function resetUjGyakMezok() {
        visszajelzestEltavolit();
        $("#ujgyak_nev").val("");
        $("#ujgyak_tipus").val("");
        $("#ujgyak_leiras").val("");
    }

    function adatBekuld() {
        var adatok = new String();
        var xhttp = new HttpClient();
        xhttp.isAsync = true;
        xhttp.requestType = "POST";
        xhttp.callback = function(result) {
            resetUjGyakMezok();
            adatInit("#dinamikusSelect");
            adatInit("#ed_gyaklista", "kapcs=1");
            adatInit("#ed_gyakizomlist", "kapcs=2&izomcsoport=" + $("#izomcsoportok").val());
            $("#hibaDiv").html(result);
        };

        adatok += "ujgyak_nev=" + $("#ujgyak_nev").val() + "&";
        adatok += "ujgyak_tipus=" + $("#ujgyak_tipus").val() + "&";
        adatok += "ujgyak_leiras=" + $("#ujgyak_leiras").val();

        xhttp.makeRequest("gyakadd.php", adatok);
    }

    //mindeközben inicializáljuk az aktuális gyakorlat listánkat
    //amelyet egy php szkriptel készítünk el

    //diagramhoz plusz két függvény és egy kis modositás alább

    function adatInit(elem, kapcs, diagramhoz) {
        var xhttp = new HttpClient();
        xhttp.isAsync = true;

        if (kapcs != "" || kapcs != null) {
            xhttp.requestType = "POST";
        }

        if (diagramhoz) {
            xhttp.requestType = "POST";
        }

        xhttp.callback = function(result) {
            $(elem).html(result);
            if (diagramhoz) {
                $("#gyName").on('change', function() {
                    $("#gyakDiagCim").html($(this).val());
                    getDiagramAdat($(this).val());
                });
            }
        }

        xhttp.makeRequest("gyaklistakeszito.php", kapcs);
    }

    function getDiagramAdat(gyakadat) {
        var gyakid = gyakadat;
        var payload = "";
        var xhttp = new HttpClient();
        xhttp.isAsync = true;
        xhttp.requestType = "POST";

        xhttp.callback = function(result) {
            sulytomb = new Array();
            allinosszuly = 0;
            var obj = JSON.parse(result);
            if (!obj.hiba) {
                if (obj.result_sajat || obj.result_partner) {
                    makeAdatOsszesito(obj, "#gyakDiagram", null);
                }
            } else if (!obj.hiba && obj.partner_hiba) {
                if (obj.result_sajat) {
                    makeAdatOsszesito(obj.result_sajat, "#gyakDiagram", obj.partner_hiba);
                }
            } else {
                makeAdatOsszesito(sulytomb, "#gyakDiagram", obj.hiba);
            }
        }

        payload += "gyak_idd=" + gyakid;

        xhttp.makeRequest("diagram_osszsuly.php", payload);
    }

    function makeAdatOsszesito(obj, target, hiba) {
        var str = new String();
        var index_tomb = 0;
        sorozat_reszlet = new Array();
        str += "<div class='table-responsive'>";
        str += "<table class='table table-bordered table-condensed'>";
        str += "<thead><tr>";
        str += "<th>Időpont</th><th></th><th>Összsúly</th>";
        str += "</tr></thead>";
        str += "<tbody>";
        if (hiba != null && !obj.result_sajat) {
            str += "<tr><td colspan='3'><h3>" + hiba + "</h3></td></tr>";
            str += "</tbody></table></div>";
            $(target).html(str);
            return;
        }

        //elöször a saját adatok, majd utána a partner adatok
        for (var i = 0; i < obj.result_sajat.length; i++) {
            str += "<tr>";
            str += "<td>" + obj.result_sajat[i].mentesidatum + "</td>";
            str += "<td style='width: 65%'><div class='diaglineSajat' id='diag" + i + "'></div>";
            str += "</td>";
            str += "<td>" + obj.result_sajat[i].osszsuly + " Kg</td>";
            str += "<td>";
            str += "<button type='button' class='btn btn-info' onclick='getSorozatAdat(\"diag" + i + "_x\")'>Részlet</button>";
            str += "</td>";
            str += "</tr>";

            sorozat_reszlet.push(new SorozatAdat("diag" + i + "_x", getSorozat(obj.result_sajat[i].sorozat)));

            sulytomb.push(new OsszS("diag" + i, obj.result_sajat[i].osszsuly));
            index_tomb = i;
        }

        str += "</tbody></table></div>";

        $(target).html(str);

        var maxertek = Math.max.apply(Math, sulytomb.map(function(o) { return o.mossz }));
        for (var i = 0; i < sulytomb.length; i++) {
            var adat = sulytomb[i].mossz;
            var s1 = (adat / maxertek) * 100;
            $("#" + sulytomb[i].ids).css("width", Math.floor(s1) + "%");
            $("#" + sulytomb[i].ids).html(adat);
        }
    }

    var sulytomb = new Array();
    var sorozat_reszlet = new Array();
    //tömb ami tartalmazza a sorozat adatai-példányokat
    function SorozatAdat(id, adat) {
        this.id = id;
        this.adat = adat;
    }
    /**
     * 
     * @param {Tartalmazza a megfelelő diagram tégla idt} ids 
     * @param {az hozzá tartozó össz súlyt} mossz 
     */
    function OsszS(ids, mossz) {
        this.ids = ids;
        this.mossz = mossz;
    }

    //korábbi rögzitett gyakorlat adatai, info megjelenitése, sorozatról és ismétlésről

    function getSorozat(sorozat) {
        var str = new String();
        str += "<div class='table-responsive'>";
        str += "<table class='table table-bordered table-condensed'>";
        str += "<thead><tr><th>Időpont</th><th>Súly X Ismétlés</th><th>Össz</th></tr></thead><tbody>";
        for (var i = 0; i < sorozat.idop.length; i++) {
            str += "<tr>";
            str += "<td>" + sorozat.idop[i] + "</td>";
            str += "<td>" + sorozat.suly[i] + "X" + sorozat.ism[i] + "</td>";
            str += "<td>" + sorozat.suly[i] * sorozat.ism[i] + "</td>";
            str += "</tr>";
        }
        str += "</tbody></table></div>";
        return str;
    }

    getSorozatAdat = function(id) {
        for (var i = 0; i < sorozat_reszlet.length; i++) {
            if (sorozat_reszlet[i].id == id) {
                inf.showinfo("Sorozat részlet", sorozat_reszlet[i].adat, "white");
                return;
            }
        }
    }
});