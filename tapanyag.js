var adatok = undefined; //ezt felhasználom az étrendhez is
var adatok_tapfajta = undefined;

//étrend mentéséhez
var etrendnapszak = undefined;

//összesitett adatok rogzitese vagyis a napi kalória és egyébb tápanyagbevitel értéke
var etrend_kaloria = 0;
var etrend_energia = 0;
var etrend_feherje = 0;
var etrend_szenhid = 0;
var etrend_zsir = 0;
var etrend_rost = 0;

function NapszakEtel(napszakneve) {
    this.etelek = new Array(); //ebbe lesznek a tapanyag objektumok (név, szénhidrát, rost... adatok)
    this.napszak = napszakneve;
    this.etelek_sulya = new Array();
    this.addEtel = function(etel, suly) {
        if (this.etelek.length == 0) {
            this.etelek.push(etel);
            this.etelek_sulya.push(parseInt(suly));
            return;
        } else {
            for (var i = 0; i < this.etelek.length; i++) {
                if (this.etelek[i].elelmiszerneve == etel.elelmiszerneve) {
                    return;
                }
            }
        }
        this.etelek.push(etel);
        this.etelek_sulya.push(parseInt(suly));
        //console.log("Napszak bővítve: " + this.napszak + " etellel: " + etel.elelmiszerneve);
    }
}

/**
 * étrend összeállításának kezdete
 */

function addEtrendEtelNapszak() {
    var e = $("#napszaknevid").val();
    var en = undefined;

    if (etrendnapszak.length == 0) {
        en = new NapszakEtel(e)
        etrendnapszak.push(en);
        $("#result_etrend_napszaklista").html(frissitEtrendNapszakArray());
        doEtrend();
        return;
    }

    for (var i = 0; i < etrendnapszak.length; i++) {
        if (etrendnapszak[i].napszak == e) {
            return;
        }
    }

    en = new NapszakEtel(e)
    etrendnapszak.push(en);
    $("#result_etrend_napszaklista").html(frissitEtrendNapszakArray());
    doEtrend();

}

function frissitEtrendNapszakArray(opt) {
    var str = new String();
    if (opt == undefined || opt == null) {
        str += "<ul class='list-group'>";
        for (var i = 0; i < etrendnapszak.length; i++) {
            str += "<li class='list-group-item'>" + etrendnapszak[i].napszak + "</li>";
        }
        str += "</ul>";
    } else {
        str += '<select name="napszaknev" id="napszaknevid1" class="form-control">';
        str += "<option value='...'>Kérlek, válassz...</option>";
        for (var i = 0; i < etrendnapszak.length; i++) {
            str += "<option value='" + etrendnapszak[i].napszak + "'>" + etrendnapszak[i].napszak + "</option>";
        }
        str += '</select>';
    }
    return str;
}
//étrend...

function getTapanyagTabla() {
    var xhttp = new HttpClient();
    var payload = new String();
    xhttp.requestType = "POST";
    xhttp.isAsync = true;
    xhttp.callback = function(res) {
        //ezekre az alertekre modal-t kellene készítenem
        var obj = JSON.parse(res);
        var str = new String();
        if (obj.hiba) {
            $("#tapanyag").html(obj.hiba);
        } else if (obj.tapanyag) {
            adatok = obj.tapanyag;
            adatok_tapfajta = obj.tapanyagfajta;
            str = getTapanyagTablaStr(obj.tapanyag, null, "ok");
            $("#tapanyag").html(str);
            getSelectElem(adatok_tapfajta, "#tapselectgrp");
            initKereso();
        } else if (obj.ures) {
            $("#tapanyag").html(obj.ures);
        }
    }

    //payload += "l1=0&l2=20";
    //payload += "page=";
    xhttp.makeRequest("tapanyaglekero.php", payload);
}

