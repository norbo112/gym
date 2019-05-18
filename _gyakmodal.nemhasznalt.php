<!--Modal gyakorlat hozzáadásához-->
    <div id="myModal" class="modal fade" role="dialog" style="padding-top: 10px">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Új gyakorlat felvétele</h4>
                </div>
                <div class="modal-body">
                    <form id="ujgyakhozzaad" action="#" method="post">
                        <div id="hibaDiv"></div>
                        <div class="form-group">
                            <label class="form-control" for="ujgyak_nev">Gyakorlat neve: </label>
                            <input class="form-control" type="text" name="ujgyak_nev" id="ujgyak_nev" />
                            <br>
                            <span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_nevHiba">
                                Gyakorlat nevének megadása kötelező! csak betüket és számokat tartalmazhat
                            </span>
                        </div>
                        <div class="form-group">
                            <label class="form-control" for="ujgyak_tipus">Gyakorlat Típusa: </label>
                            <input class="form-control" type="text" name="ujgyak_tipus" id="ujgyak_tipus" />
                            <br>
                            <span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_tipusHiba">
                                Pl Mell, Hát, - Váll megadása kötelező!
                            </span>
                        </div>
                        <div class="form-group">
                            <label class="form-control" for="ujgyak_leiras">Leírás: </label>
                            <textarea class="form-control" name="ujgyak_leiras" id="ujgyak_leiras" cols="40" rows="10"></textarea>
                            <br>
                            <span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_leirasHiba">
                                Ha kitöltöd, speciális karaktereket nem tartalmazhat!
                            </span>
                        </div>
                        <div class="form-group">
                            <label class="form-control" for="ujgyak_video">Video Link/ID: </label>
                            <input type="text" class="form-control" name="ujgyak_video" id="ujgyak_video" />
                            <br>
                            <span class="hibaVisszajelzes hibaSzakasz" id="ujgyak_videoHiba">
                                    Ha kitöltöd, speciális karaktereket nem tartalmazhat!
                            </span>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-default" type="button" onclick="hozzadas()">Felvesz</button>
                            <button class="btn btn-default" type="button" onclick="resetvan()">Reset</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal" onclick="resetvan()">Bezár</button>
                </div>
            </div>

        </div>
    </div>
    <!-- gyak hozzáadás modal vége-->