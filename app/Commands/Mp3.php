<?php

namespace App\Commands;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Midweste\SimpleLogger\EchoLogger;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Mp3 extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'mp3 {video} {output?}
    {--title=}
    {--description=}
    {--artist=}
    {--album=}
    {--year=}
    {--artwork=}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Converts video to mp3';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        abort_unless(file_exists($this->argument('video')),404,"Video file not found.");
        $ffmpeg = FFMpeg::create();//[],new EchoLogger());
        $output = $this->argument('output') ?: pathinfo($this->argument('video'), PATHINFO_FILENAME).'.mp3';
        $video = $ffmpeg->open($this->argument('video'));
        $audio_format = new \FFMpeg\Format\Audio\Mp3();
        $this->info("Converting video ".basename($this->argument('video'))." to mp3 {$output}…");
        $progressBar = $this->output->createProgressBar(100);
        $progressBar->start();
        $audio_format->on('progress', function ($video, $format, $percentage)  use($progressBar) {
            $progressBar->setProgress($percentage);
        });
        $progressBar->setProgress(100);
        $progressBar->finish();
        $meta = [
            "artist" => $this->option('artist') ?: env('VIDEO_SPLITTER_MP3_DEFAULT_ARTIST',''),
            "year" => $this->option('year') ?: date('Y'),
            "title" => $this->option('title') ?: null,
            "description" => $this->option('description') ?: null,
            "album" => $this->option('album') ?: null,
        ];
        if($this->option('artwork')){
            $meta['artwork'] = $this->option('artwork');
        }
        $video->filters()->addMetadata($meta);

        $video->save($audio_format, $output);
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
