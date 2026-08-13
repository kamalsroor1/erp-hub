import sys
import paramiko

sys.stdout.reconfigure(encoding='utf-8', errors='replace')

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect('145.79.20.98', port=65002, username='u910151740', password='Ks@Rr12699')

php_script = r"""<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admin = App\Models\User::first();
app(App\Services\ActivityLogService::class)->log(
    module: 'system',
    action: 'updated',
    description: 'اختبار توقيت السجل بعد ضبط التوقيت المحلي للقاهرة',
    userId: $admin ? $admin->id : 1
);
$latest = App\Models\ActivityLog::latest()->first();
echo "Latest Activity ID: " . $latest->id . PHP_EOL;
echo "Time: " . $latest->created_at->format('Y-m-d h:i:s A (T)') . PHP_EOL;
echo "Diff: " . $latest->created_at->locale('ar')->diffForHumans() . PHP_EOL;
"""

sftp = ssh.open_sftp()
with sftp.file('/home/u910151740/domains/sroor.baraa-solutions.com/public_html/test_time_run.php', 'w') as f:
    f.write(php_script)
sftp.close()

stdin, stdout, stderr = ssh.exec_command('cd /home/u910151740/domains/sroor.baraa-solutions.com/public_html && php test_time_run.php')
print(stdout.read().decode('utf-8', errors='replace'))
ssh.close()
