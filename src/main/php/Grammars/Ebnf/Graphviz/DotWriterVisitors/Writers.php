<?php namespace Helstern\Nomsky\Grammars\Ebnf\Graphviz\DotWriterVisitors;

use Helstern\Nomsky\Grammars\Ebnf\Ast\AlternativeNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\CommentNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\GroupedExpressionNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\IdentifierNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\OptionalExpressionNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\RepeatedExpressionNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\RuleNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\SequenceNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\SpecialSequenceNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\StringLiteralNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\SyntaxNode;
use Helstern\Nomsky\Grammars\Ebnf\Graphviz\Formatter;
use Helstern\Nomsky\Grammars\Ebnf\Graphviz\VisitContext;
use Helstern\Nomsky\Graphviz\DotFile;
use Helstern\Nomsky\Graphviz\DotWriter;
use Helstern\Nomsky\Parser\Ast\AstNode;
use Helstern\Nomsky\Parser\AstNodeVisitor\DispatchingVisitor;
use Helstern\Nomsky\Parser\AstNodeVisitor\DispatchingVisitorBuilder;

class Writers
{
    /** @var DotFile */
    private $dotFile;
    /**
     * @var VisitContext
     */
    private $visitContext;

    /**
     * @var DotWriter
     */
    private $dotWriter;

    /**
     * @var Formatter
     */
    private $formatter;


    /**
     * @param DotFile $dotFile
     * @param VisitContext $visitContext
     */
    public function __construct(DotFile $dotFile, VisitContext $visitContext)
    {
        $this->dotFile = $dotFile;
        $this->visitContext = $visitContext;
        $this->dotWriter = new DotWriter($dotFile);
        $this->formatter = new Formatter();
    }

    /**
     * @return DotWriter
     */
    public function createDotWriter()
    {
        $dotWriter = new DotWriter($this->dotFile);
        return $dotWriter;
    }

    /**
     * @param $visitor
     * @param AstNode $node
     *
     * @return DispatchingVisitor
     */
    private function createDispatchingVisitor($visitor, AstNode $node)
    {
        $builder = new DispatchingVisitorBuilder();
        $node->configureDoubleDispatcher($builder);
        $builder->setVisitor($visitor);
        $dispatcher = $builder->build();

        return $dispatcher;
    }

    /**
     * @param AlternativeNode $node
     * @return AlternativeNodeVisitor
     */
    public function getAlternativeNodeVisitor(AlternativeNode $node)
    {
        $visitor = new AlternativeNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);

        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param CommentNode $node
     * @return CommentNodeVisitor
     */
    public function getCommentNodeVisitor(CommentNode $node)
    {
        $visitor = new CommentNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param GroupedExpressionNode $node
     * @return GroupedExpressionNodeVisitor
     */
    public function getGroupedExpressionNodeVisitor(GroupedExpressionNode $node)
    {
        $visitor = new GroupedExpressionNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param IdentifierNode $node
     * @return IdentifierNodeVisitor
     */
    public function getIdentifierNodeVisitor(IdentifierNode $node)
    {
        $visitor = new IdentifierNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param OptionalExpressionNode $node
     * @return OptionalExpressionNodeVisitor
     */
    public function getOptionalExpressionNodeVisitor(OptionalExpressionNode $node)
    {
        $visitor = new OptionalExpressionNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param RepeatedExpressionNode $node
     * @return RepeatedExpressionNodeVisitor
     */
    public function getRepeatedExpressionNodeVisitor(RepeatedExpressionNode $node)
    {
        $visitor = new RepeatedExpressionNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param RuleNode $node
     * @return RuleNodeVisitor
     */
    public function getRuleNodeVisitor(RuleNode $node)
    {
        $visitor = new RuleNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param SequenceNode $node
     * @return SequenceNodeVisitor
     */
    public function getSequenceNodeVisitor(SequenceNode $node)
    {
        $visitor = new SequenceNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param SpecialSequenceNode $node
     * @return SpecialSequenceNodeVisitor
     */
    public function getSpecialSequenceNodeVisitor(SpecialSequenceNode $node)
    {
        $visitor = new SpecialSequenceNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param StringLiteralNode $node
     * @return StringLiteralNodeVisitor
     */
    public function getStringLiteralNodeVisitor(StringLiteralNode $node)
    {
        $visitor = new StringLiteralNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }

    /**
     * @param SyntaxNode $node
     * @return SyntaxNodeVisitor
     */
    public function getSyntaxNodeVisitor(SyntaxNode $node)
    {
        $visitor = new SyntaxNodeVisitor($this->visitContext, $this->dotWriter, $this->formatter);
        $dispatcher = $this->createDispatchingVisitor($visitor, $node);
        return $dispatcher;
    }
}
