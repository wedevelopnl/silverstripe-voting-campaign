<?php

namespace TheWebmen\VotingCampaign\Extensions;

use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\View\ArrayData;
use TheWebmen\VotingCampaign\Forms\NominateForm;
use TheWebmen\VotingCampaign\Models\Nomination;
use TheWebmen\VotingCampaign\Models\Vote;
use TheWebmen\VotingCampaign\Models\VotingCampaign;

class ResultsPageExtension extends DataExtension {
    private static $has_one = [
        'Campaign' => VotingCampaign::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', DropdownField::create('CampaignID', 'Campaign', VotingCampaign::get()->map()), 'Content');
    }

    public function VotingResults()
    {
        /** @var VotingCampaign $campaign */
        $campaign = $this->owner->Campaign();
        $nominations = $campaign->Nominations()->filter('Status', Nomination::STATUS_APPROVED);
        $totalVotes = $this->TotalVotes();
        $out = new ArrayList();
        foreach ($nominations as $nomination) {
            $numVotes = $nomination->Votes()->filter('Status', Vote::STATUS_CONFIRMED)->Count();
            $percentage = $numVotes === 0 ? 0 : round(($numVotes / $totalVotes) * 100);
            $out->push(new ArrayData([
                'NumVotes' => $numVotes,
                'Percentage' => $percentage,
                'Nomination' => $nomination
            ]));
        }
        return $out->sort('NumVotes DESC');
    }

    public function TotalVotes()
    {
        return $this->owner->Campaign()->Votes()->filter('Status', Vote::STATUS_CONFIRMED)->Count();
    }
}
