<!DOCTYPE html>
<html>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<title>PhosNet</title>
<head>
<?php
$x=array();//存txt檔的x位置
$y=array();//存txt檔的y位置
$protein=array();
$protein_ac=array();
$protein_id=array();
$species = $_GET['species'];
//$species='human';
//$path='map04662,LYN,SYK,BLNK,PLCG2,RASGRP3,HRAS,RAF1,MAP2K1,MAPK1,FOS,TPR,SRC';
$path=$_GET['path'];
$input=explode(",",$path);
//$input=explode(",",$_POST['path']);
$pathway_name="$input[0]";
$pathway_file="pathway/".$pathway_name.".txt";
$file = fopen($pathway_file, "r");
$protein_tmp=array();
$x_tmp=array();
$y_tmp=array();
//$tmp=explode(" ",fgets($file));
$tmp = getimagesize("pathway/{$pathway_name}.png");
$W=$tmp[0];
$H=$tmp[1];
$cancer = $_GET['cancer'];
//while(! feof($file))
//{
 // $tmp=explode(" ",fgets($file));
 // $tmp1=trim($tmp[0]);
//	if(in_array($tmp1,$PP) == false)
  //		$PP[count($PP)]=$tmp1;
  //$x_tmp[count($x_tmp)]=$tmp[1];
  //$y_tmp[count($y_tmp)]=trim($tmp[2]);
//}
fclose($file);

for($i = 1 ; $i < count($input) ; $i++)
{
	if(in_array($input[$i],$post)==false)
	$post[count($post)]=trim($input[$i]);
}

$MySQL_IP="140.138.144.141";
$User="regphos";
$Pw="regphos";
$link = mysql_connect($MYSQL_IP,$User,$Pw);
mysql_select_db("RegPhos"); 
$ac1=array();
$id1=array();
$ac2=array();
$id2=array();
$resource=array();
$pi=array();
$exist_ac=array();//存在但沒有連到的點(ac)
$exist_id=array();//存在但沒有連到的點(id)
$exist_num=0;//存在但是沒有連到的數量
$error=array();
$error_num=0;
$extand_line=array();//extant後所有點(不重複)
$extand_num=0;//extand後所有點的數量
$level=array();//紀錄level(利用 array)
$nomatch=array();//暫存沒有match到的部分
$nomatch_num=0;//暫存沒有match到的數量
$enter=array();//
$enter_ID=array();
$enter_num=0;
$enter_nospace=array();
$enter_nospace_num=0;
$i=0;
$j=0;
/*get x and y position*/
/*
for($i=0 ; $i < count($PP) ; $i++)
{
	$sql = "select AC,ID,local from Mix_kegg where map_ID = '$pathway_name' and AC = '$PP[$i]'";
	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0)
	{
		$count=1;
		while($data = mysql_fetch_array($result))
		{
			$protein[count($protein)] = $data[0];
			$protein_id[count($protein_id)] = $data[1];
			$tmp = explode(",",$data[2]);
			$x[count($x)] =intval(($tmp[0]+$tmp[2])/2);
  			$y[count($y)] =intval(($tmp[1]+$tmp[3])/2);
			$count++;
		}
	}
}*/
$sql = "select AC,ID,local,Gene_name from RegPhos_kegg_{$species} where map_ID = '$pathway_name'";
        $result = mysql_query($sql) or die("Query failed : " . mysql_error());
        $num_rows = mysql_num_rows($result);
        if($num_rows > 0)
        {
                $count=1;
                while($data = mysql_fetch_array($result))
                {
			
                        $protein[count($protein)] = $data[0];
                        $protein_id[count($protein_id)] = $data[1];
                        $tmp = explode(",",$data[2]);
                        $x[count($x)] =intval(($tmp[0]+$tmp[2])/2);
                        $y[count($y)] =intval(($tmp[1]+$tmp[3])/2);
			$gene_name_pathway[count($gene_name_pathway)]=$data[3];
                        $count++;
                }
        }
/*get all pathway's gene name
for($i=0 ; $i<count($protein_id) ; $i++)
{
         $sql = "select Gene_name from RegPhos_ID_mapping where UniProt_ID = '$protein_id[$i]'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                 while($data = mysql_fetch_array($result))
                 {
                        $gene_name_pathway[count($gene_name_pathway)] = $data[0];
                 }
         }

}
/*search which gene not in pathway*/
for($i=0 ; $i < count($post) ; $i++)
{
	if(in_array($post[$i],$gene_name_pathway) == false)
		$gene_np[count($gene_np)] = $post[$i];
}
/*get post gene's protein*/
for($i=0 ; $i < count($gene_np) ; $i++)
{
	$sql = "select Gene_name,UniProt_ID from RegPhos_ID_mapping where Gene_name = '$gene_np[$i]' and UniProt_ID like '%$species%'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                 while($data = mysql_fetch_array($result))
                 {
                        $gene_name[count($gene_name)] = $data[0];
			$post_id[count($post_id)] = $data[1];
                 }
         }
}
for($i=0 ; $i < count($post) ; $i++)
{
        $sql = "select UniProt_ID from RegPhos_ID_mapping where Gene_name = '$post[$i]' and UniProt_ID like '%$species%'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                 while($data = mysql_fetch_array($result))
                 {
                        $color_id[count($color_id)] = $data[0];
			$color_gene[count($color_gene)] = $post[$i];
                 }
         }
}
/*turn all input to uniprotac*/
for($i=0 ; $i < count($post_id) ; $i++)
{
         $sql = "select AC_a from RegPhos_PPI_{$species} where ID_a = '$post_id[$i]'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                $data = mysql_fetch_array($result);
                $post_ac[count($post_ac)]=$data[0];
         }
	 if($num_rows == 0)
	 {
		$sql = "select AC_b from RegPhos_PPI_{$species} where ID_b = '$post_id[$i]'";
        	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
         	$num_rows = mysql_num_rows($result);
         	if($num_rows > 0)
         	{
                	$data = mysql_fetch_array($result);
                	$post_ac[count($post_ac)]=$data[0];
         	}
		if($num_rows == 0)
			$post_ac[count($post_ac)]=" ";

	 }		
}
$allnode = $post_id;
for($i=0 ; $i < count($protein_id) ; $i++)
	if(in_array($protein_id[$i],$allnode) == false)
		$allnode[count($allnode)]=$protein_id[$i];
