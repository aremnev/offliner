<?php
function normilize_url($url) {
    $url = rtrim($url, '/');
    $url = str_replace(array('\\"', '\\\'', '\'', '"'), '', $url);
    $tmp = explode('#', $url);
    $url = reset($tmp);
    if (substr($url, 0, 2) === '//') {
        $url = 'http:' . $url;
    }
    $url = str_replace('www.', '', $url);
    $url = preg_replace('#(?:http(s)?://)?(.+)#', 'http\1://\2', $url);
    return $url;
}