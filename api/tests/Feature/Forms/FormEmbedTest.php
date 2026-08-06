<?php

use App\Models\Forms\Form;

it('creates form with inline embed type by default', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    expect($form->embed_type)->toBeNull();
});

it('stores and retrieves embed_type and embed_settings', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'embed_type' => 'popup',
        'embed_settings' => [
            'position' => 'right',
            'color' => '#FF0000',
            'width' => 500,
            'height' => 600,
            'icon' => '📋',
            'title' => 'Contact us',
        ],
    ]);

    expect($form->embed_type)->toBe('popup');
    expect($form->embed_settings)->toBe([
        'position' => 'right',
        'color' => '#FF0000',
        'width' => 500,
        'height' => 600,
        'icon' => '📋',
        'title' => 'Contact us',
    ]);
});

it('can store slide-in embed type', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'embed_type' => 'slide-in',
        'embed_settings' => [
            'position' => 'left',
            'color' => '#00FF00',
            'width' => 400,
            'height' => 500,
            'icon' => '📧',
            'title' => 'Get in touch',
        ],
    ]);

    expect($form->embed_type)->toBe('slide-in');
});

it('can store bubble embed type', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'embed_type' => 'bubble',
        'embed_settings' => [
            'position' => 'right',
            'color' => '#0000FF',
            'width' => 380,
            'height' => 500,
            'icon' => '💬',
            'title' => '',
        ],
    ]);

    expect($form->embed_type)->toBe('bubble');
});

it('exposes embed_type in form resource for owner', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);

    $form = $this->createForm($user, $workspace, [
        'embed_type' => 'popup',
        'embed_settings' => [
            'position' => 'right',
            'color' => '#3B82F6',
            'width' => 500,
        ],
    ]);

    $response = $this->actingAs($user)
        ->getJson(route('open.forms.show', [$workspace->id, $form->id]))
        ->assertSuccessful();

    $response->assertJson([
        'embed_type' => 'popup',
    ]);
    expect($response->json('embed_settings'))->toBe([
        'position' => 'right',
        'color' => '#3B82F6',
        'width' => 500,
    ]);
});

it('validates embed_type must be a valid type', function () {
    $user = $this->actingAsUser();
    $workspace = $this->createUserWorkspace($user);
    $form = $this->createForm($user, $workspace);

    $this->actingAs($user)
        ->putJson(route('open.forms.update', [$form->id]), [
            'title' => $form->title,
            'embed_type' => 'invalid-type',
            'language' => 'en',
            'theme' => 'default',
            'presentation_style' => 'classic',
            'width' => 'centered',
            'size' => 'md',
            'border_radius' => 'small',
            'dark_mode' => 'auto',
            'color' => '#000000',
            'uppercase_labels' => false,
            'no_branding' => false,
            'transparent_background' => false,
            'visibility' => 'public',
            'properties' => [],
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['embed_type']);
});
