//edzéstervhet készített js fájl
var edzes = undefined;
var valasz = "Kérlek, válassz...";

function EdzesTerv(edzesnap) {
    this.edzesnap = edzesnap;
    this.izomcsoport = new Array();
    this.addIzomcsoport = function(izomcs, gyaks) {
        var ics = undefined;

        if (this.izomcsoport.length == 0) {
            ics = new IzomCsop(izomcs);
            ics.addGyak(gyaks);
            this.izomcsoport.push(ics);
            return;
        }

        for (var i = 0; i < this.izomcsoport.length; i++) {
            if (this.izomcsoport[i].ineve == izomcs) {
                this.izomcsoport[i].addGyak(gyaks);
                return;
            }
        }

        ics = new IzomCsop(izomcs);
        ics.addGyak(gyaks);
        this.izomcsoport.push(ics);

        console.log("Add izomcsoport lefutott");
    }
    this.torolIcsop = function(index) {
        var tmpcsop = new Array();
        for (var i = 0; i < this.izomcsoport.length; i++) {
            if (i == index) {
                continue;
            }

            tmpcsop.push(this.izomcsoport[i]);
        }
        this.izomcsoport = tmpcsop;
    }
}

function IzomCsop(name) {
    this.gyak = new Array();
    this.ineve = name;
    this.addGyak = function(gyakpar) {
        if (this.gyak.length != 0) {
            for (var i = 0; i < this.gyak.length; i++) {
                //itt csak gyaknev változot tettem hozzá, amit vizsgálni kell
                if (this.gyak[i].gyaknev == gyakpar.gyaknev) {
                    return;
                }
            }

            this.gyak.push(gyakpar);
        } else {
            this.gyak.push(gyakpar);
        }
    }
    this.gyTorol = function(index) {
        var tmpgy = new Array();
        for (var i = 0; i < this.gyak.length; i++) {
            if (i == index) {
                continue;
            }
            tmpgy.push(this.gyak[i]);
        }
        this.gyak = tmpgy;
    }
}

/**
 * Objektum szükséges a gyakorlatohoz is, amely tartalmazza a sorozat és ismétléseket
 * hány sorozatot csinál és mely ismétlés tartományba mozog, ha a második 0 akkor
 * egyforma ismétléssel dolgozik, viszont azt a nullát nem kell jelezni majd a tervbe
 * 
 * készítek egy ellenörző függvényt melyben ellenörzöm a beviteli mezőt
 */

function EtervGyakorlat(gyaknev, sorozat, ismTol, ismIg) {
    this.gyaknev = gyaknev;
    this.sorozat = sorozat;
    this.ismTol = ismTol;
    this.ismIg = ismIg;
}

//ellenörzöm a bevitelt, szám e, 100nál kevesebb kell hogy legyen és ne legyen nulla
function cb(bevitel) {
    if (isNaN(bevitel)) {
        //console.log("isNaN true1");
        return false;
    } else {
        if (bevitel < 100 && bevitel > 0) {
            //console.log("bevitel kisebb mint 100 és nagyobb mint 0");
            return true;
        } else {
            //console.log("tul lött a célon");
            return false;
        }
    }
}

