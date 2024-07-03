<?php

namespace App\Services\API;

use App\DTO\AlmaServiceMultiResponse;
use App\DTO\AlmaServiceSingleResponse;
use App\Interfaces\AlmaAPIInterface;
use App\Models\AlmaUser;
use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class AlmaAPIService
 * Provides an interface for interacting with the Alma API.
 */
class AlmaAPIService implements AlmaAPIInterface
{
    protected $apiKey;

    protected $izCode;

    protected $baseUrl;

    /**
     * AlmaAPIService constructor.
     *
     * @param  string  $baseUrl  Base URL for the Alma API.
     * @param  string  $apiKey  API key for authentication.
     */
    public function __construct(string $baseUrl, string $izCode, string $apiKey)
    {
        $this->baseUrl = $baseUrl;
        $this->izCode = $izCode;
        $this->apiKey = $apiKey;
    }

    /**
     * Get the API key.
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    /**
     * Set the API key.
     *
     * @param string $izCode
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $izCode, string $apiKey): void
    {
        $this->izCode = $izCode;
        $this->apiKey = $apiKey;
    }

    /**
     * Get a user from a single IZ
     *
     * @param string $identifier
     * @param string $izCode
     * @return AlmaServiceSingleResponse
     */
    public function getUserFromSingleIz(string $identifier, string $izCode): AlmaServiceSingleResponse
    {
        $result = $this->fetchUserByIdentifierAndIzCode($identifier, $izCode);
        if ($result['success']) {
            return new AlmaServiceSingleResponse(true, $result['code'], $result['data'], null);
        } else {
            return new AlmaServiceSingleResponse(false, $result['code'], null, $result['message']);
        }
    }

    /**
     * Get a user from multiple IZs
     *
     * @param string $identifier
     * @param array $izCodes
     * @return AlmaServiceMultiResponse
    */
    public function getUserFromMultipleIzs(string $identifier, array $izCodes): AlmaServiceMultiResponse
    {
        $almaUsers = [];
        foreach ($izCodes as $izCode) {
            $result = $this->fetchUserByIdentifierAndIzCode($identifier, $izCode);
            if (!$result['success']) {
                return new AlmaServiceMultiResponse(false, null, $result['message']);
            }
            $almaUsers[] = $result['data'];
        }

        return new AlmaServiceMultiResponse(true, $almaUsers, null);
    }

    /**
     * Fetches a user by identifier for a given IZ code.
     *
     * @param string $identifier
     * @param string $izCode
     * @return array
     */
    private function fetchUserByIdentifierAndIzCode(string $identifier, string $izCode): array
    {
        $token = config("services.alma.api_keys.{$izCode}");
        if (!$token) {
            return ['success' => false, 'message' => "{$izCode}: Missing API Key in configuration."];
        }

        try {
            $this->setApiKey($izCode, $token);
            $almaUser = $this->getUserByIdentifier($identifier);

            return ['success' => true, 'data' => $almaUser];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => "{$izCode}: {$e->getMessage()}"];
        }
    }

    /**
     * Query users with the given unique identifier via the API.
     *
     * @param  string  $identifier  Unique identifier (uniqueID, matriculation number, etc.).
     * @return AlmaUser|null User object or null if no user was found.
     */
    private function getUserByIdentifier(string $identifier): AlmaUser
    {
        if (empty($identifier)) {
            return null;
        }

        $action = '/users/' . $identifier;
        [$statusCode, $response] = $this->makeRequest($action);

        if ($statusCode != 200) {
            $errorText = '';
            if ($response instanceof DOMDocument) {
                $errorText = $response->getElementsByTagName('errorMessage')->item(0)->nodeValue;
            } elseif ($response->errorsExist) {
                $errorText = $response->errorList->error[0]->errorMessage;
            }

            throw new \Exception($errorText);
        }

        $almaUser = AlmaUser::fromApiResponse($response);
        $almaUser->alma_iz = $this->izCode;

        return $almaUser;
    }

    /**
     * Makes an HTTP request to the API with the given parameters.
     *
     * @param  string  $action  API action.
     * @param  string  $actionType  HTTP request type (GET by default).
     * @param  string[]  $queryParams  Query parameters.
     * @param  string  $bodyData  Request body data.
     * @return array [HTTP status code, API response].
     */
    private function makeRequest(
        string $action,
        string $actionType = 'GET',
        array $queryParams = [],
        string $bodyData = ''
    ): array {
        // Add the API key to the query parameters
        $queryParams['apikey'] = $this->apiKey;

        $url = $this->baseUrl . $action;

        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $options = [
            'query' => $queryParams,
        ];

        if (!empty($bodyData)) {
            $options['body'] = $bodyData;
        }

        try {
            $response = $client->request($actionType, $url, $options);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            $userData = $this->decodeResponse($body);

            return [$statusCode, $userData];
        } catch (RequestException $e) {
            // If the request has an exception, get the response from it
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();

                $errorData = $this->decodeResponse($body);

                return [$statusCode, $errorData];
            }

            // If there is no response, rethrow the exception
            throw $e;
        }
    }

    /**
     * Decodes given response depending on the current format mode of the API.
     *
     * @param  string  $response  API response.
     * @return mixed
     */
    private function decodeResponse(string $response)
    {
        if (!preg_match('/<error>/i', $response)) {
            return json_decode($response);
        } else {
            $doc = new DOMDocument('1.0');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            if (!empty($response)) {
                $doc->loadXML($response);
            }

            return $doc;
        }
    }
}
