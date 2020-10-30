<?php
session_start();
require('dbconnect.php');

//RTの元投稿を検索する
$def_messages = $db->prepare('SELECT * FROM posts WHERE message=? AND member_id=? AND reply_post_id=? ');
$def_messages->execute(array(
  $_POST['message'],
  $_POST['member_id'],
  $_POST['reply_post_id']
));
$def_message = $def_messages->fetch();

// 元投稿のidをreferenceに代入する
$rt_messages = $db->prepare('SELECT count(*) FROM posts WHERE  message=? and reference=? AND member_id=? AND reply_post_id=? ');
$rt_messages->execute(array(
  $_POST['message'],
  $def_message['reference'],
  $_POST['member_id'],
  $_POST['reply_post_id']
));
$rt_message = $rt_messages->fetch();





// var_dump($def_message);
// var_dump($def_message['reference']);
// var_dump($rt_message);
// var_dump($_POST);
// var_dump($_POST['reference']);
// var_dump($rt_message['count(*)']);
// var_dump($rt_message['reference']);
// var_dump($rt_message['rtcount']);





if ($rt_message['count(*)'] > 0) { //ボタンを押した押してないの処理 カウントの結果で判断する
  // レコードを削除
  if ($def_message['reference'] > 0) {
    $erase = $db->prepare('DELETE FROM posts WHERE id=?  and reference=?');
    $erase->execute(array(
      $_POST['reference'],
      $def_message['reference']
      // $_POST['message'],
      // $_POST['member_id'],
      // $_POST['reply_post_id']
    ));
  }else {
    $selfrt = $db->prepare('INSERT INTO posts SET message=?, reference=?, member_id=?, reply_post_id=?, created=NOW()');
    $selfrt->execute(array(
      $_POST['message'],
      $_POST['reference'], 
      $_POST['member_id'],
      $_POST['reply_post_id']
    ));
    
  }
  
} else {
  //テーブルpostにリツイートをINSERT
  if ($def_message['reference'] = 0) { //投稿が同じ人の場合

    $rt = $db->prepare('INSERT INTO posts SET message=?, reference=?, member_id=?, reply_post_id=?, created=NOW()');
    $rt->execute(array(
      $_POST['message'],
      $_POST['reference'], //変更箇所
      $_POST['member_id'],
      $_POST['reply_post_id']
    ));
  } else {
    $def_rt = $db->prepare('INSERT INTO posts SET message=?, reference=?, member_id=?, reply_post_id=?, created=NOW()');
    $def_rt->execute(array(
      $_POST['message'],
      $_POST['reference'],
      $_POST['member_id'],
      $_POST['reply_post_id']
    ));
  }
}


header('Location: index.php');
exit();
