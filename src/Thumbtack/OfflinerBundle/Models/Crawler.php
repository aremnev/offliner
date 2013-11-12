<?php

namespace Thumbtack\OfflinerBundle\Models;
require_once(__DIR__ . '/../Misc/url_to_absolute.php'); // Review: move to top --Resolved
class Crawler {
    public static function getPage($link) {
        error_reporting(E_ERROR | E_PARSE); // Review use  -1
        $page = array();
        if (Crawler::retrieve_remote_file_size($link) > 1024 * 1024) { // big files
            return array(); // Review: you return $page at the end of function, use certain retunrned parameters, in this case - array() --Resolved..
        }
        $page['html'] = Crawler::get_url_data($link);
        if (!$page['html']) {
            return array();
        }

        /* Get the MIME type and character set */
        preg_match('@<meta\s+http-equiv=["\']Content-Type["\']\s+content=["\']([\w/]+)(;\s+charset=([^\s"\']+))?|<meta(.+)charset=(.+)/>@i', $page['html'], $matches);
        if (isset($matches[3])) $initialEncoding = $matches[3];
        if (isset($matches[5])) $initialEncoding = trim($matches[5], '"\' ');
        if (isset($initialEncoding)) {
            if (strtolower($initialEncoding) != 'utf-8') {
                $page['html'] = preg_replace('/<meta(.+)charset(.+)>/i', '<meta http-equiv="content-type" content="text/html; charset=utf-8">', $page['html']);
                $page['html'] = mb_convert_encoding($page['html'], 'UTF-8', $initialEncoding);
            }
        } else {
            //TODO: good search for html5 charset ->curl charset -> default 'latin1'
            $charset = mb_detect_encoding($page['html']);
            if ($charset != 'UTF-8') {
                $page['html'] = mb_convert_encoding($page['html'], 'UTF-8', $charset);
            }
            $page['html'] = '<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $page['html'];
        }
        /*** a new dom object ***/
        $dom = new \DOMDocument();
        $dom->recover = TRUE;
        $dom->strictErrorChecking = FALSE;
        $dom->loadHTML($page['html']);
        /*** replace $link if <base> ***/
        $base_xpth = new \DOMXPath($dom);
        $base_tag = $base_xpth->evaluate('//base');
        $cnt = $base_tag->length;
        for ($i = 0; $i < $cnt; $i++) {
            $link = $base_tag->item($i)->attributes->getNamedItem('href')->nodeValue;
        }
        /*** plaintext to index ***/
        $page['plain'] = '';
        $text_xpth = new \DOMXPath($dom);
        $text_tags = $text_xpth->evaluate("//text()[not(ancestor::script)][not(ancestor::style)][not(ancestor::noscript)]");

        $cnt = $text_tags->length;
        for ($i = 0; $i < $cnt; $i++) {
            $val = trim($text_tags->item($i)->nodeValue);
            if (strlen($val) > 3) {
                $page['plain'] .= $val . ' ';
            }
        };

        /*** title ***/
        $title_xpth = new \DOMXPath($dom);
        $title_tag = $title_xpth->evaluate('//title');
        $page['title'] = $title_tag->item(0)->nodeValue;
        /*** save inner JS ***/
        preg_match_all("/<script[^>]*>(.*)<.*script>/Uis", $page['html'], $innerJS);
        $page['html'] = preg_replace("/<script[^>]*>(.*)<.*script>/Uis", '!!!PUT_SCRIPT_HERE!!!', $page['html']);
        @$dom->loadHTML($page['html']);
        /*** links ***/

        $a_xpth = new \DOMXPath($dom);
        $a_tags = $a_xpth->evaluate('//a');
        $links = array();
        $cnt = $a_tags->length;
        for ($i = 0; $i < $cnt; $i++) {
            $url = $a_tags->item($i)->getAttribute('href');
            $url = url_to_absolute($link, $url);
            $links[] = $url;
            $a_tags->item($i)->setAttribute('href', $url);
        }
        $frame_xpth = new \DOMXPath($dom);
        $frame_tags = $frame_xpth->query('//iframe');
        $cnt = $frame_tags->length;
        for ($i = 0; $i < $cnt; $i++) {
            $url = $frame_tags->item($i)->getAttribute('src');
            $url = url_to_absolute($link, $url);
            $links[] = $url;
            $frame_tags->item($i)->setAttribute('src', $url);
        }
        $page['links'] = $links;
        $page['html'] = $dom->saveHTML();
        foreach ($innerJS[0] as $js) {
            $url = Crawler::matchProperty($js, "<script", "</script>", "src");
            $url = url_to_absolute($link, $url);
            $scripts[] = $url;
            $js = Crawler::replaceProperty($js, "<script", "</script>", "src", $url, 1);
            $page['html'] = preg_replace("/!!!PUT_SCRIPT_HERE!!!/", $js, $page['html'], 1);
        }
        return $page;
    }

    private static function retrieve_remote_file_size($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return $size;
    }

    private static function get_url_data($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.76 Safari/537.36');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function replaceProperty($data, $start, $end, $property, $alias, $limit = -1) {
        //get blocks formed as: $start $property = "..." $end or $start $property = '...' $end
        $pattern = "!(" . $start . "){1}([^>]*?)" . $property . "\s*=\s*[\"\'](.*?)[\"\'](.*?)(" . $end . "){1}!s";
        $data = preg_replace($pattern, "{$start}\${2}{$property}=\"{$alias}\"\${4}{$end}", $data, $limit);
        return $data;
    }

    public static function matchProperty($data, $start, $end, $property) {
        //get blocks formed as: $start $property = "..." $end or $start $property = '...' $end
        $pattern = "!(" . $start . "){1}([^>]*?)" . $property . "\s*=\s*[\"\'](.*?)[\"\'](.*?)(" . $end . "){1}!s";
        preg_match($pattern, $data, $data);
        return $data[3];
    }
}