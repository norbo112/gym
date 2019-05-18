function CheckData() {
    this.infoModal();
}

CheckData.prototype = {
    rgp: undefined, //majd a regexp objektum az adott mintával
    rgpFlag: "gm",
    infModalHelyID: '#spinfo',
    infModalID: 'infomodal',
    infoModalCim: "Ellenőrzés",
    infoModalInfo: "",
    doRgp: function(regminta, ellenorizendo, strlength) {
        var szoveg = $(ellenorizendo).val();
        this.rgp = new RegExp(regminta, this.rgpFlag);
        //console.log("Regexp minta: " + this.rgp.toString());

        if (szoveg == "") {
            this.infmutat("Nem töltötted ki a mezőt! Üres nem lehet!");
            return false;
        }

        if (szoveg.length > strlength) {
            this.infmutat("Túl hosszú szöveget adtál meg");
            return false;
        }

        if (this.rgp.test(szoveg)) {
            this.infmutat("A bevitel Helyes Volt!", true);
            return true;
        } else {
            this.infmutat("A megadott karakterek nem elfogadhatóak!");
            return false;
        }
    },
    infmutat: function(szoveg, bool) {
        var b = bool ? "bg-success" : "bg-danger";
        $("#spmodinfo").html("<h3 class='" + b + "'>" + szoveg + "</h3>");
        $("#" + this.infModalID).modal();
    },
    infoModal: function() {
        var str = new String();
        var element = $("<span id='" + this.infModalHelyID + "'></span>");
        str += '<div class="modal fade" id="' + this.infModalID + '">';
        str += '<div class="modal-dialog">';
        str += '<div class="modal-content">';
        str += '<div class="modal-header">';
        str += '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
        str += '<h4 class="modal-title" id="spmodcim">' + this.infoModalCim + '</h4>';
        str += '</div>';
        str += '<div class="modal-body" id="spmodinfo">' + this.infoModalInfo + '</div>';
        str += '<div class="modal-footer">';
        str += '<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>';
        str += '</div>';
        str += '</div>';
        str += '</div>';
        str += '</div>';
        $(element).html(str);
        $("body").append(element);
        console.log("infomodal hozzáadva a span elemhez");
    },
    doRgpTomeg: function(micsoda, adatok) {
        if (adatok.length == 0) {
            this.infmutat("Nincsenek adataim az ellenőrzéshez");
        }

        for (var i = 0; i < adatok.length; i++) {
            if (micsoda[i] == "szam") {
                if (this.doRgp(/[\d]+/, adatok[i], 30)) {
                    $(adatok[i]).css('background-color', '#80ff80');
                    $(adatok[i] + "_ci").css('display', 'none');
                } else {
                    $(adatok[i]).css('background-color', 'silver');
                    $(adatok[i] + "_ci").css('display', 'block');
                }
            } else if (micsoda[i] == "szoveg") {
                if (this.doRgp(/[A-Za-z0-9]+/, adatok[i], 30)) {
                    $(adatok[i]).css('background-color', '#80ff80');
                    $(adatok[i] + "_ci").css('display', 'none');
                } else {
                    $(adatok[i]).css('background-color', 'silver');
                    $(adatok[i] + "_ci").css('display', 'block');
                }
            }
        }
    }
}