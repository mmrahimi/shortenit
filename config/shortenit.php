<?php

return [
    'short_url_length' => (int) env('SHORT_URL_LENGTH', 6),
    'short_url_max_length' => (int) env('SHORT_URL_MAX_LENGTH', 8),
    'links_per_page' => (int) env('LINKS_PER_PAGE', 20),
];
