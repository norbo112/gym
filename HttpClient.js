var felhasznalo = undefined;
var nevem = undefined;

function getNevem() { //sütiből nyerem ki a nevet, ha létezik
    //var c = document.cookie;
    var kukitomb = document.cookie.split(";");
    for (var i = 0; i < kukitomb.length; i++) {
        //console.log("kukitomb " + i + " : " + kukitomb[i]);
        var a_ertek = kukitomb[i].split("=");
        for (var k = 0; k < a_ertek.length; k++) {
            //console.log("a_ertek " + k + " : " + a_ertek[k]);
            if (a_ertek[k].substr(1, 5) == "nevem") {
                if (a_ertek[k + 1] == "") {
                    return false;
                }
                return unescape(a_ertek[k + 1]);
                //return a_ertek[k + 1];
            }
        }

    }

    return false;
}

//létezik e felhasználó
function logintEllenoriz() {
    //var c = document.cookie;
    var kukitomb = document.cookie.split(";");
    for (var i = 0; i < kukitomb.length; i++) {
        //console.log("kukitomb " + i + " : " + kukitomb[i]);
        var a_ertek = kukitomb[i].split("=");
        for (var k = 0; k < a_ertek.length; k++) {
            //console.log("a_ertek " + k + " : " + a_ertek[k]);
            if (a_ertek[k] == "felhasznalo" || a_ertek[k].substr(1, 11) == "felhasznalo") {
                felhasznalo = a_ertek[k + 1];
                if (felhasznalo == "") {
                    return false;
                }
                return true;
            }
        }
    }
    return false;
}

kijelentkezo = function() {
    //alert("kijelentkezés fejlesztés alatt\n A böngésző bezárásával kijelentkezel!");
    $.cookie("felhasznalo", "");
    $.cookie("nevem", "");
    window.location = 'logout.php';
}

function HttpClient() {

}

HttpClient.prototype = {
    requestType: 'GET',
    isAsync: false,
    xmlhttp: false,
    datumom: false,
    callback: false,
    onSend: function() {

    },
    onLoad: function() {

    },
    onprogress: function(evt) {
        //xmlhttp event figyelő, itt megkell alkosak majd egy szép animációt
        document.getElementById("statusLoad2").style.display = "block";
        document.getElementById("statusLoad1").style.display = "block";
        var prg = document.getElementById("myProgress1");
        var prg2 = document.getElementById("myProgress2");
        if (evt.lengthComputable) {
            var percentComplete = evt.loaded / evt.total;
            //console.log(percentComplete);
            prg.max = evt.total;
            prg.value = evt.loaded;
            prg2.max = evt.total;
            prg2.value = evt.loaded;
        }
    },
    onloadend: function(evt) {
        var prg = document.getElementById("myProgress1");
        var prg2 = document.getElementById("myProgress2");
        prg.value = evt.loaded;
        prg2.value = evt.loaded;
        document.getElementById("statusLoad2").style.display = "none";
        document.getElementById("statusLoad1").style.display = "none";
    },
    onError: function(error) {
        console.log(error);
    },
    init: function() {
        //Ie vagy a többi...
        try {
            this.xmlhttp = new XMLHttpRequest();
        } catch (e) {
            //ie
            var XMLHTTP_IDS = new Array('MSXML2.XMLHTTP.5.0',
                'MSXML2.XMLHTTP.4.0',
                'MSXML2.XMLHTTP.3.0',
                'MSXML2.XMLHTTP',
                'Microsoft.XMLHTTP');
            var success = false;
            for (var i = 0; i < XMLHTTP_IDS.length && !success; i++) {
                try {
                    this.xmlhttp = new ActiveXObject(XMLHTTP_IDS[i]);
                    success = true;
                } catch (e) {}
            }

            if (!success) {
                this.onError("Unable to create XMLHttpRequest.");
            }
        }
    },
    makeRequest: function(url, payload) {
        if (!this.xmlhttp) {
            this.init();
        }

        this.xmlhttp.addEventListener("progress", this.onprogress);
        this.xmlhttp.addEventListener("load", this.onloadend);

        this.xmlhttp.open(this.requestType, url, this.isAsync);
        var self = this;
        this.xmlhttp.onreadystatechange = function() {
            self._readyStateChangeCallback();
        }

        if (payload != null) {
            this.xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }

        this.xmlhttp.send(payload);

        if (!this.isAsync) {
            return this.xmlhttp.responseText;
        }
    },
    _readyStateChangeCallback: function() {
        switch (this.xmlhttp.readyState) {
            case 2:
                this.onSend();
                break;
            case 4:
                this.onLoad();
                if (this.xmlhttp.status == 200) {
                    this.callback(this.xmlhttp.responseText, this.datumom);
                } else {
                    this.onError("HTTP Error Making Request: " +
                        "[ " + this.xmlhttp.status + " ]" +
                        "[ " + this.xmlhttp.statusText + " ]");
                }
                break;
        }
        /*
        if (this.xmlhttp.status == 200 && this.xmlhttp.readyState == 4) {
            this.callback(this.xmlhttp.responseText, this.datumom);
        } else {
            this.onError("HTTP Error Making Request: " +
                "[ " + this.xmlhttp.status + " ]" +
                "[ " + this.xmlhttp.statusText + " ]");
        }
    */
    }
}