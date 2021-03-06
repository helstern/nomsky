<?php namespace Helstern\Nomsky\Grammars\Ebnf\IsoEbnf;


use Helstern\Nomsky\Exception\SyntacticException;
use Helstern\Nomsky\Grammars\Ebnf\Ast\ChoiceNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\CommentNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\SpecialSequenceNode;
use Helstern\Nomsky\Parser\Ast\AstNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\GroupNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\IdentifierNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\LiteralNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\RepetitionNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\OptionalNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\RuleNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\ConcatenationNode;
use Helstern\Nomsky\Grammars\Ebnf\Ast\SyntaxNode;
use Helstern\Nomsky\Parser\Errors\ParseAssertions;
use Helstern\Nomsky\Parser\Lexer;

use Helstern\Nomsky\Grammars\Ebnf\IsoEbnf\TokenTypes;

/**
 * Class StandardEbnfParser
 * @see http://standards.iso.org/ittf/PubliclyAvailableStandards/s026153_ISO_IEC_14977_1996(E).zip
 */
class Parser
{
    /** @var ParseAssertions */
    protected $parseAssertions;

    /**
     * @param ParseAssertions $tokenAssertions
     */
    public function __construct(ParseAssertions $tokenAssertions)
    {
        $this->parseAssertions = $tokenAssertions;
    }

    /**
     * @param Lexer $lexer
     * @return SyntaxNode
     */
    public function parse(Lexer $lexer)
    {
        $astNode = $this->parseSyntax($lexer);
        return $astNode;
    }

    /**
     * @param Lexer $lexer
     * @return SyntaxNode
     */
    protected function parseSyntax(Lexer $lexer)
    {
        $token = $lexer->currentToken();
        $this->parseAssertions->assertNotEOF($token);

        $startTextPosition = null;
        $grammarTitle = null;
        $grammarComment = null;

        if ($token->getType() == TokenTypes::ENUM_STRING_LITERAL) {
            $grammarTitle = $this->parseStringLiteral($lexer);
            $lexer->nextToken();
        }

        $token = $lexer->currentToken();
        $this->parseAssertions->assertValidTokenType($token, TokenTypes::ENUM_START_REPEAT);
        if (is_null($startTextPosition)) {
            $startTextPosition = $token->getPosition();
        }
        $lexer->nextToken();

        $productionNodes = array();
        $token = $lexer->currentToken();
        while ($token->getType() == TokenTypes::ENUM_COMMENT) {
            $productionNodes[] = $this->parseComment($lexer);
            $lexer->nextToken();
            $token = $lexer->currentToken();
        }

        while ($token->getType() == TokenTypes::ENUM_IDENTIFIER) {
            $productionNodes[] = $this->parseRule($lexer);
            $lexer->nextToken();
            $token = $lexer->currentToken();

            if ($token->getType() == TokenTypes::ENUM_COMMENT) {
                $productionNodes[] = $this->parseComment($lexer);
                $lexer->nextToken();
                $token = $lexer->currentToken();
            }
        }

        $this->parseAssertions->assertValidTokenType($token, TokenTypes::ENUM_END_REPEAT);
        $lexer->nextToken();
        $token = $lexer->currentToken();


        if ($token->getType() == TokenTypes::ENUM_STRING_LITERAL) {
            $grammarComment = $this->parseStringLiteral($lexer);
            $lexer->nextToken();
            $token = null;
        }

        $syntaxNode = new SyntaxNode($startTextPosition, $productionNodes, $grammarTitle, $grammarComment);
        return $syntaxNode;
    }

    /**
     * @param Lexer $lexer
     * @return CommentNode
     * @throws \Helstern\Nomsky\Exception\SyntacticException
     */
    protected function parseComment(Lexer $lexer)
    {

        $token = $lexer->currentToken();

        if ($token->getType() != TokenTypes::ENUM_COMMENT) {
            throw new SyntacticException($token, 'Expected ' . TokenTypes::ENUM_COMMENT);
        }

        $textPosition = $token->getPosition();
        $literal = $token->getValue();

        $node = new CommentNode($textPosition, $literal);
        return $node;
    }

