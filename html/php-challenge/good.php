<?php
session_start();
require('dbconnect.php');

// 投稿を検査 
$goodmessages = $db->prepare('SELECT count(*) FROM good WHERE post_id=? AND member_id=?');
$goodmessages->execute(array(
  $_POST['post_id'],
  $_SESSION['id']
));
$goodmessage = $goodmessages->fetch();

// いいねテーブルのレコードの削除・挿入
// if ($goodmessage['post_id'] === $post['id']) {
if ( $goodmessage['count(*)'] > 0) {
  $delete = $db->prepare('DELETE FROM good WHERE post_id=? AND member_id=?');
  $delete->execute(array(
    $_POST['post_id'],
    $_SESSION['id']
  ));
}else { 
    //テーブルpostのidがテーブルgoodのpost_idにINSERTされる
    $good = $db->prepare('INSERT INTO good SET post_id=?, member_id=?, created=NOW()');
    $good->execute(array(
      $_POST['post_id'],
      $_SESSION['id']
    ));
  }



  $goodpages = $db->query('SELECT COUNT(*) AS gpc FROM posts WHERE id');
  $goodpage = $goodpages->fetch();
  $just = $goodpage['gpc'];

  // var_dump($goodmessage['count(*)']);
  header('Location: index.php?page=()');
  exit();
