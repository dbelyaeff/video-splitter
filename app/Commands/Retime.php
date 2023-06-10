<?php

namespace App\Commands;

use App\Services\Time;
use IntlDateFormatter;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Retime extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'retime {timecodes}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Recalcuates time for timecode from 00:00';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $timecodes = $this->argument('timecodes');
        $chapters = $this->parseTimecodes($timecodes);
        $this->printChapters($chapters);
    }
    /**
     * Echoes chapters with corrected time
     *
     * @param [type] $chapters
     * @return void
     */
    public function printChapters($chapters){
        foreach($chapters as $chapter){
            $secs = $chapter['start'] % 60;
            $hrs = $chapter['start'] / 3600;
            $mins = $hrs / 60;
            echo gmdate($chapters[count($chapters)-1]['start'] > 3600 ? "H:i:s" : "i:s",$chapter['start'])." ".$chapter['title']."\n";
        }
    }
    /**
     * Parses raw timecodes text and returns chapters
     *
     * @param [type] $timecodes
     * @return void
     */
    public function parseTimecodes($timecodes): array
    {
        preg_match_all("#^\d{1,2}\:\d{2}[^\n]+$#imsU", $timecodes, $lines);
        abort_unless(count($lines[0]), 500, 'No timecodes has been found');
        $chapters = [];
        $fakeStartTime = null;
        for ($i = 0; $i < count($lines[0]); $i++) {
            preg_match("#(?<timecode>(?:\d{1,2}\:)?\d{1,2}\:\d{1,2})\s+(?<title>.+)#i", $lines[0][$i], $matches);
            if(!$fakeStartTime){
                $fakeStartTime = Time::toSeconds($matches['timecode']);
            }
            $chapters[$i] = [
                'start' =>  Time::toSeconds($matches['timecode']) - $fakeStartTime,
                'title' => $matches['title']
            ];
        }
        $this->info("Found " . count($chapters) . " chapters.");
        return $chapters;
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
