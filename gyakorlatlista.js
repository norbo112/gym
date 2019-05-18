var tag = document.createElement('script');

tag.src = "https://www.youtube.com/iframe_api";
var firstScriptTag = document.getElementsByTagName('script')[0];
firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

// 3. This function creates an <iframe> (and YouTube player)
//    after the API code downloads.
var player;

function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        height: '430',
        width: '680',
        videoId: 'MOzI-I7C8W8',
        playerVars: {
            controls: 0,
            fs: 0
        },
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

// 4. The API will call this function when the video player is ready.
function onPlayerReady(event) {
    //event.target.playVideo(); //ideiglenesen kikapcsolva
    $("#player").addClass("embed-responsive-item");
}

// 5. The API calls this function when the player's state changes.
//    The function indicates that when playing a video (state=1),
//    the player should play for six seconds and then stop.
var done = false;

function onPlayerStateChange(event) {
    var loc = window.location.href.split("/");
    var oldalfilecim = loc[loc.length - 1];

    if (oldalfilecim == "gyaknezo.html") {
        return;
    }

    switch (event.data) {
        case YT.PlayerState.UNSTARTED:
            //console.log('unstarted');
            break;
        case YT.PlayerState.ENDED:
            //console.log('ended');
            $("#playerShow").removeClass("videoLoad");
            break;
        case YT.PlayerState.PLAYING:
            //console.log('playing');
            $("#playerShow").addClass("videoLoad");
            break;
        case YT.PlayerState.PAUSED:
            //console.log('paused');
            $("#playerShow").removeClass("videoLoad");
            break;
        case YT.PlayerState.BUFFERING:
            //console.log('buffering');
            $("#playerShow").removeClass("videoLoad");
            break;
        case YT.PlayerState.CUED:
            //console.log('video cued');
            break;
    }
}

function stopVideo() {
    player.stopVideo();
}

$(document).ready(function() {
    var loc = window.location.href.split("/");
    var oldalfilecim = loc[loc.length - 1];

    if (oldalfilecim == "gyaknezo.html") {
        initGyakLista();
    }
});

function initGyakLista() {
    var http = new XMLHttpRequest();
    http.onreadystatechange = function() {
        if (this.status == 200 && this.readyState == 4) {
            $("#gyakvalaszto").html(this.responseText);

            $(".list-group a").each(function() {
                $(this).click(function(e) {
                    e.preventDefault();
                    return false;
                });
            });
        }
    }
    http.open("GET", 'gyaknezo.php', true);
    http.send(null);
}

mutgyak = function(src) {
    var value = $(src).val().toLowerCase();
    //$(".dropdown-menu li").filter(function() {
    if (value != "") {
        $(".list-group .listahead").hide();
    } else if (value == "") {
        $(".list-group .listahead").show();
    }

    $(".list-group li").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
}

showvideo = function(videoid, megnevezes, gyakid, startpoz) {
    showGyakInfo(gyakid);
    $("#video_header").text(megnevezes);
    if (parseInt(startpoz) != 0) {
        player.loadVideoById(videoid, startpoz, "large");
    } else {
        player.loadVideoById(videoid, 0, "large");
    }

}

function showGyakInfo(gyakid) {
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    var adat = "kapcsolo=" + 1 + "&";
    adat += "gyakid=" + gyakid;
    xhttp.callback = function(res) {
        var myinfo = JSON.parse(res);
        var tartalom = new String();
        if (myinfo && !myinfo.hiba) {
            if ($.cookie("felhasznalo")) {
                tartalom += "<p style='font-size: 1.3em;'>Hányszor használtad a gyakorlatot: ";
            } else {
                tartalom += "<p style='font-size: 1.3em;'>Hányszor használták a gyakorlatot: ";
            }

            tartalom += "<span class='label label-default'>";
            tartalom += myinfo.hasznalatnum + "</span></p>";
            tartalom += "<p style='font-size: 1.3em;'>Hozzátartozó rövid leírás: <br>";
            tartalom += "<mark>" + myinfo.leiras + "</mark>";
            tartalom += "</p>";
            $("#gykinfo").html(tartalom);
        } else {
            $("#gykinfo").html("Sajnos hibák merültek fel:::" + myinfo.hiba);
        }

    }

    xhttp.makeRequest("gyaknezo.php", adat);
}

function gyakszerk(id) {
    var adat = "gyakid=" + id + "&";
    adat += "kapcsolo=" + 2;
    var client = new HttpClient();
    client.isAsync = true;
    client.requestType = "POST";
    client.callback = function(res) {
        var o = JSON.parse(res);
        if (!o.hiba) {
            $("#modalspanforedit").html(o.gyakmodal);
            initGyakSzerkModal();
        } else {
            console.log("Hiba a betöltés közben: " + o.hiba);
        }
    }
    client.makeRequest("gyaknezo.php", adat);
}