function addEdzesTervElem() {
    var ed_nap = $("#edzesnap").val();
    var ed_icsop = $("#izomcsoportok").val();
    var ed_gyak = $("#gyaksik_icsop").val();
    var edt = undefined;

    /**gyakorlathoz tartozo sorozat és ismétlések bevitele */
    var ed_sor = $("#be_sor_num").val();
    var ed_tol = $("#tol_num").val();
    var ed_ig = $("#ig_num").val();
    var edtGyak = undefined;

    if (!cb(ed_sor)) {
        modalErtesito("<h3>Sorozat</h3><br>Kérlek, számot irj be, mely 1-100 között legyen!");
        $("#be_sor").focus();
        return;
    }

    if (!cb(ed_tol)) {
        modalErtesito("<h3>Ismétlés</h3><br>Kérlek, számot irj be, mely 1-100 között legyen!");
        $("#tol").focus();
        return;
    }

    if (!cb(ed_ig)) {
        modalErtesito("<h3>Ismétlés</h3><br>Kérlek, számot irj be, mely 1-100 között legyen!");
        $("#ig").focus();
        return;
    }

    edtGyak = new EtervGyakorlat(ed_gyak, ed_sor, ed_tol, ed_ig);

    if (ed_nap == valasz ||
        ed_icsop == valasz ||
        ed_gyak == "Válasz csoportot") {
        alert("Kérlek válassz edzésnapot, izomcsoportot és gyakorlatot");
        return;
    }

    for (var i = 0; i < edzes.length; i++) {
        if (edzes[i].edzesnap == ed_nap) {
            console.log("edzésnap megegyezik" + ed_nap);
            edzes[i].addIzomcsoport(ed_icsop, edtGyak);
            printEdzesTerv("#edzesterv");
            return;
        }
    }

    /* ez akkor volt amikor még nem rögzítettem a sorozatot és ismétlésszámot
    for (var i = 0; i < edzes.length; i++) {
        if (edzes[i].edzesnap == ed_nap) {
            console.log("edzésnap megegyezik" + ed_nap);
            edzes[i].addIzomcsoport(ed_icsop, ed_gyak);
            printEdzesTerv("#edzesterv");
            return;
        }
    }*/

    if (edzes.length == 0) {
        edt = new EdzesTerv(ed_nap);
        //edt.addIzomcsoport(ed_icsop, ed_gyak);
        edt.addIzomcsoport(ed_icsop, edtGyak);
        edzes.push(edt);
    } else {
        edt = new EdzesTerv(ed_nap);
        //edt.addIzomcsoport(ed_icsop, ed_gyak);
        edt.addIzomcsoport(ed_icsop, edtGyak);
        edzes.push(edt);
    }

    printEdzesTerv("#edzesterv");
}

function printEdzesTerv(id) {
    var str = new String();

    str += "<div class='panel panel-default'>";
    str += "<div class='panel-heading'><h3>Edzésterv</h3></div>";
    str += "<div class='panel-body'>";

    if (edzes.length == 0) {
        str += "Nincsenek megjelenítendő elemek!";
        str += "</div>";
        str += "</div>";
        $(id).html(str);
        return;
    }

    str += "<div class='table-responsive'>";
    str += "<table class='table table-bordered table-striped table-condensed'>";
    str += "<thead><tr>";
    str += "<th>Edzésnap</th><th>Izomcsoportok és gyakorlatok</th>";
    str += "</tr></thead>";
    str += "<tbody>";
    for (var i = 0; i < edzes.length; i++) {

        str += "<tr>";
        str += "<td>" + edzes[i].edzesnap + " " + rii(i + "_enap") + "</td>";
        str += "<td colspan='2'>";
        //belső tábla
        str += "<table class='table talbe-bordered table-hover'>";
        str += "<thead><tr><th>Izomcsoport</th><th>Gyakorlat</th></tr></thead><tbody>";
        for (var k = 0; k < edzes[i].izomcsoport.length; k++) {
            str += "<tr><td><strong>" + rii(i + "_" + k + "_eicsop") + edzes[i].izomcsoport[k].ineve + "</strong></td>";
            str += "<td>";
            for (var j = 0; j < edzes[i].izomcsoport[k].gyak.length; j++) {
                str += "<strong>" + rii(i + "_" + k + "_" + j + "_gyak") + edzes[i].izomcsoport[k].gyak[j].gyaknev + "</strong> ";
                str += edzes[i].izomcsoport[k].gyak[j].sorozat + "x" +
                    edzes[i].izomcsoport[k].gyak[j].ismTol + "-" +
                    edzes[i].izomcsoport[k].gyak[j].ismIg + "<br>";
            }
            str += "</td></tr>";
        }
        str += "</tbody></table>";
        //belső tábla vége
        str += "</td></tr>";

    }

    str += "</tbody>";
    str += "</table>";
    str += "</div>"; //table-responsive
    str += "</div>";
    str += "</div>";
    $(id).html(str);
}

function mentEdzesTerv() {
    var FNev;
    if (logintEllenoriz()) {
        if (!(FNev = getNevem())) {
            FNev = "body";
        } else {
            FNev = getNevem();
        }
    } else {
        console.log("Nem vagy bejelentkezve");
        FNev = "body";
    }

    //ide kellene egy info modal-t rakni

    if (edzes.length == 0) {
        alert("Nincs mentendő tartalom");
        return;
    }

    localStorage.setItem(FNev + "_edzesterv", JSON.stringify(edzes));
    alert("Edzésterv elmentve");
}

