<?php
namespace App\Helpers;

class FinancialAdvisor
{
    private float $revenu;
    private array $depensesReelles;
    private array $previsions;
    private int $jourActuel;
    private int $joursDansMois;

    public function __construct(float $revenu, array $depensesReelles, array $previsions)
    {
        $this->revenu = $revenu;
        $this->depensesReelles = $depensesReelles;
        $this->previsions = $previsions;
        $this->jourActuel = (int) date('j');
        $this->joursDansMois = (int) date('t');
    }

    public function getAdvice(): array
    {
        return [
            'regle_50_30_20' => $this->analyserRegle50_30_20(),
            'prediction_fin_mois' => $this->predireFinMois(),
            'astuce_du_jour' => $this->astuceEconomie(),
            'alertes_categories' => $this->verifierAlertesCategories(),
        ];
    }

    private function analyserRegle50_30_20(): array
    {
        if ($this->revenu <= 0) {
            return [
                'statut' => 'info',
                'message' => 'Configurez votre revenu mensuel pour obtenir l\'analyse 50/30/20.',
                'besoins_pct' => 0,
                'envies_pct' => 0,
                'epargne_pct' => 0,
            ];
        }

        $besoins = 0;
        $envies = 0;
        $epargne = 0;

        foreach ($this->depensesReelles as $dep) {
            $type = $dep['category_type'] ?? '';
            if ($type === 'besoin') $besoins += (float) $dep['total_reel'];
            elseif ($type === 'envie') $envies += (float) $dep['total_reel'];
            elseif ($type === 'epargne') $epargne += (float) $dep['total_reel'];
        }

        $besoinsPct = round(($besoins / $this->revenu) * 100, 1);
        $enviesPct = round(($envies / $this->revenu) * 100, 1);
        $epargnePct = round(($epargne / $this->revenu) * 100, 1);

        $conseils = [];
        if ($besoinsPct > 50) {
            $conseils[] = "Réduisez vos besoins ({$besoinsPct}% vs 50% recommandé) : envisagez de renégocier vos abonnements.";
        } elseif ($besoinsPct < 50) {
            $conseils[] = "Vos besoins sont bien maîtrisés ({$besoinsPct}%).";
        }

        if ($enviesPct > 30) {
            $conseils[] = "Limitez vos envies ({$enviesPct}% vs 30% recommandé) : essayez la règle des 24h avant un achat.";
        } elseif ($enviesPct <= 30) {
            $conseils[] = "Vos loisirs sont dans les clous ({$enviesPct}%).";
        }

        if ($epargnePct < 20 && $this->revenu > 0) {
            $conseils[] = "Augmentez votre épargne ({$epargnePct}% vs 20% recommandé) : automatisez un virement mensuel.";
        } elseif ($epargnePct >= 20) {
            $conseils[] = "Bravo ! Vous épargnez suffisamment ({$epargnePct}%). Continuez ainsi !";
        }

        return [
            'statut' => $epargnePct >= 20 && $enviesPct <= 30 && $besoinsPct <= 50 ? 'success' : 'warning',
            'message' => implode(' ', $conseils),
            'besoins_pct' => $besoinsPct,
            'envies_pct' => $enviesPct,
            'epargne_pct' => $epargnePct,
            'besoins_ideal' => 50,
            'envies_ideal' => 30,
            'epargne_ideal' => 20,
        ];
    }

