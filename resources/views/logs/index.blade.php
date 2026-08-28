@extends('layouts.app')

@section('title', 'Logs')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0 h3">System Logs</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex flex-wrap gap-2 align-items-center">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center mb-0">
                    <label class="mb-0">File</label>
                    <select name="file" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($files as $f)
                            <option value="{{ $f }}" @if($f === $current) selected @endif>{{ $f }}</option>
                        @endforeach
                    </select>

                    <label class="mb-0 ms-2">Level</label>
                    <select name="level" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        @foreach($levels as $l)
                            <option value="{{ $l }}" @if($l === $activeLevel) selected @endif>{{ ucfirst($l) }}</option>
                        @endforeach
                    </select>
                </form>
                @if($current)
                <a href="?file={{ urlencode($current) }}&dl={{ urlencode($current) }}" class="btn btn-sm btn-outline-secondary ms-auto">Download</a>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 70vh; overflow:auto">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="sticky-top bg-white">
                            <tr>
                                <th style="width:90px">Level</th>
                                <th style="width:170px">Date</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                @php
                                    $levelMap = [
                                        'error' => 'danger', 'critical' => 'danger',
                                        'alert' => 'danger', 'emergency' => 'danger',
                                        'warning' => 'warning', 'info' => 'info',
                                        'debug' => 'secondary', 'notice' => 'secondary',
                                    ];
                                    $cls = $levelMap[$log['level'] ?? 'info'] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td><span class="badge text-bg-{{ $cls }}">{{ $log['level'] ?? '—' }}</span></td>
                                    <td class="text-nowrap text-muted small">{{ $log['date'] ?? '' }}</td>
                                    <td style="white-space:pre-wrap;word-break:break-word">
                                        {{ $log['text'] ?? '' }}
                                        @if(!empty($log['in_file']))
                                            <div class="text-muted small mt-1">{{ $log['in_file'] }}</div>
                                        @endif
                                        @if(!empty($log['stack']))
                                            <details class="mt-1">
                                                <summary class="small text-primary" style="cursor:pointer">Stack trace</summary>
                                                <pre class="small bg-light p-2 mt-1" style="white-space:pre-wrap">{{ trim($log['stack']) }}</pre>
                                            </details>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No log entries.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
