function Info() {}
Info.prototype = {
    modal_cim_id: "#infomodalcim",
    modal_info_id: "#infomodalinfo",
    modal_id: "#infomodal",
    showinfo: function(cim, info, szin) {
        $(this.modal_cim_id).html(cim);
        $(this.modal_info_id).html(info);
        if (szin != null) {
            $(this.modal_id + " div.modal-content").css("background-color", szin);
        }

        $(this.modal_id).modal();
    }
}