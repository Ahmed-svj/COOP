<?php
/**
 * COOP installer – works the same on Windows, macOS and Linux (no mysql command-line client needed).
 * Creates coop_db from database/schema.sql (tables, views, triggers, event, app account), writes .env
 * and loads the demo data.
 *
 *   php database/install.php                      (asks before replacing coop_db)
 *   php database/install.php --yes                (no question; used by the VS Code task)
 *   php database/install.php --root-pass=secret   (if MySQL's root user has a password)
 *   Other options: --root-user=root --host=127.0.0.1 --port=3306 --no-demo
 */
declare(strict_types=1);

$opts = getopt('', ['yes', 'no-demo', 'root-user::', 'root-pass::', 'host::', 'port::']);
$rootUser = $opts['root-user'] ?? 'root';
$rootPass = $opts['root-pass'] ?? (getenv('COOP_MYSQL_ROOT_PASSWORD') ?: '');
$host = $opts['host'] ?? '127.0.0.1';
$port = $opts['port'] ?? '3306';
$base = dirname(__DIR__);

function out(string $s): void { echo $s, PHP_EOL; }
function fail(string $s): never { fwrite(STDERR, PHP_EOL . '  ✗ ' . $s . PHP_EOL . PHP_EOL); exit(1); }

out('');
out('  COOP installer');
out('  --------------');
if (version_compare(PHP_VERSION, '8.1.0', '<')) fail('PHP 8.1 or newer is needed (you have ' . PHP_VERSION . ').');
foreach (['pdo_mysql', 'mbstring', 'fileinfo'] as $ext) {
    if (!extension_loaded($ext)) fail("The PHP extension \"$ext\" is not enabled. Enable it in php.ini (extension=$ext) and run again.");
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $rootUser, $rootPass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    fail("Cannot connect to MySQL at $host:$port as \"$rootUser\". Start MySQL, or pass --root-pass=... ({$e->getMessage()})");
}
$version = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
if (stripos($version, 'mariadb') === false && version_compare(preg_replace('/[^0-9.].*$/', '', $version), '8.0.16', '<')) {
    fail("MySQL 8.0.16 or newer is needed (found $version).");
}
out("  MySQL $version at $host:$port");

if (!isset($opts['yes'])) {
    echo '  This creates the database "coop_db" (an existing coop_db is replaced). Continue? [y/N] ';
    if (strtolower(trim((string) fgets(STDIN))) !== 'y') { out('  Cancelled.'); exit(0); }
}

// Run schema.sql statement by statement, honouring DELIMITER blocks (triggers).
out('  [1/4] Creating tables, views, triggers, event and app account...');
$sql = (string) file_get_contents("$base/database/schema.sql");
$delimiter = ';';
$buffer = '';
$count = 0;
foreach (preg_split('/\R/', $sql) as $line) {
    if (preg_match('/^\s*DELIMITER\s+(\S+)\s*$/i', $line, $m)) { $delimiter = $m[1]; continue; }
    if ($buffer === '' && preg_match('/^\s*(--.*)?$/', $line)) continue;
    $buffer .= $line . "\n";
    if (str_ends_with(rtrim($line), $delimiter)) {
        $stmt = trim(substr(rtrim($buffer), 0, -strlen($delimiter)));
        $buffer = '';
        if ($stmt === '') continue;
        try {
            $pdo->exec($stmt);
            $count++;
        } catch (PDOException $e) {
            if (stripos($stmt, 'event_scheduler') !== false) { out('      (could not turn on the event scheduler; daily codes still expire via cron)'); continue; }
            fail("SQL failed: {$e->getMessage()}\n    in: " . substr(preg_replace('/\s+/', ' ', $stmt), 0, 140));
        }
    }
}
// The app may connect over TCP as 127.0.0.1 (Windows, Docker), so allow that host too.
$pdo->exec("CREATE USER IF NOT EXISTS 'coop_app'@'127.0.0.1' IDENTIFIED BY 'ChangeThisStrongPassword!'");
$pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON coop_db.* TO 'coop_app'@'127.0.0.1'");
$pdo->exec('FLUSH PRIVILEGES');
out("      $count statements OK");

out('  [2/4] Writing .env ...');
if (!is_file("$base/.env")) {
    copy("$base/.env.example", "$base/.env");
    $env = (string) file_get_contents("$base/.env");
    $env = preg_replace('/^DB_HOST=.*$/m', "DB_HOST=$host", $env);
    $env = preg_replace('/^DB_PORT=.*$/m', "DB_PORT=$port", $env);
    file_put_contents("$base/.env", $env);
    out('      created .env');
} else {
    out('      .env already exists (kept)');
}

out('  [3/4] Preparing storage folders...');
foreach (['uploads/cv', 'uploads/reports', 'uploads/avatars', 'logs', 'backups'] as $d) {
    if (!is_dir("$base/storage/$d")) mkdir("$base/storage/$d", 0775, true);
}

if (isset($opts['no-demo'])) {
    out('  [4/4] Skipped demo data. Create the first admin with:');
    out('        php database/create_admin.php "Your Name" you@jic.edu.sa "YourPass123"');
} else {
    out('  [4/4] Loading demo data...');
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg("$base/database/seed.php"), $code);
    if ($code !== 0) fail('Loading the demo data failed (see above).');
}
out('');
out('  Done. Start the site:  php -S 127.0.0.1:8080 -t public public/index.php   then open http://127.0.0.1:8080');
out('  In VS Code: Terminal > Run Task > "COOP: Start website", or press F5.');
out('');
