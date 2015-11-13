<?php
class upLoad{
		private $fileName;//�ļ���
		private $fileType;//�ļ�����
		private $fileSize;//�ļ��Ĵ�С
		private $fileTemp;//��ʱ�ļ�
		private $error;//�ϴ��Ƿ��д���
		private $maxSize;//���������ϴ��ļ��ߴ�
		private $directory;//�ļ����մ洢Ŀ¼
		private $newName;//�涨�洢�����ݿ���ļ���
		private $arrType=array();//���������ϴ����ļ�����
		private $sMsg;//�ļ��ϴ�״̬��Ϣ
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
				$this->sMsg.=$msg?"{$this->newName}{$msg}":"{$this->newName}�ϴ��ɹ�";
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
								return '�޷��ҵ�'.$this->directory.'Ŀ¼';
							}
						}
					}else{
						return '�ϴ���֧��'.$this->fileType.'����';
					}
				}else{
					return '�ϴ�ʧ�ܣ���ȷ������ϴ��ļ������� '.$this->maxSize;
				}
			}else if($this->error==1){
				return '�ϴ��ļ�����PHP';
			}else if($this->error==2){
				return '�ϴ��ļ�����HTML';
			}else if($this->error==3){
				return '�ļ�ֻ�в��ֱ��ϴ�';
			}else if($this->error==4){
				return 'û���ļ����ϴ�';
			}else if($this->error==5){
				return '�Ҳ�����ʱ�ļ���';
			}else if($this->error==7){
				return '�ļ�д��ʧ��';
			}else {
				return 'δ֪�����������ϴ��ļ�';
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
				return '�ϴ�ʧ�ܣ���ȷ������ϴ��ļ������� '.$this->maxSize.'k���ϴ�ʱ�䳬ʱ';
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