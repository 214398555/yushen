<?php
namespace Common\Controller;
use Common\Controller\BaseController;

//require_once(__ROOT__.'/vendor/paythird/ApiConstants.php');
//require_once(__ROOT__.'/vendor/paythird/DigestUtil.php');
/**
 * admin 基类控制器
 */
class AdminBaseController extends BaseController{
	/**
	 * 初始化方法
	 */
	public function _initialize(){
        
        parent::_initialize();
   
		$auth=new \Think\Auth();
        $rule_name=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		if (!$_SESSION['user']['id']) {
			echo '<script>window.top.location.href="/Admin/Login/logout";</script>';exit;
		}
		$result=$auth->check($rule_name,$_SESSION['user']['id']);
		if(!$result){
            $referer = $_SERVER['HTTP_REFERER']?$_SERVER['HTTP_REFERER']:'';
            if(strpos(strtolower($referer),'login')){
                $this->error('您没有权限访问',U('Admin/Login/logout'));
            } else{
                $this->error('您没有权限访问');
            }
		}
	}
	
	
	/*以下方法为内部封装的公用方法*/
    /**
     * 发起第三方请求.
     *
     * @param  string  $service  本次请求的服务名称
     * @param  array  $data  本次请求的业务参数
     * @return resultCode
     */
    public function startRequest($service,$data,$orderNo)
    {
        writeLog('======Begin：'.$service.'=========');
        $params = $this->initParams($service,$orderNo);
        foreach ($data as $key => $val) {
            $params[$key]=$val;
        }
        // var_dump($params);
        $responseMessage = $this->exchange($params);
        writeLog('======Finish：'.$service.'=========');
        // echo $responseMessage;
        // echo "\r\n";
        //将JSON 格式的字符串并且把它转换为array map 变量
        $resultDataMap = json_decode($responseMessage, TRUE);
        //后续业务操作
        // $resultCode = $resultDataMap['resultCode'];
        return $resultDataMap;
    }

    /**
     * 构建本次请求的参数.
     *
     * @param  string  $service  本次请求的服务名称
     * @param  array  $data  本次请求的业务参数
     * @return resultCode
     */
    public function getBaseparams($service,$data,$orderNo=null)
    {
        writeLog('======Begin：'.$service.'=========');
        $param = $this->initParams($service,$orderNo);
        foreach ($data as $key => $val) {
            $param[$key]=$val;
        }
        Vendor('Paythird.DigestUtil');
        $signStr = \DigestUtil::digest($param, C('pay_config.security_key'), C('pay_config.sign_type'));
        $param['sign']=$signStr;
        return $param;
    }

    /**
     * 请求服务并返回信息  提现申请
     * @param parameters
     * @return mixed
     */
    public function getPostParams($service,$data,$orderNo=null)
    {
        writeLog('======Begin：'.$service.'=========');
        $param = $this->initParams($service,$orderNo);
        foreach ($data as $key => $val) {
            $param[$key]=$val;
        }
        Vendor('Paythird.DigestUtil');
        $signStr = \DigestUtil::digest($param, C('pay_config.security_key'), C('pay_config.sign_type'));
        $postStr = $this->buildPairs($param,$signStr);
        return $postStr;
    }
    /**
     * 请求服务并返回信息 发起提现
     */
	public function getPostParams_finance($service,$data,$orderNo=null)
    {
        writeLog('======Begin：'.$service.'=========');
        $param = $this->initParams_finance($service,$orderNo);
        foreach ($data as $key => $val) {
            $param[$key]=$val;
        }
        Vendor('Paythird.DigestUtil');
        $signStr = \DigestUtil::digest($param, C('pay_config.security_key'), C('pay_config.sign_type'));
        $postStr = $this->buildPairs($param,$signStr);
        return $postStr;
    }



    /**
     * 请求服务并返回信息
     * @param parameters
     * @return mixed
     */
    public function exchange($param){
    	Vendor('Paythird.DigestUtil');
        $signStr = \DigestUtil::digest($param, C('pay_config.security_key'), C('pay_config.sign_type'));
        $postStr = $this->buildPairs($param,$signStr);
        writeLog($postStr);
        dump($param);
        $result = $this->doCurlPost($param, $postStr);
        writeLog('======Request=========');
        writeLog($result);
        return $result;
    }