for($i=0 ; $i < count($allnode) ; $i++)
{
         $sql = "select AC_a from RegPhos_PPI_{$species} where ID_a = '$allnode[$i]'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                $data = mysql_fetch_array($result);
                $allnode_ac[count($allnode_ac)]=$data[0];
         }
}
/*get gene name which post from user
for($i=0 ; $i<count($post_id) ; $i++)
{
         $sql = "select Gene_name from ID_mapping where Uniport_ID = '$post_id[$i]'";
         $result = mysql_query($sql) or die("Query failed : " . mysql_error());
         $num_rows = mysql_num_rows($result);
         if($num_rows > 0)
         {
                 while($data = mysql_fetch_array($result))
                 {
                        $gene_name[count($gene_name)] = $data[0];
                        $gene_id[count($gene_id)] = $post_id[$i];
                 }
         }
        if($num_rows == 0)
        {
                $gene_name[count($gene_name)] = " ";
                $gene_id[count($gene_id)] = $post_id[$i];
        }
0
}
/*-----*/
/*search kinase in pathway*/
for($i=0 ; $i<count($allnode) ; $i++)
{
	 $sql = "select Kinase,Description from RegPhos_kinase_{$species} where UniProt_ID = '$allnode[$i]'";
	 $result = mysql_query($sql) or die("Query failed : " . mysql_error());
	 $num_rows = mysql_num_rows($result);
	 if($num_rows > 0)
	 {
		$data = mysql_fetch_array($result);
		$kinase[count($kinase)] = $data[0];
		$kinase_id[count($kinase_id)] = $allnode[$i];
		$kinase_des[count($kinase_des)] = $data[1];
		//$kinase_des[count($kinase_des)] = str_replace("RecName: Full=","",substr($data[1],0,strpos($data[1],";")));
	 }
	
}
for($i=0 ; $i<count($kinase_id) ; $i++)
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
	$sql = "select b.gene_name,a.ID,b.protein_name,count(*),a.reference from RegPhos_Phos_{$species} a, RegPhos_Phos_Protein_{$species} b where a.ID like '%$species%' and ".$kinase_sql." and a.ID=b.ID group by a.ID";
	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0)
	{
		while($data = mysql_fetch_array($result))
		{
			if(in_array($data[1],$allnode))
			{
				$sub_kinase[count($sub_kinase)]=$kinase[$i];
				$sub_name[count($sub_name)] = str_replace("Name=","",substr($data[0],0,strpos($data[0],";")));
				$sub_id[count($sub_id)] = $data[1];
				//$sub_des[count($sub_des)] = $data[2];
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

/*get experssion information*/
if($species == 'human')
{
if($cancer != "")
{
        for($i=0 ; $i<count($color_gene) ; $i++)
        {
                $sql = "select $cancer,Gene,uniprot_id from RegPhos_expression_cancer where Gene =  '$color_gene[$i]'";
                $result = mysql_query($sql) or die("Query failed : " . mysql_error());
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

if($_POST['count'] == null)//如果是第一次按search，近來這邊，line存的是有一些空白等不需要的東西
{
	$line=$protein;
	$item=count($line);
	//$line=array_unique($line);
	/*for($k=0 ; $k<$item ; $k++)//找出protein在資料庫裡的ac id
	{
        	$query = mysql_query("select AC_a,ID_a from RegPhos_PPI_{$species} where AC_a = '$line[$k]' or ID_a = '$line[$k]'");
        	$numrows=mysql_num_rows($query);
        	$data=mysql_fetch_array($query);
        	$protein_ac[count($protein_ac)] = $data[0];
                $protein_id[count($protein_id)] = $data[1];
		$query = mysql_query("select AC_b,ID_b from RegPhos_PPI_{$species} where AC_b = '$line[$k]' or ID_b = '$line[$k]'");
                $numrows=mysql_num_rows($query);
                $data=mysql_fetch_array($query);
		if(in_array($data[0],$protein_ac)==false)//這行判斷若是false表示在ACa或IDb裡沒有找到資料
		{
                	$protein_ac[count($protein_ac)] = $data[0];
                	$protein_id[count($protein_id)] = $data[1];
		}
	}*/
	$line=$allnode;
        $item=count($line);

}
else//如果對某點extand則近來這邊
{
	 $line=explode(" ",$_POST['count']);//消除掉無用的字串
	 $item=count($line);
	 $level=explode(" ",$_POST['level']);//取得相對應的level
	 $extend_ac=$_POST['thisac'];//存哪個點被extand
	 $extand_level=$_POST['thislevel'];
}
$query = mysql_query("select AC_a,AC_b from RegPhos_PPI_{$species} where AC_a = '$extend_ac' or AC_b = '$extend_ac'");
$numrows=mysql_num_rows($query);
while($data=mysql_fetch_array($query))
{
        if($data[0]==$extend_ac)
                if(in_array($data[1],$line) == false)
                {
                        $line[$item]=$data[1];
						$level[$item]=$extand_level+1;
                        $item++;
                        //$extand_line[$extand_num]=$data[1];
                        //$extand_num++;
                }
        if($data[1]==$extend_ac)
                if(in_array($data[0],$line) == false)
                {
                        $line[$item]=$data[0];
						$level[$item]=$extand_level+1;
                        $item++;
                        //$extand_line[$extand_num]=$data[0];
                        //$extand_num++;
                }
}
if($_POST['count'] != null)
{
	$enter_nospace=$line;
}

for($num=0;$num<$item;$num++)
{
if($line[$num]!=null)
{
	if($_POST['count'] == null)//如果是第一次按search
	{
		$query1=mysql_query("select AC_a from RegPhos_PPI_{$species} where ID_a = '$line[$num]'");
		$query2=mysql_query("select AC_b from RegPhos_PPI_{$species} where ID_b = '$line[$num]'");
		if($data1=mysql_fetch_array($query1))
		{
			if(in_array($data1[0], $enter_nospace) == false)
			{
				$enter_nospace[$enter_nospace_num]=$data1[0];
					$enter_nospace_num++;
			}
		}
		elseif($data2=mysql_fetch_array($query2))
		{
			if(in_array($data2[0], $enter_nospace) == false)
					{
				$enter_nospace[$enter_nospace_num]=$data2[0];
					$enter_nospace_num++;
			}
		
		}
		else
		{
			if(in_array($line[$num], $enter_nospace) == false)
			{
				$enter_nospace[$enter_nospace_num]=$line[$num];
					$enter_nospace_num++;
			}
		}
	}
	 
for($num2=0;$num2<$item;$num2++)
{
if($line[$num2]!=null)
{
$query = mysql_query("select AC_a,ID_a,AC_b,ID_b,resource,pubmedID from RegPhos_PPI_{$species} where (AC_a = '$line[$num]' and AC_b = '$line[$num2]') or (ID_a = '$line[$num]' and ID_b = '$line[$num2]') or (AC_a = '$line[$num]' and ID_b = '$line[$num2]') or  (ID_a = '$line[$num]' and AC_b = '$line[$num2]')");
$numrows=mysql_num_rows($query);

while($data=mysql_fetch_array($query))
{
	$ac1[$i]=$data[0];
        $id1[$i]=$data[1];
        $ac2[$i]=$data[2];
        $id2[$i]=$data[3];
        $resource[$i]=$data[4];
        $pi[$i]=$data[5];
	if(in_array($ac1[$i],$enter) == false)
	{
		$enter[$enter_num]=$ac1[$i];
		$enter_ID[$enter_num]=$id1[$i];
        	$enter_num++;
	}
	if(in_array($ac2[$i],$enter) == false)
        {
                $enter[$enter_num]=$ac2[$i];
		$enter_ID[$enter_num]=$id2[$i];
                $enter_num++;
        }

	$i++;
}
}
}
}
}
for($num=0;$num<$item;$num++)
	if($line[$num]!=null)
	{
		if(in_array($line[$num],$enter)==false && in_array($line[$num],$enter_ID)==false)
		{
			$nomatch[$nomatch_num]=$line[$num];
			$nomatch_num++;
		}
	}
for($num=0;$num<$nomatch_num;$num++)
{
	$check=0;
	$query = mysql_query("select AC_a,ID_a,AC_b,ID_b from RegPhos_PPI_{$species} where AC_a = '$nomatch[$num]' or AC_b = '$nomatch[$num]' or ID_a = '$nomatch[$num]'or ID_b = '$nomatch[$num]'");
$numrows=mysql_num_rows($query);

while($data=mysql_fetch_array($query))
{
	$check = 1;
	if($data[0] == $nomatch[$num]&& in_array( $nomatch[$num],$exist_ac) == false)
	{
		$exist_ac[$exist_num] = $data[0];
		$exist_id[$exist_num] = $data[1];
		$exist_num++;
	}
	if($data[2] == $nomatch[$num]&&in_array( $nomatch[$num],$exist_ac) == false)
	{
		$exist_ac[$exist_num] = $data[2];
                $exist_id[$exist_num] = $data[3];
                $exist_num++;
	}
	if($data[1] == $nomatch[$num]&&in_array( $nomatch[$num],$exist_id) == false)
        {
                $exist_ac[$exist_num] = $data[0];
                $exist_id[$exist_num] = $data[1];
                $exist_num++;
        }
	if($data[3] == $nomatch[$num]&&in_array( $nomatch[$num],$exist_id) == false)
        {
                $exist_ac[$exist_num] = $data[2];
                $exist_id[$exist_num] = $data[3];
                $exist_num++;
        }


	
}
	if($check == 0)
	{
		$error[$error_num]=$nomatch[$num];
		$error_num++;
	}


}
mysql_close($link);
if($_POST['count'] == null)//如果是第一次按search
{
	for($m=0 ; $m<$enter_nospace_num ; $m++)
		$level[$m]=0;
}
 //echo join("\", \"", $enter_nospace);
 //echo join("\", \"", $level);
?>
<meta name="description" content="[An example of getting started with Cytoscape.js]" />
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<meta charset=utf-8 />
<title>Cytoscape.js initialisation</title>
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
  background-image : url('http://140.138.150.147/~s993340/pathway/B_Cell_Receptor_Signaling.png');
  background-repeat:no-repeat;
}

#cy {
	height: 950px;
	width: 1600px;
	position: absolute;
	left: 0px;
	top: 0px;
}

#note1 {
	width: 100%;
	height: 20%;
	position: absolute;
	background-color: #f0f0f0;
	top: 951px;
	left: 0px;
}
#fun{
	width: 10%;
	height: 800px;
	position: absolute;
	left: 0px;
}
</style>
<style type="text/css">
<!--
#apDiv1 {
	position:absolute;
	width:96px;
	height:82px;
	z-index:1;
	left: -119px;
	top: 1px;
}
-->
</style>

<style>
body {
    font-family: 'Lucida Grande', 'Helvetica', sans-serif;
}

.note {
    background-color: rgb(255, 240, 70);
	z-index:9999;
    height: 300px;
    padding: 10px;
    position: absolute;
    width: 650px;
	overflow-y:auto;
    -webkit-box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.5);
}

