<?php
namespace MyRqlParserEx\SubLexer;

use Xiag\Rql\Parser\Token;
use Xiag\Rql\Parser\SubLexerInterface;

class TypeExSubLexer implements SubLexerInterface
{
    // NOTE: here we set a list of available types to make "http://"-like values valid
    const ALLOWED_TYPES = [
        'string',
        'integer',
        'float',
        'boolean',
        'glob',
        'date',
    ];

    /**
     * @inheritdoc
     */
    public function getTokenAt($code, $cursor)
    {
        foreach (self::ALLOWED_TYPES as $type) {
            if (substr_compare($code, $type . ':', $cursor, strlen($type) + 1, true) !== 0) {
                continue;
            }

            return new Token(
                Token::T_TYPE,
                $type,
                $cursor,
                $cursor + strlen($type)
            );
        }

        return null;
    }
}
