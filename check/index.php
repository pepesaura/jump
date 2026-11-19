<?php
error_reporting(0);
// jump-go15.php
// versión 19/11/26
?>
<?php
    // necesario para multisites, renombrando el archivo como jump-go15.php

    $script = pathinfo(
    basename($_SERVER['SCRIPT_NAME']),
    PATHINFO_FILENAME);

    $at = strpos($script, '-go');
    if($at){
        $at = substr($script, $at+3) . '_';
    } else {$at = '';}
?>

<?php

// se supone que el archivo está en una carpeta
require_once __DIR__ . '/../wp-config.php';

// necesario para multisites, renombrando el archivo como jump-go15.php
$table_prefix .= $at;
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>check</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:wght@400;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="styles.css">

</head>
<body>
<article>


<?php

$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// se resuelve por fin el problema de los charset
$conn -> set_charset("utf8");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

wp_url($conn, $table_prefix);

?>

<!-- muestra el menú -->
<form class="actions" method="get" action="">
  <button type="submit" name="go" value="posts">posts</button>
  <button type="submit" name="go" value="errores">errores</button>
  <button type="submit" name="go" value="category">category</button>
  <button type="submit" name="go" value="tags">tags</button>
  <button type="submit" name="go" value="jump">jump</button>
</form>

<?php
// muestra el menú
$accion = $_GET['go'] ?? 'inicio';


switch ($accion) {
    case 'terms':
        wp_terms($conn, $table_prefix);
        break;

    case 'posts':
        wp_posts($conn, $table_prefix);
        break;
    case 'errores':
        wp_errores($conn, $table_prefix);
        break;
    case 'category':
        categoria($conn, $table_prefix,'category');
        break;
    case 'tags':
        categoria($conn, $table_prefix,'post_tag');
        break;
    case 'jump':
        wp_jump($conn, $table_prefix);
        break;
    default:
        wp_posts($conn, $table_prefix);
        break;
}


//wp_terms($conn);
//categoria($conn,'category');
//categoria($conn,'post_tag');
//wp_posts($conn);
//wp_posts_array($conn);
//jump($conn);

//cerrar la conexión
$conn->close();

// **************************************************************************

//lista errores
function wp_errores($conn, $prefijo){
    
$sql = "SELECT post_title, post_name, ID, guid, post_type 
    FROM `" . $prefijo . "posts` 
    WHERE post_type='post' and post_name != ' ' 
    ORDER BY ID DESC";
    
  $result = $conn->query($sql);
    
if ($result->num_rows > 0) {
    
    $errores = 0;
    $wp_siteurl = wp_siteurl($conn, $prefijo);

    while($row = $result->fetch_assoc()) {

        // calcula el final del guid para poder compararlo con el ID
        $desde = strpos($row['guid'], '?p=');
        $zid = substr($row['guid'], $desde + 3);

        if ($zid != $row['ID']){
            
            $errores = $errores + 1;
            
            $url_new = "<a href='" . $wp_siteurl . "?p=" . $row['ID'] . "'>"  
                . $row['ID']
                . " : "
                . $row['post_title']
                . "</a> ";

            $url_old = "<a href='" . $row['guid'] . "'>"  
                . substr($row['guid'], 8)
                . "</a> ";

        
      $enlace = $url_new . $url_old . " <br> ";
      echo $enlace;
        } 
        
    }

 } else {
    echo "No se encontraron registros.";
}

echo "<h1>" . $errores . " errores</h1>";

}



// **************************************************************************

// consulta la tabla wp_posts
// escribe una lista con los enlaces y algunos datos útiles
// solamente si tienen la fila 'visita: '
function wp_posts($conn, $prefijo){
    

$wp_siteurl = wp_siteurl($conn, $prefijo);
    
$sql = "SELECT post_title, post_name, ID, guid, post_type, post_content 
    FROM `" . $prefijo . "posts` 
    WHERE post_type='post' and post_name != ' ' 
    ORDER BY ID DESC";
    
  $result = $conn->query($sql);
    
if ($result->num_rows > 0) {
    
    $errores = 0;
    $wp_siteurl = wp_siteurl($conn, $prefijo);

    while($row = $result->fetch_assoc()) {

        // calcula el final del guid para poder compararlo con el ID
        $desde = strpos($row['guid'], '?p=');
        $zid = substr($row['guid'], $desde + 3);

        if ($zid != $row['ID']) 
            $errores = $errores + 1;
        
        $desde = strpos($row['post_content'],'visita: <a href=');        
        if($desde){
            $hasta = strpos($row['post_content'],'>',  $desde);
            $url = substr($row['post_content'], $desde + 17, $hasta - $desde - 18);
            
        } else { $url = '';}
        
        //

        if($url=='')
            $url = "#";

        $url_post = "<a href='" . $wp_siteurl . "?p=" . $row['ID'] . "'>"  
            . $row['post_title']
            . "</a> ";

        $url_link = "<a href='" . $url . "'>"  
            . $url
            . "</a> ";
        
        $enlace = $url_post . $url_link . " <br> ";

        echo $enlace;


    }

 } else {
    echo "No se encontraron registros.";
}

echo "<h1>" . $result->num_rows . " entradas, " . $errores . " errores</h1>";

}