.note:hover .closebutton {
    display: block;
}

.closebutton {
    display: block;
    background-image: url(deleteButton.png);
    height: 30px;
    position:relative;
    left: 1px;
    top: 1px;
    width: 30px;
}

.closebutton:active {
    background-image: url(deleteButtonPressed.png);
}

.edit {
    outline: none;
}


</style>


<script>
var db = null;

try {
    if (window.openDatabase) {
        db = openDatabase("NoteTest", "1.0", "HTML5 Database API example", 200000);
        if (!db)
            alert("Failed to open the database on disk.  This is probably because the version was bad or there is not enough space left in this domain's quota");
    } else
        alert("Couldn't open the database.  Please try with a WebKit nightly with this feature enabled");
} catch(err) {
    db = null;
    alert("Couldn't open the database.  Please try with a WebKit nightly with this feature enabled");
}

var clickpm = new Array(2);
var captured = null;
var highestZ = 0;
var highestId = 0;

function Note(target1)
{
    var self = this;
    //var fso = new ActiveXObject("Scripting.FileSystemObject");
    //var url=fso.OpenTextFile("7851759.txt", ForReading);
    //var txt=f.ReadAll();
    var target=target1.trim();
	alert(target);
    
    var x="http://140.138.144.141/~cytoscape/pubmed/" + target.trim() +".txt";
    var APIurl=x;
     
    

	
    var note = document.createElement('div');
    note.id = target.trim();
	note.className = 'note';
    note.addEventListener('mousedown', function(e) { return self.onMouseDown(e) }, false);
    note.addEventListener('click', function() { return self.onNoteClick() }, false);
    this.note = note;
	

    var close = document.createElement('div');
    close.className = 'closebutton';
    close.addEventListener('click', function(event) { return self.close(event,target) }, false);
    note.appendChild(close);

    $.ajax({
  url: APIurl,
  type: "GET",
  dataType: "text",
  success: function(data) {
	var edit = document.createElement('div');
	edit.id=target.trim();
	data=data.replace(/href=/g, "");
    edit.className = 'edit';
    edit.innerHTML= data;
    edit.setAttribute('contenteditable', false);
    edit.addEventListener('keyup', function() { return self.onKeyUp() }, false);
    note.appendChild(edit);
    this.editField = edit;

  }
});


    document.body.appendChild(note);
    return this;
}

