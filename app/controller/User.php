<?php
declare (strict_types = 1);

namespace app\controller;

use think\Request;
use think\facade\Validate;
use app\model\User as UserModel;
use app\validate\User as UserValidate;
use think\exception\ValidateException;

class User extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $data = UserModel::field('id,username,sex,email')
            ->page($this->page,$this->pageSize)
            ->select();

        if ($data->isEmpty()) {
            return $this->create([],'无数据~',204);
        } else {
            return $this->create($data,'数据请求成功',200);
        }
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // 获取数据
        $data = $request->param();

        // 验证及错误返回
        try {
            validate(UserValidate::class)->check($data);
        } catch (ValidateException $exception) {
            return $this->create([],$exception->getError(),404);
        }

        // 写入数据
        $data['password'] = md5($data['password']);
        $return_id = UserModel::create($data)->getData('id');

        if (empty($return_id)) {
            return $this->create([],'注册失败~',400);
        } else {
            return $this->create($return_id,'注册成功~',200);
        }
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        // 判断 id 是否整型
        if (!Validate::isInteger($id)) {
            return $this->create([],'id参数不合法',400);
        }

        // 获取数据
        $data = UserModel::field('id,username,sex,email')->findOrEmpty($id);

        if ($data->isEmpty()) {
            return $this->create([],'无数据~',204);
        } else {
            return $this->create($data,'数据请求成功',200);
        }
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        // 获取数据
        $data = $request->param();

        // 验证及错误返回
        try {
            validate(UserValidate::class)->scene('edit')->check($data);
        } catch (ValidateException $exception) {
            return $this->create([],$exception->getError(),404);
        }

        $updateData = UserModel::find($id);
        // 判断邮箱是否一致
        if ($updateData->email === $data['email']) {
            return $this->create([],'修改的邮箱与原本邮箱一致~',400);
        }

        // 数据修改
        $return_id = UserModel::update($data)->getData('id');

        if (empty($return_id)) {
            return $this->create([],'修改失败~',400);
        } else {
            return $this->create($return_id,'修改成功~',200);
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // 判断 id 是否整型
        if (!Validate::isInteger($id)) {
            return $this->create([],'id参数不合法',400);
        }

        // 删除
        try {
            UserModel::find($id)->delete();
            return $this->create([],'删除成功~',200);
        } catch (\Error $e) {
            return $this->create([],'错误或无法删除~',400);
        }
    }

    /**
     * 用户喜好列表
     *
     * @param $id
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function hobby($id)
    {
        // 判断 id 是否整型
        if (!Validate::isInteger($id)) {
            return $this->create([],'id参数不合法',400);
        }

        // 喜好数据集
        $data = UserModel::find($id)->hobby()->field('id,content')->select();

        if ($data->isEmpty()) {
            return $this->create([],'无数据~',204);
        } else {
            return $this->create($data,'数据请求成功',200);
        }
    }

    /**
     * 用户登录
     *
     * @param Request $request
     * @return \think\Response
     */
    public function login(Request $request)
    {
        $data = $request->param();

        // 验证账户及密码
        $result = Validate::rule([
            'username' => 'unique:user,username^password'
        ])->check([
            'username' => $data['username'],
            'password' => md5($data['password'])
        ]);

        // 判断，反向
        if (!$result) {
            session('adminj',$data['username']);
            return $this->create([],'登录成功~',200);
        } else {
            return $this->create([],'用户名或密码错误~',400);
        }
    }
}
