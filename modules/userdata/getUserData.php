<?php
  header('Content-type: application/json');
  
  include "../utils-php/config.php";

  if (isset($_POST['uid']) && trim($_POST['uid']) != "")
//  if (isset($_GET['uid']) && trim($_GET['uid']) != "")
  {
     //moved to config.php
    //$path = $docRoot.$csRoot."main/utils/userdata/";

    $uid = trim($_POST['uid']);
    //$uid = trim($_GET['uid']);
    $filename = $userdataPath . $uid . ".json";
//print $filename;
    if (file_exists($filename))
    {
      readfile($filename);
    }
  }
  else
    print "null";
?>