Note.prototype = {
    get id()
    {
        if (!("_id" in this))
            this._id = 0;
        return this._id;
    },

    set id(x)
    {
        this._id = x;
    },

    get text()
    {
        return this.editField.innerHTML;
    },

    set text(x)
    {
        this.editField.innerHTML = x;
    },


    get left()
    {
        return this.note.style.left;
    },

    set left(x)
    {
        this.note.style.left = x;
    },

    get top()
    {
        return this.note.style.top;
    },

    set top(x)
    {
        this.note.style.top = x;
    },

    get zIndex()
    {
        return this.note.style.zIndex;
    },

    set zIndex(x)
    {
        this.note.style.zIndex = x;
    },
	


    close: function(event,target)
    {
        this.cancelPendingSave();

        var note = this;
       
        
        var duration = event.shiftKey ? 2 : .25;
        this.note.style.webkitTransition = '-webkit-transform ' + duration + 's ease-in, opacity ' + duration + 's ease-in';
        this.note.offsetTop; // Force style recalc
        this.note.style.webkitTransformOrigin = "0 0";
        this.note.style.webkitTransform = 'skew(30deg, 0deg) scale(0)';
        this.note.style.opacity = '0';
		clickpm[target.trim()] = false;
		

        var self = this;
        setTimeout(function() { document.body.removeChild(self.note) }, duration * 1160);
    },

    saveSoon: function()
    {
        this.cancelPendingSave();
        var self = this;
        this._saveTimer = setTimeout(function() { self.save() }, 200);
    },

    cancelPendingSave: function()
    {
        if (!("_saveTimer" in this))
            return;
        clearTimeout(this._saveTimer);
        delete this._saveTimer;
    },

    save: function()
    {
        this.cancelPendingSave();

        if ("dirty" in this) {
            this.timestamp = new Date().getTime();
            delete this.dirty;
        }

        var note = this;
        db.transaction(function (tx)
        {
            tx.executeSql("UPDATE WebKitStickyNotes SET note = ?, timestamp = ?, left = ?, top = ?, zindex = ? WHERE id = ?", [note.text, note.timestamp, note.left, note.top, note.zIndex, note.id]);
        });
    },

    saveAsNew: function()
    {
        this.timestamp = new Date().getTime();
        
        var note = this;
        db.transaction(function (tx) 
        {
            tx.executeSql("INSERT INTO WebKitStickyNotes (id, note, timestamp, left, top, zindex) VALUES (?, ?, ?, ?, ?, ?)", [note.id, note.text, note.timestamp, note.left, note.top, note.zIndex]);
        }); 
    },

    onMouseDown: function(e)
    {
        captured = this;
        this.startX = e.clientX - this.note.offsetLeft;
        this.startY = e.clientY - this.note.offsetTop;
        this.zIndex = ++highestZ;

        var self = this;
        if (!("mouseMoveHandler" in this)) {
            this.mouseMoveHandler = function(e) { return self.onMouseMove(e) }
            this.mouseUpHandler = function(e) { return self.onMouseUp(e) }
        }

        document.addEventListener('mousemove', this.mouseMoveHandler, true);
        document.addEventListener('mouseup', this.mouseUpHandler, true);

        return false;
    },

    onMouseMove: function(e)
    {
        if (this != captured)
            return true;
		
        this.left = e.clientX - this.startX + 'px';
		
		if (this.top<1160)
		{
        	
			this.top =1160+ 'px';
			//this.top = e.clientY - this.startY + 'px';//1160+ 'px';
			
		}
		else
		{
			
			this.top =1160+ 'px';
		
			
		}
			
        return false;
    },

    onMouseUp: function(e)
    {
        document.removeEventListener('mousemove', this.mouseMoveHandler, true);
        document.removeEventListener('mouseup', this.mouseUpHandler, true);

        this.save();
        return false;
    },

    onNoteClick: function(e)
    {
        this.editField.focus();
        getSelection().collapseToEnd();
    },

    onKeyUp: function()
    {
        this.dirty = true;
        this.saveSoon();
    },
}

