<?php
//all useful func
/*
	������ʽidn
*/
function isFormat($strdomain){
        if(!preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $strdomain)) {
                return false;
        }
    return true;
}  

/*
	ȡ������׺
*/
function getDomainExt($domain){
	$patenext="/\.([a-z]{2,8})$/i";
	preg_match($patenext,$domain,$matches);
	foreach($matches as $value){};
	return $value;
}


/*
	�������
*/
function fillArgs(&$args_,$_){
	if(!count($_)) return;
	foreach($args_ as $key=>$value){	
		if(isExistArgs($_,$key)){	
			$args_[$key]=$_[$key];
		}
	}
	return;
}


function isExistArgs($_,$key){
	return array_key_exists($key,$_)?1:0;
}

/*
	����ļ�����
*/
function detectEncoding($file) {
     $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
     $str = file_get_contents($file);
     foreach ($list as $item) {
         $tmp = mb_convert_encoding($str, $item, $item);
         if (md5($tmp) == md5($str)) {
             return $item;
         }
     }
     return null;
}


function echoStr($str,$keystr){
	echo $keystr.": ".$str."\n";
}


function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


function memUsage(){
	echo convert(memory_get_usage(true))."\n";
}

/*
	��ȡĿ¼���ļ����������ļ���������,���û���ļ����ؿ�
	datasrc���ļ�Ŀ¼
*/
function getDirFile($datasrc,$tag='\\'){
	$res=array();
	if(!is_dir($datasrc))
		return;
	$fdir=opendir($datasrc);
	$i=0;
	while(($files=readdir($fdir))!== false){
		if(preg_match("/^[.]{1,2}$/",$files)) continue;
		if(is_file($datasrc.$tag.$files))
					$res[]=$datasrc.$tag.$files;
	}
	if(!count($res)){
		return;
	}
	return $res;
}

?>
