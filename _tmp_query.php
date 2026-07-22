<?php
// Read-only query script for dream consolidation
$db = new PDO('sqlite:C:\Users\DELL\.local\share\mimocode\mimocode.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== RECENT SESSIONS (motel-app) ===\n";
$res = $db->query("SELECT id, directory, title, datetime(time_created, 'unixepoch') as created FROM session WHERE directory LIKE '%motel-app%' ORDER BY time_created DESC LIMIT 20");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['id']} | {$row['created']} | " . ($row['title'] ?: 'no title') . "\n";
}

echo "\n=== USER STATEMENTS (remember/always/never/rule/decision) ===\n";
$res = $db->query("SELECT p.session_id, substr(json_extract(p.data, '$.text'), 1, 400) as txt FROM part p WHERE json_extract(p.data, '$.type') = 'text' AND (json_extract(p.data, '$.text') LIKE '%remember%' OR json_extract(p.data, '$.text') LIKE '%always%' OR json_extract(p.data, '$.text') LIKE '%never%' OR json_extract(p.data, '$.text') LIKE '%rule%' OR json_extract(p.data, '$.text') LIKE '%decision%' OR json_extract(p.data, '$.text') LIKE '%decided%') ORDER BY p.time_created DESC LIMIT 50");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  [{$row['session_id']}] {$row['txt']}\n---\n";
}

echo "\n=== USER STATEMENTS (repeat/again/every time/workflow/do not/must not) ===\n";
$res = $db->query("SELECT p.session_id, substr(json_extract(p.data, '$.text'), 1, 400) as txt FROM part p WHERE json_extract(p.data, '$.type') = 'text' AND (json_extract(p.data, '$.text') LIKE '%repeat%' OR json_extract(p.data, '$.text') LIKE '%again%' OR json_extract(p.data, '$.text') LIKE '%every time%' OR json_extract(p.data, '$.text') LIKE '%workflow%' OR json_extract(p.data, '$.text') LIKE '%don''t%' OR json_extract(p.data, '$.text') LIKE '%do not%' OR json_extract(p.data, '$.text') LIKE '%must not%') ORDER BY p.time_created DESC LIMIT 50");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  [{$row['session_id']}] {$row['txt']}\n---\n";
}

echo "\n=== USER STATEMENTS (error/bug/broken/fix) ===\n";
$res = $db->query("SELECT p.session_id, substr(json_extract(p.data, '$.text'), 1, 400) as txt FROM part p WHERE json_extract(p.data, '$.type') = 'text' AND json_extract(m.data, '$.role') = 'user' AND (json_extract(p.data, '$.text') LIKE '%error%' OR json_extract(p.data, '$.text') LIKE '%bug%' OR json_extract(p.data, '$.text') LIKE '%broken%' OR json_extract(p.data, '$.text') LIKE '%fix%') ORDER BY p.time_created DESC LIMIT 30");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  [{$row['session_id']}] {$row['txt']}\n---\n";
}

echo "\n=== RECENT TASKS ===\n";
$res = $db->query("SELECT id, session_id, title, status, datetime(created_at, 'unixepoch') as created FROM task WHERE session_id IN (SELECT id FROM session WHERE directory LIKE '%motel-app%') ORDER BY created_at DESC LIMIT 20");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['id']} | {$row['session_id']} | {$row['status']} | {$row['title']}\n";
}

echo "\n=== RECENT TASK EVENTS ===\n";
$res = $db->query("SELECT te.task_id, te.event_type, substr(te.data, 1, 300) as data_preview, datetime(te.time_created, 'unixepoch') as created FROM task_event te JOIN task t ON te.task_id = t.id WHERE t.session_id IN (SELECT id FROM session WHERE directory LIKE '%motel-app%') ORDER BY te.time_created DESC LIMIT 20");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['task_id']} | {$row['event_type']} | {$row['created']} | {$row['data_preview']}\n";
}

echo "\n=== ACTOR REGISTRY (subagents) ===\n";
$res = $db->query("SELECT ar.id, ar.session_id, ar.type, substr(ar.data, 1, 300) as data_preview FROM actor_registry ar WHERE ar.session_id IN (SELECT id FROM session WHERE directory LIKE '%motel-app%') ORDER BY ar.time_created DESC LIMIT 20");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['id']} | {$row['session_id']} | {$row['type']} | {$row['data_preview']}\n";
}

echo "\n=== MESSAGE COUNT PER SESSION ===\n";
$res = $db->query("SELECT s.id, s.title, COUNT(m.id) as msg_count FROM session s JOIN message m ON m.session_id = s.id WHERE s.directory LIKE '%motel-app%' GROUP BY s.id ORDER BY MAX(m.time_created) DESC LIMIT 20");
foreach ($res->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "  {$row['id']} | msgs:{$row['msg_count']} | " . ($row['title'] ?: 'no title') . "\n";
}

$db = null;
