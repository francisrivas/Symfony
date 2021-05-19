<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\SmsBiuras;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**

 */
final class SmsBiurasTransportFactory extends AbstractTransportFactory
{
    /**
     * @return SmsBiurasTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('smsbiuras' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'smsbiuras', $this->getSupportedSchemes());
        }

        $uid = $this->getUser($dsn);
        $apiKey = $this->getPassword($dsn);
        $from = $dsn->getRequiredOption('from');
        $testMode = filter_var($dsn->getOption('test_mode', false), \FILTER_VALIDATE_BOOLEAN);
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new SmsBiurasTransport($uid, $apiKey, $from, $testMode, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['smsbiuras'];
    }
}
