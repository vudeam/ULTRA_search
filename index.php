<!DOCTYPE HTML>
<html>
<head></head>
<body>
	<form method='GET' action='/api/search.php' id='form'>
		<input type='text' name='name' placeholder='card name'/>
		<input type='submit' value='Fetch cards'/>
	</form>
	<script>
		window.onload = () => {
			document.querySelector("#form").addEventListener("submit", (evt) => {
				evt.preventDefault();
				evt.target.style = "visibility: hidden";
				let x = new XMLHttpRequest();
				x.open("GET", `/api/search.php?name=${evt.target[0].value}`, false);
				x.send();
				var json = JSON.parse(x.responseText);
				for (let i = 0; i < json["data"].length; i++)
					document.body.innerHTML += `<p><img src='https://api.scryfall.com/cards/${json["data"][i]["set"].toLowerCase()}/${json["data"][i]["num"]}?format=image'></p>`;
			});
		};
	</script>