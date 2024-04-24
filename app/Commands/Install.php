<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\alert;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;

class Install extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Copy bin file to /usr/local/bin';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if(!file_exists('./builds/video-splitter')){
            return alert('Build binary first!');
        }
        $install_path = text(
            label:'Where to install binary?',
            default: '/usr/local/bin/video-splitter'
        );
        if(is_dir(dirname($install_path))){
            shell_exec('sudo cp -r ./builds/video-splitter '.$install_path);
        } else {
            error('Given path is not a valid dir!');
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
