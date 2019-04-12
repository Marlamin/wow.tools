var selectingBuild1 = false;
var selectingBuild2 = false;

var build1 = '';
var build2 = '';

function resetDiffs(){
	document.getElementById("diffButton").text = 'Diff builds';
	document.getElementById("diffButton").href = '#';
	document.getElementById("diffButton").target = '';
	$(".diffbadge").remove();
	build1 = '';
	build2 = '';
	selectingBuild1 = false;
	selectingBuild2 = false;
	$("#resetButton").hide();
}
$( document ).ready(function() {
	$("#diffButton").removeClass('disabled');
	$("#diffButton").on("click", function(e){
		document.getElementById("diffButton").text = 'Click the row of the first build (old)';
		selectingBuild1 = true;
		$("#resetButton").show();
	});

	$("#resetButton").on("click", function(e){
		resetDiffs();
	});

	$("#buildtable tbody").on("click", "tr", function(e){
		console.log(e.currentTarget.children[3].innerText);
		if(selectingBuild1){
			build1 = e.currentTarget.children[3].innerText;
			$(e.currentTarget.children[3]).append(" <span class='badge badge-danger diffbadge'>First build (old)</span>");
			selectingBuild1 = false;
			selectingBuild2 = true;
			document.getElementById("diffButton").text = 'Click the row of the second build (new)';
		}else if(selectingBuild2){
			console.log(e.currentTarget.children[3]);
			build2 = e.currentTarget.children[3].innerText;
			$(e.currentTarget.children[3]).append(" <span class='badge badge-danger diffbadge'>Second build (new)</span>");
			selectingBuild1 = false;
			selectingBuild2 = false;
			document.getElementById("diffButton").text = 'Click to diff (might take up to a minute to generate)';
			document.getElementById("diffButton").href = '/builds/diff.php?from=' + build1 + '&to=' + build2;
			document.getElementById("diffButton").target = '_BLANK';

			$("#diffButton").one('click', function(e){
				setTimeout( function() { resetDiffs(); }, 1500);
			});
		}
	});
});