INSERT INTO tasks (title, description, status, created_at, updated_at) VALUES
('Vorstellungsgespräch vorbereiten', 'Demo-App erstellen und testen', 'in_progress', NOW(), NOW()),
('Projektstruktur erstellen', 'Ordner und Klassen anlegen', 'done', NOW(), NOW()),
('CRUD-Flow prüfen', 'Create/Read/Update/Delete testen', 'todo', NOW(), NOW());

INSERT INTO projects (name, client_name, budget, status, created_at, updated_at) VALUES
('CRM-Migration', 'Muster GmbH', 25000.00, 'active', NOW(), NOW()),
('Shop-Relaunch', 'Beispiel AG', 18000.00, 'planned', NOW(), NOW()),
('Intranet-Portal', 'Testfirma KG', 42000.00, 'completed', NOW(), NOW());
