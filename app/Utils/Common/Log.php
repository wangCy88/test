<?php
date_default_timezone_set("PRC");
class Log
{
    //普通日志
    public static function LogWirte($Astring, $filename)
    {
        $path =  '/home/log/';
        $file = $path . date('Ymd',time()) . '-' . $filename . ".txt";
        if(!is_dir($path)){	mkdir($path); }
        $LogTime = date('Y-m-d H:i:s',time());
        if(!file_exists($file))
        {
            $logfile = fopen($file, "w") or die("Unable to open file!");
            fwrite($logfile, "[$LogTime]:".$Astring."\r\n");
            fclose($logfile);
        }else{
            $logfile = fopen($file, "a") or die("Unable to open file!");
            fwrite($logfile, "[$LogTime]:".$Astring."\r\n");
            fclose($logfile);
        }
    }

    //存储报告数据
    public static function txtWirte($Astring, $filename)
    {
        $path =  '/home/log/';
        $file = $path . date('Ym',time()) . '-' . $filename . ".txt";
        if(!is_dir($path)){	mkdir($path); }
        //$LogTime = date('Y-m-d H:i:s',time());
        if(!file_exists($file))
        {
            $logfile = fopen($file, "w") or die("Unable to open file!");
            fwrite($logfile, $Astring."\r\n");
            fclose($logfile);
        }else{
            $logfile = fopen($file, "a") or die("Unable to open file!");
            fwrite($logfile, $Astring."\r\n");
            fclose($logfile);
        }
    }
}

?>
