<?php

namespace App\Commands;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use App\Services\Time;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Cut extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'cut
    {video : The source video (required)}
    {from}
    {to}
    {output?}
    ';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Cuts given video by start and end timecodes';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        abort_unless(file_exists($this->argument('video')),404,"Video file not found.");
        $ffmpeg = FFMpeg::create();
        $output = $this->argument('output') ?: time().'.mp4';
        $video = $ffmpeg->open($this->argument('video'));
        $this->info("Cutting video ".basename($this->argument('video'))." from {$this->argument('from')} to {$this->argument('to')}…");
        $clip = $video->clip(TimeCode::fromSeconds(Time::toSeconds($this->argument('from'))),
            TimeCode::fromSeconds(Time::toSeconds($this->argument('to')) - Time::toSeconds($this->argument('from'))));
        $clip->save(new X264('copy','copy'),$output);
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
