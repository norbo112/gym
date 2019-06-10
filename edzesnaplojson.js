//napi tevékenységhez tartozó megyjegyzés
var naplonote = "";
var automentes = null;
var myobj = null;
var mentettNaplok = null;
var inf = new Info();

//korábbi sulyokhoz
var korabbisuly = new Array();

function KOssz(id, megnevezes, aktualsuly) {
    this.megnevezes = megnevezes;
    this.id = id;
    this.aktual = aktualsuly;
}

var gyakorlatok = new Array();
var mgyk = null; //ebbol fogok ujraépíteni a gyakorlatok tömböt
//suly ill ismétlések változtatására tömbbe tárolom az elemeket
var sulyelem = new Array();
var ismelem = new Array();

//kell a cookie felhasználó a megfelelő adattárolásra (automentés)
var felh = null;

//Gyakorlat
function Gyakorlat(name, suly, ism, megj) {
    this.Name = name;
    this.Suly = new Array();
    this.Ism = new Array();
    this.RogzitesIdopont = new Date();
    //console.log("Gyak rögz idő:" + this.RogzitesIdopont.getTime());
    this.IsmRogzitesIdopontja = new Array();
    this.Megjegyzes = new String(megj);

    this.RogzitesIdopont.setHours(
        this.RogzitesIdopont.getHours() -
        this.RogzitesIdopont.getTimezoneOffset() / 60);

    if (typeof(suly) == "string") {
        this.Suly.push(suly);
        this.Ism.push(ism);
    }

    this.osszSuly = function() {
        var num = 0;
        for (var i = 0; i < this.Suly.length; i++) {
            num += parseInt(this.Suly[i]) * parseInt(this.Ism[i]);
        }
        return num;
    }
    this.addSuly = function(suly0) {
        this.Suly.push(suly0);
    }
    this.addIsm = function(ism0) {
        this.Ism.push(ism0);
    }
    this.addIsmIdo = function(ismido) {
        //var d = new Date(ismido);
        //console.log("object:: " + d);
        //d.setHours(d.getHours() -d.getTimezoneOffset() / 60);
        this.IsmRogzitesIdopontja.push(ismido);
    }
    this.setSuly = function(index, ujsuly) {
        this.Suly[index] = ujsuly;
    }
    this.setIsm = function(index, ujism) {
        this.Ism[index] = ujism;
    }

    //nem használt két függvény
    this.getDateString = function() {
        var adat = "" + this.RogzitesIdopont.getFullYear() + "-";
        adat += (this.RogzitesIdopont.getMonth() + 1) + "-";
        adat += this.RogzitesIdopont.getDate() + " ";
        adat += this.RogzitesIdopont.getHours() + ":";
        adat += this.RogzitesIdopont.getMinutes() + ":";
        adat += this.RogzitesIdopont.getSeconds();
        return adat;
    }
    this.getIsmDateString = function(k) {
        var adat = "" + this.IsmRogzitesIdopontja[k].getFullYear() + "-";
        adat += (this.IsmRogzitesIdopontja[k].getMonth() + 1) + "-";
        adat += this.IsmRogzitesIdopontja[k].getDate() + " ";
        adat += this.IsmRogzitesIdopontja[k].getHours() + ":";
        adat += this.IsmRogzitesIdopontja[k].getMinutes() + ":";
        adat += this.IsmRogzitesIdopontja[k].getSeconds();
        return adat;
    }
}

function mentes() {
    //console.log("mentés elindult");
    //JSONba akarom elmenteni a napló adatokat

    if (gyakorlatok.length == 0) {
        //$("#mentes_egyebb").html("");
        checkLogin("#mentes_egyebb");
        //$("#mentes_egyebb").append("<div class='alert alert-warning alert-dismissible'>" +
        //    "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" +
        //    "A gyakorlat lista üres!</div>");
        inf.showinfo("Információ", "A gyakorlat lista üres!");
        console.log("mentés megszakadt, 0 gyakorlat");
        return;
    }

    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    var adatok = "gyaksik=" + JSON.stringify(gyakorlatok) + "&";
    adatok += "naplonote=" + naplonote;
    xhttp.callback = function(result) {
        //$("#mentes_egyebb").append("<div class='alert alert-warning alert-dismissible'>" +
        //    "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + result + "</div>");
        inf.showinfo("Információ", result, "white");
        korabbiNaplok("#korabbinaplok");
    }


    xhttp.makeRequest("naplosavejson.php", adatok);

    //console.log("mentés végrehajtódott");
}


