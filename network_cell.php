<html>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<title>PhosNet</title>
<head>
<?php
$species = $_GET['species'];
$kinase_tmp = $_GET['kinase'];
$kinase_tmp = rtrim($kinase_tmp);
$string = $_GET['substrate'];

if(strstr($string[0],"\r")==true)
        $list = str_replace("\r\n",";",$string[0]);
else if (strstr($string[0],",")==true)
        $list = str_replace(",",";",$string[0]);
else
        $list = implode(";",$string);

$cancer = $_GET['cancer'];

include "mysql_connection.inc";

$ac1 = array();
$id1 = array();
$ac2 = array();
$id2 = array();
$resource = array();
$pi = array();

$tmp = array_unique(explode(";",$list));

if($kinase_tmp != "")
array_push($tmp,$kinase_tmp);

//$protein = $tmp;

/*get membrane position*/
$file = fopen("membrane.txt", "r");
while(! feof($file))
{
  $tmp1=explode(" ",fgets($file));
  $membrane_x[count($membrane_x)]=$tmp1[0];
  $membrane_y[count($membrane_y)]=trim($tmp1[1]);
}
fclose($file);

/*get cytoplasm position*/
$file = fopen("cytoplasm.txt", "r");
while(! feof($file))
{
  $tmp1=explode(" ",fgets($file));
  $cytoplasm_x[count($cytoplasm_x)]=$tmp1[0];
  $cytoplasm_y[count($cytoplasm_y)]=trim($tmp1[1]);
}
fclose($file);

/*get nucleus position*/
$file = fopen("nucleus.txt", "r");
while(! feof($file))
{
  $tmp1=explode(" ",fgets($file));
  $nucleus_x[count($nucleus_x)]=$tmp1[0];
  $nucleus_y[count($nucleus_y)]=trim($tmp1[1]);
}
fclose($file);

/*get post gene's protein*/
for($i=0 ; $i < count($tmp) ; $i++)
{
	 $sql = "select Gene_name , UniProt_ID from RegPhos_ID_mapping where UniProt_ID like '%_{$species}' AND Gene_name = '$tmp[$i]'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                 while($data = mysql_fetch_array($result))
                 {
                        $gene_name[count($gene_name)] = $data[0];
			
			$protein[count($protein)] = $data[1];
                 }
         }
}


/*turn all protein to uniprotac*/
for($i=0 ; $i < count($protein) ; $i++)
{
	 $sql = "select AC_a from RegPhos_PPI_{$species} where ID_a = '$protein[$i]'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                $data = mysql_fetch_array($result);
		$protein_ac[count($protein_ac)]=$data[0];
         }
	 if($num_rows == 0)
	 {
		$sql = "select AC_b from RegPhos_PPI_{$species} where ID_b = '$protein[$i]'";
         	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
         	$num_rows = mysql_num_rows($result);
		if($num_rows > 0)
         	{
                	$data = mysql_fetch_array($result);
                	$protein_ac[count($protein_ac)]=$data[0];
         	}
		if($num_rows == 0)
			$protein_ac[count($protein_ac)]=" ";

	 }

}
/*search kinase in pathway*/
for($i=0 ; $i<count($protein) ; $i++)
{
	 $sql = "select Kinase,Description from RegPhos_kinase_{$species} where UniProt_ID = '$protein[$i]'";
	 $result = mysql_query($sql) or die("Query failed : " . mysql_error());
	 $num_rows = mysql_num_rows($result);
	 if($num_rows > 0)
	 {
		$data = mysql_fetch_array($result);
		$kinase[count($kinase)] = $data[0];
		$kinase_id[count($kinase_id)] = $protein[$i];
		$kinase_des[count($kinase_des)] = $data[1];
	 }
	
}
for($i=0 ; $i<count($kinase) ; $i++)
{
        $result=mysql_query("select AC_a from RegPhos_PPI_{$species} where ID_a = '$kinase_id[$i]'");
        $num_rows = mysql_num_rows($result);
        if($num_rows > 0)
        {
                $data = mysql_fetch_array($result);
                $kinase_ac[count($kinase_ac)] = $data[0];
        }
        if($num_rows == 0)
                $kinase_ac[count($kinase_ac)] = " ";
}

/*------------------------*/

