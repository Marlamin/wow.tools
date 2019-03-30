function fillModal(fileDataID){
	$( "#moreInfoModalContent" ).load( "/files/scripts/filedata_api.php?filedataid=" + fileDataID );
}

function fillPreviewModal(buildconfig, contenthash, filedataid){
	$( "#previewModalContent" ).load( "/scripts/preview_api.php?buildconfig=" + buildconfig + "&contenthash=" + contenthash + "&filedataid=" + filedataid);
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