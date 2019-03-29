<?php

namespace TheWebmen\VotingCampaign\Extensions;

use SilverStripe\Core\Extension;
use TheWebmen\VotingCampaign\Forms\VotingForm;

class VotingPageControllerExtension extends Extension {
    private static $allowed_actions = [
        'VotingForm'
    ];

    public function VotingForm()
    {
        return new VotingForm($this->owner);
    }
}
