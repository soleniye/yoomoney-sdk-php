

# [UNOFFICIAL] PHP Yoomoney API SDK

## Requirements

PHP 5.3 or above


## Links

Yoomoney API page: [Ru](https://yoomoney.ru/docs/wallet),
[En](https://yoomoney.ru/docs/wallet?lang=en)

## Getting started

### Installation

1. Add `"soleniye/yoomoney-sdk-php": "1.0.*"` to `composer.json` of your application. Or clone repo to your project.
2. If you are using composer - simply use `require_once 'vendor/autoload.php';` otherwise paste following code
    ```php
    require_once '/path/to/cloned/repo/lib/api.php';
    ```

### Payments from the Yoomoney wallet

Using Yoomoney API requires following steps

1. Obtain token URL and redirect user's browser to Yoomoney service.
Note: `client_id`, `redirect_uri`, `client_secret` are constants that you get,
when [register](https://yoomoney.ru/myservices/new) app in Yoomoney API.

    ```php
    use \YooMoney\API;

    $auth_url = API::buildObtainTokenUrl($client_id, $redirect_uri, $scope);
    ```

2. After that, user fills Yoomoney HTML form and user is redirected back to
`REDIRECT_URI?code=CODE`.

3. You should immediately exchange `CODE` with `ACCESS_TOKEN`.

    ```php
    $access_token_response = API::getAccessToken($client_id, $code, $redirect_uri, $client_secret=NULL);
    if(property_exists($access_token_response, "error")) {
        // process error
    }
    $access_token = $access_token_response->access_token;
    ```

4. Now you can use Yoomoney API.

    ```php
    $api = new API($access_token);

    // get account info
    $acount_info = $api->accountInfo();

    // check status 

    // get operation history with last 3 records
    $operation_history = $api->operationHistory(array("records"=>3));

    // check status 

    // make request payment
    $request_payment = $api->requestPayment(array(
        "pattern_id" => "p2p",
        "to" => $money_wallet,
        "amount_due" => $amount_due,
        "comment" => $comment,
        "message" => $message,
        "label" => $label,
    ));

    // check status 

    // call process payment to finish payment
    $process_payment = $api->processPayment(array(
        "request_id" => $request_payment->request_id,
    ));
    ```



## Side notes

1. Library throws exceptions in case of
    * response status isn't equal 2**
    * I/O error(see [requests](https://github.com/rmccue/Requests))
2. If you register app and fill `CLIENT_SECRET` entry then you should
provide `$client_secret` explicitly where `$client_secret=NULL`
3. You should wrap all passed boolean values in quotes(because php converts
them to numbers otherwise). For example:

```php
API($access_token).requestPayment(array(
    test_payment => "true",
    // other params
));
```


## Running tests

1. Clone this repo.
2. Install composer
3. Run `composer install`
4. Make sure `phpunit` executable is present in your `$PATH`
5. Create `tests/constants.php` with `CLIENT_ID`, `CLIENT_SECRET` and `ACCESS_TOKEN`
constants. 
6. Run tests `phpunit --bootstrap vendor/autoload.php tests/`