/*get substrate*/
for($i=0 ; $i<count($kinase) ; $i++)
{
	$kinase_sql = "kinase like '$kinase[$i]'";
	$sql = "select b.gene_name , a.ID , b.protein_name , count(*) , a.reference from RegPhos_Phos_{$species} a, RegPhos_Phos_Protein_{$species} b where a.ID like '%_{$species}' and ".$kinase_sql." and a.ID = b.ID group by a.ID";
	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0)
	{
		while($data = mysql_fetch_array($result))
		{
			if(in_array($data[1],$protein))
			{
				$sub_kinase[count($sub_kinase)]=$kinase[$i];
				$sub_name[count($sub_name)] = str_replace("Name=","",substr($data[0],0,strpos($data[0],";")));
				$sub_id[count($sub_id)] = $data[1];
				$sub_des[count($sub_des)] = str_replace("RecName: Full=","",substr($data[2],0,strpos($data[2],";")));
				$sub_site[count($sub_site)] = $data[3];
				$sub_pubmed[count($sub_pubmed)] = $data[4];
			}
		}
	}
}
for($i=0 ; $i<count($sub_id) ; $i++)
{
	$result=mysql_query("select AC_a from RegPhos_PPI_{$species} where ID_a = '$sub_id[$i]'");
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0)
	{
		$data = mysql_fetch_array($result);
		$sub_ac[count($sub_ac)] = $data[0];
	}
	if($num_rows == 0)
		$sub_ac[count($sub_ac)] = " ";
}
/*------------------*/
/*-----interaction--------*/
for($num=0;$num<count($protein);$num++)
{
	for($num2=0;$num2<count($protein);$num2++)
	{
		$query = mysql_query("select AC_a , ID_a , AC_b , ID_b , resource , pubmedID from RegPhos_PPI_{$species} where ID_a = '$protein[$num]' AND ID_b = '$protein[$num2]'");
		$numrows=mysql_num_rows($query);

			while($data=mysql_fetch_array($query))
			{
				$ac1[count($ac1)]=$data[0];
        			$id1[count($id1)]=$data[1];
        			$ac2[count($ac2)]=$data[2];
        			$id2[count($id2)]=$data[3];
       				$resource[count($resource)]=$data[4];
        			$pi[count($pi)]=$data[5];
			}
	}
}

/*get experssion information*/
if($species == 'human')
{
if($cancer != "")
{
        for($i=0 ; $i<count($tmp) ; $i++)
        {
                $sql = "select $cancer,Gene,uniprot_id from RegPhos_expression_cancer where gene = '$tmp[$i]'"; // RegPhos 20130911
                $result = mysql_query($sql) or die("Query failed 4: " . mysql_error());
                $num_rows = mysql_num_rows($result);
                if($num_rows > 0)
                {
                        $data = mysql_fetch_array($result);
                        $exp_kinase[count($exp_kinase)]=$data[1];
                        $exp_value[count($exp_value)]=$data[0];
                        $exp_id[count($exp_id)]=$data[2];
                }
        }
}
}

/*-----------------------*/


/*get localization in cell*/
for($i=0 ; $i<count($protein); $i++)
{
	$sql = "select distinct Exp_annotation,Similarity_annotation from RegPhos_localization_{$species} where UniProt_ID='$protein[$i]'";
	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
	$num_rows = mysql_num_rows($result);
        $localization = mysql_fetch_array($result,MYSQL_NUM);

                if($localization[0] !== 'None')
		{
			if(strpos($localization[0],"Nucleus") == 'true')
			{
			 	$location[count($location)] = "Nucleus";
			}
			elseif(strpos($localization[0],"Membrane") == 'true')
			{	
				$location[count($location)] = "Membrane";
			}
                        elseif(strpos($localization[0],"Endoplasmic") == 'true')
                        {
                                $location[count($location)] = "Endoplasmic reticulum";
                        }
			elseif(strpos($localization[0],"Lysosome") == 'true')
                        {
                                $location[count($location)] = "Lysosome";
                        }
                        elseif(strpos($localization[0],"Golgi") == 'true')
                        {
                                $location[count($location)] = "Golgie";
                        }
			elseif(strpos($localization[0],"Cytoplasm") == 'true')
                        {
				$location[count($location)] = "Cytoplasm";
			}
                        else
                                $location[count($location)] = "None";

                }
		else
                {
                        if(strpos($localization[1],"Nucleus") == 'true')
                        {
                                $location[count($location)] = "Nucleus";
                        }
                        elseif(strpos($localization[1],"Membrane") == 'true')
                        {
                                $location[count($location)] = "Membrane";
                        }
                        elseif(strpos($localization[1],"Endoplasmic") == 'true')
                        {
                                $location[count($location)] = "Endoplasmic reticulum";
                        }
                        elseif(strpos($localization[1],"Lysosome") == 'true')
                        {
                                $location[count($location)] = "Lysosome";
                        }
                        elseif(strpos($localization[1],"Golgi") == 'true')
                        {
                                $location[count($location)] = "Golgie";
                        }
                        elseif(strpos($localization[1],"Cytoplasm") == 'true')
                        {
                                $location[count($location)] = "Cytoplasm";
                        }
                        else
                                $location[count($location)] = "None";

                }


}


/*--------------------*/
mysql_close($link);