    /**
     * 初始化参数 <br/> 提现申请
     * 在web环境下，业务参数是由页面传入的。<br/>
     * @return array
     */
    public function initParams($service,$orderNo=null){
        $data = array();
        // 基本参数
        //这部分参数对于一个商户而言是固定的
        //order_no随机生成，只用于demo
        $order_no=$orderNo?$orderNo:$this->create_id();
        Vendor('Paythird.ApiConstants');
        $data[\ApiConstants::ORDER_NO] = $order_no;
        $data[\ApiConstants::MERCH_ORDER_NO] = $order_no;
        $data[\ApiConstants::PARTNER_ID] = C('pay_config.partner_id');
        $data[\ApiConstants::PROTOCOL] = C('pay_config.http_post_protocol');
        $data[\ApiConstants::SIGN_TYPE] = C('pay_config.sign_type');
        $data[\ApiConstants::NOTIFY_URL] = C('pay_config.notify_url');
        $data[\ApiConstants::RETURN_URL] = C('pay_config.return_url');
        // 业务参数后续方法插入
        //每个服务的业务参数是不同的,具体请参考文档的请求参数
        $data[\ApiConstants::SERVICE] = $service;
        return $data;
    }
	/**
     * 初始化参数 <br/>  发起提现
     * 在web环境下，业务参数是由页面传入的。<br/>
     * @return array
     */
    public function initParams_finance($service,$orderNo=null){
        $data = array();
        // 基本参数
        //这部分参数对于一个商户而言是固定的
        //order_no随机生成，只用于demo
        $order_no=$orderNo?$orderNo:$this->create_id();
        Vendor('Paythird.ApiConstants');
        $data[\ApiConstants::ORDER_NO] = $order_no;
        $data[\ApiConstants::MERCH_ORDER_NO] = $order_no;
        $data[\ApiConstants::PARTNER_ID] = C('pay_config.partner_id');
        $data[\ApiConstants::PROTOCOL] = C('pay_config.http_post_protocol');
        $data[\ApiConstants::SIGN_TYPE] = C('pay_config.sign_type');
        $data[\ApiConstants::NOTIFY_URL] = C('pay_config.notify_url_finance');
        $data[\ApiConstants::RETURN_URL] = C('pay_config.return_url_finance');
        // 业务参数后续方法插入
        //每个服务的业务参数是不同的,具体请参考文档的请求参数
        $data[\ApiConstants::SERVICE] = $service;
        return $data;
    }

    public function create_id() {
        $randId = '';
        for ($i = 0; $i < 16; $i++){
            $randId .= mt_rand(0,9);
        }
        return $randId;
    }
    /**
     * 通curl发送post请求
     * @param array $param
     * @param $postStr
     * @return mixed
     */
    public function doCurlPost(array $param, $postStr){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, C('pay_config.openapi_address'));
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 7); //Timeout after 7 seconds
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //Return data instead printing directly in Browser
        curl_setopt($curl, CURLOPT_USERAGENT, "php demo");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8','Connection: Keep-Alive'));
        curl_setopt($curl, CURLOPT_POST, count($param) + 1); //number of parameters sent
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr); //parameters data
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * 将参数组装成post字符串
     * @param array $param
     * @param parameters
     * @return string
     */
    public function buildPairs(array $param, $sign){
        $postStr = "";
        foreach ($param as $key => $value) {
            $postStr .= $key . '=' . urlencode(mb_convert_encoding($value, "utf-8", "auto")) . '&';
        }
        $postStr .= 'sign='.$sign;
        return $postStr;
    }

    /**
    * 验证订单是否成功
    */
    public function isSuccessful($pars){
        if(isset($pars['service'])){
            if($pars['success']=='true'&&$pars['resultCode']=='EXECUTE_SUCCESS'&&$pars['tradeStatus']=='SUBMITED')
                return true;
        }
        return false;
    }


    /*公用方法结束*/




}

