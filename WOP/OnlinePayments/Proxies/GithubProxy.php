<?php

namespace common\modules\orderPayment\WOP\OnlinePayments\Proxies;

use Exception;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Exceptions\BaseException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Http\Exceptions\HttpRequestException;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Http\HttpClient;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Http\HttpResponse;
use common\modules\orderPayment\WOP\OnlinePayments\Core\Infrastructure\Logger\Logger;
use common\modules\orderPayment\WOP\OnlinePayments\Helpers\ModuleHelper;
use common\modules\orderPayment\WOP\OnlinePayments\Proxies\Requests\HttpRequest;
class GithubProxy
{
    /**
     * Http client instance.
     *
     * @var HttpClient
     */
    protected $httpClient;
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @param HttpClient $httpClient
     * @param string $baseUrl
     */
    public function __construct(HttpClient $httpClient, string $baseUrl)
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = 'https://' . trim(str_replace(['http:', 'https:'], '', $baseUrl), '/');
    }
    public function getLatestVersion(): string
    {
        try {
            $response = $this->get(new HttpRequest(ModuleHelper::getModuleConfig()->getGitHubEndpoint()));
            $body = $response->decodeBodyToArray();
            return ltrim($body['tag_name'], 'v') ?? '';
        } catch (HttpRequestException $e) {
            Logger::logError('Failed to get latest version from Github: ' . $e->getMessage());
            return '';
        }
    }
    /**
     * @param HttpRequest $request
     *
     * @return HttpResponse
     *
     * @throws HttpRequestException
     */
    protected function get(HttpRequest $request): HttpResponse
    {
        return $this->call(HttpClient::HTTP_METHOD_GET, $request);
    }
    /**
     * Performs HTTP call.
     *
     * @param string $method Specifies which http method is utilized in call.
     * @param HttpRequest $request
     *
     * @return HttpResponse Response instance.
     *
     * @throws HttpRequestException
     * @throws Exception
     */
    protected function call(string $method, HttpRequest $request): HttpResponse
    {
        $request->setHeaders(array_merge($request->getHeaders(), $this->getHeaders()));
        $url = $this->getRequestUrl($request);
        Logger::logDebug('Sending http ' . $method . ' request, endpoint ' . $request->getEndpoint());
        $response = $this->httpClient->request($method, $url, $request->getHeaders(), $this->getEncodedBody($request));
        Logger::logDebug('Received http ' . $method . ' response with status ' . $response->getStatus() . ' psp reference ' . ($response->decodeBodyToArray()['pspReference'] ?? ''));
        $this->validateResponse($response);
        return $response;
    }
    /**
     * Retrieves default request headers.
     *
     * @return array Complete list of default request headers.
     */
    protected function getHeaders(): array
    {
        return ['Content-Type' => 'Content-Type: application/json', 'Accept' => 'Accept: application/json'];
    }
    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getEncodedBody(HttpRequest $request): string
    {
        return (string) json_encode($request->getBody());
    }
    protected function getRequestUrl(HttpRequest $request): string
    {
        $sanitizedEndpoint = ltrim($request->getEndpoint(), '/');
        return "{$this->baseUrl}/{$sanitizedEndpoint}";
    }
    /**
     * Validates HTTP response.
     *
     * @param HttpResponse $response Response object to be validated.
     *
     * @throws HttpRequestException|BaseException
     */
    protected function validateResponse(HttpResponse $response): void
    {
        if ($response->isSuccessful()) {
            return;
        }
        throw new BaseException('Response validation failed: ' . $response->getStatus());
    }
}
