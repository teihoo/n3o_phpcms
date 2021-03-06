<?php
/*~ list_Besedila.php - List texts
.---------------------------------------------------------------------------.
|  Software: N3O CMS (frontend and backend)                                 |
|   Version: 2.2.2                                                          |
|   Contact: contact author (also http://blaz.at/home)                      |
| ------------------------------------------------------------------------- |
|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
| Copyright (c) 2007-2014, Blaž Kristan. All Rights Reserved.               |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| ------------------------------------------------------------------------- |
| This file is part of N3O CMS (backend).                                   |
|                                                                           |
| N3O CMS is free software: you can redistribute it and/or                  |
| modify it under the terms of the GNU Lesser General Public License as     |
| published by the Free Software Foundation, either version 3 of the        |
| License, or (at your option) any later version.                           |
|                                                                           |
| N3O CMS is distributed in the hope that it will be useful,                |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU Lesser General Public License for more details.                       |
'---------------------------------------------------------------------------'
*/

// define default values for URL ID and Find parameters (in case not defined)
if ( !isset($_GET['ID']) )   $_GET['ID'] = "0";
if ( !isset($_GET['Find']) ) $_GET['Find'] = "";
if ( !isset($_GET['Tip']) )  $_GET['Tip'] = "";
if ( !isset($_GET['Sort']) ) $_GET['Sort'] = "";

// define sort order
$Sort = "B.BesediloID DESC";
if ( $_GET['Sort'] == "Datum" )
	$Sort = "B.Datum DESC";
elseif ( $_GET['Sort'] == "Ime" )
	$Sort = "B.Ime";

$List = $db->get_results(
	"SELECT DISTINCT
		B.BesediloID AS ID,
		B.Ime AS Name,
		B.Tip,
		B.Slika,
		B.Datum,
		B.Izpis,
		B.ACLID
	FROM Besedila B
		LEFT JOIN BesedilaOpisi BO ON B.BesediloID = BO.BesediloID
	WHERE 1=1 " .
		(($_GET['Find']=="")? "": "AND (B.Ime LIKE '%".$db->escape(trim($_GET['Find']))."%' OR BO.Naslov LIKE '%".$db->escape(trim($_GET['Find']))."%' OR BO.Povzetek LIKE '%".$db->escape(trim($_GET['Find']))."%')").
		(($_GET['Tip']=="")? "": "AND B.Tip='".$db->escape($_GET['Tip'])."' ") .
	"ORDER BY ". $Sort
	);

$RecordCount = count($List);
?>
<SCRIPT Language="JAVASCRIPT">
<!--//
$('#list').live('pageinit', function(event){
	$("input[name=Find]").bind("change", function(event,ui){
		var URL = '<?php echo $_SERVER['PHP_SELF']; ?>?Action=<?php echo $_GET['Action']; ?>';
		$("select").each(function(index){
			if ( this[this.selectedIndex].value != "" )
				URL += '&' + this.name + '=' + this[this.selectedIndex].value;
		});
		if ( this.value != "" ) URL += '&Find='+this.value;
		document.location.href = URL;
	});
	$("select").bind("change", function(event,ui){
		var URL = '<?php echo $_SERVER['PHP_SELF']; ?>?Action=<?php echo $_GET['Action'] . ($_GET['Find']!=""? "&Find=".$_GET['Find']: ""); ?>';
		$("select").each(function(index){
			if ( this[this.selectedIndex].value != "" )
				URL += '&' + this.name + '=' + this[this.selectedIndex].value;
		});
		document.location.href = URL;
	});
});
//-->
</SCRIPT>
<?php
// are we requested do display different page?
$Page = isset($_GET['pg']) ? (int)$_GET['pg'] : 1;
// number of possible pages
$NuPg = (int) (($RecordCount-1) / $MaxRows) + 1; // $MaxRows defined in list.php
// fix page number if out of limits
$Page = min(max($Page, 1), $NuPg);
// start & end page
$StPg = min(max($Page - 2,1), max(1, $NuPg - 4));
$EdPg = min($StPg + 4, min($Page + 4, $NuPg));
// previous and next page numbers
$PrPg = $Page - 1; // <1 == no previous page
$NePg = $Page + 1; // >$NuPg == no next page

// start and end row from recordset
$StaR = ($Page - 1) * $MaxRows + 1;
$EndR = min(($Page * $MaxRows), $RecordCount);

