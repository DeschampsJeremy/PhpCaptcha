# PhpCaptcha
By [Jeremy Deschamps](https://www.jddev.net)

A simple question captcha generator

## Contrib

- Download the repository, in a CLI :
```
git clone https://github.com/DeschampsJeremy/PhpCaptcha.git
```
Push on the `staging` branch.

## Install

- Download the repository, in a CLI :
```
composer require deschamps-jeremy/php-captcha
```

- Import :
```
use DeschampsJeremy\CaptchaService;
```

## Methods

### static get(): array
Get a captcha on list and return and array, format ['token' => int, 'base64' => string]. This code will create an uniq token response
```
var_dump([
    0 => CaptchaService::get(),
    1 => CaptchaService::get(),
]);

array(2) {
    [0]=> array(2) {
        ["token"]=> string(28) "20201011111830_5f7ec344d119e"
        ["base64"]=> //A JPG image on base64 string
    },
    [1]=> array(2) {
        ["token"]=> string(28) "20201011111830_5f7ec344d2580"
        ["base64"]=> //A JPG image on base64 string
    }
}
```

### static isValid(string $token, string $response): bool
Check captcha is valid
```
var_dump([
    0 => CaptchaService::isValid("20201011111830_5f7ec344d119e", "good response"),
    1 => CaptchaService::isValid("20201011111830_5f7ec344d119e", "good response"),
    2 => CaptchaService::isValid("20201011111830_5f7ec344d2580", "bad response"),
]);

array(2) {
    [0]=> bool(true)
    [1]=> bool(false)
    [2]=> bool(false)
}
```