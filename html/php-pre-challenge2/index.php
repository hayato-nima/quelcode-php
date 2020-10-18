<?php
$array = explode(',', $_GET['array']);

// 修正はここから

$count = count($array);
for ($i = 0; $i < $count; $i++) {//大きな値を確定させていくループ
  for ($j = 0; $j < ($count - 1 - $i); $j++) {//上のループで確定させた数字を除いてループ
    if ($array[$j + 1] < $array[$j]) {//隣り合う要素との大小判定
        $tmp = $array[$j + 1];//$tmp 値の交換用の入れ物
        $array[$j + 1] = $array[$j];
        $array[$j] = $tmp;
    }
}
}

// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";