function initGyakSzerkModal() {
    $("#myGyakModal").modal();

    $("#ujgyakszerk").submit(function(e) {
        e.preventDefault();
        visszajelzestEltavolit();
        var hibak = urlapotEllenoriz();
        if (hibak == "") {
            gyakSzerkKuld();
            $("#myGyakModal").modal("hide");
            $("#modalspanforedit").html("");
        } else {
            visszajelzestAd(hibak);
        }
        return false;
    });

    $("#ujgyakszerk input[type=reset]").click(function() {
        resetvan();
    });
}

function gyakSzerkKuld() {
    var sendobj = {
        "gyakid": $("#gyakid").val(),
        "csoport": $("#ujgyak_tipus").val(),
        "nev": $("#ujgyak_nev").val(),
        "leiras": $("#ujgyak_leiras").val(),
        "videoid": $("#ujgyak_video").val(),
        "videopoz": $("#poz_video").val()
    };

    var adat = "kapcsolo=" + encodeURI("3") + "&";
    adat += "gyaksi=" + JSON.stringify(sendobj);
    var xhttp = new HttpClient();
    xhttp.isAsync = true;
    xhttp.requestType = "POST";
    xhttp.callback = function(res) {
        var ob = JSON.parse(res);
        if (ob.success) {
            makeMyModalInfo(ob.success, "#modalspanforinfo", 0);
            initGyakLista();
        } else if (ob.hiba) {
            makeMyModalInfo(ob.hiba, "#modalspanforinfo", 1);
        }

        $("#hibaModal").modal();
        $("#hibaModal").on('hide.bs.modal', function() {
            $("#modalspanforinfo").html("");
            $("#modalspanforinfo").hide();
            $(".modal-backdrop").fadeOut(250);
        });
    }
    xhttp.makeRequest("gyaknezo.php", adat);
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
    if ($("#ujgyak_nev").val() != "" && $("#ujgyak_nev").val().length > 50) {
        if (!reg.test($("#ujgyak_nev").val())) {
            hibasMezok.push("ujgyak_nev");
        }
    }

    reg = new RegExp(/[A-Za-z0-9]+/, "ig");
    if ($("#ujgyak_tipus").val() != "" && $("#ujgyak_tipus").val().length > 20) {
        if (!reg.test($("#ujgyak_tipus").val())) {
            hibasMezok.push("ujgyak_tipus");
        }
    }

    reg = new RegExp(/[A-Za-z0-9 ,.!]+/, "ig");
    if ($("#ujgyak_leiras").val() != "" && $("#ujgyak_leiras").val().length > 400) {
        if (!reg.test($("#ujgyak_leiras").val())) {
            hibasMezok.push("ujgyak_leiras");
        }
    }

    if ($("#ujgyak_video").val() != "" && $("#ujgyak_video").val().length > 255) {
        //itt kellene leelenöriznem a video linket vagy az id-t
        console.log("Video Link ellenőrzése");
    }

    reg = new RegExp(/[0-9]{1,3}/, "ig");
    if ($("#poz_video").val() != "") {
        if (!reg.test($("#poz_video").val())) {
            hibasMezok.push("poz_video");
        }
    }

    return hibasMezok;
}

function resetvan() {
    visszajelzestEltavolit();
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
    $("#ujgyakszerk input").each(function() {
        $(this).removeClass("hibaOsztaly");
    });
    $("#ujgyakszerk .hibaSzakasz").each(function() {
        $(this).addClass("hibaVisszajelzes");
    });
}

function makeMyModalInfo(adat, id, bool) {
    var str = new String();
    $(id).html("");

    str += '<div class="modal fade" id="hibaModal">';
    str += '<div class="modal-dialog">';
    if (bool == 1) {
        str += '<div class="modal-content" style="background-color:rgb(255, 255, 204);>';
    } else {
        str += '<div class="modal-content" style="background-color:rgb(204, 255, 255);">';
    }
    str += '<div class="modal-header">';
    str += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
    if (bool == 1) {
        str += '<h4 class="modal-title">Sikertelen</h4>';
    } else {
        str += '<h4 class="modal-title">Sikeres</h4>';
    }
    str += '</div>';
    str += '<div class="modal-body">';
    str += '<p>' + adat + '</p>';
    str += '</div>';
    str += '<div class="modal-footer">';
    str += '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
    str += '</div>';
    str += '</div>';
    str += '</div>';
    str += '</div>';
    $(id).html(str);
}