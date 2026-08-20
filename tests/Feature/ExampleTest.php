<?php

test('the application health endpoint returns a successful response', function () {
    $this->get('/up')->assertSuccessful();
});
