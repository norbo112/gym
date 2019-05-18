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
}

function IzomCsop(name) {
    this.gyak = new Array();
    this.ineve = name;
    this.addGyak = function(gyakpar) {
        if (this.gyak.length != 0) {
            for (var i = 0; i < this.gyak.length; i++) {
                if (this.gyak[i] == gyakpar) {
                    return;
                }
            }

            this.gyak.push(gyakpar);
        } else {
            this.gyak.push(gyakpar);
        }
    }
}

function addEdzesTervElem() {
    var ed_nap = $("#edzesnap").val();
    var ed_icsop = $("#izomcsoportok").val();
    var ed_gyak = $("#gyaksik_icsop").val();
    var edt = undefined;

    if (ed_nap == valasz ||
        ed_icsop == valasz ||
        ed_gyak == "Válasz csoportot") {
        alert("Kérlek válassz edzésnapot, izomcsoportot és gyakorlatot");
        return;
    }

    for (var i = 0; i < edzes.length; i++) {
        if (edzes[i].edzesnap == ed_nap) {
            console.log("edzésnap megegyezik" + ed_nap);
            edzes[i].addIzomcsoport(ed_icsop, ed_gyak);
            printEdzesTerv("#edzesterv");
            return;
        }
    }

    if (edzes.length == 0) {
        edt = new EdzesTerv(ed_nap);
        edt.addIzomcsoport(ed_icsop, ed_gyak);
        edzes.push(edt);
    } else {
        edt = new EdzesTerv(ed_nap);
        edt.addIzomcsoport(ed_icsop, ed_gyak);
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
    str += "<th>Edzésnap</th><th>Izomcsoportok és gyakorlatok</th><th></th>";
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
            str += "<tr><td>" + edzes[i].izomcsoport[k].ineve + " " + rii(i + "_" + k + "_eicsop") + "</td>";
            str += "<td>";
            for (var j = 0; j < edzes[i].izomcsoport[k].gyak.length; j++) {
                str += edzes[i].izomcsoport[k].gyak[j] + " " + rii(i + "_" + k + "_" + j + "_gyak") + "<br>";
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
    for (var i = 0; i < obj.length; i++) {
        var a_edzesnap = new EdzesTerv(obj[i].edzesnap);
        for (var k = 0; k < obj[i].izomcsoport.length; k++) {
            var a_izomcsop = new IzomCsop(obj[i].izomcsoport[k].ineve);
            for (var j = 0; j < obj[i].izomcsoport[k].gyak.length; j++) {
                a_izomcsop.gyak.push(obj[i].izomcsoport[k].gyak[j]);
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
    switch (tt[strTomb.length - 1]) {
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
}

function gyakTorol(enapindex, izomcsindex, gyakindex) {

}

function izomcsopTorol(enapindex, izomcsindex) {

}

function ienapTorol(enapindex) {

}

$(document).ready(function() {
    eTervekBetoltes("#eTervek");
    toltEdzesTerv();
    printEdzesTerv("#edzesterv");
});