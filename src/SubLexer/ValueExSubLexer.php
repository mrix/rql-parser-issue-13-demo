<?php
namespace MyRqlParserEx\SubLexer;

use Xiag\Rql\Parser\Exception\SyntaxErrorException;
use Xiag\Rql\Parser\Glob;
use Xiag\Rql\Parser\Token;
use Xiag\Rql\Parser\SubLexerInterface;

class ValueExSubLexer implements SubLexerInterface
{
    // NOTE: all characters before this boundary characters are valid
    const RE_BOUNDARY = ',()|&=!<>';

    /**
     * @inheritdoc
     */
    public function getTokenAt($code, $cursor)
    {
        if (!preg_match('/([^' . preg_quote(self::RE_BOUNDARY) . ']+)/Ai', $code, $matches, null, $cursor)) {
            return null;
        } elseif ($token = $this->parseNumber($matches[0], $cursor)) {
            return $token;
        } elseif ($token = $this->parseDate($matches[0], $cursor)) {
            return $token;
        } elseif ($token = $this->parseSort($matches[0], $cursor)) {
            return $token;
        } elseif ($token = $this->parseGlob($matches[0], $cursor)) {
            return $token;
        } elseif ($token = $this->parseString($matches[0], $cursor)) {
            return $token;
        } else {
            return null;
        }
    }

    private function parseNumber($match, $cursor)
    {
        if (!is_numeric($match)) {
            return null;
        }

        return new Token(
            filter_var($match, FILTER_VALIDATE_INT) === false ? Token::T_FLOAT : Token::T_INTEGER,
            $match,
            $cursor,
            $cursor + strlen($match)
        );
    }

    private function parseDate($match, $cursor)
    {
        $regExp = '/^(?<y>\d{4})-(?<m>\d{2})-(?<d>\d{2})T(?<h>\d{2}):(?<i>\d{2}):(?<s>\d{2})Z$/';
        if (!preg_match($regExp, $match, $dateChunks)) {
            return null;
        }

        if (
            !checkdate($dateChunks['m'], $dateChunks['d'], $dateChunks['y']) ||
            !($dateChunks['h'] < 24 && $dateChunks['i'] < 60 && $dateChunks['s'] < 60)
        ) {
            throw new SyntaxErrorException(sprintf('Invalid datetime value "%s"', $dateChunks[0]));
        }

        return new Token(
            Token::T_DATE,
            $match,
            $cursor,
            $cursor + strlen($match)
        );
    }

    private function parseSort($match, $cursor)
    {
        if ($match[0] !== '-' && $match[0] !== '+') {
            return null;
        }

        return new Token(
            $match[0] === '-' ? Token::T_MINUS : Token::T_PLUS,
            $match[0],
            $cursor,
            $cursor + 1
        );
    }

    private function parseGlob($match, $cursor)
    {
        if (
            strpos($match, '?') === false &&
            strpos($match, '*') === false
        ) {
            return null;
        }

        return new Token(
            Token::T_GLOB,
            $this->decodeGlob($match),
            $cursor,
            $cursor + strlen($match)
        );
    }

    private function parseString($match, $cursor)
    {
        return new Token(
            Token::T_STRING,
            rawurldecode($match),
            $cursor,
            $cursor + strlen($match)
        );
    }

    private function decodeGlob($glob)
    {
        return preg_replace_callback(
            '/[^\*\?]+/i',
            function ($encoded) {
                return Glob::encode(rawurldecode($encoded[0]));
            },
            $glob
        );
    }
}