function toltEdzesTerv(nev) {
    var FNev;
    if (logintEllenoriz()) {
        if (!(FNev = getNevem())) {
            FNev = "body_edzesterv";
        } else {
            FNev = getNevem() + "_edzesterv";
        }
    } else {
        FNev = "body_edzesterv";
    }

    if (nev != null) {
        FNev = nev;
    }

    //edzes = new Array();
    var tmp = localStorage.getItem(FNev);
    if (tmp != null && tmp != "") {
        //edzes = JSON.parse(localStorage.getItem(FNev));
        adatFelepit(JSON.parse(localStorage.getItem(FNev)));
    } else {
        edzes = new Array();
    }

    if (nev != null) {
        printEdzesTerv("#edzesterv");
    }
}

function ujTerv() {
    var FNev;
    if (logintEllenoriz()) {
        if (!(FNev = getNevem())) {
            FNev = "body";
        } else {
            FNev = getNevem();
        }
    } else {
        FNev = "body";
    }

    localStorage.setItem(FNev + "_edzesterv", null);
    edzes = new Array();
    printEdzesTerv("#edzesterv");
}

function eTervekBetoltes(id) {
    var etervek = new Array();
    var str = new String();

    for (var i = 0; i < localStorage.length; i++) {
        //itemek megvizsgálása.... _edzesterv...
        if (localStorage.key(i).indexOf("_edzesterv") > -1) {
            etervek.push(localStorage.key(i));
        }
    }

    str += "<div class='list-group'>";
    if (etervek.length != 0) {
        for (var i = 0; i < etervek.length; i++) {
            str += "<a href='javascript:void(0)' onclick='toltEdzesTerv(\"" + etervek[i] + "\")' class='list-group-item'>" +
                "" + etervek[i] + "</a>";
        }
    } else {
        str += "Nincs mentett edzésterv";
    }

    str += "</div>";
    $(id).html(str);
}

//felkell építenem az objektumot ujra a mentett adatokból, mivel úgy lehet majd bővíteni
function adatFelepit(obj) {
    edzes = new Array();
    var edtGyak = undefined;
    for (var i = 0; i < obj.length; i++) {
        var a_edzesnap = new EdzesTerv(obj[i].edzesnap);
        for (var k = 0; k < obj[i].izomcsoport.length; k++) {
            var a_izomcsop = new IzomCsop(obj[i].izomcsoport[k].ineve);
            for (var j = 0; j < obj[i].izomcsoport[k].gyak.length; j++) {
                edtGyak = new EtervGyakorlat(obj[i].izomcsoport[k].gyak[j].gyaknev,
                    obj[i].izomcsoport[k].gyak[j].sorozat,
                    obj[i].izomcsoport[k].gyak[j].ismTol,
                    obj[i].izomcsoport[k].gyak[j].ismIg);
                //a_izomcsop.gyak.push(obj[i].izomcsoport[k].gyak[j]);
                a_izomcsop.gyak.push(edtGyak);
            }
            a_edzesnap.izomcsoport.push(a_izomcsop);
        }
        edzes.push(a_edzesnap);
    }
}

//egy kis ikon és parancs amivel ellehet távolítani az edzésterv elemeit
function rii(torlendoneve) {
    return "<button class='btn btn-default btn-xs' onclick='itemTorol(\"" +
        torlendoneve +
        "\")'><span class='glyphicon glyphicon-minus'></span></button>";
}

function itemTorol(item) {
    var tt = item.split("_");
    // 0: edzésnap száma
    // 1: izomcsoport száma
    // 2: gyakorlat száma
    // 3: gyak, izomcsop, edzésnap megfelelő funkció kiválasztására
    switch (tt[tt.length - 1]) {
        case "gyak":
            //console.log("Gyakorlat"); gyakorlat törlése a megfelelő helyen
            gyakTorol(tt[0], tt[1], tt[2]);
            break;
        case "eicsop":
            // console.log("Izomcsoport"); izomcsoport törlése a megfelelő helyen
            izomcsopTorol(tt[0], tt[1]);
            break;
        case "enap":
            //console.log("Edzésnap"); egy egész edzésnap törlése a megadott helyről
            ienapTorol(tt[0]);
            break;
    }

    printEdzesTerv("#edzesterv");
}

