<?php
// routes/web.php — VERSION CORRIGÉE
// Erreur résolue : Route [login] not defined
// Solution : ajouter Auth::routes() + importer les bons controllers

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeController;
use App\Http\Controllers\Admin\PresenceController;
use App\Http\Controllers\Admin\AbsenceController;
use App\Http\Controllers\Admin\SalaireController;
use App\Http\Controllers\Admin\CongeController;
use App\Http\Controllers\Admin\JourFerieController;
use App\Http\Controllers\Admin\SanctionController;
use App\Http\Controllers\Admin\DocumentEmployeController;
use App\Http\Controllers\Admin\ParametreController;
use App\Http\Controllers\Admin\PdfController;
use App\Http\Controllers\Admin\StatistiqueController;
use App\Http\Controllers\Pointage\PointageController;
use App\Http\Controllers\Admin\ContratController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\RapportController;

// ════════════════════════════════════════════════════════════════════
//  AUTH — Routes générées par Laravel UI
//  OBLIGATOIRE : c'est ce qui définit la route nommée "login"
// ════════════════════════════════════════════════════════════════════
Auth::routes([
    'register' => false,  // désactivé : un seul admin
    'reset'    => true,   // réinitialisation mot de passe activée
    'verify'   => false,
]);

// ════════════════════════════════════════════════════════════════════
//  PAGE D'ACCUEIL — redirection intelligente
// ════════════════════════════════════════════════════════════════════
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

// ════════════════════════════════════════════════════════════════════
//  INTERFACE PUBLIQUE DE POINTAGE — sans authentification
// ════════════════════════════════════════════════════════════════════
Route::prefix('pointage')->name('pointage.')->group(function () {
    Route::get('/',           [PointageController::class, 'index'])->name('index');
    Route::post('/empreinte', [PointageController::class, 'pointageEmpreinte'])->name('empreinte');
    Route::get('/statut',     [PointageController::class, 'statutAppareil'])->name('statut');
});

