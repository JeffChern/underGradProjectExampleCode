<!DOCTYPE html>
<html>
<!--
  Created using jsbin.com
  Source can be edited via http://jsbin.com/iteloj/1/edit
-->
<head>
<?php
$x=array();//存txt檔的x位置
$y=array();//存txt檔的y位置
$protein=array();
$protein_nomatch=array();
$protein_nomatch_ac=array();
$protein_nomatch_id=array();
$protein_ac=array();
$protein_id=array();
$input=explode(",",$_POST['path']);
$pathway_name=$input[0];
$pathway_file="pathway/".$input[0].".txt";
$file = fopen($pathway_file, "r");
$protein_tmp=array();
$x_tmp=array();
$y_tmp=array();
$tmp=explode(" ",fgets($file));
$W=$tmp[0];
$H=$tmp[1];
while(!feof($file))
{
  $tmp=explode(" ",fgets($file));
  $protein_tmp[count($protein_tmp)]=trim($tmp[0]);
  $x_tmp[count($x_tmp)]=$tmp[1];
  $y_tmp[count($y_tmp)]=trim($tmp[2]);
}
fclose($file);
for($k=1 ; $k < count($input) ; $k++)
	if(in_array($input[$k],$protein)==false)
	{
		for($m=0 ; $m < count($protein_tmp) ; $m++)
		{
			if($input[$k] != null && $protein_tmp[$m] == $input[$k] && in_array($input[$k],$protein) == false)
			{
				$protein_tmp1[count($protein_tmp1)] = $protein_tmp[$m];
				//$x[count($x)] = intval($x_tmp[$m]);
				//$y[count($y)] = intval($y_tmp[$m]);
			}
		}
		if($input[$k] != null && in_array($input[$k],$protein_nomatch)==false && in_array($input[$k],$protein)==false)
			$protein_nomatch[count($protein_nomatch)]=$input[$k];
	}
$MySQL_IP="140.138.144.141";
$User="cytoscape";
$Pw="cytoscape";
$link = mysql_connect($MYSQL_IP,$User,$Pw);
mysql_select_db("cytoscape"); 
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
for($i=0 ; $i < count($protein_tmp1) ; $i++)
{
	$sql = "select local,AC from Mix_kegg where map_ID = '$input[0]' and AC = '$protein_tmp1[$i]'";
	$result = mysql_query($sql) or die("Query failed : " . mysql_error());
	$num_rows = mysql_num_rows($result);
	if($num_rows > 0)
	{
		$data = mysql_fetch_array($result);
		
		//	echo $count." ";
			$tmp = explode(",",$data[0]);
			$x[count($x)] =intval(($tmp[0]+$tmp[2])/2);
  			$y[count($y)] =intval(($tmp[1]+$tmp[3])/2);
			$protein[count($protein)] = $data[1];
	}
}

if($_POST['count'] == null)//如果是第一次按search，近來這邊，line存的是有一些空白等不需要的東西
{
	$line=$protein;
	$item=count($line);
	//$line=array_unique($line);
	for($k=0 ; $k<$item ; $k++)//找出protein在資料庫裡的ac id
	{
        	$query = mysql_query("select ACa,IDa from done_MIX where ACa = '$line[$k]' or IDa = '$line[$k]'");
        	$numrows=mysql_num_rows($query);
        	$data=mysql_fetch_array($query);
        	$protein_ac[count($protein_ac)] = $data[0];
                $protein_id[count($protein_id)] = $data[1];
		$query = mysql_query("select ACb,IDb from done_MIX where ACb = '$line[$k]' or IDb = '$line[$k]'");
                $numrows=mysql_num_rows($query);
                $data=mysql_fetch_array($query);
		if(in_array($data[0],$protein_ac)==false)//這行判斷若是false表示在ACa或IDb裡沒有找到資料
		{
                	$protein_ac[count($protein_ac)] = $data[0];
                	$protein_id[count($protein_id)] = $data[1];
		}
	}
	$line=$protein_nomatch;
	$item=count($line);
        //$line=array_unique($line);
        for($k=0 ; $k<$item ; $k++)//找出protein在資料庫裡的ac id
        {
                $query = mysql_query("select ACa,IDa from done_MIX where ACa = '$line[$k]' or IDa = '$line[$k]'");
                $numrows=mysql_num_rows($query);
                $data=mysql_fetch_array($query);
                $protein_nomatch_ac[count($protein_nomatch_ac)] = $data[0];
                $protein_nomatch_id[count($protein_nomatch_id)] = $data[1];
                $query = mysql_query("select ACb,IDb from done_MIX where ACb = '$line[$k]' or IDb = '$line[$k]'");
                $numrows=mysql_num_rows($query);
                $data=mysql_fetch_array($query);
                if(in_array($data[0],$protein_nomatch_ac)==false)//這行判斷若是false表示在ACa或IDb裡沒有找到資料
                {
                        $protein_nomatch_ac[count($protein_nomatch_ac)] = $data[0];
                        $protein_nomatch_id[count($protein_nomatch_id)] = $data[1];
                }
        }
	$line=array_merge($protein,$protein_nomatch);
//	$line=array_unique($line);
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
	if($_POST['count'] == null)//如果是第一次按search
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
	z-index:1;
	left: 0px;
	top: 0px;
}
#note {
	width: 100%;
	height: 20%;
	position: absolute;
	background-color: #f0f0f0;
	top: 951px;
	left: 0px;
}
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
		this.top=e.clientY - this.startY + 'px';;
		

			
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
</head>
<body>
  <div id="cy"></div>
  <div id="note">
  <p>Click nodes or edges.</p>
  </div>