$(document).ready(function() {
    console.log("document ready function");
    felh = $.cookie("felhasznalo");

    if (felh == null || felh == "") {
        felh = "ALT";
    }

    checkLogin("#mentes_egyebb");

    printGyakorlat(gyakorlatok);
    rogzitettIdoMegjelenito(gyakorlatok, "#gyakIdopontok");


    ujgyakaktiv();
});

function checkLogin(megjelenito) {
    var megh = new String();

    korabbiNaplok("#korabbinaplok");
    if (getNevem()) {

        //megjelenítés, de itt fogom beolvasni a webstorage-ból az 
        // elmentett edzésnapitevékenység objektumomat
        //ha esetleg aktiv az automentés akkor deaktiválom
        if (automentes) {
            window.clearInterval(automentes);
        }

        var tempmgyk = localStorage.getItem(felh + "_gyakorlatok");
        var temp = localStorage.getItem(felh + "_naplonote");
        if (tempmgyk != null || tempmgyk != "") {
            mgyk = JSON.parse(tempmgyk);
        } else {
            mgyk = null;
        }

        if (temp != null || temp != "") {
            naplonote = temp;
        } else {
            naplonote = "";
        }

        if (mgyk == null) {
            console.log("Sikertelen adatvisszatöltés");
        } else {
            gyakorlatEpitoFromMentes();
        }

        //megh += getMentGombok();
    } else {
        megh += "";
        //ez a visszatöltés független hogy bevan e jelentkezve
        //reg nélkül is lehet tárolni a napi edzést, de csak az adott napot
        var tempmgyk = localStorage.getItem(felh + "_gyakorlatok");
        var temp = localStorage.getItem(felh + "_naplonote");
        if (tempmgyk != null || tempmgyk != "") {
            mgyk = JSON.parse(tempmgyk);
        } else {
            mgyk = null;
        }

        if (temp != null || temp != "") {
            naplonote = temp;
        } else {
            naplonote = "";
        }

        if (mgyk == null) {
            console.log("Sikertelen adatvisszatöltés");
        } else {
            gyakorlatEpitoFromMentes();
        }
    }

    $(megjelenito).html(megh);
    $("#nnote").val(naplonote);
}

function getMentGombok() {
    var megh = new String();

    if (!getNevem()) {
        megh += "Kérlek jelentkezz be...";
        return megh;
    }

    //megh += "<ul class='pager'>";
    megh += "<div class='form-group'>";
    megh += "<button type='button' class='btn btn-default' onclick='mentes()'>Mentés</button>";
    //megh += "<li><button type='button' class='btn btn-primary' onclick='notesmentes()'>Megyjegyzés a naplóhoz</button></li>";
    megh += "<button type='button' class='btn btn-default' onclick='automenttorol()'>AutoMentés törlése</button>";
    //megh += "</ul>";
    megh += "</div>";
    return megh;
}

//napló note hozzáadása, majd ezt is bekell építenem a mentésbe, hogy notes is belekerüljön
//és továbbá a php-ba is bekell szúrnom a napló note táblába

function ujgyakaktiv() {
    //ezt egy php szkriptből adom meg, ugyanis ha a felhasználó nem admin, akkor
    //nem adhat hozzá új gyakorlatot
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.callback = function(res) {
        //persze ellenörzöm be van e jelentkezve
        $("#addujgyak").html(res);
    }

    xhttp.makeRequest("admincheck.php", null);
}

