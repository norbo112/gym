var myobj = undefined; //dátumokat tartalmazo objektum
var edzesnaplo = new Array();

function Naplo(mentesidatum, gyaksik) {
    this.Datum = mentesidatum;
    this.Gyaksik = gyaksik;
}

$(document).ready(function() {

    rogz_gyak_megjelenito();

    if (logintEllenoriz()) {
        adatInit("#dinSelect", "kapcs=5");
        partner_init("#dinSelectPartner");
        var s = new String();
        s += '<button type="button" class="btn btn-primary" onclick="exportCSV()">';
        s += 'Export to CSV <span class="glyphicon glyphicon-export"></span>';
        s += '</button><span id="exportStatus"></span>';
        $("#exportGomb").html(s);
    } else {
        info("Diagramösszesítő használatához, kérlek jelentkezz be!");
    }

});

function naploMegjLista(target) {
    var str = new String()
    if (myobj) {
        str += "<span>";
        for (var i = 0; i < myobj.mentesidatum.length; i++) {
            str += "<input class='naplogomb' type='button' id='megjelen' value='" +
                myobj.mentesidatum[i] + "' onclick='alert($(this).val())'><br>";

        }
        str += "</span>";
    }

    $("#" + target).html(str);
}

//betölti az adataimat a mysqlből php-n keresztül json obj-be, elvileg :)
function rogz_gyak_megjelenito() {
    edzesnaplo = new Array();
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.callback = function(res) {
        myobj = JSON.parse(res);
        //itt probálom betölteni a naplo adatokat dátumhoz tartozoan
        if (myobj.hiba) {
            $("#korabbi_gyak_lista_reszletezo").html("<h2>" + myobj.hiba + "</h2>");
            return;
        } else if (myobj.ures) {
            $("#korabbi_gyak_lista_reszletezo").html("<h2>" + myobj.ures + "</h2>");
            return;
        }

        //gyakNaploDatumMegj("#rogzitettNaplok");

        for (var i = 0; i < myobj.mentesidatum.length; i++) {
            var xh2 = new HttpClient();

            // console.log(mentDatum); ez mindig másik dátumot mutat, csak jelenleg
            // nem tudom átadni az uj Naplo objektumomnak
            //.. sierült, csak ehhez átkellett irnom a HttpClient osztály, csak egy plusz tulajdonságot adtam
            xh2.datumom = myobj.mentesidatum[i];
            var payload = "mentesidatum=" + myobj.mentesidatum[i];

            xh2.isAsync = true;
            xh2.requestType = "POST";
            xh2.callback = function(res2, mentDatum) {
                edzesnaplo.push(new Naplo(mentDatum, JSON.parse(res2)));
                //itt jelenitem meg a tényleges naplot, remélhetöleg felépült az edzésnapló tömböm
                //
                naplotMegjelenit("#korabbi_gyak_lista_reszletezo");
                gyakNaploDatumMegj("#rogzitettNaplok");
            }

            xh2.makeRequest("naplogyakleker.php", payload);
        }
    }

    xhttp.makeRequest("naplogyakleker.php", null);
}

function gyakNaploDatumMegj(id) {
    var str = new String();
    if (myobj) {
        //str += "<div class='list-group' id='napikeresendo' style='height: 250px; overflow: auto;'>";
        str += "<div class='btn-group justified' id='napikeresendo' style='height: 150px; overflow: auto;'>";
        for (var i = 0; i < myobj.mentesidatum.length; i++) {
            str += "<a style='width: 20%;' class='btn btn-default' onclick='showInf(\"" + myobj.mentesidatum[i] + "\")'>" + myobj.mentesidatum[i] + "</a>";
        }
        str += "<a style='width: 20%;' class='btn btn-primary' onclick='setToTop()'>Mind mutat</a>";
        str += "</div>";

        $(id).html(str);
    }
}

function showInf(obj) {
    var elem = document.getElementById(obj);
    var foelem = document.getElementsByClassName("panel-default");
    var phead = document.getElementsByClassName("panel-heading");
    //console.log(obj);
    $(foelem).removeClass("kijelolve");
    $(phead).filter(function() {
        var id = $(this).attr("id").toLowerCase();
        $(this).parent().toggle(id.indexOf(obj.toLowerCase()) > -1);
    });
    $(elem).parent().addClass("kijelolve");
}

function clearSiblings() {
    var foelem = document.getElementsByClassName("panel-default");
    $(foelem).removeClass("kijelolve").show();
}

