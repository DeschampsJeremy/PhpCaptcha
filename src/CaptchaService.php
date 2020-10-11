<?php

namespace DeschampsJeremy;

use DateTime;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;

class CaptchaService
{
    const TOKEN_ROOT = __DIR__ . "/token";
    const FILTER_ROOT = __DIR__ . "/editor";

    /////////////////////////////////////////////////////////////////
    //PUBLIC
    /////////////////////////////////////////////////////////////////

    /**
     * Get a captcha on list and return and array, format ['token' => int, 'base64' => string]
     * @return array
     */
    public static function get(): array
    {
        $captcha = self::getCaptcha();
        return [
            "token" => self::getToken($captcha['response']),
            "base64" => $captcha['base64'],
        ];
    }

    /**
     * Check captcha is valid
     * @return bool
     */
    public static function isValid(string $token, string $response): bool
    {
        foreach (self::lists(self::TOKEN_ROOT) as $value) {
            $explodes = explode("_", $value);

            //Delete old tokens
            if ($explodes[0] < ((new DateTime())->modify('-1 day'))->format('YmdHis')) {
                unlink(self::TOKEN_ROOT . '/' . $value);
            } else {

                //Check token validity
                if ($token . ".json" == $value) {
                    $jsonIterator = new RecursiveIteratorIterator(
                        new RecursiveArrayIterator(json_decode(file_get_contents(self::TOKEN_ROOT . '/' . $value, true))),
                        RecursiveIteratorIterator::SELF_FIRST
                    );
                    foreach ($jsonIterator as $value2) {
                        if ($value2 == strtolower($response)) {
                            unlink(self::TOKEN_ROOT . '/' . $value);
                            return true;
                        } else {
                            return false;
                        }
                    }
                }
            }
        }
        return false;
    }

    /////////////////////////////////////////////////////////////////
    //PRIVATE
    /////////////////////////////////////////////////////////////////

    /**
     * Generate captcha image and return a base64 captcha
     * @return string
     */
    private static function getCaptcha(): array
    {
        //Create an image
        $im = imagecreatetruecolor(100, 20);

        //Define colors
        $background_color = imagecolorallocate($im, 220, 220, 220);
        $text_color = imagecolorallocate($im, 25, 25, 25);

        //Define response
        $response = self::getTextCaptcha();

        //Add image content
        imagefill($im, 0, 0, $background_color);
        imagestring($im, 5, 10, 2, $response, $text_color);

        //Get the filter
        $filter = imagecreatefrompng(self::FILTER_ROOT . "/" . 'filter.png');

        //Merge images
        imagecopymerge($im, $filter, 0, 0, 0, 0, 100, 20, 40);

        //Get base64
        ob_start();
        imagejpeg($im);
        $image_data = ob_get_contents();
        ob_end_clean();

        //Free resources
        imagedestroy($im);

        //Return
        return [
            'response' => strtolower(str_replace(" ", "", $response)),
            'base64' => 'data:image/jpeg;base64,' . base64_encode($image_data),
        ];
    }

    /**
     * Get captcha text
     */
    private static function getTextCaptcha(int $size = 5): string
    {
        $captcha = random_int(0, 9);
        $captcha .= self::charLower();
        $captcha .= self::charUpper();
        for ($i = strlen($captcha); $i <= $size; $i++) {
            switch (random_int(0, 2)) {
                case 0:
                    $captcha .= random_int(0, 9);
                    break;
                case 1:
                    $captcha .= self::charLower();
                    break;
                default:
                    $captcha .= self::charUpper();
            }
        }
        $rand = str_shuffle($captcha);
        $final = "";
        for ($i = 0; $i < strlen($rand); $i++) {
            $final .= $rand[$i] . ' ';
        }
        return $final;
    }

    /**
     * Generate a random lower character
     * @return string
     */
    private static function charLower(): string
    {
        return (string) chr(random_int(97, 122));
    }

    /**
     * Generate a random upper character
     * @return string
     */
    private static function charUpper(): string
    {
        return (string) chr(random_int(65, 90));
    }

    /**
     * Get captchas images list
     * @return array
     */
    private static function lists(string $root): array
    {
        $returns = [];
        foreach (scandir($root) as $file) {
            if ($file !== "." && $file !== "..") {
                $returns[] = $file;
            }
        }
        return $returns;
    }

    /**
     * Get a uniq id token
     * @return string
     */
    private static function getToken(string $response): string
    {
        $token = (new DateTime())->format('YmdHis') . '_' . uniqid();
        $fp = fopen(self::TOKEN_ROOT . '/' . $token . '.json', 'w');
        fwrite($fp, json_encode(['response' => $response]));
        fclose($fp);
        return $token;
    }
}
