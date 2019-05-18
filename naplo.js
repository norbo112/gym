var captchaObj = undefined;

$(document).ready(function() {
    //proba, ellenőrzöm az oldalamat, hogy ha lehet ne töltsön be olyan kodokat amikre épp nincs szükség
    var loc = window.location.href.split("/");
    var oldalfilecim = loc[loc.length - 1];


    origW = $('#stopper_kijelzo').outerWidth(true);
    origW2 = $('#stpoff').outerWidth(true);
    tgHo = $('#stpIk').outerHeight(true);
    tgWo = $('#stpIk').outerWidth(true);

    //első kőrben ellenörzöm a logint, és ennek eredményeként készítem el a formot
    checkLogin0();

    if (oldalfilecim == "index.html" || oldalfilecim == "") {
        hirekMegjelenito("#hirek");
        hirkuldoFormMegjelenit("#hirekAdd");
        checkegyfelh();
    } else if (oldalfilecim == "felhasznalo.html") {
        checkegyfelh();
    }

    $("#footercontent").load("footer.html");

    function belepestElkeszitoEventek() {
        $("#belepteto").submit(function(e) {
            e.preventDefault();
            if (captchaObj == undefined || !captchaObj.success) {
                $("#hibaModal").modal();
                $("#fhonnan").val("loginurlap");
                $("#hibaModal").on("hide.bs.modal", function() {
                    $("#bevitelCap").focus();
                });
                return false;
            }
            $("#loadingModal").modal();
            //alert("Submit megtörtént");
            var client = new HttpClient();
            var str = new String();
            client.isAsync = true;
            client.requestType = "POST";
            client.callback = function(res) {
                console.log("Bejelentkezés: " + res);
                $("#loadingModal").modal("hide");
                checkLogin0();
                uzenetOlv(1);
                hirekMegjelenito("#hirek");
                hirkuldoFormMegjelenit("#hirekAdd");
                $("#resultCap").text("");
                captchaObj = undefined;
                if (oldalfilecim == "gyaklista.html") {
                    rogz_gyak_megjelenito();
                }

                if (oldalfilecim == "napitev.html") {
                    checkLogin("#mentes_egyebb");
                    //checkegyfelh();
                    ujgyakaktiv();
                }

                if (oldalfilecim == "felhasznalo.html") {
                    felhOlv(0);
                    felhOlv(1);
                    checkegyfelh();
                }

                if (oldalfilecim == "" || oldalfilecim == "index.html") {
                    checkegyfelh();
                }
            }

            str += "email=" + $("#email2").val() + "&";
            str += "pwd=" + $("#pwd2").val();

            client.makeRequest("login.php", str);
            return false;
        });

        //captcha
        $("#capthaForm").submit(function(e) {
            e.preventDefault();
            checkKepEredmeny();
            return false;
        });

        $("#capimg").click(function(e) {
            e.preventDefault();
            $("#capimg").removeAttr("src").attr("src", "");
            $("#capimg").attr("src", "tmp_res/nwctest.php" + "?dummy=" + Math.floor(Math.random() * 1000));
            $("#subgombCap").removeAttr("disabled");
            return false;
        });
    }


    $("#kereso").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#keresendo tr").filter(function() {
            var mit = $(this).text().toLowerCase().indexOf(value) > -1;
            $(this).toggle(mit);
        });
    });

    $("#napikereso").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#napikeresendo a").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    $("#napikereso").on("change", function() {
        var value = $(this).val().toLowerCase();
        $("#napikeresendo a").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    //email küldése a kapcsolat oldalról
    wbkuldo = function() {
        console.log("wbform submit here");
        //itt helyben az ellenörzést is elvégzem
        if ($("#wmmail").val() == "") {
            $("#wmmail").val("Kérlek add meg az email címed");
            $("#wmmail").animate({ backgroundColor: 'green' }, 500, function() {
                $(this).css("background-color", "white");
            });

            return false;
        }

        var reg = new RegExp(/[\-\_A-Za-z0-9]+@[\-\_A-Za-z0-9]+\.(hu|com|org|net)/, "ig");
        if ($("#wmmail").val() != "" && $("#wmmail").val().length < 200) {
            if (!reg.test($("#wmmail").val())) {
                $("#wmmail").val("Kérlek helyes email címet adj meg");
                $("#wmmail").animate({ backgroundColor: 'green' }, 500, function() {
                    $(this).css("background-color", "white");
                });

                return false;
            }
        }

        if ($("#wmuzi").val() == "") {
            $("#wmuzi").val("Kérlek add meg az üzeneted");
            $("#wmuzi").animate({ backgroundColor: 'green' }, 500, function() {
                $(this).css("background-color", "white");
            });

            return false;
        }

        if ($("#wmuzi").val() != "" && $("#wmuzi").val().length < 200) {
            var regt = new RegExp(/([A-Za-z0-9 \_\-\^]+)/, "ig");
            if (!regt.test($("#wmuzi").val())) {
                $("#wmuzi").val("Kérlek csak számokat és betüket írj");
                $("#wmuzi").animate({ backgroundColor: 'green' }, 500, function() {
                    $(this).css("background-color", "white");
                });

                return false;
            }
        }

        //ha itt vagyok akkor minden jó
        var http = new HttpClient();
        http.isAsync = true;
        http.requestType = "POST";
        http.callback = function(res) {
            var ob = JSON.parse(res);
            if (ob.hiba) {
                $("#resultfootermsg").html("<div class='alert alert-warning alert-dismissible'>" +
                    "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + ob.hiba + "</div>");
            } else if (ob.siker) {
                $("#resultfootermsg").html("<div class='alert alert-success alert-dismissible'>" +
                    "<a href='#' class='close' data-dismiss='alert' aria-label='close'>&times;</a>" + ob.siker + "</div>");
            }

            $("#wmuzi").val("");
            $("#wmmail").val("");

        }

        var adatok = "email=" + encodeURIComponent($("#wmmail").val()) + "&";
        adatok += "wbuzi=" + encodeURIComponent($("#wmuzi").val());
        http.makeRequest("mailme.php", adatok);

        return true;
    }


    function checkKepEredmeny() {
        $.ajax({
            type: "POST",
            url: "tmp_res/valid_captcha.php",
            data: {
                captcha: $("#bevitelCap").val()
            },
            dataType: "text",
            success: function(response) {
                captchaObj = JSON.parse(response);
                $("#resultCap").toggleClass("captcha");
                if (captchaObj.success) {
                    $("#resultCap").text("Sikeres, egyezés!");
                    $("#subgombCap").attr("disabled", "disabled");
                    if ($("#fhonnan").val() == "regurlap") {
                        $("#regUrlap").trigger("submit");
                    } else if ($("#fhonnan").val() == "loginurlap") {
                        $("#belepteto").trigger("submit");
                    }

                } else if (captchaObj.failed) {
                    $("#resultCap").text("Nem egyezik! " + captchaObj.failed);
                    $("#capimg").trigger("click");
                }


            }
        });
    }
    //captchaend

    function checkegyfelh() {
        var ffh = $.cookie('felhasznalo');
        if (ffh && ffh != "") {
            $("#hanincslog").html("");
            $("#hanincslog").hide();
        } else {
            $("#hanincslog").show();
            $("#hanincslog").load("loginregform.html", function(resText, statusText, xhr) {
                if (statusText == "success") {
                    checkLogin0();
                    belepestElkeszitoEventek();
                    regurlapEventAdder();
                }
                if (statusText == "error") {
                    console.log("Error: " + xhr.status + ": " + xhr.statusText);
                }
            });


            idotjelez();
        }
    }

    function checkLogin0() {
        if (logintEllenoriz()) {
            console.log("bejelentkezve");
            logout("#login2");
            checkAdmin();


        } else {
            console.log("be kell jelentkezni");
            login("#login2");
        }
    }

    function checkAdmin() {
        var ertek = false;
        var http = new HttpClient();
        http.isAsync = true;
        http.requestType = "POST";
        var payload = "csaka=1";
        http.callback = function(res) {
            if (res == 1) {
                if (oldalfilecim != "avatar.html") {
                    $("ul").eq(2).append("<li><a href='avatar.html'>Avatar</a></li>");
                }
            }

        }
        http.makeRequest("admincheck.php", payload);
    }

    function login(id) {
        var str = new String();
        str += "<form id='belepteto' action='#' method='POST'>";
        str += "<div class='input-group'>";
        str += "<span class='input-group-addon'><i class='glyphicon glyphicon-user'></i></span>";
        str += "<input type='email' class='form-control' id='email2' name='email2' placeholder='Email cím'>";
        str += "</div>";
        str += "<div class='input-group'>";
        str += "<span class='input-group-addon'><i class='glyphicon glyphicon-lock'></i></span>";
        str += "<input type='password' class='form-control' id='pwd2' name='pwd2' placeholder='Jelszó'>"
        str += "</div>";
        str += "<button type='submit' class='btn btn-default'>Belépés</button>";
        str += "</form>";

        $(id).html(str);
    }

    function logout(id) {
        var str = new String();
        var str2 = new String();
        str2 += "Üdvözlet az edzésnaplóban kedves <mark>" + $.cookie("nevem") + "</mark> !";
        str += "<h3>Üdvözletem, <mark>" + $.cookie("nevem") + "</mark> !</h3>";
        str += "<button class='btn btn-default' onclick='kijelentkezo()'>Kilépés</button>";
        $(id).html(str);
        $(id + "Udv").html(str2);
        $(id + "head").text("Bejelentkezve, mint " + $.cookie("nevem"));
    }

    //elkészítem a hirküldö formot, ha admin a felhasználó
    function hirkuldoFormMegjelenit(id) {
        var http = new HttpClient();
        http.isAsync = true;
        http.callback = function(result) {
            $(id).html(result);
        }
        http.makeRequest("hirkuldo.php", null);
    }

    function idotjelez() {
        var d = new Date();
        var str = new String();
        var y = d.getFullYear();
        var m = d.getMonth() + 1;
        var day = d.getDate();
        var h = d.getHours();
        var mi = d.getMinutes();
        var s = d.getSeconds();

        str = y + "-" + setSzam(m) + "-" + setSzam(day) + " " + setSzam(h) + ":" + setSzam(mi) + ":" + setSzam(s);
        $("#idokijelzo").html(str);

        window.setTimeout(idotjelez, 1000);
    }

    function setSzam(szam) {
        if (szam < 10) {
            return 00 + "" + szam;
        } else {
            return "" + szam;
        }
    }
});

