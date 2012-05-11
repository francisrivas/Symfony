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

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Event\DataEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class ValidationListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    static public function getSubscribedEvents()
    {
        return array(FormEvents::POST_BIND => 'validateForm');
    }

    public function validateForm(DataEvent $event)
    {
        $form = $event->getForm();

        if (!$form->isSynchronized()) {
            $form->addError(new FormError(
                $form->getAttribute('invalid_message'),
                $form->getAttribute('invalid_message_parameters')
            ));
        }

        if (count($form->getExtraData()) > 0) {
            $form->addError(new FormError('This form should not contain extra fields'));
        }

        if ($form->isRoot() && isset($_SERVER['CONTENT_LENGTH'])) {
            $length = (int) $_SERVER['CONTENT_LENGTH'];
            $max = $this->getPostMaxSize();

            if ('' !== $max) {
                switch (strtolower(substr($max, -1))) {
                    // The 'G' modifier is available since PHP 5.1.0
                    case 'g':
                        $max *= 1024;
                    case 'm':
                        $max *= 1024;
                    case 'k':
                        $max *= 1024;
                }

                if ($length > $max) {
                    $form->addError(new FormError('The uploaded file was too large. Please try to upload a smaller file'));
                }
            }
        }
    }

    /**
     * @return string
     */
    protected function getPostMaxSize()
    {
        return trim(ini_get('post_max_size'));
    }
}
