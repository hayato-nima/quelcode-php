<?php
session_start();
require('dbconnect.php');

// 投稿を検索＆カウント リツイートの判定に使用
//original_post_idを取りに行く

$original_messages = $db->prepare('SELECT * FROM posts WHERE id=?');
$original_messages->execute(array(
  $_POST['post_id']
));
$original_message = $original_messages->fetch();


$rt_messages = $db->prepare('SELECT count(*) FROM posts WHERE  message=? and original_post_id=? AND member_id=? AND reply_post_id=? ');
$rt_messages->execute(array(
  $_POST['message'],
  $original_message['original_post_id'],
  $_POST['member_id'], //ログインしている人
  $_POST['reply_post_id']
));
$rt_message = $rt_messages->fetch();

//POST_[id]をoriginal_post_idに当てはめてリツイートがあるか確認
$rt_counts = $db->prepare('SELECT count(*) FROM posts WHERE  message=? and original_post_id=? AND member_id=? AND reply_post_id=? ');
$rt_counts->execute(array(
  $_POST['message'],
  $_POST['post_id'],
  $_POST['member_id'], //ログインしている人
  $_POST['reply_post_id']
));
$rt_count = $rt_counts->fetch();


// var_dump($_POST);
// var_dump($rt_count['count(*)']);
// var_dump($_POST['original_post_id']);
// var_dump($_SESSION['id']);
// var_dump($original_message['member_id']);


if ($rt_message['count(*)'] > 0) { //ボタンを押した押してないの処理 カウントの結果で判断する
  // レコードを削除 ログインしているIDと投稿のIDが同じ場合、削除できるようにする
  if ($_SESSION['id'] === $original_message['member_id']) {
    $delete = $db->prepare('DELETE FROM posts WHERE id=? ');
    $delete->execute(array(
      $_POST['post_id']
    ));
  }else{
    $delete = $db->prepare('DELETE FROM posts WHERE message=? and original_post_id=? and member_id=? ');
    $delete->execute(array(
      $_POST['message'],
      $_POST['original_post_id'],
      $_POST['member_id']
    ));
  }
} else {

  //リツイートをリツイートした時のレコード挿入は、元のidをカラムoriginal_post_idに入れる必要がある
  if ($_POST['original_post_id']) {
    $rtrt = $db->prepare('INSERT INTO posts SET message=?,  original_post_id=?, member_id=?, reply_post_id=?, created=NOW()');
    $rtrt->execute(array(
      $_POST['message'],
      $_POST['original_post_id'],
      $_POST['member_id'],
      $_POST['reply_post_id']
    ));
  } else {
    //自分がリツイートボタンを押してリツイート済みだった場合、削除する
    if ($rt_count['count(*)'] > 0) {
      $delete = $db->prepare('DELETE FROM posts WHERE original_post_id=? and member_id=?');
      $delete->execute(array(
        $_POST['post_id'],
        $_SESSION['id']
      ));
    } else {
      // 通常メッセージをリツイートした場合のレコード挿入
      $rt = $db->prepare('INSERT INTO posts SET message=?,  original_post_id=?, member_id=?, reply_post_id=?, created=NOW()');
      $rt->execute(array(
        $_POST['message'],
        $_POST['post_id'],
        $_POST['member_id'],
        $_POST['reply_post_id']

      ));
    }
  }
}

header('Location: index.php');
exit();
