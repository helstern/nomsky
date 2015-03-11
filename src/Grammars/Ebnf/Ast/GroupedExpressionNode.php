<?php namespace Helstern\Nomsky\Grammars\Ebnf\Ast;

use Helstern\Nomsky\Parser\Ast\AbstractCompositeAstNode;
use Helstern\Nomsky\Parser\Ast\AstNode;
use Helstern\Nomsky\Text\TextPosition;

class GroupedExpressionNode extends AbstractCompositeAstNode implements AstNode
{
    /** @var TextPosition */
    protected $textPosition;

    /** @var AstNode */
    protected $childNode;

    public function __construct(TextPosition $textPosition, AstNode $childNode)
    {
        $this->textPosition = $textPosition;
        $this->childNode = $childNode;
    }

    public function getTextPosition()
    {
        return $this->textPosition;
    }

    public function getChildren()
    {
        return array($this->childNode);
    }

    /**
     * @return int
     */
    public function countChildren()
    {
        return 1;
    }
}
