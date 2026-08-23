<?php

namespace App\Integrations\OAuth\Drivers\Notion;

use SocialiteProviders\Manager\SocialiteWasCalled;

class NotionExtendSocialite
{
    public function handle(SocialiteWasCalled $event)
    {
        $event->extendSocialite('notion', Provider::class);
    }
}
