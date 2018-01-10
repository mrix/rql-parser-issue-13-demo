<?php
namespace MyRqlParserEx;

use Xiag\Rql\Parser\SubLexerChain;
use Xiag\Rql\Parser\Lexer as BaseLexer;
use Xiag\Rql\Parser\SubLexer as BaseSubLexer;

class Lexer extends BaseLexer
{
    /**
     * @inheritdoc
     */
    public static function createDefaultSubLexer()
    {
        return (new SubLexerChain())
            ->addSubLexer(new BaseSubLexer\ConstantSubLexer())
            ->addSubLexer(new BaseSubLexer\PunctuationSubLexer())
            ->addSubLexer(new BaseSubLexer\FiqlOperatorSubLexer())
            ->addSubLexer(new BaseSubLexer\RqlOperatorSubLexer())

            // NOTE: override these sub-lexers
            // ->addSubLexer(new BaseSubLexer\SortSubLexer())
            // ->addSubLexer(new BaseSubLexer\TypeSubLexer())
            // ->addSubLexer(new BaseSubLexer\GlobSubLexer())
            // ->addSubLexer(new BaseSubLexer\StringSubLexer())
            // ->addSubLexer(new BaseSubLexer\DatetimeSubLexer())
            // ->addSubLexer(new BaseSubLexer\NumberSubLexer())

            ->addSubLexer(new SubLexer\TypeExSubLexer())
            ->addSubLexer(new SubLexer\ValueExSubLexer());
    }
}
