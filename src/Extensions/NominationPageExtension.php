<?php

namespace TheWebmen\VotingCampaign\Extensions;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use TheWebmen\VotingCampaign\Forms\NominateForm;
use TheWebmen\VotingCampaign\Models\VotingCampaign;

class NominationPageExtension extends DataExtension {
    private static $has_one = [
        'Campaign' => VotingCampaign::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', DropdownField::create('CampaignID', 'Campaign', VotingCampaign::get()->map()), 'Content');
    }
}