function notesmentes() {
    if (naplonote != "") {
        $("#nnote").val(naplonote);
    }
    //$("#naplonoteModal").modal(); nem modalt használok
}

function checkPlatform() {
    var str = navigator.platform;
    var str_tomb = str.split(" ");
    if (str_tomb[0] == "Linux" || str_tomb[1] == "armv8l") {
        alert("Kérlek forgasd el a készüléket " + str_tomb[0] + "," + str_tomb[1]);
    } else {
        console.log("Üdvözletem: " + str_tomb[0]);
    }
}

function addGyakSulyEsIsm(gyaksik) {
    var suly = $("#gySuly").val();
    var ism = $("#gyIsm").val();

    if (isNaN(suly) || suly == "" || parseInt(suly) == 0) {
        $("#hibaGySuly").removeClass("hibaMezoJo");
        $("#hibaGySuly").text("Írd be a használt súlyt, szám legyen, ne hagyd üresen!");
        return;
    }

    if (isNaN(ism) || ism == "" || parseInt(ism) == 0) {
        $("#hibaGyIsm").removeClass("hibaMezoJo");
        $("#hibaGyIsm").text("Írd be a használt súlyt, szám legyen, ne hagyd üresen!");
        return;
    }

    var opt = $("#gyName").val();
    for (var i = 0; i < gyaksik.length; i++) {
        if (gyaksik[i].Name == opt) {
            gyaksik[i].addSuly(suly);
            gyaksik[i].addIsm(ism);
            gyaksik[i].addIsmIdo(new Date());
            printGyakorlat(gyaksik);
        } else {
            console.log("Nincs ilyen gyakorlat az eddigi listában :" + opt);
            //alert("Elöször hozzá kell adnod a gyakorlatot a listához!");
        }
    }

    rogzitettIdoMegjelenito(gyakorlatok, "#gyakIdopontok");
}

function tggle(hol, mit) {
    $(hol).click(function() {
        $(mit).slideToggle(300);
    });
}

function addGyakorlat() {
    //itt ellenőrzöm hogy van e már ilyen gyakorlatom a listában
    //ha van akkor átugrok a suly ismétlés hozzáadásához
    for (var i = 0; i < gyakorlatok.length; i++) {
        if (gyakorlatok[i].Name == $("#gyName").val()) {
            //console.log("addGyakorlat - átugrás az addGyakSulyEsIsm-re");
            addGyakSulyEsIsm(gyakorlatok);
            //itt is frissítem a mentett adatokat
            saveGyakorlatok();
            s_stop();
            s_run();
            return; //és persze innen kilépek
        }
    }
    var suly = $("#gySuly").val();
    if (suly == "" || isNaN(suly)) {
        $("#gySulyHiba").removeClass("hibaVisszajelzes");
        $("#gySuly").addClass("hibaOsztaly");
        return;
    }

    suly = $("#gyIsm").val();
    if (suly == "" || isNaN(suly)) {
        $("#gyIsmHiba").removeClass("hibaVisszajelzes");
        $("#gyIsm").addClass("hibaOsztaly");
        return;
    }

    var notes = $("#gyNote").val();
    var reg = new RegExp(/([A-Za-z0-9]+)/, "ig");
    if (notes != "" && !reg.test(notes)) {
        $("#gyNoteHiba").removeClass("hibaVisszajelzes");
        $("#gyNote").addClass("hibaOsztaly");
        return;
    }
    $("gyNote").removeClass("hibaOsztaly");

    //ha sikeres volt a beküldés akkor eltávolítom a hibák jelzését
    clearHibak();

    //evvel a függvénnyel adom hozzá a tömbömhöz az új gyakorlato
    var egygyak = new Gyakorlat($("#gyName").val(), $("#gySuly").val(),
        $("#gyIsm").val(), notes);
    egygyak.addIsmIdo(new Date());
    gyakorlatok.push(egygyak);

    //a notes-ot csak egyszer elég hozzáadni, tehát hozzáadás után törlöm a mezőt
    $("#gyNote").val("");


    //amikor hozzáadtam a gyakorlatot, 
    //vagy épp az ismétlést akkor elinditom a stoppert
    s_stop();
    s_run();

    //elinditom az automentést, persze nem minden gyakorlat hozzáadásakor
    //csak az elején amikor még ez null értékü
    if (!automentes) {
        timingSaveGyakorlatok();
    }

    //mindenképp updatálom a localstorage tartalmát, de másik függvénnyel, hogy a timing
    //csak egyszer fusson
    saveGyakorlatok();

    printGyakorlat(gyakorlatok);
    rogzitettIdoMegjelenito(gyakorlatok, "#gyakIdopontok");

}

