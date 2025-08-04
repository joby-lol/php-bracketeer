<?php

namespace Joby\bbMark;

use League\CommonMark\Util\HtmlElement;

class ErrorBuilder
{
    public static function inline(string $message): HtmlElement
    {
        return new HtmlElement(
            'mark',
            [
                'class' => 'bbmark-error',
                'role' => 'alert',
                'title' => $message
            ],
            '⚠️'
        );
    }

    public static function block(string $message): HtmlElement
    {
        $mark = new HtmlElement(
            'mark',
            [
                'class' => 'bbmark-error'
            ],
            sprintf('⚠️ %s', $message)
        );

        return new HtmlElement(
            'section',
            [
                'class' => 'bbmark-error',
                'role' => 'alert'
            ],
            $mark
        );
    }
}
