<?php namespace Helstern\Nomsky\Grammar\Production;

use Helstern\Nomsky\Grammar\Expressions\ExpressionIterable;
use Helstern\Nomsky\Grammar\Expressions\Walker\DepthFirstStackBasedWalker;
use Helstern\Nomsky\Grammar\Expressions\Walker\Visit\NoDispatchDispatcher;

use Helstern\Nomsky\Grammar\Production\ExpressionWalkState\CountAllStateMachine;
use Helstern\Nomsky\Grammar\Production\ExpressionWalkState\CountMaxStateMachine;
use Helstern\Nomsky\Grammar\Production\ExpressionWalkState\FindFirstStateMachine;
use Helstern\Nomsky\Grammar\Production\ExpressionWalkState\FindMaxStateMachine;

use Helstern\Nomsky\Grammar\Symbol\Symbol;
use Helstern\Nomsky\Grammar\Symbol\Predicate\SymbolPredicate;
use Helstern\Nomsky\Grammar\Symbol\Predicate\SymbolTypeEquals;
use Helstern\Nomsky\Grammar\Symbol\Predicate\AnySymbolPredicate;

class DefaultProduction implements Production
{
    /** @var \Helstern\Nomsky\Grammar\Symbol\Symbol  */
    protected $nonTerminal;

    /** @var ExpressionIterable  */
    protected $expression;

    public function __construct(Symbol $nonTerminal, ExpressionIterable $expression)
    {
        $this->nonTerminal = $nonTerminal;
        $this->expression = $expression;
    }

    public function getNonTerminal()
    {
        return $this->nonTerminal;
    }

    public function getExpression()
    {
        return $this->expression;
    }

    public function startsWith($nonTerminal)
    {
        // TODO: Implement startsWith() method.
    }

    public function count()
    {
        $count = $this->countAll(AnySymbolPredicate::singletonInstance());
        return $count;
    }

    public function countMax(SymbolPredicate $predicate, $max)
    {
        $findFirstStateMachine  = new CountMaxStateMachine($predicate, $max);
        $walker                 = new DepthFirstStackBasedWalker($findFirstStateMachine);

        $expression             = $this->getExpression();
        $walker->walk($expression, NoDispatchDispatcher::singletonInstance());

        $count = $findFirstStateMachine->getCount();
        return $count;
    }

    public function countAll(SymbolPredicate $predicate)
    {
        $findFirstStateMachine  = new CountAllStateMachine($predicate);
        $walker                 = new DepthFirstStackBasedWalker($findFirstStateMachine);

        $expression             = $this->getExpression();
        $walker->walk($expression, NoDispatchDispatcher::singletonInstance());

        $count = $findFirstStateMachine->getCount();
        return $count;
    }

    public function findMax(SymbolPredicate $predicate, $max)
    {
        $findFirstStateMachine  = new FindMaxStateMachine($predicate, $max);
        $walker                 = new DepthFirstStackBasedWalker($findFirstStateMachine);

        $expression             = $this->getExpression();
        $walker->walk($expression, NoDispatchDispatcher::singletonInstance());

        $found = $findFirstStateMachine->getExpressions();
        return $found;
    }

    public function findFirst(SymbolPredicate $predicate)
    {
        $findFirstStateMachine  = new FindFirstStateMachine($predicate);
        $walker                 = new DepthFirstStackBasedWalker($findFirstStateMachine);

        $expression             = $this->getExpression();
        $walker->walk($expression, NoDispatchDispatcher::singletonInstance());

        $found = $findFirstStateMachine->getExpression();
        return $found;
    }

    public function findAll(SymbolPredicate $predicate)
    {
        return $this->findMax($predicate, PHP_INT_MAX);
    }

    public function findAllTerminals()
    {
        $predicate = SymbolTypeEquals::newInstance(Symbol::TYPE_TERMINAL);
        return $this->findMax($predicate, PHP_INT_MAX);
    }

    public function findAllNonTerminals()
    {
        $predicate = SymbolTypeEquals::newInstance(Symbol::TYPE_NON_TERMINAL);
        return $this->findMax($predicate, PHP_INT_MAX);
    }

    public function getFirstSymbol()
    {
        $symbols = $this->getSymbols();
        if (count($symbols)) {
            return array_shift($symbols);
        }
        return null;
    }

    public function getSymbols()
    {
        $predicate = AnySymbolPredicate::singletonInstance();
        $symbols = $this->findMax($predicate, PHP_INT_MAX);

        return $symbols;
    }
}