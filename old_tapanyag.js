var adatok = undefined; //ezt felhasználom az étrendhez is
var adatok_tapfajta = undefined;

//étrend mentéséhez
var etrendnapszak = undefined;

function NapszakEtel(napszakneve) {
    this.etelek = new Array(); //ebbe lesznek a tapanyag objektumok (név, szénhidrát, rost... adatok)
    this.napszak = napszakneve;
    this.addEtel = function(etel) {
        if (this.etelek.length == 0) {
            this.etelek.push(etel);
        } else {
            for (var i = 0; i < this.etelek.length; i++) {
                if (this.etelek[i].elelmiszerneve == etel) {
                    return;
                }
            }
            this.etelek.push(etel);
        }
    }
}

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
            str = getTapanyagTablaStr(obj.tapanyag, null);
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

function getTapanyagTablaStr(obj, fajta) {
    var str = new String();

    /* ezt is inkább átrakom htmlbe
    str += "<div class='panel panel-default'>";
    str += "<div class='panel-heading'><h3>Tápanyag táblázat</h3></div>";
    str += "<div class='panel-body' style='color: black;'>";

    if (obj.length == 0) {
        str += "Nincsenek megjelenítendő elemek!";
        str += "</div>";
        str += "</div>";
        return str;
    }

    Ezt inkább átrakom a HTML oldalra
    str += "<form class='form-inline'>";
    str += "<div class='input-group'>";
    str += "<label class='input-group-addon'>Kereső: </label>";
    str += "<input name='tapkeres' class='form-control' type='text' id='tapkeres' placeholder='Terméknév...'>";
    str += "</div>";
    str += "<div class='input-group'>";
    str += "<label class='input-group-addon' for='tapfajta'>Termékfajta: </label>";
    str += getSelectElem(adatok_tapfajta);
    str += "</div>";
    str += "</form>";
    str += "<br>";*/

    str += "<div class='tap_header'>";
    str += "<span>+</span>";
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
        //str += "<span>" + (i + 1) + "</span>";
        str += "<span><button type='button' class='btn btn-default'>+</button></span>";
        str += "<span class='tapnev'>" + obj[i].elelmiszerneve + "</span>";
        str += "<span>" + obj[i].energia + "</span>";
        str += "<span>" + obj[i].kaloria + "</span>";
        str += "<span>" + obj[i].feherje + "</span>";
        str += "<span>" + obj[i].szenhidrat + "</span>";
        str += "<span>" + obj[i].zsir + "</span>";
        str += "<span>" + obj[i].rost + "</span>";
        str += "</div>";
    }
    str += "</div>"; //tap_adat

    //str += "</div>";
    //str += "</div>";
    return str;
}

function _ss(mivel) {
    var str = new String();
    str += "<br>";
    str += "<div class='btn-group'>";
    str += "<button class='btn btn-default btn-sort btn-xs' onclick='sortTapAdatok(\"" + mivel + "\",\"1\")'>";
    str += "<span class='glyphicon glyphicon-arrow-up'></span>";
    str += "</button>";
    str += "<button class='btn btn-default btn-sort btn-xs' onclick='sortTapAdatok(\"" + mivel + "\",\"0\")'>";
    str += "<span class='glyphicon glyphicon-arrow-down'></span>";
    str += "</button>";
    str += "</div>";
    //return str;
    return "";
}

function sortTapAdatok(mivel, hogy) {
    //hogy 1 növekvő, 0 csökkenő
    var str = new String();
    switch (mivel) {
        case "energia":
            adatok.sort(function(a, b) {
                if (hogy == 1) return parseInt(a.energia) - parseInt(b.energia);
                else return parseInt(b.energia) - parseInt(a.energia);
            });
            break;
        case "kaloria":
            adatok.sort(function(a, b) {
                if (hogy == 1) return parseInt(a.kaloria) - parseInt(b.kaloria);
                else return parseInt(b.kaloria) - parseInt(a.kaloria);
            });
            break;
        case "feherje":
            adatok.sort(function(a, b) {
                if (hogy == 1) return parseInt(a.feherje) - parseInt(b.feherje);
                else return parseInt(b.feherje) - parseInt(a.feherje);
            });
            break;
        case "szenhidrat":
            adatok.sort(function(a, b) {
                if (hogy == 1) return parseInt(a.szenhidrat) - parseInt(b.szenhidrat);
                else return parseInt(b.szenhidrat) - parseInt(a.szenhidrat);
            });
            break;
        case "zsir":
            adatok.sort(function(a, b) {
                if (hogy == 1) return parseInt(a.zsir) - parseInt(b.zsir);
                else return parseInt(b.zsir) - parseInt(a.zsir);
            });
            break;
        case "rost":
            adatok.sort(function(a, b) {
                if (hogy == 1) return parseInt(a.rost) - parseInt(b.rost);
                else return parseInt(b.rost) - parseInt(a.rost);
            });
            break;
        case "nev":
            adatok.sort(function(a, b) {
                if (hogy == 1) return a.rost - b.rost;
                else return b.rost - a.rost;
            });
            break;
    }

    str = getTapanyagTablaStr(adatok, null);
    $("#tapanyag").html(str);
    initKereso();
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
            str = getTapanyagTablaStr(adatok, $(this).val());
        } else {
            str = getTapanyagTablaStr(adatok, null);
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
        $(".kre").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $(".tap_adat .kre:even").css("backgroundColor", "white");
}

function doEtrend() {
    if (etrendnapszak.length == 0) {
        $("#etrend").html("<button type='button' class='btn btn-primary'>Kezdés</button>");
    } else {

    }
}

function _modal(str) {
    $("#ertesito_adat").html(str);
    $("#ertesito").modal();
}

$(document).ready(function() {
    getTapanyagTabla();

    etrendnapszak = new Array();
    doEtrend();
});