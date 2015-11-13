<?php
class upLoad{
		private $fileName;//文件名
		private $fileType;//文件类型
		private $fileSize;//文件的大小
		private $fileTemp;//临时文件
		private $error;//上传是否有错误
		private $maxSize;//允许的最大上传文件尺寸
		private $directory;//文件最终存储目录
		private $newName;//规定存储在数据库的文件名
		private $arrType=array();//构造允许上传的文件类型
		private $sMsg;//文件上传状态信息
		public function __construct($fileField,$maxSize,$directory,$name=''){
			$this->maxSize=$maxSize;
			$this->directory=$directory;
			$this->setArray();
			//foreach($fileField['name'] as $key=>$filename){
				$this->newName=empty($name)?$fileField['name']:$name;
				$this->fileName=$name;
				$this->fileTemp=$fileField['tmp_name'];
				$this->error=$fileField['error'];
				$this->fileSize=$fileField['size'];
				$this->fileType=$fileField['type'];
				$msg=$this->uploading();
				$this->sMsg.=$msg?"{$this->newName}{$msg}":"{$this->newName}上传成功";
				$this->sMsg.="<br/>";
			//}
			echo $this->sMsg;
		}
		public function uploading(){
			if($this->error==0){
				if($this->fileSize <= $this->maxSize){
					if(in_array($this->fileType,$this->arrType)){
						if(file_exists($this->directory)){
							return $this->moveuploadedfile();
						}else {
								if(mkdir($this->directory,0775)){
								return	$this->moveuploadedfile();
								}else{
								return '无法找到'.$this->directory.'目录';
							}
						}
					}else{
						return '上传不支持'.$this->fileType.'类型';
					}
				}else{
					return '上传失败，请确认你的上传文件不超过 '.$this->maxSize;
				}
			}else if($this->error==1){
				return '上传文件过大PHP';
			}else if($this->error==2){
				return '上传文件过大HTML';
			}else if($this->error==3){
				return '文件只有部分被上传';
			}else if($this->error==4){
				return '没有文件被上传';
			}else if($this->error==5){
				return '找不到临时文件夹';
			}else if($this->error==7){
				return '文件写入失败';
			}else {
				return '未知错误，请重新上传文件';
			}
		}
		private function moveuploadedfile(){
			$ext=substr($this->fileName, strrpos($this->fileName, '.'));
			$name=$this->newName.$ext;
			$dir=str_replace('//', '/', $this->directory.'/'.$name);
			$dir=str_replace('\\', '/', $this->directory.'/'.$name);
			if(move_uploaded_file($this->fileTemp,$dir)){
				return false;
			}else{
				return '上传失败，请确认你的上传文件不超过 '.$this->maxSize.'k或上传时间超时';
			}
		}
		private function setArray(){
			$this->arrType=array(
								'multipart/form-data',
								'application/octet-stream',
								'application/vnd.ms-excel'
								);
		}
	}
?>