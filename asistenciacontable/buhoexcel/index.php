<?php
$servername = "localhost";
$username = "redpwifi_remoto";
$password = "Polaca2024*";
$dbname = "redpwifi_sacco";

$connection= mysqli_connect ($servername, $username, $password,$dbname);
if(isset($_POST['submit'])!=""){
  $name=$_FILES['photo']['name'];
  $size=$_FILES['photo']['size'];
  $type=$_FILES['photo']['type'];
  $temp=$_FILES['photo']['tmp_name'];
  $caption1=$_POST['caption'];
  $link=$_POST['link'];
  move_uploaded_file($temp,"upload/".$name);

  $sql1 = "insert into upload(name) values('$name')";
        $insert = mysqli_query($connection, $sql1);
        if ($insert) {
            echo "Subio Correctamente el archivo!";
			header("location:index.php");
        } else {
            echo "Error" . mysqli_error($connection);
        }
}
?>
<html>
<head>
<title>Karmita :: XML SRI a xls</title>
<link rel="stylesheet" href="css/style.css" />
</head>
<body>

<h><p align="center">BUHO XML a EXCEL</p></h>
<h3><p align="center">Subir XML SRI y Descargar xls</p></h3>	
<form enctype="multipart/form-data" action="" name="form" method="post">

<table border="0" cellspacing="0" cellpadding="5" id="table">
	<tr>
		<th >Subir Archivo </th>
	</tr>
	<tr>
		<td ><label for="photo"></label>
			<input type="file" name="photo" id="photo" />
		</td>
	</tr>

	<tr>
	<th colspan="2" scope="row"><input type="submit" name="submit" id="submit" value="Cargar..." /></th>
	</tr>
</table>
</form>
<br />
<br />
<table border="1" align="center" id="table1" cellpadding="0" cellspacing="0">
	<tr>
		<th colspan="3">
		Convertir
		</th>
	</tr>
<?php
$sql = "select * from upload order by id desc"; 
$result = mysqli_query($connection, $sql);

while ($row1 = mysqli_fetch_assoc($result)) {
	$name=$row1['name'];
	$id=$row1['id'];
	?>
	<tr>
		<td width="300">
			<?php echo $id;?>. <a href="download.php?filename=<?php echo $name;?>">
			<?php echo $name ;?></a>
		</td>
		<td align="center">
			<a href="convertir.php?id=<?php echo $row1["id"]; ?>">Convertir</a>
		</td>
		<td align="center">
			<a href="delete.php?id=<?php echo $row1["id"]; ?>">Borrar</a>
		</td>
	</tr>
<?php }?>
</table>
</body>
</html>