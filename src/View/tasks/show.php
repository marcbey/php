<!-- Task-Detailansicht im Read-Only-Modus. -->
<section class="header">
  <div>
    <h1>Aufgabe anzeigen</h1>
    <p>Detailansicht ohne Bearbeitungsmodus.</p>
  </div>
  <a class="btn btn-secondary" href="/?entity=tasks">Zurück</a>
</section>

<section class="card">
  <div>
    <strong>Titel</strong>
    <div><?= e($task->title()) ?></div>
  </div>

  <div>
    <strong>Beschreibung</strong>
    <div class="muted"><?= e($task->description() ?? '-') ?></div>
  </div>

  <div>
    <strong>Status</strong>
    <div><span class="badge badge-<?= e($task->status()) ?>"><?= e($task->status()) ?></span></div>
  </div>

  <div>
    <strong>Erstellt</strong>
    <div><?= e($task->createdAt()->format('d.m.Y H:i')) ?></div>
  </div>

  <div>
    <strong>Aktualisiert</strong>
    <div><?= e($task->updatedAt()->format('d.m.Y H:i')) ?></div>
  </div>

  <div class="actions">
    <a class="btn" href="/?entity=tasks&action=edit&id=<?= $task->id() ?>">Bearbeiten</a>
  </div>
</section>