function saveGyakorlatok() {
    localStorage.setItem(felh + "_gyakorlatok", JSON.stringify(gyakorlatok));
    localStorage.setItem(felh + "_naplonote", JSON.stringify(naplonote));
    //console.log("saveGyakorlatok lefutott");
}

//itt felépítem a mentett adatból a listámat
function gyakorlatEpitoFromMentes() {
    if (mgyk) {
        var suly = new Array();
        var ism = new Array();
        var ismido = new Array();
        for (var i = 0; i < mgyk.length; i++) {
            var egygyak = new Gyakorlat(mgyk[i].Name, suly, ism, mgyk[i].Megjegyzes);
            egygyak.RogzitesIdopont = new Date(mgyk[i].RogzitesIdopont);

            for (var u = 0; u < mgyk[i].Suly.length; u++) {
                egygyak.addSuly(mgyk[i].Suly[u]);
                egygyak.addIsm(mgyk[i].Ism[u]);
            }

            for (var k = 0; k < mgyk[i].IsmRogzitesIdopontja.length; k++) {
                egygyak.addIsmIdo(new Date(mgyk[i].IsmRogzitesIdopontja[k]));
            }

            gyakorlatok.push(egygyak);
        }
    }
}

function timingSaveGyakorlatok() {
    if (felh == null || felh == "") {
        felh = "ALT";
    }

    localStorage.setItem(felh + "_gyakorlatok", JSON.stringify(gyakorlatok));
    localStorage.setItem(felh + "_naplonote", JSON.stringify(naplonote));
    console.log("timingSaveGyakorlat lefutott");
    //ha minden igaz akkor percenként frissíti az adatot...
    //pár perc alatt több mint 4000 kérést reggelt a chrome console..
    //utána kell járnom az időzítéseknek
    //automentes = window.setInterval(timingSaveGyakorlatok, 1000 * 60);
}

function clearHibak() {
    $("#gyNoteHiba").addClass("hibaVisszajelzes");
    $("#gyNote").removeClass("hibaOsztaly");
    $("#gySulyHiba").addClass("hibaVisszajelzes");
    $("#gySuly").removeClass("hibaOsztaly");
    $("#gyIsmHiba").addClass("hibaVisszajelzes");
    $("#gyIsm").removeClass("hibaOsztaly");
}

function zoldSav(id) {
    var obj = $(id);
    obj.text("");
    obj.addClass("hibaMezoJo");
}

function resetMezo() {
    $("#gySuly").val("");
    $("#hibaGySuly").removeClass("hibaMezoJo");
    $("#gyIsm").val("");
    $("#hibaGyIsm").removeClass("hibaMezoJo");
    $("#gyNote").val("");
    $("#gyNote").removeClass("hibaMezoJo");
}

