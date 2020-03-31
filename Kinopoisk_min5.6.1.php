<?php

namespace Ministra\Lib;

use DOMDocument;
use DOMXPath;
use Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50;
class Kinopoisk implements \Ministra\Lib\StbApi\VClubinfo
{
    public static function getInfoById($id, $type = null)
    {
        $movie_info = ['kinopoisk_id' => $id];
        $movie_url = 'https://www.kinopoisk.ru/film/' . $id . '/';
        $series_url = 'https://www.kinopoisk.ru/series/' . $id . '/';
        $headers = @get_headers($series_url);
        $is_film = true;
        if (strpos($headers[0], '404') == 0) {
            $movie_url = 'https://www.kinopoisk.ru/series/' . $id . '/';
            $is_film = false;
        }
        $movie_info['kinopoisk_url'] = $movie_url;
        $movie_info['cover'] = 'https://kinopoisk.ru/images/film/' . $id . '.jpg';
        $cover_big_url = 'https://kinopoisk.ru/images/film_big/' . $id . '.jpg';
        $big_cover_headers = \get_headers($cover_big_url, 1);
        if ($big_cover_headers !== false) {
            if (\strpos($big_cover_headers[0], '302') !== false && !empty($big_cover_headers['Location'])) {
                $movie_info['cover_big'] = $big_cover_headers['Location'];
            } else {
                $movie_info['cover_big'] = $cover_big_url;
            }
        }
        $ch = \curl_init();
        $curl_options = [CURLOPT_URL => $movie_url, CURLOPT_HEADER => false, CURLOPT_RETURNTRANSFER => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_HTTPHEADER => ['Connection: keep-alive', 'Cache-Control: no-cache', 'Pragma: no-cache', 'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.5 ' . '(KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5', 'Accept: text/css,*/*;q=0.1', 'Accept-Encoding: deflate,sdch', 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4', 'Accept-Charset: utf-8,windows-1251;q=0.7,*;q=0.3', 'Content-Type: text/html,charset=utf-8']];
        if (\Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::exist('http_proxy')) {
            $curl_options[CURLOPT_PROXY] = \str_replace('tcp://', '', \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::get('http_proxy'));
            if (\Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::exist('http_proxy_login') && \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::exist('http_proxy_password')) {
                $curl_options[CURLOPT_PROXYUSERPWD] = \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::get('http_proxy_login') . ':' . \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::get('http_proxy_password');
            }
        }
        \curl_setopt_array($ch, $curl_options);
        $page = \curl_exec($ch);
        \curl_close($ch);
        \libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHTML($page);
        \libxml_use_internal_errors(false);
        $xpath = new \DOMXPath($dom);
        if ($is_film) {
            $node_list = $xpath->query('//*[@id="headerFilm"]/h1');
        } else {
            $node_list = $xpath->query('//*[@class="film-header-group film-basic-info__title"]/h1');
        }
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['name'] = self::getNodeText($node_list->item(0));
        }
        if (empty($movie_info['name'])) {
            throw new \Ministra\Lib\KinopoiskException(\sprintf(\_("Movie name in '%s' not found"), $movie_url), $page);
        }
        if ($is_film) {
            $node_list = $xpath->query('//*[@id="headerFilm"]/span');
        } else {
            $node_list = $xpath->query('//*[@class="film-header-group film-basic-info__title"]/span');
        }
        
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['o_name'] = self::getNodeText($node_list->item(0));
        }
        if (empty($movie_info['o_name'])) {
            $movie_info['o_name'] = $movie_info['name'];
        }
        if ($is_film) {
            $node_list = $xpath->query('//*[@id="infoTable"]/table/tr[1]/td[2]/div/a');
        } else {
            $node_list = $xpath->query('//*[@class="table-col-years__years"]');
        }
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['year'] = self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//*[@id="infoTable"]/table/tr[2]/td[2]/div');
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['country'] = self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//*[@id="runtime"]');
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['duration'] = (int) self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//*[@id="infoTable"]/table/tr[4]/td[2]/a');
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['director'] = self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//*[@id="actorList"]/ul[1]/li');
        if ($node_list !== false && $node_list->length != 0) {
            $actors = [];
            foreach ($node_list as $node) {
                $actors[] = self::getNodeText($node);
            }
            if ($actors[\count($actors) - 1] == '...') {
                unset($actors[\count($actors) - 1]);
            }
            $movie_info['actors'] = \implode(', ', $actors);
        }
        $node_list = $xpath->query('//div[@itemprop="description"]');
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['description'] = self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//div[contains(@class, "ageLimit")]');
        if ($node_list !== false && $node_list->length != 0) {
            $class = $node_list->item(0)->attributes->getNamedItem('class')->nodeValue;
            $movie_info['age'] = \substr($class, \strrpos($class, 'age') + 3);
            if (\is_numeric($movie_info['age'])) {
                $movie_info['age'] .= '+';
            }
        }
        $node_list = $xpath->query('//td[contains(@class, "rate_")]');
        if ($node_list !== false && $node_list->length != 0) {
            $class = $node_list->item(0)->attributes->getNamedItem('class')->nodeValue;
            $movie_info['rating_mpaa'] = \strtoupper(\substr($class, 5));
            if ($movie_info['rating_mpaa'] == 'PG13') {
                $movie_info['rating_mpaa'] = 'PG-13';
            } elseif ($movie_info['rating_mpaa'] == 'NC17') {
                $movie_info['rating_mpaa'] = 'NC-17';
            }
        }
        $node_list = $xpath->query('//*[@id="block_rating"]/div[1]/div[1]/a/span[1]');
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['rating_kinopoisk'] = self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//*[@id="block_rating"]/div[1]/div[1]/a/span[2]');
        if ($node_list !== false && $node_list->length != 0) {
            $movie_info['rating_count_kinopoisk'] = self::getNodeText($node_list->item(0));
        }
        $node_list = $xpath->query('//*[@id="block_rating"]/div[1]/div[2]');
        if ($node_list !== false && $node_list->length != 0) {
            $imdb_raw = self::getNodeText($node_list->item(0));
            if (\preg_match("/IMDb: (.*) \\((.*)\\)/", $imdb_raw, $match)) {
                $movie_info['rating_imdb'] = $match[1];
                $movie_info['rating_count_imdb'] = $match[2];
            }
        }
        return $movie_info;
    }
    public static function getInfoByName($orig_name)
    {
        if (empty($orig_name)) {
            return false;
        }
        $ch = \curl_init();
        if ($ch === false) {
            throw new \Ministra\Lib\KinopoiskException(\_('Curl initialization error'), \curl_error($ch));
        }
        $orig_name = \iconv('utf-8', 'windows-1251', $orig_name);
        $orig_name = \urlencode($orig_name);
        $search_url = 'https://www.kinopoisk.ru/index.php?level=7&from=forma&result=' . 'adv&m_act[from]=forma&m_act[what]=content&m_act[find]=' . $orig_name . '&m_act[content_find]=film,serial&first=yes';
        $curl_options = [CURLOPT_URL => $search_url, CURLOPT_HEADER => 1, CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Connection: keep-alive', 'Cache-Control: no-cache', 'Pragma: no-cache', 'User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.5 ' . '(KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5', 'Accept: text/css,*/*;q=0.1', 'Accept-Encoding: gzip,deflate,sdch', 'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4', 'Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.3']];
        if (\Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::exist('http_proxy')) {
            $curl_options[CURLOPT_PROXY] = \str_replace('tcp://', '', \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::get('http_proxy'));
            if (\Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::exist('http_proxy_login') && \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::exist('http_proxy_password')) {
                $curl_options[CURLOPT_PROXYUSERPWD] = \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::get('http_proxy_login') . ':' . \Ministra\Lib\S642b6461e59cef199375bfb377c17a39\a777f7659bfaad9ba0acb83e0c546a50::get('http_proxy_password');
            }
        }
        \curl_setopt_array($ch, $curl_options);
        $response = \curl_exec($ch);
        \curl_close($ch);
        if ($response === false) {
            throw new \Ministra\Lib\KinopoiskException(\_('Curl exec failure'), \curl_error($ch));
        }
        if (\preg_match("/Location: ([^\\s]*)/", $response, $match)) {
            $location = $match[1];
        }
        if (empty($location)) {
            throw new \Ministra\Lib\KinopoiskException(\_('Empty location header'), $response);
        }
        if (\strpos($location, 'http') === 0) {
            throw new \Ministra\Lib\KinopoiskException(\_('Wrong location header.') . ' ' . \sprintf(\_("Location: ('%s')"), $location), $response);
        }
        if (\preg_match("/\\/([\\d]*)\\/\$/", $location, $match)) {
            $movie_id = $match[1];
        } else {
            throw new \Ministra\Lib\KinopoiskException(\_('Location does not contain movie id.') . ' ' . \sprintf(\_("Location: ('%s')"), $location), $response);
        }
        return self::getInfoById($movie_id);
    }
    public static function getRatingByName($orig_name)
    {
        $info = self::getInfoByName($orig_name);
        if (!$info) {
            return false;
        }
        $fields = \array_fill_keys(['kinopoisk_url', 'kinopoisk_id', 'rating_kinopoisk', 'rating_count_kinopoisk', 'rating_imdb', 'rating_count_imdb'], true);
        return \array_intersect_key($info, $fields);
    }
    public static function getRatingById($kinopoisk_id, $type = null)
    {
        $result = ['kinopoisk_id' => $kinopoisk_id];
        $xml_url = 'https://www.kinopoisk.ru/rating/' . $kinopoisk_id . '.xml';
        $xml = @\simplexml_load_file($xml_url);
        if (!$xml) {
            throw new \Ministra\Lib\KinopoiskException(\_("Can't get rating from") . ' ' . $xml_url . '; ' . \implode(', ', \libxml_get_errors()), '');
        }
        $result['rating_kinopoisk'] = (string) $xml->kp_rating;
        $result['rating_count_kinopoisk'] = (int) $xml->kp_rating->attributes()->num_vote;
        if ($xml->imdb_rating) {
            $result['rating_imdb'] = (string) $xml->imdb_rating;
            $result['rating_count_imdb'] = (int) $xml->imdb_rating->attributes()->num_vote;
        }
        return $result;
    }
    private static function getNodeText($node)
    {
        $text = \html_entity_decode($node->nodeValue);
        $rules = ["/\\x{0085}/u" => '...', "/(\\s+)/" => ' ', "/\n/" => ''];
        $text = \trim(\preg_replace(\array_keys($rules), \array_values($rules), $text));
        return $text;
    }
}
