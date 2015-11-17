<?php

class SiteController extends Controller
{    
	
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{   
       /*$redis = new Redis(); 
		$redis->connect("localhost","6379"); 
		$redis->set("key1","Hello world"); 
		echo $redis->get("key1"); */
		echo phpinfo();
		exit;  
		echo "xxx" ;
	  	$this->switchLight(100,100) ;   //灯的算法
	    $this->moveData();
		$this->render('index');
	}
     
    /**
     *算法,计算灯的亮灭
     *@Since 2015.9.4
     *@params   total(总的灯数) times(按动开关的次数)  on off 开关的两种状态   arrayLight
     *@return $result 亮着灯的个数  分别是哪一盏  
    */
    public  function  switchLight($total,$times){
    	 $on = 1 ;
    	 $off = -1;
    	 $hits = -1 ; //按动开关的动作    	 
    	 $arrayLight =array() ;
    	 //灯开始的状态  全灭
    	 for($i=1;$i<=$total;$i++){
    	 	$arrayLight[$i] = $off ;
    	 }    	 
    	 if($times ==0){
    	 	return 0 ;
    	 }

    	 for($i=1;$i<=$times;$i++){
    	    for($j=1;$j<=$total;$j++){
               if(!($j % $i )){   //假如 $j%$i ==0  为真  ，注意此处
               	 $arrayLight[$j] *= $hits ;
               }
    	    }
    	 } 
    	 //将所有亮的灯存入新数组
    	 $newArray= array();
    	 for($i=1;$i<=count($arrayLight);$i++){ 
    	 	 if($arrayLight[$i] == $on){
    	 	 	$newArray[] = $i ;
    	 	 }
    	 }
    	 //统计亮的灯的个数
    	 $message =  "总共有".count($newArray).'盏灯亮着';
    	 $dataReturn['newArray'] = $newArray;
    	 $dataReturn['message'] =$message ;
    	 $result=json_encode($dataReturn);
    	 return $result ;
    }
    
    /**
     *程序新建数据库，数据表，将老数据表中的数据迁移到新表中
     *@Since 2015.9.5
     *@params   total(总的灯数) times(按动开关的次数)  on off 开关的两种状态   arrayLight
     *@return $result true 迁移成功   false 迁移失败
    */
    public  function moveData(){
       //连接数据库
       $link=mysql_connect('localhost','root','');
       //显示所有数据库
       $res =mysql_query('show databases');
       while($row=mysql_fetch_array($res)){
           $dbName[]=$row['Database'];
       }
       if(!in_array('testdata',$dbName)){
       	 //创建数据库
       	mysql_query('create database testdata');       	
       }
       //选择数据库
       mysql_select_db('testdata',$link);
    }
    
	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['adminEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}