<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
  <title>PhosNet</title>
  <meta name="description" content="website description" />
  <meta name="keywords" content="website keywords, website keywords" />
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
  <link rel="stylesheet" type="text/css" href="style/style.css" />
  <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
 <?php
set_time_limit(0);
$MySQL_IP="-----";
$User="-----";
$Pw="-----";
$link = mysql_connect($MYSQL_IP,$User,$Pw);
mysql_select_db("-----"); 
$x=$_POST['ac'];
$ac1=array();
$id1=array();
$ac2=array();
$id2=array();
$resource=array();
$pi=array();
$exist_ac=array();//?????????(ac)
$exist_id=array();//?????????(id)
$exist_num=0;//???????????
$error=array();
$error_num=0;
$extand_line=array();//extant????(???)
$extand_num=0;//extand???????
$level=array();//??level(?? array)
$nomatch=array();//????match????
$nomatch_num=0;//????match????
$enter=array();//
$enter_ID=array();
$enter_num=0;
$enter_nospace=array();
$enter_nospace_num=0;
$i=0;
$j=0;

if($_POST['count'] == null)//???????search,????,line???????????????
{
	$line=explode("\r\n",$x);
	$item=count($line);
}
else//?????extand?????
{
	 $line=explode(" ",$_POST['count']);//????????
	 $item=count($line);
	 $level=explode(" ",$_POST['level']);//??????level
	 $extend_ac=$_POST['thisac'];//?????extand
	 $extand_level=$_POST['thislevel'];
}
$query = mysql_query("select ACa,ACb from done_MIX where ACa = '$extend_ac' or ACb = '$extend_ac'");
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
	if($_POST['count'] == null)//???????search
	{
		$query1=mysql_query("select ACa from done_MIX where IDa = '$line[$num]'");
		$query2=mysql_query("select ACb from done_MIX where IDb = '$line[$num]'");
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
$query = mysql_query("select ACa,IDa,ACb,IDb,resource,pubmedID from done_MIX where (ACa = '$line[$num]' and ACb = '$line[$num2]') or (IDa = '$line[$num]' and IDb = '$line[$num2]') or (ACa = '$line[$num]' and IDb = '$line[$num2]') or  (IDa = '$line[$num]' and ACb = '$line[$num2]')");
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
	$query = mysql_query("select ACa,IDa,ACb,IDb from done_MIX where ACa = '$nomatch[$num]' or ACb = '$nomatch[$num]' or IDa = '$nomatch[$num]'or IDb = '$nomatch[$num]'");
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
if($_POST['count'] == null)//???????search
{
	for($m=0 ; $m<$enter_nospace_num ; $m++)
		$level[$m]=0;
}
 //echo join("\", \"", $enter_nospace);
 //echo join("\", \"", $level);
?>
<style type="text/css">
<!--
#apDiv1 {
	position:absolute;
	width:96px;
	height:82px;
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

<script src="http://code.jquery.com/jquery-1.8.2.js"></script>

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
        setTimeout(function() { document.body.removeChild(self.note) }, duration * 1000);
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
		this.top = e.clientY - this.startY + 'px';
		
	
			
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
    var y=event.clientY-100;	
    var note = new Note(target.trim());
   
    note.timestamp = new Date().getTime();
    note.left = x+'px';
    note.top =y+ 'px';
	
	
    note.zIndex = ++highestZ;
    //note.saveAsNew();
	clickpm[target.trim()]= true;
	
	}
}


if (db != null)
    addEventListener('load', loaded, false);
</script>


 </script>

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
	width: 85%;
	float: left;
	position: absolute;
	z-index: 1;
}

#note1 {
	width: 100%;
	height: 20%;
	background-color: #f0f0f0;
	overflow: auto;
	float: left;
}

#fun{
	float: right;
	width: 10%;
	height: 800px;
	
}


</style>

</head>

<body>
  <div id="main">
    <div id="links"></div>
    <? include("top.html"); ?>
    <div id="site_content">
    <div id="cy"></div>
     <div id="fun"> <form>
<b>Layout :
<select name="layout" size="1" onchange="changed(this)">
  <option selected value="grid">Grid</option>
  <option value="arbor">Arbor</option>
  <option value="random">Random</option>
  <option value="breadthfirst">Breadthfirst </option>
  <option value="circle">Circle</option>
</select>
</b>
     </form>
