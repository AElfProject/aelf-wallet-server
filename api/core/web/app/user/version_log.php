<?php
/**
 * 版本日志
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/../app.php';

class app_user_version_log extends app {

    public function doRequest(){

        $device = $this->basePostData['device'];
        $isStore = (int)post('is_store');

        $key =  $device;

        if ($isStore == 1){
            $key =  $key."_store";
        }

        $mdl_version = $this->db( 'index', 'version');
        $where['key'] = $key;
        $list = $mdl_version->getList(null, $where);

        $lang = $this->getLang();

        foreach($list as $k => $version) {
            if ($lang != 'zh-cn') {
                $version['intro'] = $version['intro_'.$lang];
            }

            $version['intro'] =  $version['intro'] ? explode('#@#@#@', $version['intro']) : [];

            $version = $this->getKeyValue($version, ['id', 'key', 'intro', 'verNo', 'status', 'is_force', 'min_version', 'create_time', 'update_time', 'upgrade_time']);
            $list[$k] = $version;
        }

        $this->returnSuccess('', ['list' => $this->format_elements_to_string($list)]);
    }


}