function hirekMegjelenito(id) {
    var http = new HttpClient();
    http.isAsync = true;
    http.callback = function(result) {
        $(id).html(result);
    }
    http.makeRequest("hirlista.php", null);
}

var s_perc = 0;
var s_mp = 0;
var s_smp = 0;
var s_timer = false;

function s_run() {
    stopper.start();
    $("#stopper_kijelzo").addClass("stopperanimclass");
}

function s_stop() {
    stopper.stop();
    $("#stopper_kijelzo").removeClass("stopperanimclass");
}

/**
 * Animáció: start on stopper óra
 */
var volteanim = false;
var origW;
var origW2;
var tgHo, tgWo;

function stpToggle() {
    $('#stpikon').toggleClass("glyphicon glyphicon-chevron-left");
    $('#stpikon').toggleClass("glyphicon glyphicon-chevron-right");
    if (volteanim) {
        $('#stpIk').animate({ opacity: '1' });
        $('#stopper_kijelzo').toggle(1000).animate({ width: origW });
        $('#stpoff').toggle(1000).animate({ width: origW2 + 3 });

        volteanim = false;
    } else {
        $('#stopper_kijelzo').animate({ width: '0px' }, function() {
            $(this).toggle(1000);
        });
        $('#stpoff').animate({ width: '0px' }, function() {
            $(this).toggle(1000);
            $('#stpIk').animate({ opacity: '0.4' });
        });

        volteanim = true;
    }
}
/**
 * Anim end on stopper óra
 */

