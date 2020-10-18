<?php
$array = explode(',', $_GET['array']);

// 修正はここから
$count = count($array);
for ($i = 0; $i < $count; $i++) {
  for ($j = 0; $j < ($count - 1 - $i); $j++) {
    if ($array[$j + 1] < $array[$j]) {
        $tmp = $array[$j + 1];
        $array[$j + 1] = $array[$j];
        $array[$j] = $tmp;
    }
}
}

// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";
