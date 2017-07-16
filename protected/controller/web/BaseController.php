<?php
Class BaseController extends Controller{
	
	public $tep_dir = 'web';
    public $layout = "web/layout.php";
	public $cars ;
	public $config;
	public function init(){
		$configDb = new DB('site');
		$this->config = $configDb->find(array('id'=>1));
		$carDb = new DB('class');
		$this->cars = $carDb->orderDesc('class_orderid')
							->get();
		
	}
	
	/**
	 * 获取客户端ip
	 */
	public function getIp(){
		if (getenv("HTTP_CLIENT_IP"))
			$ip = getenv("HTTP_CLIENT_IP");
		else if(getenv("HTTP_X_FORWARDED_FOR"))
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		else if(getenv("REMOTE_ADDR"))
			$ip = getenv("REMOTE_ADDR");
		else $ip = "Unknow";
		return $ip;
	}
	
	
	/**
     * 统一输出下分页
     */
    function pager($pageArr, $param=""){
        if($param !=''){
            $param = '&'.$param;
        }
        if(!$pageArr['all_pages']){
            return "";
        }
        $pageStr = '';  
        $pageStr.= ' <span>共  <span style="color: red;">'.$pageArr['total_count'].'</span> 条记录
                 '.$pageArr['total_page'].' 页 </span>&nbsp; ';
        $current = $pageArr['current_page'];
        if($current > 1){
            $pageStr.='<a href="?page=1'.$param.'">首页</a>&nbsp;<a href="?page='.strval($curren-1).$param.'">上一页</a>';
        }else{
            $pageStr.='<span>首页</span><span>上一页</span>';
        }  
        foreach ($pageArr['all_pages'] as $p){
             if($p== $current){
                 $pageStr.= '<span> &nbsp;'.strval($p).'&nbsp;</span>';
             }else{
                 $pageStr.= '<a href="?page='.strval($p) .$param.'">&nbsp;'.strval($p).'&nbsp;</a>';
             }
         }  
        if($curren < $pageArr['total_page']){
            $pageStr.='<a href="?page='.strval($curren+1).$param.'">下一页</a>&nbsp;<a href="?page=1'.$param.'">末页</a>';
        }else{
            $pageStr.='<span>下一页</span><span>末页</span>';
        }     
        
        return $pageStr;
      
    }
}
