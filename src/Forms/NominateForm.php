<?php

namespace TheWebmen\VotingCampaign\Forms;

use SilverStripe\AssetAdmin\Controller\AssetAdmin;
use SilverStripe\Assets\File;
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
use TheWebmen\VotingCampaign\Models\VotingCampaign;

class NominateForm extends Form
{
    public function __construct(
        RequestHandler $controller = null,
        $name = 'NominateForm'
    ) {
        $fields = FieldList::create([
            TextField::create('FirstName', _t(__CLASS__ . '.FIRST_NAME', 'Firstname')),
            TextField::create('Surname', _t(__CLASS__ . '.SURNAME', 'Surname')),
            EmailField::create('EmailAddress', _t(__CLASS__ . '.EMAIL_ADDRESS', 'Email address'))
        ]);

        $actions = FieldList::create([
            FormAction::create('handle', _t(__CLASS__ . '.NOMINATE', 'Nominate'))
        ]);

        $validator = RequiredFields::create([
            'FirstName',
            'Surname',
            'EmailAddress'
        ]);

        $this->extend('UpdateForm', $controller, $fields, $actions, $validator);

        parent::__construct($controller, $name, $fields, $actions, $validator);
    }

    public function handle(array $data, Form $form)
    {
        /** @var VotingCampaign $campaign */
        $campaign = $this->controller->Campaign();
        if ($campaign->dbObject('NominationClosingDateTime')->InPast()) {
            $this->sessionMessage(_t(__CLASS__ . '.NOMINATION_CLOSED', 'Nominations are closed'), 'bad');
            return $this->controller->redirectBack();
        }

        $nomination = new Nomination([
            'FirstName' => $data['FirstName'],
            'Surname' => $data['Surname'],
            'EmailAddress' => $data['EmailAddress'],
            'Status' => Nomination::STATUS_NEW,
            'CampaignID' => $campaign->ID
        ]);

        unset($data['FirstName']);
        unset($data['Surname']);
        unset($data['EmailAddress']);
        unset($data['SecurityID']);
        unset($data['action_handle']);
        unset($data['MAX_FILE_SIZE']);

        $json = [];
        foreach ($data as $extraFieldName => $extraField) {
            $fieldClass = get_class($form->Fields()->dataFieldByName($extraFieldName));
            if (!is_array($extraField) || !array_key_exists('tmp_name', $extraField)) {
                $json[$extraFieldName] = [
                    'value' => $extraField,
                    'fieldClass' => $fieldClass
                ];
                continue;
            }
            if (empty($extraField['tmp_name'])) {
                continue;
            }
            $upload = new Upload();
            $upload->loadIntoFile($extraField, null, 'VotingCampaignNominations');
            AssetAdmin::singleton()->getObjectFromData($upload->getFile());
            $json[$extraFieldName] = [
                'value' => $upload->getFile()->ID,
                'fieldClass' => $fieldClass
            ];
        }
        $nomination->ExtraFieldsData = json_encode($json);

        $this->extend('BeforeFinishHandle', $data, $nomination, $form);

        $nomination->write();

        if ($campaign->EnableNominationEmail) {
            $nomination->sendEmail($campaign->NominationEmailFrom, $campaign->NominationEmailSubject);
        }

        if ($campaign->NominateFormSuccessText) {
            $this->sessionMessage($campaign->NominateFormSuccessText, 'good');
        } else {
            $this->sessionMessage(_t(__CLASS__ . '.NOMINATION_SUCCESSFUL', 'Your nomination is successful'), 'good');
        }

        return $this->controller->redirectBack();
    }
}
