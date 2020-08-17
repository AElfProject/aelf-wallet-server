<?php
/**
 * dapp 基类.
 * User: Jett
 * Date: 2019/03/28
 * Time: 17:30 PM
 */

require_once __DIR__.'/../app.php';

class app_dapp_base extends app {

    protected $dappsChainUrl = '';
    protected $lang;
    protected $ossUrl;
    protected $cats = [1=>"游戏",2=>"交易",3=>"工具",4=>"其他"];
    protected $hex = ['hot'=>"FF2E6B","new"=>"0555FF"];

    public function __construct() {
        parent::__construct();

        //$this->lang = str_replace('-', '_', get2( 'lang' ));
        $this->lang = get2( 'lang' );

        if($this->getConfig( 'OSS_URL' )){
            $this->ossUrl = $this->getConfig( 'OSS_URL' );
        }else{
            $this->ossUrl = $this->getConfig( 'oss_url' );
        }

        $this->lang = get2( 'lang' );
    }

    protected function getDappUrl($urls, $lang = '') {
        if (!is_null(json_decode($urls))) {
            $urls = json_decode($urls, true);
            $lang = $lang ? $lang : $this->getLang();
            $url = $urls[$lang];
        } else {
            $url = $urls;
        }
        return $url;
    }

    /**
     * 获取游戏数据
     * @param $cat
     * @param $isindex
     * @return mixed
     */
    protected function getGameData($cat, $isindex=0 )
    {

        $mdl_games = $this->db('index', 'dapps_games');
        if($isindex) {
            $games = $mdl_games->getList(array('id', 'ico', 'coin', 'tag', 'name', 'desc', 'cat', 'url', 'isindex'), array('status' => 1, 'isindex' => $isindex, 'cat' => $cat), 'sort desc', 10);
        }else{
            $games = $mdl_games->getList(array('id', 'ico', 'coin', 'tag', 'name', 'desc', 'cat', 'url', 'isindex'), array('status' => 1, 'cat' => $cat), 'sort desc', 10);
        }
        $games = $this->handleData($games);
        return $games;
    }

    protected function handleData($games){
        foreach ($games as $k => $item) {
            foreach ($item as $k2 => $item2) {
                if (in_array($k2, ['tag','name','desc']) !==false) {
                    $tmp = unserialize($item2);
                    if ($k2 == 'tag') {
                        $tmp2 = $tmp[$this->lang];
                        $tmp3 =[];
                        if($tmp2){
                            $_tmp3 = explode('|',$tmp2);
                            $_tmp3_en = explode('|',$tmp['en']);
                            foreach($_tmp3 as $k3=>$item3){
                                $tmp3[] = [
                                    'val'=>$item3,
                                    'hex'=>$this->hex[strtolower($_tmp3_en[$k3])]
                                ];
                            }
                        }
                        $games[$k][$k2] = $tmp3;
                    } else {
                        $games[$k][$k2] = $tmp[$this->lang];
                    }
                }
                if ($k2 == 'ico') {
                    $games[$k]['logo'] = $item2 ? $this->ossUrl . $item2 : $this->ossUrl . 'onchain.default.png';
                }
                if ($k2 == 'cat') {
                    $games[$k]['type'] = __($this->cats[$item2]);
                }
                if ($k2 == 'url') {
                    $games[$k]['url'] = $this->getDappUrl($item2, $this->lang);
                }
                $games[$k]['website'] = $games[$k]['url'];
            }
        }
        return $games;
    }

    protected function request($url, $data = array(), $type = '')
    {
        //$data是字符串，则application/x-www-form-urlencoded
        //$data是数组，则multipart/form-data

        //$headers = array();
        //$headers[] = "Content-type: text/xml; charset=utf-8";

        if ($this->is_json($data)) {
            $headers = array('Content-type: application/json');
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($headers) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        if ($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($curl);
        $errno = curl_errno($curl);

        if ($type){
            return curl_getinfo($curl, $type);
        }

        if ($errno) {
            $output = array('errno' => $errno, 'error' => curl_error($curl));
            $output['detail'] = curl_getinfo($curl);
        }
        curl_close($curl);
        return $output;
    }

    //判断是否是json格式数据
    public function is_json($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }




}