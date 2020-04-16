<?php
/**
 * 接收onchain广播时错误日志.
 * User: Jett
 * Date: 2018/12/17
 * Time: 2:37 PM
 */

require_once __DIR__.'/app.php';
class app_send_error extends app {

    public function doRequest(){
        $coin = trim(post('coin'));
        $error = stripslashes(post('error'));
        $status = trim('status');

        $data = array(
            'coin'=>$coin,
            'status'=>$status,
            'error'=>$error
        );
        $this->logFile($data, "send_error");

        return $this->returnSuccess( '' );

    }
}