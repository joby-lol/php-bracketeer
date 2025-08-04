<?php

namespace Joby\bbMark\Tags;

use League\CommonMark\Parser\Inline\InlineParserInterface;
use League\CommonMark\Parser\Inline\InlineParserMatch;
use League\CommonMark\Parser\InlineParserContext;

class InlineTagParser implements InlineParserInterface
{
    const string SELF_CLOSING_REGEX = '\[([a-z][a-z0-9]*)( *)+ *\/\]';
    const string NORMAL_REGEX = '\[([a-z][a-z0-9]*)( *[a-z+](=([\'"]).+?\4)?)+ *\](.+?)\[\/\1\]';

    public function getMatchDefinition(): InlineParserMatch
    {
        return InlineParserMatch::join(
            InlineParserMatch::regex(static::SELF_CLOSING_REGEX),
            InlineParserMatch::regex(static::NORMAL_REGEX)
        );
    }

    public function parse(InlineParserContext $inlineContext): bool
    {
        return false;
    }
}
