<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpClient\Tests;

use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockHttpClientTest extends HttpClientTestCase
{
    protected function getHttpClient(string $testCase): HttpClientInterface
    {
        $responses = [];

        $headers = [
          'Host: localhost:8057',
          'Content-Type: application/json',
        ];

        $body = '{
    "SERVER_PROTOCOL": "HTTP/1.1",
    "SERVER_NAME": "127.0.0.1",
    "REQUEST_URI": "/",
    "REQUEST_METHOD": "GET",
    "HTTP_FOO": "baR",
    "HTTP_HOST": "localhost:8057"
}';

        $client = new NativeHttpClient();

        switch ($testCase) {
            default:
                return new MockHttpClient(function (string $method, string $url, array $options) use ($client) {
                    try {
                        // force the request to be completed so that we don't test side effects of the transport
                        $response = $client->request($method, $url, $options);
                        $content = $response->getContent(false);

                        return new MockResponse($content, $response->getInfo());
                    } catch (\Throwable $e) {
                        $this->fail($e->getMessage());
                    }
                });

            case 'testUnsupportedOption':
                $this->markTestSkipped('MockHttpClient accepts any options by default');
                break;

            case 'testChunkedEncoding':
                $this->markTestSkipped("MockHttpClient doesn't dechunk");
                break;

            case 'testGzipBroken':
                $this->markTestSkipped("MockHttpClient doesn't unzip");
                break;

            case 'testDestruct':
                $this->markTestSkipped("MockHttpClient doesn't timeout on destruct");
                break;

            case 'testGetRequest':
                array_unshift($headers, 'HTTP/1.1 200 OK');
                $responses[] = new MockResponse($body, ['response_headers' => $headers]);

                $headers = [
                  'Host: localhost:8057',
                  'Content-Length: 1000',
                  'Content-Type: application/json',
                ];

                $responses[] = new MockResponse($body, ['response_headers' => $headers]);
                break;

            case 'testDnsError':
                $mock = $this->getMockBuilder(ResponseInterface::class)->getMock();
                $mock->expects($this->any())
                    ->method('getStatusCode')
                    ->willThrowException(new TransportException('DNS error'));
                $mock->expects($this->any())
                    ->method('getInfo')
                    ->willReturn([]);

                $responses[] = $mock;
                $responses[] = $mock;
                break;

            case 'testToStream':
            case 'testBadRequestBody':
            case 'testOnProgressCancel':
            case 'testOnProgressError':
                $responses[] = new MockResponse($body, ['response_headers' => $headers]);
                break;

            case 'testTimeoutOnAccess':
                $mock = $this->getMockBuilder(ResponseInterface::class)->getMock();
                $mock->expects($this->any())
                    ->method('getHeaders')
                    ->willThrowException(new TransportException('Timeout'));

                $responses[] = $mock;
                break;

            case 'testResolve':
                $responses[] = new MockResponse($body, ['response_headers' => $headers]);
                $responses[] = new MockResponse($body, ['response_headers' => $headers]);
                $responses[] = new MockResponse((function () { throw new \Exception('Fake connection timeout'); yield ''; })(), ['response_headers' => $headers]);
                break;

            case 'testTimeoutOnStream':
            case 'testUncheckedTimeoutThrows':
                $body = ['<1>', '', '<2>'];
                $responses[] = new MockResponse($body, ['response_headers' => $headers]);
                break;
        }

        return new MockHttpClient($responses);
    }
}
