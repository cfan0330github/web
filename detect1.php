<?php
require_once("function.php");
require_once("config.php");
require_once("punycode.php");
require_once("conn.php");
@ini_set('memory_limit','256M');
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

/*
$file = fopen(getcwd().'/test/1.dat',"r");
	$csvtxt=array();
	while(!feof($file)){
		$a=fgetcsv($file,"|");
		if(1==count($a)){
			$str=preg_replace('/[-0-9a-zA-Z\.]+/','',$a[0]);
			echo $str."\n";
		}
		break;
	}
	fclose($file);
//	echo detectEncoding(getcwd().'/test/1.dat');
*/

$datestr_st="2015-11-11";
$datestr_ed="2015-11-11";
$register=$domext['xyz']['register'];

//Section A  read dir file--->get data
$allres=array();
$ext="";
$home=getcwd();
$files=getDirFile($home."/test",'/');
$filenum=array();
foreach($files as $value){
	$res=getCsvTop($value);
	if(!is_array($res)) {
		echoStr($res,"result");
		continue;
	}
	$ext=$res['ext'];
	$filenum=filterCsvtop($res);
	if(!count($filenum)) die("no filenum data");
	$allres=getCsvData($value,$filenum);
	//print_r($allres);
}

//Section B query db--->get data
if(!isset($domext[$ext]['delay'])){

	$sql="select u_in,u_out,u_memo,u_date from simulate_countlist where u_register='".$domext[$ext]['register']."' and datediff(s,'".$datestr_st." 00:00:00 ',u_date)>=0 and datediff(s,'".$datestr_ed." 23:59:59',u_date)<=0";
}else{
	preg_match("/([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$/",$datestr_ed,$matches);
	$datastr=array(
		'year'=>$matches[1],
		'month'=>$matches[2],
		'day'=>$matches[3]
	);
	$datestr_ed=$datastr['year']."-".$datastr['month']."-".($datastr['day']+1);
	$sql="select u_in,u_out,u_memo,u_date from simulate_countlist where u_register='".$domext[$ext]['register'];
	$sql.="' and datediff(s,'".$datestr_st." ".$domext[$ext]['delay']." ',u_date)>=0 and datediff(s,'";
	$sql.=$datestr_ed." ".$domext[$ext]['delay']." ',u_date)<=0";
}
$stmt=mssql_query($sql,$conn);
if(!$stmt) die("query err");
$dbsimres=array();
memUsage();
$paten="/\)(\((.*)\)){0,1}\((.*\.[a-z\.]{2,8})\)(\((.*)\)){0,1}/";
while($row=mssql_fetch_array($stmt)){
		preg_match($paten,$row['u_memo'],$matches);
		if($matches['3']){
			$type=is_string($matches['2'])?$matches['2'] : $matches['5'];
			//for debug 
			//if(strcasecmp($type,"Registration")){
			//	echo "domain: ".$matches['3'].", type: ".$type."\n";
			//}
			$strdomain=$matches['3'];
			if(!isFormat($strdomain)){
				$strdomain=$O_Punycode->encode(unicode2utf8($strdomain));
				//echoStr($strdomain,"domain");
			}
			$dbsimures[$matches['3']]=array(
				'u_in'		=> 	$row['u_in'],
				'u_out'	 	=> 	$row['u_out'],
				'u_date'	=>	$row['u_date'],
				'type'		=>	$type
			);
		}
	}
mssql_free_result($stmt);

//memUsage();
//test chk
$dbonly=array();	//只有数据库有
$csvonly=array();	//只有上级有
$strkey=$strdomain;
echoStr(count($allres),"count csv");
echoStr(count($dbsimures),"count simudb");
$interDomain=array_intersect_key($allres,$dbsimures);
echoStr(count($interDomain),"count inter");
$dbonly=array_diff_key($dbsimures,$interDomain);
echoStr(count($dbonly),"total dbonly domain");
$csvonly=array_diff_key($allres,$interDomain);
echoStr(count($csvonly),"total csvonly domain");



$sql="select strdomain from domainlist where strdomain='";
$flog=fopen(date('y-n-j')."-taskdomain.log","a+");
//只有上级有
foreach($csvonly as $key => $value){
	if(!$key) continue;
	if(array_key_exists($key,$dbonly)){
		die("Err.\n");
	}
	$strsql=$sql.$key."'";
	if($value['cover']){
		$strsql.=" or s_memo='".$key."'";
	}
	
	$stmt = mssql_query($strsql,$conn);
	//echo echoStr($strsql,"sql:");
	if(!$stmt) die("query err");
	$row = mssql_fetch_array($stmt);
		if(!$row){
			$strdata="";
			foreach($value as $data){
				$strdata.="|".$data;
			}
			echoStr($strdata,"result");
			/*if(!strcasecmp($value["type"],"域名创建费用")){
				fwrite($flog,$key." Registration\n");
			}else if(!strcasecmp($value["type"],"Renewal")){
				fwrite($flog,$key." ".$value["type"]."n");
			}else{
				echoStr($key."|".$value["type"],"只有上级有 domain:type");
			}*/
		}
}
mssql_free_result($stmt);
//memUsage();
?>
