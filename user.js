var uzenetObj = null;
var uzenetSzam = null;
var torolveObj = null;
var felh = null;
var sajatAdat = null;
var uzenetTime = false;
var voltanim = false;
var loc = window.location.href.split("/");
var oldalfilecim = loc[loc.length - 1];

$(document).ready(function() {
    console.log("User.js betöltve");
    //olvasatlan üzenetek jelzése
    //uzenetOlv();
    //beállítom hogy 5mpenként vizsgálja
    uzenetTime = window.setInterval(uzenetOlv2, 5000);

    if (oldalfilecim == "felhasznalo.html") {
        uzenetOlv();
        felhOlv(0);
        felhOlv(1);
    }

});

function felhOlv(postvan) {
    var xht = new HttpClient();
    xht.isAsync = true;
    var adat = null;
    xht.callback = function(result) {
        if (postvan) {
            sajatAdat = JSON.parse(result);
            felhMegjelenitese(null, "#sajatAdatok");
        } else {
            felh = JSON.parse(result);
            felhMegjelenitese("#felhasznalok", null);
        }

    }

    if (postvan) {
        xht.requestType = "POST";
        adat = "sajat=1";
    }

    xht.makeRequest("felh.php", adat);

}

function uzenetOlv() {
    var xht = new HttpClient();

    xht.isAsync = true;

    xht.callback = function(result) {
        uzenetObj = JSON.parse(result);
        doitUzenet2();
    }

    xht.makeRequest("uzenetolv.php", null);
}

function uzenetOlv2() {
    var kapcs = "kapcsoloval=1";
    var xht = new HttpClient();

    xht.isAsync = true;
    xht.requestType = "POST";

    xht.callback = function(result) {
        uzenetSzam = JSON.parse(result);
        doitUzenet();
    }

    xht.makeRequest("uzenetolv.php", kapcs);

    //evvel egyetemben frissitem a listát
    //printUzenetek("#uzenetek");
}


//feldolgozás
function doitUzenet() {
    if (uzenetSzam != null && uzenetSzam.uziszam > 0) {
        //olvasatlan üzenetek számának kijelzése
        if (uzenetSzam.uzinote) {
            console.log(uzenetSzam.uzinote);
        }
        if (oldalfilecim == "felhasznalo.html") {
            uzenetOlv();
        }
        $("#uziPot1").html(uzenetSzam.uziszam);
        $("#uziPot2").html(uzenetSzam.uziszam);
        anim();
    } else if (uzenetSzam != null && uzenetSzam.hiba) {
        $("#uziPot1").html("");
        $("#uziPot2").html("");
        console.log("uzenetSzam hiba" + uzenetSzam.hiba);
    } else {
        $("#uziPot1").html("");
        $("#uziPot2").html("");
        //console.log("NULL uzenetSzam");
    }
    //console.log("doitUzenet lement");
}

function doitUzenet2() {
    if (uzenetObj || uzenetObj.uzik) {
        printUzenetek("#uzenetek");
    }
}

function anim() {
    if (uzenetSzam != null && uzenetSzam.uziszam > 0) {
        $("#uziJelzo1").addClass("uzenetJelzo");
        $("#uziJelzo2").addClass("uzenetJelzo");
    } else {
        $("#uziJelzo1").removeClass("uzenetJelzo");
        $("#uziJelzo2").removeClass("uzenetJelzo");
    }
}

function printUzenetek0(id) {
    var str = new String();
    if (uzenetObj.uzik) {
        var o = uzenetObj.uzik;
        for (var i = 0; i < o.length; i++) {
            str += "<div class='panel panel-default'>";
            str += "<div class='panel-heading'>";
            str += "<div class='row'>";
            str += "<div class='col-md-10'>";
            str += "<h3>" + o[i].uzicim + "</h3>";
            str += "</div><div class='col-md-2'>";
            str += "<div class='btn-group'>";
            str += "<button type='button' class='btn btn-default' onclick='delUsrMsg(\"" + o[i].uid + "\")'><span class='glyphicon glyphicon-remove'></span></button>";
            str += "<button type='button' class='btn btn-default' onclick='showUsrMsg(\"" + o[i].kitol + "\")'><span class='glyphicon glyphicon-envelope'></span></button>";
            str += "</div></div></div>";

            str += "</div>";
            str += "<div class='panel-body'>";
            str += "<p>" + o[i].uzibody + "</p>";
            str += "</div>";
            str += "<div class='panel-footer'>";
            str += "<span class='label label-warning' style='font-size:1.2em'>Küldő: " + o[i].kitol + "</span>";
            str += "<span class='pull-right'>" + o[i].mikor + "</span>";
            str += "</div>";
            str += "</div>";
        }
    }


    if (uzenetObj.uzinote) {
        str += "<h3>" + uzenetObj.uzinote + "</h3>";
    }

    $(id).html(str);
}