//kikell egészítenem a gyakorlat össz suly-t és a napi összes megmozgatott suly
//megjelenítésével
function naplotMegjelenit(id) {
    var str = new String();

    //dátum szerint sorba kellene rendeznem az objektumomat
    edzesnaplo.sort(function(a, b) {
        return new Date(b.Datum) - new Date(a.Datum);
    });

    for (var i = 0; i < edzesnaplo.length; i++) {
        //str += "<div class='containter-fluid'>";
        str += "<div class='panel panel-default'>";
        str += "<div class='panel-heading' id='" + edzesnaplo[i].Datum + "'>" + edzesnaplo[i].Datum + "</div>";
        str += "<div class='panel-body'>";
        str += "<div class='table-responsive'>";
        str += "<table id='keresendo' class='table table-bordered table-hover table-condensed'>";
        str += "<thead><tr>";
        str += "<th>Rögzítési dátum</th><th>Izomcsoport</th><th>Gyakorlat neve</th><th>Sorozat</th><th>Időpont</th><th>Össz(kg)</th>";
        str += "</tr></thead>";
        var naplogyak = edzesnaplo[i].Gyaksik.naplo;
        var ossznapisuly = 0;
        //console.log(naplogyak);
        for (var j = 0; j < naplogyak.length; j++) {
            var osszgyaksuly = 0;
            str += "<tbody>";
            str += "<tr>";
            str += "<td>" + naplogyak[j].gyakrogzido + "</td>";
            //alább csak megjelenítettem a gyakorlat csoportot, sikerült e visszaszednem mysqlből php-n keresztül
            //és igen, sikerült, majd ez alapján számítom ki a rész (izomcsoport) eredményeket
            str += "<td>" + naplogyak[j].gycsoport + "</td>";
            str += "<td>" + naplogyak[j].megnevezes + "<br><em>" + naplogyak[j].megjegyzes + "</em></td>";
            str += "<td>";
            for (var k = 0; k < naplogyak[j].sorozat.suly.length; k++) {
                str += naplogyak[j].sorozat.suly[k] + "x" + naplogyak[j].sorozat.ism[k] + "<br>";
                osszgyaksuly = osszgyaksuly + (parseInt(naplogyak[j].sorozat.suly[k]) * parseInt(naplogyak[j].sorozat.ism[k]));
            }
            str += "</td>";
            str += "<td>";
            //próbálom kiszámítani az eltelt percet
            var lmeret = naplogyak[j].sorozat.idop.length;
            //console.log("lmeret: " + lmeret);
            for (var k = 0, l = lmeret - 1; k < lmeret; k++) {
                str += "<i>" + naplogyak[j].sorozat.idop[k].substr(11.20) + "</i>&nbsp;";

                if (k != 0) {
                    if (lmeret != 0) {
                        var t = (Date.parse(naplogyak[j].sorozat.idop[l]) -
                            Date.parse(naplogyak[j].sorozat.idop[l - k])) / 1000 / 60;
                        t = Math.floor(t);
                        str += "<span data-toggle='tooltip' data-placement='right' title='Eltelt idő " + t + " perc' class='label label-info test'>" + t + "</span>";
                    }
                }

                str += "<br>";
            }
            str += "</td>";
            ossznapisuly += osszgyaksuly;
            str += "<td>" + osszgyaksuly + "</td>";
            str += "</tr>";
            str += "</tbody>";
        }
        //str += "<tr><td colspan='6'>Napi megmozgatott súly: <mark>" + ossznapisuly + " kg</mark></td>";
        str += "</table>";
        str += "</div>"; //table-responsive
        //itt hozzáadom a kinyert naplo notest, ha mindenoké
        //str += "<div class='col-md-10 megj'>";
        str += "<blockquote>";
        if (edzesnaplo[i].Gyaksik.naplonote) {
            if (edzesnaplo[i].Gyaksik.naplonote == "null") {
                str += "<i>Nem mentettél megjegyzést a naplóhoz</i>";
            } else {
                var a = edzesnaplo[i].Gyaksik.naplonote.replace(/\"/g, "");
                str += "<i>" + a + "</i>";
            }

        } else if (edzesnaplo[i].Gyaksik.naplonotehiba) {
            str += "<i>:: " + edzesnaplo[i].Gyaksik.naplonotehiba + "</i>";
        }
        //str += "</div>";
        str += "</blockquote>";
        // naplonote hozzáadás vége
        str += "</div>";
        str += "<div class='panel-footer'>";
        str += "Napi megmozgatott súly: <mark>" + ossznapisuly + " kg</mark>";
        str += "<span class='pull-right'><button type='button' class='btn btn-default btn-xs' onclick='setToTop()'>Top</button>";
        str += "<button class='btn btn-danger btn-xs' type='button' onclick='delnaplo(\"" + edzesnaplo[i].Datum + "\")'><span class='glyphicon glyphicon-remove'></span></button></span>";
        str += "</div>";
        str += "</div>";

    }


    $(id).html(str);
    $('[data-toggle="tooltip"]').tooltip();
}

function setToTop() {
    window.location = "#top";
    clearSiblings();
}

function selectAdd(opt, optid) {
    var str = new String();
    str += "<select id='" + optid + "' name='" + optid + "'>";
    str += "<option>--------</option>";
    str += "<option value='asd'>Összes</option>";
    for (var i = 0; i < opt.naplo.length; i++) {
        if (optid == "mentesidatum") {
            str += "<option value='" + opt.naplo[i].mentesidatum + "'>" + opt.naplo[i].mentesidatum + "</option>";
        } else if (optid == "gyakrogzido") {
            str += "<option value='" + opt.naplo[i].gyakrogzido + "'>" + opt.naplo[i].gyakrogzido + "</option>";
        } else {
            str += "<option value='" + opt.naplo[i].megnevezes + "'>" + opt.naplo[i].megnevezes + "</option>";
        }

    }
    str += "</select>";

    return str;
}

function selectAddEvent(opt) {
    $("#" + opt).change(function() {
        gyaksikMegjelenitese("#korabbi_gyak_lista_reszletezo", opt, $(this).val());
        console.log("selectAddEvent " + opt + " funk lefutott");
    });
}

function exportCSV() {
    /*var http = new HttpClient();
    http.isAsync = false;
    http.requestType = "POST";
    var adat = "mentesidatum=csvkeres";
    http.callback = function(result) {
        //$("#exportStatus").html(result);
        console.log("Fájl mentése folyamatban");
    }
    http.makeRequest("naplogyakleker.php", adat);*/
    window.open("naplogyakleker.php?csvkeres=1", "_blank");
}

function delnaplo(mentesidatum) {
    var conf = confirm("Biztosan törölni szeretnéd a kiválaszott naplót?");
    if (!conf) {
        return;
    }
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(res) {
        var obme = JSON.parse(res);
        var str = new String();
        if (obme.torolve) {
            if (obme.torolve.naplo) {
                str += obme.torolve.naplo + "<br>";
            }

            if (obme.torolve.sorozat) {
                str += obme.torolve.sorozat + "<br>";
            }

            if (obme.torolve.note) {
                str += obme.torolve.note + "<br>";
            }
        } else if (obme.hiba) {
            str += obme.hiba + "<br>";
        }

        $("#delinfomodalinfo").html(str);
        $("#delinfomodal").modal();

        rogz_gyak_megjelenito();
    }
    var adat = "deldatum=" + encodeURIComponent(mentesidatum);
    xhttp.makeRequest("naplogyakleker.php", adat);
}

//diagram készítéséhez tartozó függvények
var sulytomb = undefined;
var sorozat_reszlet = new Array();
//var allinosszuly = 0;

function adatInit(elem, kapcs) {
    var xhttp = new HttpClient();
    xhttp.isAsync = true;

    if (kapcs != "" || kapcs != null) {
        xhttp.requestType = "POST";
    }

    xhttp.callback = function(result) {
        $(elem).html(result);

        //diagram
        $("#gyNameDig").on('change', function() {
            var a = $(this).val().split("_");
            $("#kivalasztottGyaksi").text(a[1]);

            getDiagramAdat(a[0]);
        });
    }

    xhttp.makeRequest("gyaklistakeszito.php", kapcs);
}

function partner_init(celelem) {
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(result) {
        var o = JSON.parse(result);
        if (!o.hiba) {
            if (o.partner) {
                makePartnerekValaszto(celelem, o.partner);
            }
        } else {
            info("Hiba történt!<br>" + o.hiba);
        }
    }

    xhttp.makeRequest("diagram_megoszto.php", "partner_kero=1");
}

function makePartnerekValaszto(celelem, obj) {
    var str = new String();
    if (obj.length != 0) {
        str += "<label class='form-control' for='gyPartnerDig'>Partner: </label>";
        str += '<select class="form-control" name="gyPartnerDig" id="gyPartnerDig">';
        str += "<option value='Kérlek, válassz...'>Kérlek, válassz...</option>";
        for (var i = 0; i < obj.length; i++) {
            str += "<option value='" + obj[i].pid + "_" + obj[i].vneve + " " + obj[i].kneve + "'>" + obj[i].vneve + " " + obj[i].kneve + "</option>";
        }
        str += "</select>";
    } else {
        str += "<h4>Nincs megfelelő partner</h4>";
    }
    $(celelem).html(str);

    //partner amikor kilett választva
    $("#gyPartnerDig").on('change', function() {
        var a = $("#gyNameDig").val().split("_");
        var b = $(this).val().split('_');

        if ($(this).val() == "Kérlek, válassz...") {
            //info("Kérlek válassz ki egy partnert, akivel összehasonlítanád a gyakorlatodat");
            getDiagramAdat(a[0]);
            return;
        }
        if ($("#gyNameDig").val() != "" || $("#gyNameDig").val() != "Kérlek, válassz...") {

            $("#kivalasztottGyaksi").text(a[1] + " és " + b[1] + " gyakorlata");
            getDiagramAdat(a[0], b[0]); //partnerid
        } else {
            alert("Válaszd ki a gyakorlatot!");
        }
    });
}

function getDiagramAdat(gyakadat, partnerid) {
    var a = gyakadat.split("_");
    var gyakid = a[0];
    var gyaknev = a[1];
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
                makeAdatOsszesito(obj, "#mydiagram", null);
            }
        } else if (!obj.hiba && obj.partner_hiba) {
            if (obj.result_sajat) {
                makeAdatOsszesito(obj.result_sajat, "#mydiagram", obj.partner_hiba);
            }
        } else {
            makeAdatOsszesito(sulytomb, "#mydiagram", obj.hiba);
        }
    }

    if (partnerid != null) {
        payload += "partner_id=" + partnerid + "&";
        console.log("Partner id nem null: " + partnerid);
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
        str += "<td><strong>" + obj.result_sajat[i].mentesidatum + "</strong></td>";
        str += "<td style='width: 65%'><div class='diaglineSajat' id='diag" + i + "'></div>";
        str += "</td>";
        str += "<td><strong>" + obj.result_sajat[i].osszsuly + " Kg</strong#</td>";
        str += "<td>";
        str += "<button type='button' class='btn btn-info' onclick='getSorozatAdat(\"diag" + i + "_x\")'>Részlet</button>";
        str += "</td>";
        str += "</tr>";

        sorozat_reszlet.push(new SorozatAdat("diag" + i + "_x", getSorozat(obj.result_sajat[i].sorozat)));

        sulytomb.push(new OsszS("diag" + i, obj.result_sajat[i].osszsuly));
        //allinosszuly += obj.result_sajat[i].osszsuly;
        index_tomb = i;
    }

    if (obj.result_partner) {
        //elöször a saját adatok, majd utána a partner adatok
        for (var i = 0; i < obj.result_partner.length; i++, index_tomb++) {
            str += "<tr>";
            str += "<td>" + obj.result_partner[i].mentesidatum + "</td>";
            str += "<td style='width: 65%'><div class='diagline' id='diag2" + index_tomb + "'></div>";
            str += "</td>";
            str += "<td>" + obj.result_partner[i].osszsuly + " Kg</td>";
            str += "<td>";
            str += "<button type='button' class='btn btn-info' onclick='getSorozatAdat(\"diag2" + index_tomb + "_x\")'>Részlet</button>";
            str += "</td>";
            str += "</tr>";

            sorozat_reszlet.push(new SorozatAdat("diag2" + index_tomb + "_x", getSorozat(obj.result_partner[i].sorozat)));

            sulytomb.push(new OsszS("diag2" + index_tomb, obj.result_partner[i].osszsuly));
            //allinosszuly += obj.result_partner[i].osszsuly;
        }
    } else if (!obj.result_partner && obj.partner_hiba) {
        str += "<tr><td colspan='3'><h3>" + obj.partner_hiba + "</h3></td></tr>";
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

function info(str, szin) {
    $("#delinfomodalinfo").html(str);
    if (szin != null) {
        $("#delinfomodal div.modal-content").css("background-color", szin);
    }

    $("#delinfomodal").modal();
}

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

function getSorozatAdat(id) {
    for (var i = 0; i < sorozat_reszlet.length; i++) {
        if (sorozat_reszlet[i].id == id) {
            //console.log("ok itt vagyok jo vagy");
            info(sorozat_reszlet[i].adat, "white");
            return;
        }
    }
}