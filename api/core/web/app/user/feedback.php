<?php
/**
 * 留言板
 * User: David
 * Date: 2019/5/31
 * Time: 17:17
 */

require_once __DIR__.'/base.php';

class app_user_feedback extends app_user_base {

    public function doRequest(){
        $title = trim(post('title'));    //标题
        $email = trim(post('email'));    //邮箱
        $desc = strip_tags(trim(post('desc')));    //内容
        $udid = trim(post('udid'));
        $device = trim(post('device'));
        $version = trim(post('version'));
        $ip = ip();

        if (empty($title))  $this->error(__('请填写标题'));
        if (empty($desc))   $this->error(__('请填写内容'));
        if (strlen($title) > 150) $this->error(__('标题不能超过150个字符'));
        if (strlen($desc) > 1500) $this->error(__('内容不能超过1500个字符'));
        if ( ! preg_match( "/^[0-9a-zA-Z\_]+([_\\.-][0-9a-zA-Z\_]+)*"."@"."([0-9a-zA-Z\_]+([\.-][0-9a-zA-Z\_]+)*)+"."\\.[a-z]{2,}$/i", $email ) ) $this->error( __( '邮箱格式不正确' ) );

        //防止连续提交
        $keyOnly = "only_once_feedback_$ip";
        if ($this->redis()->exists($keyOnly)) {
            $this->error(__('您的操作过于频繁，请稍后再试'));
        } else {
            $this->redis()->set($keyOnly, 1, 5);
        }

        $mdl_feedback = $this->db('index', 'feedback', 'master');

        $insertData['title'] = $title;
        $insertData['email'] = $email;
        $insertData['desc'] = $desc;
        $insertData['user_id'] = $this->user['id'];
        $insertData['create_time'] = time();
        $insertData['udid'] = $udid;
        $insertData['device'] = $device;
        $insertData['version'] = $version;
        $insertData['ip'] = $ip;

        if ($mdl_feedback->insert($insertData)) {
            $this->addMailQueue($insertData);
            $this->returnSuccess(__('成功'));
        } else {
            $this->returnError(__('失败'));
        }

    }

    /**
     * 发送邮箱
     * @param $data
     */
    private function addMailQueue($data) {
        $customer_service_list = $this->getConfig('customer_service_list');
        $customer_service_list = explode(',', $customer_service_list);    //所有客服

        if ($customer_service_list) {
            foreach ($customer_service_list as $customer_service) {
                if ($customer_service) {
                    $queueName = 'mail_send_queue';
                    $queue = array(
                        'user' => $this->user,
                        'level' => 999,
                        'time' => time(),
                        'type' => 'mail',
                        'receive' => $customer_service,
                        'subject' => $data['title'],
                        'body' => $data['desc'],
                    );
                    $this->redis()->rPush($queueName, json_encode($queue));
                }
            }
        }
    }

}