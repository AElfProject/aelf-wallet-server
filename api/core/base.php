<?php

/**
 * @Author Today Nie
 * @Date 2017-12-20
 */

class base
{
    /**
     * 默认加密key
     */
    protected $defaultEncodeKey = 'Aa2goJbqeWm%$fBRP';

    /**
     * redis
     */
    protected $redis = array();

    /**
     * 缓存db连接
     */
    protected $db = array(
        'index' => null,  //主索引库
    );

    /**
     * 缓存db操作对象
     */
    protected $mdl;

    /**
     * 配置参数
     * 从主索引库中获取
     */
    protected $configs;

    /**
     * 数据库索引
     * 从主索引库中获取
     */
    protected $databases;

    /**
     * 币种
     * 从主索引库中获取
     */
    protected $coins;

    /**
     * 模板引擎
     */
    protected $smarty;

    /**
     * 请求处理结果
     */
    protected $response;

    /**
     * 汇率
     */
    protected $exchangeRates;

    protected $debug = array();

    /**
     * 输出json数据
     */
    protected function json($data = array(), $exit = true)
    {
        if (!isset($data['msg'])) $data['msg'] = '';
        if (!isset($data['data'])) $data['data'] = array();

        if ( DEBUG ) {
            $data['debug'] = $this->debug;
        }

        if ($exit) {
            echo json_encode($data);
            exit;
        } else {
            $this->response = $data;
        }
    }

    /**
     * success
     * 当post请求时返回json，当get请求时返回html
     */
    protected function success($msg, $data = array())
    {
        if (is_ajax()) {
            if (!isset($data['info'])) $data['info'] = $msg;
            if (!isset($data['status'])) $data['status'] = 1;
            return $this->json($data);
        }
        $this->smarty()->assign('msg', $msg);

        $this->display('success');
        exit;
    }

    /**
     * error
     * 当post请求时返回json，当get请求时返回html
     */
    protected function error($msg, $data = array())
    {
        if (is_ajax()) {
            if (!isset($data['info'])) $data['info'] = $msg;
            if (!isset($data['status'])) $data['status'] = 0;
            return $this->json($data);
        }
        $this->smarty()->assign('msg', $msg);

        $this->display('error');
        exit;
    }

    public function jump($url = '')
    {
        $url = $this->parseUrl($url);
        if (is_ajax()) {
            $data = array('url' => $url->toString());
            $this->json($data);
            exit;
        }
        header('Location:' . $url->toString());
        exit;
    }

    /**
     * 保存日志到文件中
     */
    protected function logFile($data, $level = '')
    {
        if (is_array($data)) {
            if (!isset($data['ip'])) $data['ip'] = ip();
            if (!isset($data['get_url'])) $data['get_url'] = $_SERVER['REQUEST_URI'];
        } else {
            $data = array(
                'data' => $data,
                'ip' => ip(),
                'get_url' => $_SERVER['REQUEST_URI'],
            );
        }
        return file_put_contents('logs/web/' . date('Ymd') . ($level ? '.' . $level : '') . '.log', date('Y-m-d H:i:s') . ' ' . json_encode($data) . PHP_EOL, FILE_APPEND);
    }

    /**
     * 从数组中获取需要的key和对应的value
     */
    protected function getKeyValue($data, $keyArr)
    {
        if (empty($keyArr)) return array();
        $tmp = array();
        foreach ($keyArr as $key => $ka) {
            if (is_numeric($key)) {
                if (isset($data[$ka])) $tmp[$ka] = $data[$ka];
            } else {
                if (isset($data[$key])) $tmp[$ka] = $data[$key];
            }
        }
        return $tmp;
    }

    /**
     * 获取redis对象
     */
    protected function redis($key = '')
    {
        if (empty($key)) $key = 'index';
        if (empty($this->redis[$key])) {
            require_once 'core/v2.1/redis.php';
            $setting = $GLOBALS['REDIS_LIST'][$key];
            if (!$setting) {
                echo 'Connect redis faild.';
                exit;
            }
            $this->redis[$key] = new RedisByToday($setting['host'], $setting['port'], $setting['auth'], $setting['dbNumber']);
        }
        return $this->redis[$key];
    }

