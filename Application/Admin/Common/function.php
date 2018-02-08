<?php

header("Content-type:text/html;charset=utf-8");
	/**
	* 删除redis主键
	* @param str $key 删除的主键
	* @return bloor
	*/
	function delRedisKey($key){
		$redis = new \redis();
		$redis->connect(C('REDIS_IP'),C('REDIS_PORT'));
		$redis->auth(C('REDIS_PWD'));
		$redis -> select(1);
		try{
			$redis->del($key);
			return true;
		}catch(Exception $e){
			return false;
		}
	}
	/**
    * 上传单个文件
    * @param $fileid 文件id
    * @param $type 文件类型：mobile检查文件
    * @return 返回文件绝对路径
    */
    function uploadSingleFile($fileid,$type){
        switch($type){
            case 'mobile':
                $rule=array(
                    'maxSize'=>0,
                    'exts'=>array('xls','xlsx'),
                    'savePath'=>'Uploads/excel/'
                );
                break;
            case 'express':
                $rule=array(
                    'maxSize'=>0,
                    'exts'=>array('xls','xlsx'),
                    'savePath'=>'Uploads/excel/'
                );
                break;
            default:
                $rule=array(
                    'maxSize'=>0,// 设置附件上传大小，默认3M
                    'exts'=>array(),// 设置附件上传类型
                    'savePath'=>'Uploads/file'// 设置附件上传目录
                );
                break;
        }
        $config = array(
            'rootPath'   =>  './',                                // 设置根路径
            'saveName'   =>  array('uniqid','')
        );
        $config=array_merge($config,$rule);
        $upload = new \Think\Upload($config);// 实例化上传类   
        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES[$fileid]);    
        if(!$info) {
            // 上传错误提示错误信息        
            return array('code'=>300,'msg'=>$upload->getError());
        }else{
            // 上传成功 获取上传文件信息         
            return array('code'=>200,'file'=>$info['savepath'].$info['savename']);    
        }
    }
	/**
     * 读取excel文件到Array
     * @param  string $file excel文件路径
     * @return array        excel文件内容数组
     */
    function readExcel($file){
        // 判断文件是什么格式
        $type = pathinfo($file); 
        $type = strtolower($type["extension"]);
        if($type == 'csv')
            $type = 'CSV';
        else if($type == 'xls')
            $type = 'Excel5';
        else if($type == 'xlsx')
            $type = 'Excel2007';
        else
            $type = 'Excel5';
        ini_set('max_execution_time', '0');
        Vendor('PHPExcel.PHPExcel');
        // 判断使用哪种格式
        $objReader = \PHPExcel_IOFactory::createReader($type);
        $objPHPExcel = $objReader->load($file); 
        $sheet = $objPHPExcel->getSheet(0); 
        // 取得总行数 
        $highestRow = $sheet->getHighestRow();     
        // 取得总列数      
        $highestColumn = $sheet->getHighestColumn(); 
        //循环读取excel文件,读取一条,插入一条
        $data=array();
        //从第一行开始读取数据
        for($j=1;$j<=$highestRow;$j++){
            //从A列读取数据
            for($k='A';$k<=$highestColumn;$k++){
                // 读取单元格
                $data[$j][]=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            } 
        }  
        return $data;
    }


    function log_1($str, $fileName='log.txt'){
        $root=dirname(THINK_PATH).'/Uploads';
        if(!is_dir($root)){
            mkdir($root);
        }
        $rootLog = dirname(THINK_PATH).'/Uploads/log';
        if(!is_dir($rootLog)){
            mkdir($rootLog);
        }
        $currentDir=$rootLog.'/'.date('Y-m-d',time());
        if(!is_dir($currentDir)){
            mkdir($currentDir);
        }
        $file=$currentDir.'/'.$fileName;
        file_put_contents($file,date('Y-m-d H:i:s',time()).'：'.$str."\r\n",FILE_APPEND);
    }

    function curl_post($url,$data = null,$header=null){
        $ch = curl_init();
      if($header)
          curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_URL, $url);   //设置访问的url地址 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //timeout on connect
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout on response
        /* if($data){
             curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
             curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
            
         }*/
        $data &&  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $tmpInfo;
    }

    function curl_get($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);   //设置访问的url地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); //timeout on connect
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); //timeout on response
        
        $tmpInfo = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        return $tmpInfo;
    }