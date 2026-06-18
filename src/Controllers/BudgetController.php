<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Budget;
use App\Models\Category;

class BudgetController extends Controller
{
    public function saveRevenue(): void
    {
        $this->requireAuth();
        $mois = $_POST['mois'] ?? date('Y-m');
        $revenu = (float) ($_POST['revenu'] ?? 0);
        $budget = Budget::getOrCreate($this->userId, $mois);
        Budget::updateRevenue($budget['id'], $revenu);
        $this->json(['success' => true, 'revenu' => $revenu]);
    }

    public function savePrevisions(): void
    {
        $this->requireAuth();
        $mois = $_POST['mois'] ?? date('Y-m');
        $budget = Budget::getOrCreate($this->userId, $mois);
        $previsions = json_decode($_POST['previsions'] ?? '[]', true);

        foreach ($previsions as $prev) {
            Budget::setPrevision($budget['id'], (int)$prev['category_id'], (float)$prev['montant']);
        }
        $this->json(['success' => true]);
    }

    public function cloturer(): void
    {
        $this->requireAuth();
        $mois = $_POST['mois'] ?? '';
        if (!$mois) {
            $this->json(['error' => 'Mois requis'], 400);
            return;
        }
        $budget = Budget::getCurrent($this->userId, $mois);
        if ($budget) {
            Budget::cloturer($budget['id']);
            $this->json(['success' => true, 'message' => "Budget de {$mois} clôturé."]);
        } else {
            $this->json(['error' => 'Budget introuvable'], 404);
        }
    }

    public function saveEpargneAuto(): void
    {
        $this->requireAuth();
        $mois = $_POST['mois'] ?? date('Y-m');
        $montant = (float) ($_POST['montant'] ?? 0);
        $budget = Budget::getOrCreate($this->userId, $mois);
        Budget::updateEpargneAuto($budget['id'], $montant);
        $this->json(['success' => true, 'montant' => $montant]);
    }
}