function printGyakorlat(gyaksik) {
    var string = new String();
    var ossz = 0;

    /*gyaksik.sort(function(a, b) {
        return new Date(b.RogzitesIdopont) - new Date(a.RogzitesIdopont);
    });*/

    string += "<div class='panel panel-default'>";
    string += "<div class='panel-body'>";
    string += "<div class='table-responsive'>";
    string += "<table class='table table-hover'>";
    string += "<thead>";
    string += "<tr>";
    string += "<th>Nr</th>";
    string += "<th>Gyakorlat neve</th>";
    string += "<th>Sorozatszám</th>";
    string += "<th>Súly X Ismétlés</th>";
    string += "<th>Megmozgatott Súly (Kg)</th>";
    //bővítem az oldalt, hogy kiadja a legkorábbi sulyt is
    string += "<th>Korábbi súly (Kg)</th>";
    string += "</tr>";
    string += "</thead><tbody>";
    //itt listázom az adataimat

    if (gyaksik.length == 0) {
        string += "<tr><td colspan='5'><p class='text-danger'>Üres a tárolóm</p></td></tr>";
    }

    for (var i = 0, k = 1; i < gyaksik.length; i++, k++) {
        var gy = gyaksik[i];
        var sXi = new String();

        string += "<tr>";
        string += "<td>" + k + "</td>";
        string += "<td>" + gy.Name + "</td>";
        string += "<td>" + gy.Suly.length + "</td>";

        for (var j = 0; j < gy.Suly.length; j++) {
            sulyelem.push("id_" + i + "_setsuly_" + j + "");
            ismelem.push("id_" + i + "_setism_" + j + "");
            sXi += "<span id='id_" + i + "_setsuly_" + j + "'>" + gy.Suly[j] +
                "</span>x<span id='id_" + i + "_setism_" + j + "'>" + gy.Ism[j] + "</span> ";
        }
        string += "<td>" + sXi + "</td>";
        string += "<td class='osszsuly" + i + "'>" + gy.osszSuly() + "</td>";
        //bővitem a legkésöbb berögzített összsulyt is jelezze
        var kid = "kosszsuly_" + i;
        korabbisuly.push(new KOssz("#" + kid, gy.Name, gy.osszSuly()));
        string += "<td id='" + kid + "'></td>";
        ossz += parseInt(gy.osszSuly());
        string += "</tr>";
    }

    string += "</tbody>";
    string += "</table>";
    string += "</div>"; //table-responsive div end
    string += "</div>";
    string += "<div id='osszgyaksuly' class='panel-footer' style='padding-bottom: 20px'>";
    string += "Összesen megmozgatott napi súly: <span class='badge'>" +
        (ossz == 0 ? " " : ossz) + "</span> Kg";
    //mentés gombok orientálása jobbra
    string += "<span class='pull-right'>" + getMentGombok() + "</span>";
    string += "</div>"
    string += "</div>";



    $("#gyakLista").html(string);
    if (gyaksik.length != 0) {
        setSuly(gyaksik);
        setIsm(gyaksik);
    }

    //korábbi összsuly kijelzésének elkészítése
    for (var i = 0; i < korabbisuly.length; i++) {
        var k = korabbisuly[i];
        getKorabbiOsszSuly(k.megnevezes, k.id, k.aktual);
    }
}

function getOsszGyakSuly(gyaktomb) {
    var ossz = 0;
    for (var i = 0; i < gyaktomb.length; i++) {
        ossz += gyaktomb[i].osszSuly();
    }

    return ossz;
}

/*Szerkeszhetővé teszem a gyakorlataimban az esetleg elgépelt suly ill ismétléseket 
    egy Eventet is felkell vennem, dupla kattintásra kicserélni az elemet, enter-e pedig set lesz
    az id tartalmazza a gyakorlat indexét és a suly ill ismétlés indexet
    tehát ezt szét kell vágnom majd : 2_suly_3 azaz második gyakorlat, harmadig suly érték megváltoztatása
*/
function setSuly(gyaktomb) {
    for (var i = 0; i < sulyelem.length; i++) {
        $("#" + sulyelem[i]).on("dblclick", function() {
            console.log("suly click on");
            var sulynum = $(this).text();
            $(this).html("<input type='text' name='ujsuly' id='ujsuly' value='" + sulynum + "'>");
            $("#ujsuly").focus();

            var s = this.id.split("_");
            var indexGyak = parseInt(s[1]);
            var indexSuly = parseInt(s[3]);

            $("#ujsuly").on("change", function() {
                console.log("change event on");
                var ertek = $(this).val();
                var ujErtek = 0;
                if (isNaN(ertek) || ertek == "" || parseInt(ertek) == 0) {
                    alert("Kérlek számot írj be és ne legyen 0!");
                } else {
                    ujErtek = parseInt(ertek);
                    gyaktomb[indexGyak].setSuly(indexSuly, ujErtek);
                    //itt alább valamiért nem állítja be!
                    $(this).parent().html(gyaktomb[indexGyak].Suly[indexSuly]);
                    gyakSulyFrissit(gyaktomb);
                    $("#osszgyaksuly").html("Összesen megmozgatott napi súly: <span class='badge'>" + getOsszGyakSuly(gyaktomb) + "</span> kg");
                }
            });
        });
    }
}

