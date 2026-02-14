<?php

use App\Models\Page;
use App\Models\Category;

it('has many categories', function () {
    $page = Page::factory()->create();
    Category::factory()->count(3)->create(['page_id' => $page->id]);
    expect($page->categories)->toHaveCount(3);
});

it('uses soft deletes', function () {
    $page = Page::factory()->create();
    $page->delete();
    $this->assertSoftDeleted('pages', ['id' => $page->id]);
});