    protected function initSession()
    {

        if (session_status() != 2) {
            ini_set("session.save_handler", "redis");
            ini_set("session.save_path", "tcp://" . $GLOBALS['REDIS_LIST']['index']['host'] . ":" . $GLOBALS['REDIS_LIST']['index']['port'] . '?auth=' . $GLOBALS['REDIS_LIST']['index']['auth']);
            session_start();
        }
    }

    protected function getRouteStr()
    {
        return implode('_', $GLOBALS['parse']['path']);
    }

    /**
     * 生成表单key
     */
    protected function getFormKey($formName = '')
    {
        if (empty($formName)) $formName = $this->getRouteStr();

        if (empty($formName)) return false;
        $formName = preg_replace('/[^a-zA-Z0-9_]/', '', $formName);

        $this->initSession();

        //加入时间
        $_SESSION['form_name_' . $formName] = time();
        $data = $formName . $_SESSION['form_name_' . $formName];

        return $this->md5($data);
    }

    /**
     * 验证表单key
     */
    protected function verifyFormKey($formName = '', $data = '')
    {
        if (empty($formName)) $formName = $this->getRouteStr();

        if (empty($formName)) return false;
        if (empty($data)) $data = post('formKey');
        if (empty($data)) return false;
        $formName = preg_replace('/[^a-zA-Z0-9_]/', '', $formName);

        $this->initSession();

        return $this->md5($formName . $_SESSION['form_name_' . $formName]) == $data;
    }

    /**
     * 获取db操作对象
     *
     * 默认从slave库中读取
     */
    protected function db($conn = 'index',$tb, $type = 'slave')
    {
        if (empty($tb)) return false;
        if (empty($conn)) return false;
        if ($type != 'master' && $type != 'slave') $type = 'slave';

        //配置参数
        $setting = $GLOBALS['DB_LIST'][$conn];
        if (!$setting) {
            exit('Connect database faild.1');
        }

        if ($type == 'slave' && !empty($setting['slaves'])) {
            //根据当前时间戳%从库数量，随机读取
            $setting = $setting['slaves'][(time() % count($setting['slaves']))];
        } else $type = 'master';

        if (!$this->db[$conn][$type]) {
            //如果没有连接，先建立连接
            require_once 'core/v2.1/pdo.php';
            $this->db[$conn][$type] = new PdoByToday($setting['host'], $setting['port'], $setting['name'], $setting['pwd'], $setting['db']);
        }

        if (empty($this->mdl[$conn][$type][$tb])) {
            //如果没有实例化过对此表的操作对象，则先实例化
            require_once 'core/model/pdo.php';
            $this->mdl[$conn][$type][$tb] = new mdl_pdo($this->db[$conn][$type], $tb);
        }

        return $this->mdl[$conn][$type][$tb];
    }

    /**
     * 获取所有配置参数
     */
    protected function getConfigs()
    {
        if ($this->configs) return $this->configs;

        //先从redis中查找，过期时间为5分钟
        $configs = $this->redis()->get('elf:configs');
        if (!$configs) {
            //从数据库中生成
            $mdl_config = $this->db('index', 'config_data');
            $data = $mdl_config->getList();
            $configs = array();
            foreach ($data as $item) {
                $configs[$item['key']] = $item['val'];
            }
            $this->redis()->set('elf:configs', $configs, 5 * 60);
        }
        $this->configs = $configs;
        return $this->configs;
    }

    /**
     * 获取指定配置参数
     */
    protected function getConfig($key)
    {
        if (empty($key)) return false;

        $configs = $this->getConfigs();
        return $configs[$key];
    }

    /**
     * 获取数据库索引
     */
    protected function getDatabases()
    {
        if ($this->databases) return $this->databases;

        //先从redis中查找，过期时间为5分钟
        $databases = $this->redis()->get('databases');
        if (!$databases) {
            //从数据库中生成
            $mdl_database = $this->db('index', 'database');
            $data = $mdl_database->getList();
            $databases = array();
            foreach ($data as $item) {
                $databases[$item['id']] = $item;
            }
            $this->redis()->set('databases', $databases, 5 * 60);
        }
        $this->databases = $databases;
        return $this->databases;
    }

    /**
     * 获取指定数据库索引
     */
    protected function getDatabase($id)
    {
        $databases = $this->getDatabases();
        return $databases[$id];
    }

