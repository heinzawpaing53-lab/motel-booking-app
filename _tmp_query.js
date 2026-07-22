const Database = require('better-sqlite3');
const db = new Database('C:\\Users\\DELL\\.local\\share\\mimocode\\mimocode.db');

const sessions = db.prepare(`SELECT id, directory, title, datetime(time_created, 'unixepoch') as created FROM session WHERE directory LIKE '%motel-app%' ORDER BY time_created DESC LIMIT 20`).all();
console.log("=== RECENT SESSIONS ===");
sessions.forEach(s => console.log(`  ${s.id} | ${s.created} | ${s.title || 'no title'}`));

// Get recent notes
const notes = db.prepare(`SELECT session_id, substr(data, 1, 500) as preview FROM part WHERE json_extract(data, '$.type') = 'text' AND json_extract(data, '$.text') LIKE '%remember%' OR json_extract(data, '$.text') LIKE '%always%' OR json_extract(data, '$.text') LIKE '%never%' OR json_extract(data, '$.text') LIKE '%rule%' ORDER BY time_created DESC LIMIT 30`).all();
console.log("\n=== USER STATEMENTS WITH KEYWORDS ===");
notes.forEach(n => console.log(`  [${n.session_id}] ${n.preview}\n`));

db.close();