function printUzenetek(id) {
    var str = new String();
    if (uzenetObj.uzik) {
        var o = uzenetObj.uzik;
        for (var i = 0; i < o.length; i++) {
            str += "<div class='col-md-12'>";
            str += "<blockquote>";
            str += "<div class='btn-group pull-right'>";
            str += "<button type='button' class='btn btn-default' onclick='showUsrMsg(\"" + o[i].kitol + "\")'><span class='glyphicon glyphicon-envelope'></span></button>";
            str += "<button type='button' class='btn btn-default' onclick='delUsrMsg(\"" + o[i].uid + "\")'><span class='glyphicon glyphicon-remove'></span></button>";
            str += "</div>";
            str += "<h3>" + o[i].uzicim + "</h3>";
            str += "<p>" + o[i].uzibody + "</p>";
            str += "<footer>";
            str += "<span class='label label-warning' style='font-size:1.2em'>Küldő: " + o[i].kitol + "</span>";
            str += "<span class='pull-right'>" + o[i].mikor + "</span>";
            str += "</footer>";
            str += "</blockqoute><br>";
            str += "</div>";
        }
    }


    if (uzenetObj.uzinote) {
        str += "<h3>" + uzenetObj.uzinote + "</h3>";
    }

    $(id).html(str);
}

function delUsrMsg(uid) {
    var xht = new HttpClient();
    xht.isAsync = true;
    xht.requestType = "POST";

    xht.callback = function(result) {
        torolveObj = JSON.parse(result);
        if (torolveObj && torolveObj.siker) {
            $("#uzivalasz").html("<h1>" + torolveObj.siker + "</h1>");
        }

        $("#usrMsgDel").modal();
        //frissitem az üziket a szerverről,hogy már ne látszódjon a törölt elem
        uzenetOlv();
    }

    xht.makeRequest("uzenetolv.php", "torol=" + uid);
}

