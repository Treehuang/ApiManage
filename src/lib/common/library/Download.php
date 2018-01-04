<?php

namespace common\library;

/**
 * 文件下载类
 */
class Download {
    /**
     * http下载文件
     *
     * Reads a file and send a header to force download it.
     *
     * @access public
     *
     * @param string $file 文件路径
     * @param string $rename 文件重命名后的名称
     *
     * @return bool
     */
    public static function render($file, $rename = null) {

        //参数分析
        if(!$file) {
            return false;
        }

        if(headers_sent()) {
            return false;
        }

        //分析文件是否存在
        if (!is_file($file) && !is_link($file)) {
            return false;
        }

        //分析文件名
        $filename = (!$rename) ? basename($file) : $rename;

        $invalid_link = false;

        //判断是否是无效链接
        if ( is_link($file) && !is_file($file) ) {
            $mime_type = "link/invalid";
            $target = readlink($file);
            $file_size = strlen($target);
            $invalid_link = true;
        } else {
            //获取文件类型
            $target = null;
            $fi = new \finfo(FILEINFO_MIME_TYPE);
            $mime_type = $fi->file($file);
            $file_size = filesize($file);
        }


        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime_type);
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);
        ob_clean();
        flush();

        if ( $invalid_link == true ) {
            echo $target;
            exit();
        }
        
        //打开文件 
        $fp = fopen($file, "rb"); 
        while (!feof($fp)) { 
            //设置文件最长执行时间 
            set_time_limit(0); 
            print(fread($fp, 1024 * 8)); //输出文件
            // $str = fread($fp, 1024 * 8);
            // $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'));
            // $str_encode = mb_convert_encoding($str, 'UTF-8', $encode);
            // print($str_encode);
            flush(); //输出缓冲 
            ob_flush(); 
        } 
        fclose($fp); 

        exit();
    }

    /**
     * @param string $data
     * @param string $filename
     * @param string $file_type
     * @return bool
     */
    public static function renderData($data, $filename, $file_type = "text/plain") {

        if(headers_sent()) {
            return false;
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $file_type);
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($data));
        ob_clean();
        echo $data;
        flush();

        //#$fp = fopen('data://'.$file_type.',' . $data, 'r');
        //#while (!feof($fp)) {
        //#    set_time_limit(0);
        //#    print fread($fp, 1024*8);
        //#    flush();
        //#    ob_flush();
        //#}
        //#fclose($fp);

        exit();
    }
}
