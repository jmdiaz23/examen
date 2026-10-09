<?php

namespace Tests\Feature;

use App\Models\Attempt;
use App\Models\Exam;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicExamTest extends TestCase
{
    use RefreshDatabase;

    private function makeExam(int $questionCount = 10, int $toShow = 5): Exam
    {
        $exam = Exam::create([
            'title' => 'Examen público',
            'duration_minutes' => 25,
            'questions_to_show' => $toShow,
            'passing_score' => 3,
            'shuffle_questions' => false,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= $questionCount; $i++) {
            $question = $exam->questions()->create(['text' => "Pregunta {$i}", 'position' => $i]);
            $question->options()->createMany([
                ['text' => 'correcta', 'is_correct' => true, 'position' => 0],
                ['text' => 'incorrecta', 'is_correct' => false, 'position' => 1],
            ]);
        }

        return $exam;
    }

    private function startAttempt(Exam $exam): Attempt
    {
        $this->post("/examen/{$exam->uuid}/iniciar", [
            'full_name' => 'Ana Pérez',
            'semester' => '5',
            'email' => 'ana@example.com',
        ])->assertRedirect();

        return Attempt::firstOrFail();
    }

    public function test_student_can_see_exam_page(): void
    {
        $exam = $this->makeExam();

        $this->get("/examen/{$exam->uuid}")
            ->assertOk()
            ->assertSee($exam->title);
    }

    public function test_starting_creates_attempt_with_random_subset(): void
    {
        $exam = $this->makeExam(10, 5);

        $attempt = $this->startAttempt($exam);

        $this->assertSame($exam->id, $attempt->exam_id);
        $this->assertCount(5, $attempt->question_ids);
        $this->assertSame('started', $attempt->status);
        $this->assertNotNull($attempt->started_at);
        $this->assertNotNull($attempt->expires_at);
    }

    public function test_answering_and_submitting_grades_the_attempt(): void
    {
        $exam = $this->makeExam(10, 5);
        $attempt = $this->startAttempt($exam);

        $questions = $exam->questions()->with('options')->whereIn('id', $attempt->question_ids)->get();

        foreach ($questions as $question) {
            $correct = $question->options->firstWhere('is_correct', true);

            $this->postJson("/examen/{$exam->uuid}/intento/{$attempt->uuid}/responder", [
                'question_id' => $question->id,
                'option_id' => $correct->id,
            ])->assertOk();
        }

        $this->post("/examen/{$exam->uuid}/intento/{$attempt->uuid}/enviar")
            ->assertRedirect(route('public.exam.result', [$exam, $attempt]));

        $attempt->refresh();
        $this->assertSame(5, $attempt->score);
        $this->assertSame('submitted', $attempt->status);
        $this->assertTrue($attempt->passed());
    }

    public function test_same_email_cannot_take_the_exam_twice(): void
    {
        $exam = $this->makeExam();
        $this->startAttempt($exam);

        $this->from("/examen/{$exam->uuid}")
            ->post("/examen/{$exam->uuid}/iniciar", [
                'full_name' => 'Ana Pérez',
                'semester' => '5',
                'email' => 'ana@example.com',
            ])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, Attempt::count());
    }

    public function test_inactive_exam_cannot_be_started(): void
    {
        $exam = $this->makeExam();
        $exam->update(['is_active' => false]);

        $this->post("/examen/{$exam->uuid}/iniciar", [
            'full_name' => 'Ana',
            'semester' => '1',
            'email' => 'ana@example.com',
        ])->assertForbidden();
    }

    public function test_expired_attempt_is_graded_automatically(): void
    {
        $exam = $this->makeExam();
        $attempt = $this->startAttempt($exam);

        $attempt->update(['expires_at' => now()->subMinute()]);

        $this->get("/examen/{$exam->uuid}/intento/{$attempt->uuid}")
            ->assertRedirect(route('public.exam.result', [$exam, $attempt]));

        $attempt->refresh();
        $this->assertSame('expired', $attempt->status);
        $this->assertSame(0, $attempt->score);
    }
}
