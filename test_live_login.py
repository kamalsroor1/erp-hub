import paramiko

HOST = '145.79.20.98'
PORT = 65002
USER = 'u910151740'
PASS = 'Ks@Rr12699'
TARGET = '/home/u910151740/domains/sroor.baraa-solutions.com/public_html'
PHP84 = '/opt/alt/php84/usr/bin/php'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(HOST, port=PORT, username=USER, password=PASS, timeout=30)

php_script = """<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);

foreach (['/login', '/items', '/profile', '/users', '/'] as $path) {
    $request = Illuminate\\Http\\Request::create($path, 'GET');
    $response = $kernel->handle($request);
    echo "PATH: {$path} | STATUS: " . $response->getStatusCode() . " | LEN: " . strlen($response->getContent()) . "\\n";
}
"""

sftp = ssh.open_sftp()
with sftp.file(f'{TARGET}/test_routes.php', 'w') as f:
    f.write(php_script)
sftp.close()

stdin, stdout, stderr = ssh.exec_command(f'{PHP84} {TARGET}/test_routes.php')
print('=== DIRECT LARAVEL ROUTE OUTPUT ===')
print(stdout.read().decode('utf-8'))
print('=== STDERR ===')
print(stderr.read().decode('utf-8'))
ssh.close()
