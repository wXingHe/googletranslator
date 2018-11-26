/**
 * 获取翻译内容
 * @param content 需翻译的内容
 * @returns {*}
 */
function translator(content,from="zh-CN",to="en") {
    let tk = token(content);
    let query = [
        "client=t",
        "sl="+from,
        "tl="+to,
        "hl=zh-CN",
        "dt=at",
        "dt=bd",
        "dt=ex",
        "dt=ld",
        "dt=md",
        "dt=qca",
        "dt=rw",
        "dt=rm",
        "dt=ss",
        "dt=t",
        "ie=UTF-8",
        "oe=UTF-8",
        "source=btn",
        "ssel=0",
        "tsel=0",
        "kc=0",
        "tk="+tk,
        "q="+content
    ];
    let queryString = query.join("&");
    let resp = http.get("http://translate.google.cn/translate_a/single?" + queryString, {
        headers: {
            'User-Agent': 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
            'Accept-Language': 'zh-CN,zh;q=0.9,en;q=0.8,zh-TW;q=0.7',
            'Cache-Control': 'no-cache',
            Connection: 'keep-alive',
        }
    });
    let response = resp.body.read().toString();
    if(resp.statusCode == 200){
        return {
            code:1,
            content:JSON.parse(response)[0][0][0]
        }
    }else{
        return {
            code:0,
            content:"网络连接失败"
        }
    }
}

/**
 * 获取tk
 * @param a
 * @returns {string}
 */
function token(a) {
    var k = "";
    var b = 406644;
    var b1 = 3293161072
        var jd = ".";
    var sb = "+-a^+6";
    var Zb = "+-3^+b+-f";
    for (var e = [], f = 0, g = 0; g < a.length; g++)
    {
        var m = a.charCodeAt(g);
        128 > m ? e[f++] = m : (2048 > m ? e[f++] = m >> 6 | 192 : (55296 == (m & 64512) && g + 1 < a.length && 56320 == (a.charCodeAt(g + 1) & 64512) ? (m = 65536 + ((m & 1023) << 10) + (a.charCodeAt(++g) & 1023), e[f++] = m >> 18 | 240, e[f++] = m >> 12 & 63 | 128) : e[f++] = m >> 12 | 224, e[f++] = m >> 6 & 63 | 128), e[f++] = m & 63 | 128)
    }
    a = b;
    for (f = 0; f < e.length; f++) a += e[f], a = RL(a, sb);
    a = RL(a, Zb);
    a ^= b1 || 0;
    0 > a && (a = (a & 2147483647) + 2147483648); a %= 1E6;
    return a.toString() + jd + (a ^ b)
}

/**
 * 添加连接符
 * @param a
 * @param b
 * @returns {*}
 * @constructor
 */
function RL(a, b) {
    var t = "a"; var Yb = "+";
    for (var c = 0; c < b.length - 2; c += 3) {
        var d = b.charAt(c + 2), d = d >= t ? d.charCodeAt(0) - 87 : Number(d), d = b.charAt(c + 1) == Yb ? a >>> d: a << d;
        a = b.charAt(c) == Yb ? a + d & 4294967295 : a ^ d
    }
    return a
}

//简单的使用
var http = require('http');
var content = "今天天气不错";
var text = translator(content);//可以填第二个以及第三个参数来,规定原语言和要翻译的语言,默认中译英
console.log(text);
