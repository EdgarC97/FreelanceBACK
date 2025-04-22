<?php

return [
    "secret_key" => "clave_super_segura123456",
    "issuer" => "localhost",
    "audience" => "localhost",
    "issued_at" => time(),
    "expiration_time" => time() + (60 * 60 * 24) // 24 horas
];
