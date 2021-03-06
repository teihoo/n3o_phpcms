<?php
/*~ edit_emlGrupe.php - Editing group members.
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

if ( !isset($_GET['ID']) ) $_GET['ID'] = "0";

$Group = $db->get_row(
	"SELECT emlGroupID, Naziv, KtgID
	FROM emlGroups
	WHERE emlGroupID = ". (int)$_GET['ID']
	);

?>
<SCRIPT Language="JAVASCRIPT">
<!--//
function setAction(form_obj, action_str) {
	form_obj.Action.value = action_str;
}

function setList(list_obj, select_obj) {
	var count = 0;

	list_obj.value = "";			
    for (i=0; i < select_obj.length; i++) {
		if (select_obj.options[i].selected && select_obj.options[i].value != "") {
			var startPosition = 0;
			var indexPosition = 0;
			var selectString;

			if (count > 0 ) { list_obj.value += ","; }

			selectString = select_obj.options[i].value;
			indexPosition = selectString.indexOf(",");

			for (; indexPosition > 0; indexPosition = selectString.indexOf(",", startPosition)) {
				list_obj.value += selectString.substring(startPosition, indexPosition);
				list_obj.value += "~";	
				startPosition = indexPosition + 1;
			}					

			list_obj.value += selectString.substring(startPosition, selectString.length);
			count++;
		}
	}
}

$(document).ready(function(){
	// bind to the form's submit event
	$("form[name='Vnos']").submit(function(){
		// inside event callbacks 'this' is the DOM element so we first
		// wrap it in a jQuery object and then invoke ajaxSubmit
		$(this).ajaxSubmit({
			target: '#divEdit', // target element(s) to be updated with server response
			beforeSubmit: function( formDataArr, jqObj, options ) {
				var fObj = jqObj[0];	// form object
				if (empty(fObj.Naziv))	{alert("Please enter group name!"); fObj.Naziv.focus(); return false;}
				$('#lgdData').html('<span class="gry"><img src="pic/control.spinner.gif" alt="Updating" border="0" height="14" width="14" align="absmiddle">&nbsp;: Updating ...</span>');
				return true;
			} // pre-submit callback
		});
		return false;
	});
	$("form[name='Grupe']").submit(function(){
		$(this).ajaxSubmit({target: '#divEdit'});
		return false;
	});
	$("#Add").click(function(){
		MemberList = document.getElementsByName("MemberList");
		NonGroup   = document.getElementsByName("NonGroup");
		setList(MemberList[0],NonGroup[0]);
		$("form[name='Grupe'] :hidden[name='Action']").val('Add');
		$("form[name='Grupe']").ajaxSubmit({target: '#divEdit'});
	});
	$("#Remove").click(function(){
		MemberList = document.getElementsByName("MemberList");
		Group      = document.getElementsByName("Group");
		setList(MemberList[0],Group[0]);
		$("form[name='Grupe'] :hidden[name='Action']").val('Remove');
		$("form[name='Grupe']").ajaxSubmit({target: '#divEdit'});
	});
	// refresh list
	listRefresh();
});
//-->
</SCRIPT>
<FIELDSET ID="fldData" style="width:430px;">
<LEGEND ID="lgdData">
	Basic&nbsp;information</LEGEND>
<FORM NAME="Vnos" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE BORDER="0" CELLPADDING="2" CELLSPACING="0" WIDTH="100%">
<TR><TD COLSPAN="2" HEIGHT="10"></TD></TR>
<TR>
	<TD ALIGN="right" WIDTH="25%"><B>Group:</B>&nbsp;</TD>
	<TD><INPUT TYPE="text" NAME="Naziv" SIZE="43" MAXLENGTH="50" VALUE="<?php echo ($Group ? $Group->Naziv : "") ?>" STYLE="width:100%;"></TD>
</TR>
<TR>
	<TD ALIGN="right" WIDTH="35%">Category:&nbsp;</TD>
	<TD><SELECT NAME="KtgID" SIZE="1" STYLE="width:100%;">
		<option value="">--- unspecified ---</option>
<?php
$List = $db->get_results("SELECT KategorijaID, Ime, ACLID FROM Kategorije WHERE Izpis <> 0 ORDER BY KategorijaID");
foreach( $List as $Item ) {
	$ACL = userACL( $Item->ACLID );
	if ( contains($ACL,"L") ) {
		echo "\t\t<option value=\"$Item->KategorijaID\"";
		echo $Group->KtgID == $Item->KategorijaID ? " SELECTED" : "";
		echo ">";
		echo str_repeat("&nbsp;", strlen($Item->KategorijaID)-2);
		echo $Item->Ime . "</option>\n";
	}
}
?>
	</SELECT>
	</TD>
</TR>
<TR>
	<TD ALIGN="right" COLSPAN="2" STYLE="margin-top:3px;padding-top:3px;border-top:silver solid 1px;"><INPUT TYPE="submit" VALUE=" Save " CLASS="but"></TD>
</TR>
</TABLE>
</FORM>
</FIELDSET>

<?php
if ( (int)$_GET['ID'] > 0 ) {
	// users
	$Members = $db->get_results(
		"SELECT EM.emlMemberID AS ID, EM.Email AS Naziv
		FROM emlMembers EM
			LEFT JOIN emlMembersGrp EMG ON EM.emlMemberID = EMG.emlMemberID
		WHERE EMG.emlMemberID IS NOT NULL AND EMG.emlGroupID = ". (int)$_GET['ID'] ."
		ORDER BY EM.Email"
		);

	$NonMembers = $db->get_results(
		"SELECT EM.emlMemberID AS ID, EM.Email AS Naziv
		FROM emlMembers EM
			LEFT JOIN emlMembersGrp EMG
				ON EM.emlMemberID = EMG.emlMemberID AND EMG.emlGroupID = ". (int)$_GET['ID'] ."
		WHERE EMG.emlMemberID IS NULL
		ORDER BY EM.Email"
		);
?>
<FIELDSET ID="fldUser" style="width:430px;">
<LEGEND ID="lgdUser">Users</LEGEND>
<FORM NAME="Grupe" ACTION="<?php echo $_SERVER['PHP_SELF']; ?>?<?php echo $_SERVER['QUERY_STRING'] ?>" METHOD="post">
<TABLE ID="results" BORDER="0" CELLPADDING="0" CELLSPACING="0" WIDTH="100%">
<TR>
	<TD ALIGN="right" WIDTH="45%">Not members:</TD>
	<TD ALIGN="center" WIDTH="10%"></TD>
	<TD ALIGN="right" WIDTH="45%">Members:</TD>
</TR>
<TR>
	<TD ALIGN="left">
	<INPUT Name="GroupID" Type="HIDDEN" VALUE="<?php echo (int)$_GET['ID'] ?>">
	<INPUT Name="MemberList" Type="HIDDEN" VALUE="">
	<INPUT Name="Action" Type="HIDDEN" VALUE="">
	<SELECT NAME="NonGroup" MULTIPLE SIZE="15" STYLE="width:100%;">
<?php
if ( count( $NonMembers ) > 0 )
	foreach ( $NonMembers as $NonMember )
		echo "\t\t<OPTION VALUE=\"$NonMember->ID\">$NonMember->Naziv</OPTION>\n";
?>
	</SELECT>
	</TD>
	<TD ALIGN="center">
	<IMG ID="Add" SRC="pic/icon.arrow_right.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon"><BR><BR>
	<IMG ID="Remove" SRC="pic/icon.arrow_left.png" WIDTH=16 HEIGHT=16 ALT="" ALIGN="absmiddle" CLASS="icon">
<!--
	<INPUT TYPE="Submit" NAME="Add" VALUE="&nbsp;---&gt;&nbsp;" ONCLICK="setList(UserList,NonGroup),setAction(this.form,'Add');" CLASS="but"><BR><BR>
	<INPUT TYPE="Submit" NAME="Remove" VALUE="&nbsp;&lt;---&nbsp;" ONCLICK="setList(UserList,Group),setAction(this.form,'Remove');" CLASS="but">
-->
	</TD>
	<TD ALIGN="right">
	<SELECT NAME="Group" MULTIPLE SIZE="15" STYLE="width:100%;">
<?php
if ( count( $Members ) > 0 )
	foreach ( $Members as $Member )
		echo "\t\t<OPTION VALUE=\"$Member->ID\">$Member->Naziv</OPTION>\n";
?>
	</SELECT>
	</TD>
</TR>
</TABLE>
</FORM>
</FIELDSET>
<?php
}
?>