function getTapanyagTablaStr(obj, fajta, anyop, sulytomb) {
    var str = new String();
    var o_etelszam = obj.length;
    var o_energiaszam = 0,
        resz_energia = 0;
    var o_kaloriaszam = 0,
        resz_kaloria = 0;
    var o_feherszam = 0,
        resz_feherje = 0;
    var o_szenszam = 0,
        resz_szen = 0;
    var o_zsirszam = 0,
        resz_zsir = 0;
    var o_rostszam = 0,
        resz_rost = 0;

    str += "<div class='tap_header'>";
    if (anyop == null) {
        str += "<span>Súly(g)</span>";
    } else {
        str += "<span>+</span>";
    }

    str += "<span class='tapnev'>Élelmiszer</span>";
    str += "<span>Energia</span>";
    str += "<span>Kalória</span>";
    str += "<span>Fehérje</span>";
    str += "<span>Szénhidrát</span>";
    str += "<span>Zsír</span>";
    str += "<span>Rost</span>";
    str += "</div>"; //táp_header div
    str += "<div class='tap_adat'>";

    for (var i = 0; i < obj.length; i++) {
        if (fajta != null) {
            if (obj[i].fajta != fajta) {
                continue;
            }
        }
        str += "<div class='kre'>";
        if (anyop == null) {
            str += "<span>" + sulytomb[i] * 100 + "(g)</span>"
        } else {
            str += "<span><button type='button' class='btn btn-default' onclick='addEtrendEtel(\"" +
                obj[i].elelmiszerneve + "\")'>+</button></span>";
        }

        if (anyop == null) {
            resz_energia = obj[i].energia * sulytomb[i];
            resz_kaloria = obj[i].kaloria * sulytomb[i];
            resz_feherje = parseFloat(obj[i].feherje * sulytomb[i]);
            resz_szen = parseFloat(obj[i].szenhidrat * sulytomb[i]);
            resz_zsir = parseFloat(obj[i].zsir * sulytomb[i]);
            resz_rost = parseFloat(obj[i].rost * sulytomb[i]);

            //globalba, összesített adat miatt
            etrend_energia += resz_energia;
            etrend_kaloria += resz_kaloria;
            etrend_feherje += resz_feherje;
            etrend_szenhid += resz_szen;
            etrend_zsir += resz_zsir;
            etrend_rost += resz_rost;

            str += "<span class='tapnev'>" + obj[i].elelmiszerneve + "</span>";
            str += "<span>" + new String(resz_energia).substr(0, 4) + "</span>";
            str += "<span>" + new String(resz_kaloria).substr(0, 4) + "</span>";
            str += "<span>" + new String(resz_feherje).substr(0, 4) + "</span>";
            str += "<span>" + new String(resz_szen).substr(0, 4) + "</span>";
            str += "<span>" + new String(resz_zsir).substr(0, 4) + "</span>";
            str += "<span>" + new String(resz_rost).substr(0, 4) + "</span>";
            //str += "</div>";
        } else {
            str += "<span class='tapnev'>" + obj[i].elelmiszerneve + "</span>";
            str += "<span>" + obj[i].energia + "</span>";
            str += "<span>" + obj[i].kaloria + "</span>";
            str += "<span>" + obj[i].feherje + "</span>";
            str += "<span>" + obj[i].szenhidrat + "</span>";
            str += "<span>" + obj[i].zsir + "</span>";
            str += "<span>" + obj[i].rost + "</span>";
        }


        if (anyop == null) {
            o_energiaszam += resz_energia;
            o_kaloriaszam += resz_kaloria;
            o_feherszam += resz_feherje;
            o_szenszam += resz_szen;
            o_zsirszam += resz_zsir;
            o_rostszam += resz_rost;
        }
        str += "</div>"; //tap_adat
    }

    str += "</div>";

    //plusz tap_header a étrendlistának
    if (anyop == null) {
        str += "<div class='tap_header'>";
        str += "<span>Összes:</span>";
        str += "<span id='elelemszam' class='tapnev'>" + o_etelszam + "</span>";
        str += "<span id='energiaszam'>" + o_energiaszam + "</span>";
        str += "<span id='kaloriaszam'>" + o_kaloriaszam + "</span>";
        str += "<span id='feherjeszam'>" + new String(o_feherszam).substr(0, 4) + "</span>";
        str += "<span id='szenszam'>" + new String(o_szenszam).substr(0, 4) + "</span>";
        str += "<span id='zsirszam'>" + new String(o_zsirszam).substr(0, 4) + "</span>";
        str += "<span id='rostszam'>" + new String(o_rostszam).substr(0, 4) + "</span>";
        str += "</div>"; //táp_header div
    }
    return str;
}

