<?php

use App\Mail\CreationContactFormMail;
use Illuminate\Support\Facades\Mail;

it('sends creation contact email with valid data', function () {
    Mail::fake();

    $this->postJson('/api/contact/creation', [
        'firstName' => 'Marie',
        'lastName' => 'Dupont',
        'email' => 'marie@example.com',
        'phone' => '+33612345678',
        'message' => 'Je souhaite une création florale.',
        'additional_info' => '',
    ])->assertOk()
        ->assertJson(['message' => 'Formulaire envoyé avec succès']);

    Mail::assertSent(CreationContactFormMail::class, function ($mail) {
        return $mail->contactData['email'] === 'marie@example.com';
    });
});

it('rejects missing required fields', function () {
    Mail::fake();

    $this->postJson('/api/contact/creation', [])
        ->assertUnprocessable()
        ->assertJson(['message' => 'Validation failed'])
        ->assertJsonValidationErrors(['firstName', 'lastName', 'email', 'phone', 'message']);

    Mail::assertNothingSent();
});

it('rejects invalid email format', function () {
    Mail::fake();

    $this->postJson('/api/contact/creation', [
        'firstName' => 'Marie',
        'lastName' => 'Dupont',
        'email' => 'not-an-email',
        'phone' => '+33612345678',
        'message' => 'Test message',
        'additional_info' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    Mail::assertNothingSent();
});

it('rejects spam via honeypot', function () {
    Mail::fake();

    $this->postJson('/api/contact/creation', [
        'firstName' => 'Bot',
        'lastName' => 'Spam',
        'email' => 'bot@spam.com',
        'phone' => '+33600000000',
        'message' => 'Spam content',
        'additional_info' => 'filled-honeypot',
    ])->assertUnprocessable()
        ->assertJson(['message' => 'Spam detected']);

    Mail::assertNothingSent();
});