?>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script src="cytoscape.js/cytoscape.min.js"></script>
		<script src="cytoscape.js/arbor.js"></script>
		<script src="cytoscape.js/cytoscape.js"></script>
		<script src="cytoscape.js/jquery.cxtmenu.js"></script>
		<script src="cytoscape.js/jquery.cxtmenu.min.js"></script>
		<script src="cytoscape.js/jquery.cytoscape-edgehandles.js"></script>
		<script src="cytoscape.js/jquery.cytoscape-edgehandles.js"></script>
		<script src="cytoscape.js/jquery.cytoscape-edgehandles.min.js"></script>
		<script src="cytoscape.js/jquery.cytoscape-panzoom.js"></script>
		<script src="cytoscape.js/jquery.cytoscape-panzoom.min.js"></script>

<style id="jsbin-css">
body { 
  font: 14px helvetica neue, helvetica, arial, sans-serif;
  background-repeat:no-repeat;
}

#cy {
        height: 800px;
        width: 1600px;
        background-image: url("images/cell.png");
        background-repeat: no-repeat;
        background-position: center center;
        position: absolute;
        left: 0px;
        top: 0px;
}
#note1 {
        width: 100%;
        height: 20%;
        position: absolute;
        background-color: #f0f0f0;
        top: 801px;
        left: 0px;
}
#fun{
        float: right;
        width: 10%;
        height: 800px;
        position: absolute;
        left: 1601px;
}


</style>

</head>

<body>
  <div id="main">
    <div id="links"></div>
    
    <div id="site_content">
    <div id="cy"></div>
     <div id="fun"> 
	 <?
if($species == "human")
{
echo "<b>Cancer : {$cancer}</b><br><br>";
echo "<b>Change Cancer:</b>";
echo "<form action ='network_cell.php' method='GET'>";
echo "<select name='cancer' onchange='changecancer(this)' size:'2'>";
echo "<option>&lt;select a caner&gt;</option>";
echo "<option value='Prostate_Cancer'>Prostate_Cancer</option>";
echo "<option value='Papillary_Thyroid_Cancer_1'>Papillary_Thyroid_Cancer_1</option>";
echo "<option value='Breast_Tumor'>Breast_Tumor</option>";
echo "<option value='Colorectal_Carcinoma'>Colorectal_Carcinoma</option>";
echo "<option value='Adenoma_and_Colorectal_Carcinoma'>Adenoma_and_Colorectal_Carcinoma</option>";
echo "<option value='Ductal_Breast_Carcinoma_and_Invasive_Lobular_Carcinoma'>Ductal_Breast_Carcinoma_and_Invasive_Lobular_Carcinoma</option>";
echo "<option value='Papillary_Thyroid_Cancer_2'>Papillary_Thyroid_Cancer_2</option>";
echo "<option value='Liver_Cancer'>Liver_Cancer</option>";
echo "<option value='Peripheral_T_cell_Lymphoma'>Peripheral_T_cell_Lymphoma</option>";
echo "<option value='Hepatocellular_Carcinoma'>Hepatocellular_Carcinoma</option>";
echo "<option value='Cervical_Cancer_1'>Cervical_Cancer_1</option>";
echo "<option value='Cervical_Cancer_2'>Cervical_Cancer_2</option>";
echo "<option value='Head_and_Neck_Cancer_1'>Head_and_Neck_Cancer_1</option>";
echo "<option value='Head_and_Neck_Cancer_2'>Head_and_Neck_Cancer_2</option>";
echo "<option value='Bladder_Cancer'>Bladder_Cancer</option>";
echo "<option value='Metastatic_Melanoma'>Metastatic_Melanoma</option>";
echo "<option value='Breast_Cancer_and_Basal_like_Cancer'>Breast_Cancer_and_Basal_like_Cancer</option>";
echo "<option value='Breast_Cancer_1'>Breast_Cancer_1</option>";
echo "<option value='Midgut_Carcinoid'>Midgut_Carcinoid</option>";
echo "<option value='Tongue_Squamous_Cell_Carcinoma'>Tongue_Squamous_Cell_Carcinoma</option>";
echo "<option value='Breast_Cancer_2'>Breast_Cancer_2</option>";
echo "<option value='Lung_Tumor_1'>Lung_Tumor_1</option>";
echo "<option value='Lung_Tumor_2'>Lung_Tumor_2</option>";
echo "<option value='Adrenocortical_Carcinoma_and_Adrenocortical_Adenoma'>Adrenocortical_Carcinoma_and_Adrenocortical_Adenoma</option>";
echo "<option value='Serous_Carcinoma_1'>Serous_Carcinoma_1</option>";
echo "<option value='Serous_Carcinoma_2'>Serous_Carcinoma_2</option>";
echo "<option value='Conventional_Renal_Cell_Cancer'>Conventional_Renal_Cell_Cancer</option>";
echo "<option value='Collecting_Duct_Carcinoma'>Collecting_Duct_Carcinoma</option>";
echo "<option value='Renal_Cell_Cancer'>Renal_Cell_Cancer</option>";
echo "<option value='Renal_Oncocytoma'>Renal_Oncocytoma</option>";
echo "<option value='Papillary_Renal_Cell_Cancer'>Papillary_Renal_Cell_Cancer</option>";
echo "<option value='Wilms_Tumor'>Wilms_Tumor</option>";
echo "<option value='Diffuse_Large_B_cell_Lymphoma'>Diffuse_Large_B_cell_Lymphoma</option>";
echo "<option value='Nasopharyngeal_Carcinoma'>Nasopharyngeal_Carcinoma</option>";
echo "<option value='Nodular_Lymphocyte_Predominant_Hodgkin_Lymphoma'>Nodular_Lymphocyte_Predominant_Hodgkin_Lymphoma</option>";
echo "<option value='Primary_Renal_Cell_Carcinoma'>Primary_Renal_Cell_Carcinoma</option>";
echo "<option value='Alveolar_Soft_Part_Sarcoma'>Alveolar_Soft_Part_Sarcoma</option>";
echo "<option value='Primary_Gastric_Tumor'>Primary_Gastric_Tumor</option>";
echo "<option value='Ovarian_Cancer'>Ovarian_Cancer</option>";

echo "</select>";
echo "<input type='hidden' id='cancer' name='cancer' value='firsttime'/>";
echo "<input type='hidden' id='ac' name='substrate[]' value=''/>";
echo "<input type='hidden' id='species' name='species' value='{$species}'/>";
echo "<input type='submit' id='cc' name='cc' value='Search' style='VISIBILITY: hidden'/>";
echo "</form>";
}
?>