function getEtelObj(etelnev) {
    for (var i = 0; i < adatok.length; i++) {
        if (adatok[i].elelmiszerneve == etelnev) {
            //console.log("Etel obj talált " + adatok[i].elelmiszerneve);
            return adatok[i];
        }
    }
}

function osszesitoEtrendAdatFrissit(obj) {
    //obj lenne az étrendben lévő ételek, melyekből kiszámítom az összes bevitelt
    // és frissitem az idvel elátott adatokat
    var o_etelszam = obj.length;
    var o_energiaszam = 0;
    var o_kaloriaszam = 0;
    var o_feherszam = 0;
    var o_szenszam = 0;
    var o_zsirszam = 0;
    var o_rostszam = 0;

    for (var i = 0; i < obj.length; i++) {
        o_energiaszam += obj[i].energia;
        o_kaloriaszam += obj[i].kaloria;
        o_feherszam += obj[i].feherje;
        o_szenszam += obj[i].szenhidrat;
        o_zsirszam += obj[i].zsir;
        o_rostszam += obj[i].rost;
    }

    $("#elelemszam").text(o_etelszam);
    $("#energiaszam").text(o_energiaszam);
    $("#kaloriaszam").text(o_kaloriaszam);
    $("#feherjeszam").text(o_feherszam);
    $("#szenszam").text(o_szenszam);
    $("#zsirszam").text(o_zsirszam);
    $("#rostszam").text(o_rostszam);
}

var globaletelnev;

function addEtrendEtel(etel) {
    globaletelnev = etel;
    if (etrendnapszak.length == 0) {
        _modal("Kérlek kattints a Kezdés gombra, mert jelenleg még nincsenek az étrendhez napszakok kötve");
        return;
    }

    $("#ertesito_adat").load("napszaketelfelvetel.html", function(responseTxt, statusTxt, xhr) {
        if (statusTxt == "success") {
            $("#etelneve").text(etel + " hozzáadása");
            $("#napszaklista0").html(frissitEtrendNapszakArray("ok"));
        }

        if (statusTxt == "error")
            alert("Error: " + xhr.status + ": " + xhr.statusText);
    });

    $("#ertesito").modal();
}

function etelHozzaadNapszakhoz() {
    if ($("#napszaknevid1").val() == "...") {
        //alert("Kérlek válaszd ki a megfelelő napszakot!");
        return;
    }

    var sulya = $("#mennyi").val() == "" ? 1 : $("#mennyi").val();

    addEtrendhezEtel($("#napszaknevid1").val(), globaletelnev, sulya);
    doEtrend();
    $("#ertesito").modal("hide");
}

/**
 * Itt valamiért bedobja a korábban kiválasztott ételeket
 * @param {*} napszaknev 
 * @param {*} etel 
 */
function addEtrendhezEtel(napszaknev, etel, suly) {
    for (var i = 0; i < etrendnapszak.length; i++) {
        if (etrendnapszak[i].napszak == napszaknev) {
            var o = getEtelObj(etel);
            etrendnapszak[i].addEtel(o, suly);
            return;
        }
        //console.log("AddEtrendhezEtel: " + napszaknev + "_" + etel);
    }

}

function getSelectElem(obj, id) {
    var str = new String();

    str += "<label class='input-group-addon' for='tapfajta'>Termékfajta: </label>";
    str += "<select name='tapfajta' id='tapanyagfajta' class='form-control'>";
    str += "<option value='Mind'>Mind mutat</option>";
    for (var i = 0; i < obj.length; i++) {
        str += "<option value='" + obj[i] + "'>" + obj[i] + "</option>";
    }
    str += "</select>";
    $(id).html(str);

    $("#tapanyagfajta").on("change", function() {
        var str = "";
        if ($(this).val() != "Mind") {
            str = getTapanyagTablaStr(adatok, $(this).val(), "ok");
        } else {
            str = getTapanyagTablaStr(adatok, null, "ok");
        }

        $("#tapanyag").html(str);
        $(".tap_adat .kre:even").css("backgroundColor", "white");
    });
}