echo "<div id=\"list\" data-role=\"page\" data-title=\"Texts\">\n";
echo "<div data-role=\"header\" data-theme=\"b\">\n";
echo "<h1>Texts</h1>\n";
echo "<a href=\"./#menu". left($_GET['Action'],2) ."\" title=\"Back\" class=\"ui-btn-left\" data-iconpos=\"left\" data-icon=\"arrow-l\" data-ajax=\"false\" data-transition=\"slide\">Back</a>\n";
echo "<a href=\"./\" title=\"Home\" class=\"ui-btn-right\" data-ajax=\"false\" data-iconpos=\"notext\" data-icon=\"home\">Home</a>\n";

echo "<div data-role=\"navbar\">\n";
echo "<ul>";
echo "<li>";
echo "<SELECT NAME=\"Sort\" SIZE=\"1\">";
echo "<OPTION VALUE=\"\">Zaporedje vnosa</OPTION>";
echo "<OPTION VALUE=\"Ime\"".(($_GET['Sort']=="Ime")? " SELECTED": "").">Naziv</OPTION>";
echo "<OPTION VALUE=\"Datum\"".(($_GET['Sort']=="Datum")? " SELECTED": "").">Datum</OPTION>";
echo "</SELECT>";
echo "</li>\n";
echo "<li>";
echo "<SELECT NAME=\"Tip\" SIZE=\"1\">";
echo "<OPTION VALUE=\"\">- all types -</OPTION>";
$Tipi = $db->get_col("SELECT SifrText FROM Sifranti WHERE SifrCtrl='BESE' ORDER BY SifrCtrl, SifrZapo");
if ( $Tipi ) foreach ( $Tipi as $Tip )
	echo "<OPTION VALUE=\"$Tip\"". ($_GET['Tip']==$Tip ? " SELECTED" : "").">$Tip</OPTION>";
echo "</SELECT>";
echo "</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "</div>\n";
echo "<div data-role=\"content\">\n";
echo "<div style=\"margin-bottom:30px;\"><input type=\"search\" name=\"Find\" id=\"search\" value=\"". ($_GET['Find']!=""? $_GET['Find']: "") ."\" data-theme=\"d\" /></div>\n";

// display results
if ( $RecordCount == 0 ) {

	echo "<div class=\"ui-body ui-body-d ui-corner-all\" style=\"color:red;padding:1em;text-align:center;\">\n";
	echo "<B>No data!</B>\n";
	echo "</div>\n";

} else {
	echo "<ul data-role=\"listview\" data-filter-test=\"true\" data-theme=\"d\" data-split-icon=\"delete\" data-split-theme=\"d\">\n";
	//foreach ( $List as $Item ) {
	$i = $StaR-1;
	while ( $i < $EndR ) {
		// get list item
		$Item = $List[$i++];
		if ( contains($ActionACL,"R") ) {
			echo "<li>";
			echo "<a href=\"edit.php?Izbor=".$_GET['Izbor']."&ID=$Item->ID\" data-ajax=\"false\">";
			if ( $Item->Slika != "" )
				echo "<img src=\"../media/besedila/". basename($Item->Slika) ."\">";
			echo "<h3>". $Item->Name ."</h3>";
			echo "<p>". $Item->Tip ."</p>";
			if ( !$Item->Izpis )
				echo "<span class=\"ui-li-count\">hidden</span>";
			echo "</a>";
			if ( contains($ActionACL,"D") )
				echo "<a href=\"#\" onclick=\"check('$Item->ID','$Item->Name');\">Delete</a>";
			echo "</li>\n";
		}
	}
	if ( $_GET['Find']=="" && $RecordCount == 25 )
		echo "<li style=\"text-align:center;\">... 'search' for more results ...</li>\n";
	echo "</ul>\n";
}
echo "</div>\n";

if ( $NuPg > 1 ) {
	echo "<div data-role=\"footer\">\n";
	echo "<div data-role=\"navbar\" data-theme=\"a\">\n";
	echo "<ul>\n";
	for ( $i = $StPg; $i <= $EdPg; $i++ ) {
		echo "<li>";
		echo "<a href=\"list.php?Action=". $_GET['Action'] . ($_GET['Sort']!=""? "&Sort=".$_GET['Sort']: "") . ($_GET['Tip']!=""? "&Tip=".$_GET['Tip']: "") . ($_GET['Find']!=""? "&Find=".$_GET['Find']: "") ."&pg=$i\" data-ajax=\"false\"". ( $i == $Page ? " data-theme=\"b\"" : "" ) .">";
		if ( $i == $EdPg && $EdPg < $NuPg )
			echo "&gt;";
		else if ( $i == $StPg && $StPg > 1 )
			echo "&lt;";
		else
			echo "$i";
		echo "</a>";
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</div>\n";
	echo "\t</div>\n";
}
echo "</div>\n"; // page
?>
