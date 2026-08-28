<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Rap2hpoutre\LaravelLogViewer\LaravelLogViewer;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        $log = new LaravelLogViewer();

        if ($request->filled('file')) {
            $log->setFile(basename($request->file));
        }

        $logs = $log->all();
        // ponytail: trim whitespace the parser leaves on text/stack/in_file
        $logs = array_map(fn ($e) => [
            ...$e,
            'text' => isset($e['text']) ? ltrim($e['text']) : $e['text'],
            'stack' => isset($e['stack']) ? trim($e['stack']) : $e['stack'],
            'in_file' => isset($e['in_file']) ? ltrim($e['in_file']) : $e['in_file'],
        ], $logs);
        $level = $request->get('level');

        if ($level) {
            $logs = array_filter($logs, fn ($e) => ($e['level'] ?? '') === $level);
        }

        $levels = ['error', 'warning', 'info', 'debug', 'notice', 'critical', 'alert', 'emergency'];

        return view('logs.index', [
            'logs' => $logs,
            'files' => $log->getFiles(true),
            'current' => $log->getFileName(),
            'levels' => $levels,
            'activeLevel' => $level,
        ]);
    }
}
