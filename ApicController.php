<?php

class ApicController extends Controller
{
	public function filters(){
		return array(
			'accessControl',
		);
	}

	public function accessRules(){
		return array(

			array(
				'allow',
				'users'	=> array('@')
			),
			array(
				'deny',
				'users' => array('*')
			),


		);
	}

	public function actionIndex($fall=false)
	{
		$apicModel = Apic::model();
		$criteria = new CDbCriteria();//AR的另一种写法
		$total = $apicModel->count($criteria);//统计总条数
		$pager = new CPagination($total);//实例化分页类
		$pager->pageSize = 5;//每页显示多少条
		$pager->applyLimit($criteria);//进行limit截取
		$criteria->order='img_id desc';
		$apicInfo = $apicModel->findAll($criteria);//查询截取过的数据
		$data = array('apicInfo'=>$apicInfo,'pages'=>$pager);

		if($fall == false){
			$this->renderPartial('index',array('data'=>$data));
		}else{
			$this->render('index',array('data'=>$data));
		}
	}

	public function actionApicAdd(){
		$apicModel = new Apic();
		if(!empty($_POST['Apic'])){
			$apicModel->attributes=$_POST['Apic'];
			if($apicModel->validate()){
				//生成文件夹名
				$dirname = '/images/upload/'.date("Ymd",time()).'/';

				//生成文件夹绝对路径
				$dir = dirname(Yii::app()->BasePath).$dirname;

				//创建文件夹
				if(!is_dir($dir)){
					if(!@mkdir($dir)){
						echo '创建文件夹没有权限';
						exit;
					};
				}
				//上传文件
				$image  = CUploadedFile::getInstance($apicModel,'img_file');
				if($image){
					//生成文件名
					$img = time() . mt_rand(0,999). '.' . $image->extensionName;//生成文件名
					//保存文件
					$image->saveAs($dir.$img);

					//缩略图文件名
					$simgname =date("Ymd",time()) .time(). mt_rand(0,999);
					//调用缩略图类
					$resizeimage=new ResizeImage($dir.$img, '218', '112', '1', $dir.$simgname.".".$image->extensionName);

					//大图


					$resizeimageS=new ResizeImage($dir.$img, '800', '600', '1', $dir.$simgname.'_big.'.$image->extensionName);

					//删除源文件
					if(is_file($dir.$img)){
						@unlink($dir.$img);
					}

					$apicModel->img_file=$dirname.$simgname.".".$image->extensionName;

					if($apicModel->save()){
						$this->actionIndex(true);
					}
				}else{
					$this->render('apicAdd',array('apicModel'=>$apicModel));
					exit;
				}
			}else{
				$this->render('apicAdd',array('apicModel'=>$apicModel));
				exit;
			}

		}
		$this->renderPartial('apicAdd',array('apicModel'=>$apicModel));
	}
}