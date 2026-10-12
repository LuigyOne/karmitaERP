<?php 
$servername = "localhost";
$username = "redpwifi_remoto";
$password = "Polaca2024*";
$dbname = "redpwifi_sacco";

$connection= mysqli_connect ($servername, $username, $password,$dbname);

$id=$_REQUEST['id'];

$sql1 = "select * from upload where id=$id"; 
$result = mysqli_query($connection, $sql1);



if (mysqli_num_rows($result) > 0) {
	// Output data of each row
	while($row = mysqli_fetch_assoc($result)) {
		$field1 = $row["name"];
	}
	} else {
	echo "0 results";
}

// $file = mysqli_fetch_array($result); 
$file = $field1; 
$file_pointer = "upload/".$file; 


$query = "DELETE FROM upload WHERE id=$id"; 

if(mysqli_query($connection, $query)){
    echo "Registro eliminado con éxito.";
} else{
    echo "ERROR: No se pudo eliminar registro $id. " . mysqli_error($link);
}

  
// Use unlink() function to delete a file 
if (!unlink($file_pointer)) { 
    echo ("$file_pointer no se pudo borrar debido a un error"); 
} 
else { 
    echo ("$file_pointer se borro exitosamente"); 
} 

header("Location: index.php"); 
 ?>