</br>
<b></b>
<FORM NAME="my_form" METHOD=POST TARGET>
    <INPUT TYPE="checkbox"  NAME="check1" onClick="checkbox(this)" checked value="interaction"><b>Interaction<P> 
    <INPUT TYPE="checkbox"  NAME="check2" onClick="checkbox(this)" checked value="phos">Phosphorylation</b><P>
 </FORM>   
</br>
<form>
<input type="button" id="delete" name="Delete node or edge" value="Delete node or edge" onClick="Delete()"/>
</form>
  </div>
  <div id="note1">
  <p>Click nodes or edges.</p>
<script>
var tmp_gene =  ["<?php echo join("\", \"", $tmp); ?>"];
var protein =  ["<?php echo join("\", \"", $protein); ?>"];
var protein_ac = ["<?php echo join("\", \"", $protein_ac); ?>"];
var kinase = ["<?php echo join("\", \"", $kinase); ?>"];
var kinase_id = ["<?php echo join("\", \"", $kinase_id); ?>"];
var kinase_ac = ["<?php echo join("\", \"", $kinase_ac); ?>"];
var kinase_group = ["<?php echo join("\", \"", $kinase_group); ?>"];
var kinase_family = ["<?php echo join("\", \"", $kinase_family); ?>"];
var kinase_des = ["<?php echo join("\", \"", $kinase_des); ?>"];
var sub_kinase = ["<?php echo join("\", \"", $sub_kinase); ?>"];
var sub_name = ["<?php echo join("\", \"", $sub_name); ?>"];
var sub_id = ["<?php echo join("\", \"", $sub_id); ?>"];
var sub_ac = ["<?php echo join("\", \"", $sub_ac); ?>"];
var sub_des = ["<?php echo join("\", \"", $sub_des); ?>"];
var sub_site = ["<?php echo join("\", \"", $sub_site); ?>"];
var sub_pubmed = ["<?php echo join("\", \"", $sub_pubmed); ?>"];
var ac1 = ["<?php echo join("\", \"", $ac1); ?>"];
var id1 = ["<?php echo join("\", \"", $id1); ?>"];
var ac2 = ["<?php echo join("\", \"", $ac2); ?>"];
var id2 = ["<?php echo join("\", \"", $id2); ?>"];
var resource = ["<?php echo join("\", \"", $resource); ?>"];
var pi = ["<?php echo join("\", \"", $pi); ?>"];
var exp_kinase = ["<?php echo join("\", \"", $exp_kinase); ?>"];
var exp_value = ["<?php echo join("\", \"", $exp_value); ?>"];
var exp_id = ["<?php echo join("\", \"", $exp_id); ?>"];
var loca = ["<?php echo join("\", \"", $location); ?>"];
var membrane_x = ["<?php echo join("\", \"", $membrane_x); ?>"];
var membrane_y = ["<?php echo join("\", \"", $membrane_y); ?>"];
var cytoplasm_x = ["<?php echo join("\", \"", $cytoplasm_x); ?>"];
var cytoplasm_y = ["<?php echo join("\", \"", $cytoplasm_y); ?>"];
var nucleus_x = ["<?php echo join("\", \"", $nucleus_x); ?>"];
var nucleus_y = ["<?php echo join("\", \"", $nucleus_y); ?>"];
var gene_name = ["<?php echo join("\", \"", $gene_name); ?>"];
var gene_id = ["<?php echo join("\", \"", $gene_id); ?>"];
var cancer = "<?php echo $cancer; ?>";
var species = "<?php echo $species; ?>";
 $('#cy').cytoscape({
  style: cytoscape.stylesheet()
	.selector('node')
      .css({
        'content': 'data(name)',
        'text-valign': 'center',
        'color': 'white',
		'background-color':'#4169e1',
        'text-outline-width': 2,
        'text-outline-color': '#888',
		'width': 30,
		'height': 30
      })
    .selector('edge')
      .css({
	'width':2,
	'line-color':'orange',
	 'opacity': 0.8
      })
    .selector(':selected')
      .css({
        'border-color': 'black',
		'border-width': 3,
        'line-color': 'black',
        'target-arrow-color': 'black',
        'source-arrow-color': 'black'
      })
    .selector('.sub_pathway')
      .css({
        'opacity': 0.3,
	'text-opacity': 0.0,
	'shape' : 'rectangle',
	'width': 44,
        'height': 17,
	'background-color':'#4169e1'
      })
    .selector('.faded')
      .css({
        'opacity': 0.8,
      })
    .selector('.faded_node')
      .css({
        'opacity': 0.1,
        'text-opacity': 0
      })
	 .selector('.phos')
      .css({
        'width': 10,
		'height': 10,
		'background-color': '#000000',
		'opacity': 1.0
      })
	  .selector('.phos_edge')
      .css({
        'width':3,
		'line-color': 'red',
		'target-arrow-shape': 'triangle',
		'target-arrow-color': 'red'
      })
	  .selector('.substrate')
      .css({
		'content': 'data(name)',
        'text-valign': 'center',
        'color': 'white',
		'background-color':'#4169e1',
        'text-outline-width': 2,
        'text-outline-color': '#888',
		'width': 30,
		'height': 30
		
      })
	  .selector('.background')
      .css({
		'border-width':0.0001,
		'border-color': '#ffffff',
		
		
      }),
  ready: function(){
		window.cy = this;
		cy.zoomingEnabled(false);
		cy.panningEnabled(false);
		/*set protein to membrane or Cytoplasm or Nucleus*/
		var N = cy.nodes("[Iskinase='false']");
		var mem_num = 0;
		var cytop_num = 0;
		var nucleus_num = 0;
		for(var i = 0 ; i<N.length ; i++)
		{
			var tmp = N[i].data('loca').toLowerCase();
			if(tmp.match('membrane') == 'membrane' &&  mem_num < membrane_x.length)
			{
				N[i].position({
					x: parseInt(membrane_x[mem_num],10),
					y: parseInt(membrane_y[mem_num],10)
				});
				mem_num++;
			}
			else if(tmp.match('cytoplasm') == 'cytoplasm' && cytop_num < cytoplasm_x.length)
                        {
                                N[i].position({
                                        x: parseInt(cytoplasm_x[cytop_num],10),
                                        y: parseInt(cytoplasm_y[cytop_num],10)
                                });
                                cytop_num++;
                        }
			else if(tmp.match('nucleus') == 'nucleus'  && nucleus_num < nucleus_x.length)
                        {
                                N[i].position({
                                        x: parseInt(nucleus_x[nucleus_num],10),
                                        y: parseInt(nucleus_y[nucleus_num],10)
                                });
                                nucleus_num++;
                        }
		}
		/*------------------*/
		/*if no localization data*/
		var x_tmp= 60;
                var y_tmp= 60;
                for(var i = 0 ; i<N.length ; i++)
                {
			var tmp = N[i].data('loca').toLowerCase();
			if(tmp == "" || (tmp.match('nucleus') == null && tmp.match('membrane') == null && tmp.match('cytoplasm') == null))
			{
                        	N[i].position({
                                        x: x_tmp,
                                        y: y_tmp
                                });
                        	y_tmp+=50;
                        	if(y_tmp >= 780)
                        	{
                                	y_tmp=60;
                                	x_tmp = x_tmp+120;
                        	}
			}
                }

		/*if protein has multiple localization data*/
		
		var x_tmp= 1200;
                var y_tmp= 100;
                for(var i = 0 ; i<N.length ; i++)
                {
                        var tmp = N[i].data('loca').toLowerCase();
                        if((tmp.match('nucleus') == 'nucleus' && tmp.match('membrane') == 'membrane') || (tmp.match('nucleus') == 'nucleus' && tmp.match('cytoplasm') == 'cytoplasm') || (tmp.match('membrane') == 'membrane' && tmp.match('cytoplasm') == 'cytoplasm'))
                        {
                                N[i].position({
                                        x: x_tmp,
                                        y: y_tmp
                                });
                                y_tmp+=50;
                                if(y_tmp >= 780)
                                {
                                        y_tmp=60;
                                        x_tmp = x_tmp+120;
                                }
                        }
                }

		/*find kinase and set gene name*/
		if(kinase_id[0]!="")
		{
			for(var i=0 ; i < kinase_id.length ; i++)
			{
				var node = cy.nodes("[uniprotid='"+kinase_id[i]+"']");
				if(node.data('gene') == "")
                        		node.data('gene',kinase[i]);
                		else
                		{
                        		var G = node.data('gene');
                        		if(G.match(kinase[i]) == null)
                        		{
                                		G = G + " ; " + kinase[i];
                                		node.data("gene",G);
                        		}
                }

				node.data('Iskinase','true');
                        	node.data('kinase_des',kinase_des[i]);
				node.css('shape','hexagon');
			}
		}

		/*phosphrylation*/
		if(sub_kinase[0]!="")
		{
			for(var i=0 ; i < sub_kinase.length ; i++)
			{
				var thisnode=cy.nodes("[uniprotid='"+sub_id[i]+"']");//取得在pathway上的node
				if(thisnode.data('Iskinase') != "true")
				{
					thisnode.data('gene',sub_name[i]);
					thisnode.data('uniprotac',sub_ac[i]);
					thisnode.data('sub_des',sub_des[i]);
					thisnode.data('sub_site',sub_site[i]);
					thisnode.data('sub',"true");
				}
				var n= cy.nodes("[gene*='"+sub_kinase[i]+"']");//取得這個phos的kinase
				thisnode.selectify();
				thisnode.addClass('substrate');
				cy.add({
					group: "edges",
					data: {source: n.data("id"),target: thisnode.data('id'),edge_for_phos:"true" , pubmedid: sub_pubmed[i]},
					classes: 'phos_edge'
				});
				var n_tmp = cy.nodes("[host='"+thisnode.data('id')+"']");//取得背景
				var phos_number=n_tmp.data('phos_num');
				phos_number++;
				n_tmp.data('phos_num',phos_number);
			}
		}
		/*interaction*/
		for(var i=0 ;i < ac1.length ; i++)
		{
			if(cy.$("#"+ac2[i]+ac1[i]).data("id")==undefined)
				{
					cy.add({
						group: "edges",
						data: { id :ac1[i]+ac2[i] , source:id1[i], target:id2[i], pubmedid:pi[i] , resource:resource[i], PPI:ac1[i] + " and "+ ac2[i], edge_for_inter: "true" }
					});
				}
			else
			{
				var cut =resource[i].split(";");
                		var thisedge=cy.edges("[id="+"'"+ac2[i]+ac1[i]+"'"+"]");
                		for(var m=0 ; m < cut.length ; m++)
                		{
					thisedge = cy.edges("[id="+"'"+ac2[i]+ac1[i]+"'"+"]");
                    			if((thisedge.data("resource")).match(cut[m]) == null)
					{
						var tmp = thisedge.data("resource");
                       				tmp =  tmp + "; "  + cut[m];
                   				thisedge.data("resource",tmp);
                    			}
                		}
                		cut =pi[i].split(";");
                		for(var m=0 ; m < cut.length ; m++)
                		{
					thisedge = cy.edges("[id="+"'"+ac2[i]+ac1[i]+"'"+"]");
					if((thisedge.data("pubmedid")).match(cut[m]) == null)
					{
						var tmp = thisedge.data("pubmedid");
						tmp =  tmp + "; "  + cut[m];
						thisedge.data("pubmedid",tmp);
					}
                		}
			}
		}
		/*set expression value*/
		if(cancer != "")
		{
			for(var i=0 ; i < exp_kinase.length ; i++)
			{
				var n=cy.nodes("[gene*='"+exp_kinase[i]+"']");
				n.data('value',exp_value[i]);
			
			}
		}
		/*set background image*/
		cy.nodes("[phos_num ='0']").css("background-opacity",0);
		cy.nodes("[phos_num>='1']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/1.png");
		//cy.nodes("[phos_num='2']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/2.png");
		//cy.nodes("[phos_num='3']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/3.png");
		//cy.nodes("[phos_num='4']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/4.png");
		//cy.nodes("[phos_num='5']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/5.png");
		//cy.nodes("[phos_num>='6']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/6.png");
		/*-------------------------*/
		/*set expression color*/
		if(cancer != "")
		{
			var bg = cy.nodes("[background='true']");
			var nodes = cy.nodes().not(bg);
			for(var i=0 ; i<nodes.length ; i++)
			{
				var value = nodes[i].data('value')+0;
		/*	
				if(value >-0.000000001 && value <=0.000000001)
					nodes[i].css('background-color','#000000');
				else if(value >0.000000001 && value <=0.95)
                                	nodes[i].css('background-color','#270000');	
				else if(value >0.95 && value <=1.4)
                                	nodes[i].css('background-color','#370000');
				else if(value >1.4 && value <=1.85)
                                	nodes[i].css('background-color','#500000');
				else if(value >1.85 && value <=2.3)
                                	nodes[i].css('background-color','#690000');
				else if(value >2.3 && value <=2.75)
                                	nodes[i].css('background-color','#820000');
				else if(value >2.75 && value <=3.2)
                                	nodes[i].css('background-color','#9B0000');
				else if(value >3.2 && value <=3.65)
                                	nodes[i].css('background-color','#B40000');
				else if(value >3.65 && value <=4.1)
                                	nodes[i].css('background-color','#CD0000');
				else if(value >4.1 && value <=4.55)
                               		nodes[i].css('background-color','#E60000');
				else if(value >4.55 && value!='Null')
                                	nodes[i].css('background-color','#FF0000');
				else if(value > -0.95 && value <=-0.000000001)
                                	nodes[i].css('background-color','#001E00');
				else if(value >-1.4 && value <=-0.95)
                               		nodes[i].css('background-color','#003700');
				else if(value >-1.85 && value <=-1.4)
                                	nodes[i].css('background-color','#005000');
				else if(value >-2.3 && value <=-1.85)
                                	nodes[i].css('background-color','#006900');
				else if(value >-2.75 && value <=-2.3)
                                	nodes[i].css('background-color','#008200');
				else if(value >-3.2 && value <=-2.75)
                                	nodes[i].css('background-color','#009B00');
				else if(value >-3.75 && value <=-3.2)
                                	nodes[i].css('background-color','#00B400');
				else if(value >-4.1 && value <=-3.65)
                                	nodes[i].css('background-color','#00CD00');
				else if(value >-4.55 && value <=-4.1)
                                	nodes[i].css('background-color','#00E600');
				else if(value <=-4.55)
                                	nodes[i].css('background-color','#00FF00');
			*/
                                if(value >-0.1 && value <0.1)
                                        nodes[i].css('background-color','#000000');
// red
                                else if(value >=0.1 && value <=0.5)
                                        nodes[i].css('background-color','#690000');
                                else if(value >0.5 && value <=1.5)
                                        nodes[i].css('background-color','#9B0000');
                                else if(value >1.5 && value <=3.0)
                                        nodes[i].css('background-color','#CD6000');
                                else if(value >3.0 && value <=4.5)
                                        nodes[i].css('background-color','#E60000');
                                else if(value >4.5 && value!='Null')
                                        nodes[i].css('background-color','#FF0000');
// green
                                else if(value >-0.5 && value <=-0.1)
                                        nodes[i].css('background-color','#006900');
                                else if(value >-1.5 && value <=-0.5)
                                        nodes[i].css('background-color','#009B00');
                                else if(value >-3.0 && value <=-1.5)
                                        nodes[i].css('background-color','#00CD00');
                                else if(value >-4.5 && value <=-3.0)
                                        nodes[i].css('background-color','#00E600');
                                else if(value <=-4.5)
                                        nodes[i].css('background-color','#00FF00');

			}
		}
		/*--------------------*/
		/*event function*/
		function handle_click(event) {
                         //var target = event.target;
                clear();
                if(event.cyTarget.isNode()==true && event.cyTarget.data('sub') == "true"){
				print("Gene name : "+event.cyTarget.data('gene'));
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
				print("Protein Description : "+event.cyTarget.data("sub_des"));
				print("Localization :"+event.cyTarget.data("loca"))
				print("Number of kinase-specific phosphorylation sites : "+event.cyTarget.data("sub_site"));
				if(species == "human")
					print("Expression value : "+event.cyTarget.data("value"));

                        }
		if(event.cyTarget.isNode()==true && event.cyTarget.data('Iskinase') == "true" && event.cyTarget.data('sub') != "true"){
                                print("Gene name : "+event.cyTarget.data('gene'));
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
                                print("Protein Description : "+event.cyTarget.data("kinase_des"));
				print("Localization :"+event.cyTarget.data("loca"));
				if(species == "human")
                                	print("Expression value : "+event.cyTarget.data("value"));

                        }
		if(event.cyTarget.isNode()==true && event.cyTarget.data('Iskinase') != "true" && event.cyTarget.data('sub') != "true"){
				print("Gene name : "+event.cyTarget.data('gene'));
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
				print("Localization :"+event.cyTarget.data("loca"));
				if(species == "human")
					print("Expression value : "+event.cyTarget.data("value"));
                        }

				if(event.cyTarget.isEdge()==true && event.cyTarget.data('edge_for_inter') == "true"){
				print("Interaction between : "+event.cyTarget.data("PPI"));
                                print("Resource : "+event.cyTarget.data("resource"));
                                printPumed("PubmedID : ",event.cyTarget.data("pubmedid"));
					}
		if(event.cyTarget.isEdge()==true && event.cyTarget.data('edge_for_phos') == "true"){
                                print("Kinase : "+event.cyTarget.data("kinase"));
                                print("Substrate : "+event.cyTarget.data("substrate"));
                                printPumed("PubmedID : ",event.cyTarget.data("pubmedid"));
                                }

                 }

				function clear() {
                        document.getElementById("note1").innerHTML = "";
                    }

                    function print(msg) {
                        document.getElementById("note1").innerHTML += "<p>" + msg + "</p>";
                    }
                    function printNodes(msg,KB){
                        document.getElementById("note1").innerHTML += "<p>" +msg+"<a href=http://www.uniprot.org/uniprot/"+KB+" target=_blank  style=text-decoration:none;color:blue;>"+ KB +"</a>"+ "</p>";
                    }
			function printPumed(msg,PUMED)
                    {
                        var cut = PUMED.split(";");
                        var tmp = "";
                        //document.getElementById("note1").innerHTML += "<p>"+msg;
						//newNote(event.cyTarget.innerHTML.trim())20482850
                        for(var j=0 ; j < cut.length ; j++)
						{
                                var a = $.trim(cut[j]);

                               // tmp += "<font onclick=newNote('"+a+"') style=color:blue;>" + cut[j] +"</font>" + ";";
								tmp +="<a href=http://140.138.144.141/~cytoscape/trans.php onclick=window.open('http://www.ncbi.nlm.nih.gov/pubmed/?term="+a+"','gg',config='height=500,width=500')  target=_blank  style=text-decoration:none;color:blue;>"+a +"</a>"+";";
						}
                        document.getElementById("note1").innerHTML += "<p>" + msg + tmp +"</p>";
                    }
		cy.on('select' , 'node' , function(e){
			var node = cy.$(':selected');
			var neighborhood = node.neighborhood().add(node);
			cy.elements().addClass('faded_node');
			neighborhood.removeClass('faded_node');
			cy.nodes("[background='true']").removeClass('faded_node');
		});
		cy.on('tap', function(e){
		if( e.cyTarget === cy ){
			cy.elements().removeClass('faded_node');//??????
			}
		});
		cy.on('tap','node' ,function(e){
                       handle_click(e); 
                });
		cy.on('tap','edge' ,function(e){
                       handle_click(e);
                });
	}
});
var cy = $("#cy").cytoscape("get");
if(protein[0] != "")
		{
			for(var i=0 ; i < protein.length ; i++)
			{
				if(cy.nodes("[id='"+protein[i]+"']").data('id') == null)
				{
					var tmp=cy.add({//加上一個特有的背景
						group: "nodes",
						data:{background:"true" , phos_num: 0},
						classes: 'background',
						selectable: false,
						grabbable: false
						
					});
				
					var tmp1=cy.add({
						group: "nodes",
						data: {parent:tmp.data('id'),id:protein[i] ,name: gene_name[i] , uniprotac: protein_ac[i] , uniprotid: protein[i] , Iskinase:'false', value:"Null" , loca:loca[i] , gene: gene_name[i]}
					});
					tmp.data('host',tmp1.data('id'));
				}
				else
				{
					var node = cy.nodes("[id='"+protein[i]+"']");
					var gene = node.data('gene');
					if(name.match(gene_name[i]) == null)
					{
						gene = gene + " ; " + gene_name[i];
						node.data('gene',gene);
					}
				}
			}
		}