    /**
     * @param Lexer $lexer
     * @return \Helstern\Nomsky\Grammars\Ebnf\Ast\RuleNode
     */
    protected function parseRule(Lexer $lexer)
    {
        $identifierNode = $this->parseIdentifier($lexer);

        $peekToken = $lexer->peekToken();
        $this->parseAssertions->assertValidTokenType($peekToken, TokenTypes::ENUM_DEFINITION_LIST_START);

        $lexer->nextToken();
        $expressionNode = $this->parseExpression($lexer);

        $peekToken = $lexer->peekToken();

        $this->parseAssertions->assertValidTokenType($peekToken, TokenTypes::ENUM_TERMINATOR);
        $lexer->nextToken();

        $textPosition = $identifierNode->getTextPosition();
        $node = new RuleNode($textPosition, $identifierNode, $expressionNode);
        return $node;
    }

    /**
     * @param Lexer $lexer
     * @return \Helstern\Nomsky\Grammars\Ebnf\Ast\IdentifierNode
     * @throws SyntacticException
     */
    protected function parseIdentifier(Lexer $lexer)
    {
        $token = $lexer->currentToken();
        $this->parseAssertions->assertValidTokenType($token, TokenTypes::ENUM_IDENTIFIER);

        $identifierName = $token->getValue();
        $textPosition = $token->getPosition();
        $node = new IdentifierNode($textPosition, $identifierName);

        return $node;
    }

    /**
     * @param Lexer $lexer
     *
*@return ChoiceNode|GroupNode|IdentifierNode|LiteralNode|RepetitionNode|OptionalNode|ConcatenationNode
     */
    protected function parseExpression(Lexer $lexer)
    {
        $head = $this->parseTerm($lexer);
        $tail = array();

        $peekToken = $lexer->peekToken();
        $predicates = $this->parseAssertions->getTokenPredicates();
        while ($predicates->hasSameType($peekToken, TokenTypes::ENUM_DEFINITION_SEPARATOR)) {
            $lexer->nextToken();
            $tail[] = $this->parseTerm($lexer);

            $peekToken = $lexer->peekToken();
        }

        if (empty($tail)) {
            return $head;
        }

        $textPosition = $head->getTextPosition();
        $node = new ChoiceNode($textPosition, $head, $tail);
        return $node;
    }

    /**
     * @param Lexer $lexer
     *
*@return \Helstern\Nomsky\Grammars\Ebnf\Ast\IdentifierNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\OptionalNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\ConcatenationNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\SpecialSequenceNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\LiteralNode|null
     */
    protected function parseTerm(Lexer $lexer)
    {
        //first factor
        $head = $this->parseFactor($lexer);

        $tail = array();
        $predicates = $this->parseAssertions->getTokenPredicates();
        $peekToken = $lexer->peekToken();
        while ($predicates->hasSameType($peekToken, TokenTypes::ENUM_CONCATENATE)) {
            $lexer->nextToken();
            $tail[] = $this->parseFactor($lexer);

            $peekToken = $lexer->peekToken();
        }

        if (empty($tail)) {
            return $head;
        }

        $textPosition = $head->getTextPosition();
        $node = new ConcatenationNode($textPosition, $head, $tail);
        return $node;
    }

    /**
     * @param Lexer $lexer
     *
*@return GroupNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\IdentifierNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\OptionalNode|RepetitionNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\SpecialSequenceNode|\Helstern\Nomsky\Grammars\Ebnf\Ast\LiteralNode|null
     * @throws \Exception
     */
    protected function parseFactor(Lexer $lexer)
    {
        $predicates = $this->parseAssertions->getTokenPredicates();
        $peekToken = $lexer->peekToken();

        /** @var AstNode $node */
        $node = null;
        if ($predicates->hasSameType($peekToken, TokenTypes::ENUM_IDENTIFIER)) {
            $lexer->nextToken();
            $node = $this->parseIdentifier($lexer);
        } elseif ($predicates->hasSameType($peekToken, TokenTypes::ENUM_STRING_LITERAL)) {
            $lexer->nextToken();
            $node = $this->parseStringLiteral($lexer);
        } elseif ($predicates->hasSameType($peekToken, TokenTypes::ENUM_SPECIAL_SEQUENCE)) {
            $lexer->nextToken();
            $node = $this->parseSpecialExpression($lexer);
        } elseif ($predicates->hasSameType($peekToken, TokenTypes::ENUM_START_OPTION)) {
            $lexer->nextToken();
            $node = $this->parseOptionalExpression($lexer);
        } elseif ($predicates->hasSameType($peekToken, TokenTypes::ENUM_START_GROUP)) {
            $lexer->nextToken();
            $node = $this->parseGroupedExpression($lexer);
        } elseif ($predicates->hasSameType($peekToken, TokenTypes::ENUM_START_REPEAT)) {
            $lexer->nextToken();
            $node = $this->parseRepeatedExpression($lexer);
        }

        if (is_null($node)) {
            throw new \Exception('boo');
        }

        return $node;
    }

