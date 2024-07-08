<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Symfony\Component\Notifier\Bridge\JoliNotif;

use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Message\MessageOptionsInterface;

/**
 * @author Ahmed Ghanem <ahmedghanem7361@gmail.com>
 */
final class JoliNotifOptions implements MessageOptionsInterface
{
    private array $options = [];

    public function toArray(): array
    {
        return $this->options;
    }

    public function getRecipientId(): ?string
    {
        return null;
    }

    /**
     * @return $this
     */
    public function setIconPath(string $iconPath): self
    {
        $this->options['icon_path'] = $iconPath;

        return $this;
    }

    public function getIconPath(): ?string
    {
        return $this->options['icon_path'] ?? null;
    }

    /**
     * Extra options maybe supported and effective by the JoliNotif package on some operating systems
     * while not on others.
     * For more details, you can always check the package page on GitHub (https://github.com/jolicode/JoliNotif).
     *
     * @return $this
     */
    public function setExtraOption(string $key, string|int $value): self
    {
        $this->options['extraOptions'][$key] = $value;

        return $this;
    }

    public function getExtraOption(string $key): string|int
    {
        if (!isset($this->options['extraOptions'][$key])) {
            throw new InvalidArgumentException(\sprintf('The extra option (%s) cannot be fetched as it does not exist', $key));
        }

        return $this->options['extraOptions'][$key];
    }

    public function getExtraOptions(): array
    {
        return $this->options['extraOptions'] ?? [];
    }
}