//cy.layout({ name: 'grid' });
function changed(theselect){
				 cy.layout({
				 name: theselect.value,
				 fit: true
				 });
}
function changecancer(theselect)
{
	var a = document.getElementById("cancer");
	a.value = theselect.value;
	var b = document.getElementById("ac");
	b.value = tmp_gene[0]+"\r";
	for(var i =1 ; i < tmp_gene.length ; i++)
	{
		b.value += tmp_gene[i]+"\r";
	}
	var submit = document.getElementById("cc");
        submit.click();
}

function checkbox(theselect)
{
	if(theselect.checked==true && theselect.value=="interaction")
		cy.edges("[edge_for_inter='true']").show();
	if(theselect.checked==false && theselect.value=="interaction")
		cy.edges("[edge_for_inter='true']").hide();
	if(theselect.checked==true && theselect.value=="phos")
                cy.edges("[edge_for_phos='true']").show();
        if(theselect.checked==false && theselect.value=="phos")
                cy.edges("[edge_for_phos='true']").hide();
}
/*delete node or edge*/
function Delete()
{
        var selected = cy.$(':selected');
        if(cy.$(':selected').data("id") == undefined)
                alert("Please select a node or edge")
        else
        if(confirm("Sure to delete?"))
        {
                var n = cy.nodes("[host='"+selected[0].data('id')+"']").add(selected)
                for(var i=1 ; i < selected.nodes().length ; i++)
                {
                        var m = cy.nodes("[host='"+selected[i].data('id')+"']").add(n);
                        n = m;
                }
                n.remove();
        }
}
</script>
</body>
</html>
