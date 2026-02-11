<!-- Task-Formular-Template fuer Erstellen- und Bearbeiten-Modus. -->
<section class="header">
  <div>
    <h1><?= $mode === 'edit' ? 'Aufgabe bearbeiten' : 'Neue Aufgabe' ?></h1>
    <p><?= $mode === 'edit' ? 'Änderungen speichern' : 'Neue Aufgabe anlegen' ?></p>
  </div>
  <a class="btn btn-secondary" href="/?entity=tasks">Zurück</a>
</section>

<?php if ($errors !== []): ?>
  <div class="errors">
    <strong>Bitte korrigieren:</strong>
    <ul>
      <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" action="/?entity=tasks&action=<?= $mode === 'edit' ? 'update&id=' . $task->id() : 'store' ?>" class="card">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

  <label>
    Titel
    <input type="text" name="title" maxlength="150" required value="<?= e((string) $input['title']) ?>">
  </label>

  <label>
    Beschreibung
    <textarea name="description" maxlength="1000" rows="4"><?= e((string) $input['description']) ?></textarea>
  </label>

  <label>
    Status
    <select name="status">
      <?php foreach ($statuses as $status): ?>
        <option value="<?= e($status) ?>" <?= $input['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <div class="actions">
    <button class="btn" type="submit">Speichern</button>
  </div>
</form>
