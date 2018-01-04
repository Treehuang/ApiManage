<?php
/**
 * @author index
 *   ┏┓   ┏┓+ +
 *  ┏┛┻━━━┛┻┓ + +
 *  ┃       ┃
 *  ┃  ━    ┃ ++ + + +
 * ████━████┃+
 *  ┃       ┃ +
 *  ┃  ┻    ┃
 *  ┃       ┃ + +
 *  ┗━┓   ┏━┛
 *    ┃   ┃
 *    ┃   ┃ + + + +
 *    ┃   ┃     Codes are far away from bugs with the animal protecting
 *    ┃   ┃ +         神兽保佑,代码无bug
 *    ┃   ┃
 *    ┃   ┃   +
 *    ┃   ┗━━━┓ + +
 *    ┃       ┣┓
 *    ┃       ┏┛
 *    ┗┓┓┏━┳┓┏┛ + + + +
 *     ┃┫┫ ┃┫┫
 *     ┗┻┛ ┗┻┛+ + + +
 */

namespace common\library;
use common\core\Configure;
use Noodlehaus\Exception;
use Swift_Mailer;


/**
 * Class EasyEmail
 * @memo 邮件模块
 * @package common\library
 */
class EasyEmail extends \Swift_Message
{
    private $fromName = null;

    /**
     * @param null $subject
     * @param null $body
     * @param null $contentType
     * @param null $charset
     * @return EasyEmail
     */
    public static function newInstance($subject = null, $body = null, $contentType = null, $charset = null)
    {
        return new self($subject, $body, $contentType, $charset);
    }

    /**
     * @return Swift_Mailer
     */
    protected function getMailer()
    {
        $server = Configure::get('email.smtp_server');
        $port = Configure::get('email.smtp_port');
        $encryption = Configure::get('email.smtp_encryption');
        $username = Configure::get('email.username', null);
        $password = Configure::get('email.password', null);

        try {
            $transport = \Swift_SmtpTransport::newInstance($server, $port, $encryption);
            !is_null($username) && $transport->setUsername($username);
            !is_null($password) && $transport->setPassword($password);

            return \Swift_Mailer::newInstance($transport);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return null;
        }
    }

    /**
     * @param string $fromName
     * @return $this
     */
    public function setFromName($fromName)
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return int 返回码
     */
    public function send()
    {
        // 设置发件人信息
        $fromName = $this->fromName;
        $from = Configure::get('email.from');
        is_null($fromName) && $fromName = Configure::get('email.from_name', null);
        $this->setFrom($from, $fromName);

        // 获取邮件发送器
        $mailer = $this->getMailer();
        if (is_null($mailer)) return ReturnCode::NETWORK_EMAIL_CONNECT_FAILED;

        // 发送邮件
        try {
            $mailer->send($this);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return ReturnCode::NETWORK_EMAIL_SEND_FAILED;
        }
        return 0;
    }

}