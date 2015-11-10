<?php
require_once("config.php");
require_once("conn.php");
require_once("punycode.php");


/*	!!!	important	!!!! 
	xyzע����ļ� ����״̬
	Auto-Renewal
	Registration
	Renewal
	
	select distinct Ψһ
	ģ�����simulate_countlist  ����״̬u_memo
	TRANSFER
	RENEW
	DELETE

	������domainlist  
	islocal (1 DNS������ 0 ��������)
	bizcnorder	��˾ע��ӿ���  eg west 10.
	proid		����������׺	eg domxyz 
	s_memo  �������������,������ʾ����������ʽ�������ַ���
		
*/


/*
	��ȡxyz csv�ļ�,ȡ�����������Ӧ�ļ�¼
	tag�������ַ������ڵ�����
	line��Ҫȡ���ö��У���������Զ���
*/
function unicode2utf8($str){
        if(!$str) return $str;
        $decode = json_decode($str);
        if($decode) return $decode;
        $str = '["' . $str . '"]';
        $decode = json_decode($str);
        if(count($decode) == 1){
                return $decode[0];
        }
        return $str;
}


function isFormat($strdomain){
        if(!preg_match("/^([-a-z0-9]{2,100})\.([a-z\.]{2,8})$/i", $strdomain)) {
                return false;
        }
    return true;
}  

function getCsv($csvfile,$tag,$line=0){
	$file = fopen($csvfile,"r");
	$csvtxt=array();
	$i=1;
	while(!feof($file))
	{
  		$a=fgetcsv($file);
  	  /*�ֶζ�Ӧ  Item Date | Transaction Type | Years | Item GBP Amount */
  		$csvtxt["$a[$tag]"]=array(
  			'date'		=>	$a[1],
  			'type'		=>	$a[8],
  			'year'		=>	$a[9],
  			'count'		=>	$a[10]
  		);
  		/*for debug
  		if(strcasecmp($a[8],"Registration")){
				echo "domain: ".$a[$tag].", type: ".$a[8]."\n";
			}
  		*/
  		
  		if($line){
  			if($i>=$line){
  				break;
  			}
  			$i++;
  		}
	}
	fclose($file);
	return $csvtxt;
}

/*
	��ȡtop csv�ļ�,ȡ�����������Ӧ�ļ�¼
	tag�������ַ������ڵ�����
	line��Ҫȡ���ö��У���������Զ���
*/
function getCsvTop($csvfile,$tag,$line=0){
	$O_Punycode=new Punycode();
	$file = fopen($csvfile,"r");
	$csvtxt=array();
	$i=0;
	while(!feof($file))
	{
  		$a=fgetcsv($file);
  		$i++;				//��һ�в�ȡ
  		if($i<=1) continue;
  		
  	  /*�ֶζ�Ӧ  tag(domain)=4|Operation Date | Operation Type | Years(Unit) | Amount(Fee) */
  	  	$strdomain=iconv("UTF-8", "GB2312//IGNORE", $a[$tag]);
  	  	
  		if(!isFormat($strdomain)){
				$strdomain=$O_Punycode->encode(unicode2utf8($a[$tag]));
				//echoStr($strdomain,"domain");
				$csvtxt[$strdomain]=array(
  				'date'		=>	$a[9],
  				'type'		=>	iconv("UTF-8", "GB2312//IGNORE", $a[2]),
  				'year'		=>	$a[5],
  				'count'		=>	$a[6],
  				'cover'		=>	1
  				);
			}else{
				$csvtxt[$strdomain]=array(
  				'date'		=>	$a[9],
  				'type'		=>	iconv("UTF-8", "GB2312//IGNORE", $a[2]),
  				'year'		=>	$a[5],
  				'count'		=>	$a[6],
  				'cover'		=>	0
  				);
			}	
  			
  		/*for debug*/
  		//if(strcasecmp($a[8],"Registration")){
  		 
		//		echo "domain: ".iconv("UTF-8", "GB2312//IGNORE", $a[$tag]).", type: ".iconv("UTF-8", "GB2312//IGNORE", $a[2])."\n";
		//	}
  		
  		if($line){
  			if($i>=$line){
  				break;
  			}
  			$i++;
  		}
  		
	}
	fclose($file);
	return $csvtxt;
}
/*
	��������Ƿ��������������ȡ����Ӧ����������Ϣ,û�з��ؿ�
	res��������������
	key�������ַ���
*/
function chkDomainIn($res,$key){
	if(array_key_exists($key,$res)){
		return ($res[$key]);
	}
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
		$i++;
		if($i>2){
			$res[]=$datasrc.$tag.$files;
		}
	}
	if(!count($res)){
		return;
	}
	return $res;
}