    /**
     * 获取币种
     */
    protected function getCoins()
    {
        if ($this->coins) return $this->coins;

        //先从redis中查找，过期时间为5分钟
        $coins = $this->redis()->get('coins');
        if (!$coins) {
            //从数据库中生成
            $mdl_coin = self::db('index', 'coin');
            $data = $mdl_coin->getList(null, array('status' => 1), 'name asc, id asc');
            $coins = array();
            empty($data) ? $data = [] : null;
            foreach ($data as $item) {
                $coins[$item['name']] = $item;
            }
            $this->redis()->set('coins', $coins, 5 * 60);
        }
        $this->coins = $coins;
        return $this->coins;
    }

    /**
     * 获取指定币种
     */
    protected function getCoin($name)
    {
        $coins = $this->getCoins();
        return $coins[$name];
    }

    /**
     * 获取语言列表
     */
    protected function getLangs()
    {
        $tmp = unserialize(LANGS);
        $langs = array();
        foreach ($tmp as $t) {
            $langs[$t['id']] = $t;
        }
        return $langs;
    }

    /**
     * 获取当前语言
     */
    protected function getLang()
    {
        global $gbl_con, $admin_lang, $lang;

        if ($gbl_con == 'admin') {
            $lang = isset($admin_lang) ? $admin_lang : $_COOKIE['admin_lang'];
        } else {
            $lang = isset($lang) ? $lang : $_COOKIE['lang'];
        }

        $langs = $this->getLangs();
        if (!isset($langs[$lang])) {
            reset($langs);
            $lang = key($langs);
        }
        return $lang;
    }

    protected function assign($key, $val)
    {
        return $this->smarty()->assign($key, $val);
    }

    protected function display($tpl)
    {
        $this->smarty()->assign('UPLOAD_PATH', UPLOAD_PATH);
        $this->smarty()->assign('STATIC_PATH', STATIC_DIR);
        $this->smarty()->assign('SKIN_PATH', HTTP_ROOT . 'themes/' . STYLE);
        $this->smarty()->assign('OSS_URL', $this->OSS_URL);

        $langStr = $this->getLang();

        $this->smarty()->assign('http_root', HTTP_ROOT);
        $this->smarty()->assign('http_root_www', HTTP_ROOT_WWW);

        $template = $this->smarty()->template_dir . $tpl . '.htm';

        if (!file_exists($template)) {
            //如果404.htm存在，则输出404
            header('HTTP/1.1 404 Not Found');
            header('Status: 404 Not Found');
            $this->smarty()->display('404.htm');
            exit;
        }
        $this->smarty()->display($tpl . '.htm');
        exit;
    }

    /**
     * 获取IP详情
     */
    protected function getIpDetail($ip)
    {
        $detail = $this->request('http://freegeoip.net/json/' . $ip);
        $detail = json_decode($detail, true);
        return array('country' => $detail['country_name'], 'city' => $detail['city']);
    }

    protected function md5($str)
    {
        return md5($GLOBALS['KEY_'] . $str . $GLOBALS['_KEY']);
    }

    public function __construct()
    {
        unset($_GET['con']);
        unset($_GET['ctl']);
        unset($_GET['act']);
    }

    /**
     * 发送短信
     */
    protected function sendsms($mobile, $content)
    {

    }

    /**
     * 发送邮件
     */
    protected function sendmail($email, $subject, $content)
    {
        if (empty($email) || empty($subject) || empty($content)) return false;

        $smtp = array(
            'host' => 'smtp.mxhichina.com',
            'port' => '465',
            'username' => 'no-reply@aelf.com',
            'password' => '',
            'senderEmail' => 'no-reply@aelf.com',
            'senderName' => 'no-reply@aelf.com',
        );

        return smtp_mail($email, $subject, $content, $smtp['host'], $smtp['port'], $smtp['username'], $smtp['password'], $smtp['senderEmail'], $smtp['senderName']);
    }

    protected function request($url, $data = array())
    {
        //$data是字符串，则application/x-www-form-urlencoded
        //$data是数组，则multipart/form-data

        //$headers = array();
        //$headers[] = "Content-type: text/xml; charset=utf-8";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($headers) curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

        if($data) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($curl);
        $errno = curl_errno($curl);
        if ($errno) {
            $output = array('errno' => $errno, 'error' => curl_error($curl));
            $output['detail'] = curl_getinfo($curl);
        }
        curl_close($curl);
        return $output;
    }

