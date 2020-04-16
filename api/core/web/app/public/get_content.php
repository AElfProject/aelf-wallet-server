<?php
/**
 * 获取内容
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/../app.php';
class app_public_get_content extends app {

    public function doRequest(){
        $type = (int)post('type');

        $mdl_info = $this->db( 'index', 'info');
        $lang = $this->getLang();

        $where['classId'] = self::getClassIdByType($type);
        $where['isApproved'] = 1;
        $where['lang'] = $lang;

        $infoList = $mdl_info->getList(['title', 'content'], $where);
        $this->returnSuccess('', ['list' => $this->format_elements_to_string($infoList)]);
    }

    /**
     * 根据类型获取classId
     * @param $type
     * @return int
     */
    public static function getClassIdByType($type) {
        switch ($type) {
            case 1 :    //如何使用aelf wallet
                return 101;
                break;
            case 2 :    //隐私条款
                return 102;
                break;
            case 3 :    //用户协议
                return 103;
                break;
            case  4 :    //帮助反馈
                return 104;
                break;
        }
    }

}