function setIsm(gyaktomb) {
    for (var i = 0; i < ismelem.length; i++) {
        $("#" + ismelem[i]).on("dblclick", function() {
            console.log("iam click on");
            var ismnum = $(this).text();
            $(this).html("<input type='text' name='ujism' id='ujism' value='" + ismnum + "'>");

            $("#ujism").focus();

            var s = this.id.split("_");
            var indexGyak = parseInt(s[1]);
            var indexIsm = parseInt(s[3]);

            $("#ujism").on("change", function() {
                console.log("change event on");
                var ertek = $(this).val();
                var ujErtek = 0;
                if (isNaN(ertek) || ertek == "" || parseInt(ertek) == 0) {
                    alert("Kérlek számot írj be és ne legyen 0!");
                } else {
                    ujErtek = parseInt(ertek);
                    gyaktomb[indexGyak].setIsm(indexIsm, ujErtek);
                    //itt alább valamiért nem állítja be!
                    $(this).parent().html(gyaktomb[indexGyak].Ism[indexIsm]);
                    gyakSulyFrissit(gyaktomb);
                    $("#osszgyaksuly").html("Összesen megmozgatott napi súly: <span class='badge'>" + getOsszGyakSuly(gyaktomb) + "</span> kg");
                }
            });
        });
    }
}

//suly és ismétlés szerkesztése után frissítem a megjelenő gyakorlatonként megmozgatott összes sulyokat
function gyakSulyFrissit(gyaksik) {
    for (var i = 0; i < gyaksik.length; i++) {
        $(".osszsuly" + i).html(gyaksik[i].osszSuly());
    }
}

function rogzitettIdoMegjelenito(gyaksik, megjelenitoId) {
    var str = new String();
    if (typeof(gyaksik) == "object" && gyaksik.length > 0) {
        str += "<div class='panel panel-default'>";
        str += "<div class='panel-body'>";
        str += "<table class='table table-hover'>";
        str += "<thead><tr>";
        str += "<th>Gyakorlat neve</th>";
        str += "<th>Időpont</th>";
        str += "<th>Utolsó sorozat rögzítése</th></tr></thead>";
        str += "<tbody>";
        for (var i = 0; i < gyaksik.length; i++) {
            str += "<tr>";
            str += "<td>" + gyaksik[i].Name + "</td>";
            str += "<td>" +
                (gyaksik[i].RogzitesIdopont.getHours() - 2) + ":" +
                gyaksik[i].RogzitesIdopont.getMinutes() + ":" +
                gyaksik[i].RogzitesIdopont.getSeconds() + "</td>";
            //var idopont = new Date(Date.parse(gyaksik[i].IsmRogzitesIdopontja[gyaksik[i].IsmRogzitesIdopontja.length - 1]));
            var idopont = gyaksik[i].IsmRogzitesIdopontja[gyaksik[i].IsmRogzitesIdopontja.length - 1];
            str += "<td>" + (idopont.getHours() - 2) + ":" + idopont.getMinutes() + ":" + idopont.getSeconds() + "</td>";
            str += "</tr>";
        }
        str += "</tbody>";
        str += "</table>";
        str += "</div></div>";
    } else {
        str += "<div class='panel panel-default'>";
        str += "<div class='panel-body'>";
        str += "<table class='table table-hover'>";
        str += "<thead><tr>";
        str += "<th>Gyakorlat neve</th>";
        str += "<th>Időpont</th>";
        str += "<th>Utolsó sorozat rögzítése</th></tr></thead>";
        str += "<tbody>";
        str += "<tr><td colspan='3'><p class='text-danger'>Üres a tárolóm!</p></td></tr>";
        str += "</tbody>";
        str += "</table>";
        str += "</div></div>";
    }

    $(megjelenitoId).html(str);
}

