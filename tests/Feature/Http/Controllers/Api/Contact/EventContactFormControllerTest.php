<?php

use App\Mail\EventContactFormMail;
use Illuminate\Support\Facades\Mail;

it('sends event contact email with valid data', function () {
    Mail::fake();

    $this->postJson('/api/contact/event', [
        'firstName' => 'Marie',
        'lastName' => 'Martin',
        'email' => 'marie@example.com',
        'phone' => '+33612345678',
        'eventDate' => '2025-06-15',
        'eventLocation' => 'Château de Versailles',
        'themeColors' => 'Rose et blanc',
        'message' => 'Je souhaite organiser un événement.',
        'additional_info' => '',
    ])->assertOk()
        ->assertJson(['message' => 'Formulaire envoyé avec succès']);

    Mail::assertSent(EventContactFormMail::class, function ($mail) {
        return $mail->contactData['email'] === 'marie@example.com';
    });
});

it('rejects missing required fields', function () {
    Mail::fake();

    $this->postJson('/api/contact/event', [])
        ->assertUnprocessable()
        ->assertJson(['message' => 'Validation failed'])
        ->assertJsonValidationErrors(['firstName', 'lastName', 'email', 'phone', 'eventDate', 'eventLocation', 'themeColors', 'message']);

    Mail::assertNothingSent();
});

it('rejects invalid email format', function () {
    Mail::fake();

    $this->postJson('/api/contact/event', [
        'firstName' => 'Marie',
        'lastName' => 'Martin',
        'email' => 'bad-email',
        'phone' => '+33612345678',
        'eventDate' => '2025-06-15',
        'eventLocation' => 'Paris',
        'themeColors' => 'Rose',
        'message' => 'Test',
        'additional_info' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);

    Mail::assertNothingSent();
});

it('rejects invalid date format for eventDate', function () {
    Mail::fake();

    $this->postJson('/api/contact/event', [
        'firstName' => 'Marie',
        'lastName' => 'Martin',
        'email' => 'marie@example.com',
        'phone' => '+33612345678',
        'eventDate' => 'not-a-date',
        'eventLocation' => 'Paris',
        'themeColors' => 'Rose',
        'message' => 'Test',
        'additional_info' => '',
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['eventDate']);

    Mail::assertNothingSent();
});

it('rejects spam via honeypot', function () {
    Mail::fake();

    $this->postJson('/api/contact/event', [
        'firstName' => 'Bot',
        'lastName' => 'Spam',
        'email' => 'bot@spam.com',
        'phone' => '+33600000000',
        'eventDate' => '2025-06-15',
        'eventLocation' => 'Paris',
        'themeColors' => 'Rose',
        'message' => 'Spam',
        'additional_info' => 'filled-honeypot',
    ])->assertUnprocessable()
        ->assertJson(['message' => 'Spam detected']);

    Mail::assertNothingSent();
});
