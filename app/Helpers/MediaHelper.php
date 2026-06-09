<?php

namespace App\Helpers;

class MediaHelper
{
    public static function isImageFile($fileName)
    {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return in_array(
            strtolower($ext),
            array("jpg", "jpeg", "gif", "png", "bmp")
        );
    }

    public static function getPathImg($path)
    {
        $path = strpos($path, '/') === 0 ? $path : '/' . $path;

        return config('common.aws.cloud_front_domain') . $path;
    }

    public static function isYouTubeUrl($url)
    {
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['host'])) {
            return false;
        }

        $host = str_replace('www.', '', $parsedUrl['host']);
        if (!in_array($host, ['youtube.com', 'youtu.be'])) {
            return false;
        }
        $pattern = '/(youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})/';

        return preg_match($pattern, $url);
    }

    public static function getCorrectValueByType($type, $value)
    {
        if (empty($value)) {
            return '';
        }

        $result = '';
        switch ($type) {
            case 'text':
                $result = $value;
                break;
            case 'audio':
            case 'image':
            case 'video':
                $result = Helper::getCloudFront($value);
                break;
        }

        return $result;
    }

    public static function getCorrectQuestionByType($type, $value)
    {
        if (empty($value)) {
            return '';
        }

        $result = '';
        switch ($type) {
            case 'audio':
            case 'text':
                $result = $value;
                break;
            case 'image':
            case 'video':
                $result = Helper::getCloudFront($value);
                break;
        }

        return $result;
    }
}
