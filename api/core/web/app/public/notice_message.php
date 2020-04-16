<?php
/**
 * 公告消息
 * User: David
 * Date: 2019/6/01
 * Time: 17:17
 */

require_once __DIR__.'/../app.php';

class app_public_notice_message extends app {

    public function doRequest(){

        $mdl_info = $this->db( 'index', 'message');
        $where['type'] = 2;
        $where['status'] = 1;

        $infoList = $mdl_info->getList(null, $where, 'sort desc');

        foreach ($infoList as $k => $info) {
            $info = $this->formatLangJsonValue($info, ['title', 'desc'], $this->getLang());

            if ($this->getLang() != 'zh-cn') {
                $info['message'] = $info['message_' . $this->getLang()];
            }
            $info = $this->getKeyValue($info, ['id', 'title', 'message', 'type', 'desc', 'create_time', 'sort']);
            $infoList[$k] = $info;
        }

        $this->returnSuccess('', $this->format_elements_to_string(['list' => $infoList]));
    }


}