    private function predireFinMois(): array
    {
        if ($this->revenu <= 0) {
            return [
                'statut' => 'info',
                'message' => 'Configurez votre revenu pour voir les prédictions.',
            ];
        }

        $totalDepense = array_sum(array_column($this->depensesReelles, 'total_reel'));

        if ($this->jourActuel <= 1 || $totalDepense <= 0) {
            return [
                'statut' => 'info',
                'message' => 'Pas assez de données pour une prédiction ce mois-ci.',
            ];
        }

        $moyenneJournaliere = $totalDepense / ($this->jourActuel - 1);
        $joursRestants = $this->joursDansMois - $this->jourActuel + 1;
        $projectionFinMois = $totalDepense + ($moyenneJournaliere * $joursRestants);
        $restePrevu = $this->revenu - $projectionFinMois;

        // Jours avant decouvert
        if ($moyenneJournaliere > 0) {
            $joursAvantDecouvert = floor($this->revenu / $moyenneJournaliere);
        } else {
            $joursAvantDecouvert = $this->joursDansMois + 1;
        }

        $dateDecouvert = min($joursAvantDecouvert, $this->joursDansMois);

        if ($restePrevu < 0) {
            $jourDecouvert = (int) ceil($this->revenu / $moyenneJournaliere);
            return [
                'statut' => 'danger',
                'message' => "Attention ! À ce rythme ({$moyenneJournaliere}€/jour), vous serez à découvert autour du {$jourDecouvert}. Réduisez vos dépenses de " . round(abs($restePrevu) / $joursRestants, 2) . "€/jour.",
                'projection' => round($projectionFinMois, 2),
                'reste_prevu' => round($restePrevu, 2),
            ];
        }

        if ($restePrevu < $this->revenu * 0.1) {
            return [
                'statut' => 'warning',
                'message' => "Vous risquez de terminer le mois avec seulement {$restePrevu}€. Essayez d'économiser " . round($restePrevu / $joursRestants, 2) . "€/jour.",
                'projection' => round($projectionFinMois, 2),
                'reste_prevu' => round($restePrevu, 2),
            ];
        }

        return [
            'statut' => 'success',
            'message' => "Bonne gestion ! Projection de fin de mois : {$restePrevu}€ restants. Continuez ainsi.",
            'projection' => round($projectionFinMois, 2),
            'reste_prevu' => round($restePrevu, 2),
        ];
    }

    private function astuceEconomie(): array
    {
        $astuces = [
            'Alimentation' => 'Préparez vos repas à l\'avance pour éviter les achats impulsifs.',
            'Transport' => 'Utilisez les transports en commun ou le covoiturage pour réduire vos frais.',
            'Loisirs' => 'Explorez des activités gratuites : randonnées, musées gratuits, bibliothèques.',
            'Shopping' => 'Appliquez la règle des 30 jours : attendez 30 jours avant tout achat non essentiel.',
            'Restaurant' => 'Limitez les restaurants à 2 fois par semaine et cuisinez le reste du temps.',
            'Abonnements' => 'Faites l\'inventaire de vos abonnements et résiliez ceux inutilisés.',
            'Logement' => 'Éteignez les appareils en veille pour réduire votre facture d\'électricité.',
            'Sante' => 'Privilégiez les médicaments génériques et la prévention.',
            'Voyages' => 'Réservez vos voyages en basse saison et utilisez des comparateurs de prix.',
            'Assurances' => 'Comparez les assurances chaque année pour trouver de meilleures offres.',
            'Education' => 'Utilisez les ressources gratuites en ligne (MOOC, YouTube) pour apprendre.',
        ];

        $depensesTriees = $this->depensesReelles;
        usort($depensesTriees, fn($a, $b) => (float)($b['total_reel'] ?? 0) <=> (float)($a['total_reel'] ?? 0));

        if (!empty($depensesTriees)) {
            $topCategorie = $depensesTriees[0]['category_nom'] ?? '';
            if (isset($astuces[$topCategorie])) {
                return [
                    'categorie' => $topCategorie,
                    'astuce' => $astuces[$topCategorie],
                    'montant' => (float) $depensesTriees[0]['total_reel'],
                ];
            }
        }

        $random = $astuces[array_rand($astuces)];
        return [
            'categorie' => 'Général',
            'astuce' => $random,
            'montant' => 0,
        ];
    }

    private function verifierAlertesCategories(): array
    {
        $alertes = [];
        foreach ($this->previsions as $prev) {
            $prevu = (float) ($prev['montant_prevu'] ?? 0);
            if ($prevu <= 0) continue;

            foreach ($this->depensesReelles as $reel) {
                if ((int)($reel['category_id'] ?? 0) === (int)($prev['category_id'] ?? 0)) {
                    $reelMontant = (float) ($reel['total_reel'] ?? 0);
                    $ratio = $prevu > 0 ? ($reelMontant / $prevu) * 100 : 0;
                    if ($ratio >= 80) {
                        $alertes[] = [
                            'categorie' => $prev['category_nom'] ?? '',
                            'prevu' => $prevu,
                            'reel' => $reelMontant,
                            'ratio' => round($ratio, 1),
                            'message' => "{$prev['category_nom']} : {$ratio}% du budget utilisé ({$reelMontant}€/{$prevu}€)",
                        ];
                    }
                    break;
                }
            }
        }
        return $alertes;
    }
}