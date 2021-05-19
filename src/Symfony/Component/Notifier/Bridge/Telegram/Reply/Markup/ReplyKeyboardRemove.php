<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Telegram\Reply\Markup;

/**

 *
 * @see https://core.telegram.org/bots/api#replykeyboardremove
 */
final class ReplyKeyboardRemove extends AbstractTelegramReplyMarkup
{
    public function __construct(bool $removeKeyboard = false, bool $selective = false)
    {
        $this->options['remove_keyboard'] = $removeKeyboard;
        $this->options['selective'] = $selective;
    }
}
