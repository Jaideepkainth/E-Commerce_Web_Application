<html>
<style>
body{
	font-family: Georgia, serif;
}
#fieldset{
	color:black;
	border:5px solid green;
	background-color:#9ACD32;
	border-radius: 20px;
}
#legend{
	font-weight:bold;
	border:2px solid green;
	background-color:#FFFFFF;
	border-radius: 20px;
}
#select{
   border-radius: 20px;
}
#search{
	border-radius: 20px;
}
#table1{
	margin-top: 4px;
	width: 100%;
	border-collapse: collapse;
	border-radius: 9px;
	border: 5px solid green;
}
#sbhead{
	border: 5px solid green;
	background-color: #9ACD32;
	border-radius: 10px 10px 0px 0px;
	padding: 4px;
}#emptyb{
	margin: 10px 4px;
	border: 1px solid green;
	background-color: #9ACD32;
	border-radius: 5px;
}
#emptyb:hover{
	background-color: #5a7a19;
	color: white;
	border-radius: 5px;
}
#sbfoot{
	border: 5px solid green;
	background-color: #9ACD32;
	border-radius: 0px 0px 10px 10px;
	float: right;
	padding: 4px;
}
#table2 th{
	background-color: #9ACD32;
    color: black;
}
#table2{
	border-collapse: collapse;
	border:5px solid green;
	text-align: center;
}
</style>
<?php
session_start();
$total=0;
if(!isset($_SESSION['id_list']))
{
	$_SESSION['id_list']=array();
}
if(isset($_GET['clear']))
{
	if($_GET['clear']==1)
	{
		session_unset();
	}
}
if(isset($_GET['delete']))
{
	$p_id=$_GET['delete'];
	unset($_SESSION['id_list'][$p_id]);
}
if(isset($_GET['buy']))
{
	$ids= $_GET['buy'];
	$searchpdt = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey= &visitorUserAgent&visitorIPAddress&trackingId=7000610&offerId='.$ids);
	$searchpdtxml = new SimpleXMLElement($searchpdt);
	$name= (string)$searchpdtxml->categories->category->items->offer->name;
	$base_price=(string)$searchpdtxml->categories->category->items->offer->basePrice;
	$items=array($ids,$name,$base_price);
	$_SESSION['id_list'][$ids]=$items;
}
?>
<head><title>Buy Products</title></head>
<body>
<span id="sbhead">Shopping Basket:</span><br/>
<?php
if(!empty($_SESSION['id_list']))
{ ?>   
<table border="1" id="table1">
<tr><th>Product Name</th><th>Price</th><th>Action</th></tr>
<?php
foreach ($_SESSION['id_list'] as $list)
{ ?>
	<tr><td><?php print $list[1] ?></td><td><?php print $list[2] ?></td><td><a href="buy.php?delete=<?php print $list[0] ?>">Delete</a></td></tr>
<?php $total=$total+$list[2];
}
?>
</table>
<form action="buy.php" method="GET">
	<input type="hidden" name="clear" value="1"/>
	<span id="sbfoot">Total: $ <?php print $total?></span>
	<input type="submit" id="emptyb" value="Empty Basket"/><br/><br/>
</form>
<?php
}
?>
<hr/>
<form action="buy.php" method="GET">
	<fieldset id="fieldset"><legend id="legend">Find products:</legend>
	<label>Category:</label><select name="category" id="select">
<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey= &visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
$xml = new SimpleXMLElement($xmlstr);
?>
<optgroup label="<?php print $xml->category->name ?>"><option value="<?php print $xml->category["id"] ?>"><?php  print $xml->category->name ?></option></optgroup>
<?php
foreach ($xml->category->categories->category as $cat)
{ ?><optgroup label="<?php  print $cat->name ?>"><option value="<?php print $cat["id"] ?>"><?php  print $cat->name ?></option>
<?php
if(!is_null($cat->categories->category))
{
	foreach ($cat->categories->category as $subcat)
	{
		?><option value="<?php print $subcat["id"] ?>"><?php  print $subcat->name; ?></option><?php
	}
}
?>
</optgroup><?php
}
?>
</select>
<label>Search keywords: <input type="text" name="search"/ id="search"></label>
<input type="submit" value="Search"/ id="search">
</fieldset>
</form>
<?php
if(isset($_GET['search']))
{
$cate=$_GET['category'];
$sear=$_GET['search'];
$searchresult = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey= &trackingId=7000610&categoryId='.$cate.'&keyword='.$sear.'&numItems=20');
$searchresultxml = new SimpleXMLElement($searchresult);
if(!($searchresultxml->categories->category->items["matchedItemCount"]==0))
{ ?> 
<table border="1" id="table2">
	<colgroup>
		<col width="33%">
		<col width="7%">
	</colgroup>
<tr><th>Product Name</th><th>Price</th><th>Description</th></tr>
<?php
foreach ($searchresultxml->categories->category->items->offer as $key) { ?>
<tr><td><a href="buy.php?buy=<?php print $key["id"] ?>"> <?php print $key->name ?></a></td><td>$ <?php print $key->basePrice ?></td><td><?php print $key->description ?></td></tr>
<?php }}
else
{
	print "No Products in this Category";
}
}
?>
</table>
</body>
</html>
