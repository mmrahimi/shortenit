<?php

return [
    'short_code_length' => (int) env('SHORT_CODE_LENGTH', 6),
    'short_code_max_length' => (int) env('SHORT_CODE_MAX_LENGTH', 8),
    'links_per_page' => (int) env('LINKS_PER_PAGE', 20),
];
