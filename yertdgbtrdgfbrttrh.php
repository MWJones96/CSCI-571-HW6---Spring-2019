<!DOCTYPE html>
<html>
<head>
	<title>Homework 6</title>
	<style type="text/css">
		#main-frame
		{
			border: 3px solid #CACACA;
			background-color: #FAFAFA;
			width: 700px;
			margin: auto;
		}

		#main-frame h1
		{
			text-align: center;
			margin-bottom: 15px;
		}

		#main-frame hr
		{
			width: 98%;
		}

		#main-frame div
		{
			padding-left: 15px;
			display: flex;
			height: 22px;
			align-items: center;
			margin-bottom: 5px;
		}

		#main-frame div input, select
		{
			height: 18px;
			margin-left: 10px;
		}

		#main-frame div ul
		{
			list-style-type: none;
			padding: 0;
		}

		#main-frame div strong
		{
			margin-left: 5px;
			margin-right: 5px;
		}

		#main-frame .nearby-search
		{
			margin-left: 20px;
		}

		#main-frame .buttons
		{
			padding: 0;
			margin: 0;

			margin-bottom: 25px;
			justify-content: center;
		}

		#main-frame .buttons input
		{
			width: 65px;
			height: 25px;
		}

		#results-table
		{
			padding: 0;
			margin: 0 auto;
			width: 1000px;
		}

		#results-table, #results-table td, #results-table th
		{
			border: 1px solid grey;
			border-collapse: collapse;
		}

		#results-table tr td img
		{
			width: 65px;
		}

		#error-bar
		{
			text-align: center;
			margin: auto;
			width: 1000px;
			background-color: #F0F0F0;
			border: 2px solid #E7E7E7;
			visibility: hidden;
		}
	</style>
