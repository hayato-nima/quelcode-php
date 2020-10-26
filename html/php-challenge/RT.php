<?php
session_start();
require('dbconnect.php');



// var_dump($_POST);

//レコードを挿入
if (!empty($_POST)) {
  $rt = $db->prepare('INSERT INTO posts SET rt_member_id=?, member_id=?, message=?, reply_post_id=?, created=NOW()');
  try {
    $rt->execute(array(
      $_SESSION['id'],
      $_POST['member_id'],
      $_POST['message'],
      $_POST['reply_post_id']
    ));
  } catch (PDOException $e) {
    echo 'エラー： ' . $e->getMessage();
    exit();
  }
}

header('Location: index.php');
exit();