function loaded()
{
    db.transaction(function(tx) {
        tx.executeSql("SELECT COUNT(*) FROM WebkitStickyNotes", [], function(result) {
            loadNotes();
        }, function(tx, error) {
            tx.executeSql("CREATE TABLE WebKitStickyNotes (id REAL UNIQUE, note TEXT, timestamp REAL, left TEXT, top TEXT, zindex REAL)", [], function(result) { 
                loadNotes(); 
            });
        });
    });
}

function loadNotes()
{
    db.transaction(function(tx) {
        tx.executeSql("SELECT id, note, timestamp, left, top, zindex FROM WebKitStickyNotes", [], function(tx, result) {
            for (var i = 0; i < result.rows.length; ++i) {
                var row = result.rows.item(i);
                var note = new Note();
                note.id = row['id'];
                note.text = row['note'];
                note.timestamp = row['timestamp'];
                note.left = row['left'];
                note.top = row['top'];
                note.zIndex = row['zindex'];

                if (row['id'] > highestId)
                    highestId = row['id'];
                if (row['zindex'] > highestZ)
                    highestZ = row['zindex'];
            }

            if (!result.rows.length)
                newNote();
        }, function(tx, error) {
            alert('Failed to retrieve notes from database - ' + error.message);
            return;
        });
    });
}

function modifiedString(date)
{
    return 'Last Modified: ' + date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate() + ' ' + date.getHours() + ':' + date.getMinutes() + ':' + date.getSeconds();
}

function newNote(target)
{
   
    if( clickpm[target.trim()] == true )
	{
		return;
	}
	else
	{
		
    var x=event.clientX;
    var y=event.clientY;	
    var note = new Note(target.trim());
   
    note.timestamp = new Date().getTime();
    note.left = x+'px';
    note.top =1160+ 'px';
	
	
    note.zIndex = ++highestZ;
    //note.saveAsNew();
	clickpm[target.trim()]= true;
	
	}
}


if (db != null)
    addEventListener('load', loaded, false);
</script>



</head>
<body>
  <div id="cy"></div>
  <div id="fun">
<?
if($species == "human")
{
echo "<b>Cancer : {$cancer}</b><br><br>";
echo "<b>Change Cancer:</b>";
echo "<form action ='network_pathway.php' method='GET'>";
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
echo "<input type='hidden' id='path' name='path' value=''/>";
echo "<input type='hidden' id='species' name='species' value=''/>";
echo "<input type='submit' id='cc' name='cc' value='Search' style='VISIBILITY: hidden'/>";
echo "</form>";
}
?>


</br>
<FORM NAME="my_form" METHOD=POST TARGET>
    <INPUT TYPE="checkbox"  NAME="check1" onclick="checkbox(this)" checked value="interaction"><b>Interaction<P> 
    <INPUT TYPE="checkbox"  NAME="check2" onclick="checkbox(this)" checked value="phos">Phosphorylation</b><P>
 </FORM>
</br>
<form>
<input type="button" id="delete" name="Delete node or edge" value="Delete node or edge" onClick="Delete()"/>
</form>
  </div>
  <div id="note1">
  <p>Click nodes or edges.</p>
  