function datumMegjelenito() {
    var ido = new Date();
    $("#statusIdo").html("<h2>" + ido.toDateString() + " " + ido.getHours() + ":" + ido.getMinutes() + ":" +
        ido.getSeconds() + "</h2>");
    window.setTimeout(datumMegjelenito, 1000 / 60);
}

function addNote() {
    var anote = $("#nnote").val();
    var reg = new RegExp(/([A-Za-z0-9 ,\.\!\?]+)/, "ig");
    if (anote != "" && !reg.test(anote)) {
        $("#nnotesHiba").removeClass("hibaVisszajelzes");
        $("#nnote").addClass("hibaOsztaly");
        return;
    }
    $("#nnote").removeClass("hibaOsztaly");

    naplonote = anote;
    //a localt is frissítem
    localStorage.setItem(felh + "_naplonote", JSON.stringify(naplonote));
    //nem modalt használok ezentul
    //$("#naplonoteModal").modal("hide");
    modalErtesito("A megjegytés el lett mentve!");
}

function resetNote() {
    $("#nnote").val("");
    naplonote = "";

    localStorage.setItem(felh + "_naplonote", "");
    //$("#naplonoteModal").modal("hide"); nem modalt használok
}

function resetvan() {
    $("#nnote").removeClass("hibaOsztaly");
}

//törlöm a localStorage-ben tárolt személyes naplo adatot
function automenttorol(noconf) {
    if (!noconf) {
        if (confirm("Biztosan törölni szeretnéd az adatokat?")) {
            localStorage.removeItem($.cookie("felhasznalo") + "_gyakorlatok");
            localStorage.removeItem($.cookie("felhasznalo") + "_naplonote");
            console.log("Törlés végrehajtódott");
            resetNote();
            clearInterval(automentes);
            gyakorlatok = new Array();
            printGyakorlat(gyakorlatok);
            rogzitettIdoMegjelenito(gyakorlatok, "#gyakIdopontok");
            s_stop();
        } else {
            alert("Nem lett törölve");
        }
    } else {
        localStorage.removeItem($.cookie("felhasznalo") + "_gyakorlatok");
        localStorage.removeItem($.cookie("felhasznalo") + "_naplonote");
        //console.log("Törlés végrehajtódott");
        //clearInterval(automentes);
        //
        //printGyakorlat(gyakorlatok);
        //rogzitettIdoMegjelenito(gyakorlatok, "#gyakIdopontok");
        s_stop();
        console.log("storage tartalom törölve");
    }


}

//korábbi naplok megjelenítése, majd egyszer visszaolvasás szerkesztésre
function korabbiNaplok(id) {
    var http = new HttpClient();
    http.isAsync = true;
    http.callback = function(res) {
        myobj = JSON.parse(res);
        gyakNaploDatumMegj2(id);
    }
    http.makeRequest("naplogyakleker.php", null);
}

function gyakNaploDatumMegj2(id) {
    var str = new String();
    if (myobj && myobj.mentesidatum) {
        str += "<div class='list-group' id='napikeresendo'>";
        for (var i = 0; i < myobj.mentesidatum.length; i++) {
            str += "<a class='list-group-item' href='javascript:knpbetolt(\"" + myobj.mentesidatum[i] + "\")'>" + myobj.mentesidatum[i] + "</a>";
        }
        str += "</div>";


    }

    if (myobj.hiba) {
        str += "<div class='list-group' id='napikeresendo'>";
        str += "<a class='list-group-item' href='index.html'>" + myobj.hiba + "</a>";
        str += "</div>";
    }

    $(id).html(str);

    $("#napikeresendo a").click(function() {
        $(this).addClass("active");
        $(this).siblings("a").removeClass("active");
    });
}

