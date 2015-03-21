<?php namespace Helstern\Nomsky\Grammars\Ebnf\Graphviz\DotWriterVisitors;

use Helstern\Nomsky\Grammars\Ebnf\Ast\RepeatedExpressionNode;
use Helstern\Nomsky\Grammars\Ebnf\Graphviz\VisitorCollaborators;
use Helstern\Nomsky\Parser\Ast\AstNodeVisitor;
use Helstern\Nomsky\Parser\AstNodeVisitor\AbstractDispatchingVisitor;
use Helstern\Nomsky\Parser\AstNodeVisitor\VisitDispatcher;

class RepeatedExpressionNodeVisitor extends AbstractDispatchingVisitor implements AstNodeVisitor
{
    /** @var VisitorCollaborators */
    protected $collaborators;

    /** @var VisitDispatcher  */
    protected $visitDispatcher;

    /**
     * @param VisitorCollaborators $collaborators
     * @param VisitDispatcher $visitDispatcher
     */
    public function __construct(VisitorCollaborators $collaborators, VisitDispatcher $visitDispatcher)
    {
        $this->collaborators = $collaborators;
        $this->visitDispatcher = $visitDispatcher;
    }

    /**
     * @param RepeatedExpressionNode $astNode
     * @return string
     */
    protected function buildDOTIdentifier(RepeatedExpressionNode $astNode)
    {
        $nodeCounter = $this->collaborators->nodeCounter();
        $idNumber = $nodeCounter->getNodeCount();

        return '"' . 'repeated_expression' . '[' .$idNumber . ']' . '"';
    }

    /**
     * @param RepeatedExpressionNode $astNode
     * @return bool
     */
    public function preVisitRepeatedExpressionNode(RepeatedExpressionNode $astNode)
    {
        $nodeCounter = $this->collaborators->nodeCounter();
        $nodeCounter->increment($astNode);

        return true;
    }

    /**
     * @param RepeatedExpressionNode $astNode
     * @return bool
     */
    public function visitRepeatedExpressionNode(RepeatedExpressionNode $astNode)
    {
        $dotWriter = $this->collaborators->dotWriter();
        $formatter = $this->collaborators->formatter();

        $parents = $this->collaborators->parentNodeIds();
        $increment = $parents->count();
        $formatter->indent($increment, $dotWriter);

        $parentId = $parents->top();
        $nodeId    = $this->buildDOTIdentifier($astNode);

        $dotWriter->writeEdgeStatement($parentId, $nodeId);
        $formatter->whitespace(1, $dotWriter); //formatting options
        $dotWriter->writeStatementTerminator();

        $parents->push($nodeId);

        return true;
    }

    /**
     * @param RepeatedExpressionNode $astNode
     * @return bool
     */
    public function postVisitRepeatedExpressionNode(RepeatedExpressionNode $astNode)
    {
        $parents = $this->collaborators->parentNodeIds();
        $parents->pop();

        return true;
    }

    /**
     * @return VisitDispatcher
     */
    protected function getVisitDispatcher()
    {
        $visitDispatcher = $this->visitDispatcher;
        return $visitDispatcher;
    }
}
