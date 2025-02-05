<?php

function is_development(): bool
{
    return ENVIRONMENT === 'development';
}