<?php
include "types.inc";
$in = stream_get_contents(STDIN);

preg_match_all("/struct[^{]*{([^}]*)}\W*(\w*)/s", $in, $m1, PREG_SET_ORDER);
foreach($m1 as $tk){
  list(,$fields,$name) = $tk;
  echo "const $name = new ctypes.StructType(\"$name\", [\r\n";
  echo list_tns($fields, 1); echo "\r\n";
}

preg_match_all("/(\w+)\s*(\w+)\s*\((.*?)\)\s*;/s", $in, $m1, PREG_SET_ORDER);
foreach($m1 as $tk){
  list(,$ret,$name, $args) = $tk;
  $rt = get_tn("$ret $name");
  $tns = list_tns($args, 0);
  echo "const $name = lib.declare(\"$name\", \r\n  ctypes.winapi_abi,\r\n";
  echo "  {$rt[0]}" . ($tns?",":");") . " // return\r\n";
  echo $tns."\r\n";
}

preg_match_all("/^\s*#define\s*(\w+)\s*(\w+)/mi", $in, $m1, PREG_SET_ORDER);
foreach($m1 as $tk){
  list(,$name,$value) = $tk;
  echo "const $name = $value;\r\n";
}

function list_tns($str, $fmt){
  preg_match_all("/(\w+\W+\w+\s*)($|;|,)/s", $str, $m1);
  $tns = array(); $res = "";
  foreach($m1[1] as $ln){
    $tn = get_tn($ln);
    if ($tn) $tns[] = $tn;
  }
  for($i = 0; $i < count($tns); $i++){
    $last = ($i == count($tns)-1); 
    list($t, $n) = $tns[$i]; 
    if ($fmt == 0) $res .= "  $t".($last?");":",") . " // $n\r\n";
    if ($fmt == 1) $res .= "  {'$n': $t}".($last?" ]);":",")."\r\n";
  }
  return $res;
}

function get_tn($str){
  global $TYPES;
  if(!preg_match("/^(.*?)(\w+)\s*(\**)\s*(\w+)\s*$/s", $str, $m1)) return false;
  list (, $pfx, $t, $star, $name) = $m1;
  $type = strtolower($t);
  if (preg_match("/unsigned/", $pfx)) $type = "unsigned_" . $type;
  if (preg_match("/signed/", $pfx)) $type = "signed_" . $type;
  preg_match("/^p?(.*?)(_ptr)?$/", $type, $m1); $typeP = $m1[1];
  if (isset($TYPES[$type])) $rt = "ctypes.".$TYPES[$type];
  elseif (isset($TYPES[$typeP])) $rt = "ctypes.".$TYPES[$typeP] . ".ptr";
  else $rt = "??$t??";
  $rt .= str_repeat(".ptr", strlen($star)); 
  return array ($rt, $name);
}

?>