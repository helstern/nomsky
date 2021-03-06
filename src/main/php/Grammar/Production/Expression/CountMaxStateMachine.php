<?php namespace Helstern\Nomsky\Grammar\Production\Expression;

use Helstern\Nomsky\Grammar\Expressions\Expression;
use Helstern\Nomsky\Grammar\Expressions\Walker\WalkState\WalkStateMachine;
use Helstern\Nomsky\Grammar\Production\Expression\SymbolPredicateAdapter as PredicateExpressionAdapter;
use Helstern\Nomsky\Grammar\Symbol\Predicate\SymbolPredicate;

class CountMaxStateMachine implements WalkStateMachine
{
    /** @var SymbolPredicate */
    protected $predicateAdapter;

    /** @var int */
    protected $maxCount;

    /** @var int */
    protected $countLeft;

    public function __construct(SymbolPredicate $symbolPredicate, $maxCount)
    {
        $this->predicateAdapter  = new PredicateExpressionAdapter($symbolPredicate);
        $this->maxCount         = $maxCount;
        $this->countLeft        = $maxCount;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->maxCount - $this->countLeft;
    }

    /**
     * @param Expression $root
     * @return boolean
     */
    public function startWalking(Expression $root)
    {
        return $this->countLeft == 0 ? false : true;
    }

    /**
     * @param Expression $lastVisited
     * @return boolean
     */
    public function continueWalkingAfterVisit(Expression $lastVisited)
    {
        if ($this->countLeft == 0) {
            return false;
        }

        $found = $this->predicateAdapter->matchExpression($lastVisited);
        if ($found) {
            $this->countLeft--;
        }

        return $this->countLeft == 0 ? false : true;
    }
}
