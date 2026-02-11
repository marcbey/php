<!-- Projekt-Detailansicht im Read-Only-Modus. -->
<section class="header">
  <div>
    <h1>Projekt anzeigen</h1>
    <p>Detailansicht ohne Bearbeitungsmodus.</p>
  </div>
  <a class="btn btn-secondary" href="/?entity=projects">Zurück</a>
</section>

<section class="card">
  <div>
    <strong>Name</strong>
    <div><?= e($project->name()) ?></div>
  </div>

  <div>
    <strong>Kunde</strong>
    <div class="muted"><?= e($project->clientName() ?? '-') ?></div>
  </div>

  <div>
    <strong>Budget</strong>
    <div><?= e($project->budget() !== null ? $project->budget() . ' EUR' : '-') ?></div>
  </div>

  <div>
    <strong>Status</strong>
    <div><span class="badge badge-<?= e($project->status()) ?>"><?= e($project->status()) ?></span></div>
  </div>

  <div>
    <strong>Erstellt</strong>
    <div><?= e($project->createdAt()->format('d.m.Y H:i')) ?></div>
  </div>

  <div>
    <strong>Aktualisiert</strong>
    <div><?= e($project->updatedAt()->format('d.m.Y H:i')) ?></div>
  </div>

  <div class="actions">
    <a class="btn" href="/?entity=projects&action=edit&id=<?= $project->id() ?>">Bearbeiten</a>
  </div>
</section>
