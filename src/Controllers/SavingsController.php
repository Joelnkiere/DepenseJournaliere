<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\SavingsGoal;

class SavingsController extends Controller
{
    public function list(): void
    {
        $this->requireAuth();
        $goals = SavingsGoal::getByUser($this->userId);
        $this->json($goals);
    }

    public function add(): void
    {
        $this->requireAuth();
        $titre = trim($_POST['titre'] ?? '');
        $montantCible = (float) ($_POST['montant_cible'] ?? 0);
        $dateLimite = $_POST['date_limite'] ?? null;

        if (empty($titre) || $montantCible <= 0) {
            $this->json(['error' => 'Titre et montant cible requis.'], 400);
            return;
        }

        $id = SavingsGoal::create($this->userId, $titre, $montantCible, $dateLimite ?: null);
        $goal = SavingsGoal::getById($id);
        $this->json(['success' => true, 'goal' => $goal]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $montantCible = (float) ($_POST['montant_cible'] ?? 0);
        $dateLimite = $_POST['date_limite'] ?? null;

        if ($id <= 0) {
            $this->json(['error' => 'ID requis.'], 400);
            return;
        }

        SavingsGoal::update($id, $titre, $montantCible, $dateLimite ?: null);
        $goal = SavingsGoal::getById($id);
        $this->json(['success' => true, 'goal' => $goal]);
    }

    public function delete(): void
    {
        $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->json(['error' => 'ID requis.'], 400);
            return;
        }
        SavingsGoal::delete($id);
        $this->json(['success' => true]);
    }

    public function addFunds(): void
    {
        $this->requireAuth();
        $id = (int) ($_POST['id'] ?? 0);
        $montant = (float) ($_POST['montant'] ?? 0);
        if ($id <= 0 || $montant <= 0) {
            $this->json(['error' => 'ID et montant requis.'], 400);
            return;
        }
        SavingsGoal::addFunds($id, $montant);
        $goal = SavingsGoal::getById($id);
        $this->json(['success' => true, 'goal' => $goal]);
    }
}