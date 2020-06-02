<?php

namespace helper;

class WebHelper extends \Prefab
{
    function headers(array $raw): array
    {
        $headers = [];
        list($protocol, $code, $text) = explode(' ', array_shift($raw));
        $headers['protocol'] = $protocol;
        $headers['code'] = $code;
        $headers['text'] = $text;
        foreach ($raw as $line) {
            list($name, $value) = explode(': ', $line);
            $headers[strtolower($name)] = $value;
        }
        return $headers;
    }

    function fetchImageThumbnail($url): string
    {
        $info = pathinfo(parse_url($url, PHP_URL_PATH));
        $response = \Web::instance()->request($url);
        $headers = $this->headers($response['headers']);
        if (substr($headers['code'], 0, 2) == 20) {
            $extension = explode('/', $headers['content-type'])[1];
            $file = '/tmp/' . $info['filename'] . '_100.' . $extension;
            file_put_contents($file, $response['body']);
            return $file;
        }
        return ROOT . '/html/img/holder_100.jpg';
    }
}