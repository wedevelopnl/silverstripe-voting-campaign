<?php

namespace TheWebmen\VotingCampaign\Forms;

use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\Validator;
use TheWebmen\VotingCampaign\Models\Nomination;
use TheWebmen\VotingCampaign\Models\Vote;
use TheWebmen\VotingCampaign\Models\VotingCampaign;
use TheWebmen\VotingCampaign\Models\VotingCode;

class VotingForm extends Form
{
    public function __construct(
        RequestHandler $controller = null,
        $name = 'VotingForm'
    ) {
        /** @var VotingCampaign $campaign */
        $campaign = $controller->Campaign();

        $nominations = $campaign->Nominations()->filter('Status', Nomination::STATUS_APPROVED);
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

        if ($campaign->CampaignUsesCodes) {
            $fields->push(new TextField('Code', _t(__CLASS__ . '.CODE', 'Code')));
            $validator->addRequiredField('Code');
        }

        parent::__construct($controller, $name, $fields, $actions, $validator);
        
        if ($code = $controller->getRequest()->getVar('code')) {
            $this->loadDataFrom([
                'Code' => $code
            ]);
        }
    }

    public function handle(array $data, Form $form)
    {
        /** @var VotingCampaign $campaign */
        $campaign = $this->controller->Campaign();

        $voteCheck = $campaign->Votes()->filter('Email', $data['Email'])->first();
        if ($voteCheck) {
            $this->sessionMessage(_t(__CLASS__ . '.EMAIL_ALREADY_VOTED', 'This email address is already used for voting'), 'bad');
            $this->setSessionData($data);
            return $this->controller->redirectBack();
        }

        if ($campaign->dbObject('VotingStartDateTime')->InFuture()) {
            $this->sessionMessage(_t(__CLASS__ . '.VOTING_NOT_OPEN', 'Voting is not yet open'), 'bad');
            $this->setSessionData($data);
            return $this->controller->redirectBack();
        }

        if ($campaign->dbObject('VotingClosingDateTime')->InPast()) {
            $this->sessionMessage(_t(__CLASS__ . '.VOTING_CLOSED', 'Voting is closed'), 'bad');
            $this->setSessionData($data);
            return $this->controller->redirectBack();
        }

        /** @var VotingCode $code */
        $code = null;
        if ($campaign->CampaignUsesCodes) {
            if (!isset($data['Code'])) {
                $this->sessionMessage(_t(__CLASS__ . '.CODE_MISSING', 'A code is missing'), 'bad');
                $this->setSessionData($data);
                return $this->controller->redirectBack();
            }
            $code = $campaign->VotingCodes()->filter('Code', $data['Code'])->first();
            if (!$code || !$code->exists() || $code->IsUsed) {
                $this->sessionMessage(_t(__CLASS__ . '.CODE_INVALID', 'This code is not valid or already used'), 'bad');
                $this->setSessionData($data);
                return $this->controller->redirectBack();
            }
            $code->IsUsed = true;
            $code->write();
        }

        $vote = new Vote([
            'CampaignID' => $campaign->ID,
            'NominationID' => $data['Choice'],
            'Email' => $data['Email'],
            'Status' => $campaign->EnableVoteConfirmation ? Vote::STATUS_UNCONFIRMED : Vote::STATUS_CONFIRMED,
            'Weight' => $code ? $code->Weight : 1,
            'VotingCodeID' => $code ? $code->ID : null
        ]);
        $vote->write();
        if ($campaign->EnableVoteConfirmation) {
            $vote->sendConfirmEmail($campaign->ConfirmEmailSender, $campaign->ConfirmEmailSubject);
            $this->sessionMessage(_t(__CLASS__ . '.VOTE_SUCCESSFUL', 'An email is send to your email address to confirm your vote'), 'good');
        }else {
            $this->sessionMessage(_t(__CLASS__ . '.VOTE_SUCCESSFUL_CONFIRMED', 'Thank you for voting'), 'good');
        }
        
        return $this->controller->redirectBack();
    }
}
