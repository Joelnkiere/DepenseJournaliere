<?php
namespace App\Planners;

use App\Interfaces\PlannerInterface;

class AIPlanner implements PlannerInterface
{
    private PlannerInterface $fallback;

    public function __construct()
    {
        $this->fallback = new RuleBasedPlanner();
    }

    public function generatePlan(int $userId): array
    {
        // TODO: Intégrer API IA (OpenAI, Anthropic, etc.)
        // Pour l'instant, délègue au RuleBasedPlanner
        $plan = $this->fallback->generatePlan($userId);
        $plan['ai_generated'] = false;
        $plan['ai_note'] = 'Mode déterministe actif. L\'IA sera intégrée prochainement.';
        return $plan;
    }

    public function getSuggestions(int $userId): array
    {
        return $this->fallback->getSuggestions($userId);
    }
}