// ****************************
// consulta la tabla wp_terms
// escribe una lista con los términos y su número de índice
function wp_terms($conn, $prefijo){
    
  echo "<h1>wp_terms: name, [term_id], ". date('h:i:s', time()). "</h1>";

  $sql = "SELECT term_id,name,slug 
    FROM `" . $prefijo . "terms`"; 

  $result = $conn->query($sql);

if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
        $enlace = "<a href=https://pepesegura.es/tags/" . $row['slug'] . ">"  
       . $row['term_id'] . " | "
        . $row['name'] . " | " 
        . $row['slug']
        . "</a> ";
      echo $enlace;
    }

 } else {
    echo "No se encontraron registros.";
}

}

// consulta la tabla wp_posts
// crea un array con los números 
// escribe una lista con los enlaces y algunos datos útiles
function wp_posts_array($conn){
    
  echo "<h1>wp_terms: name, [term_id] ". date('h:i:s', time()). "</h1>";


//$sql = "SELECT ID, post_title, post_name, guid, post_type, post_content FROM `wppi_posts` WHERE post_type='post' and post_name != ' '";

    // una consulta para llenar el array
    $sql = "SELECT ID, guid, post_type, post_content FROM `wppi_posts` WHERE post_type='post' and post_name != ' '";

    $result = $conn->query($sql);

if ($result->num_rows > 0) {

    $posts_array = array();
    while($row = $result->fetch_assoc()) {
        
        $posts_array[] = $row['ID'];
        echo "<p>" . $row['ID'] . " : " . $row['guid'];
    }
    
    $random = rand(0,count($posts_array));
    echo "<p>entradas = " . count($posts_array);
    echo "<p>resultado = " . $posts_array[$random];
    echo "<p>";

 } else {
    echo "No se encontraron registros.";
}

    $sql = "SELECT ID, guid, post_content FROM `wppi_posts` WHERE ID =" . $posts_array[$random];
    $result = $conn->query($sql);

if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
        
        $desde = strpos($row['post_content'],'visita: <a href=');
        
        // si no se encuentra el enlace 
        if($desde){
            $hasta = strpos($row['post_content'],'>',  $desde);
            $url = substr($row['post_content'], $desde + 16, $hasta - $desde - 16);
            
        } else { $url = '';}
        
        //

        if($url!=''){
      
      $enlace = "<a href='" . $row['guid'] . "'>"  
        . 'name: '.$row['post_name'] . ' '
        . '| url: '. $url . ' '
        . "</a> ";
      echo $enlace;
        }
    }

 } else {
    echo "No se encontraron registros.";
}

}


// funcion original que aprovecha las tablas de YOAST SEO
function categoria($conn, $prefijo, $sub_type){
    
    $sql = "SELECT object_id, object_type, object_sub_type, permalink, breadcrumb_title
            FROM `" . $prefijo . "yoast_indexable` 
        WHERE object_sub_type= '$sub_type' 
        ORDER BY `permalink` ASC ";

    $result = $conn->query($sql);


    if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
      $enlace = "<a href='" . $row["permalink"] . "'>" . $row["breadcrumb_title"] . "</a>";
      echo $enlace;
    }

    } else {
    echo "No se encontraron registros.";
    }

}


// consulta la tabla wp_posts
// escribe una lista con los enlaces y algunos datos útiles
// solamente si tienen la fila 'visita: '

function wp_jump($conn, $prefijo){

$sql = "SELECT post_title, post_name, ID, guid, post_type, post_content 
FROM `" . $prefijo . "posts` 
WHERE post_type='post' and post_name != ' ' 
ORDER BY `ID` DESC";
    

$result = $conn->query($sql);

    
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
        
        $desde = strpos($row['post_content'],'visita: <a href=');
        
        // si no se encuentra el enlace 
        if($desde){
            $hasta = strpos($row['post_content'],'>',  $desde);
            $url = substr($row['post_content'], $desde + 17, $hasta - $desde - 18);
            
        } else { $url = '';}
        
        //

        if($url!='') {

          $enlace = "<a href='" . $url . "'>"  
            . $row['ID'] . ' | '
            . $url . ' | '
            . $row['post_title'] . ' '
            . "</a> ";
          echo $enlace;
        }
    }

 } else {
    echo "No se encontraron registros.";
}

}


?>
</article>
   <footer>© 2026 · Hecho con HTML y CSS</footer>

</body>
</html>


<?php

// busca la url del blog
function wp_blogname($conn, $prefijo){
    $sql = "SELECT option_name, option_value
    FROM `" . $prefijo . "options`
    WHERE option_name='blogname' "; 

    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        $resultado = $row['option_value'];
    }
return $resultado;
}

// busca la url del blog
function wp_siteurl($conn, $prefijo){
    $sql = "SELECT option_name, option_value
    FROM `" . $prefijo . "options`
    WHERE option_name='siteurl' "; 

    $result = $conn->query($sql);
    while($row = $result->fetch_assoc()) {
        $resultado = $row['option_value'];
    }
return $resultado;
}

function wp_url($conn, $prefijo){
    $wp_siteurl = wp_siteurl($conn, $prefijo);
    $wp_blogname = wp_blogname($conn, $prefijo);
    $enlace = "<a href='" . $wp_siteurl . "'>"  
        . $wp_blogname
        . "</a> ";
    echo "<h1>" . $enlace . "</h1>";
    }

?>

