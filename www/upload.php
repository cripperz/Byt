<?php

$ignored = array(".", "..");
$host="http://byt.tl/";

function random_dir()
{
    $upload_dir = "files/";

    $name = "";
    do {
        $name = md5(uniqid(rand(), true));
    } while (file_exists($upload_dir.$name));

    return $upload_dir.$name;
}

//var_dump($_FILES);
if (isset($_FILES['file'])) {
    if ($_FILES['file']['error'] > 0) {

        error_log("Error: ".$_FILES['file']['error']);
        echo "An error append =/";
    } else {

        $directory = random_dir();
        $target_file = $directory."/".$_FILES['file']['name'];
        if (mkdir($directory)) {
            move_uploaded_file($_FILES['file']['tmp_name'], $target_file);

            require_once "url_shorter.php";

            $db = open_database();

            $hash = rand_sha1(20);
            $req = $db->prepare("INSERT INTO files (path, filename, hash) VALUES (:path, :filename, :hash)");
            $req->execute(array('path' => $target_file, 'filename' => $_FILES['file']['name'], 'hash' => $hash));

            echo make_short($host."file.php?f=".$hash);
            //echo $host."file.php?f=".$hash;
        }
        else
        {
            error_log("Error: fail to create remote dir: ".$directory);
            echo "An error append =/";
        }
    }
}
else
{
    echo "<form method='post' action='upload.php' enctype='multipart/form-data'>";
    echo "<input type='file' name='file' />";
    echo "<input type='submit' name='submit' value='Send' />";
    echo "</form>";
}

?>