function felhMegjelenitese(id, id2) {
    //console.log(felh.felhs);
    var str = new String();
    var str_id2 = new String();
    var sajat_adat_megoszt = undefined;
    var cols = 5;
    if (felh && id && !felh.hiba) {
        //ide cserélem a kodot és táblázatot fogok készíteni
        //egy táblázat készítő függvénnyel
        var o = felh.felhs;
        str += "<h2 class='text-center'>Regisztált felhasználók listája</h2>";
        str += "<div class='col-md-12'>";
        str += tablazatKeszito(o, ["Név", "Súly", "Fekvenyomás", "Guggolás", "Felhúzás", "Magasság", "Regisztáció", "Naplók", ""]);
        str += "</div>";
    } else if (sajatAdat && id2 && !sajatAdat.hiba) {
        //saját adatok...
        var o = sajatAdat.felhs;
        for (var i = 0; i < o.length; i++) {
            //a szerkesztés kiválasztását elég béna modon oldottam meg,ezen majd még elkell gondolkozni 
            //és kihasználni a jquery adottságait
            str_id2 += "<div class='panel panel-default'>";
            str_id2 += "<div class='panel-heading'>";
            str_id2 += "<h3>" + o[i].knev + " " + o[i].vnev + " <span class='label label-warning'>" + o[i].email + "</span></h3>";
            str_id2 += "</div>";
            str_id2 += "<div class='panel-body' id='felh_adatok_lista'>";
            str_id2 += "<h4>Személyes adatok</h4>";
            str_id2 += "<ul class='list-group'>";
            str_id2 += "<li class='list-group-item'>Megye: <span id='sr_megye'>" + o[i].megye + "</span></li>";
            str_id2 += "<li class='list-group-item'>Város: <span id='sr_varos'>" + o[i].varos + "</span></li>";
            str_id2 += "<li class='list-group-item'>Cím: <span id='sr_cim'>" + o[i].cim + "</span></li>";
            str_id2 += "<li class='list-group-item'>Irányítószám: <span id='sr_irsz'>" + o[i].iranyitoszam + "</span></li>";
            str_id2 += "</ul>";
            str_id2 += "<h4>Edző adatok</h4>";
            str_id2 += "<ul class='list-group'>";
            str_id2 += "<li class='list-group-item'>Súly: <span id='sr_suly'>" + o[i].suly + "</span></li>";
            str_id2 += "<li class='list-group-item'>Max Fekvenyomás: <span id='sr_mfek'>" + o[i].maxfek + "</span></li>";
            str_id2 += "<li class='list-group-item'>Max guggolás: <span id='sr_mgugg'>" + o[i].maxgugg + "</span></li>";
            str_id2 += "<li class='list-group-item'>Max felhúzás: <span id='sr_mfelhuz'>" + o[i].maxfelhuz + "</span></li>";
            str_id2 += "<li class='list-group-item'>Magasság: <span id='sr_magassag'>" + o[i].magassag + "</span></li>";
            str_id2 += "</ul>";
            str_id2 += "<h4>Érdekességek</h4>";
            str_id2 += "<ul class='list-group'>";
            str_id2 += "<li class='list-group-item'>Eddigi mentett naplók: " + o[i].mszam + "</li>";
            str_id2 += "<li class='list-group-item'>Megosztás: ";
            str_id2 += "<label class='radio-inline'><input type='radio' name='sr_megoszt' id='megoszt_igen' value='1'>Igen</label>";
            str_id2 += "<label class='radio-inline'><input type='radio' name='sr_megoszt' id='megoszt_nem' value='0'>Nem</label>";
            str_id2 += "</li>";
            str_id2 += "</ul>";
            str_id2 += "<span id='menteni'></span>";
            str_id2 += "</div>";
            str_id2 += "<div class='panel-footer'>";
            str_id2 += "<h4>Regisztált: " + o[i].letrejott + "</h4>";
            str_id2 += "</div>";
            str_id2 += "</div>";
            sajat_adat_megoszt = parseInt(o[i].megoszt);
        }
    } else {
        if (felh && felh.hiba) {
            str += "<h5>:::" + felh.hiba + " :::</h5>";

        }

        if (sajatAdat && sajatAdat.hiba) {
            str_id2 += "<h5>::: " + sajatAdat.hiba + " :::</h5>";
        }
    }

    $(id).html(str);
    $(id2).html(str_id2);

    if (sajat_adat_megoszt != undefined) {
        if (sajat_adat_megoszt == 1) {
            $("#megoszt_igen").attr("checked", true);
        } else {
            $("#megoszt_nem").attr("checked", true);
        }
    }

    $("input[type=radio][name=sr_megoszt]").change(function() {
        $("#menteni").html(m_gomb());
    })

    $("#felh_adatok_lista span[id^='sr_']").addClass("label label-default useradatbadge");
    $("#felh_adatok_lista span[id^='sr_']").dblclick(function() {
        var inputme = $("<input>");
        var old = $(this).text();
        var adatid = $(this).attr('id');
        inputme.attr("type", "text");
        inputme.attr("class", "form-control");
        inputme.attr("value", old);
        inputme.attr("id", "inp");

        $(this).html(inputme);
        $("#inp").focus();

        $("#inp").on("change", function() {
            var ujertek = $(this).val();
            //itt ellenörzést kell majd végrehajtanom
            if (usrAdatSzerk(adatid, ujertek)) {
                $(this).parent().html(ujertek);
            } else {
                $(this).parent().html("Error");
                $("#menteni").html("");
            }

        });
    });

}

function usrAdatSzerk(adat, ujadat) {
    var token = adat.split("_");
    var tok = token[1];

    switch (tok) {
        case "megye":
            if (!adatcheck(ujadat)) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].megye = ujadat;
            break;
        case "varos":
            if (!adatcheck(ujadat)) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].varos = ujadat;
            break;
        case "cim":
            if (!adatcheck(ujadat)) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].cim = ujadat;
            break;
        case "irsz":
            var regel = new RegExp(/^(\d{1})(\d{1})(\d{1})(\d{1})$/, "i");
            if (!regel.test(ujadat)) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].iranyitoszam = ujadat;
            break;
        case "suly":
            if (!adatcheck(parseInt(ujadat))) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].suly = ujadat;
            break;
        case "mfek":
            if (!adatcheck(parseInt(ujadat))) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].maxfek = ujadat;
            break;
        case "mgugg":
            if (!adatcheck(parseInt(ujadat))) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].maxgugg = ujadat;
            break;
        case "mfelhuz":
            if (!adatcheck(parseInt(ujadat))) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].maxfelhuz = ujadat;
            break;
        case "magassag":
            if (!adatcheck(parseInt(ujadat))) {
                alert("Hiba merült fel az adatbevitel során");
                $("#menteni").html("");
                return false;
            }
            sajatAdat.felhs[0].magassag = ujadat;
            break;
        default:
            return false;
            break;
    }


    $("#menteni").html(m_gomb());
    return true;
}

function m_gomb() {
    return "<button class='btn btn-primary btn-block' onclick='adatfrissit()'>Ment</button>";
}

