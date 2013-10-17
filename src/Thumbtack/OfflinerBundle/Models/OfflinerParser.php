<?php
/**
 * Created by JetBrains PhpStorm.
 * User: istrelnikov
 * Date: 9/19/13
 * Time: 4:43 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Application\Model;

use Zend\Dom\Query;

class PageSaverParser {
    public static function getPage($link){
        require_once('module/Application/src/Application/Misc/url_to_absolute.php');

        $page = array();
        if(PageSaverParser::retrieve_remote_file_size($link)>1024*1024){ // big files
            return null;
        }

        require_once('module/Application/src/Application/Misc/CasperJS/Casperjs.php');
        $casperJS = new \Casperjs('casperjs');
        $result = json_decode($casperJS->getHtml($link));
        $page['html'] = $result->html;
        if(!$page['html']){
            return null;
        }
        $page['content'] = $result->plain;
        $page['title'] = $result->title;
        $page['links'] = $result->links;
        /* Get the MIME type and character set */
        preg_match( '@<meta\s+http-equiv="Content-Type"\s+content="([\w/]+)(;\s+charset=([^\s"]+))?@i',
            $page['html'], $matches );
        if ( isset( $matches[3] ) )
            $initialEncoding = $matches[3];

        if(isset($initialEncoding)){
            if( $initialEncoding != 'UTF-8' ){
                $page['html'] = preg_replace('/<meta(.+)charset(.+)>/i','<meta http-equiv="content-type" content="text/html; charset=utf-8">', $page['html']);
                $page['html'] = mb_convert_encoding($page['html'],'UTF-8',$initialEncoding);
                file_put_contents('charsets.txt',$link.' - !ENCODED!',FILE_APPEND);
                }
        }else{
                //TODO: good search for html5 charset ->curl charset -> default 'latin1'
            $charset = mb_detect_encoding($page['html']);
            if($charset!='UTF-8'){
                $page['html'] = mb_convert_encoding($page['html'],'UTF-8',$charset);
                file_put_contents('charsets.txt',$link.' - !ENCODED!',FILE_APPEND);
            }
            $page['html'] = '<meta http-equiv="content-type" content="text/html; charset=utf-8">' . $page['html'];
        }

        return $page;
    }

    public static function prepareUri($url){
        $uri = str_replace(array('\\"','\\\'','\'','"'),'',$url);
        if(substr($uri, 0, 2) === '//'){
            $uri = 'http:'.$uri;
        }
        return $uri;
    }

    private static function retrieve_remote_file_size($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);
        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        return $size;
    }

    private static function get_url_data($url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.76 Safari/537.36');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    public static function uri_to_local($url,$type,$page_url){
        $ret['name'] = end(explode('/',$url));
        $ret['name'] = reset(explode('?', $ret['name']));
        $ret['name'] = reset(explode('%', $ret['name']));
        $ret['name'] ='attach';
        $parsed_page_url = parse_url($page_url);
        $ret['path'] = '/uploads/'.$parsed_page_url['host'].'/'.md5($page_url).'/'.$type.'/'.md5($url);
        return $ret;
    }
    public static function link_to_local($url){
        return 'preview?url='.md5($url);
    }

    public static function replaceProperty($data, $start, $end, $property, $alias, $limit = -1){
        //get blocks formed as: $start $property = "..." $end or $start $property = '...' $end
        $pattern = "!(".$start."){1}([^>]*?)".$property."\s*=\s*[\"\'](.*?)[\"\'](.*?)(".$end."){1}!s";
        $data = preg_replace($pattern, "{$start}\${2}{$property}=\"{$alias}\"\${4}{$end}", $data, $limit);
        return $data;
    }

    public static function matchProperty($data, $start, $end, $property){
        //get blocks formed as: $start $property = "..." $end or $start $property = '...' $end
        $pattern = "!(".$start."){1}([^>]*?)".$property."\s*=\s*[\"\'](.*?)[\"\'](.*?)(".$end."){1}!s";
        preg_match($pattern, $data, $data);
        return $data[3];
    }
}