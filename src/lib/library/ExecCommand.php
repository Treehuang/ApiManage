<?php
/**
 * Created by IntelliJ IDEA.
 * User: Kinming
 * Date: 2017/12/12
 * Time: 上午9:23.
 */

namespace library;

class ExecCommand
{
    /**
     * 执行ls命令.
     *
     * @param $rs
     * @param $code
     *
     * @author Kinming
     */
    public static function execLs(&$rs, &$code)
    {
        exec('ls', $rs, $code);
    }

    /**
     * 执行mv命令.
     *
     * @param $old
     * @param $new
     *
     * @author Kinming
     */
    public static function execMv($old, $new)
    {
        exec("mv $old $new");
    }

    /**
     * 执行rm命令.
     *
     * @param $file
     *
     * @author Kinming
     */
    public static function execRm($file)
    {
        exec("rm -rf $file");
    }

    /**
     * 执行git checkout命令.
     *
     * @param $branch
     *
     * @author Kinming
     */
    public static function execGitCheckout($branch)
    {
        exec("git checkout $branch");
    }

    /**
     * 执行git add命令.
     *
     * @param $file
     *
     * @author Kinming
     */
    public static function execGitAdd($file)
    {
        exec("git add $file");
    }

    /**
     * 执行git commit命令.
     *
     * @param $comment
     *
     * @author Kinming
     */
    public static function execGitCommit($comment)
    {
        //一定要加单引号
        exec("git commit -m '$comment'");
    }

    /**
     * 执行git push命令.
     *
     * @param $param
     * @param $rs
     * @param $code
     *
     * @author Kinming
     */
    public static function execGitPush($param, &$rs, &$code)
    {
        exec("git push $param", $rs, $code);
    }

    /**
     * 执行git pull命令.
     *
     * @author Kinming
     */
    public static function execGitPull()
    {
        exec('git pull');
    }

    /**
     * 执行git pull -f命令.
     *
     * @author Kinming
     */
    public static function execGitForcePull()
    {
        exec('git pull -f');
    }

    /**
     * 执行git clone命令.
     *
     * @param $url
     * @param $rs
     * @param $code
     *
     * @author Kinming
     */
    public static function execGitClone($url, &$rs, &$code)
    {
        exec("git clone $url 2>&1", $rs, $code);
    }

    /**
     * 执行touch命令.
     *
     * @param $file
     *
     * @author Kinming
     */
    public static function execTouch($file)
    {
        exec("touch $file");
    }

    /**
     * 执行git branch -r命令.
     *
     * @param $bra
     * @param $code
     *
     * @author Kinming
     */
    public static function execGitRemoteBranch(&$bra, &$code)
    {
        exec('git branch -r', $bra, $code);
    }

    /**
     * 执行git reset命令.
     *
     * @author Kinming
     */
    public static function execGitResetHead()
    {
        exec('git reset --hard HEAD~');
    }

    /**
     * 执行git add commit push命令.
     *
     * @param $file
     * @param $comment
     * @param $push
     * @param $rs
     * @param $code
     *
     * @author Kinming
     */
    public static function execGitAddCommitPush($file, $comment, $push, &$rs, &$code)
    {
        self::execGitAdd($file);
        self::execGitCommit($comment);
        self::execGitPush($push, $rs, $code);
    }
}