function tgglDiv(id) {
    $(id).slideToggle(200);
}

//egy külsú forrásból származó stopper.class
stopper = {
    start: function() {
        this.startDate = new Date();
        this.getCurrentDate();
        this.step = 1000 / 60;
        var stopper = this;
        this.timer = setInterval(function() {
            stopper.getCurrentDate();
        }, this.step);
    },
    stop: function() {
        clearInterval(this.timer);
        document.getElementById("stopper_kijelzo").innerHTML = "00:00";
    },
    getCurrentDate: function() {
        this.currentDate = new Date();
        this.time = this.currentDate - this.startDate;
        this.printTime(this.time);
    },
    printTime: function(time) {
        var msecs = time % 1000;
        time = Math.floor(time / 1000);
        var secs = stopper.setSzam(time % 60);
        time = Math.floor(time / 60);
        var mins = stopper.setSzam(time % 60);
        //if (mins < 60) { stopper.beep(); }
        time = Math.floor(time / 60);
        var hours = time;
        document.getElementById("stopper_kijelzo").innerHTML = mins + ":" + secs;
    },
    setSzam: function(szam) {
        if (szam < 10) {
            return 00 + "" + szam;
        } else {
            return "" + szam;
        }
    },
    beep: function() {
        //var snd = new Audio("beep.wav");
        //snd.play();
    }
}