<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExamTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/exams')->assertRedirect('/login');
    }

    public function test_admin_can_create_an_exam(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/exams', [
            'title' => 'Examen de prueba',
            'description' => 'Descripción',
            'duration_minutes' => 25,
            'questions_to_show' => 5,
            'passing_score' => 3,
            'is_active' => '1',
        ]);

        $exam = Exam::first();

        $this->assertNotNull($exam);
        $this->assertSame('Examen de prueba', $exam->title);
        $this->assertTrue($exam->is_active);
        $response->assertRedirect(route('exams.edit', $exam));
    }

    public function test_admin_can_add_a_question_with_options(): void
    {
        $user = User::factory()->create();
        $exam = Exam::create([
            'title' => 'Examen',
            'duration_minutes' => 25,
            'questions_to_show' => 5,
            'passing_score' => 3,
        ]);

        $response = $this->actingAs($user)->post("/exams/{$exam->uuid}/questions", [
            'text' => '¿Cuánto es 2 + 2?',
            'options' => [
                ['text' => '3'],
                ['text' => '4'],
                ['text' => '5'],
            ],
            'correct' => 1,
        ]);

        $response->assertRedirect(route('exams.edit', $exam));

        $question = Question::with('options')->first();
        $this->assertNotNull($question);
        $this->assertCount(3, $question->options);
        $this->assertTrue($question->options->firstWhere('is_correct', true)->text === '4');
    }
}
