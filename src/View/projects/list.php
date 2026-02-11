<?php

// Projekt-Index-Template: listet Datensaetze und bietet Bearbeiten-/Loeschen-Aktionen.
/** @var App\Model\Project[] $projects */
?>
<section class="header">
  <div>
    <h1>Projects</h1>
    <p>Zweite Entität für den Kurztest.</p>
  </div>
  <a class="btn" href="/?entity=projects&action=create">Neues Projekt</a>
</section>

<?php if (!empty($flash)): ?>
  <div class="flash"><?= e($flash) ?></div>
<?php endif; ?>

<?php if ($projects === []): ?>
  <div class="empty">Noch keine Projekte.</div>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Kunde</th>
        <th>Budget</th>
        <th>Status</th>
        <th>Aktualisiert</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $project): ?>
        <tr>
          <td><strong><?= e($project->name()) ?></strong></td>
          <td><?= e($project->clientName() ?? '-') ?></td>
          <td><?= e($project->budget() !== null ? $project->budget() . ' EUR' : '-') ?></td>
          <td><span class="badge badge-<?= e($project->status()) ?>"><?= e($project->status()) ?></span></td>
          <td><?= e($project->updatedAt()->format('d.m.Y H:i')) ?></td>
          <td class="actions">
            <a class="btn btn-secondary" href="/?entity=projects&action=show&id=<?= $project->id() ?>">Anzeigen</a>
            <a class="btn btn-secondary" href="/?entity=projects&action=edit&id=<?= $project->id() ?>">Bearbeiten</a>
            <form method="post" action="/?entity=projects&action=delete&id=<?= $project->id() ?>" class="inline">
              <input type="hidden" name="_method" value="DELETE">
              <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
              <button class="btn btn-danger" type="submit" onclick="return confirm('Wirklich löschen?');">Löschen</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>
