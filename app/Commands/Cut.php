<?php

namespace App\Commands;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use App\Services\Time;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\text;
use function Laravel\Prompts\suggest;

class Cut extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'cut
    {video? : The source video (required)}
    {from?}
    {to?}
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
        $video_file = $this->argument('video') ?? suggest('Choose video:',fn($value) => array_filter(glob("*.mp4"),fn($file)=>str_starts_with($file,$value)));
        abort_unless(file_exists($video_file),404,"Video file not found.");
        $from = $this->argument('from') ?? text(
            label: 'From:',
            placeholder: '00:00',
            default: '00:00',
            required: true,
            hint: 'Timecode to cut from.',
            validate: fn(string $value) => preg_match("#^\d{1,2}\:\d{1,2}$#",$value) === 1 ? null : 'Timecode is incorrect'
        );
        $to = $this->argument('to') ?? text(
            label: 'To:',
            placeholder: '00:00',
            default: '00:00',
            required: true,
            hint: 'Timecode to cut to.',
            validate: fn(string $value) => match(true){
                $from == $value => 'Must be greater that From',
                default => preg_match("#^\d{1,2}\:\d{1,2}$#",$value) === 1 ? null : 'Timecode is incorrect'
            }
        );

        $output = text(
            label: 'Output filename',
            required: true,
            default: $this->argument('output') ?: pathinfo($video_file,PATHINFO_FILENAME)." ({$from}-{$to}).mp4",
            hint: 'Give a name to the output file.'
        );
        $ffmpeg = FFMpeg::create();
        $video = $ffmpeg->open($video_file);
        $this->info("Cutting video ".basename($video_file)." from {$from} to {$to}…");
        $clip = $video->clip(TimeCode::fromSeconds(Time::toSeconds($from)),
            TimeCode::fromSeconds(Time::toSeconds($to) - Time::toSeconds($from)));
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
