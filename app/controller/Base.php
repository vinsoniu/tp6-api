<?php
namespace app\controller;

use think\facade\Config;
use think\facade\Request;
use think\Response;

/**
 * Class Base
 * @package app\controller
 */
abstract class Base
{
    /**
     * @var int
     */
    protected $page;
    /**
     * @var int
     */
    protected $pageSize;

    /**
     * @param $name
     * @param $arguments
     * @return Response
     */
    public function __call($name, $arguments)
    {
        // 404 方法不存在
        return $this->create([],'资源不存在~',404);
    }

    /**
     * Base constructor.
     */
    public function __construct()
    {
        // 获取分页
        $this->page = (int)Request::param('page');
        // 获取分页条数
        $this->pageSize = (int)Request::param('page_size',Config::get('app.page_size'));
    }

    /**
     * @param $data
     * @param string $msg
     * @param int $code
     * @param string $type
     * @return Response
     */
    protected function create($data, string $msg = '', int $code = 200, string $type = 'json'):Response
    {
        $result = [
            // 状态码
            'code' => $code,
            // 消息
            'msg' => $msg,
            // 数据
            'data' => $data
        ];

        // 返回 api 接口
        return Response::create($result,$type);
    }
}