<?php

namespace App\Commands;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use App\Services\Time;
use Psr\Log\LoggerInterface;
use FFMpeg\Format\Video\X264;
use FFMpeg\Coordinate\TimeCode;
use Midweste\SimpleLogger\EchoLogger;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;
class Split extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'split
    {video? : The video to split (required)}
    {timecodes? : Timecodes either as string or in given file (required) }
    {--suffix=}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Splits video by given chapters';

    protected string $suffix;
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $video = $this->argument('video') ?? suggest('Choose video:',fn($value) => array_filter(glob("*.mp4"),fn($file)=>str_starts_with($file,$value)));
        abort_unless(file_exists($video), 404, 'File doesn\'t exists.');
        $timecodes = $this->argument('timecodes') ?? textarea(
            label: 'Timecodes',
            hint: "Provide a list of timecodes in format:\n
            00:00 Chapter 1
            NN:NN Chapter N",
            required: true
        );
        $this->suffix = $this->option('suffix') ?? text(
            label: 'Video suffix',
            default: env('VIDEO_SPLITTER_DEFAULT_FILENAME_SUFFIX',''),
            hint: 'Text after filename and before extension.'
        );
        $chapters = $this->parseTimecodes($timecodes);
        $this->splitVideoByChapters($video, $chapters);
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
        for ($i = 0; $i < count($lines[0]); $i++) {
            preg_match("#(?<timecode>(?:\d{1,2}\:)?\d{1,2}\:\d{1,2})\s+(?<title>.+)#i", $lines[0][$i], $matches);
            $chapters[$i] = [
                'from' => Time::toSeconds($matches['timecode']),
                'to' => '',
                'title' => $matches['title']
            ];
            if ($i > 0) {
                $chapters[$i - 1]["to"] = Time::toSeconds($matches['timecode']);
            }
        }
        $this->info("Found " . count($chapters) . " chapters.");
        return $chapters;
    }

    /**
     * Splitting video by chapters
     *
     * @param [type] $video
     * @param [type] $chapter
     * @return void
     */
    public function splitVideoByChapters($video, $chapters)
    {
        // $logger = new \Midweste\SimpleLogger\EchoLogger;
        $ffmpeg = FFMpeg::create([]);
        $ffprobe = FFProbe::create();
        $duration = $ffprobe->format($video)->get('duration');

        $this->info("Preparing to split video by chapters…");

        $video = $ffmpeg->open($video);
        $i = 1;
        foreach($chapters as $chapter){
            if('' == $chapter['to']){
                $chapter['to'] = $duration;
            }
            $clip = $video->clip(TimeCode::fromSeconds($chapter['from']),TimeCode::fromSeconds($chapter['to']-$chapter['from']));
            $filename = "{$i}. {$chapter['title']}{$this->suffix}.mp4";
            $this->info("Writing \"{$filename}\"");
            $format = new X264('copy','copy');
            $clip->save($format,$filename);
            $i++;
        }
    }
}