function adatcheck(adat) {
    var regSzam = new RegExp(/[0-9]+/, "ig");
    var regString = new RegExp(/[A-Za-z0-9 \-\!\?\.,]+/, "ig");
    if (adat == null) {
        return false;
    }

    switch (typeof(adat)) {
        case "undefined":
            return false;
            break;
        case "string":
            if (adat.length > 50) { return false; }
            if (!regString.test(adat)) { return false; } else { return true; }
            break;
        case "number":
            var n = new String(adat);
            if (n.length > 3) { return false; }
            if (adat == 0) { return false; }
            if (!regSzam.test(adat)) { return false; } else { return true; }
            break;
        case "boolean":
            return false;
            break;
        default:
            return false;
            break;
    }
}

function adatfrissit() {
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    var adatok = "";
    xhttp.callback = function(res) {
        $("#hibaJelzo").html(res);
        $("#menteni").html("");
    };

    //átadom az űrlap értékeit a php scriptemnek
    adatok += "volte=volt&";
    adatok += "megye=" + $("#sr_megye").text() + "&";
    adatok += "varos=" + $("#sr_varos").text() + "&";
    adatok += "cim=" + $("#sr_cim").text() + "&";
    adatok += "iranyitoszam=" + $("#sr_irsz").text() + "&";
    adatok += "sajatsuly=" + $("#sr_suly").text() + "&";
    adatok += "mellcsucs=" + $("#sr_mfek").text() + "&";
    adatok += "guggolocsucs=" + $("#sr_mgugg").text() + "&";
    adatok += "felhuzocsucs=" + $("#sr_mfelhuz").text() + "&";
    adatok += "magassag=" + $("#sr_magassag").text() + "&";
    adatok += "megoszt=" + $("input[type=radio][name=sr_megoszt]:checked").val() + "&";

    xhttp.makeRequest("felhupdate.php", adatok);
}

function showUsrMsg(name) {
    $("#usrMsgTo").text("" + name);
    $("#usrMsg").modal();
}

function usrMsgKuld() {
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(res) {
        $("#hibaDiv2").html(res);
        $("#uzi_cim").val("");
        $("#uzi_note").val("");
    }
    var adatok = "";
    adatok += "volte=volt&";
    adatok += "kinek=" + $("#usrMsgTo").text() + "&";
    adatok += "uzi_cim=" + $("#uzi_cim").val() + "&";
    adatok += "uzi_note=" + $("#uzi_note").val();

    xhttp.makeRequest("uuzikuldo.php", adatok);
}

function delUsr(email) {
    alert("Fejlesztés alatt");
}


//ha minden igaz akkor a hirkuldo.jsből használok egykét funkciót
function usrMsgK() {
    uziVisszajelzestEltavolit();
    var hibak = uziUrlapotEllenoriz();
    if (hibak == "") {
        usrMsgKuld();
        //üzi beküldése ajaxal
        return true;
    } else {
        visszajelzestAd(hibak);
        return false;
    }
}

$("button[type='reset']").on("click", function() {
    uziVisszajelzestEltavolit();
});

function tablazatKeszito(o, objHead) {
    var str = new String();
    str += "<div class='table-responsive'>";
    str += "<table class='table table-hover table-bordered wh'>";
    str += "<thead><tr>";
    for (var i = 0; i < objHead.length; i++) {
        str += "<th>" + objHead[i] + "</th>";
    }
    str += "</tr></thead>";
    str += "<tbody>";
    for (var i = 0; i < o.length; i++) {
        if ($.cookie('felhasznalo') == o[i].email) {
            continue;
        }
        str += "<tr>";
        str += "<td>" + o[i].knev + " " + o[i].vnev + "</td>";
        /*var s = o[i].email.replace("@", " at ");
        s = s.replace(".", " dot ");
        str += "<td>" + s + "</td>";*/
        str += "<td>" + o[i].suly + "</td>";
        str += "<td>" + o[i].maxfek + "</td>";
        str += "<td>" + o[i].maxgugg + "</td>";
        str += "<td>" + o[i].maxfelhuz + "</td>";
        str += "<td>" + o[i].magassag + "</td>";
        str += "<td>" + o[i].letrejott + "</td>";
        str += "<td>" + o[i].mszam + "</td>";
        str += "<td>";
        str += "<div class='btn-group'>";
        str += "<button type='button' class='btn btn-default btn-xs' onclick='showUsrMsg(\"" + o[i].email + "\")'><span class='glyphicon glyphicon-envelope'></span></button>";
        str += o[i].delu;
        str += "</div></td>";
        str += "</tr>";
    }

    str += "</tbody></table></div>";

    return str;
}