<?php
session_start();
require('dbconnect.php');
// 投稿を検査
// $rt_messages = $db->prepare('SELECT count(*) as rtcount FROM posts WHERE message=? AND  member_id=? AND reply_post_id=?');
// $rt_messages->execute(array(
//   $_POST['message'],
//   $_POST['member_id'],
//   $_POST['reply_post_id']
// ));
// $rt_message = $rt_messages->fetch();

// 投稿を検査
$rt_messages = $db->prepare('SELECT * FROM posts WHERE message=? AND member_id=? AND reply_post_id=?');
$rt_messages->execute(array(
  $_POST['message'],
  $_POST['member_id'],
  $_POST['reply_post_id']
));
$rt_message = $rt_messages->fetch();





// var_dump($_POST);
// var_dump($_POST['reference']);
// var_dump($rt_message);
// var_dump($rt_message['reference']);
// var_dump($rt_message['rtcount']);




if ($rt_message['reference'] > 0) {
  // レコードを削除
  $erase = $db->prepare('DELETE FROM posts WHERE message=? AND reference=? and member_id=? AND reply_post_id=?');
  $erase->execute(array(
    $_POST['message'],
    $rt_message['reference'],
    $_POST['member_id'],
    $_POST['reply_post_id']
  ));
}
else{
  //テーブルpostにリツイートをINSERT
  $rt = $db->prepare('INSERT INTO posts SET message=?, reference=?, member_id=?, reply_post_id=?, created=NOW()');
  $rt->execute(array(
$_POST['message'],
$_POST['reference'],
$_POST['member_id'],
$_POST['reply_post_id']
  ));
}

header('Location: index.php');
exit();
