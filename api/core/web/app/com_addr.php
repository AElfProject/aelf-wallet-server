<?php
/**
 * 保存币种 address.
 * User: Jett
 * Date: 2018/9/4
 * Time: 3:05 PM
 */

require_once __DIR__.'/app.php';

class app_com_addr extends app {
    public function doRequest() {
        $parent = trim( post( 'parent' ) );
        $s = trim( post( 'address' ) );
        $udid = trim( post( 'udid' ) );
        $androidNoticeToken = trim( post( 'androidNoticeToken' ) );
        $iosNoticeToken = trim( post( 'iosNoticeToken' ) );
        $deviceInfo = trim( post( 'deviceInfo' ) );

        try{
            if ( empty( $s ) || empty( $parent ) || (!$androidNoticeToken && !$iosNoticeToken)){
                throw new Exception( __( '参数错误' ) );
            }

            $mdl_address = $this->db( 'index', 'com_addr', 'master' );
            $where = [
                'address' => $s,
                'android_notice_token' => $androidNoticeToken,
                'ios_notice_token'=>$iosNoticeToken,
                'lang'=>$this->getLang()
            ];
            $address = $mdl_address->getByWhere($where);
            if($address){
                //已存在
                $mdl_address->delete($address['id']);
            }else{
                //新用户
                //新增余额队列数据
                $this->addBalanceQueue($s);
            }

            $in = [ 'coin' => $parent,
                    'address' => $s,
                    'udid' => $udid,
                    'firstTime' => time(),
                    'lastTime' => time(),
                    'lang' => $this->getLang(),
                    'android_notice_token' => $androidNoticeToken,
                    'ios_notice_token' => $iosNoticeToken,
                    'device_info' => $deviceInfo
            ];
            $mdl_address->insert( $in );

            $this->redis()->set("aelf:address:{$s}", 1);

            $this->success( '' );
        }catch(Exception $ex){
            $this->error($ex->getMessage());
        }
    }

}