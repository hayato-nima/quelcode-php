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

// var_dump($original_message['original_post_id']);

$rt_messages = $db->prepare('SELECT count(*) FROM posts WHERE  message=? and original_post_id=? AND member_id=? AND reply_post_id=? ');
$rt_messages->execute(array(
  $_POST['message'],
  $original_message['original_post_id'],
  $_POST['member_id'], //ログインしている人
  $_POST['reply_post_id']
));
$rt_message = $rt_messages->fetch();


// var_dump($rt_message['count(*)']);
// var_dump($rt_message);
// var_dump($_POST['post_id']);
// var_dump($_POST['original_post_id']);
// var_dump($original_message['member_id']);
// var_dump($_SESSION['id']);



if ($rt_message['count(*)'] > 0) { //ボタンを押した押してないの処理 カウントの結果で判断する
  // レコードを削除 ログインしているIDと投稿のIDが同じ場合、削除できるようにする
  if ($_SESSION['id'] === $original_message['member_id']) {
    $delete = $db->prepare('DELETE FROM posts WHERE id=? ');
    $delete->execute(array(
      $_POST['post_id'],
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
    //通常メッセージをリツイートした場合のレコード挿入
    $rt = $db->prepare('INSERT INTO posts SET message=?,  original_post_id=?, member_id=?, reply_post_id=?, created=NOW()');
    $rt->execute(array(
      $_POST['message'],
      $_POST['post_id'],
      $_POST['member_id'],
      $_POST['reply_post_id']
    ));
  }
}



header('Location: index.php');
exit();





// //RTの元投稿の検索
// // 元の投稿はreference=0になっている コメントを照合して取得
// $origin_messages = $db->prepare('SELECT * FROM posts WHERE message=? and member_id=?');
// $origin_messages->execute(array(
//   $_POST['message'],
//   $_POST['post_member_id']
// ));

// $origin_message = $origin_messages->fetch(); //$origin_message['id']で元投稿のidはわかる
// // var_dump($origin_message);
// // var_dump($_POST['post_member_id']);


// //RTの元投稿を検索する
// $def_messages = $db->prepare('SELECT * FROM posts WHERE id=? and message=? AND member_id=? AND reply_post_id=? ');
// $def_messages->execute(array(
//   $_POST['reference'],
//   $_POST['message'],
//   $_POST['member_id'],
//   $_POST['reply_post_id']
// ));
// $def_message = $def_messages->fetch();

// // 元投稿のidをreferenceに代入する ボタンの押下の判定に使う 
// $rt_messages = $db->prepare('SELECT count(*) FROM posts WHERE  message=? and reference=? AND member_id=? AND reply_post_id=? ');
// $rt_messages->execute(array(
//   $_POST['message'],
//   $def_message['reference'],
//   $_POST['member_id'],
//   $_POST['reply_post_id']
// ));
// $rt_message = $rt_messages->fetch();


// // var_dump($origin_message);
// // var_dump($origin_message['id']);
// // var_dump($def_message);
// // var_dump($def_message['reference']);
// // var_dump($rt_message);
// // var_dump($_POST);
// // var_dump($_POST['reference']);
// // var_dump($rt_message['count(*)']);
// // var_dump($rt_message['reference']);
// // var_dump($rt_message['rtcount']);


// if ($rt_message['count(*)'] > 0) { //ボタンを押した押してないの処理 カウントの結果で判断する
//   // レコードを削除
//   if ($def_message['reference'] > 0) {
//     $erase = $db->prepare('DELETE FROM posts WHERE id=? ');
//     $erase->execute(array(
//       $_POST['reference']
//     ));
//   } else {
//     // 自分の投稿にリツイートした場合
//     if ($def_message['reference'] > 0) {
//       $erase = $db->prepare('DELETE FROM posts WHERE id=?  ');
//       $erase->execute(array(
//         $_POST['reference'],
//       ));
//     } else {
//       $selfrt = $db->prepare('INSERT INTO posts SET message=?, reference=?, original_post_id=?, member_id=?, reply_post_id=?, created=NOW()');
//       $selfrt->execute(array(
//         $_POST['message'],
//         $_POST['reference'],
//         $origin_message['id'],
//         $_POST['member_id'],
//         $_POST['reply_post_id']
//       ));
//     }
//   }
// } else {
//   //テーブルpostにリツイートをINSERT
//   if ($def_message['reference'] = 0) { //投稿が同じ人の場合

//     $rt = $db->prepare('INSERT INTO posts SET message=?, reference=?, original_post_id=?, member_id=?, reply_post_id=?, created=NOW()');
//     $rt->execute(array(
//       $_POST['message'],
//       $_POST['reference'],
//       $origin_message['id'],
//       $_POST['member_id'],
//       $_POST['reply_post_id']
//     ));
//   } else {
//     $def_rt = $db->prepare('INSERT INTO posts SET message=?, reference=?, original_post_id=?, member_id=?, reply_post_id=?, created=NOW()');
//     $def_rt->execute(array(
//       $_POST['message'],
//       $_POST['reference'],
//       $origin_message['id'],
//       $_POST['member_id'],
//       $_POST['reply_post_id']
//     ));
//   }
// }
