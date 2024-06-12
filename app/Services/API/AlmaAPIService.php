<?php

namespace App\Services\API;

use App\DTO\AlmaServiceResponse;
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

    protected $baseUrl;

    /**
     * AlmaAPIService constructor.
     *
     * @param  string  $baseUrl  Base URL for the Alma API.
     * @param  string  $apiKey  API key for authentication.
     */
    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
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
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Query users with the given unique identifier via the API.
     *
     * @param  string  $identifier  Unique identifier (uniqueID, matriculation number, etc.).
     * @return AlmaUser|null User object or null if no user was found.
     */
    public function getUserByIdentifier(string $identifier): AlmaServiceResponse
    {
        if (empty($identifier)) {
            return null;
        }

        $action = '/users/'.$identifier;
        [$statusCode, $response] = $this->makeRequest($action);

        if ($statusCode != 200) {
            $errorText = '';
            if ($response instanceof DOMDocument) {
                $errorText = $response->getElementsByTagName('errorMessage')->item(0)->nodeValue;
            } elseif ($response->errorsExist) {
                $errorText = $response->errorList->error[0]->errorMessage;
            }

            return new AlmaServiceResponse(false, $statusCode, null, $errorText);
        }

        $almaUser = AlmaUser::fromApiResponse($response);

        return new AlmaServiceResponse(true, $statusCode, $almaUser, null);
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

        $url = $this->baseUrl.$action;

        $client = new Client([
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $options = [
            'query' => $queryParams,
        ];

        if (! empty($bodyData)) {
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
        if (! preg_match('/<error>/i', $response)) {
            return json_decode($response);
        } else {
            $doc = new DOMDocument('1.0');
            $doc->preserveWhiteSpace = false;
            $doc->formatOutput = true;

            if (! empty($response)) {
                $doc->loadXML($response);
            }

            return $doc;
        }
    }
}
