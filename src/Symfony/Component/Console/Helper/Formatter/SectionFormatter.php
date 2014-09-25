<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper\Formatter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SectionFormatter implements FormatterInterface
{
    protected $section;
    protected $message;
    protected $style;

    /**
     * Formats a message within a section.
     *
     * @param string $section
     * @param string $message
     * @param string $style
     */
    public function __construct($section, $message, $style = 'info')
    {
        $this->section = $section;
        $this->message = $message;
        $this->style = $style;
    }

    /**
     * {@inheritdoc}
     */
    public function format()
    {
        return sprintf('<%s>[%s]</%s> %s', $this->style, $this->section, $this->style, $this->message);
    }
}
