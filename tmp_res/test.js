var progressBar = document.getElementById("p"),
    client = new XMLHttpRequest();
client.open("GET", "magical-unicorns");
client.onprogress = function(pe) {
    if (pe.lengthComputable) {
        progressBar.max = pe.total;
        progressBar.value = pe.loaded;
    }
}
client.onloadend = function(pe) {
    progressBar.value = pe.loaded;
}
client.send();


var oReq = new XMLHttpRequest();

oReq.addEventListener("progress", updateProgress);
oReq.addEventListener("load", transferComplete);
oReq.addEventListener("error", transferFailed);
oReq.addEventListener("abort", transferCanceled);

oReq.open();

// ...

// progress on transfers from the server to the client (downloads)
function updateProgress(oEvent) {
    if (oEvent.lengthComputable) {
        var percentComplete = oEvent.loaded / oEvent.total * 100;
        // ...
    } else {
        // Unable to compute progress information since the total size is unknown
    }
}

function transferComplete(evt) {
    console.log("The transfer is complete.");
}

function transferFailed(evt) {
    console.log("An error occurred while transferring the file.");
}

function transferCanceled(evt) {
    console.log("The transfer has been canceled by the user.");
}