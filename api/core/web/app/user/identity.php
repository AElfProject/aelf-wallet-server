<?php
/**
 * 身份信息
 * User: David
 * Date: 2019/06/03
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_identity extends app_user_base {

    public function doRequest(){

        if (empty($this->user)) {
            return $this->error( __( '用户不存在' ) );
        }

        $data = $this->user;
        $ossUrl = $this->getConfig( 'OSS_URL' )??$this->getConfig( 'oss_url' );
        $data['img'] = $data['img']?$data['img']:'elf_wallet/elf/defaulth.png';
        $data['img'] = $ossUrl.$data['img'];

        return $this->returnSuccess('', $this->format_elements_to_string($data));
    }

}