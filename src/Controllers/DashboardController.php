<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\SavingsGoal;
use App\Models\Category;
use App\Helpers\FinancialAdvisor;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $mois = $_GET['mois'] ?? date('Y-m');
        $userId = $this->userId;

        $budget = Budget::getOrCreate($userId, $mois);
        $depenses = Expense::getByBudget($budget['id'], false);
        $previsions = Budget::getPrevisions($budget['id']);
        $categories = Expense::getCategoryTotals($budget['id']);
        $totalDepense = Expense::getTotalByBudget($budget['id']);
        $totalPrevu = Expense::getTotalPrevuByBudget($budget['id']);
        $resteAVivre = $budget['revenu_mensuel'] - $totalDepense;
        $totalEpargne = SavingsGoal::getTotalEpargne($userId);
        $savingsGoals = SavingsGoal::getByUser($userId);

        $advisor = new FinancialAdvisor(
            $budget['revenu_mensuel'],
            Budget::getReelByCategory($budget['id']),
            $previsions
        );
        $conseils = $advisor->getAdvice();

        $this->view('dashboard/index', [
            'budget' => $budget,
            'depenses' => $depenses,
            'previsions' => $previsions,
            'categories' => $categories,
            'totalDepense' => $totalDepense,
            'totalPrevu' => $totalPrevu,
            'resteAVivre' => $resteAVivre,
            'totalEpargne' => $totalEpargne,
            'savingsGoals' => $savingsGoals,
            'conseils' => $conseils,
            'mois' => $mois,
        ]);
    }
}