<?php

test('root endpoint redirects to admin dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin');
});
