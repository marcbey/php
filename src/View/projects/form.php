<!-- File purpose: form template in the src view layer for the CRUD demo app. -->
<section class="header">
  <div>
    <h1><?= $mode === 'edit' ? 'Projekt bearbeiten' : 'Neues Projekt' ?></h1>
    <p><?= $mode === 'edit' ? 'Änderungen speichern' : 'Projekt anlegen' ?></p>
  </div>
  <a class="btn btn-secondary" href="/?entity=projects">Zurück</a>
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

<form method="post" action="/?entity=projects&action=<?= $mode === 'edit' ? 'update&id=' . $project->id() : 'store' ?>" class="card">
  <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

  <label>
    Projektname
    <input type="text" name="name" maxlength="150" required value="<?= e((string) $input['name']) ?>">
  </label>

  <label>
    Kunde
    <input type="text" name="client_name" maxlength="120" value="<?= e((string) $input['client_name']) ?>">
  </label>

  <label>
    Budget (EUR)
    <input type="text" name="budget" inputmode="decimal" maxlength="13" value="<?= e((string) $input['budget_input']) ?>">
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