    protected function smarty()
    {
        echo 'no smarty';
        exit;

        if (!$this->smarty) {
            require_once(CORE_DIR . "smarty/Smarty.class.php");

            $this->smarty = new Smarty();
            $this->smarty->config_dir = $GLOBALS['TPL_SM_CONFIG_DIR'];
            $this->smarty->caching = $GLOBALS['TPL_SM_CACHEING'];
            $this->smarty->template_dir = $GLOBALS['TPL_SM_TEMPLATE_DIR'];
            $this->smarty->compile_dir = $GLOBALS['TPL_SM_COMPILE_DIR'];
            $this->smarty->cache_dir = $GLOBALS['TPL_SM_CACHE_DIR'];
            $this->smarty->left_delimiter = $GLOBALS['TPL_SM_DELIMITER_LEFT'];
            $this->smarty->right_delimiter = $GLOBALS['TPL_SM_DELIMITER_RIGHT'];
            $this->smarty->force_compile = false;
        }
        return $this->smarty;
    }

    protected function rnd($len = 6)
    {
        $rnd = '';
        while (strlen($rnd) < $len) {
            $rnd .= mt_rand();
        }
        $rnd = substr($rnd, 0, $len);

        return $rnd;
    }

    protected function rndStr($len = 10, $symbol = true)
    {
        $randStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        if ( $symbol ) $randStr .= '!@#$%^&*()<>?_+';
        $rnd = '';
        while (strlen($rnd) < $len) {
            $rnd .= $randStr[mt_rand(0, strlen($randStr) - 1)];
        }
        $rnd = substr($rnd, 0, $len);

        return $rnd;
    }

    /**
     * 隐藏电话号码
     */
    protected function hidePhone($phone)
    {
        if (empty($phone)) return '';
        if (strlen($phone) == 11) {
            return substr($phone, 0, 3) . '****' . substr($phone, strlen($phone) - 4);
        } else {
            return substr($phone, 0, 3) . '****' . substr($phone, strlen($phone) - 2);
        }
    }

    protected function parseUrl($url = '')
    {
        require_once 'core/v2.1/ParseURL.php';
        $parseUrl = new ParseURL($url);
        return $parseUrl;
    }

    /**
     * 将数组转成xml对象
     */
    protected function array2Xml($arrayObj, $xmlDoc = null, $ele = null, $rootName = '')
    {
        if (!isset($xmlDoc)) {
            $xmlDoc = new DOMDocument();
            $xmlDoc->formatOutput = true;
        }
        if (!isset($ele)) {
            $ele = $xmlDoc->createElement($rootName);
            $xmlDoc->appendChild($ele);
        }

        foreach ($arrayObj as $key => $val) {
            /*if ( !is_string( $key ) && is_array( $val ) ) {
                $this->array2Xml( $val, $xmlDoc, $ele );
                continue;
            }*/

            $elex = $xmlDoc->createElement(is_string($key) ? $key : substr($rootName, 0, strlen($rootName) - 1));
            $ele->appendChild($elex);
            if (is_array($val)) {
                $this->array2Xml($val, $xmlDoc, $elex, $key);
            } else {
                $elexText = $xmlDoc->createCDATASection($val);
                $elex->appendChild($elexText);
            }
        }
        return $xmlDoc;
    }

    /**
     * 将xml对象转成数组
     */
    protected function xml2Array($xmlObj)
    {
        $result = array();
        $array = $xmlObj;
        if (get_class($array) == 'SimpleXMLElement') {
            $array = get_object_vars($xmlObj);
        }
        if (is_array($array)) {
            if (count($array) <= 0) {
                return trim(strval($xmlObj));
            }
            foreach ($array as $key => $val) {
                $result[$key] = $this->xml2Array($val);
            }
            return $result;
        } else {
            return trim(strval($array));
        }
    }

    /**
     * 填充算法
     */
    private function stripPKSC7Padding( $source ) {
        $blocksize = mcrypt_get_block_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
        $pad = $blocksize - ( strlen( $source ) % $blocksize );
        return $source.str_repeat( chr( $pad ), $pad );
    }

    /**
     * 移去填充算法
     */
    private function stripPKSC7UnPadding( $source ) {
        $pad = ord( $source{ strlen( $source ) - 1 } );
        if ( $pad > strlen( $source ) ) return $source;
        if ( strspn( $source, chr( $pad ), strlen( $source ) - $pad ) != $pad ) return $source;
        $ret = substr( $source, 0, -1 * $pad );
        return $ret;
    }