function gyakTorol(enapindex, izomcsindex, gyakindex) {
    edzes[enapindex].izomcsoport[izomcsindex].gyTorol(gyakindex);
    if (edzes[enapindex].izomcsoport[izomcsindex].gyak.length == 0) {
        izomcsopTorol(enapindex, izomcsindex);
    }
}

function izomcsopTorol(enapindex, izomcsindex) {
    edzes[enapindex].torolIcsop(izomcsindex);
    if (edzes[enapindex].izomcsoport.length == 0) {
        ienapTorol(enapindex);
    }
}

function ienapTorol(enapindex) {
    var tmpen = new Array();
    for (var i = 0; i < edzes.length; i++) {
        if (i == enapindex) {
            continue;
        }
        tmpen.push(edzes[i]);
    }
    edzes = tmpen;
}

function adatMent() {
    var xhttp = new HttpClient();
    var payload = new String();
    xhttp.requestType = "POST";
    xhttp.isAsync = true;
    xhttp.callback = function(res) {
        //ezekre az alertekre modal-t kellene készítenem
        var obj = JSON.parse(res);
        if (obj.hiba) {
            modalErtesito(obj.hiba);
        } else if (obj.sikeres) {
            modalErtesito("Sikeres mentés");
        } else {
            modalErtesito("Ismeretlen viselkedés");
        }

        eTervekDBKeres();
    }

    if (edzes.length == 0) {
        modalErtesito("Nincs küldendő adat!");
        return;
    }

    payload += "eterv=" + JSON.stringify(edzes);
    xhttp.makeRequest("etervmentes.php", payload);
}

function eTervekDBKeres() {
    var xhttp = new HttpClient();
    var payload = new String();
    xhttp.requestType = "POST";
    xhttp.isAsync = true;
    xhttp.callback = function(res) {
        //ezekre az alertekre modal-t kellene készítenem
        var obj = JSON.parse(res);
        var str = new String();
        if (obj.hiba) {
            modalErtesito(obj.hiba);
        } else if (obj.etervID && obj.etervEID) {
            var etervID = obj.etervID;
            var etervEID = obj.etervEID;

            str += "<div class='list-group'>";
            for (var i = 0; i < etervID.length; i++) {
                str += "<a href='javascript:void(0)' onclick='eTervekDBBetolt(\"" + etervID[i] + "\",\"" +
                    etervEID[i] + "\")' class='list-group-item'>" +
                    "" + etervEID[i] + "</a>";
            }
            str += "</div>";

            $("#eTervekDB").html(str);
        } else if (obj.ures) {
            str += "Üres";
            $("#eTervekDB").html(str);
        }
    }

    payload += "ekeres=1";
    xhttp.makeRequest("etervmentes.php", payload);
}

function eTervekDBBetolt(userid, eid) {
    var xhttp = new HttpClient();
    var payload = new String();
    xhttp.requestType = "POST";
    xhttp.isAsync = true;
    xhttp.callback = function(res) {
        //ezekre az alertekre modal-t kellene készítenem
        var obj = JSON.parse(res);
        var str = new String();
        if (obj.hiba) {
            modalErtesito(obj.hiba);
        } else if (obj.eterv) {
            //console.log(obj.eterv);
            var eoe = makeEdzestervFromDB(obj.eterv);
            modalErtesito(printEdzesTervFromDB(eoe));
        } else if (obj.ures) {
            str += "Üres";
            modalErtesito(str);
        }
    }

    payload += "ekeres=2&userID=" + userid + "&eid=" + eid;
    xhttp.makeRequest("etervmentes.php", payload);
}

function modalErtesito(info) {
    $("#ertesito_adat").html(info);
    //$("#btnPrint").html("<button type='button' class='btn btn-default' onclick='printTerv()'><span class='glyphicon glyphicon-print'></span></button>");
    $("#ertesito").modal();
}

