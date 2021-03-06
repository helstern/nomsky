<?php namespace Helstern\Nomsky\GrammarAnalysis\Production;

class SimpleHashKeyFactory implements HashKeyFactory
{
    /**
     * @param NormalizedProduction $production
     * @return string
     */
    public function hash(NormalizedProduction $production)
    {
        $symbol= $production->getLeftHandSide();
        $hash = $symbol->getType() . $symbol->toString();

        $symbols = $production->getRightHandSide();
        foreach ($symbols as $symbol) {
            $hash .= $symbol->getType() . $symbol->toString();
        }

        $hash = md5($hash);
        return $hash;
    }
}
