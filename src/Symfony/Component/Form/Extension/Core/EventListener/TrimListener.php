<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Trims string data
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class TrimListener implements EventSubscriberInterface
{
    public function preBind(FormEvent $event)
    {
        $data = $event->getData();

        if (is_string($data)) {
            $event->setData(trim($data));
        }
    }

    static public function getSubscribedEvents()
    {
        return array(FormEvents::PRE_BIND => 'preBind');
    }
}
