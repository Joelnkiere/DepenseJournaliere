<?php
namespace App\Planners;

use App\Interfaces\PlannerInterface;

class PlannerFactory
{
    public static function create(string $type = 'rule'): PlannerInterface
    {
        return match ($type) {
            'ai' => new AIPlanner(),
            default => new RuleBasedPlanner(),
        };
    }
}
