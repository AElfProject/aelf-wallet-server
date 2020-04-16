<?php
/**
 * 获取app所有通用货币
 * User: David
 * Date: 2019/6/03
 * Time: 09:53
 */

require_once __DIR__.'/../app.php';

class app_settings_get_currencies extends app {

    public function doRequest(){

        $lang = $this->getLang();

        if ( empty( $lang )  ) return $this->error( __( '参数错误' ) );

        $currencies = $this->getConfig('currencies');
        $currencies = json_decode($currencies, true);

        if (isset($currencies[$lang])) {
            $list = $currencies[$lang];
        } else {
            $list = [];
        }

        $this->returnSuccess('', ['list' => $this->format_elements_to_string($list)]);
    }

}