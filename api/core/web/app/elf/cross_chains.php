<?php
/**
 * 获取跨链信息.
 * User: Jett
 * Date: 2019-09-25
 * Time: 14:19
 */
require_once __DIR__.'/base.php';

class app_elf_cross_chains extends app_elf_base{

    public function doRequest()
    {
        $cross_info =  json_decode($this->getConfig('chains'), true);

        $nodes = array_flip($this->r_nodes);

        foreach ($cross_info as $k=>$item){
            $item['color'] = $this->chain_color[$item['name']];

            $item['issueid'] = strval($nodes[$item['name']]);
            $cross_info[$k] = $item;
        }

        $this->returnSuccess('', $cross_info);
    }


}