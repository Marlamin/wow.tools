var theme = localStorage.getItem('theme');
if(!theme){
	localStorage.setItem('theme', 'dark');
	theme = 'dark';
}else{
	if(theme == 'light'){
		updateCSSVars('light');
	}else{
		updateCSSVars('dark');
	}
}

document.addEventListener("DOMContentLoaded", function(event) {
	document.getElementById("themeToggle").addEventListener("click", toggleTheme );
});

function toggleTheme(){
	var theme = localStorage.getItem('theme');
	if(theme == 'dark'){
		localStorage.setItem('theme', 'light');
		updateCSSVars('light');
	}else if(theme == 'light'){
		localStorage.setItem('theme', 'dark');
		updateCSSVars('dark');
	}
}

function updateCSSVars(theme){
	if(theme == 'dark'){
		document.documentElement.style.setProperty('--background-color', '#343a40');
		document.documentElement.style.setProperty('--text-color', 'rgba(255,255,255,.8)');
		document.documentElement.style.setProperty('--hover-color', '#fff');
		document.documentElement.style.setProperty('--diff-added-color', '#368a23');
		document.documentElement.style.setProperty('--diff-removed-color', '#9b0d0d');
	}else if(theme == 'light'){
		document.documentElement.style.setProperty('--background-color', '#f8f9fa');
		document.documentElement.style.setProperty('--text-color', '#000000');
		document.documentElement.style.setProperty('--hover-color', '#7e7e7e');
		document.documentElement.style.setProperty('--diff-added-color', '#e6ffe6');
		document.documentElement.style.setProperty('--diff-removed-color', '#ffe6e6');
	}
}
/* multiple modal scroll fix */
$(function() {
	$('.modal').on("hidden.bs.modal", function (e) {
		if($('.modal:visible').length) {
			$('body').addClass('modal-open');
		}
	});
});

function renderBLPToIMGElement(url, elementID){
	// Check if data-loaded is present and true, hackfix
	if(document.getElementById(elementID).getAttribute('data-loaded') == 'true'){
		return;
	}

	fetch(url).then(function(response) {
		return response.arrayBuffer();
	}).then(function(arrayBuffer) {
		let data = new Bufo(arrayBuffer);
		let blp = new BLPFile(data);

		let canvas = document.createElement('canvas');
		canvas.width = blp.width;
		canvas.height = blp.height;

		let image = blp.getPixels(0, canvas);

		let img = document.getElementById(elementID);
		img.src = canvas.toDataURL();
		img.setAttribute('data-loaded', true);
	});
}