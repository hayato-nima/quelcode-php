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



// $_POST['reference']に値があればリツイートの投稿だとわかる

$iineids = $db->prepare('SELECT * FROM good WHERE post_id=? and member_id=?');
$iineids->execute(array(
  $_POST['reference'],
  $_SESSION['id']
));
$iineid = $iineids->fetch();

// if ($_POST['reference'] > 0) 
// {
//   $rtdelete = $db->prepare('DELETE FROM good WHERE post_id=? AND member_id=?');
//   $rtdelete->execute(array(
//     $_POST['reference'],
//     $_SESSION['id']
//   ));
// }
// else
if ($_POST['reference'] = 0) 
{
  $rtgood = $db->prepare('INSERT INTO good SET post_id=?, member_id=?, created=NOW()');
  $rtgood->execute(array(
    $_POST['reference'],
    $_SESSION['id']
  ));
}


// var_dump($_POST['reference']);

// いいねテーブルのレコードの削除・挿入
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


header('Location: index.php'); exit();

