function search() {
	var URLparams = parseURLParams(document.location.href);
	$("#search-input").val(URLparams['s'][0]);
	$("#search-input").removeAttr("placeholder");
	sendGet("./php/cards.php", "search", "s="+URLparams['s'][0], function(xhr){
		//console.log(xhr.response)
		var searchObj = JSON.parse(xhr.responseText);
		var searchHtml = "";
		searchObj.forEach(function(cards) {
			searchHtml += 
			`
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2><a href="./learn.html?c=` + encodeURIComponent(cards.name.replace("'", "%27")) + `">` + cards.name + `</a></h2>
					<ul class="list-inline">
						<li><strong>Subject </strong>` + cards.subject + `</li>`;
					if(cards.subject == "Languages") {
						searchHtml += `
						<li><strong>From </strong>` + cards.language_from + `</li>
						<li><strong>To </strong>` + cards.language_to + `</li>
						`;
					}
						searchHtml += `
						<li><strong>Owner </strong>` + cards.owner_name + `</li>
					</ul>
				</div>				
				<div class="panel-body">
					<p>
					` + cards.description + `
					</p>
				</div>
			</div>
			`;
		});	
		if(searchObj.length == 0) {
			searchHtml += `
			<div class="panel panel-default">
				<div class="panel-heading">
					No Cards were found!
				</div>				
			</div>
			`;
		}
		$("#search-results").html(searchHtml);
	} , async = true)
}

function learnNextCard() {

}

function parseURLParams(url) {
    var queryStart = url.indexOf("?") + 1,
        queryEnd   = url.indexOf("#") + 1 || url.length + 1,
        query = url.slice(queryStart, queryEnd - 1),
        pairs = query.replace(/\+/g, " ").split("&"),
        parms = {}, i, n, v, nv;

    if (query === url || query === "") return;

    for (i = 0; i < pairs.length; i++) {
        nv = pairs[i].split("=", 2);
        n = decodeURIComponent(nv[0]);
        v = decodeURIComponent(nv[1]);

        if (!parms.hasOwnProperty(n)) parms[n] = [];
        parms[n].push(nv.length === 2 ? v : null);
    }
    return parms;
}