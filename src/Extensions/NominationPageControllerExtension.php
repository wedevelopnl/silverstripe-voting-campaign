<?php

namespace TheWebmen\VotingCampaign\Extensions;

use SilverStripe\Core\Extension;
use TheWebmen\VotingCampaign\Forms\NominateForm;

class NominationPageControllerExtension extends Extension {
    private static $allowed_actions = [
        'NominateForm'
    ];

    public function NominateForm()
    {
        return new NominateForm($this->owner);
    }
}
