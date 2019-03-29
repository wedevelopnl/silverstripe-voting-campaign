<?php

namespace TheWebmen\VotingCampaign\Forms;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Validator;
use TheWebmen\VotingCampaign\Models\Nomination;
use TheWebmen\VotingCampaign\Models\Vote;
use TheWebmen\VotingCampaign\Models\VotingCampaign;

class VotingForm extends Form
{
    public function __construct(
        RequestHandler $controller = null,
        $name = 'VotingForm'
    ) {
        $nominations = $controller->Campaign()->Nominations()->filter('Status', Nomination::STATUS_APPROVED);
        $fields = FieldList::create([
            EmailField::create('Email', _t(__CLASS__ . '.EMAIL_ADDRESS', 'Email address')),
            OptionsetField::create('Choice', _t(__CLASS__ . '.CHOICE', 'Choice'), $nominations->map())
        ]);

        $actions = FieldList::create([
            FormAction::create('handle', _t(__CLASS__ . '.VOTE', 'Vote'))
        ]);

        $validator = new RequiredFields([
            'Email',
            'Choice'
        ]);

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    public function handle(array $data, Form $form)
    {
        /** @var VotingCampaign $campaign */
        $campaign = $this->controller->Campaign();

        $voteCheck = $campaign->Votes()->filter('Email', $data['Email'])->first();
        if ($voteCheck) {
            $this->sessionMessage(_t(__CLASS__ . '.EMAIL_ALREADY_VOTED', 'This email address is already used for voting'), 'bad');
            return $this->controller->redirectBack();
        }

        if ($campaign->dbObject('VotingStartDateTime')->InFuture()) {
            $this->sessionMessage(_t(__CLASS__ . '.VOTING_NOT_OPEN', 'Voting is not yet open'), 'bad');
            return $this->controller->redirectBack();
        }

        if ($campaign->dbObject('VotingClosingDateTime')->InPast()) {
            $this->sessionMessage(_t(__CLASS__ . '.VOTING_CLOSED', 'Voting is closed'), 'bad');
            return $this->controller->redirectBack();
        }

        $vote = new Vote([
            'CampaignID' => $campaign->ID,
            'NominationID' => $data['Choice'],
            'Email' => $data['Email'],
            'Status' => Vote::STATUS_UNCONFIRMED
        ]);
        $vote->write();
        $vote->sendConfirmEmail($campaign->ConfirmEmailSender, $campaign->ConfirmEmailSubject);

        $this->sessionMessage(_t(__CLASS__ . '.VOTE_SUCCESSFUL', 'An email is send to your email address to confirm your vote'), 'good');
        return $this->controller->redirectBack();
    }
}
