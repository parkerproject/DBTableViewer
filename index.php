<?php
/*
Drale DBTableViewer v100123
Copyright 2009 http://www.drale.com
Darrell is not responsible for any abnormal behavior of this program

This file is part of Drale DBTableViewer.

Drale DBTableViewer is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Drale DBTableViewer is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Drale DBTableViewer.  If not, see <http://www.gnu.org/licenses/gpl.txt>.

drale.com is not responsible for any abnormal behavior of this program

version 100123:
rebuilt columnSortArrows()
included tableviewdemo dummy data

version 100120:
added mysql_real_escape_string
only allow ASC and DESC in $_GET['sort']

version 100119:
initial build
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<title>Database Table View</title> 
<link rel="stylesheet" type="text/css" href="reset.css" />
<link rel="stylesheet" type="text/css" href="typography.css" />
<link rel="stylesheet" type="text/css" href="style.css" />
</head> 
<body>
Download: <a href="./drale_dbtableview.zip">drale_dbtableview.zip</a> v100123
<?php

//DATABASE SETTINGS
$config['host'] = "";
$config['user'] = "";
$config['pass'] = "";
$config['database'] = "";
$config['table'] = "";
$config['nicefields'] = true; //true or false | "Field Name" or "field_name"
$config['perpage'] = 20;
$config['showpagenumbers'] = true; //true or false
$config['showprevnext'] = true; //true or false

/******************************************/
//SHOULDN'T HAVE TO TOUCH ANYTHING BELOW...
//except maybe the html echos for pagination and arrow image file near end of file.

include './Pagination.php';
$Pagination = new Pagination();

//CONNECT
mysql_connect($config['host'], $config['user'], $config['pass']);
mysql_select_db($config['database']);

//get total rows
$totalrows = mysql_fetch_array(mysql_query("SELECT count(*) as total FROM `".$config['table']."`"));

//limit per page, what is current page, define first record for page
$limit = $config['perpage'];
if(isset($_GET['page']) && is_numeric(trim($_GET['page']))){$page = mysql_real_escape_string($_GET['page']);}else{$page = 1;}
$startrow = $Pagination->getStartRow($page,$limit);

//create page links
if($config['showpagenumbers'] == true){
	$pagination_links = $Pagination->showPageNumbers($totalrows['total'],$page,$limit);
}else{$pagination_links=null;}

if($config['showprevnext'] == true){
	$prev_link = $Pagination->showPrev($totalrows['total'],$page,$limit);
	$next_link = $Pagination->showNext($totalrows['total'],$page,$limit);
}else{$prev_link=null;$next_link=null;}

//IF ORDERBY NOT SET, SET DEFAULT
if(!isset($_GET['orderby']) OR trim($_GET['orderby']) == ""){
	//GET FIRST FIELD IN TABLE TO BE DEFAULT SORT
	$sql = "SELECT * FROM `".$config['table']."` LIMIT 1";
	$result = mysql_query($sql) or die(mysql_error());
	$array = mysql_fetch_assoc($result);
	//first field
	$i = 0;
	foreach($array as $key=>$value){
		if($i > 0){break;}else{
		$orderby=$key;}
		$i++;		
	}
	//default sort
	$sort="ASC";
}else{
	$orderby=mysql_real_escape_string($_GET['orderby']);
}	

//IF SORT NOT SET OR VALID, SET DEFAULT
if(!isset($_GET['sort']) OR ($_GET['sort'] != "ASC" AND $_GET['sort'] != "DESC")){
	//default sort
		$sort="ASC";
	}else{	
		$sort=mysql_real_escape_string($_GET['sort']);
}

//GET DATA
$sql = "SELECT * FROM `".$config['table']."` ORDER BY $orderby $sort LIMIT $startrow,$limit";
$result = mysql_query($sql) or die(mysql_error());

//START TABLE AND TABLE HEADER
echo "<table>\n<tr>";
$array = mysql_fetch_assoc($result);
foreach ($array as $key=>$value) {
	if($config['nicefields']){
	$field = str_replace("_"," ",$key);
	$field = ucwords($field);
	}
	
	$field = columnSortArrows($key,$field,$orderby,$sort);
	echo "<th>" . $field . "</th>\n";
}
echo "</tr>\n";

//reset result pointer
mysql_data_seek($result,0);

//start first row style
$tr_class = "class='odd'";

//LOOP TABLE ROWS
while($row = mysql_fetch_assoc($result)){

	echo "<tr ".$tr_class.">\n";
	foreach ($row as $field=>$value) {	
		echo "<td>" . $value . "</td>\n";
	}
	echo "</tr>\n";
	
	//switch row style
	if($tr_class == "class='odd'"){
		$tr_class = "class='even'";
	}else{
		$tr_class = "class='odd'";
	}
	
}

//END TABLE
echo "</table>\n";

if(!($prev_link==null && $next_link==null && $pagination_links==null)){
echo '<div class="pagination">'."\n";
echo $prev_link;
echo $pagination_links;
echo $next_link;
echo '<div style="clear:both;"></div>'."\n";
echo "</div>\n";
}

/*FUNCTIONS*/

function columnSortArrows($field,$text,$currentfield=null,$currentsort=null){	
	//defaults all field links to SORT ASC
	//if field link is current ORDERBY then make arrow and opposite current SORT
	
	$sortquery = "sort=ASC";
	$orderquery = "orderby=".$field;
	
	if($currentsort == "ASC"){
		$sortquery = "sort=DESC";
		$sortarrow = '<img src="arrow_up.png" />';
	}
	
	if($currentsort == "DESC"){
		$sortquery = "sort=ASC";
		$sortarrow = '<img src="arrow_down.png" />';
	}
	
	if($currentfield == $field){
		$orderquery = "orderby=".$field;
	}else{	
		$sortarrow = null;
	}
	
	return '<a href="?'.$orderquery.'&'.$sortquery.'">'.$text.'</a> '. $sortarrow;	
	
}

?>
</body>
</html>