</head>
<body>
	<?php
		function getJSON()
		{
			if (empty($_GET))
			{
				return '""';
			}

			$kwd = str_replace(' ', '%20', $_GET["keyword"]);
			$category = ($_GET["category"] == "all") ? "" : "&categoryId={$_GET["category"]}";

			$freeShipping = "false";
			$localPickup = "false";

			if (!isset($_GET["local"]) && !isset($_GET["free"]))
			{
				$freeShipping = "true";
				$localPickup = "true";
			}
			else
			{
				if (isset($_GET["local"])) { $localPickup = "true"; }
				if (isset($_GET["free"])) { $freeShipping = "true"; }
			}

			$condition = "";

			if (!isset($_GET["new"]) && !isset($_GET["used"]) && !isset($_GET["unspec"]))
			{
				$condition = "&itemFilter(4).value(0)=New&itemFilter(4).value(1)=Used&itemFilter(4).value(2)=Unspecified";
			}
			else
			{
				$index = 0;
				if (isset($_GET["new"])) { $condition .= "&itemFilter(4).value({$index})=New"; $index++; }
				if (isset($_GET["used"])) { $condition .= "&itemFilter(4).value({$index})=Used"; $index++; }
				if (isset($_GET["unspec"])) { $condition .= "&itemFilter(4).value({$index})=Unspecified"; }
			}

			$distance = "0";
			$zip = "90007";

			//Nearby search enabled
			if (isset($_GET["nearby"]))
			{
				if (empty($_GET["miles"]) == "") { $distance = "10"; }
				else { $distance = $_GET["miles"]; }

				if ($_GET["location"] == "zip") { $zip = $_GET["zip"]; }
			}

			$_API_URL = "http://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=MatthewJ-CS571-PRD-2f2cd4cf7-09303b6c&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&paginationInput.entriesPerPage=20&keywords={$kwd}{$category}&buyerPostalCode={$zip}&itemFilter(0).name=MaxDistance&itemFilter(0).value={$distance}&itemFilter(1).name=FreeShippingOnly&itemFilter(1).value={$freeShipping}&itemFilter(2).name=LocalPickupOnly&itemFilter(2).value={$localPickup}&itemFilter(3).name=HideDuplicateItems&itemFilter(3).value=true&itemFilter(4).name=Condition{$condition}";

			//Call API
			$json = file_get_contents($_API_URL);

			return $json;
		}
	?>
	<form id="main-frame" method="get">
		<h1><i>Product Search</i></h1>
		<hr>
		<div>
			<h3>Keyword</h3>
			<input name="keyword" id="keyword" input type="textarea" name="keyword-entry" value="<?php if (isset($_GET["keyword"])) echo $_GET["keyword"] ?>" required></input>
		</div>
		<div>
			<h3>Category</h3>
			<select name="category" id="category">
				<option value="all">All Categories</option>
				<option value="550">Art</option>
				<option value="2984">Baby</option>
				<option value="267">Books</option>
				<option value="11450">Clothing, Shoes & Accessories</option>
				<option value="58058">Computers/Tablets & Networking</option>
				<option value="26395">Health & Beauty</option>
				<option value="11233">Music</option>
				<option value="1249">Video Games & Consoles</option>
			</select>
		</div>
		<div>
			<h3>Condition</h3>
			<input name="new" id="new" type="checkbox" value="<?php if (isset($_GET["new"])) echo $_GET["new"] ?>">New</input>
			<input name="used" id="used" type="checkbox" value="<?php if (isset($_GET["used"])) echo $_GET["used"] ?>">Used</input>
			<input name="unspec" id="unspecified" type="checkbox" value="<?php if (isset($_GET["unspec"])) echo $_GET["unspec"] ?>">Unspecified</input>
		</div>
		<div>
			<h3>Shipping Options</h3>
			<input name="local" id="local" type="checkbox" value="<?php if (isset($_GET["local"])) echo $_GET["local"] ?>">Local Pickup</input>
			<input name="free" id="free-shipping" type="checkbox" value="<?php if (isset($_GET["free"])) echo $_GET["free"] ?>">Free Shipping</input>
		</div>
		<table class="nearby-search">
			<tr>
				<td>
					<input id="enable-search" type="checkbox" name="nearby" onchange="enableNearbySearch()"></input>
					<strong>Enable Nearby Search</strong>
					<input class="cond-fields" type="number" name="miles" placeholder="10" disabled></input>
					<strong>miles from</strong>
				</td>
				<td>
					<input class="cond-fields" type="radio" value="here" name="location" onclick = "disableZipReq()" disabled checked>Here</input>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input class="cond-fields" type="radio" name="location" onclick = "enableZipReq()" value="zip" disabled></input>
					<input id="zip" class="cond-fields" type="textarea" name="zip" placeholder="zip code" disabled></input>
				</td>
			</tr>
		</table>
		<br>
		<div class="buttons">
			<input id="submit-form" type="submit" name="submit" value="Search" disabled></input>
			<input type="reset" name="clear" value="Clear" onclick="clearForm()"></input>
		</div>
	</form>
	<br>
	<table id="results-table">
	</table>
	<div id="error-bar">
	</div>
	<script type="text/javascript">
		var geoLocationJSON = null;

		function submitForm()
		{
			var json = <?php echo getJSON(); ?>;
			if (json == "") { return; }

			if (json.findItemsAdvancedResponse[0].ack[0] == "Failure")
			{
				if (json.findItemsAdvancedResponse[0].errorMessage[0].error[0].errorId[0] == "18")
				{
					document.getElementById("error-bar").innerHTML = "Zipcode is invalid";
					document.getElementById("error-bar").style.visibility = "visible";
				}
				
				return;
			}

			var html_text = "";
			html_text += "<tr>";
			html_text += "<th><strong>Index</strong></th>";
			html_text += "<th><strong>Photo</strong></th>";
			html_text += "<th><strong>Name</strong></th>";
			html_text += "<th><strong>Price</strong></th>";
			html_text += "<th><strong>Zip code</strong></th>";
			html_text += "<th><strong>Condition</strong></th>";
			html_text += "<th><strong>Shipping option</strong></th>";
			html_text += "</tr>";

			if (!("item" in json.findItemsAdvancedResponse[0].searchResult[0]))
			{
				document.getElementById("error-bar").innerHTML = "No records have been found";
				document.getElementById("error-bar").style.visibility = "visible";
				return;
			}

			var items = json.findItemsAdvancedResponse[0].searchResult[0].item;

			for (i = 0; i < items.length; i++)
			{
				html_text += "<tr>";
				html_text += "<td>" + (i+1) + "</td>";
				html_text += "<td><img src=\"" + items[i].galleryURL[0] + "\"/></td>";

				if (("title") in items[i])
				{
					html_text += "<td>" + items[i].title[0] +"</td>";
				}
				else
				{
					html_text += "<td>N/A</td>";
				}

				if (("sellingStatus") in items[i])
				{
					html_text += "<td>$" + Number(items[i].sellingStatus[0].currentPrice[0].__value__).toFixed(2) +"</td>";
				}
				else
				{
					html_text += "<td>N/A</td>";
				}

				if (("postalCode") in items[i])
				{
					html_text += "<td>" + items[i].postalCode[0] +"</td>";
				}
				else
				{
					html_text += "<td>N/A</td>";
				}

				if (("condition") in items[i])
				{
					html_text += "<td>" + items[i].condition[0].conditionDisplayName + "</td>";
				}
				else
				{
					html_text += "<td>N/A</td>";
				}
				if (("shippingInfo") in items[i])
				{
					html_text += "<td>" + ((Number(items[i].shippingInfo[0].shippingServiceCost[0].__value__) == 0) ? "Free Shipping" : ("$" + Number(items[i].shippingInfo[0].shippingServiceCost[0].__value__).toFixed(2))) +"</td>";
				}
				else
				{
					html_text += "<td>N/A</td>";
				}
				html_text += "</tr>";
			}

			document.getElementById("error-bar").style.visibility = "hidden";
			document.getElementById("results-table").innerHTML = html_text;
		}

		function disableZipReq()
		{
			document.getElementById("zip").required = false;
		}

		function enableZipReq()
		{
			document.getElementById("zip").required = true;
		}

		function enableNearbySearch()
		{
			var checked = document.getElementById("enable-search").checked;

			if (checked)
			{
				var fields = document.getElementsByClassName("cond-fields");
				for (i = 0; i < fields.length; i++)
				{
					fields[i].disabled = 0;
				}

			}
			else
			{
				var fields = document.getElementsByClassName("cond-fields");
				for (i = 0; i < fields.length; i++)
				{
					fields[i].disabled = true;
				}
			}
		}

		function clearForm()
		{
			var fields = document.getElementsByClassName("cond-fields");
			for (i = 0; i < fields.length; i++)
			{
				fields[i].disabled = true;
			}

			document.getElementById("results-table").innerHTML = "";
			document.getElementById("error-bar").innerHTML = "";
			document.getElementById("error-bar").style.visibility = "hidden";

			window.history.pushState({}, document.title, "/HW6/yertdgbtrdgfbrttrh.php");
			window.location.reload();
		}

		window.onload = function() {
			var xml = new XMLHttpRequest();

			xml.open("GET", "http://ip-api.com/json", false);
			xml.send();
			geoLocationJSON = JSON.parse(xml.responseText);

			document.getElementById("submit-form").disabled = false;

			submitForm();
		}
	</script>
</body>
</html>