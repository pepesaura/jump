<?php
//versión 18/11/26

require_once __DIR__ . '/wp-config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
$conn -> set_charset("utf8");

if ($conn->connect_error) {
    die("Error de conexion: " . $conn->connect_error);
}


    // selecciona todos los posts
    $sql = "SELECT ID, guid, post_type, post_content 
    	FROM `" . $table_prefix . "posts` 
    	WHERE post_type='post' and post_name != ' '";

    $result = $conn->query($sql);

if ($result->num_rows > 0) {

	// guarda los ID en un array    
    $posts_array = array();
    while($row = $result->fetch_assoc()) {
        $posts_array[] = $row['ID'];
    }
    
 } else {
    echo "No se encontraron registros en la primera consulta.";
}


$url = '';
while ($url === ''){
    
$random = rand(0,count($posts_array)-1);

    $sql = "SELECT ID, post_content 
            FROM `" . $table_prefix . "posts`
            WHERE ID = $posts_array[$random]";

    $result = $conn->query($sql);

$row = $result->fetch_assoc();
// echo "<p>" . $row['post_content'];
            $desde = strpos($row['post_content'],'visita: <a href=');
            if($desde){
                $hasta = strpos($row['post_content'],'>',  $desde);
                $url = substr($row['post_content'], $desde + 17, $hasta - $desde - 18);
                // echo "<p>url = " . $url;
                }
} // while hasta encontrar una url en visita:

$conn->close();
$url = "Location: " . $url;
header($url);
die();

?>