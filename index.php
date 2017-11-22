<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <title>PhosNet</title>
  <link href="used_js/js-image-slider.css" rel="stylesheet" type="text/css" />
  <script src="used_js/js-image-slider.js" type="text/javascript"></script>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  <link rel="stylesheet" type="text/css" href="style/style.css" />
  
<style type="text/css">
#apDiv1 {
	position:absolute;
	width:362px;
	height:195px;
	z-index:1;
	left: 19px;
	top: 223px;
}
#apDiv2 {
	width:394px;
	height:218px;
	z-index:2;
	left: 408px;
	top: 207px;
	position: absolute;
}
</style>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
<script language="JavaScript">
function Localization(objform)
{
        //var objform = document.form;
        objform.action = "network_cell.php";
        objform.submit();
}

function Pathway(objform)
{
        //var objform = document.form;
        objform.action = "select_pathway.php";
        objform.submit();
}

</script>

</head>

<body>
<div id="main">
    <div id="links"></div>
    
<? include("top.html"); ?>

  <div id="site_content">
  <div class="yellow1"> 
    <h1> Welcome to PhosNeT</h1>
    <p>&nbsp;&nbsp;&nbsp;PhosNet can let users input a group of proteins/genes and the system efficiently returns the protein phosphorylation networks associated with three network models, such as network with protein-protein interactions, network with subcellular localization, and network with metabolic pathway and protein-protein interactions. Furthermore, in order to provide a cancer analysis for kinases and phosphoproteins, a total of 30 experiment series involved in 39 cancer types from Affymetrix Human Genome U133 Plus 2.0 Array (GPL570), in which consisting of 54675 probe set for over 47000 transcripts, are integrated in this work.</p>
  </div>
<div class="function">
      <p align=left>
	<form name='form' method='get' action='network_phos.php'>
	
	<script language="JavaScript">
	function toggle(source) {
	checkboxes = document.getElementsByName('GSE[]');
	for(var i=0, n=checkboxes.length;i<n;i++) {
		checkboxes[i].checked = source.checked;
	}
	}
	</script>

<table width = 99% border=1 align=center cellpadding=10>
<tr><td>
<?
//get the parameters
$kinase_name = $_GET['kinase'];
$quick_search = $_POST['keyword'];
$species = $_POST['species'];
$example = "LYN,SYK,BLNK,PLCG2,RASGRP3,HRAS,RAF1,MAP2K1,MAPK1,FOS,TPR,SRC";


//include "mysql_connection.inc";
//show the kinase

echo "<table width=100% border=0 align=center>";
echo "<tr align=left><td valign='top' colspan='2'>";
echo "<h3><font size='4' color='black'><b>Step 1. Input a group of genes:</b></font></h3><br>";


if($quick_search != "")
echo "<textarea class='input-xlarge span10' rows='4' cols='70' name='substrate[]' wrap='Off'>$quick_search</textarea><br>";
else
echo "<textarea class='input-xlarge span10' rows='4' cols='70' name='substrate[]' wrap='Off'>$example</textarea><br>";
echo "</td>";

echo "<td align='left' valign='top'>";
echo "<h3><font size='4' color='black'><b>Step 2. Select the organism:</b></font></h3><br>";
echo "<table cellpadding='10' border='2' bordercolor='orange' width=70% height='80'></td>";
echo "<td><font color='black'>";
echo "<label><input name='species' type='radio' value='human' checked/>&nbsp;&nbsp; Homo sapiens (Human)</label><br>";
echo "<label><input name='species' type='radio' value='mouse' />&nbsp;&nbsp; Mus musculus (Mouse)</label><br>";
echo "<label><input name='species' type='radio' value='rat' />&nbsp;&nbsp; Rattus norvegicus (Rat)</label><br>";
echo "</font></td></tr></table>";
echo "</td></tr>";

echo "<tr><td colspan='3' align='left' valign='top'>";
echo "<h3><font size='4' color='black'><b>Step 3. Choose the type of network analysis:</b></font></h3><br>";
echo "</td></tr>";

echo "<tr valign=top>";
echo "<td width=25%><input type='image' img src='./images/network_phos.png'  onclick=this.form.action='network_phos.php' value='Interacting Network'></td>";
echo "<td width=25%><input type='image' img src='./images/network_cell.png' onclick=Localization(this.form)></td>";
echo "<td width=25%><input type='image' img src='./images/network_pathway.png' onclick=Pathway(this.form)></td>";
echo "</tr>";
echo "</table>";
?>
<br>
</td></tr>
</table>
	
	</form>
</p> 


      </div>
 
<div class="purple" id="ap" align="center">
        <div id="slider" align="center">
        <img src="picture1/1.png" alt="System Flow" />
        <img src="picture1/2.png" alt="Network with PPI Interaction"/>
        <img src="picture1/3.png" alt="Network with PPI Interaction"/>
        <img src="picture1/4.png" alt="Network with Subcellular Localization"/>
        <img src="picture1/5.png" alt="Network with Metabolic Pathway"/>
        
    </div>
    </div>
     
      <div id="site_content_bottom"></div>
  </div>

  <p>
    <? include("down.html"); ?>
  </p>
  </div>
</body>
</html>
