<?php

// File purpose: list module in the src layer for the CRUD demo app.
/** @var App\Model\Task[] $tasks */
?>
<section class="header">
  <div>
    <h1>Tasks</h1>
    <p>CRUD-Demo mit PDO und MariaDB.</p>
  </div>
  <a class="btn" href="/?entity=tasks&action=create">Neue Aufgabe</a>
</section>

<?php if (!empty($flash)): ?>
  <div class="flash"><?= e($flash) ?></div>
<?php endif; ?>

<?php if ($tasks === []): ?>
  <div class="empty">Noch keine Einträge.</div>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>Titel</th>
        <th>Status</th>
        <th>Aktualisiert</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tasks as $task): ?>
        <tr>
          <td>
            <strong><?= e($task->title()) ?></strong>
            <?php if ($task->description()): ?>
              <div class="muted"><?= e($task->description()) ?></div>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-<?= e($task->status()) ?>"><?= e($task->status()) ?></span></td>
          <td><?= e($task->updatedAt()->format('d.m.Y H:i')) ?></td>
          <td class="actions">
            <a class="btn btn-secondary" href="/?entity=tasks&action=edit&id=<?= $task->id() ?>">Bearbeiten</a>
            <form method="post" action="/?entity=tasks&action=delete&id=<?= $task->id() ?>" class="inline">
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
