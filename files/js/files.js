$("#files").on('click', '.fileTableDL', function(e){
	if(e.altKey){
		event.preventDefault();
		showButtons();
		addFileToDownloadQueue(this.href);
	}
});

document.addEventListener("DOMContentLoaded", function(event) {
	if(localStorage.getItem('queue[fdids]')){
		updateButton();
		showButtons();
	}
});

function addFileToDownloadQueue(file){
	// Parse URL
	var url = new URL(file);
	const urlParams = new URLSearchParams(url.search);
	var buildConfig  = urlParams.get('buildconfig');
	var cdnConfig = urlParams.get('cdnconfig');
	var fileDataID = urlParams.get('filedataid');

	// Update localstorage
	var currentBuildConfig = localStorage.getItem('queue[buildconfig]');
	if(!currentBuildConfig){
		localStorage.setItem('queue[buildconfig]', buildConfig);
	}else if(currentBuildConfig != buildConfig){
		showDifferentBuildWarning();
	}

	var currentCDNConfig = localStorage.getItem('queue[cdnconfig]');
	if(!currentCDNConfig){
		localStorage.setItem('queue[cdnconfig]', cdnConfig);
	}

	var fdids = localStorage.getItem('queue[fdids]');
	if(!fdids){
		localStorage.setItem('queue[fdids]', fileDataID);
	}else{
		var fdidArr = fdids.split(',');
		fdidArr.push(fileDataID);
		localStorage.setItem('queue[fdids]', fdidArr.join(','));
	}

	updateButton();
}

function showButtons(){
	$("#multipleFileDLButton").show();
	$("#multipleFileResetButton").show();
}

function updateButton(){
	var fdids = localStorage.getItem('queue[fdids]').split(',');
	$("#multipleFileDLButton").text('Download selected files (' + fdids.length + ')');
	$("#multipleFileDLButton").attr('href', 'https://wow.tools/casc/zip/fdids?buildConfig=' + localStorage.getItem('queue[buildconfig]') + '&cdnConfig=' + localStorage.getItem('queue[cdnconfig]') + '&ids=' + localStorage.getItem('queue[fdids]') + '&filename=selection.zip');
}

function resetQueue(){
	localStorage.removeItem('queue[buildconfig]');
	localStorage.removeItem('queue[cdnconfig]');
	localStorage.removeItem('queue[fdids]');
	$("#multipleFileDLButton").popover('hide');
	$("#multipleFileDLButton").hide();
	$("#multipleFileResetButton").hide();
}

function showDifferentBuildWarning(){
	$("#multipleFileDLButton").popover({
        title: 'Warning',
        placement: 'bottom',
        content: '<p>You have files from different builds selected, this is currently not supported. Files will be retrieved from only the first build you selected.</p>',
        html: true
    });
    $("#multipleFileDLButton").popover('show');
}

function fillModal(fileDataID){
	$( "#moreInfoModalContent" ).load( "/files/scripts/filedata_api.php?filedataid=" + fileDataID );
}

function fillPreviewModal(buildconfig, contenthash, filedataid){
	$( "#previewModalContent" ).load( "/files/scripts/preview_api.php?buildconfig=" + buildconfig + "&contenthash=" + contenthash + "&filedataid=" + filedataid);
}

function fillChashModal(contenthash){
	$( "#chashModalContent" ).load( "/files/scripts/filedata_api.php?contenthash=" + contenthash);
}

$("html").on('hidden.bs.modal', '#moreInfoModal', function(e) {
	console.log("Clearing modal");
	$( "#moreInfoModalContent" ).html( '<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>' );
})

$("html").on('hidden.bs.modal', '#previewModal', function(e) {
	console.log("Clearing modal");
	$( "#previewModalContent" ).html( '<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>' );
})

$("html").on('hidden.bs.modal', '#chashModal', function(e) {
	console.log("Clearing modal");
	$( "#chashModalContent" ).html( '<i class="fa fa-refresh fa-spin" style="font-size:24px"></i>' );
})

$(function () {
	$('[data-toggle="popover"]').popover()
})