<br/>
<form>
<b>Label : </b><select name="label" size="1" onChange="ChangeLabel(this)">
<option selected value="uniprotac">UniprotAC</option>
<option value="uniprotid">UniprotID</option>
</select>
</form>
<br/>
<form>
<input type="button" id="delete" name="Delete node or edge" value="Delete node or edge" onClick="Delete()"/>
</form>
<br/>
<form id="form" name="form" method="POST" action="network.php"> 
<input type="hidden" id="count" name="count" value="firsttime"/> 
<input type="hidden" id="extand_num" name="extand_num" value=0 />
<input type="hidden" id="level" name="level" value="non" />
<input type="hidden" id="thisac" name="thisac" value="thisac" />
<input type="hidden" id="thislevel" name="thislevel" value="thislevel" />
<input type="button" id="button1" name="extension" value="Extension" onClick="Extension()"/>
<input type="submit" id="button" name="extension" value="Search" style="VISIBILITY: hidden"/> 
</form>
<br/> 
<form>
<input type="button" id="nibor" name="nibor" value="Get Neighbors" onClick="Neighbors()"/>
</form>
<br/> 
<form>
<input type="button" id="fit" name="fit" value="Fit to screen" onClick="Fit()"/>
</form>
</div>
      <div id="note1">
        <p>Click nodes or edges.</p>
         <script>
	var count= <?echo $i;?>;
	var line = ["<?php echo join("\", \"", $line); ?>"];
	var line_num = <?echo $item;?>;
	var ac1 = ["<?php echo join("\", \"", $ac1); ?>"];
	var id1 = ["<?php echo join("\", \"", $id1); ?>"];
	var ac2 = ["<?php echo join("\", \"", $ac2); ?>"];
	var id2 = ["<?php echo join("\", \"", $id2); ?>"];
	var resource = ["<?php echo join("\", \"", $resource); ?>"];
	var pi = ["<?php echo join("\", \"", $pi); ?>"];
	var enter = ["<?php echo join("\", \"", $enter); ?>"];
	var item = <?echo $enter_num;?>;
	var exist_ac =  ["<?php echo join("\", \"", $exist_ac); ?>"];
	var exist_id =  ["<?php echo join("\", \"", $exist_id); ?>"];
	var exist_num = <?echo $exist_num;?>;
	var error = ["<?php echo join("\", \"", $error); ?>"];
	var error_num = <?echo $error_num;?>;
	var extand_line =  ["<?php echo join("\", \"", $extand_line); ?>"];
	var extand_num = <?echo $extand_num;?>;
	var level = ["<?php echo join("\", \"", $level); ?>"];
	var enter_nospace = ["<?php echo join("\", \"", $enter_nospace); ?>"];
	var select;
