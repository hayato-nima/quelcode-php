<?php
session_start();
require('dbconnect.php');

// 投稿をカウント いいねの有無の確認に使用
$goodmessages = $db->prepare('SELECT count(*) FROM good WHERE post_id=? AND member_id=?');
$goodmessages->execute(array(
  $_POST['original_post_id'],
  $_SESSION['id']
));
$goodmessage = $goodmessages->fetch();


//投稿を検索 いいねの挿入・削除の判定に使用
$goodrecords = $db->prepare('SELECT * FROM good WHERE post_id=? AND member_id=?');
$goodrecords->execute(array(
  $_POST['post_id'],
  $_SESSION['id']
));
$goodrecord = $goodrecords->fetch();


// いいねテーブルのレコードの削除・挿入
if ($goodmessage['count(*)'] > 0) {
  $delete = $db->prepare('DELETE FROM good WHERE post_id=? AND member_id=?');
  $delete->execute(array(
    $_POST['original_post_id'],
    $_SESSION['id']
  ));
} else {
  //リツイートにいいねした場合
  if ($_POST['original_post_id']) {
    $rtgood = $db->prepare('INSERT INTO good SET post_id=?, member_id=?, created=NOW()');
    $rtgood->execute(array(
      $_POST['original_post_id'],
      $_SESSION['id']
    ));
  } else {
    //普通メッセージにいいねした場合
    if ($goodrecord) {
      $delete = $db->prepare('DELETE FROM good WHERE post_id=? AND member_id=?');
      $delete->execute(array(
        $_POST['post_id'],
        $_SESSION['id']
      ));
    } else {
      $good = $db->prepare('INSERT INTO good SET post_id=?, member_id=?,  created=NOW()');
      $good->execute(array(
        $_POST['post_id'],
        $_SESSION['id']
      ));
    }
  }
}

header('Location: index.php');
exit();
