<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	$members->execute(array($_SESSION['id']));
	$member = $members->fetch();
} else {
	// ログインしていない
	header('Location: login.php');
	exit();
}

// 投稿を記録する
if (!empty($_POST)) {
	if ($_POST['message'] != '') {
		$message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_post_id=?, created=NOW()');
		$message->execute(array(
			$member['id'],
			$_POST['message'],
			$_POST['reply_post_id']
		));

		header('Location: index.php');
		exit();
	}
}

// 投稿を取得する
$page = $_REQUEST['page'];
if ($page == '') {
	$page = 1;
}
$page = max($page, 1);

// 最終ページを取得する
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;
$start = max(0, $start);


$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?, 5');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();


// 返信の場合
if (isset($_REQUEST['res'])) {
	$response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
	$response->execute(array($_REQUEST['res']));

	$table = $response->fetch();
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value)
{
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 本文内のURLにリンクを設定します
function makeLink($value)
{
	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>', $value);
}


?>
<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>ひとこと掲示板</h1>
		</div>
		<div id="content">
			<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
			<form action="" method="post">
				<dl>
					<dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
					<dd>
						<textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
						<input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>" />
					</dd>
				</dl>
				<div>
					<p>
						<input type="submit" value="投稿する" />
					</p>
				</div>
			</form>


			<?php
			foreach ($posts as $post) : //id入っている
			?>
				<!-- postのループの開始 
			いいねとリツイートのフォームを5回ずつ繰り返し表示
			いいねは押してあった場合に取り消しをするリツイートも同様-->

				<div class="msg">
					<?php
					if ($post['original_post_id']) { //普通投稿は0 リツイートなら値が1以上
					?>
						<p class="day">
							<?php
							// リツイートした人の名前を取得して表示
							$rt_names = $db->prepare('SELECT DISTINCT name, member_id FROM members, posts WHERE members.id=?');
							$rt_names->execute(array(
								$post['member_id']
							));
							$rt_name = $rt_names->fetch();
							echo ($rt_name['name'] . 'さんがリツイートしました');
							?>
						</p>
					<?php
					}
					?>

					<!-- リツイート時の元投稿の画像への切り替え -->
					<?php if ($post['original_post_id']) : ?>

						<?php  //$post['original_post_id']は元投稿のid これをpostsのidに代入して取得する

						$def_names = $db->prepare('SELECT * FROM posts WHERE id=? ');
						$def_names->execute(array(
							$post['original_post_id']
						));
						$def_name = $def_names->fetch();



						// 取得したpostテーブルのmember_idを使ってmembersテーブルの情報を取得する
						$def_records = $db->prepare('SELECT * FROM members WHERE id=? ');
						$def_records->execute(array(
							$def_name['member_id']
						));
						$def_record = $def_records->fetch();
						?>

						<img src="member_picture/<?php echo h($def_record['picture']) ?>" width="48" height="48" alt="<?php echo h($def_record['name']); ?>" />
					<?php else : ?>
						<img src="member_picture/<?php echo h($post['picture']) ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
					<?php endif; ?>

					<!-- メッセージと投稿者名の表示↓ -->
					<?php if ($post['original_post_id']) : ?>
						<p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($def_record['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
					<?php else : ?>
						<p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
					<?php endif; ?>


					<!-- 日付と削除の処理↓ -->
					<p class="day"><a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
						<?php
						if ($post['reply_post_id'] > 0) :
						?>
							<a href="view.php?id=<?php echo
																			h($post['reply_post_id']); ?>">
								返信元のメッセージ</a>
						<?php
						endif;
						?>
						<?php
						if ($_SESSION['id'] == $post['member_id']) :
						?>
							[<a href="delete.php?id=<?php echo h($post['id']); ?>" style="color: #F33;">削除</a>]
						<?php
						endif;
						?>

						<div style="display:inline-flex">
							<!-- リツイートのフォーム 
						必要な値はtype-hiddenで飛ばし、ファイル移動の起点はbuttonタグ-->
							<form action="RT.php" method="post" style="height: 10px;">
								<input type="hidden" name="message" value="<?php echo $post['message']; ?>">
								<input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
								<input type="hidden" name="member_id" value="<?php echo $_SESSION['id']; ?>">
								<input type="hidden" name="post_member_id" value="<?php echo $post['member_id']; ?>">
								<input type="hidden" name="original_post_id" value="<?php echo $post['original_post_id']; ?>">
								<input type="hidden" name="reply_post_id" value="<?php echo $post['reply_post_id']; ?>">

								<?php
								// リツイートしているコメント確認する→ボタンの横に表示
								//postsテーブルのoriginal_post_idに$post['original_post_id']を当てはめリツイートしているかを確認
								$rt_counts = $db->query("SELECT count(*) as rtcount FROM posts WHERE original_post_id='" . $post['original_post_id'] . "' ");
								$rt_count = $rt_counts->fetch();

								//元のコメントにも回数を表示させたいが、元のコメントは$post['original_post_id']がnullのため、$post['id']を代入して確認する
								$original_counts = $db->query("SELECT count(*) as originalcount FROM posts WHERE original_post_id='" . $post['id'] . "' ");
								$original_count = $original_counts->fetch();

								//自分のリツイートだけ緑にする リツイート用のカウント
								$rt_colors = $db->prepare('SELECT count(*) FROM posts WHERE id=? and member_id=?');
								$rt_colors->execute(array(
									$post['id'],
									$_SESSION['id']
								));
								$rt_color = $rt_colors->fetch();

								//自分のリツイートした元のコメントを確認→アイコンの色を変化させる
								//元コメントが自分のリツイートしたかをを取りに行くSQL｜postのidをoriginnal_post_idに入れるとリツイートのレコードが取れる｜尚且つセッションidを入れることで自分のものだけ取り出せる｜これをカウントし、アイコンの色変化の判定に使用する
								$original_colors = $db->prepare('SELECT count(*) FROM posts WHERE original_post_id=? and member_id=?');
								$original_colors->execute(array(
									$post['id'],
									$_SESSION['id']
								));
								$original_color = $original_colors->fetch();

								//リツイートした際に他人のリツイートのアイコンも変化させるようにしたいのでテーブルのoriginal_post_idカラムに$post['original_post_id']を当てはめて、尚且つ自分のリツイートだということを確認するためにログインユーザーの$_SESSION['id']を入れる
								$others_colors = $db->prepare('SELECT count(*) FROM posts WHERE original_post_id=? and member_id=?');
								$others_colors->execute(array(
									$post['original_post_id'],
									$_SESSION['id']
								));
								$others_color = $others_colors->fetch();


								if ($post['original_post_id']) :
								?>
									<!-- リツイートコメントの場合の色の変化処理↓ -->
									<?php if ($rt_color['count(*)']) { ?>

										<button type="submit" style="background :none; border: none; cursor: pointer;">
											<img src="images/icon_retweet_green.png" width="15px" height="15px">
											<span class="rt_count" style=" font-size:14px; color: rgb(0, 255, 83);">
												<?php echo ($rt_count['rtcount']); ?></span></button>

									<?php } else { ?>
										<!-- ここで他人のリツイートを確認して色の表示を変える-->
										<?php if ($others_color['count(*)']) { ?>
											<button type="submit" style="background :none; border: none; cursor: pointer;">
												<img src="images/icon_retweet_green.png" width="15px" height="15px">
												<span class="rt_count" style=" font-size:14px; color: rgb(0, 255, 83);">
													<?php echo ($rt_count['rtcount']); ?></span></button>
										<?php } else { ?>
											<button type="submit" style="background :none; border: none; cursor: pointer; ">
												<img src="images/icon_retweet.png" width="15px" height="15px">
												<span class="rt_count" style=" font-size:14px; ">
													<?php echo ($rt_count['rtcount']); ?></span></button>
										<?php } ?>

									<?php } ?>

								<?php elseif ($post['original_post_id'] == null) : ?>
									<!-- postsテーブルのoriginal_post_idが空の場合、リツイートではないということが分かる -->

									<!-- 通常コメントの場合の色の変化の処理↓ -->
									<?php if ($original_count['originalcount'] > 0) { ?>

										<?php if ($original_color['count(*)']) { ?>
											<button class="blue" type="submit" style="background: none; border: none; cursor: pointer ; ">
												<img src="images/icon_retweet_green.png" width="15px" height="15px">
												<span class="rt_count" style="font-size:14px; color: rgb(0, 255, 83);">
													<?php echo ($original_count['originalcount']); ?></span></button>
										<?php } else { ?>
											<button type="submit" style="background: none; border: none; cursor: pointer ;">
												<img src="images/icon_retweet.png" width="15px" height="15px">
												<span class="rt_count" style="font-size:14px;">
													<?php echo ($original_count['originalcount']); ?></span></button>

										<?php } ?>

									<?php } else { ?>

										<button type="submit" style="background: none; border: none; cursor: pointer;	">
											<img src="images/icon_retweet.png" width="15px" height="15px">
											<span class="rt_count" style="font-size:14px; visibility:hidden">
												<?php echo ($original_count['originalcount']); ?></span></button>

									<?php } ?>
								<?php endif; ?>
							</form>


							<!-- いいねのフォーム｜必要な値はinput type="hiddenで送信、ファイル移動のトリガーはbuttonタグ-->
							<form action="good.php" method="post" style="margin-left: 20px;">
								<input type="hidden" name="original_post_id" value=<?php echo $post['original_post_id']; ?>>
								<input type="hidden" name="post_id" value=<?php echo $post['id']; ?>>

								<?php
								//いいねカウント 通常コメント表示用
								$iines = $db->prepare('SELECT COUNT(*) as good_count FROM good WHERE post_id=?');
								$iines->execute(array(
									$post['id']
								));
								$iine = $iines->fetch();

								//いいねカウント リツイートコメント表示用
								$rtiines = $db->prepare('SELECT COUNT(*) as rtgood_count FROM good WHERE post_id=?');
								$rtiines->execute(array(
									$post['original_post_id']
								));
								$rtiine = $rtiines->fetch();

								//goodテーブルを検索 自分のいいねだけ赤くする用
								//リツイート用
								$my_iines = $db->prepare('SELECT count(*) FROM good WHERE post_id=? and member_id=?');
								$my_iines->execute(array(
									$post['original_post_id'],
									$_SESSION['id']
								));
								$my_iine = $my_iines->fetch();

								//元コメント用
								$rt_iines = $db->prepare('SELECT count(*) FROM good WHERE post_id=? and member_id=?');
								$rt_iines->execute(array(
									$post['id'],
									$_SESSION['id']
								));
								$rt_iine = $rt_iines->fetch();

								if ($iine['good_count']) :
								?>
									<!-- 通常コメントに対して 自分のいいねだけ赤くする -->
									<?php if ($rt_iine['count(*)']) { ?>
										<button class="red" type="submit" style="background: none; border: none; cursor: pointer; ">❤<span class="goodcount"><?php echo h($iine['good_count']); ?></span></button>
									<?php } else { ?>
										<button type="submit" style="background: none; border: none; cursor: pointer; ">❤<span class="goodcount"><?php echo h($iine['good_count']); ?></span></button>
									<?php } ?>

								<?php elseif ($rtiine['rtgood_count']) : ?>

									<!--リツイートに対して 自分のいいねだけ赤くする  -->
									<?php if ($my_iine['count(*)']) { ?>
										<button class="red" type="submit" style="background: none; border:none; cursor: pointer;">❤<span class="goodcount"><?php echo h($rtiine['rtgood_count']); ?></span></button>
									<?php } else { ?>
										<button type="submit" style="background: none; border:none; cursor: pointer;">❤<span class="goodcount"><?php echo h($rtiine['rtgood_count']); ?></span></button>
									<?php } ?>


								<?php else : ?>
									<button type="submit" style="background: none; border:none; cursor: pointer;">❤</button>
								<?php endif; ?>
							</form>
						</div>
					</p><!-- .day/ -->

				</div>
			<?php endforeach; ?>

			<ul class="paging">
				<?php
				if ($page > 1) {
				?>
					<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
				<?php
				} else {
				?>
					<li>前のページへ</li>
				<?php
				}
				?>
				<?php
				if ($page < $maxPage) {
				?>
					<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
				<?php
				} else {
				?>
					<li>次のページへ</li>
				<?php
				}
				?>
			</ul>
		</div>
	</div>
</body>

</html>