//csak a modal - betöltött adatbázis edzéstervhez
function printEdzesTervFromDB(obj) {
    var str = new String();

    str += "<div class='panel panel-default'>";
    str += "<div class='panel-heading'><h3>Edzésterv</h3></div>";
    str += "<div class='panel-body'>";

    if (obj.length == 0) {
        str += "Nincsenek megjelenítendő elemek!";
        str += "</div>";
        str += "</div>";
        return str;
    }

    str += "<div class='table-responsive'>";
    str += "<table class='table table-bordered table-striped table-condensed'>";
    str += "<thead><tr>";
    str += "<th>Edzésnap</th><th>Izomcsoportok és gyakorlatok</th>";
    str += "</tr></thead>";
    str += "<tbody>";
    for (var i = 0; i < obj.length; i++) {

        str += "<tr><td>" + obj[i].edzesnap + "</td>";
        str += "<td colspan='2'>";
        //belső tábla
        str += "<table class='table talbe-bordered table-hover'>";
        str += "<thead><tr><th>Izomcsoport</th><th>Gyakorlat</th></tr></thead><tbody>";
        for (var k = 0; k < obj[i].izomcsoport.length; k++) {
            str += "<tr><td>" + obj[i].izomcsoport[k].ineve + "</td>";
            str += "<td>";
            for (var j = 0; j < obj[i].izomcsoport[k].gyak.length; j++) {
                str += obj[i].izomcsoport[k].gyak[j].gyaknev + " ";
                str += obj[i].izomcsoport[k].gyak[j].sorozat + "x" +
                    obj[i].izomcsoport[k].gyak[j].ismTol + "-" +
                    obj[i].izomcsoport[k].gyak[j].ismIg + "<br>";
            }
            str += "</td></tr>";
        }
        str += "</tbody></table>";
        //belső tábla vége
        str += "</td></tr>";

    }

    str += "</tbody>";
    str += "</table>";
    str += "</div>"; //table-responsive
    str += "</div>";
    str += "</div>";
    return str;
}

function makeEdzestervFromDB(nyersobj) {
    var dbedzesterv = new Array();
    var edtGyak = undefined;
    for (var i = 0; i < nyersobj.napok.length; i++) {
        dbedzesterv.push(new EdzesTerv(nyersobj.napok[i]));
    }

    for (var i = 0; i < nyersobj.adat.length; i++) {
        for (var k = 0; k < dbedzesterv.length; k++) {
            if (dbedzesterv[k].edzesnap == nyersobj.adat[i].edzesnap) {
                for (var o = 0; o < nyersobj.adat[i].gyak.length; o++) {
                    edtGyak = new EtervGyakorlat(nyersobj.adat[i].gyak[o],
                        getSorSzam(nyersobj.adat[i].sor[o], "x"),
                        getIsmTol(nyersobj.adat[i].sor[o]),
                        getIsmIg(nyersobj.adat[i].sor[o]));
                    //dbedzesterv[k].addIzomcsoport(nyersobj.adat[i].ineve, nyersobj.adat[i].gyak[o]);
                    dbedzesterv[k].addIzomcsoport(nyersobj.adat[i].ineve, edtGyak);
                }
            }
        }
    }

    return dbedzesterv;
}

function getSorSzam(sorozat, _explodejel) {
    var arr = sorozat.split(_explodejel);
    return arr[0]; //elvileg az x jell első eleme a sorozat
}

function getIsmTol(sorozat) {
    var arr = sorozat.split("x");
    var ism = arr[1].split("-");
    return ism[0];
}

function getIsmIg(sorozat) {
    var arr = sorozat.split("x");
    var ism = arr[1].split("-");
    return ism[1];
}

function printTerv() {
    window.print();
}

var globHint = "";

$(document).ready(function() {
    eTervekBetoltes("#eTervek");
    eTervekDBKeres();
    toltEdzesTerv();
    printEdzesTerv("#edzesterv");

    //a sorozat ismétlés tipp eltüntetésére
    $("#sorism").on('click', function() {
        if ($(this).text() != 'help...') {
            globHint = $(this).text();
            $(this).text('help...');
            $(this).css('cursor', 'help');
        } else {
            $(this).text(globHint);
            $(this).css('cursor', 'pointer');
        }
    });

});