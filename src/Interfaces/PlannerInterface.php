<?php
namespace App\Interfaces;

interface PlannerInterface
{
    public function generatePlan(int $userId): array;
    public function getSuggestions(int $userId): array;
}