<script>
var pathway= "<?echo $input[0];?>";
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
var protein_nomatch =  ["<?php echo join("\", \"", $protein_nomatch); ?>"];
var protein_nomatch_ac =  ["<?php echo join("\", \"", $protein_nomatch_ac); ?>"];
var protein_nomatch_id =  ["<?php echo join("\", \"", $protein_nomatch_id); ?>"];
var protein_ac = ["<?php echo join("\", \"", $protein_ac); ?>"];
var protein_id = ["<?php echo join("\", \"", $protein_id); ?>"];
var position_x = ["<?php echo join("\", \"", $x); ?>"];
var position_y = ["<?php echo join("\", \"", $y); ?>"];
var selected = new Array();
var H = <?echo $H;?>;
var W = <?echo $W;?>;
var url="url('http://140.138.144.141/~cytoscape/pathway/"+pathway+".png')"
$('body').css("background-image",url);//動態更動背景圖
$('#cy').css({
        "width":  W+500+"px",
        "height": H+"px"
});
$("#note").css("top",H+1+"px");
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
	'line-color':'#8b4513',
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
	if(protein[0] != "")
	{
		for(var i=0 ; i < protein.length ; i++)//把有match到pathway的protein畫到對應的地方
		{
			cy.add({
    				group: "nodes",
    				data: { name: protein_id[i] , uniprotac: protein_ac[i] , uniprotid: protein_id[i]},
    				position: { x: parseInt(position_x[i],10), y: parseInt(position_y[i],10) },
				locked:true
			});
		}
	}
	if(protein_nomatch[0] != "")
	{
		var x_tmp= W+30;
		var y_tmp= 20;
		for(var i=0 ; i < protein_nomatch.length ; i++)
		{
			cy.add({
                                group: "nodes",
                                data: { name: protein_nomatch_id[i] , uniprotac: protein_nomatch_ac[i] , uniprotid: protein_nomatch_id[i], nomatch: "true",alone:"true"},
                                position: { x: x_tmp, y: y_tmp },
				
                        });
			//x_tmp+=20;
			y_tmp+=50;
			if(y_tmp >= H-20)
			{
				y_tmp = 20;
				x_tmp+=70;
			}
		}
	}
	cy.nodes("[nomatch='true']").css("background-color","#1e90ff");
	
	for(var i =0 ; i < count ; i++)
	{
		var n1 = cy.nodes("[uniprotac="+"'"+ac1[i]+"'"+"]");
		var n2 = cy.nodes("[uniprotac="+"'"+ac2[i]+"'"+"]");
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
                				data: { id :n3.data("id")+n4.data("id") , source:n3.data("id"), target:n4.data("id"), pubmedid:pi[i] , resource:resource[i], PPI:n3.data("uniprotac")+" and "+n4.data("uniprotac") }
               	 			});
					n3.data("alone","false");
					n4.data("alone","false");
				}

			}
	}
	cy.nodes("[alone='true']").css("background-color","#a9a9a9");
	function handle_click(event) {
                         //var target = event.target;
                clear();
                if(event.cyTarget.isNode()==true){
                                printNodes("UniprotAC : ",event.cyTarget.data("uniprotac"));
                                printNodes("UniprotID : ",event.cyTarget.data("uniprotid"));
                                //priint("Level :"+event.target.data.level);

                        }
                      if(event.cyTarget.isEdge()==true){
								print("Interaction between : "+event.cyTarget.data("PPI"));
                                print("Resource : "+event.cyTarget.data("resource"));
                                printPumed("PubmedID : ",event.cyTarget.data("pubmedid"));
						}
                    }

	function clear() {
                        document.getElementById("note").innerHTML = "";
                    }

                    function print(msg) {
                        document.getElementById("note").innerHTML += "<p>" + msg + "</p>";
                    }
                    function printNodes(msg,KB){
                        document.getElementById("note").innerHTML += "<p>" +msg+"<a href=http://www.uniprot.org/uniprot/"+KB+" target=_blank  style=text-decoration:none;color:blue;>"+ KB +"</a>"+ "</p>";
                    }
					function printPumed(msg,PUMED)
                    {
                        var cut = PUMED.split(";");
                        var tmp = "";
                        //document.getElementById("note").innerHTML += "<p>"+msg;
                         for(var j=0 ; j < cut.length ; j++)
						{
                                var a = $.trim(cut[j]);

                               tmp += "<font onclick=newNote('"+a+"') style=color:blue;>" + cut[j] +"</font>" + ";";
								//tmp +="<a href=http://140.138.144.141/~cytoscape/trans.php onclick=window.open('http://www.ncbi.nlm.nih.gov/pubmed/?term="+a+"','gg',config='height=500,width=500')  target=_blank  style=text-decoration:none;color:blue;>"+a +"</a>"+";";
						}
                        document.getElementById("note").innerHTML += "<p>" + msg + tmp +"</p>";
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
</script>
</body>
</html>
