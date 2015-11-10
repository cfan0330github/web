<?php
require_once("function.php");
require_once("config.php");
//echoStr(detectEncoding("detect.php"),"code");

/*templete query arg
$args_get=array(
	"time_st"	=>	"",
	"time_ed"	=>	""
);
$args_post=array();
fillArgs($args_get,$_GET);
*/
//$res=array();
//Section A  read dir file--->get data
function getCsvTop($csvfile){
	global $domext;

	if(!preg_match("/.*\.csv$/i",$csvfile)) return;
	echoStr($csvfile,"load file");
	$file = fopen($csvfile,"r");
	$csvtxt=array();
	while(!feof($file)){
  		$a=fgetcsv($file);break;
	}
	
	if(count($a)<2) return "bad file content";
	foreach($domext as $key=>$value){
		echoStr($key."|".count($value)."|".count(array_intersect($value,$a)),"result");
		if(count(array_intersect($value,$a))==count($value)){
			echoStr($key,"domain");
			return $key;
		}
	}

}

$home=getcwd();
$files=getDirFile($home."/test",'/');
foreach($files as $value){
	$res=getCsvTop($value);
	if($res)	echoStr($res,"result");
	
}



?>
