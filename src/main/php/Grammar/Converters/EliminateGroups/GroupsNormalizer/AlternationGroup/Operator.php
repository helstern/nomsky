<?php namespace Helstern\Nomsky\Grammar\Converters\EliminateGroups\GroupsNormalizer\AlternationGroup;

use Helstern\Nomsky\Grammar\Converters\EliminateGroups\GroupsNormalizer\NormalizeOperator;
use Helstern\Nomsky\Grammar\Converters\EliminateGroups\GroupsNormalizer\OperationResult\AlternationResult;
use Helstern\Nomsky\Grammar\Expressions\Expression;
use Helstern\Nomsky\Grammar\Expressions\Sequence;

class Operator implements NormalizeOperator
{
    /**
     * (x | y) | (a | b) => x | y | a | b
     *
     * @param array|Expression[] $leftGroupItems
     * @param array|Expression[] $rightGroupItems
     * @return AlternationResult
     */
    public function operateOnAlternationAndAlternation(array $leftGroupItems, array $rightGroupItems)
    {
        $normalized = array_merge($leftGroupItems, $rightGroupItems);

        return new AlternationResult($normalized);
    }

    /**
     * (x | y) | (a b) => x | y | a b
     *
     * @param array|Expression[] $leftGroupItems alternation items
     * @param array|Expression[] $rightGroupItems sequence items
     * @return AlternationResult
     */
    public function operateOnAlternationAndSequence(array $leftGroupItems, array $rightGroupItems)
    {
        $tail = new Sequence(array_shift($rightGroupItems), $rightGroupItems);
        $normalized = array_merge($leftGroupItems,array($tail));

        return new AlternationResult($normalized);
    }

    /**
     * (x y) | (a | b) => x y | a | b
     *
     * @param array $leftGroupItems sequence items
     * @param array $rightGroupItems alternation items
     * @return AlternationResult
     */
    public function operateOnSequenceAndAlternation(array $leftGroupItems, array $rightGroupItems)
    {
        $head = new Sequence(array_shift($leftGroupItems), $leftGroupItems);
        $normalized = array_merge(array($head), $rightGroupItems);

        return new AlternationResult($normalized);
    }

    /**
     * (x y) | (a b) => x y | a b
     *
     * @param array $leftGroupItems sequence items
     * @param array $rightGroupItems sequence items
     * @return AlternationResult
     */
    public function operateOnSequenceAndSequence(array $leftGroupItems, array $rightGroupItems)
    {
        $head = new Sequence(array_shift($leftGroupItems), $leftGroupItems);
        $tail = new Sequence(array_shift($rightGroupItems), $rightGroupItems);

        $normalized = array($head, $tail);
        return new AlternationResult($normalized);
    }
}