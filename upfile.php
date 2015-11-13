<?php
require_once("function.php");
require_once("config.php");
require_once("punycode.php");
require_once("conn.php");
require_once("upload.php");

@ini_set('memory_limit','256M');

function getgbk($str){
	return iconv("UTF-8", "GB2312//IGNORE", $str);
}

/*
	getdomstr
*/
function getCsvdomainKey($str){
	foreach($str as $key=>$value){
		if(isFormat($value)) return $key;
	}
	return;
}

function getCsvdomainStr($str){
	foreach($str as $value){
		if(isFormat($value)) return $value;
	}
	return;
}

function getCsvTop($csvfile){
	$title=array();			//title
	$founddom="";			//is found domain
	$founddomkey=0;
	$res=array();
	
	echoStr($csvfile,"load file");
	if(!preg_match("/.*\.csv$/i",$csvfile)) return "bad file format";
	$file = fopen($csvfile,"r");
	$csvtxt=array();
	while(!feof($file)){
		/*
			found title
		*/		
		if(!count($title)){			
			$title=fgetcsv($file);
			if(count($title)<2) return "bad file content";
			continue;
		}
		$a=fgetcsv($file);
		/*
			found domain
		*/
		$founddom=strtolower(getCsvdomainStr($a));
		$founddomkey=getCsvdomainKey($a);
		if(!$founddom) continue;
		if(count($title) && $founddom) {
			$ext=getDomainExt($founddom);
			return array('ext'=>$ext,'title'=>$title,'key'=>$founddomkey);
		}
	}
}


function filterCsvTop($str){
	global $domext;
	$extnum=array();
	/*
		compare template
	*/
	if(array_key_exists($str['ext'],$domext)){
		//for debug print_r(array_flip($domext[$ext]));
		foreach($str['title'] as $key=>$value){
			if(array_key_exists('filter',$domext[$str['ext']])){
				preg_match($domext[$str['ext']]['filter'],$value,$matches);
				//for debug echoStr($matches[$domext[$ext]['dumpmatch']],"str");
				if(array_key_exists($matches[$domext[$str['ext']]['dumpmatch']],array_flip($domext[$str['ext']]))){
						$extnum[]=$key;
				}	
			}else{
				if(array_key_exists($value,array_flip($domext[$str['ext']]))){
						$extnum[]=$key;
				}
			}
		}
		$extnum['key']=$str['key'];
		return $extnum;
	}else{
		die("not exist domain ext: ".$str['ext']."\n");
	}
}


function getCsvData($value,$filenum){
	$O_Punycode=new Punycode();
	$file = fopen($value,"r");
	$csvtxt=array();
	$domkey=$filenum['key'];
	while(!feof($file)){
		$a=fgetcsv($file);
		//for debug print_r(array_flip($filenum));break;
		$csv=array();
		foreach(array_flip($filenum) as $key=>$value){		
				$csv[]=$a[$key];
		}
		$csv['cover']='0';
		if(!isFormat($a[$domkey])){
			$csv['cover']='1';
			$csvtxt[$O_Punycode->encode($a[$domkey])]=$csv;
			continue;
			//for debug echoStr($O_Punycode->encode($a[$domkey])."|domain: ".$a[$domkey],"idn");
		}
		$csvtxt[strtolower($a[$domkey])]=$csv;
	}
	fclose($file);
	return $csvtxt;
}
/*========================================function end===============================================*/


//echoStr(detectEncoding("detect.php"),"code");

/*templete query arg*/
$args_post=array(
	"time_st"	=>	"",
	"time_ed"	=>	"",
	"file_name"	=>""
);

fillArgs($args_post,$_POST);	
	
$name=$args_post['file_name'];
if(!$args_post['time_st']){
	die("ÊäÈëÊ±¼ä");
}elseif(!$args_post['time_ed']){
	$args_post['time_ed']=$args_post['time_st'];
}else{
	
}

if(!empty($_FILES)){
	$oUp=new upLoad($_FILES['upfile'], 1024*1024*30, getcwd().'/test1/',$name);
}
$allres=array();
$ext="";
$home=getcwd();
$files=getDirFile($home."/test1",'/');
if(!count($files)) die("no file");
$filenum=array();
foreach($files as $value){
	if(!file_exists($value)) die("no file,path=".$value);
	if(checkFileType($value)){
		echoStr($value,"status:ok");
	}else{
		die("file type err.");
	}
}

/*===================main=================================*/

?>