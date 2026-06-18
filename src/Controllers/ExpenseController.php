<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Budget;
use App\Models\Expense;

class ExpenseController extends Controller
{
    public function list(): void
    {
        $this->requireAuth();
        $mois = $_GET['mois'] ?? date('Y-m');
        $type = $_GET['type'] ?? 'reel';
        $budget = Budget::getCurrent($this->userId, $mois);
        if (!$budget) {
            $this->json([]);
            return;
        }
        $expenses = Expense::getByBudget($budget['id'], $type === 'prevu');
        $this->json($expenses);
    }

    public function add(): void
    {
        $this->requireAuth();
        $mois = $_POST['mois'] ?? date('Y-m');
        $budget = Budget::getOrCreate($this->userId, $mois);

        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $dateDepense = $_POST['date_depense'] ?? date('Y-m-d');
        $estPrevu = !empty($_POST['est_prevu']);

        if ($categoryId <= 0 || $montant <= 0) {
            $this->json(['error' => 'Catégorie et montant requis.'], 400);
            return;
        }

        $id = Expense::create($budget['id'], $categoryId, $montant, $description, $dateDepense, $estPrevu);
        $expense = Expense::getById($id);
        $this->json(['success' => true, 'expense' => $expense]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $dateDepense = $_POST['date_depense'] ?? date('Y-m-d');

        if ($id <= 0) {
            $this->json(['error' => 'ID requis.'], 400);
            return;
        }

        Expense::update($id, $categoryId, $montant, $description, $dateDepense);
        $expense = Expense::getById($id);
        $this->json(['success' => true, 'expense' => $expense]);
    }

    public function delete(): void
    {
        $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID requis.'], 400);
            return;
        }
        Expense::delete($id);
        $this->json(['success' => true]);
    }
}