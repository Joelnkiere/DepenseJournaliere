<?php
namespace App\Planners;

use App\Interfaces\PlannerInterface;
use App\Models\Account;
use App\Models\BudgetPeriod;
use App\Models\Expense;
use App\Models\Revenue;
use App\Models\SavingsGoal;

class RuleBasedPlanner implements PlannerInterface
{
    public function generatePlan(int $userId): array
    {
        $period = BudgetPeriod::getOrCreateActive($userId);
        if (!$period) return ['error' => 'Aucune période active'];

        $accounts = Account::getByUser($userId);
        $totalSolde = array_sum(array_column($accounts, 'solde_actuel'));
        $revenus = Revenue::getByUser($userId, $period['id']);
        $totalRevenus = array_sum(array_column($revenus, 'montant'));
        $recurrentMensuel = Revenue::getMonthlyRecurring($userId);
        $depensesRecentes = Expense::getTotalByPeriod($period['id']);
        $goals = SavingsGoal::getByUser($userId);

        $suggestions = [];
        $planItems = [];
        $disponible = $totalSolde + $totalRevenus;

        // 1. Réserver les dépenses essentielles estimées
        $besoinsEstimes = $depensesRecentes > 0 ? $depensesRecentes * 1.1 : $recurrentMensuel * 0.5;
        $reste = $disponible - $besoinsEstimes;

        $planItems[] = [
            'type' => 'reserve',
            'label' => 'Réserve dépenses courantes',
            'montant' => round($besoinsEstimes, 2),
            'priorite' => 1,
        ];

        // 2. Plan épargne par objectif
        $totalGoals = 0;
        foreach ($goals as $g) {
            $resteGoal = $g['montant_cible'] - $g['montant_actuel'];
            $totalGoals += $resteGoal;
            $parMois = 0;
            if ($g['date_limite']) {
                $moisRestants = max(1, (strtotime($g['date_limite']) - time()) / 2592000);
                $parMois = round($resteGoal / $moisRestants, 2);
            }
            $planItems[] = [
                'type' => 'goal',
                'goal_id' => $g['id'],
                'label' => $g['titre'],
                'montant' => $resteGoal,
                'par_mois' => $parMois,
                'priorite' => 2,
            ];
        }

        // 3. Suggestion d'épargne automatique
        $epargneSuggeree = $reste > 0 ? round($reste * 0.2, 2) : 0;
        $suggestions[] = [
            'type' => 'epargne',
            'message' => "Épargne suggérée : {$epargneSuggeree}€ (20% du disponible)",
        ];

        // 4. Alerte si découvert
        if ($reste < 0) {
            $suggestions[] = [
                'type' => 'danger',
                'message' => "Dépassement estimé de " . abs(round($reste, 2)) . "€. Réduisez les dépenses.",
            ];
        }

        // 5. Répartition 50/30/20
        if ($recurrentMensuel > 0) {
            $suggestions[] = [
                'type' => 'regle',
                'message' => "Règle 50/30/20 : Besoins " . round($recurrentMensuel * 0.5, 2) . "€ | Envies " . round($recurrentMensuel * 0.3, 2) . "€ | Épargne " . round($recurrentMensuel * 0.2, 2) . "€",
            ];
        }

        // 6. Plan d'action
        $planAction = [];
        foreach ($planItems as $item) {
            if ($item['type'] === 'goal' && $item['par_mois'] > 0) {
                $planAction[] = "Épargnez {$item['par_mois']}€/mois pour « {$item['label']} »";
            }
        }
        if ($epargneSuggeree > 0) {
            $planAction[] = "Placez {$epargneSuggeree}€ en épargne de précaution";
        }
        if (empty($planAction)) {
            $planAction[] = 'Aucune action recommandée pour le moment';
        }

        return [
            'generated_at' => date('Y-m-d H:i:s'),
            'period' => $period,
            'total_solde_comptes' => $totalSolde,
            'total_revenus_periode' => $totalRevenus,
            'total_depenses_estimees' => $besoinsEstimes,
            'disponible_estime' => round($disponible, 2),
            'reste_estime' => round($reste, 2),
            'recurrent_mensuel' => $recurrentMensuel,
            'plan_items' => $planItems,
            'suggestions' => $suggestions,
            'plan_action' => $planAction,
        ];
    }

    public function getSuggestions(int $userId): array
    {
        $plan = $this->generatePlan($userId);
        return $plan['suggestions'] ?? [];
    }
}
