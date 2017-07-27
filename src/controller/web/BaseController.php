<?php

Class BaseController extends Controller
{

    public $tep_dir = 'web';
    public $layout = "web/layout.php";

    /**
     * 初始化 action 执行之前执行
     */
    public function init()
    {
    }

    /**
     * 统一输出下分页
     */
    function pager($pageArr, $param = "")
    {
        if ($param != '') {
            $param = '&' . $param;
        }
        if (!$pageArr['all_pages']) {
            return "";
        }
        $pageStr = '';
        $pageStr .= '</span> 条记录' . ' <span>共  <span style="color: red;">' . $pageArr['total_count'] . $pageArr['total_page'] . ' 页 </span>&nbsp; ';
        $current = $pageArr['current_page'];
        if ($current > 1) {
            $pageStr .= '<a href="?page=1' . $param . '">首页</a>&nbsp;<a href="?page=' . strval($current - 1) . $param . '">上一页</a>';
        } else {
            $pageStr .= '<span>首页</span><span>上一页</span>';
        }
        foreach ($pageArr['all_pages'] as $p) {
            if ($p == $current) {
                $pageStr .= '<span> &nbsp;' . strval($p) . '&nbsp;</span>';
            } else {
                $pageStr .= '<a href="?page=' . strval($p) . $param . '">&nbsp;' . strval($p) . '&nbsp;</a>';
            }
        }
        if ($current < $pageArr['total_page']) {
            $pageStr .= '<a href="?page=' . strval($current + 1) . $param . '">下一页</a>&nbsp;<a href="?page=1' . $param . '">末页</a>';
        } else {
            $pageStr .= '<span>下一页</span><span>末页</span>';
        }

        return $pageStr;

    }
}
