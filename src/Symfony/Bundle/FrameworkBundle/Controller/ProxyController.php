<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Controller;

use Symfony\Component\HttpClient\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Proxies a request to a remote URL.
 *
 * @author Jérôme Tamarelle <jerome@tamarelle.net>
 *
 * @final
 */
class ProxyController
{
    /** @var HttpClientInterface */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Creates a response by fetching the content from a remote URL.
     *
     * @param Request $request         The request instance
     * @param string  $url             The URL to fetch
     * @param string  $method          The HTTP method to use
     * @param array   $options         Options passed to the HttpClient
     * @param array   $responseHeaders HTTP headers added to the response
     *
     * @throws \InvalidArgumentException In case of invalid HTTP client options
     */
    public function __invoke(Request $request, string $url, string $method = 'GET', $options = [], $extraResponseHeaders = []): Response
    {
        $options = array_replace([
            'buffer' => false,
        ], $options);

        try {
            $remoteResponse = $this->httpClient->request($method, $url, $options);
        } catch (InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Invalid proxy configuration for route "%s": %s', $request->attributes->get('_route'), $e->getMessage()), 0, $e);
        }

        $response = new StreamedResponse(function () use ($remoteResponse) {
            foreach ($this->httpClient->stream([$remoteResponse]) as $chunk) {
                echo $chunk->getContent();
            }
        });
        $response->setStatusCode($remoteResponse->getStatusCode());

        $responseHeaders = $remoteResponse->getHeaders();
        unset($responseHeaders['content-encoding']);
        unset($responseHeaders['content-transfer-encoding']);
        $response->headers->add($responseHeaders);

        $response->headers->add($extraResponseHeaders);

        return $response;
    }
}
