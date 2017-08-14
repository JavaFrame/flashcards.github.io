$("#file-selector-a").click(function(e)  {
	e.preventDefault();

	if(!isUserLogedIn()) {
		$("#login-modal").modal('show');
		return;
	}

	$("#file-selector").trigger('click');
});

//document.querySelector("#file-selector").addEventListener('change,')

$(function() {
	$("#file-selector").change(function() {
		var file = $("#file-selector").prop("files")[0];
		if(file != null) {
			$("#upload-btn").removeAttr("disabled");
			$("#upload-btn").removeClass("disabled")
		}
	});
	$("#upload-btn").click(function(){
		var file = $("#file-selector").prop("files")[0];
		if(file == null) return;
		if(id_token == -1) {
			alert("You must be signedIn.");
			return;
		}
		$("#upload-modal").modal('show');
		console.log(file);
		var fd = new FormData();
		fd.append('file', file);
		fd.append('fun', 'uploadCards');
		fd.append('id_token', id_token);

		fd.append('name', $("#cards-name").val());
		fd.append('description', $("#cards-description").val());
		fd.append('subject', $("#cards-subject").val());
		fd.append('language_from', $("#cards-language-from").val());
		fd.append('language_to', $("#cards-language-to").val());
		fd.append('ignore_first_line', $("#ignoring-first-line-cb").val());

		
		var xhr = new XMLHttpRequest();
		xhr.open('POST', './php/create_cards.php', true);

		xhr.upload.onprogress = function(e) {
			if(e.lengthComputable) {
				var percentComplete = (e.loaded / e.total) * 100;
      			console.log(percentComplete + '% uploaded');
      			$("#upload-progressbar").css("width", percentComplete + "%");
      			$("#upload-progressbar").text(percentComplete + "%");
      			console.log("width: " + percentComplete + "%;");
			}
		};
		xhr.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				console.log(this.responseText);
				var returnObj = JSON.parse(this.responseText);
				if(returnObj.type == "error") {
					alert(returnObj.msg);
				}
			}
		}

		xhr.onload = function() {
			if(this.status == 200) {
				console.log(this.response);
				$("#upload-modal-close-btn").removeAttr("disabled");
				$("#upload-modal-close-btn").removeClass("disabled")
			}
		};

		xhr.send(fd);
	});

})