function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function comBine($allres,$res){
	if(!is_array($res)){
		retrun;
	}
	foreach($res as $key => $ele){
		$allres[$key]=$ele;
	}
	return;
}

function memUsage(){
	echo convert(memory_get_usage(true))."\n";
}

function echoStr($str,$keystr){
	echo $keystr.": ".$str."\n";
}


/*=====================================================function end=====================================================================*/

$strdomain='273818.top';		//for debug only

$allres=array();
$O_Punycode=new Punycode();

$datestr_st="2015-11-09";
$datestr_ed="2015-11-09";
$register="west11";    //top:west11|ren:west12|xyz:west10


//Section A   getcsvfile   domain-cover-idn
$files=getDirFile("test");
foreach($files as $src){
	echo "load file: ".$src."\n";
	$res=getCsvTop($src,4);
	combine(&$allres,$res);
}

//print_r((chkDomainIn($allres,$strdomain)));

//Section B  query simulate_countlist
memUsage();

$sql="select u_in,u_out,u_memo,u_date from simulate_countlist where u_register='".$register."' and datediff(s,'".$datestr_st." 00:00:00 ',u_date)>=0 and datediff(s,'".$datestr_ed." 23:59:59',u_date)<=0";
//$sql="select u_in,u_out,u_memo,u_date from simulate_countlist where u_register='".$register."' and datediff(d,'".$datestr_st."',u_date)=0";
$stmt = sqlsrv_query($conn,$sql);
if(!$stmt) die(print_r(sqlsrv_errors(),true));
$dbsimres=array();
memUsage();

$paten="/\)(\((.*)\)){0,1}\((.*\.[a-z\.]{2,8})\)(\((.*)\)){0,1}/";
while ($row = sqlsrv_fetch_array($stmt)) {
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


//Section C query domainlist


memUsage();
//test chk
$dbonly=array();	//ֻ�����ݿ���
$csvonly=array();	//ֻ���ϼ���
$strkey=$strdomain;
echoStr(count($allres),"count csv");
echoStr(count($dbsimures),"count simudb");
$interDomain=array_intersect_key($allres,$dbsimures);
echoStr(count($interDomain),"count inter");
$dbonly=array_diff_key($dbsimures,$interDomain);
echoStr(count($dbonly),"total dbonly domain");
$csvonly=array_diff_key($allres,$interDomain);
echoStr(count($csvonly),"total csvonly domain");
/*
//ֻ�����ݿ���
$i=1;
foreach($dbonly as $key => $value){
	if(array_key_exists($key,$csvonly)){
		die("Err.\n");
	}
	echoStr($key,"ֻ�����ݿ��� domain");
	print_r($value);
	if($i>3) break;
	$i++;
}
*/

$sql="select strdomain from domainlist where strdomain='";
$flog=fopen(date('y-n-j')."-taskdomain.log","a+");
//ֻ���ϼ���
foreach($csvonly as $key => $value){
	if(!$key) continue;
	if(array_key_exists($key,$dbonly)){
		die("Err.\n");
	}
	$strsql=$sql.$key."'";
	if($key['cover']){
		$strsql.=" or s_memo='".$key."'";
	}
	
	$stmt = sqlsrv_query($conn,$strsql);
	//echo echoStr($strsql,"sql:");
	if(!$stmt) die(print_r(sqlsrv_errors(), true));
	$row = sqlsrv_fetch_array($stmt);
		if(!$row){
			if(!strcasecmp($value["type"],"������������")){
				fwrite($flog,$key." Registration\n");
			}else if(!strcasecmp($value["type"],"Renewal")){
				fwrite($flog,$key." ".$value["type"]."n");
			}else{
				echoStr($key."|".$value["type"],"ֻ���ϼ��� domain:type");
			}
		}
}

memUsage();
unset($stmt);
unset($dbpdo);
fclose($flog);


$topdomstat=array(
	"������������"	=>	"Registration",
	"��������"		=>	"Renewal",
	"����ת�Ʒ���"	=>	"transfer"
);

?>