<!-- File purpose: layout template in the src view layer for the CRUD demo app. -->
<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PDO CRUD Demo</title>
    <link rel="stylesheet" href="/styles.css">
  </head>
  <body>
    <nav class="top-nav">
      <a class="nav-link <?= ($entity ?? 'tasks') === 'tasks' ? 'active' : '' ?>" href="/?entity=tasks">Tasks</a>
      <a class="nav-link <?= ($entity ?? 'tasks') === 'projects' ? 'active' : '' ?>" href="/?entity=projects">Projects</a>
    </nav>
    <main class="container">
      <?= $content ?>
    </main>
  </body>
</html>
