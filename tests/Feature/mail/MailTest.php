<?php

use App\Mail\CreationContactFormMail;
use App\Mail\EventContactFormMail;
use Illuminate\Support\Facades\Mail;

it('sends creation contact form email', function () {
    Mail::fake();

    $formData = [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'john.doe@example.com',
        'phone' => '+33123456789',
        'message' => 'Je souhaiterais discuter d\'un projet de création florale pour mon mariage.',
        'additional_info' => '', // Honeypot field
    ];

    $response = $this->postJson('/api/contact/creation', $formData);
    // dd($response);
    $response->assertStatus(200);

    Mail::assertSent(CreationContactFormMail::class, function ($mail) use ($formData) {
        return $mail->contactData['email'] === $formData['email'] &&
               $mail->contactData['firstName'] === $formData['firstName'];
    });
});

it('sends event contact form email', function () {
    Mail::fake();

    $formData = [
        'email' => 'marie.martin@example.com',
        'firstName' => 'Marie',
        'lastName' => 'Martin',
        'phone' => '+33987654321',
        'eventDate' => '2024-06-15',
        'eventLocation' => 'Château de Versailles',
        'themeColors' => 'Rose et blanc',
        'message' => 'Je souhaiterais organiser un événement avec des arrangements floraux.',
        'additional_info' => '', // Honeypot field
    ];

    $response = $this->postJson('/api/contact/event', $formData);
    $response->assertStatus(200);

    Mail::assertSent(EventContactFormMail::class, function ($mail) use ($formData) {
        return $mail->contactData['email'] === $formData['email'] &&
               $mail->contactData['firstName'] === $formData['firstName'];
    });
});

it('rejects spam with honeypot field filled for creation form', function () {
    Mail::fake();

    $formData = [
        'firstName' => 'Spam',
        'lastName' => 'Bot',
        'email' => 'spam@bot.com',
        'phone' => '+33000000000',
        'message' => 'This is spam',
        'additional_info' => 'dsqdsqds', // Honeypot field
    ];

    $response = $this->postJson('/api/contact/creation', $formData);
    $response->assertStatus(422);
    $response->assertJson(['message' => 'Spam detected']);

    Mail::assertNotSent(CreationContactFormMail::class);
});

it('rejects spam with honeypot field filled for event form', function () {
    Mail::fake();

    $formData = [
        'email' => 'spam@bot.com',
        'firstName' => 'Spom',
        'lastName' => 'Spoum',
        'phone' => '+3398707878',
        'eventDate' => '2024-06-15',
        'eventLocation' => 'Château de Versailles',
        'themeColors' => 'Rose et blanc',
        'message' => 'Je souhaiterais organiser un événement avec des arrangements floraux.',
        'additional_info' => 'fdsfdsfds', // Honeypot field
    ];

    $response = $this->postJson('/api/contact/event', $formData);
    $response->assertStatus(422);
    $response->assertJson(['message' => 'Spam detected']);

    Mail::assertNotSent(CreationContactFormMail::class);
});

it('rejects invalid email format for creation form', function () {
    Mail::fake();
    $formData = [
        'firstName' => 'John',
        'lastName' => 'Doe',
        'email' => 'invalid-email',
        'phone' => '+33123456789',
        'message' => 'Je souhaiterais discuter d\'un projet de création florale pour mon mariage.',
        'additional_info' => '', // Honeypot field
    ];

    $response = $this->postJson('/api/contact/creation', $formData);
    $response->assertStatus(422);
    $response->assertJson(['message' => 'Validation failed']);
    $response->assertJsonValidationErrors(['email']);
});
