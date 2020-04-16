<?php
/**
 * android、ios推送类.
 * User: Jett
 * Date: 2019-07-02
 * Time: 20:10
 */
require_once(dirname(__FILE__) .'/'.'umeng/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) .'/'.'umeng/android/AndroidUnicast.php');
require_once(dirname(__FILE__) .'/'.'umeng/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) .'/'.'umeng/ios/IOSUnicast.php');

class PushMessage {
    protected $appkey           = NULL;
    protected $appMasterSecret     = NULL;
    protected $timestamp        = NULL;
    protected $validation_token = NULL;
    private $param = [];

    function __construct($appkey, $appMasterSecret) {
        $this->appkey = $appkey;
        $this->appMasterSecret = $appMasterSecret;
        $this->timestamp = strval(time());
    }

    function __set($name, $value)
    {
        $this->param[$name] = $value;
    }

    function __get($name)
    {
        if(isset($this->param[$name])){
            return $this->param[$name];
        }else{
            return false;
        }
    }

    function sendAndroidBroadcast() {
        try {
            $brocast = new AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker",           $this->ticker);
            $brocast->setPredefinedKeyValue("title",            $this->title);
            $brocast->setPredefinedKeyValue("text",             $this->text);
            $brocast->setPredefinedKeyValue("after_open",       $this->after_open);
            if($this->after_open == "go_activity") {
                $brocast->setPredefinedKeyValue("activity",       $this->activity);
            }
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            //$brocast->setPredefinedKeyValue("production_mode", "true");
            $brocast->setPredefinedKeyValue("production_mode", PUSHENV);
            // [optional]Set extra fields
            $brocast->setExtraField("extra", $this->extra);
            //print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            //print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    function sendAndroidUnicast() {
        try {
            $unicast = new AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $this->device_tokens);
            $unicast->setPredefinedKeyValue("ticker",           $this->ticker);
            $unicast->setPredefinedKeyValue("title",            $this->title);
            $unicast->setPredefinedKeyValue("text",             $this->text);
            $unicast->setPredefinedKeyValue("after_open",       $this->after_open);
            if($this->after_open == "go_activity") {
                $unicast->setPredefinedKeyValue("activity",       $this->activity);
            }
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", PUSHENV);
            // Set extra fields
            $unicast->setExtraField("extra", $this->extra);
            //print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            //print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    function sendIOSBroadcast() {
        try {
            $brocast = new IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            $brocast->setPredefinedKeyValue("alert", $this->alert);
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", PUSHENV);
            // Set customized fields
            $brocast->setCustomizedField("extra", $this->extra);
            //print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            //print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

    function sendIOSUnicast() {
        try {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $this->device_tokens);
            $unicast->setPredefinedKeyValue("alert", $this->alert);
            $unicast->setPredefinedKeyValue("badge", 0);
            $unicast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $unicast->setPredefinedKeyValue("production_mode", PUSHENV);
            // Set customized fields
            $unicast->setCustomizedField("extra", $this->extra);
            //print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            //print("Sent SUCCESS\r\n");
        } catch (Exception $e) {
            print("Caught exception: " . $e->getMessage());
        }
    }

}