// ════════════════════════════════════════════════════════════════════
//  ROUTES ADMIN — auth + middleware admin
// ════════════════════════════════════════════════════════════════════
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('rapports', [RapportController::class, 'index'])->name('rapports.index');

        // ── Employés ─────────────────────────────────────────────────────
        Route::resource('employes', EmployeController::class);

        // Documents (sur un employé)
        Route::get('employes/{employe}/documents',           [DocumentEmployeController::class, 'index'])->name('employes.documents.index');
        Route::post('employes/{employe}/documents',           [DocumentEmployeController::class, 'store'])->name('employes.documents.store');

        // Documents (actions directes sur le document)
        Route::get('employes/documents/{document}/download', [DocumentEmployeController::class, 'download'])->name('employes.documents.download');
        Route::get('employes/documents/{document}/preview',  [DocumentEmployeController::class, 'preview'])->name('employes.documents.preview');
        Route::put('employes/documents/{document}',          [DocumentEmployeController::class, 'update'])->name('employes.documents.update');
        Route::delete('employes/documents/{document}',          [DocumentEmployeController::class, 'destroy'])->name('employes.documents.destroy');

        // ── Contrats ─────────────────────────────────────────────────────
        Route::resource('contrats', ContratController::class);
        Route::post('contrats/{contrat}/resilier',   [ContratController::class, 'resilier'])->name('contrats.resilier');
        Route::post('contrats/{contrat}/renouveler', [ContratController::class, 'renouveler'])->name('contrats.renouveler');
        Route::get('contrats/{contrat}/download',   [ContratController::class, 'download'])->name('contrats.download');
        Route::post('contrats/expiration',           [ContratController::class, 'mettreAJourExpiration'])->name('contrats.expiration');

        // ── Notifications ─────────────────────────────────────────────────
        Route::get('notifications',                       [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/{alerte}/lire',         [NotificationController::class, 'lire'])->name('notifications.lire');
        Route::post('notifications/tout-lire',             [NotificationController::class, 'toutLire'])->name('notifications.tout-lire');
        Route::delete('notifications/{alerte}',             [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::post('notifications/generer',               [NotificationController::class, 'generer'])->name('notifications.generer');

        // Biométrique
        Route::post('biometrique/enroler/{employe}',  [EmployeController::class, 'enrolerEmpreinte'])->name('biometrique.enroler');
        Route::post('biometrique/supprimer/{employe}', [EmployeController::class, 'supprimerEmpreinte'])->name('biometrique.supprimer');
        Route::get('biometrique/statut',             [EmployeController::class, 'statutBiometrique'])->name('biometrique.statut');
        Route::post('biometrique/synchroniser',       [EmployeController::class, 'synchroniserBiometrique'])->name('biometrique.sync');

        // ── Présences ────────────────────────────────────────────────────
        Route::get('presences',         [PresenceController::class, 'index'])->name('presences.index');
        Route::post('presences/manuel',  [PresenceController::class, 'pointageManuel'])->name('presences.manuel');
        Route::get('presences/rapport', [PresenceController::class, 'rapport'])->name('presences.rapport');
        Route::post('presences/generer-absences', [AbsenceController::class, 'genererDepuisPresences'])->name('presences.generer-absences');

        // ── Absences ─────────────────────────────────────────────────────
        Route::get('absences',                      [AbsenceController::class, 'index'])->name('absences.index');
        Route::post('absences',                      [AbsenceController::class, 'store'])->name('absences.store');
        Route::put('absences/{absence}',            [AbsenceController::class, 'update'])->name('absences.update');
        Route::delete('absences/{absence}',            [AbsenceController::class, 'destroy'])->name('absences.destroy');
        Route::post('absences/{absence}/justifier',  [AbsenceController::class, 'justifier'])->name('absences.justifier');

        // ── Sanctions ────────────────────────────────────────────────────
        Route::get('sanctions',                     [SanctionController::class, 'index'])->name('sanctions.index');
        Route::post('sanctions',                     [SanctionController::class, 'store'])->name('sanctions.store');
        Route::post('sanctions/{sanction}/lever',    [SanctionController::class, 'lever'])->name('sanctions.lever');
        Route::post('sanctions/{sanction}/signer',   [SanctionController::class, 'signer'])->name('sanctions.signer');
        Route::delete('sanctions/{sanction}',          [SanctionController::class, 'destroy'])->name('sanctions.destroy');

        // ── Congés ───────────────────────────────────────────────────────
        Route::resource('conges', CongeController::class);
        Route::post('conges/{conge}/approuver', [CongeController::class, 'approuver'])->name('conges.approuver');
        Route::post('conges/{conge}/refuser',   [CongeController::class, 'refuser'])->name('conges.refuser');

        // ── Jours fériés ─────────────────────────────────────────────────
        Route::get('jours-feries',                  [JourFerieController::class, 'index'])->name('jours-feries.index');
        Route::post('jours-feries',                  [JourFerieController::class, 'store'])->name('jours-feries.store');
        Route::put('jours-feries/{jourFerie}',      [JourFerieController::class, 'update'])->name('jours-feries.update');
        Route::delete('jours-feries/{jourFerie}',      [JourFerieController::class, 'destroy'])->name('jours-feries.destroy');
        Route::post('jours-feries/dupliquer',        [JourFerieController::class, 'dupliquerAnnee'])->name('jours-feries.dupliquer');

        // ── Salaires ─────────────────────────────────────────────────────
        Route::get('salaires',                        [SalaireController::class, 'index'])->name('salaires.index');
        Route::post('salaires/calculer',               [SalaireController::class, 'calculer'])->name('salaires.calculer');
        Route::post('salaires/{salaire}/paye',         [SalaireController::class, 'marquerPaye'])->name('salaires.paye');

        // ── Fiches PDF ───────────────────────────────────────────────────
        Route::get('fiches',                            [PdfController::class, 'index'])->name('fiches.index');
        Route::get('pdf/fiche-individuelle/{salaire}',  [PdfController::class, 'ficheIndividuelle'])->name('pdf.fiche-individuelle');
        Route::get('pdf/fiches-collectives',            [PdfController::class, 'fichesCollectives'])->name('pdf.fiches-collectives');
        Route::get('pdf/carte-service/{employe}',       [PdfController::class, 'carteService'])->name('pdf.carte-service');
        Route::get('pdf/rapport-presences',             [PdfController::class, 'rapportPresences'])->name('pdf.rapport-presences');

        // ── Statistiques ─────────────────────────────────────────────────
        Route::get('statistiques', [StatistiqueController::class, 'index'])->name('statistiques.index');

        // ── Paramètres ───────────────────────────────────────────────────
        Route::get('parametres', [ParametreController::class, 'index'])->name('parametres.index');
        Route::post('parametres', [ParametreController::class, 'update'])->name('parametres.update');
    });