function knpbetolt(mentesiidopont) {
    var http = new HttpClient();
    http.isAsync = true;
    http.requestType = "POST";
    http.callback = function(res) {
        //console.log(JSON.parse(res));
        mentettNaplok = JSON.parse(res);
        gyakorlatEpitoFromKMentes();
        printGyakorlat(gyakorlatok);
        rogzitettIdoMegjelenito(gyakorlatok, "#gyakIdopontok");
    }

    http.makeRequest("naplogyakleker.php", "mentesidatum=" +
        encodeURIComponent(mentesiidopont));
}

//itt felépítem a korábbi mentett naplobol a listámat
function gyakorlatEpitoFromKMentes() {
    if (mentettNaplok) {
        gyakorlatok = new Array();
        var mn = mentettNaplok.naplo;
        var suly = new Array();
        var ism = new Array();
        //var ismido = new Array();
        for (var i = 0; i < mn.length; i++) {
            var egygyak = new Gyakorlat(mn[i].megnevezes, suly, ism, mn[i].megjegyzes);
            egygyak.RogzitesIdopont = new Date(mn[i].gyakrogzido);

            for (var u = 0; u < mn[i].sorozat.suly.length; u++) {
                egygyak.addSuly(mn[i].sorozat.suly[u]);
                egygyak.addIsm(mn[i].sorozat.ism[u]);
            }

            for (var k = 0; k < mn[i].sorozat.idop.length; k++) {
                egygyak.addIsmIdo(mn[i].sorozat.idop[k]);
            }

            gyakorlatok.push(egygyak);
        }

        $("#korabbijelzo").html("<div class='alert alert-success alert-dismissible'>" +
            "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Korábbi mentett napló betöltve!</div>");
        $("#korabbijelzo2").html("<div class='alert alert-danger alert-dismissible'>" +
            "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>Sajnos az időpontok így nem lesznek valósak, ha ujra elmentjük!</div>");
        automenttorol(1);
    }

    if (mentettNaplok.naplonote && !mentettNaplok.naplonotehiba) {
        naplonote = mentettNaplok.naplonote;
        loadNaploNote();
    }
}

function loadNaploNote() {
    $("#nnote").val(naplonote);
}

//legkésöbb rögzített össz megmozgatott suly lekérése
function getKorabbiOsszSuly(megnevezes, id, aktualsuly) {
    var xhttp = new HttpClient();
    var payload = "Megnevezes=" + megnevezes + "&gttp=k";
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(res) {
        var eredmeny = JSON.parse(res);
        var sorozat = "";
        var szin = "";
        if (!eredmeny.hiba) {
            if (parseInt(aktualsuly) < parseInt(eredmeny.result)) {
                szin = "color: red";
            } else {
                szin = "color: green";
            }

            if (!eredmeny.sorozat.hiba) {
                if (!eredmeny.sorozat.ures) {
                    for (var i = 0; i < eredmeny.sorozat.suly.length; i++) {
                        sorozat += eredmeny.sorozat.suly[i] + "x" + eredmeny.sorozat.ism[i] + "\n";
                    }
                } else {
                    sorozat += "Hiba:(";
                }
            } else {
                sorozat += "Hiba:(";
            }

            $(id).html("<span style='" + szin + "' data-toggle='tooltip' data-placement='right' title='" + sorozat + "' class='test'>" + eredmeny.result + "</span>");

            $('[data-toggle="tooltip"]').tooltip();
        } else {
            $(id).html("<span style='color: red'>0</span>");
            //console.log("Gyakorlat összsuly hiba: " + eredmeny.hiba);
        }
    }

    xhttp.makeRequest("osszsuly.php", payload);
}