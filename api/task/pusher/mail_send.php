<?php
/**
 * 发送邮件
 */
require_once __DIR__ . '/../data.config.php';
require_once __DIR__ . '/../base.php';
require_once __DIR__ . '/../redis.php';

class mail_send extends base {

    /**
     * SMTP列表
     * 从主索引库中获取
     */
    protected $smtp;

    function action()
    {
        echo date('Y-m-d H:i:s') . ' start ' . PHP_EOL;

        $queueName = 'mail_send_queue';

        while (true) {
            $queueSize = $this->redis()->Llen($queueName);

            if ($queueSize > 0){
                echo $queueSize.PHP_EOL;
            }

            $queueData = $this->redis()->lPop($queueName);

            if ($queueData !== false) {
                $result = $this->sendMail($queueData);
	            sleep(2);
            } else {
                sleep(5);
            }
        }

        echo date('Y-m-d H:i:s') . ' end ' . PHP_EOL;
    }


    /**
     * 发送邮件
     */
    protected function sendMail($queueData) {
        $queueData = json_decode($queueData, true);

        $email = $queueData['receive'];
        $subject = $queueData['subject'];
        $content = $queueData['body'];

        if ( empty( $email ) || empty( $subject ) || empty( $content ) ){
            return false;
        }

	    /*
		if(substr($email,-7, strlen($email)) === '163.com' || substr($email,-7, strlen($email)) === '126.com' )
		{
			$mdl_smtp = $this->db( 'index', 'smtp' );
			$smtp = $mdl_smtp->getByWhere( array( 'name' => '163' ) );
		}
		elseif(substr($email,-6, strlen($email)) === 'QQ.com' || substr($email,-6, strlen($email)) === 'qq.com' || substr($email,-7, strlen($email)) === '139.com')
		{
			$mdl_smtp = $this->db( 'index', 'smtp' );
			$smtp = $mdl_smtp->getByWhere( array( 'name' => 'QQ' ) );
		}

		if ( empty($smtp) ) {
			$list = $this->getSmtp();
			if ( !$list ) return false;

			$smtp = $list[time() % count( $list )];
			if ( !$smtp ) return false;
		}
		*/

	    $list = $this->getSmtp();
	    if ( !$list ) return false;

	    $smtp = $list[time() % count( $list )];
	    if ( !$smtp ) return false;

        return $this->smtp_mail( $email, $subject, $content, $smtp['host'], $smtp['port'], $this->authcode( $smtp['username'], 'd', $smtp['salt'] ), $this->authcode( $smtp['password'], 'd', $smtp['salt'] ), $smtp['senderEmail'], $smtp['senderName'] );
    }


    /**
     * 获取smtp列表
     */
    protected function getSmtp() {
        if ( $this->smtp ) return $this->smtp;

        //先从redis中查找，过期时间为5分钟
        $smtp = $this->redis()->get( 'smtp' );
        if ( !$smtp ) {
            //从数据库中生成
            $mdl_smtp = $this->db( 'index', 'smtp' );
            $data = $mdl_smtp->getList( null, array( 'status' => 1 ) );
            $smtp = array();
            foreach ( $data as $item ) {
                $smtp[] = $item;
            }
            $this->redis()->set( 'smtp', $smtp, 5 * 60 );
        }
        $this->smtp = $smtp;
        return $this->smtp;
    }


    function smtp_mail( $email, $subject, $body, $host, $port, $un, $up, $fromEmail = '', $fromName = '', $attachments = '' ) {

        require_once(dirname(__FILE__) .'/'.'phpmailer/class.phpmailer.php');
        require_once(dirname(__FILE__) .'/'.'phpmailer/class.smtp.php');

        $mail = new PHPMailer();
        //$mail->SMTPDebug = 4;
        $mail->IsSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        if ( $port == '465' ) $mail->SMTPSecure = 'ssl';
        $mail->SMTPAuth = true;
        $mail->Username = $un;
        $mail->Password = $up;

        $mail->Sender = $fromEmail ? $fromEmail : $email;
        $mail->From = $fromEmail ? $fromEmail : $email;
        $mail->FromName = $fromName ? $fromName : $mail->From;
        if ( $fromEmail ) $mail->setFrom( $fromEmail, $fromName );
        $mail->AddReplyTo( $fromEmail ? $fromEmail : $email, $fromName ? $fromName : $mail->From );

        if ( $attachments ) {
            foreach ( $attachments as $attachment ) {
                $mail->AddAttachment( $attachment['file'], $attachment['name'] );
            }
        }

        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->AddAddress( $email, $email );

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = 'text/html';

        if ( $mail->Send() ) return true;
        else {
            return $mail->ErrorInfo;
        }
    }

    function authcode( $string, $operation = 'd', $key = '', $expiry = 0 ) {
        $ckey_length = 4;

        //密匙
        $key = md5( $key );

        $keya = md5( substr( $key, 0, 16 ) );
        $keyb = md5( substr( $key, 16, 16 ) );
        $keyc = $ckey_length ? ( $operation == 'd' ? substr( $string, 0, $ckey_length ): substr( md5( microtime() ), -$ckey_length ) ) : '';
        $cryptkey = $keya.md5( $keya.$keyc );
        $key_length = strlen( $cryptkey );
        $string = $operation == 'd' ? base64_decode( substr( $string, $ckey_length ) ) : sprintf( '%010d', $expiry ? $expiry + time() : 0 ).substr( md5( $string.$keyb ), 0, 16 ).$string;
        $string_length = strlen( $string );
        $result = '';
        $box = range( 0, 255 );
        $rndkey = array();
        for ( $i = 0; $i <= 255; $i++ ) {
            $rndkey[$i] = ord( $cryptkey[$i % $key_length] );
        }
        for ( $j = $i = 0; $i < 256; $i++ ) {
            $j = ( $j + $box[$i] + $rndkey[$i] ) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ( $a = $j = $i = 0; $i < $string_length; $i++ ) {
            $a = ( $a + 1 ) % 256;
            $j = ( $j + $box[$a] ) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr( ord( $string[$i] ) ^ ( $box[( $box[$a] + $box[$j] ) % 256] ) );
        }
        if ( $operation == 'd' ) {
            if ( ( substr( $result, 0, 10 ) == 0 || substr( $result, 0, 10 ) - time() > 0 ) && substr( $result, 10, 16 ) == substr( md5( substr( $result, 26 ).$keyb ), 0, 16 ) ) {
                return substr( $result, 26 );
            } else {
                return '';
            }
        } else {
            return $keyc.str_replace( '=', '', base64_encode( $result ) );
        }
    }

}

set_time_limit( 0 );
$markets = new mail_send();
$markets->action();