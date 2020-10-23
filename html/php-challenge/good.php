<?php
session_start();
require('dbconnect.php');



if (!empty($_POST)) { //$_POSTが空のとき
  //いいねする
  // echo $_POST['post_id'];

    $good = $db->prepare('INSERT INTO good SET post_id=?, member_id=?, created=NOW()');
    $good->execute(array(
      $_POST['id'],
      $_SESSION['id']

    ));
    
    header('Location: index.php');
    exit();
}
  
  