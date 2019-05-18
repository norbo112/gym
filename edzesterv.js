//edzéstervhet készített js fájl
var edzes = new Array();
var valasz = "Kérlek, válassz...";

function EdzesTerv(edzesnap) {
    this.edzesnap = edzesnap;
    this.izomcsoport = new Array();
    this.addIzomcsoport = function(izomcs, gyaks) {
        if (this.izomcsoport.length != 0) {
            for (var i = 0; i < this.izomcsoport.length; i++) {
                if (this.izomcsoport[i].name == izomcs) {
                    this.izomcsoport[i].addGyak(gyaks);
                    return;
                } else {
                    this.izomcsoport.push(new IzomCsop(izomcs, gyaks));
                    return;
                }
            }
        } else {
            this.izomcsoport.push(new IzomCsop(izomcs, gyaks));
        }
        console.log("Add izomcsoport lefutott");
    }
}

function IzomCsop(name, gyaks) {
    this.gyak = new Array();
    this.name = name;
    this.gyak.push(gyaks);
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

    if (ed_nap == valasz ||
        ed_icsop == valasz ||
        ed_gyak == "Válasz csoportot") {
        alert("Kérlek válassz edzésnapot, izomcsoportot és gyakorlatot");
        return;
    }

    if (edzes.length != 0) {
        for (var i = 0; i < edzes.length; i++) {

            if (edzes[i].edzesnap == ed_nap) {
                edzes[i].addIzomcsoport(ed_icsop, ed_gyak);
                printEdzesTerv("#edzesterv");
                return;
            } else {
                var edt = new EdzesTerv(ed_nap);
                edt.addIzomcsoport(ed_icsop, ed_gyak);
                edzes.push(edt);
                printEdzesTerv("#edzesterv");
                return;
            }
        }
    } else {
        var edt = new EdzesTerv(ed_nap);
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
    str += "<table class='table table-bordered table-hover table-condensed'>";
    str += "<thead><tr>";
    str += "<th>Edzésnap</th><th>Izomcsoportok és gyakorlatok</th><th></th>";
    str += "</tr></thead>";
    str += "<tbody>";
    for (var i = 0; i < edzes.length; i++) {

        str += "<tr>";
        str += "<td>" + edzes[i].edzesnap + "</td>";
        str += "<td colspan='2'>";
        //belső tábla
        str += "<table class='table talbe-bordered table-hover'>";
        str += "<thead><tr><th>Izomcsoport</th><th>Gyakorlat</th></tr></thead><tbody>";
        for (var k = 0; k < edzes[i].izomcsoport.length; k++) {
            str += "<tr><td>" + edzes[i].izomcsoport[k].name + "</td>";
            str += "<td>";
            for (var j = 0; j < edzes[i].izomcsoport[k].gyak.length; j++) {
                str += edzes[i].izomcsoport[k].gyak[j] + "<br>";
            }
            str += "</td>";
        }
        str += "</tbody></table>";
        //belső tábla vége
        str += "</td>";

    }

    str += "</tbody>";
    str += "</table>";
    str += "</div>"; //table-responsive
    str += "</div>";
    str += "</div>";
    $(id).html(str);
}

$(document).ready(function() {
    printEdzesTerv("#edzesterv");
});