<?php

declare(strict_types=1);

namespace App\Configuration\Transport\AutoMapper\Configuration;

use App\General\Transport\AutoMapper\RestRequestMapper;

/**
 * @package App\Configuration
 */
class RequestMapper extends RestRequestMapper
{
    /**
     * @var array<int, non-empty-string>
     */
    protected static array $properties = [
        'title',
        'description',
        'userId',
        'photo',
        'birthday',
        'gender',
        'googleId',
        'githubId',
        'githubUrl',
        'instagramUrl',
        'linkedInId',
        'linkedInUrl',
        'twitterUrl',
        'facebookUrl',
        'phone'
    ];
}
