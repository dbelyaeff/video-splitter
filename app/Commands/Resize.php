<?php

namespace App\Commands;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\Dimension;
use Midweste\SimpleLogger\EchoLogger;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Resize extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'resize {video} {resolution} {output?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Resizes video by given resolution. ';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        abort_unless(file_exists($this->argument('video')),404,"Video file not found.");
        $ffmpeg = FFMpeg::create([]);//,new EchoLogger());
        $output = $this->argument('output') ?: time().'.mp4';
        $video = $ffmpeg->open($this->argument('video'));
        abort_unless(preg_match("#\d{3}(x\d{3})?#",$this->argument('resolution')),500,"Provide correct resolution. Example: 1280x720");
        preg_match("#(?<width>\d+)(x(?<heigth>\d+))?#",$this->argument('resolution'),$matches);
        extract($matches);
        if(!isset($heigth)){
            $heigth = round($width*9/16);
            if($heigth%2 != 0){
                $heigth+=1;
            }
        }
        $this->info("Resizing video ".basename($this->argument('video'))." with resolution {$this->argument('resolution')}…");
        $video
            ->filters()
            ->resize(new Dimension(intval($width), intval($heigth)))
            ->synchronize();
        $format = new X264('copy');
        $progressBar = $this->output->createProgressBar(100);
        $progressBar->start();
        $format->on('progress', function ($video, $format, $percentage)  use($progressBar) {
            $progressBar->setProgress($percentage);
        });
        $progressBar->setProgress(100);
        $progressBar->finish();
        $video->save($format,$output);
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
