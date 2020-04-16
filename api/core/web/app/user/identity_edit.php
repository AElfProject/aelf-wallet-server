<?php
/**
 * 修改用户身份信息
 * User: David
 * Date: 2019/06/03
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_identity_edit extends app_user_base {

    public function doRequest(){

        $img = $_FILES['img'];
        $name = trim(post('name'));

        if ($img) {
            $avatar = $this->uploadImage($img);

            if ($avatar) {
                $updateData['img'] = $avatar;
            }
        }

        if ($name) {
            $updateData['name'] = $name;
        }

        $mdl_user_address = $this->db( 'index', 'user_address', 'master');
        if ($mdl_user_address->update($updateData, $this->user['id'])) {
            return $this->returnSuccess(__('成功'));
        } else {
            return $this->returnError( __( '失败' ) );
        }


    }


    /**
     * 上传图片
     * @param $avatarObj
     * @return bool|string|void
     */
    protected function uploadImage($avatarObj){
        
        $file = new file();

        if ($avatarObj['size'] > 0) {
            $filepath = date('Y-m');
            $file->createdir('data/upload/' . $filepath);
            $allowExts = array('jpg', 'jpeg', 'gif', 'png');
            $avatar = $file->upfile($allowExts, $avatarObj, UPDATE_DIR, $filepath . '/' . date('YmdHis') . $this->rnd(10));
        }
        if (!$avatar) return $this->error( __( '请选择图片文件' ) );

        $file->resize( UPDATE_DIR.$avatar, UPDATE_DIR.$avatar, 160, 160 );

        require_once 'core/v2.1/AliYun_OSS.php';

        /*临时路径*/
        {
            $upload_file = 'elf_wallet/' . $avatar;
            $ossStatus = AliYun_OSS::uploadFile('elf', $upload_file, UPDATE_DIR . $avatar);
        }

        $file->deletefile(UPDATE_DIR . $avatar);

        if ($ossStatus['status']) {
            return $upload_file;
        } else {
            return false;
        }
    }

}