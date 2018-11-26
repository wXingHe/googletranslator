<?php

/**
 * google翻译接口
 * Class GoogleTranslator
 */
Class GoogleTranslator{
    protected $url = "http://translate.google.cn/translate_a/single"; //谷歌翻译地址

    /**
     * 生成参数中的tk即token
     * @param $a string 文章内容
     * @return string 生成的token
     */
    protected function token($a) {
        $k = "";
        $b = 406644;
        $b1 = 3293161072;

        $jd = ".";
        $sb = "+-a^+6";
        $Zb = "+-3^+b+-f";
        for ($e = [], $f = 0, $g = 0; $g < mb_strlen($a,'UTF8'); $g++) {
            $char = mb_substr($a,$g,1,'utf-8');
            $m = $this->charCodeAt($char);
                if(128 > $m){
                    $e[$f++] = $m;

                }else if(2048 > $m){
                    $e[$f++] = $m >> 6 | 192;

                }else if(55296 == ($m & 64512) && $g + 1 < mb_strlen($a,'UTF8') && 56320 == ($this->charCodeAt(mb_substr($a,$g++,1,'utf-8')) & 64512)){
                    $m = 65536 + (($m & 1023) << 10) + ($this->charCodeAt(mb_substr($a,++$g,1,'utf-8')) & 1023);
                    $e[$f++] = $m >> 18 | 240;
                    $e[$f++] = $m >> 12 & 63 | 128;

                }else{
                    $e[$f++] = $m >> 12 | 224;
                    $e[$f++] = $m >> 6 & 63 | 128;
                    $e[$f++] = $m & 63 | 128;
                }
        }

        $a = $b;
        for ($f = 0; $f < count($e); $f++){
            $a += $e[$f];
            $a = $this->RL($a, $sb);
        }
        $a = $this->RL($a, $Zb);
        $a ^= ($b1 || 0 ? $b1 : 0 );
        0 > $a && ($a = ($a & 2147483647) + 2147483648);
        $a = fmod($a,1E6);
        return (String)($a).$jd.($a^$b);
    }

    /**
     * 字符在unicode中的位置
     * @param $str string 字符
     * @param string $encoding 输入编码
     * @param string $prefix 生成的编码的前缀
     * @param string $postfix 生成的编码的后缀
     * @return int 字符的位置(没有前后缀)
     */
    protected function charCodeAt($str, $encoding = 'utf-8', $prefix = '', $postfix = '') {
        //将字符串拆分
        $str = iconv("UTF-8", "gb2312", $str);
        $cind = 0;
        $arr_cont = array();
        for ($i = 0; $i < mb_strlen($str,'gb2312'); $i++) {
            if (mb_strlen(mb_substr($str, $cind, 1,'gb2312'),"gb2312") > 0) {
                if (ord(mb_substr($str, $cind, 1,'gb2312')) < 0xA1) { //如果为英文则取1个字节
                    array_push($arr_cont, mb_substr($str, $cind, 1,'gb2312'));
                    $cind++;
                } else {
                    array_push($arr_cont, mb_substr($str, $cind, 2,'gb2312'));
                    $cind+=2;
                }
            }
        }
        foreach ($arr_cont as &$row) {
            $row = iconv("gb2312", "UTF-8", $row);
        }
        $unicodestr = '';
        //转换Unicode码
        foreach ($arr_cont as $key => $value) {
            $unicodestr.= $prefix . base_convert(bin2hex(iconv('utf-8', 'UCS-4', $value)), 16, 10) .$postfix;
        }
        return $unicodestr;
    }

    /**
     * 加上字符之间的链接
     * @param $a string 字符
     * @param $b string 链接字符
     * @return int
     */
    protected function RL($a, $b) {
        $t = "a";
        $Yb = "+";
        for ($c = 0; $c < mb_strlen($b,'utf-8') - 2; $c += 3) {
            $d = mb_substr($b,$c + 2,1,'utf-8');
            if($d >= $t){
                $d = $this->charCodeAt(mb_substr($b,$c + 2,1,'utf-8')) - 87;
            }else {
                $d = intval($d);
            }
            $d = mb_substr($b,$c + 1,1,'utf-8') == $Yb ? $this->shr32($a,$d) : $a << $d;
            $a = mb_substr($b,$c,1,'utf-8') == $Yb ? $a + $d & 4294967295 : $a ^ $d;
        }
        return $a;
    }

    /**
     * 拼接url并获取翻译内容
     * @param $content
     */
    public function getContent($content,$from="zh-CN",$to="en"){
        $tk = $this->token($content);
        $tk = iconv('ASCII','utf-8',$tk);
        $tk = str_replace("-","",$tk);
        $query = [];
        $query["client"]="t";
        $query["sl"]=$from;
        $query["tl"]=$to;
        $query["hl"]="zh-CN";
        $query["dt"]="at";
        $query["dt"]="bd";
        $query["dt"]="ex";
        $query["dt"]="ld";
        $query["dt"]="md";
        $query["dt"]="qca";
        $query["dt"]="rw";
        $query["dt"]="rm";
        $query["dt"]="ss";
        $query["dt"]="t";
        $query["ie"]="UTF-8";
        $query["oe"]="UTF-8";
        $query["source"]="btn";
        $query["ssel"]="0";
        $query["tsel"]="0";
        $query["kc"] ="0";
        $query["tk"]= $tk;
        $query["q"]= $content;
        $queryString = http_build_query($query); //拼接查询字符串
        $headers = array(
            'User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
            'Accept-Language:zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7',
            'Cache-Control:no-cache',
            'Connection:keep-alive',
        );
        $ch = curl_init();
        $url = $this->url."?".$queryString;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //设置头
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        if($httpCode == 200){
            @$response = json_decode($response);
            $response = $response[0][0][0];
        }
        if(curl_errno($ch)) $response = "";
        curl_close ($ch);
        return $response;
    }

    /**
     * 无符号32位右移
     * @param mixed $x 要进行操作的数字，如果是字符串，必须是十进制形式
     * @param string $bits 右移位数
     * @return mixed 结果，如果超出整型范围将返回浮点数
     */
    protected function shr32($x, $bits){
        // 位移量超出范围的两种情况
        if($bits <= 0){
            return $x;
        }
        if($bits >= 32){
            return 0;
        }
        //转换成代表二进制数字的字符串
        $bin = decbin($x);
        $l = strlen($bin);
        //字符串长度超出则截取底32位，长度不够，则填充高位为0到32位
        if($l > 32){
            $bin = substr($bin, $l - 32, 32);
        }elseif($l < 32){
            $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
        }
        //取出要移动的位数，并在左边填充0
        return bindec(str_pad(substr($bin, 0, 32 - $bits), 32, '0', STR_PAD_LEFT));
    }

    /**
     * 无符号32位左移
     * @param mixed $x 要进行操作的数字，如果是字符串，必须是十进制形式
     * @param string $bits 左移位数
     * @return mixed 结果，如果超出整型范围将返回浮点数
     */
    protected function shl32 ($x, $bits){
        // 位移量超出范围的两种情况
        if($bits <= 0){
            return $x;
        }
        if($bits >= 32){
            return 0;
        }
        //转换成代表二进制数字的字符串
        $bin = decbin($x);
        $l = strlen($bin);
        //字符串长度超出则截取底32位，长度不够，则填充高位为0到32位
        if($l > 32){
            $bin = substr($bin, $l - 32, 32);
        }elseif($l < 32){
            $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);
        }
        //取出要移动的位数，并在右边填充0
        return bindec(str_pad(substr($bin, $bits), 32, '0', STR_PAD_RIGHT));
    }

}