<script>
var path = "<?echo $path;?>";
var pathway= "<?echo $pathway_name;?>";
var count= <?echo $i;?>;//所有interaction的數量
//var line = ["<?php echo join("\", \"", $line); ?>"];
var ac1 = ["<?php echo join("\", \"", $ac1); ?>"];
var id1 = ["<?php echo join("\", \"", $id1); ?>"];
var ac2 = ["<?php echo join("\", \"", $ac2); ?>"];
var id2 = ["<?php echo join("\", \"", $id2); ?>"];
var resource = ["<?php echo join("\", \"", $resource); ?>"];
var pi = ["<?php echo join("\", \"", $pi); ?>"];
//var exist_ac =  ["<?php echo join("\", \"", $exist_ac); ?>"];
//var exist_id =  ["<?php echo join("\", \"", $exist_id); ?>"];
//var exist_num = <?echo $exist_num;?>;
//var error = ["<?php echo join("\", \"", $error); ?>"];
//var error_num = <?echo $error_num;?>;
//var level = ["<?php echo join("\", \"", $level); ?>"];
//var enter_nospace = ["<?php echo join("\", \"", $enter_nospace); ?>"];
var protein =  ["<?php echo join("\", \"", $protein); ?>"];
var protein_ac = ["<?php echo join("\", \"", $protein_ac); ?>"];
var protein_id = ["<?php echo join("\", \"", $protein_id); ?>"];
var position_x = ["<?php echo join("\", \"", $x); ?>"];
var position_y = ["<?php echo join("\", \"", $y); ?>"];
var gene_name_pathway = ["<?php echo join("\", \"", $gene_name_pathway); ?>"];
var post_ac = ["<?php echo join("\", \"", $post_ac); ?>"];
var post_id = ["<?php echo join("\", \"", $post_id); ?>"];
var kinase = ["<?php echo join("\", \"", $kinase); ?>"];
var kinase_id = ["<?php echo join("\", \"", $kinase_id); ?>"];
var kinase_ac = ["<?php echo join("\", \"", $kinase_ac); ?>"];
var kinase_des = ["<?php echo join("\", \"", $kinase_des); ?>"];
var sub_kinase = ["<?php echo join("\", \"", $sub_kinase); ?>"];
var sub_name = ["<?php echo join("\", \"", $sub_name); ?>"];
var sub_id = ["<?php echo join("\", \"", $sub_id); ?>"];
var sub_ac = ["<?php echo join("\", \"", $sub_ac); ?>"];
var sub_des = ["<?php echo join("\", \"", $sub_des); ?>"];
var sub_site = ["<?php echo join("\", \"", $sub_site); ?>"];
var sub_pubmed = ["<?php echo join("\", \"", $sub_pubmed); ?>"];
var exp_kinase = ["<?php echo join("\", \"", $exp_kinase); ?>"];
var exp_value = ["<?php echo join("\", \"", $exp_value); ?>"];
var exp_id = ["<?php echo join("\", \"", $exp_id); ?>"];
var gene_name = ["<?php echo join("\", \"", $gene_name); ?>"];
var gene_id = ["<?php echo join("\", \"", $gene_id); ?>"];
var color_id = ["<?php echo join("\", \"", $color_id); ?>"];
var color_gene = ["<?php echo join("\", \"", $color_gene); ?>"];
var species = "<?php echo $species; ?>";
var W= <?echo $W;?>;
var H= <?echo $H;?>;
var selected = new Array();
var newnode=0;
var cancer = "<?echo $cancer;?>";
var url="url('pathway/"+pathway+".png')"
$('body').css("background-image",url);//動態更動背景圖
$('#cy').css({
	"width":  W+200+"px",
	"height": H+"px"
});
$('#fun').css({
        "left":  W+201+"px",
        "height": H+"px"
});

$("#note1").css("top",H+1+"px");