//itt nem csak a keresőt inicializálom, hanem a header fent maradását is 
// megakarom oldani, egy kiválasztót is raktam a kereső mellé, azt is itt inicializálom
function initKereso() {
    $("#tapkeres").on("keyup", function() {
        $("#tapanyagfajta").val("Mind");
        $("#tapanyagfajta").change();

        var value = $(this).val().toLowerCase();
        $("#tapanyag .tap_adat .kre").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    $("#tapkeresoform").on("submit", function(e) {
        e.preventDefault();
        return;
    });

    $("#tapanyag .tap_adat .kre:even").css("backgroundColor", "white");
}

function initTableStyle() {
    $("#etrend .tap_adat .kre:even").css("backgroundColor", "white");
}

function doEtrend() {
    var str = new String();
    etrend_kaloria = 0;
    etrend_energia = 0;
    etrend_feherje = 0;
    etrend_szenhid = 0;
    etrend_zsir = 0;
    etrend_rost = 0;
    if (etrendnapszak.length == 0) {
        str = "<button type='button' onclick='napszakFelvetel()' class='btn btn-primary'>Kezdés</button>";
        $("#etrend").html(str);
    } else {
        //itt kell majd megjeleníteni az étrendhez rögzített napszakokat, ételeket, összesítőt
        //most a frissítés kedvéért csak a napszakokat iratom ki egy táblázatba
        str = getEtrendTabla();
        str += "<br><br><button type='button' onclick='napszakFelvetel()' class='btn btn-primary'>Napszak felvétele</button>";
        //str += "<br><br><button type='button' onclick='log1()' class='btn btn-primary'>Log</button>";
        $("#etrend").html(str);
        initTableStyle();
    }
}

//táblázat készítése a rögzített étrend adatokhoz
function getEtrendTabla() {
    var str = new String();
    str += "<div class='table-responsive'>";
    str += "<table class='table table-bordered table-condensed'>";
    str += "<thead><tr>";
    str += "<th>Napszak</th><th>Ételek és adataik</th>";
    str += "</tr></thead>";
    str += "<tbody>";
    for (var i = 0; i < etrendnapszak.length; i++) {
        str += "<tr>";
        str += "<td><h2>" + etrendnapszak[i].napszak + "</h2></td>";
        str += "<td>"; //belső tábla az ételekkel és összesítővel
        str += getTapanyagTablaStr(etrendnapszak[i].etelek, null, null, etrendnapszak[i].etelek_sulya);
        str += "</td>"; //belső tábla cella vége
        str += "</tr>";
    }

    //összesitett adatok
    str += "<tr>";
    str += "<td><h3>Napi bevitel</h3></td>";
    str += "<td>";
    str += "<div class='tap_header'>";
    str += "<span>Megnevezés:</span>";
    str += "<span class='tapnev'> _o_ </span>";
    str += "<span>Energia</span>";
    str += "<span>Kalória</span>";
    str += "<span>Fehérje</span>";
    str += "<span>Szénhidrát</span>";
    str += "<span>Zsír</span>";
    str += "<span>Rost</span>";
    str += "</div>"; //táp_header div
    str += "<div class='tap_header'>";
    str += "<span>Összes:</span>";
    str += "<span id='elelemszam' class='tapnev'> _o_ </span>";
    str += "<span>" + new String(etrend_energia).substr(0, 4) + "</span>";
    str += "<span>" + new String(etrend_kaloria).substr(0, 4) + "</span>";
    str += "<span>" + new String(etrend_feherje).substr(0, 4) + "</span>";
    str += "<span>" + new String(etrend_szenhid).substr(0, 4) + "</span>";
    str += "<span>" + new String(etrend_zsir).substr(0, 4) + "</span>";
    str += "<span>" + new String(etrend_rost).substr(0, 4) + "</span>";
    str += "</div>"; //táp_header div
    str += "</td>";
    str += "</tr>";
    //összesített adatok vége

    str += "</tbody>";
    str += "</table>";
    str += "</div>"; //table-responsive
    return str;
}

function _modal(str) {
    $("#ertesito_adat").html(str);
    $("#ertesito").modal();
}

function napszakFelvetel() {
    $("#ertesito_adat").load("napszakfelvetel.html");
    $("#ertesito").modal();
}

function log1() {
    for (var i = 0; i < etrendnapszak.length; i++) {
        console.log("Napszak: " + etrendnapszak[i].napszak + "\n");
        for (var k = 0; k < etrendnapszak[i].etelek.length; k++) {
            console.log("\t" + etrendnapszak[i].etelek[k].elelmiszerneve + "\n");
        }
    }
}

//étrend mentése ill. törlése webstorage-ba
function mentEtrend() {
    var nev = $.cookie("felhasznalo");
    if (etrendnapszak.length == 0) {
        _modal("<h3>Nincs mentendő étrendtábla</h3>");
        return;
    }

    localStorage.setItem(nev + "_etrendtabla", JSON.stringify(etrendnapszak));
    //itt leegyszerüsítve el is mentem az adatbázisba is
    mentEtrendTODB();
    //_modal("<h3>Étrend elmentve</h3>");
}

function torolEtrend() {
    if (confirm("Biztosan törölni szeretnéd?")) {
        var nev = $.cookie("felhasznalo");
        localStorage.removeItem(nev + "_etrendtabla");
        etrendnapszak = new Array();
        _modal("<h3>Étrend törölve</h3>");
    } else {
        _modal("A törlés nem teljesült");
    }

    doEtrend();
}

function etrendBetoltese() {
    var nev = $.cookie("felhasznalo");
    var obj = localStorage.getItem(nev + "_etrendtabla");
    var mentettEtrend = JSON.parse(obj);
    etrendnapszak = new Array();
    if (obj != null || obj != undefined) {
        for (var i = 0; i < mentettEtrend.length; i++) {
            var e = new NapszakEtel(mentettEtrend[i].napszak);
            for (var k = 0; k < mentettEtrend[i].etelek.length; k++) {
                e.addEtel(mentettEtrend[i].etelek[k], mentettEtrend[i].etelek_sulya[k]);
            }
            etrendnapszak.push(e);
        }
        console.log("Ételek betöltve");
        doEtrend();
    } else {
        //_modal("<h3>Hiba az étrendtábla betöltése közben</h3>");
        console.log("Hiba az étrendtábla betöltése közben");
        //ha még nincs ilyen tábla mentve a webstorageba, akkor megjelenik a hiba....
        //ezért console-ba iratom a hibát, ideiglenesen
        //a hibák jelentését, szerveren lévő fájlba kellene mentenem, php-n keresztül
    }
}

function mentEtrendTODB() {
    if (etrendnapszak.length != 0) {
        var xhttp = new HttpClient();
        var payload = "etrend=" + JSON.stringify(etrendnapszak);
        xhttp.isAsync = true;
        xhttp.requestType = "POST";
        xhttp.callback = function(result) {
            var o = JSON.parse(result);
            if (o.hiba) {
                _modal("<h3>Hiba lépett fel: </h3><br>" + o.hiba);
            } else if (o.ok) {
                _modal("<h3>Sikeres mentés!</h3><br><pre>" + o.ok + "</pre>");
                getEtrendFromDB();
            } else {
                _modal("<h3>Ismeretlen hiba!</h3><br>");
            }
        }

        xhttp.makeRequest("etrend.php", payload);
    } else {
        _modal("Nincs mentendő adat!");
    }
}

function getEtrendFromDB() {
    var xhttp = new HttpClient();
    var payload = "etrend=0&leker=0";
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(result) {
        var o = JSON.parse(result);
        if (o.hiba) {
            //_modal("Sikertelen adatlekérés " + o.hiba);
            $("#dbEtrendek").html("Hiba: " + o.hiba);
        } else if (o.etrendek) {
            $("#dbEtrendek").html(getEtrendListaFromObj(o.etrendek));
        } else if (o.etrendek_null) {
            $("#dbEtrendek").html("<h3>" + o.etrendek_null + "</h3>");
        } else {
            _modal("Ismeretlen hiba");
        }
    }
    xhttp.makeRequest("etrend.php", payload);
}

function getEtrendListaFromObj(etrendlista) {
    var str = new String();
    str += "<div class='list-group'>";
    for (var i = 0; i < etrendlista.length; i++) {
        var d = new Date(etrendlista[i] * 1000);
        str += "<a onclick='etrendBetoltFromDB(\"" + etrendlista[i] + "\")' class='list-group-item'>" +
            d.getFullYear() + "." + (d.getMonth() + 1) + "." + d.getDate() + " " + d.getHours() + ":" + d.getMinutes() + "</a>";
    }
    str += "</div>";
    return str;
}

function getEtrendFromDBtoTable(etrendlista) {
    var str = new String();
    str += "<div class='table-responsive'>";
    str += "<table class='table table-bordered table-condensed'>";
    str += "<thead><tr>";
    str += "<th>Napszak</th><th>Ételek és adagok</th>";
    str += "</tr></thead>";
    str += "<tbody>";
    for (var i = 0; i < etrendlista.length; i++) {
        str += "<tr>";
        str += "<td>" + etrendlista[i].napszak + "</td>";
        str += "<td>";
        str += "<ul class='list-group'>";
        for (var k = 0; k < etrendlista[i].etelek.length; k++) {
            str += "<li class='list-group-item'>" + etrendlista[i].etelek[k] + ", " + etrendlista[i].adagok[k] + " adag</li>";
        }
        str += "</ul>";
        str += "</td>";
        str += "</tr>";
        //teszt jeleggel idekellene kiszámítanom a rész értékeket, majd megjelenítenem
        //az össz napi bevitel értékét
        str += "<tr><td colspan='2'>";
        str += getEeszertek(etrendlista[i].adatok);
        str != "</td></tr>";
    }
    str += "</tbody></table></div>";
    return str;
}

//részeredmények kijelzése az étrend adatokból
// 2019-02-18 össz eredményt is össze kellene állítanom az egészből
function getEeszertek(el) {
    var str = new String();
    var kaloria = 0;
    var energia = 0;
    var feherje = 0;
    var szenh = 0;
    var zsir = 0;
    var rost = 0;

    for (var i = 0; i < el.length; i++) {
        kaloria += el[i].kaloria;
        energia += el[i].energia;
        feherje += el[i].feherje;
        szenh += el[i].szenhidrat;
        zsir += el[i].zsir;
        rost += el[i].rost;
    }

    str += "Energia: " + energia + ", Kalória: " + kaloria + ", Fehérje: " + gtt(feherje, 4) +
        ", Szénhidrát: " + gtt(szenh, 4) + ", Zsír: " + gtt(zsir, 4) + ", Rost: " + gtt(rost, 4);

    return str;
}

function gtt(str, len) {
    return new String(str).substr(0, len);
}

function etrendBetoltFromDB(etrendlistaazon) {
    var xhttp = new HttpClient();
    var payload = "lekerlista=" + etrendlistaazon;
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(result) {
        var o = JSON.parse(result);
        if (o.hiba) {
            $("#dbEtrendekNezo").html("Sikertelen adatlekérés" + o.hiba);
        } else if (o.etrendeklista) {
            $("#dbEtrendekNezo").html(getEtrendFromDBtoTable(o.etrendeklista));
        } else {
            $("#dbEtrendekNezo").html("Ismeretlen hiba");
        }
    }
    xhttp.makeRequest("etrend.php", payload);
}

$(document).ready(function() {
    getTapanyagTabla();
    etrendnapszak = new Array();
    etrendBetoltese();
    doEtrend();

    if (!logintEllenoriz()) {
        _modal("<h3>Étrend tábla mentéséhez kérlek jelentkezz be</h3>");
    } else {
        $("#vezerlopanel").html("<p><button onclick='mentEtrend()' type='button' class='btn btn-danger'>Étrend mentése</button>" +
            "<button onclick='torolEtrend()' type='button' class='btn btn-danger'>Étrend törlése</button></p>");
    }
    getEtrendFromDB();
});