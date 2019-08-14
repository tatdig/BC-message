<?php
function utf8StringToHexString($string) {
    $nums = array();
    $convmap = array(0x0, 0xffff, 0, 0xffff);
    $strlen = mb_strlen($string, "UTF-8");
    for ($i = 0; $i < $strlen; $i++) {
      $ch = mb_substr($string, $i, 1, "UTF-8");
      $decimal = substr(mb_encode_numericentity($ch, $convmap, 'UTF-8'), -5, 4);
      //$nums[] = "&#x" .base_convert($decimal, 10, 16). ";";
      $nums[] = base_convert($decimal, 10, 16);
    }
    return implode("", $nums);
}

function utf8StringToHexArray($string) {
$nums = array();
$convmap = array(0x0, 0xffff, 0, 0xffff);
$strlen = mb_strlen($string, "UTF-8");
for ($i = 0; $i < $strlen; $i++) {
$ch = mb_substr($string, $i, 1, "UTF-8");
$decimal = substr(mb_encode_numericentity($ch, $convmap, 'UTF-8'), -5, 4);
$nums[] = "&#x" .base_convert($decimal, 10, 16). ";";
}
return $nums;
}

function utf8StringToDecimalArray($string) {
  $nums = array();
  $convmap = array(0x0, 0xffff, 0, 0xffff);
  $strlen = mb_strlen($string, "UTF-8");
  for ($i = 0; $i < $strlen; $i++) {
    $ch = mb_substr($string, $i, 1, "UTF-8");
    $nums[] = mb_encode_numericentity($ch, $convmap, 'UTF-8');
  }
return $nums;
}

function hexdump($str) {
    $hex = '';
    for ($i = 0, $n = strlen($str); $i < $n; $i++) {
        $byte = $str[$i];
        $byteNo = ord($byte);
        $hex .= sprintf('%02X', $byteNo);
    }
    return trim($hex);
}

?>
