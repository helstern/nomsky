<?php namespace Helstern\Nomsky\Parser\AstNodeVisitor;

use Helstern\Nomsky\Parser\Ast\AstNode;
use Helstern\Nomsky\Parser\Ast\AstNodeVisitor;

class PostVisitAction implements VisitAction
{
    /** @var AstNode */
    protected $astNode;

    /** @var AstNodeVisitor */
    protected $visitor;

    /**
     * @param AstNode $visitReceiver
     * @param AstNodeVisitor $visitor
     */
    public function __construct(AstNode $visitReceiver, AstNodeVisitor $visitor)
    {
        $this->astNode = $visitReceiver;
        $this->visitor = $visitor;
    }

    public function getVisitor()
    {
        return $this->visitor;
    }

    public function getSubject()
    {
        return $this->astNode;
    }

    public function execute()
    {
        $this->visitor->postVisit($this->astNode);
        return true;
    }


}

