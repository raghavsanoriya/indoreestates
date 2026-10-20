<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

#[Signature('local:serve {--port=8000 : Local preview port}')]
#[Description('Serve the local app with the current PHP configuration and correct public working directory')]
class ServeLocal extends Command
{
    public function handle(): int
    {
        $port = filter_var($this->option('port'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1024, 'max_range' => 65535]]);
        if (! $port) {
            $this->error('Choose a port between 1024 and 65535.');

            return self::FAILURE;
        }
        $arguments = [PHP_BINARY];
        if (php_ini_loaded_file()) {
            $arguments[] = '-c';
            $arguments[] = php_ini_loaded_file();
        }
        array_push($arguments, '-S', '127.0.0.1:'.$port, '-t', public_path(), base_path('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'));
        $this->info('Local preview: http://127.0.0.1:'.$port);
        $process = new Process($arguments, public_path(), null, null, null);

        return $process->run(fn (string $type, string $buffer) => $this->output->write($buffer));
    }
}
