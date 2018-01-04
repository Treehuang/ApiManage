<?php
/**
 * @author index
 */

namespace common\library;


use common\core\Configure;

/**
 * Class EmailService
 * @package common\library
 */
abstract class EmailService
{
    /**
     * 获取Mailer
     *
     * @param \Swift_Mailer &$mailer 邮件操作实例
     * @return int 返回码
     */
    public static function getMailer(&$mailer)
    {
        $server = Configure::get('email.smtp_server');
        $port = Configure::get('email.smtp_port');
        $encryption = Configure::get('email.smtp_encryption');
        empty($encryption) && $encryption = null;
        $username = Configure::get('email.username');
        $password = Configure::get('email.password');

        try {
            $transport = \Swift_SmtpTransport::newInstance($server, $port, $encryption)
                ->setUsername($username)
                ->setPassword($password);

            $mailer = \Swift_Mailer::newInstance($transport);
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            return ReturnCode::NETWORK_EMAIL_CONNECT_FAILED;
        }
        return 0;
    }

    /**
     * 发送邮件
     *
     * @param string|null $fromName 发件人名称
     * @param string $subject 邮件标题
     * @param array|string $to 目标邮箱地址
     * @param array|string|null $cc 抄送地址
     * @param string $body 邮件内容
     * @param string $contentType 内容类型
     * @return int 返回码
     */
    public static function send($fromName = null, $subject, $to, $cc = null, $body, $contentType = 'text/plain')
    {
        // 读取发送地址
        $from = Configure::get('email.from');
        is_null($fromName) && $fromName = Configure::get('email.from_name');

        // 生成消息对象实例
        $message = \Swift_Message::newInstance($subject)->setFrom($from, $fromName)->setTo($to);
        // 判断是否需要抄送
        !is_null($cc) && !empty($cc) && $message = $message->setCc($cc);
        // 设置消息体
        $message = $message->setBody($body, $contentType, 'utf-8');

        /** @var \Swift_Mailer $mailer */
        $code = self::getMailer($mailer);
        if ($code !== 0) return $code;

        // 发送邮件
        $mailer->send($message);

        return 0;
    }
}