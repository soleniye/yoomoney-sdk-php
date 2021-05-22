<?php 
namespace YooMoney;

require_once __DIR__ . "/base.php";

/**
 * The Yoomoney wallet API
 */
class API extends BaseAPI {

    function __construct($access_token) {
        $this->access_token = $access_token;
    }
    function sendAuthenticatedRequest($url, $options=array()) {
        $this->checkToken();
        return self::sendRequest($url, $options, $this->access_token);
    }
    function checkToken() {
        if($this->access_token == NULL) {
            throw new \Exception("obtain access_token first");
        }
    }

    /**
     * Returns information about a user's wallet
     *
     * @see https://yoomoney.ru/docs/wallet/user-account/account-info
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function accountInfo() {
        return $this->sendAuthenticatedRequest("/api/account-info");
    }

    function getAuxToken($scope) {
        return $this->sendAuthenticatedRequest("/api/token-aux", array(
            "scope" => implode(" ", $scope)
        ));
    }

    /**
     * Returns operation history of a user's wallet.
     *
     * @see https://yoomoney.ru/docs/wallet/user-account/operation-history
     * @param array[] $options Key-value parameters collection
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function operationHistory($options=NULL) {
        return $this->sendAuthenticatedRequest("/api/operation-history", $options);
    }

    /**
     * Returns details of operation specified by operation_id.
     *
     * @see https://yoomoney.ru/docs/wallet/user-account/operation-details
     * @param  string $operation_id
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function operationDetails($operation_id) {
        return $this->sendAuthenticatedRequest("/api/operation-details",
            array("operation_id" => $operation_id)
        );
    }

    /**
     * Requests a payment.
     *
     * @see https://yoomoney.ru/docs/wallet/process-payments/request-payment
     * @param array[] $options Key-value parameters collection
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function requestPayment($options) {
        return $this->sendAuthenticatedRequest("/api/request-payment", $options);
    }

    /**
     * Confirms a payment that was created using the request-payment method.
     *
     * @see https://yoomoney.ru/docs/wallet/process-payments/process-payment
     * @param array[] $options Key-value parameters collection
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function processPayment($options) {
        return $this->sendAuthenticatedRequest("/api/process-payment", $options);
    }

    /**
     * Accepts incoming transfer with a protection code or deferred transfer.
     *
     * @see https://yoomoney.ru/docs/wallet/process-payments/incoming-transfer-accept
     * @param string $operation_id
     * @param  string $protection_code Used in case of protected transfer.
     * Omitted for deffered transfers
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function incomingTransferAccept($operation_id, $protection_code=NULL) {
        return $this->sendAuthenticatedRequest("/api/incoming-transfer-accept",
            array(
                "operation_id" => $operation_id,
                "protection_code" => $protection_code
            ));
    }

    /**
     * Rejects incoming transfer with a protection code or deferred trasfer.
     *
     * @see https://yoomoney.ru/docs/wallet/process-payments/incoming-transfer-reject
     * @param string $operation_id
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws  Exceptions/ScopeError If a token does not have permissions for the
     * requested operation
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    function incomingTransferReject($operation_id) {
        return $this->sendAuthenticatedRequest("/api/incoming-transfer-reject",
            array(
                "operation_id" => $operation_id,
            ));
    }

    /**
     * Builds authorization url for user's browser
     *
     * @see https://yoomoney.ru/docs/wallet/using-api/authorization/request-access-token
     * @param string $client_id The client_id that was assigned to the application.
     * @param string $redirect_uri URI that the OAuth server sends the
     * authorization result to. Must have a string value that exactly matches
     * the redirect_uri parameter specified in the application registration
     * data. Any additional parameters required for the application can beadded
     * at the end of the string. 
     * @param string $scope A string of requested permissions(joined list of
     * strings)
     * @return response object
     */
    public static function buildObtainTokenUrl($client_id, $redirect_uri,
        $scope) {
        $params = sprintf(
            "client_id=%s&response_type=%s&redirect_uri=%s&scope=%s",
            $client_id, "code", $redirect_uri, implode(" ", $scope)
            );
        return sprintf("%s/oauth/authorize?%s", Config::$MONEY_URL, $params);
    }

    /**
     * Exchanges temporary authorization code for an access_token.
     *
     * @see https://yoomoney.ru/docs/wallet/using-api/authorization/obtain-access-token
     * @param string $client_id The client_id that was assigned to the application.
     * @param string $code Temporary token.
     * @param string $redirect_uri URI that the OAuth server sends the
     * authorization result to. The value must exactly match the `redirect_uri` value
     * from the previous "authorize" call. 
     * @param string $client_secret A secret word for verifying the application's
     * authenticity. Specified if the service is registered with the option to
     * verify authenticity.
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    public static function getAccessToken($client_id, $code, $redirect_uri,
            $client_secret=NULL) {
        $full_url = Config::$MONEY_URL . "/oauth/token";
        return self::sendRequest($full_url, array(
            "code" => $code,
            "client_id" => $client_id,
            "grant_type" => "authorization_code",
            "redirect_uri" => $redirect_uri,
            "client_secret" => $client_secret
        ));
    }

    /**
     * Revokes a token.
     *
     * @see https://yoomoney.ru/docs/wallet/using-api/authorization/revoke-access-token
     * @param string $token A token to be revoked
     * @param string $revoke_all
     * @throws Exceptions/FormatError If authorization header is missing or
     * has an invalid value
     * @throws Exceptions/TokenError If a token is nonexistent, expired or revoked
     * @throws Exceptions/ServerError If status code >= 500
     * @return response object
     */
    public static function revokeToken($token, $revoke_all=false) {
        return self::sendRequest("/api/revoke", array(
            "revoke-all" => $revoke_all,
        ), $token);
    }
}
