<?php
// 基础设置
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(300);
ignore_user_abort(true);

// 获取传入链接
if (!isset($_GET['url'])) {
    echo json_encode(['code' => 400, 'msg' => '缺少参数 url']);
    exit;
}

$url = trim($_GET['url']);

// 快手短链接跳转 & 提取真实视频页面
function curlRequest($url, $include_header = false) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随跳转
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36');
    if ($include_header) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    }
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

// 请求短链接，得到实际页面内容
$html = curlRequest($url);

// 解析无水印视频链接
preg_match('/"photoUrl":"(.*?)"/', $html, $matches);
$video_url = isset($matches[1]) ? stripslashes($matches[1]) : '';

// 解析作者昵称
preg_match('/"userName":"(.*?)"/', $html, $name_match);
$nickname = isset($name_match[1]) ? $name_match[1] : '未知用户';

// 解析作者签名
preg_match('/"userCaption":"(.*?)"/', $html, $sign_match);
$signature = isset($sign_match[1]) ? $sign_match[1] : '';

// 判断解析结果
if (!$video_url) {
    echo json_encode([
        'code' => 500,
        'msg' => '解析失败，可能链接已失效或接口被目标网站限制'
    ]);
    exit;
}

// 返回结果
echo json_encode([
    'code' => 200,
    'msg' => '解析成功',
    'nickname' => $nickname,
    'signature' => $signature,
    'video_url' => $video_url
], JSON_UNESCAPED_UNICODE);
