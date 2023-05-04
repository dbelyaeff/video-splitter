<?php

namespace App\Commands;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use App\Services\Time;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Frame extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'frame {video} {timecode} {output?}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Makes screenshots at given timecode';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        abort_unless(file_exists($this->argument('video')),404,"Video file not found.");
        $ffmpeg = FFMpeg::create();
        $ffprobe = FFProbe::create();
        $output = $this->argument('output') ?: time().'.jpg';
        $video = $ffmpeg->open($this->argument('video'));
        $this->info("Taking frame from video ".basename($this->argument('video'))." from {$this->argument('timecode')}…");
        $video->frame(TimeCode::fromSeconds(Time::toSeconds($this->argument('timecode'))))
              ->save($output);
    }
}
