<?php

return [
    App\Providers\AppServiceProvider::class,

    // ↓ Añade esta línea ↓
    App\Providers\RepositoryServiceProvider::class,
];