    /**
     * 加密
     */
    protected function aesEncode( $str ) {
        $privateKey = '';
        $iv = '16-Bytes--String';
        $str = $this->stripPKSC7Padding( $str );
        return base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $privateKey, $str, MCRYPT_MODE_CBC, $iv ) );
    }

    /**
     * 解密
     */
    protected function aesDecode( $encode ) {
        $privateKey = '';
        $iv = '16-Bytes--String';
        $encode = rawurldecode( urlencode( urldecode( $encode ) ) );
        $encryptedData = base64_decode( $encode );
        $encrypt_str = mcrypt_decrypt( MCRYPT_RIJNDAEL_128,  $privateKey, $encryptedData, MCRYPT_MODE_CBC, $iv );
        $decrypted = $this->stripPKSC7UnPadding( $encrypt_str );
        return $decrypted;
    }

    protected function rsaDecode( $data ) {
        require_once 'core/v2.1/RSA.php';
        $privateKey = $GLOBALS['RSA_LIST']['privateKey'];

        return RSA::decodeByPrivateKey( $data, $privateKey );
    }

    protected function rsaDecodeFix( $data ) {
        $arr = explode( ';', $data );
        $str = '';
        foreach ( $arr as $item ) $str .= $this->rsaDecode( $item );
        return $str;
    }

    protected function rsaEncode( $data ) {
        require_once 'core/v2.1/RSA.php';

        $publicKey = $GLOBALS['RSA_LIST']['publicKey'];

        return RSA::encodeByPublicKey( $data, $publicKey );
    }

    protected function rsaEncodeFix( $data ) {
        $arr = array();
        foreach ( str_split( $data, 117 ) as $d ) {
            $arr[] = $this->rsaEncode( $d );
        }
        return implode( ';', $arr );
    }


    /**
     * 取汉字的第一个字的首字母
     * @param type $str
     * @return string|null
     */
    protected function getFirstChar($s){
        $s0 = mb_substr($s,0,3);
        $s = iconv('UTF-8','gb2312', $s0);
        if (ord($s0)>128) {
            $asc=ord($s{0})*256+ord($s{1})-65536;
            if($asc>=-20319 and $asc<=-20284)return "A";
            if($asc>=-20283 and $asc<=-19776)return "B";
            if($asc>=-19775 and $asc<=-19219)return "C";
            if($asc>=-19218 and $asc<=-18711)return "D";
            if($asc>=-18710 and $asc<=-18527)return "E";
            if($asc>=-18526 and $asc<=-18240)return "F";
            if($asc>=-18239 and $asc<=-17760)return "G";
            if($asc>=-17759 and $asc<=-17248)return "H";
            if($asc>=-17247 and $asc<=-17418)return "I";
            if($asc>=-17417 and $asc<=-16475)return "J";
            if($asc>=-16474 and $asc<=-16213)return "K";
            if($asc>=-16212 and $asc<=-15641)return "L";
            if($asc>=-15640 and $asc<=-15166)return "M";
            if($asc>=-15165 and $asc<=-14923)return "N";
            if($asc>=-14922 and $asc<=-14915)return "O";
            if($asc>=-14914 and $asc<=-14631)return "P";
            if($asc>=-14630 and $asc<=-14150)return "Q";
            if($asc>=-14149 and $asc<=-14091)return "R";
            if($asc>=-14090 and $asc<=-13319)return "S";
            if($asc>=-13318 and $asc<=-12839)return "T";
            if($asc>=-12838 and $asc<=-12557)return "W";
            if($asc>=-12556 and $asc<=-11848)return "X";
            if($asc>=-11847 and $asc<=-11056)return "Y";
            if($asc>=-11055 and $asc<=-10247)return "Z";
        }else if(ord($s)>=48 and ord($s)<=57){
            switch(iconv_substr($s,0,1,'utf-8')){
                case 1:return "#";
                case 2:return "#";
                case 3:return "#";
                case 4:return "#";
                case 5:return "#";
                case 6:return "#";
                case 7:return "#";
                case 8:return "#";
                case 9:return "#";
                case 0:return "#";
            }
        }else if(ord($s)>=65 and ord($s)<=90){
            return substr($s,0,1);
        }else if(ord($s)>=97 and ord($s)<=122){
            return strtoupper(substr($s,0,1));
        }
        else
        {
            return iconv_substr($s0,0,1,'utf-8');
        }
    }

}