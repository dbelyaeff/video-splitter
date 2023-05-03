# Video Splitter

This app gives user simple command line interface to split video files by timecodes (Youtube-like).

## Installation

1. Clone the repository locally.
2. Build app with command `./video-splitter app:build`.
3. Copy binary from `./builds/video-splitter` to `/usr/local/bin/video-splitter` (with `sudo`).
4. Set permission to the executable file if needed.

## Usage

Once installed, it can be used anythere.

```bash
video-splitter split videofile [timecodes] [suffix]
```

* `videofile` (required) is a path to the source video file (just filename if cwd or absolute path)
* `timecodes` (optional) is a string with timecodes or path to timecodes file (default: *timecodes.txt* in `cwd`)
* `suffix` (optional) is a output chapters filename suffix (before the extension). Can be set by environmental variable `VIDEO_SPLITTER_DEFAULT_FILENAME_SUFFIX` in your shell config file (like `~./zshrc`)

## Timecodes

String or file must looks like
```
00:00 First chapter
05:32 Second chapter
...
NN:NN N-chapter
```