$('#cy').cytoscape({
  style: cytoscape.stylesheet()
	.selector('node')
      .css({
        'content': 'data(name)',
        'text-valign': 'center',
        'color': 'white',
	'border-color': 'black',
        'border-width': 1,
		//'background-color':'#4169e1',
	'background-color':'#ffffff',
        'text-outline-width': 2,
        'text-outline-color': '#888',
		'width': 30,
		'height': 30
      })
    .selector('edge')
      .css({
	'width':2,
	'line-color':'orange',
	 'opacity': 0.0
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
        'opacity': 0.25,
	'text-opacity': 0.0,
	'shape' : 'rectangle',
	'width': 46,
        'height': 17,
	'background-color':'#ffffff' /* Nodes On Pathway */
      })
    .selector('.faded')
      .css({
        'opacity': 0.8,
      })
    .selector('.faded_node')
      .css({
        'opacity': 0.25,
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
	if(protein[0] != "")
		{
			for(var i=0 ; i < protein.length ; i++)//把有match到pathway的protein畫到對應的地方
			{
				if(cy.elements("node[x = "+position_x[i]+"][y = "+position_y[i]+"]").data('id') == undefined)				     {	
					var tmp=cy.add({//加上一個特有的背景
						group: "nodes",
						data:{background:"true" , phos_num: 0},
						classes: 'background',
						selectable: false,
						grabbable: false
						
					});
				
					var tmp1=cy.add({
						group: "nodes",
						data: {parent:tmp.data('id'), name: protein_id[i] , uniprotac: protein[i] , uniprotid: protein_id[i] , Iskinase:'false', shouldhide: 'true' , value:"Null",right_click: "false" ,x: position_x[i] , y: position_y[i] , gene: gene_name_pathway[i]},
						position: { x: parseInt(position_x[i],10), y: parseInt(position_y[i],10) },
					locked:true,
					classes:'sub_pathway'
					});
					tmp.data('host',tmp1.data('id'));
				}
				else
				{
					var node = cy.elements("node[x = "+position_x[i]+"][y = "+position_y[i]+"]");
					var node_ac = node.data('uniprotac');
					var node_id = node.data('uniprotid');
					var node_gene = node.data('gene');
					if(node_ac.match(protein[i]) != protein[i])
					{
						node_ac = node_ac + " ; " + protein[i];
						node_id = node_id + " ; " + protein_id[i];
						node.data('uniprotac',node_ac);
						node.data('uniprotid',node_id);
					}
					if(node_gene.match(gene_name_pathway[i]) != gene_name_pathway[i])
					{
						node_gene = node_gene + " ; " + gene_name_pathway[i];
						node.data('gene',node_gene);
					}
				}
			}
		}
	/*write gene which not in pathway*/
	
	var x_tmp= W+30;
	var y_tmp= 60;
	if(post_id[0] != "")
	{
		for(var i=0 ; i <post_id.length ; i++)
		{
			if(cy.nodes("[name='"+gene_name[i]+"']").data('id') == undefined)
			{
				var tmp=cy.add({//加上一個特有的背景
                                                group: "nodes",
                                                data:{background:"true" , phos_num: 0 ,out_of_pathway:"true"},
                                                classes: 'background',
                                                selectable: false,
                                                grabbable: false

                                	});

                                	var tmp1=cy.add({
                                                group: "nodes",
                                                data: {parent:tmp.data('id'), name: gene_name[i] , uniprotac: post_ac[i] , uniprotid: post_id[i] , Iskinase:'false', shouldhide: 'true' , value:"Null", right_click: "false" , gene: gene_name[i]},
                                                position: { x: x_tmp, y: y_tmp }
                                	});
                                	tmp.data('host',tmp1.data('id'));
				tmp1.css('background-color','#4169e1');
		
				y_tmp+=50;
				if(y_tmp >= H-20)
				{
					y_tmp=60;
					x_tmp = x_tmp+70;
				}
				newnode++;
			}
			else
			{
				var node = cy.nodes("[name='"+gene_name[i]+"']");
				var AC = node.data('uniprotac');
				var ID = node.data('uniprotid');
				if(AC.match(post_ac[i]) == null)
				{
					AC = AC + " ; " + post_ac[i];
					ID = ID + " ; " + post_id[i];
					node.data('uniprotac',AC);
					node.data('uniprotid',ID);
				}
			}
		}
	}
	/*find kinase and set gene name*/
	
        for(var i=0 ; i < kinase_id.length ; i++)
        {
        	var node = cy.nodes("[uniprotid*='"+kinase_id[i]+"']");
                node.data('name',kinase[i]);
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
                node.data('shouldhide','false');
                node.data('kinase_des',kinase_des[i]);
		node.removeClass('sub_pathway');
		var back = cy.nodes("[host='"+node.data('id')+"']");
                        back.data('sub_pathway',"false");
         }
	cy.nodes("[Iskinase='true']").css('shape','hexagon');

	/*phosphrylation*/
		
	if(sub_kinase[0]!="")
	{
		for(var i=0 ; i < sub_id.length ; i++)
		{
			var thisnode=cy.nodes("[uniprotid *= '"+sub_id[i]+"']");//取得在pathway上的node
			//thisnode.data('gene',sub_name[i]);
			thisnode.data('sub_des',sub_des[i]);
			thisnode.data('sub_site',sub_site[i]);
			thisnode.data('sub',"true");
			if(thisnode.data('Iskinase') != "true")
			{
				//thisnode.data('gene',sub_name[i]);
				var back = cy.nodes("[host='"+thisnode.data('id')+"']");
				back.data('sub_pathway',"true");
			}
			//thisnode.data('sub_pathway',"true");
			var n= cy.nodes("[gene *= '"+sub_kinase[i]+"']");//取得這個phos的kinase
			thisnode.selectify();
			cy.add({
					group: "edges",
					data: {source: n.data("id"),target: thisnode.data('id'),edge_for_phos:"true" , kinase : sub_kinase[i] , substrate : sub_name[i] , pubmedid: sub_pubmed[i]},
					classes: 'phos_edge'
				});
			var n_tmp = cy.nodes("[host='"+thisnode.data('id')+"']");//取得背景
			var phos_number=n_tmp.data('phos_num');
			phos_number++;
			n_tmp.data('phos_num',phos_number);
			thisnode.data('shouldhide','false');

			
		}
	}
	/*find out input gene and set color*/

	cy.nodes("[out_of_pathway='true']").data('sub_pathway','false');
	for(var i=0 ; i < color_gene.length ; i++)
        {
                var node = cy.nodes("[gene*='"+color_gene[i]+"']");
                node.css('background-color','#4169e1');
                //alert(node.data("id"))

        }

	/*interaction*/
	
	for(var i =0 ; i < count ; i++)
	{
		var n1 = cy.nodes("[uniprotac*="+"'"+ac1[i]+"'"+"]");
		var n2 = cy.nodes("[uniprotac*="+"'"+ac2[i]+"'"+"]");
		for(var j=0 ; j < n1.length ; j++)
			for(var k=0 ; k < n2.length ; k++)
			{
				var n3 = n1[j];
				var n4 = n2[k];
				if(n3.data("id")!=n4.data("id") && n3.data("name")==n4.data("name")){}
				else{
					if(cy.$("#"+n3.data("id")+n4.data("id")).data("id")==undefined && cy.$("#"+n4.data("id")+n3.data("id")).data("id")==undefined)
					cy.add({
              					group: "edges",
            					data: { id :n3.data("id")+n4.data("id") , source:n3.data("id"), target:n4.data("id"), pubmedid:pi[i] , resource:resource[i], PPI:id1[i]+" and "+id2[i] ,edge_for_inter: "true"}
             	 			});
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
	/*------------------*/
	/*set background image*/
	
		var n1 = cy.nodes("[sub_pathway='true']");
		var n2 = cy.nodes("[background='true']").not(n1);
		n1.nodes("[phos_num='0']").css("background-opacity",0);
		n1.nodes("[phos_num>='1']").css("background-image","http://140.138.144.141/~cytoscape/phos/phos_pathway/1.png");
		//n1.nodes("[phos_num='2']").css("background-image","http://140.138.144.141/~cytoscape/phos/phos_pathway/2.png");
		//n1.nodes("[phos_num='3']").css("background-image","http://140.138.144.141/~cytoscape/phos/phos_pathway/3.png");
		//n1.nodes("[phos_num='4']").css("background-image","http://140.138.144.141/~cytoscape/phos/phos_pathway/4.png");
		//n1.nodes("[phos_num='5']").css("background-image","http://140.138.144.141/~cytoscape/phos/phos_pathway/5.png");
		//n1.nodes("[phos_num>='6']").css("background-image","http://140.138.144.141/~cytoscape/phos/phos_pathway/6.png");
		n2.nodes("[phos_num='0']").css("background-opacity",0);
                n2.nodes("[phos_num>='1']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/1.png");
                //n2.nodes("[phos_num='2']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/2.png");
                //n2.nodes("[phos_num='3']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/3.png");
                //n2.nodes("[phos_num='4']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/4.png");
                //n2.nodes("[phos_num='5']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/5.png");
                //n2.nodes("[phos_num>='6']").css("background-image","http://140.138.144.141/~cytoscape/s993340/phos/6.png");
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
				if(value >-0.5 && value <=0.5)
					nodes[i].css('background-color','#000000');
				else if(value >0.5 && value <=0.95)
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
				else if(value > -0.95 && value <=-0.5)
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
		/*SET WIDTH*/
		
		var WIDTH=Math.floor((newnode/14)*80+W);
		if(WIDTH >  W+200)
		{
			$('#cy').css({
        		"width":  WIDTH+"px",
			});
		} 
		/*------------*/
	/*event function*/
	function handle_click(event) {
                         //var target = event.target;
                clear();
                if(event.cyTarget.isNode()==true && event.cyTarget.data('sub') == "true"){
				print("Gene name : "+event.cyTarget.data('gene'));
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
				print("Protein Description : "+event.cyTarget.data("sub_des"));
				print("Number of kinase-specific phosphorylation sites : "+event.cyTarget.data("sub_site"));
				if(species == "human")
					print("Expression value : "+event.cyTarget.data("value"));

                        }
		if(event.cyTarget.isNode()==true && event.cyTarget.data('Iskinase') == "true" && event.cyTarget.data('sub') != "true"){
                                print("Gene name : "+event.cyTarget.data('gene'));
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
                                print("Protein Description : "+event.cyTarget.data("kinase_des"));
				if(species == "human")
                                	print("Expression value : "+event.cyTarget.data("value"));

                        }
		if(event.cyTarget.isNode()==true && event.cyTarget.data('Iskinase') != "true" && event.cyTarget.data('sub') != "true"){
				print("Gene name : "+event.cyTarget.data('gene'));
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
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
			var cut = KB.split(";");
                        var tmp = "";
                        //document.getElementById("note").innerHTML += "<p>"+msg;
                                                //newNote(event.cyTarget.innerHTML.trim())20482850
                        for(var j=0 ; j < cut.length ; j++)
                                                {
                                var a = $.trim(cut[j]);

                               // tmp += "<font onclick=newNote('"+a+"') style=color:blue;>" + cut[j] +"</font>" + ";";
                                                                tmp +="<a href=http://www.uniprot.org/uniprot/"+a+"  target=_blank  style=text-decoration:none;color:blue;>"+a +"</a>"+";";
                                                }
				document.getElementById("note1").innerHTML += "<p>" + msg + tmp +"</p>";
                    }
	function printPumed(msg,PUMED)
                    {
                        var cut = PUMED.split(";");
                        var tmp = "";
                        //document.getElementById("note").innerHTML += "<p>"+msg;
						//newNote(event.cyTarget.innerHTML.trim())20482850
                        for(var j=0 ; j < cut.length ; j++)
						{
                                var a = $.trim(cut[j]);

                               // tmp += "<font onclick=newNote('"+a+"') style=color:blue;>" + cut[j] +"</font>" + ";";
								tmp +="<a href=http://www.ncbi.nlm.nih.gov/pubmed/?term="+a+"  target=_blank  style=text-decoration:none;color:blue;>"+a +"</a>"+";";
						}
                        document.getElementById("note1").innerHTML += "<p>" + msg + tmp +"</p>";
                    }
	cy.zoomingEnabled(false);
	cy.panningEnabled(false);
	 cy.on('tap', 'node', function(e){
        if(selected.indexOf(e.cyTarget.data("id")) == -1)//如果被點到的node沒被點過
        {
                selected.push(e.cyTarget.data("id"))//放入selected陣列
                handle_click(e);//在下方顯示資訊
                var node = e.cyTarget;
                var neighborhood = node.neighborhood().add(node);
                neighborhood.edges().addClass('faded');//點到的那一點以及他的鄰居的邊顯示出來
                cy.nodes().addClass('faded_node');//把所有點隱藏
                for(var d=0 ; d < selected.length ; d++)//把曾經點過的node的鄰居都去除隱藏
                {
                        node = cy.nodes("[id="+"'"+selected[d]+"'"+"]");
                        neighborhood = node.neighborhood().add(node);
                        neighborhood.removeClass('faded_node');
                }
		cy.nodes("[background='true']").removeClass('faded_node');
        }
        else if(selected.indexOf(e.cyTarget.data("id")) != -1)//如果所點到的node已經在selected中，表示同一個點被點了第二次
        {
                var index = selected.indexOf(e.cyTarget.data("id"));
                selected.splice(index,1)//把select中存在的id刪除
                var node = e.cyTarget;
                var neighborhood = node.neighborhood().add(node);
                neighborhood.edges().removeClass('faded');//把顯示的邊隱藏
                neighborhood.nodes().addClass('faded_node');//把顯示的node隱藏
        }
    });
    cy.on('tap', 'edge', function(e){
        handle_click(e);
    });
    cy.on('tap', function(e){
      if( e.cyTarget === cy ){
        cy.elements().edges().removeClass('faded');//去除所有屬性
        cy.elements().nodes().removeClass('faded_node');//去除所有屬性
        var leng=selected.length;//把selected中所有內容刪除
        selected.splice(0,leng);
      }

    });

  }
});

var cy = $("#cy").cytoscape("get");
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
function changecancer(theselect)
{
	
	var a = document.getElementById("cancer");
	a.value = theselect.value;
	var b = document.getElementById("path");
	b.value=path;
	var c = document.getElementById("species");
	c.value=species; 
	var submit = document.getElementById("cc");
        submit.click();
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