    /**
     * @param Lexer $lexer
     *
*@return \Helstern\Nomsky\Grammars\Ebnf\Ast\LiteralNode
     * @throws SyntacticException
     */
    protected function parseStringLiteral(Lexer $lexer)
    {
        $token = $lexer->currentToken();

        if ($token->getType() != TokenTypes::ENUM_STRING_LITERAL) {
            throw new SyntacticException($token, 'Expected ' . TokenTypes::ENUM_STRING_LITERAL);
        }

        $textPosition = $token->getPosition();
        $literal = $token->getValue();

        $node = new LiteralNode($textPosition, $literal);
        return $node;
    }

    /**
     * @param Lexer $lexer
     * @return \Helstern\Nomsky\Grammars\Ebnf\Ast\SpecialSequenceNode
     * @throws SyntacticException
     */
    protected function parseSpecialExpression(Lexer $lexer)
    {
        $token = $lexer->currentToken();

        if ($token->getType() != TokenTypes::ENUM_SPECIAL_SEQUENCE) {
            throw new SyntacticException($token, 'Expected ' . TokenTypes::ENUM_SPECIAL_SEQUENCE);
        }

        $textPosition = $token->getPosition();
        $literal = $token->getValue();

        $node = new SpecialSequenceNode($textPosition, $literal);
        return $node;
    }

    /**
     * @param Lexer $lexer
     *
*@return \Helstern\Nomsky\Grammars\Ebnf\Ast\OptionalNode
     */
    protected function parseOptionalExpression(Lexer $lexer)
    {
        $token = $lexer->currentToken();
        $this->parseAssertions->assertValidTokenType($token, TokenTypes::ENUM_START_OPTION);
        $startPosition = $token->getPosition();

        $expression = $this->parseExpression($lexer);

        $peekToken = $lexer->peekToken();
        $this->parseAssertions->assertValidTokenType($peekToken, TokenTypes::ENUM_END_OPTION);
        $lexer->nextToken();

        $node = new OptionalNode($startPosition, $expression);
        return $node;
    }

    /**
     * @param Lexer $lexer
     *
     * @return GroupNode
     */
    protected function parseGroupedExpression(Lexer $lexer)
    {
        $token = $lexer->currentToken();
        $this->parseAssertions->assertValidTokenType($token, TokenTypes::ENUM_START_GROUP);
        $startPosition = $token->getPosition();

        $expression = $this->parseExpression($lexer);

        $peekToken = $lexer->peekToken();
        $this->parseAssertions->assertValidTokenType($peekToken, TokenTypes::ENUM_END_GROUP);
        $lexer->nextToken();

        $node = new GroupNode($startPosition, $expression);
        return $node;
    }

    /**
     * @param Lexer $lexer
     *
     * @return RepetitionNode
     */
    protected function parseRepeatedExpression(Lexer $lexer)
    {
        $token = $lexer->currentToken();
        $this->parseAssertions->assertValidTokenType($token, TokenTypes::ENUM_START_REPEAT);

        $startPosition = $token->getPosition();

        $expression = $this->parseExpression($lexer);

        $peekToken = $lexer->peekToken();
        $this->parseAssertions->assertValidTokenType($peekToken, TokenTypes::ENUM_END_REPEAT);
        $lexer->nextToken();

        $node = new RepetitionNode($startPosition, $expression);
        return $node;
    }
}
