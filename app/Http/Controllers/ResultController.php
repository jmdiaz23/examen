<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Exam;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Exam $exam): View
    {
        $attempts = $exam->attempts()
            ->whereIn('status', ['submitted', 'expired'])
            ->orderByDesc('submitted_at')
            ->get();

        $finished = $attempts->whereNotNull('score');

        $stats = [
            'total' => $attempts->count(),
            'average' => $finished->count() ? round($finished->avg('score'), 1) : 0,
            'max' => $finished->max('score') ?? 0,
            'min' => $finished->min('score') ?? 0,
            'passed' => $finished->filter(fn (Attempt $a) => $a->score >= $exam->passing_score)->count(),
        ];

        return view('results.index', compact('exam', 'attempts', 'stats'));
    }

    public function show(Exam $exam, Attempt $attempt): View
    {
        abort_unless($attempt->exam_id === $exam->id, 404);

        $attempt->load(['answers.question.options', 'answers.option']);

        return view('results.show', compact('exam', 'attempt'));
    }
}
