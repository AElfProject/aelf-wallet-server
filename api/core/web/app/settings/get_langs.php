<?php
/**
 * 获取app所有语言
 * User: David
 * Date: 2019/6/03
 * Time: 09:53
 */

require_once __DIR__.'/../app.php';

class app_settings_get_langs extends app {

    public function doRequest(){

        $langs = unserialize( LANGS );

        $this->returnSuccess('', ['list' => $this->format_elements_to_string($langs)]);
    }

}