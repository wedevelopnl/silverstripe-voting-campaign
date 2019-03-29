<?php

namespace TheWebmen\VotingCampaign\Forms;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Upload;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\FileField;
use SilverStripe\Forms\Validator;
use TheWebmen\VotingCampaign\Models\Nomination;

class NominateForm extends Form
{
    public function __construct(
        RequestHandler $controller = null,
        $name = 'NominateForm'
    ) {
        $fields = FieldList::create([
            TextField::create('Name', _t(__CLASS__ . '.NAME', 'Name')),
            EmailField::create('EmailAddress', _t(__CLASS__ . '.EMAIL_ADDRESS', 'Email address')),
            FileField::create('Photo', _t(__CLASS__ . '.PHOTO', 'Photo')),
            TextareaField::create('Text', _t(__CLASS__ . '.TEXT', 'Text'))
        ]);

        $actions = FieldList::create([
            FormAction::create('handle', _t(__CLASS__ . '.NOMINATE', 'Nominate'))
        ]);

        $validator = RequiredFields::create([
            'Name',
            'EmailAddress',
            'Text'
        ]);

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    public function handle(array $data, Form $form)
    {
        if ($this->controller->Campaign()->dbObject('NominationClosingDateTime')->InPast()) {
            $this->sessionMessage(_t(__CLASS__ . '.NOMINATION_CLOSED', 'Nominations are closed'), 'bad');
            return $this->controller->redirectBack();
        }

        $photo = null;
        if (array_key_exists('Photo', $data)) {
            $upload = new Upload();
            $photo = new Image();
            $upload->loadIntoFile($data['Photo'], $photo, 'VotingCampaignNominations');
        }

        $nomination = new Nomination([
            'Name' => $data['Name'],
            'EmailAddress' => $data['EmailAddress'],
            'Text' => $data['Text'],
            'Status' => Nomination::STATUS_NEW,
            'CampaignID' => $this->controller->CampaignID,
            'PhotoID' => $photo ? $photo->ID : null,
        ]);
        $nomination->write();

        $this->sessionMessage(_t(__CLASS__ . '.NOMINATION_SUCCESSFUL', 'Your nomination is successful'), 'good');
        return $this->controller->redirectBack();
    }
}