$('#cy').cytoscape({
  style: cytoscape.stylesheet()
    .selector('node')
      .css({
        'content': 'data(name)',
        'text-valign': 'center',
        'color': 'white',
	'background-color':'#ff8c00',
        'text-outline-width': 2,
        'text-outline-color': '#888',
		'width': 25,
		'height': 25
      })
    .selector('edge')
      .css({
	'width':2,
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
    .selector('.faded')
      .css({
        'opacity': 0.8,
      })
    .selector('.faded_node')
      .css({
        'opacity': 0.25,
        'text-opacity': 0
      }),
  ready: function(){
    window.cy = this;
	function handle_click(event) {
                         //var target = event.target;
                clear();
                if(event.cyTarget.isNode()==true){
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
								print("Level :"+event.cyTarget.data("level"));

                        }
				if(event.cyTarget.isEdge()==true){
				print("Interaction between : "+event.cyTarget.data("PPI"));
                                print("Resource : "+event.cyTarget.data("resource"));
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
                        for(var j=0 ; j < cut.length ; j++)
						{
                                var a = $.trim(cut[j]);

                               tmp += "<font onclick=newNote('"+a+"') style=color:blue;>" + cut[j] +"</font>" + ";";
								//tmp +="<a href=http://140.138.144.141/~cytoscape/trans.php onclick=window.open('http://www.ncbi.nlm.nih.gov/pubmed/?term="+a+"','gg',config='height=500,width=500')  target=_blank  style=text-decoration:none;color:blue;>"+a +"</a>"+";";
						}
                        document.getElementById("note1").innerHTML += "<p>" + msg + tmp +"</p>";
						
                    }
					cy.on('tap', 'node', function(e){
						handle_click(e);//???????
						});
					cy.on('tap', 'edge', function(e){
						handle_click(e);
						});
					cy.on('tap', function(e){
							if( e.cyTarget === cy ){
							cy.elements().removeClass('faded_node');//??????
							select = undefined;
						  }
						  });

					cy.on('select',function(e){
						select = e.cyTarget.data("id");
					});
					
    }
});
var cy = $("#cy").cytoscape("get");
/*write node and edge*/
for(var i =0 ; i < count ; i++)
	{
		cy.add({
    				group: "nodes",
    				data: {id:ac1[i], name: ac1[i] , uniprotac: ac1[i] , uniprotid: id1[i]}
			});
		cy.add({
    				group: "nodes",
    				data: {id:ac2[i], name: ac2[i] , uniprotac: ac2[i] , uniprotid: id2[i]}
			});
			
			if(cy.$("#"+ac2[i]+ac1[i]).data("id")==undefined)
			{
					cy.add({
						group: "edges",
						data: { id :ac1[i]+ac2[i] , source:ac1[i], target:ac2[i], pubmedid:pi[i] , resource:resource[i], PPI:ac1[i] + " and "+ ac2[i] }
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
/*確實存在在資料庫但�'有interaction*/
for(var j=0 ; j < exist_num ; j++){
		cy.add({
    				group: "nodes",
    				data: {id:exist_ac[j], name: exist_ac[j] , uniprotac: exist_ac[j] , uniprotid: exist_id[j]}
			});
}
/*不存在於資料庫*/
for(var j=0 ; j < error_num ; j++){
		cy.add({
    				group: "nodes",
    				data: {id:error[j], name: error[j] , uniprotac:  "No match data in database", uniprotid: "No match data in database"}
			});
}
cy.layout({ name: 'grid' });
/*設定level*/
for(var j=0 ; j < enter_nospace.length ; j++)
{
	var n=cy.nodes("[id="+"'"+enter_nospace[j]+"'"+"]");
	n.data("level",level[j]);
}
/*設定level color*/
cy.nodes("[level="+"'1'"+"]").css("background-color","#00ff00")
cy.nodes("[level="+"'2'"+"]").css("background-color","#b0c4de")

/*更換layout*/
function changed(theselect){
				 cy.layout({
				 name: theselect.value,
				 fit: true
				 });
}
/*更換label*/
function ChangeLabel(theselect){
			var nodes = cy.nodes();
			if(theselect.value == "uniprotac"){
				for(var i = 0 ; i < nodes.length ; i++){
					var node = nodes[i];
					node.data("name",node.data("uniprotac"));
				}
			}
			else{
				for(var i = 0 ; i < nodes.length ; i++){
                                	var node = nodes[i];
                                	node.data("name",node.data("uniprotid"));
                                }
			}
		}
/*delete node or edge*/
function Delete()
{
	if(cy.$(':selected').data("id") == undefined)
                alert("Please select a node or edge")
	else
	if(confirm("Sure to delete?"))
		cy.$(':selected').remove();
}
/*extension*/
function Extension()
{
	if(cy.$(':selected').data("id") == undefined || cy.$(':selected').isEdge()==true)
			alert("Please select a node")
	else if(cy.$(':selected').length>1)
			alert("Please select only one node")
	else if(cy.$(':selected').data("uniprotac") == "No match data in database")
		alert("This node can not be extened")
	else if (cy.$(':selected').data("level") == "2")
		alert("achieved upper limit of extension")
	else
	if(confirm("It may take a lot of time, sure to extend?"))	
        {
			var nodes = cy.nodes();
            var all_line = nodes[0].data("id");
            var all_level = nodes[0].data("level");
            for(var k = 1 ; k < nodes.length ; k++)
            {
                     all_line = all_line +" "+ nodes[k].data("id");
                     all_level = all_level+" "+nodes[k].data("level");
            }
            var selectac = document.getElementById("thisac");
            selectac.value =cy.$(':selected').data("id");
            var selectlevel = document.getElementById("thislevel");
            selectlevel.value = cy.$(':selected').data("level");
            var te = document.getElementById("count");
            te.value = all_line;
            var tl = document.getElementById("level");
            tl.value = all_level;

            var submit = document.getElementById("button");
            submit.click();
        }
}
/*除了�"�居以外都隱藏*/
function Neighbors()
{
	if(cy.$(':selected').isEdge()==true)
		alert("Please select a node")
	else
	{
		var node = cy.$(':selected');
		var neighborhood = node.neighborhood().add(node);
		cy.elements().addClass('faded_node');
		neighborhood.removeClass('faded_node');
	}
}
function Fit()
{
	cy.fit();
}
  </script>   
        
      </div>
    </div>
  <? include("down.html"); ?>
  </div>
</body>
</html>
