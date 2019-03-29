<?php

namespace TheWebmen\VotingCampaign\Controllers;

use SilverStripe\Control\Controller;
use TheWebmen\VotingCampaign\Models\Vote;

class ConfirmVoteController extends Controller {

    public function index()
    {
        $hash = $this->getRequest()->param('Hash');
        /** @var Vote $vote */
        $vote = Vote::get()->filter('Hash', $hash)->first();

        if (!$vote) {
            return $this->customise([
                'Type' => 'no-vote',
                'Vote' => $vote
            ])->renderWith(['TheWebmen\\VotingCampaign\\ConfirmVote', 'Page']);
        }

        if ($vote->Status === Vote::STATUS_CONFIRMED) {
            return $this->customise([
                'Type' => 'vote-already-confirmed',
                'Vote' => $vote
            ])->renderWith(['TheWebmen\\VotingCampaign\\ConfirmVote', 'Page']);
        }

        $campaign = $vote->Campaign();
        if ($campaign->dbObject('VotingClosingDateTime')->InPast()) {
            return $this->customise([
                'Type' => 'voting-closed',
                'Vote' => $vote
            ])->renderWith(['TheWebmen\\VotingCampaign\\ConfirmVote', 'Page']);
        }

        $vote->Status = Vote::STATUS_CONFIRMED;
        $vote->write();
        return $this->customise([
            'Type' => 'success',
            'Vote' => $vote
        ])->renderWith(['TheWebmen\\VotingCampaign\\ConfirmVote', 'Page']);
    }

}
